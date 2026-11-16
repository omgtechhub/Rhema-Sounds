<?php
// ============================================================
//  RHEMA SOUNDS – IMAGE COMPRESSION PIPELINE
// ------------------------------------------------------------
//  Every user-supplied image (profile avatars, track thumbnails)
//  passes through here before it is written to disk. Originals are
//  never stored: each upload is auto-oriented, centre-cropped,
//  downscaled and re-encoded to WebP (JPEG/PNG fallback), and a set
//  of smaller variants is emitted so the UI can request the size it
//  actually renders instead of a 4000px phone photo in a 48px row.
//
//  Pure GD, no external binaries. Degrades safely: if GD is missing
//  the original is copied through unchanged so uploads never break
//  on a host without the extension.
// ============================================================

// ---- Tunables -------------------------------------------------
if (!defined('IMG_QUALITY_WEBP')) define('IMG_QUALITY_WEBP', 82);
if (!defined('IMG_QUALITY_JPEG')) define('IMG_QUALITY_JPEG', 84);
if (!defined('IMG_QUALITY_PNG'))  define('IMG_QUALITY_PNG', 8);   // 0-9 zlib level
// Refuse to decode absurd images (decompression-bomb guard). 50MP ~ 8000x6250.
if (!defined('IMG_MAX_PIXELS'))   define('IMG_MAX_PIXELS', 50000000);

// Variant ladder per image kind: suffix => longest edge in pixels.
// '' is the primary file, sized for the largest place it is rendered
// on a 2x display; '@md' and '@sm' serve cards and list rows.
const IMG_PRESETS = [
    'thumb'  => ['' => 640, '@md' => 256, '@sm' => 96],
    'avatar' => ['' => 320, '@md' => 128, '@sm' => 64],
];

// Extensions a stored image may carry.
const IMG_OUTPUT_EXTS = ['webp', 'jpg', 'jpeg', 'png', 'gif'];

// ============================================================
//  CAPABILITY PROBES
// ============================================================

function imgGdAvailable(): bool {
    return extension_loaded('gd') && function_exists('imagecreatetruecolor');
}

function imgWebpAvailable(): bool {
    static $ok = null;
    if ($ok === null) {
        $ok = imgGdAvailable()
            && function_exists('imagewebp')
            && !empty(gd_info()['WebP Support']);
    }
    return $ok;
}

// ============================================================
//  MEMORY GUARD
//  GD decodes to raw truecolour: width * height * 4 bytes, and we
//  hold a source and a destination canvas at once. Raise the limit
//  when we can, and bail out before an OOM fatal when we cannot.
// ============================================================

function imgMemoryLimitBytes(): int {
    $raw = trim((string)ini_get('memory_limit'));
    if ($raw === '' || $raw === '-1') return -1;               // unlimited
    $unit  = strtolower(substr($raw, -1));
    $value = (int)$raw;
    if ($unit === 'g') return $value * 1024 * 1024 * 1024;
    if ($unit === 'm') return $value * 1024 * 1024;
    if ($unit === 'k') return $value * 1024;
    return $value;
}

function imgEnsureMemory(int $w, int $h): bool {
    // Source canvas + destination canvas + decoder overhead.
    $need = (int)($w * $h * 4 * 2.2) + (24 * 1024 * 1024);
    $have = imgMemoryLimitBytes();
    if ($have === -1 || $have >= $need) return true;

    @ini_set('memory_limit', (string)min($need, 512 * 1024 * 1024));
    $have = imgMemoryLimitBytes();
    return $have === -1 || $have >= $need;
}

// ============================================================
//  DECODE
// ============================================================

// Reads real dimensions/type from the file header, never from the
// client-supplied name or MIME.
function imgProbe(string $path): ?array {
    if (!is_readable($path)) return null;
    $info = @getimagesize($path);
    if (!$info || empty($info[0]) || empty($info[1])) return null;
    return [
        'width'  => (int)$info[0],
        'height' => (int)$info[1],
        'type'   => (int)$info[2],
        'mime'   => (string)($info['mime'] ?? ''),
    ];
}

function imgTypeToExt(int $type): string {
    switch ($type) {
        case IMAGETYPE_JPEG: return 'jpg';
        case IMAGETYPE_PNG:  return 'png';
        case IMAGETYPE_GIF:  return 'gif';
        case IMAGETYPE_WEBP: return 'webp';
    }
    return '';
}

// Image kinds that can carry transparency and must not be flattened.
function imgTypeHasAlpha(int $type): bool {
    return in_array($type, [IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP], true);
}

function imgLoad(string $path, int $type) {
    switch ($type) {
        case IMAGETYPE_JPEG: return @imagecreatefromjpeg($path);
        case IMAGETYPE_PNG:  return @imagecreatefrompng($path);
        case IMAGETYPE_GIF:  return @imagecreatefromgif($path);
        case IMAGETYPE_WEBP:
            return function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false;
    }
    return false;
}

// Phone cameras store the frame unrotated plus an EXIF orientation
// flag. Browsers honour it, GD does not, so bake it in before resizing
// or portrait shots come out sideways.
function imgAutoOrient($im, string $path, int $type) {
    if ($type !== IMAGETYPE_JPEG || !function_exists('exif_read_data')) return $im;

    $exif = @exif_read_data($path);
    $o    = (int)($exif['Orientation'] ?? 0);
    if ($o < 2 || $o > 8) return $im;

    // Mirrored orientations need a horizontal flip first.
    if (in_array($o, [2, 4, 5, 7], true) && function_exists('imageflip')) {
        imageflip($im, IMG_FLIP_HORIZONTAL);
    }
    $angle = 0;
    if ($o === 3 || $o === 4)      $angle = 180;
    elseif ($o === 5 || $o === 6)  $angle = -90;
    elseif ($o === 7 || $o === 8)  $angle = 90;

    if ($angle !== 0 && function_exists('imagerotate')) {
        $rotated = @imagerotate($im, $angle, 0);
        if ($rotated !== false) {
            imagedestroy($im);
            $im = $rotated;
        }
    }
    return $im;
}

// ============================================================
//  RESIZE / CROP
// ============================================================

function imgPrepareCanvas(int $w, int $h, bool $alpha) {
    $dst = imagecreatetruecolor($w, $h);
    if (!$dst) return false;
    if ($alpha) {
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        $clear = imagecolorallocatealpha($dst, 0, 0, 0, 127);
        imagefilledrectangle($dst, 0, 0, $w, $h, $clear);
    }
    return $dst;
}

// $square centre-crops to a 1:1 frame. Every surface in the app renders
// these inside a square box with object-fit:cover, so cropping here is
// visually identical to what the browser already does - it just stops
// us storing and shipping the pixels that get clipped anyway.
// Never upscales: a 120px source stays 120px.
function imgResample($src, int $sw, int $sh, int $maxEdge, bool $square, bool $alpha) {
    if ($square) {
        $side = min($sw, $sh);
        $sx   = (int)(($sw - $side) / 2);
        $sy   = (int)(($sh - $side) / 2);
        $out  = min($side, $maxEdge);
        $dst  = imgPrepareCanvas($out, $out, $alpha);
        if (!$dst) return false;
        imagecopyresampled($dst, $src, 0, 0, $sx, $sy, $out, $out, $side, $side);
        return $dst;
    }

    $scale = min(1.0, $maxEdge / max($sw, $sh));
    $dw    = max(1, (int)round($sw * $scale));
    $dh    = max(1, (int)round($sh * $scale));
    $dst   = imgPrepareCanvas($dw, $dh, $alpha);
    if (!$dst) return false;
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $dw, $dh, $sw, $sh);
    return $dst;
}

// ============================================================
//  ENCODE
// ============================================================

// Writes $destNoExt with whichever modern format this build supports
// and returns the full path written, or '' on failure. Encoders are
// tried in order and each result is verified by re-reading it, so a
// broken GD WebP build falls back instead of writing a corrupt file.
function imgEncode($im, string $destNoExt, bool $alpha): string {
    $attempts = [];
    if (imgWebpAvailable()) $attempts[] = 'webp';
    $attempts[] = $alpha ? 'png' : 'jpg';
    $attempts[] = $alpha ? 'jpg' : 'png';

    foreach ($attempts as $ext) {
        $path = $destNoExt . '.' . $ext;
        $ok   = false;

        if ($ext === 'webp') {
            imagesavealpha($im, true);
            $ok = @imagewebp($im, $path, IMG_QUALITY_WEBP);
        } elseif ($ext === 'png') {
            imagesavealpha($im, true);
            $ok = @imagepng($im, $path, IMG_QUALITY_PNG);
        } else {
            // JPEG cannot hold transparency; flatten onto the app's
            // dark surface colour so edges never fringe white.
            $flat = $im;
            if ($alpha) {
                $w = imagesx($im);
                $h = imagesy($im);
                $flat = imagecreatetruecolor($w, $h);
                $bg   = imagecolorallocate($flat, 24, 24, 24);
                imagefilledrectangle($flat, 0, 0, $w, $h, $bg);
                imagecopy($flat, $im, 0, 0, 0, 0, $w, $h);
            }
            $ok = @imagejpeg($flat, $path, IMG_QUALITY_JPEG);
            if ($flat !== $im) imagedestroy($flat);
        }

        if ($ok && is_file($path) && filesize($path) > 0 && @getimagesize($path)) {
            return $path;
        }
        if (is_file($path)) @unlink($path);
    }
    return '';
}

// ============================================================
//  HOUSEKEEPING
// ============================================================

// Removes a stored image and every variant of it, whatever extension
// each happens to carry. Used before writing a replacement so an old
// JPEG can never shadow a new WebP.
function imgDeleteVariants(string $dir, string $baseName): void {
    $suffixes = array_unique(array_merge(
        array_keys(IMG_PRESETS['thumb']),
        array_keys(IMG_PRESETS['avatar'])
    ));
    foreach ($suffixes as $suffix) {
        foreach (IMG_OUTPUT_EXTS as $ext) {
            $p = $dir . $baseName . $suffix . '.' . $ext;
            if (is_file($p)) @unlink($p);
        }
    }
}

// Finds the stored file for a base name + variant suffix, or '' if absent.
function imgFindVariant(string $dir, string $baseName, string $suffix = ''): string {
    foreach (IMG_OUTPUT_EXTS as $ext) {
        $p = $dir . $baseName . $suffix . '.' . $ext;
        if (is_file($p)) return $p;
    }
    return '';
}

// ============================================================
//  PUBLIC ENTRY POINTS
// ============================================================

/**
 * Compress $srcPath into $dir/$baseName plus its smaller variants.
 *
 * @param string $preset 'thumb' | 'avatar' - picks the size ladder.
 * @param bool   $square centre-crop to 1:1.
 * @return array ok, primary, variants, bytes_in, bytes_out, fallback, error
 */
function imgCompressTo(string $srcPath, string $dir, string $baseName, string $preset = 'thumb', bool $square = true): array {
    $result = [
        'ok'        => false,
        'primary'   => '',
        'variants'  => [],
        'bytes_in'  => is_file($srcPath) ? (int)filesize($srcPath) : 0,
        'bytes_out' => 0,
        'fallback'  => false,
        'error'     => '',
    ];

    $sizes = IMG_PRESETS[$preset] ?? IMG_PRESETS['thumb'];
    if (!is_dir($dir)) @mkdir($dir, 0755, true);

    $probe = imgProbe($srcPath);
    if (!$probe) {
        $result['error'] = 'Not a readable image';
        return $result;
    }

    // Without GD, or for anything we cannot safely decode, store the
    // original so the feature still works - just uncompressed.
    $decodable = imgGdAvailable()
        && imgTypeToExt($probe['type']) !== ''
        && ($probe['width'] * $probe['height']) <= IMG_MAX_PIXELS
        && imgEnsureMemory($probe['width'], $probe['height']);

    if (!$decodable) {
        $ext  = imgTypeToExt($probe['type']) ?: 'jpg';
        $dest = $dir . $baseName . '.' . $ext;
        imgDeleteVariants($dir, $baseName);
        if (!@copy($srcPath, $dest)) {
            $result['error'] = 'Could not store image';
            return $result;
        }
        @chmod($dest, 0644);
        $result['ok']        = true;
        $result['fallback']  = true;
        $result['primary']   = $dest;
        $result['bytes_out'] = (int)filesize($dest);
        return $result;
    }

    $src = imgLoad($srcPath, $probe['type']);
    if (!$src) {
        $result['error'] = 'Could not decode image';
        return $result;
    }

    // Palette GIF/PNG must become truecolour before resampling.
    if (function_exists('imagepalettetotruecolor')) @imagepalettetotruecolor($src);
    $src = imgAutoOrient($src, $srcPath, $probe['type']);

    $sw    = imagesx($src);
    $sh    = imagesy($src);
    $alpha = imgTypeHasAlpha($probe['type']);

    imgDeleteVariants($dir, $baseName);

    $written = [];
    foreach ($sizes as $suffix => $maxEdge) {
        $canvas = imgResample($src, $sw, $sh, (int)$maxEdge, $square, $alpha);
        if (!$canvas) continue;
        $path = imgEncode($canvas, $dir . $baseName . $suffix, $alpha);
        imagedestroy($canvas);
        if ($path !== '') {
            @chmod($path, 0644);
            $written[$suffix] = $path;
            $result['bytes_out'] += (int)filesize($path);
        }
    }
    imagedestroy($src);

    if (!isset($written[''])) {
        // Primary failed to encode - nothing usable was produced.
        foreach ($written as $p) @unlink($p);
        $result['error'] = 'Could not encode image';
        return $result;
    }

    $result['ok']       = true;
    $result['primary']  = $written[''];
    $result['variants'] = $written;
    return $result;
}

/**
 * Lazily build a missing variant from an already-stored primary.
 * Lets images uploaded before this pipeline existed gain their small
 * sizes on first request instead of needing a migration pass.
 *
 * @return string path to the variant, or the primary if it cannot be made.
 */
function imgEnsureVariant(string $dir, string $baseName, string $suffix, string $preset = 'thumb', bool $square = true): string {
    $existing = imgFindVariant($dir, $baseName, $suffix);
    if ($existing !== '') return $existing;

    $primary = imgFindVariant($dir, $baseName, '');
    if ($primary === '') return '';
    if ($suffix === '') return $primary;

    $sizes   = IMG_PRESETS[$preset] ?? IMG_PRESETS['thumb'];
    $maxEdge = (int)($sizes[$suffix] ?? 0);
    if ($maxEdge <= 0 || !imgGdAvailable()) return $primary;

    $probe = imgProbe($primary);
    if (!$probe || imgTypeToExt($probe['type']) === '') return $primary;
    if (!imgEnsureMemory($probe['width'], $probe['height'])) return $primary;

    $src = imgLoad($primary, $probe['type']);
    if (!$src) return $primary;
    if (function_exists('imagepalettetotruecolor')) @imagepalettetotruecolor($src);

    $alpha  = imgTypeHasAlpha($probe['type']);
    $canvas = imgResample($src, imagesx($src), imagesy($src), $maxEdge, $square, $alpha);
    imagedestroy($src);
    if (!$canvas) return $primary;

    $path = imgEncode($canvas, $dir . $baseName . $suffix, $alpha);
    imagedestroy($canvas);
    if ($path === '') return $primary;

    @chmod($path, 0644);
    return $path;
}

// Maps an API ?size= value onto a variant suffix.
function imgSizeSuffix(?string $size): string {
    switch (strtolower((string)$size)) {
        case 'sm':
        case 'small':
            return '@sm';
        case 'md':
        case 'medium':
            return '@md';
    }
    return '';
}
