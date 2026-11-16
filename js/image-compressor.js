/* ============================================================
 *  RHEMA SOUNDS – CLIENT-SIDE IMAGE COMPRESSOR
 * ------------------------------------------------------------
 *  Shrinks a picked image in the browser before it is uploaded.
 *  The server re-compresses authoritatively (imagecompress.php) --
 *  this pass exists so a creator on mobile data sends ~80 KB
 *  instead of an 8 MB camera original, which is the difference
 *  between an instant save and a minute-long upload.
 *
 *  Always fails soft: if anything goes wrong the original File is
 *  returned unchanged and the server still handles it correctly.
 *
 *  Usage:
 *    const small = await RhemaImage.compress(file, 'avatar');
 *    fd.append('avatar', small);
 * ============================================================ */
(function (global) {
  'use strict';

  // Longest edge sent to the server. Kept above the server's own
  // primary size (640 thumb / 320 avatar) so it still has headroom
  // to resample cleanly rather than upscaling our output.
  var PRESETS = {
    avatar: { maxEdge: 512,  quality: 0.85, square: true },
    thumb:  { maxEdge: 1024, quality: 0.85, square: true }
  };

  // Below this there is nothing worth doing; skip the work entirely.
  var SKIP_BELOW_BYTES = 120 * 1024;

  function isImage(file) {
    return file && typeof file === 'object' && /^image\//.test(file.type || '');
  }

  // Animated GIFs would lose their animation on a canvas round-trip,
  // so they are passed through untouched and left to the server.
  function isAnimatedCandidate(file) {
    return (file.type || '') === 'image/gif';
  }

  function supportsWebpEncode() {
    try {
      var c = document.createElement('canvas');
      c.width = c.height = 1;
      return c.toDataURL('image/webp').indexOf('data:image/webp') === 0;
    } catch (e) {
      return false;
    }
  }

  // createImageBitmap honours EXIF orientation with from-image, which
  // keeps portrait phone shots upright. The <img> path is the fallback
  // for browsers without it (modern browsers auto-orient <img> too).
  function loadBitmap(file) {
    if (global.createImageBitmap) {
      try {
        return global.createImageBitmap(file, { imageOrientation: 'from-image' })
          .catch(function () { return global.createImageBitmap(file); })
          .catch(function () { return loadViaImgElement(file); });
      } catch (e) { /* fall through */ }
    }
    return loadViaImgElement(file);
  }

  function loadViaImgElement(file) {
    return new Promise(function (resolve, reject) {
      var url = URL.createObjectURL(file);
      var img = new Image();
      img.onload = function () { URL.revokeObjectURL(url); resolve(img); };
      img.onerror = function () { URL.revokeObjectURL(url); reject(new Error('decode failed')); };
      img.src = url;
    });
  }

  function dimensionsOf(source) {
    return {
      w: source.width  || source.naturalWidth  || 0,
      h: source.height || source.naturalHeight || 0
    };
  }

  function toBlob(canvas, mime, quality) {
    return new Promise(function (resolve) {
      if (canvas.toBlob) {
        canvas.toBlob(function (blob) { resolve(blob); }, mime, quality);
      } else {
        try {
          var parts = canvas.toDataURL(mime, quality).split(',');
          var bin   = atob(parts[1]);
          var bytes = new Uint8Array(bin.length);
          for (var i = 0; i < bin.length; i++) bytes[i] = bin.charCodeAt(i);
          resolve(new Blob([bytes], { type: mime }));
        } catch (e) {
          resolve(null);
        }
      }
    });
  }

  function renamed(file, blob, mime) {
    var ext  = mime === 'image/webp' ? '.webp' : '.jpg';
    var base = (file.name || 'image').replace(/\.[^.]+$/, '') || 'image';
    try {
      return new File([blob], base + ext, { type: mime, lastModified: Date.now() });
    } catch (e) {
      // Older Safari has no File constructor; a Blob works in FormData.
      blob.name = base + ext;
      return blob;
    }
  }

  /**
   * @param {File} file         the user's picked file
   * @param {string} presetName 'avatar' | 'thumb'
   * @returns {Promise<File|Blob>} compressed file, or the original on any failure
   */
  function compress(file, presetName) {
    var preset = PRESETS[presetName] || PRESETS.thumb;

    if (!isImage(file) || isAnimatedCandidate(file)) return Promise.resolve(file);

    return loadBitmap(file).then(function (source) {
      var dim = dimensionsOf(source);
      if (!dim.w || !dim.h) return file;

      // Already small in both bytes and pixels: leave it alone.
      if (file.size < SKIP_BELOW_BYTES && Math.max(dim.w, dim.h) <= preset.maxEdge) {
        if (source.close) source.close();
        return file;
      }

      var sx = 0, sy = 0, sw = dim.w, sh = dim.h, dw, dh;

      if (preset.square) {
        // Centre-crop to 1:1 -- every slot that renders these is a
        // square box with object-fit:cover, so this crops nothing the
        // user would have seen anyway.
        var side = Math.min(dim.w, dim.h);
        sx = Math.floor((dim.w - side) / 2);
        sy = Math.floor((dim.h - side) / 2);
        sw = sh = side;
        dw = dh = Math.min(side, preset.maxEdge);
      } else {
        var scale = Math.min(1, preset.maxEdge / Math.max(dim.w, dim.h));
        dw = Math.max(1, Math.round(dim.w * scale));
        dh = Math.max(1, Math.round(dim.h * scale));
      }

      var canvas = document.createElement('canvas');
      canvas.width  = dw;
      canvas.height = dh;
      var ctx = canvas.getContext('2d');
      if (!ctx) return file;
      ctx.imageSmoothingEnabled = true;
      if ('imageSmoothingQuality' in ctx) ctx.imageSmoothingQuality = 'high';
      ctx.drawImage(source, sx, sy, sw, sh, 0, 0, dw, dh);
      if (source.close) source.close();

      var mime = supportsWebpEncode() ? 'image/webp' : 'image/jpeg';

      return toBlob(canvas, mime, preset.quality).then(function (blob) {
        // Never hand back something larger than what we were given.
        if (!blob || !blob.size || blob.size >= file.size) return file;
        return renamed(file, blob, mime);
      });
    }).catch(function () {
      return file;   // server-side compression still applies
    });
  }

  // Human-readable byte count, handy for upload status text.
  function formatBytes(n) {
    if (!n) return '0 KB';
    if (n < 1024 * 1024) return (n / 1024).toFixed(0) + ' KB';
    return (n / 1024 / 1024).toFixed(1) + ' MB';
  }

  global.RhemaImage = { compress: compress, formatBytes: formatBytes, presets: PRESETS };
})(window);
