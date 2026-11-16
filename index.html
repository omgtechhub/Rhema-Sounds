<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Rhema Sounds – Stream Music & Video</title>

  <!-- PWA: capture the native install prompt as early as humanly possible.
       Chrome can fire `beforeinstallprompt` the moment it decides the page is
       installable — sometimes before render-blocking resources (Tailwind CDN,
       hls.js, etc. below) finish loading and the bottom-of-page app script
       runs. If that happens before a listener exists, the event is lost
       forever and the Install button has nothing to trigger — which is
       exactly why it silently did nothing. Stashing it on `window` here,
       before any other script loads, guarantees it is never missed. -->
  <script>
    window.__rhemaInstallEvent = null;
    window.addEventListener('beforeinstallprompt', function (e) {
      e.preventDefault();
      window.__rhemaInstallEvent = e;
      if (typeof window.__rhemaOnInstallPromptReady === 'function') window.__rhemaOnInstallPromptReady(e);
    });
    window.addEventListener('appinstalled', function () {
      window.__rhemaInstallEvent = null;
      if (typeof window.__rhemaOnAppInstalled === 'function') window.__rhemaOnAppInstalled();
    });
  </script>

  <!-- PWA / "Add to Home Screen" support -->
  <link rel="manifest" href="manifest.json" crossorigin="use-credentials"/>
  <meta name="theme-color" content="#121212"/>
  <link rel="icon" type="image/png" sizes="192x192" href="icon-192.png"/>
  <link rel="apple-touch-icon" href="apple-touch-icon.png"/>
  <meta name="mobile-web-app-capable" content="yes"/>
  <meta name="apple-mobile-web-app-capable" content="yes"/>
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent"/>
  <meta name="apple-mobile-web-app-title" content="Rhema Sounds"/>
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <script src="https://cdn.jsdelivr.net/npm/hls.js@latest/dist/hls.min.js"></script>
  <script src="https://accounts.google.com/gsi/client" async defer></script>
  <!-- Shrinks picked images in the browser before upload; the server
       re-compresses authoritatively in imagecompress.php -->
  <script src="js/image-compressor.js" defer></script>
  <style>
    :root {
      --green: #1db954;
      --green-dark: #17a347;
      --bg-main: #121212;
      --bg-sidebar: #000000;
      --bg-card: #1e1e1e;
      --bg-card2: #282828;
      --bg-player: #181818;
      --text-main: #ffffff;
      --text-muted: #b3b3b3;
      --accent: #e8b84b;
      --page-pad-x: 32px;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { background: var(--bg-main); color: var(--text-main); font-family: 'Segoe UI', Arial, sans-serif; overflow: hidden; height: 100vh; display: flex; flex-direction: column; }
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: #555; border-radius: 3px; }
    ::-webkit-scrollbar-thumb:hover { background: #888; }

    /* LAYOUT */
    #app { display: flex; flex: 1; overflow: hidden; }
    #sidebar { width: 240px; min-width: 240px; background: var(--bg-sidebar); display: flex; flex-direction: column; overflow: hidden; transition: width 0.3s; }
    #sidebar.collapsed { width: 72px; min-width: 72px; }
    #main-content { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
    #topbar { height: 64px; background: rgba(18,18,18,0.95); display: flex; align-items: center; padding: 0 24px; gap: 16px; position: sticky; top: 0; z-index: 10; border-bottom: 1px solid #282828; }
    #page-area { flex: 1; overflow-y: auto; padding: 0; }
    #player-bar { height: 90px; background: var(--bg-player); border-top: 1px solid #282828; display: flex; align-items: center; padding: 0 16px; gap: 8px; z-index: 20; }

    /* SIDEBAR */
    .sidebar-logo { padding: 20px 16px 8px; display: flex; align-items: center; gap: 10px; }
    .sidebar-logo .logo-text { font-size: 1.2rem; font-weight: 800; color: var(--green); white-space: nowrap; overflow: hidden; }
    .sidebar-nav { display: flex; flex-direction: column; gap: 2px; padding: 8px 8px; }
    .nav-item { display: flex; align-items: center; gap: 14px; padding: 10px 12px; border-radius: 8px; cursor: pointer; color: var(--text-muted); font-size: 0.92rem; font-weight: 600; transition: all 0.15s; white-space: nowrap; overflow: hidden; }
    .nav-item:hover { color: #fff; background: #282828; }
    .nav-item.active { color: #fff; background: #282828; }
    .nav-item i { font-size: 1.1rem; min-width: 20px; text-align: center; }
    .nav-item.active i { color: var(--green); }
    .sidebar-divider { height: 1px; background: #282828; margin: 8px 16px; }
    .sidebar-section-title { padding: 8px 20px; font-size: 0.72rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; }
    .playlist-list { flex: 1; overflow-y: auto; padding: 0 8px 8px; }
    .playlist-item { display: flex; align-items: center; gap: 10px; padding: 8px 12px; border-radius: 6px; cursor: pointer; color: var(--text-muted); font-size: 0.85rem; transition: all 0.15s; white-space: nowrap; overflow: hidden; }
    .playlist-item:hover { color: #fff; background: #1a1a1a; }
    .playlist-item.active { color: var(--green); }
    .playlist-item-img { width: 36px; height: 36px; border-radius: 4px; object-fit: cover; background: #333; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 1rem; }

    /* TOPBAR */
    .nav-btn { width: 32px; height: 32px; border-radius: 50%; background: rgba(0,0,0,0.7); border: none; color: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1rem; }
    .nav-btn:hover { background: #333; }
    #search-input { flex: 1; max-width: 360px; background: #2a2a2a; border: 2px solid transparent; border-radius: 24px; padding: 8px 18px 8px 40px; color: #fff; font-size: 0.9rem; outline: none; transition: border 0.2s; }
    #search-input:focus { border-color: var(--green); background: #333; }
    .search-wrap { position: relative; flex: 1; max-width: 360px; }
    .search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted); }
    .search-clear-btn { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 1rem; padding: 4px; display: flex; align-items: center; justify-content: center; }
    .search-clear-btn:hover { color: #fff; }
    /* Use our own clear button instead of the native one */
    #search-input::-webkit-search-cancel-button,
    #search-input::-webkit-search-decoration { -webkit-appearance: none; appearance: none; }
    #search-input::-ms-clear { display: none; }
    #search-suggestions { position: absolute; top: calc(100% + 6px); left: 0; right: 0; background: #282828; border-radius: 8px; box-shadow: 0 8px 32px rgba(0,0,0,0.5); z-index: 90; display: none; flex-direction: column; padding: 6px; max-height: 320px; overflow-y: auto; }
    #search-suggestions.open { display: flex; }
    .search-suggestion-item { display: flex; align-items: center; gap: 10px; padding: 8px 10px; border-radius: 4px; cursor: pointer; font-size: 0.85rem; color: #eee; }
    .search-suggestion-item:hover { background: #3a3a3a; }
    .search-suggestion-item .ss-text { flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .search-suggestion-item .ss-remove { color: var(--text-muted); cursor: pointer; padding: 4px; }
    .search-suggestion-item .ss-remove:hover { color: #fff; }
    .topbar-right { margin-left: auto; display: flex; align-items: center; gap: 12px; }
    .avatar-btn { width: 36px; height: 36px; border-radius: 50%; background: var(--green); border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1rem; color: #000; overflow: hidden; }
    .avatar-btn img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }

    /* PLAYER */
    .player-left { display: flex; align-items: center; gap: 12px; width: 280px; min-width: 200px; }
    .player-thumb { width: 56px; height: 56px; border-radius: 6px; object-fit: cover; background: #333; flex-shrink: 0; }
    .player-info { overflow: hidden; }
    .player-title { font-size: 0.88rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 160px; }
    .player-artist { font-size: 0.78rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 160px; }
    .player-center { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 6px; }
    .player-controls { display: flex; align-items: center; gap: 18px; }
    .ctrl-btn { background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 1.1rem; transition: color 0.15s; }
    .ctrl-btn:hover { color: #fff; }
    .ctrl-btn.active { color: var(--green); }
    .play-btn { width: 36px; height: 36px; border-radius: 50%; background: #fff; border: none; color: #000; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1rem; transition: transform 0.1s, background 0.1s; }
    .play-btn:hover { background: #e0e0e0; transform: scale(1.06); }
    .progress-row { display: flex; align-items: center; gap: 10px; width: 100%; max-width: 560px; }
    .time-label { font-size: 0.72rem; color: var(--text-muted); min-width: 36px; text-align: center; }
    .progress-bar { flex: 1; height: 4px; background: #535353; border-radius: 2px; cursor: pointer; position: relative; }
    .progress-bar:hover .progress-thumb { opacity: 1; }
    .progress-fill { height: 100%; background: var(--green); border-radius: 2px; transition: width 0.1s; }
    .progress-thumb { position: absolute; right: -6px; top: -4px; width: 12px; height: 12px; border-radius: 50%; background: #fff; opacity: 0; transition: opacity 0.15s; }
    .player-right { display: flex; align-items: center; gap: 10px; width: 200px; justify-content: flex-end; }
    .vol-slider { -webkit-appearance: none; width: 80px; height: 4px; border-radius: 2px; background: #535353; cursor: pointer; }
    .vol-slider::-webkit-slider-thumb { -webkit-appearance: none; width: 12px; height: 12px; border-radius: 50%; background: #fff; }

    /* CARDS */
    .card { background: var(--bg-card); border-radius: 10px; padding: 14px; cursor: pointer; transition: background 0.2s; }
    .card:hover { background: var(--bg-card2); }
    .card-img { width: 100%; aspect-ratio: 1; border-radius: 6px; object-fit: cover; background: #333; margin-bottom: 12px; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; overflow: hidden; position: relative; }
    .card-img img { width: 100%; height: 100%; object-fit: cover; border-radius: 6px; }
    .card-title { font-size: 0.9rem; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .card-sub { font-size: 0.78rem; color: var(--text-muted); margin-top: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .card-play-overlay { position: absolute; bottom: 8px; right: 8px; width: 40px; height: 40px; border-radius: 50%; background: var(--green); border: none; color: #000; font-size: 1rem; cursor: pointer; display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.2s, transform 0.2s; transform: translateY(4px); }
    .card:hover .card-play-overlay { opacity: 1; transform: translateY(0); }
    .card.playing { background: rgba(29,185,84,0.10); box-shadow: inset 0 0 0 2px var(--green); }
    .card.playing .card-title { color: var(--green); }
    .card-now-playing-badge { position: absolute; top: 8px; left: 8px; width: 28px; height: 28px; border-radius: 50%; background: rgba(0,0,0,0.7); display: flex; align-items: center; justify-content: center; }

    /* HERO */
    .hero { padding: 80px 32px 40px; background: linear-gradient(180deg, #1a3a1a 0%, #121212 100%); }
    .hero-green { background: linear-gradient(180deg, #1a3a2a 0%, #121212 100%); }

    /* SECTION */
    .section-title { font-size: 1.4rem; font-weight: 800; margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between; }
    .see-all { font-size: 0.8rem; color: var(--text-muted); font-weight: 600; cursor: pointer; }
    .see-all:hover { color: #fff; text-decoration: underline; }
    .grid-cards { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 16px; }
    .grid-cards.list-view { display: flex; flex-direction: column; gap: 4px; }
    .view-toggle-group { display: flex; gap: 2px; background: #2a2a2a; border-radius: 6px; padding: 2px; }
    .view-toggle-btn { background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 0.8rem; padding: 5px 9px; border-radius: 4px; transition: color 0.15s, background 0.15s; }
    .view-toggle-btn:hover { color: #fff; }
    .view-toggle-btn.active { color: #000; background: var(--green); }

    /* TRACK LIST */
    .track-row { display: flex; align-items: center; gap: 12px; padding: 8px 12px; border-radius: 6px; cursor: pointer; transition: background 0.15s; }
    .track-row:hover { background: #2a2a2a; }
    .track-row.playing { color: var(--green); background: rgba(29,185,84,0.10); }
    .track-row.playing:hover { background: rgba(29,185,84,0.16); }
    .track-num { width: 24px; text-align: center; font-size: 0.85rem; color: var(--text-muted); }
    .track-thumb { width: 40px; height: 40px; border-radius: 4px; object-fit: cover; background: #333; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; overflow: hidden; }
    .track-thumb img { width: 100%; height: 100%; object-fit: cover; }
    .track-info { flex: 1; overflow: hidden; }
    .track-name { font-size: 0.9rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .track-artist { font-size: 0.78rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .track-duration { font-size: 0.8rem; color: var(--text-muted); margin-left: auto; }
    .track-actions { display: flex; align-items: center; gap: 8px; opacity: 0; transition: opacity 0.15s; }
    .track-row:hover .track-actions { opacity: 1; }
    .track-action-btn { background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 0.9rem; }
    .track-action-btn:hover { color: #fff; }
    .track-action-btn.liked { color: var(--green); }

    /* LIVE BADGE */
    .live-badge { background: #e8312a; color: #fff; font-size: 0.65rem; font-weight: 800; padding: 2px 6px; border-radius: 3px; letter-spacing: 0.08em; animation: pulse 1.5s infinite; }
    @keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: 0.6; } }
    .nav-icon-wrap { position: relative; display: inline-flex; }
    .live-nav-dot { position: absolute; top: -3px; right: -4px; width: 8px; height: 8px; background: #e8312a; border-radius: 50%; display: none; animation: pulse 1.5s infinite; }
    .live-nav-dot.show { display: block; }

    /* MODAL */
    .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 100; display: none; align-items: center; justify-content: center; }
    .modal-overlay.open { display: flex; }
    .modal-box { background: #282828; border-radius: 12px; padding: 28px; max-width: 480px; width: 90%; max-height: 80vh; overflow-y: auto; }
    .modal-title { font-size: 1.2rem; font-weight: 800; margin-bottom: 16px; }
    .modal-close { float: right; background: none; border: none; color: #fff; font-size: 1.4rem; cursor: pointer; margin-top: -4px; }

    /* VIDEO */
    .video-thumb { width: 100%; aspect-ratio: 16/9; background: #000; border-radius: 8px; overflow: hidden; position: relative; }
    .video-thumb video, .video-thumb iframe { width: 100%; height: 100%; border: none; }

    /* TABS */
    .tab-row { display: flex; gap: 4px; padding: 0 32px; border-bottom: 1px solid #282828; }
    .tab-btn { padding: 12px 20px; font-size: 0.88rem; font-weight: 700; color: var(--text-muted); background: none; border: none; cursor: pointer; border-bottom: 2px solid transparent; transition: all 0.15s; }
    .tab-btn.active { color: #fff; border-bottom-color: var(--green); }
    .tab-btn:hover { color: #fff; }

    /* QUEUE */
    #queue-panel { width: 300px; background: #121212; border-left: 1px solid #282828; overflow-y: auto; display: none; flex-direction: column; }
    #queue-panel.open { display: flex; }

    /* LYRICS */
    .lyrics-box { background: #1a1a1a; border-radius: 10px; padding: 24px; max-height: 400px; overflow-y: auto; line-height: 2; font-size: 1rem; color: var(--text-muted); }
    .lyrics-box p.active-line { color: #fff; font-weight: 700; font-size: 1.1rem; }

    /* INPUT */
    .form-input { width: 100%; background: #3a3a3a; border: 1px solid #555; border-radius: 6px; padding: 10px 14px; color: #fff; font-size: 0.9rem; outline: none; }
    .form-input:focus { border-color: var(--green); }
    .btn-green { background: var(--green); color: #000; font-weight: 700; border: none; border-radius: 24px; padding: 10px 24px; cursor: pointer; font-size: 0.9rem; transition: background 0.15s; }
    .btn-green:hover { background: var(--green-dark); }
    .btn-outline { background: transparent; color: #fff; font-weight: 700; border: 1px solid #fff; border-radius: 24px; padding: 10px 24px; cursor: pointer; font-size: 0.9rem; }
    .btn-outline:hover { background: rgba(255,255,255,0.1); }

    /* TOAST */
    #toast { position: fixed; bottom: 110px; left: 50%; transform: translateX(-50%); background: var(--green); color: #000; font-weight: 700; padding: 10px 24px; border-radius: 24px; font-size: 0.88rem; z-index: 200; opacity: 0; transition: opacity 0.3s; pointer-events: none; }
    @media (max-width: 600px) { #toast { bottom: 150px; } }
    #toast.show { opacity: 1; }

    /* INSTALL APP BANNER */
    #install-banner { display: none; position: fixed; left: 12px; right: 12px; bottom: 16px; z-index: 250; background: #282828; border: 1px solid #3a3a3a; border-radius: 14px; padding: 14px 16px; align-items: center; gap: 12px; box-shadow: 0 8px 28px rgba(0,0,0,0.55); }
    #install-banner.show { display: flex; }
    #install-banner .ib-icon { width: 44px; height: 44px; border-radius: 12px; background: var(--green); display: flex; align-items: center; justify-content: center; font-size: 1.4rem; flex-shrink: 0; }
    #install-banner .ib-text { flex: 1; min-width: 0; }
    #install-banner .ib-title { font-weight: 800; font-size: 0.9rem; }
    #install-banner .ib-sub { font-size: 0.78rem; color: var(--text-muted); margin-top: 2px; }
    #install-banner .ib-btn { background: var(--green); color: #000; font-weight: 800; font-size: 0.82rem; border: none; border-radius: 20px; padding: 9px 18px; cursor: pointer; white-space: nowrap; }
    #install-banner .ib-close { background: none; border: none; color: var(--text-muted); font-size: 1.1rem; cursor: pointer; padding: 4px 6px; flex-shrink: 0; }
    @media (max-width: 600px) { #install-banner { bottom: 150px; } }

    /* LIKE HEART */
    .heart-btn { background: none; border: none; cursor: pointer; color: var(--text-muted); font-size: 1rem; }
    .heart-btn.liked { color: var(--green); }

    /* NOW PLAYING — MOBILE SWIPEABLE LAYOUT (Song | Lyrics) */
    .np-mobile { display: none; }
    @media (max-width: 600px) {
      #page-nowplaying .np-desktop { display: none; }
      .np-mobile { display: block; padding-top: 18px; }
      .np-mobile-tabs { display: flex; align-items: center; justify-content: center; gap: 10px; font-weight: 700; font-size: 0.95rem; color: var(--text-muted); margin-bottom: 24px; }
      .np-tab { cursor: pointer; padding: 4px 6px; }
      .np-tab.active { color: #fff; }
      .np-tab-sep { color: #555; }
      .np-swipe { display: flex; overflow-x: auto; scroll-snap-type: x mandatory; -webkit-overflow-scrolling: touch; scrollbar-width: none; }
      .np-swipe::-webkit-scrollbar { display: none; }
      .np-panel { flex: 0 0 100%; scroll-snap-align: start; box-sizing: border-box; padding: 0 28px; text-align: center; }
      .np-art-wrap { display: flex; justify-content: center; margin-bottom: 24px; }
      .np-art { width: 100%; max-width: 280px; aspect-ratio: 1; border-radius: 16px; background: #333; display: flex; align-items: center; justify-content: center; font-size: 5rem; box-shadow: 0 16px 64px rgba(0,0,0,0.5); overflow: hidden; }
      .np-track-title { font-size: 1.3rem; font-weight: 900; margin-bottom: 4px; }
      .np-track-artist { color: var(--text-muted); font-size: 0.9rem; }
      .np-action-row { display: flex; align-items: center; justify-content: space-around; margin: 28px 0 22px; }
      .np-action-btn { background: none; border: none; color: var(--text-muted); font-size: 1.2rem; cursor: pointer; padding: 8px; }
      .np-offline-btn.saved { color: var(--green); }
      .np-progress-row { margin-bottom: 18px; }
      .np-controls { justify-content: center; gap: 28px; }
      .np-play-btn { width: 56px; height: 56px; font-size: 1.3rem; }
      .np-speed-row { display: flex; align-items: center; justify-content: center; gap: 8px; margin-top: 24px; flex-wrap: wrap; }
      .np-speed-btn { background: #1a1a1a; border: 1px solid #2a2a2a; color: var(--text-muted); font-size: 0.74rem; font-weight: 700; border-radius: 16px; padding: 6px 14px; cursor: pointer; transition: background 0.15s, color 0.15s, border-color 0.15s; }
      .np-speed-btn.active { background: var(--green); border-color: var(--green); color: #000; }
      .np-panel-lyrics { text-align: left; padding-top: 6px; }
    }

    /* EQUALIZER ANIMATION */
    .eq-bars { display: flex; align-items: flex-end; gap: 2px; height: 14px; }
    .eq-bar { width: 3px; background: var(--green); border-radius: 1px; animation: eq 0.8s ease-in-out infinite alternate; }
    .eq-bar:nth-child(1) { animation-delay: 0s; height: 6px; }
    .eq-bar:nth-child(2) { animation-delay: 0.2s; height: 10px; }
    .eq-bar:nth-child(3) { animation-delay: 0.4s; height: 7px; }
    .eq-bar:nth-child(4) { animation-delay: 0.1s; height: 12px; }
    .eq-bars.paused .eq-bar { animation-play-state: paused; }
    @keyframes eq { to { transform: scaleY(0.3); } }

    /* PLAYLIST PAGE */
    .playlist-hero { padding: 40px 32px 24px; display: flex; gap: 24px; align-items: flex-end; }
    .playlist-hero-img { width: 200px; height: 200px; border-radius: 8px; background: #333; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 5rem; box-shadow: 0 8px 32px rgba(0,0,0,0.5); }
    .playlist-hero-info h1 { font-size: 2.5rem; font-weight: 900; }
    .playlist-hero-info .meta { color: var(--text-muted); font-size: 0.88rem; margin-top: 8px; }

    /* VIDEO PAGE */
    .video-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
    .video-card { background: var(--bg-card); border-radius: 10px; overflow: hidden; cursor: pointer; transition: background 0.2s; }
    .video-card:hover { background: var(--bg-card2); }
    .video-preview { width: 100%; aspect-ratio: 16/9; background: #222; position: relative; display: flex; align-items: center; justify-content: center; font-size: 3rem; overflow: hidden; }
    .video-preview img { width: 100%; height: 100%; object-fit: cover; }
    .video-thumb-fallback { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; }
    .video-thumb-fallback span { font-size: 2.6rem; font-weight: 800; color: rgba(255,255,255,0.85); }
    .video-play-btn { position: absolute; width: 50px; height: 50px; border-radius: 50%; background: rgba(0,0,0,0.7); border: 2px solid #fff; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
    .video-card-info { padding: 12px; }

    /* RESPONSIVE */
    @media (max-width: 900px) {
      #sidebar { width: 72px; min-width: 72px; }
      .sidebar-logo .logo-text, .nav-item span, .sidebar-section-title, .playlist-item span { display: none; }
      #queue-panel { display: none !important; }
      .player-left { width: 140px; min-width: 140px; }
      .player-right { width: 120px; }
    }
    @media (max-width: 600px) {
      :root { --page-pad-x: 8px; }
      #sidebar { display: none; }
      #player-bar { display: none !important; }
      #topbar { padding: 0 8px; gap: 8px; }
      #topbar .nav-btn { display: none; }
      .search-wrap, #search-input { min-width: 0; max-width: none; }
      .topbar-right { gap: 14px; }
      .hero { padding: 40px var(--page-pad-x) 24px; }
      #page-area { padding-bottom: 150px; }
      .grid-cards, #genre-grid { gap: 8px !important; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)) !important; }
      .video-grid { gap: 10px; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); }
      .playlist-hero { padding: 28px var(--page-pad-x) 20px; gap: 16px; }
      .playlist-hero-img { width: 120px; height: 120px; font-size: 3rem; }
      .playlist-hero-info h1 { font-size: 1.6rem; }
      .tab-row { padding: 0 var(--page-pad-x); }
      .banner-slide { width: 260px; }
    }
    /* MOBILE BOTTOM NAV */
    #mobile-nav { display: none; position: fixed; bottom: 0; left: 0; right: 0; background: #111; border-top: 1px solid #282828; z-index: 15; padding: 8px 0 4px; }
    @media (max-width: 600px) { #mobile-nav { display: flex; justify-content: space-around; } }
    .mobile-nav-btn { display: flex; flex-direction: column; align-items: center; gap: 3px; background: none; border: none; color: var(--text-muted); font-size: 0.65rem; cursor: pointer; padding: 4px 12px; }
    .mobile-nav-btn i { font-size: 1.2rem; }
    .mobile-nav-btn.active { color: var(--green); }

    /* GRADIENT BG */
    .gradient-green { background: linear-gradient(135deg, #1a4a2a 0%, #121212 60%); }
    .gradient-gold { background: linear-gradient(135deg, #3a2a00 0%, #121212 60%); }
    .gradient-blue { background: linear-gradient(135deg, #001a3a 0%, #121212 60%); }
    .gradient-purple { background: linear-gradient(135deg, #2a003a 0%, #121212 60%); }

    /* SLEEP TIMER */
    .dropdown { position: relative; }
    .dropdown-menu { position: absolute; bottom: 48px; right: 0; background: #282828; border-radius: 8px; min-width: 180px; box-shadow: 0 8px 32px rgba(0,0,0,0.5); z-index: 50; display: none; flex-direction: column; padding: 6px; }
    .dropdown-menu.open { display: flex; }
    .dropdown-item { padding: 10px 14px; border-radius: 4px; cursor: pointer; font-size: 0.85rem; color: var(--text-muted); }
    .dropdown-item:hover { background: #3a3a3a; color: #fff; }

    /* CROSSFADE */
    .page { display: none; }
    .page.active { display: block; }

    /* VIDEO MODAL — anchored to the top of the viewport (like major streaming
       platforms) instead of being vertically centered, so the player is
       immediately visible with details flowing below it. */
    #video-modal { position: fixed; inset: 0; background: #000; z-index: 150; display: none; flex-direction: column; align-items: stretch; overflow-y: auto; -webkit-overflow-scrolling: touch; }
    #video-modal.open { display: flex; }
    #video-modal-inner { width: 100%; max-width: 1100px; margin: 0 auto; position: relative; }
    #video-modal video, #video-modal iframe { width: 100%; aspect-ratio: 16/9; border: none; display: block; background: #000; }
    #video-modal-close { position: absolute; top: 12px; right: 12px; z-index: 2; width: 38px; height: 38px; border-radius: 50%; background: rgba(0,0,0,0.6); border: none; color: #fff; font-size: 1.1rem; cursor: pointer; display: flex; align-items: center; justify-content: center; }
    #video-modal-info { padding: 16px var(--page-pad-x) 40px; }
    @media (min-width: 700px) {
      #video-modal-inner { margin-top: 24px; border-radius: 12px; overflow: hidden; }
      #video-modal video, #video-modal iframe { border-radius: 0; }
    }

    /* PROFILE DROPDOWN */
    #profile-menu { position: absolute; top: 56px; right: 16px; background: #282828; border-radius: 8px; min-width: 200px; box-shadow: 0 8px 32px rgba(0,0,0,0.5); z-index: 80; display: none; flex-direction: column; padding: 6px; }
    #profile-menu.open { display: flex; }

  /* SCROLL FADE */
  #page-area { background: var(--bg-main); }
  .page-scroll { padding: 0 32px 80px; }
  @media (max-width: 600px) { .page-scroll { padding: 0 var(--page-pad-x) 80px; } }

  /* MINI PLAYER (mobile) */
  #mini-player { display: none; position: fixed; bottom: 60px; left: 8px; right: 8px; background: #282828; border-radius: 10px; padding: 10px 14px; z-index: 18; align-items: center; gap: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.6); cursor: pointer; }
  @media (max-width: 600px) { #mini-player { display: flex; } }
  #mini-player .mp-progress { position: absolute; bottom: 0; left: 0; height: 3px; background: var(--green); border-radius: 0 0 10px 10px; transition: width 0.3s; }

  /* FEATURED BANNER */
  .banner-slide { border-radius: 14px; overflow: hidden; position: relative; height: 200px; display: flex; align-items: flex-end; padding: 20px; cursor: pointer; flex-shrink: 0; width: 320px; transition: transform 0.2s; }
  .banner-slide:hover { transform: scale(1.02); }
  .banner-wrap { display: flex; gap: 16px; overflow-x: auto; padding-bottom: 8px; scrollbar-width: none; }
  .banner-wrap::-webkit-scrollbar { display: none; }

  /* STATS BAR */
  .stats-bar { display: flex; gap: 20px; padding: 12px 32px; background: #0a0a0a; border-bottom: 1px solid #1a1a1a; overflow-x: auto; }
  .stat-item { display: flex; align-items: center; gap: 8px; color: var(--text-muted); font-size: 0.78rem; white-space: nowrap; }
  .stat-item i { color: var(--green); }

  /* NOTIFICATION DOT */
  .notif-dot { width: 8px; height: 8px; background: var(--green); border-radius: 50%; position: absolute; top: 2px; right: 2px; }

  /* CONTEXT MENU */
  #context-menu { position: fixed; background: #282828; border-radius: 8px; min-width: 200px; box-shadow: 0 8px 32px rgba(0,0,0,0.5); z-index: 999; display: none; flex-direction: column; padding: 6px; }
  #context-menu.open { display: flex; }
  .context-item { padding: 10px 14px; border-radius: 4px; cursor: pointer; font-size: 0.85rem; color: var(--text-muted); display: flex; align-items: center; gap: 10px; }
  .context-item:hover { background: #3a3a3a; color: #fff; }
  </style>
</head>
<body>

<!-- Decoy login fields: Chrome's password manager has a saved admin.php credential
     for this origin and was offering it on the search box (the most prominent
     visible text field) since nothing on this page was explicitly marked as a
     username/password pair. These hidden, unfocusable fields give Chrome a
     correctly-labeled target instead, so the saved credential stops appearing
     as a fill suggestion on #search-input. -->
<input type="text" name="username" autocomplete="username" tabindex="-1" aria-hidden="true"
       style="position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0;"/>
<input type="password" name="password" autocomplete="current-password" tabindex="-1" aria-hidden="true"
       style="position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0;"/>

<!-- TOAST -->
<div id="toast"></div>

<!-- INSTALL APP BANNER -->
<div id="install-banner">
  <div class="ib-icon">🎵</div>
  <div class="ib-text">
    <div class="ib-title">Install Rhema Sounds</div>
    <div class="ib-sub" id="install-banner-sub">Add the app to your home screen for quick access &amp; offline playback.</div>
  </div>
  <button class="ib-btn" id="install-banner-btn" onclick="handleInstallClick()">Install</button>
  <button class="ib-close" onclick="dismissInstallBanner()" title="Dismiss"><i class="fas fa-times"></i></button>
</div>

<!-- CONTEXT MENU -->
<div id="context-menu">
  <div class="context-item" id="ctx-play" onclick="ctxPlay()"><i class="fas fa-play"></i> Play</div>
  <div class="context-item" id="ctx-next" onclick="ctxAddNext()"><i class="fas fa-step-forward"></i> Play Next</div>
  <div class="context-item" onclick="ctxAddToQueue()"><i class="fas fa-list-ul"></i> Add to Queue</div>
  <div class="context-item" onclick="ctxLike()"><i class="fas fa-heart"></i> <span id="ctx-like-label">Like</span></div>
  <div class="context-item" onclick="ctxAddToPlaylist()"><i class="fas fa-plus"></i> Add to Playlist</div>
  <div class="context-item" onclick="ctxDownloadLyrics()"><i class="fas fa-file-alt"></i> Download Lyrics</div>
  <div class="context-item" onclick="ctxShare()"><i class="fas fa-share"></i> Share</div>
  <div class="context-item" onclick="ctxGoToArtist()"><i class="fas fa-microphone"></i> Go to Artist</div>
</div>

<!-- MINI PLAYER (mobile) -->
<div id="mini-player" onclick="showPage('nowplaying',null)">
  <div id="mp-progress" class="mp-progress" style="width:0%;"></div>
  <div id="mini-thumb" style="font-size:1.4rem;">🎵</div>
  <div style="flex:1;overflow:hidden;">
    <div id="mini-title" style="font-weight:700;font-size:0.85rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">No track</div>
    <div id="mini-artist" style="font-size:0.72rem;color:var(--text-muted);">–</div>
  </div>
  <button onclick="event.stopPropagation();togglePlay()" style="background:none;border:none;color:#fff;font-size:1.2rem;cursor:pointer;width:36px;height:36px;display:flex;align-items:center;justify-content:center;">
    <i id="mini-play-icon" class="fas fa-play"></i>
  </button>
  <button onclick="event.stopPropagation();nextTrack()" style="background:none;border:none;color:#fff;font-size:1rem;cursor:pointer;width:32px;height:32px;display:flex;align-items:center;justify-content:center;">
    <i class="fas fa-step-forward"></i>
  </button>
</div>

<!-- MOBILE BOTTOM NAV -->
<div id="mobile-nav">
  <button class="mobile-nav-btn active" onclick="showPage('home',this)"><i class="fas fa-home"></i><span data-i18n="nav_home">Home</span></button>
  <button class="mobile-nav-btn" onclick="showPage('search',this)"><i class="fas fa-search"></i><span data-i18n="nav_search">Search</span></button>
  <button class="mobile-nav-btn" onclick="showPage('library',this)"><i class="fas fa-book-open"></i><span data-i18n="nav_library_short">Library</span></button>
  <button class="mobile-nav-btn" onclick="showPage('videos',this)"><i class="fas fa-film"></i><span data-i18n="nav_videos">Videos</span></button>
  <button class="mobile-nav-btn" onclick="showPage('live',this)"><span class="nav-icon-wrap"><i class="fas fa-broadcast-tower"></i><span class="live-nav-dot" id="live-nav-dot-mobile"></span></span><span data-i18n="nav_live_short">Live</span></button>
  <button class="mobile-nav-btn" onclick="showPage('offline',this)"><i class="fas fa-cloud-download-alt"></i><span data-i18n="nav_offline">Offline</span></button>
</div>

<!-- APP LAYOUT -->
<div id="app">

  <!-- SIDEBAR -->
  <div id="sidebar">
    <div class="sidebar-logo">
      <div style="width:36px;height:36px;background:var(--green);border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <i class="fas fa-music" style="color:#000;font-size:1rem;"></i>
      </div>
      <span class="logo-text">Rhema Sounds</span>
    </div>

    <div class="sidebar-nav">
      <div class="nav-item active" onclick="showPage('home',this)">
        <i class="fas fa-home"></i><span data-i18n="nav_home">Home</span>
      </div>
      <div class="nav-item" onclick="showPage('search',this)">
        <i class="fas fa-search"></i><span data-i18n="nav_search">Search</span>
      </div>
      <div class="nav-item" onclick="showPage('library',this)">
        <i class="fas fa-book-open"></i><span data-i18n="nav_library">Your Library</span>
      </div>
      <div class="nav-item" onclick="showPage('videos',this)">
        <i class="fas fa-film"></i><span data-i18n="nav_videos">Videos</span>
      </div>
      <div class="nav-item" onclick="showPage('live',this)">
        <span class="nav-icon-wrap"><i class="fas fa-broadcast-tower"></i><span class="live-nav-dot" id="live-nav-dot-sidebar"></span></span><span data-i18n="nav_live">Live Stream</span>
        <span class="live-badge" style="margin-left:auto;">LIVE</span>
      </div>
      <div class="nav-item" onclick="showPage('offline',this);renderOfflinePage();">
        <i class="fas fa-cloud-download-alt"></i><span data-i18n="nav_offline">Offline</span>
      </div>
      <div class="nav-item" onclick="showPage('artists',this);renderArtistsPage();">
        <i class="fas fa-microphone"></i><span data-i18n="nav_artists">Artists</span>
      </div>
    </div>

    <div class="sidebar-divider"></div>

    <div style="padding:8px 8px 4px;display:flex;align-items:center;justify-content:space-between;">
      <span class="sidebar-section-title" style="padding:0 12px;" data-i18n="sidebar_playlists">Playlists</span>
      <button onclick="openCreatePlaylist()" style="background:none;border:none;color:var(--text-muted);cursor:pointer;padding:4px 8px;font-size:0.85rem;" title="Create Playlist">
        <i class="fas fa-plus"></i>
      </button>
    </div>

    <div class="playlist-list" id="sidebar-playlists">
      <!-- Populated by JS -->
    </div>
  </div>

  <!-- MAIN CONTENT -->
  <div id="main-content">
    <!-- TOPBAR -->
    <div id="topbar">
      <button class="nav-btn" onclick="historyBack()"><i class="fas fa-chevron-left"></i></button>
      <button class="nav-btn" onclick="historyForward()"><i class="fas fa-chevron-right"></i></button>
      <div class="search-wrap">
        <i class="fas fa-search search-icon"></i>
        <input type="search" id="search-input" name="rhema-search" data-i18n-placeholder="search_placeholder" placeholder="What do you want to listen to?"
               oninput="doSearch(this.value)"
               onfocus="showPage('search',null);showSearchSuggestions()"
               onkeydown="onSearchKeydown(event)"
               onblur="hideSearchSuggestions()" autocomplete="off" autocapitalize="off" autocorrect="off" spellcheck="false"
               data-lpignore="true" data-1p-ignore data-form-type="other"/>
        <button class="search-clear-btn" id="search-clear-btn" onclick="clearSearch()" title="Clear search" style="display:none;"><i class="fas fa-times-circle"></i></button>
        <div id="search-suggestions"></div>
      </div>
      <div class="topbar-right" style="position:relative;">
        <button class="ctrl-btn" onclick="openSettings()" title="Settings"><i class="fas fa-cog"></i></button>
        <div class="avatar-btn" id="nav-avatar-btn" onclick="onAvatarBtnClick()" title="Account">
          <img id="nav-avatar-img" src="" style="display:none;"/>
          <i class="fas fa-user" id="nav-avatar-icon"></i>
        </div>
        <div id="profile-menu">
          <div class="dropdown-item" onclick="openProfile()"><i class="fas fa-user" style="margin-right:8px;"></i><span data-i18n="profile_profile">Profile</span></div>
          <div class="dropdown-item" onclick="showPage('library',null);closeProfileMenu()"><i class="fas fa-heart" style="margin-right:8px;"></i><span data-i18n="profile_liked_songs">Liked Songs</span></div>
          <a href="creator_dashboard.php" class="dropdown-item" style="display:flex;align-items:center;text-decoration:none;"><i class="fas fa-broadcast-tower" style="margin-right:8px;"></i><span>Creator Studio</span></a>
          <div style="height:1px;background:#444;margin:4px 0;"></div>
          <div class="dropdown-item" style="color:#e87;" onclick="logOutCurrentUser()"><i class="fas fa-sign-out-alt" style="margin-right:8px;"></i><span data-i18n="profile_logout">Log out</span></div>
        </div>
      </div>
    </div>

    <!-- TAB ROW (for album/playlist pages) -->
    <div class="tab-row" id="tab-row" style="display:none;">
      <button class="tab-btn active" onclick="switchTab('tracks',this)">Tracks</button>
      <button class="tab-btn" onclick="switchTab('about',this)">About</button>
    </div>

    <!-- PAGE AREA -->
    <div id="page-area">

      <!-- HOME PAGE -->
      <div id="page-home" class="page active">
        <div class="gradient-green" style="padding:32px var(--page-pad-x) 0;">
          <h1 style="font-size:2rem;font-weight:900;margin-bottom:4px;"><span data-i18n="greeting_prefix">Good</span> <span id="greeting-time">evening</span>, <span data-i18n="greeting_listener">Listener</span> 👋</h1>
          <p id="site-tagline" style="color:var(--text-muted);font-size:0.9rem;">Your daily dose of Rhema Sounds</p>
        </div>

        <!-- FEATURED BANNER -->
        <div style="padding:20px var(--page-pad-x) 0;">
          <div class="banner-wrap" id="banner-wrap"></div>
        </div>

        <!-- QUICK ACCESS -->
        <div style="padding:20px var(--page-pad-x) 0;">
          <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:8px;" id="quick-access">
            <!-- populated by JS -->
          </div>
        </div>

        <!-- FEATURED SECTION -->
        <div style="padding:28px var(--page-pad-x) 0;">
          <div class="section-title">
            <span data-i18n="home_featured_tracks">Featured Tracks</span>
            <span style="display:flex;align-items:center;gap:14px;">
              <span class="view-toggle-group" data-section="featured">
                <button class="view-toggle-btn active" data-view="card" onclick="setHomeSectionView('featured','card')" title="Card view"><i class="fas fa-th-large"></i></button>
                <button class="view-toggle-btn" data-view="list" onclick="setHomeSectionView('featured','list')" title="List view"><i class="fas fa-list"></i></button>
              </span>
              <span class="see-all" data-i18n="see_all" onclick="showPage('library',null)">See all</span>
            </span>
          </div>
          <div class="grid-cards" id="featured-grid"></div>
        </div>

        <!-- ARTISTS ROW -->
        <div style="padding:28px var(--page-pad-x) 0;">
          <div class="section-title"><span data-i18n="home_popular_artists">Popular Artists</span> <span class="see-all" data-i18n="see_all" onclick="showPage('artists',null);renderArtistsPage()">See all</span></div>
          <div style="display:flex;gap:20px;overflow-x:auto;padding-bottom:8px;" id="artists-row"></div>
        </div>

        <!-- RECENT -->
        <div style="padding:28px var(--page-pad-x) 80px;">
          <div class="section-title">
            <span data-i18n="home_gospel_collection">Gospel Collection</span>
            <span style="display:flex;align-items:center;gap:14px;">
              <span class="view-toggle-group" data-section="gospel">
                <button class="view-toggle-btn active" data-view="card" onclick="setHomeSectionView('gospel','card')" title="Card view"><i class="fas fa-th-large"></i></button>
                <button class="view-toggle-btn" data-view="list" onclick="setHomeSectionView('gospel','list')" title="List view"><i class="fas fa-list"></i></button>
              </span>
              <span class="see-all" data-i18n="see_all" onclick="showPage('library',null)">See all</span>
            </span>
          </div>
          <div class="grid-cards" id="recent-grid"></div>
        </div>
      </div>

      <!-- SEARCH PAGE -->
      <div id="page-search" class="page">
        <div style="padding:24px var(--page-pad-x) 0;">
          <h2 style="font-size:1.6rem;font-weight:800;margin-bottom:20px;" data-i18n="search_browse_genres">Browse Genres</h2>
          <div id="genre-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:16px;"></div>
        </div>
        <div style="padding:24px var(--page-pad-x) 80px;" id="search-results-section" style="display:none;">
          <div class="section-title" id="search-results-title">Search Results</div>
          <div id="search-results"></div>
        </div>
      </div>

      <!-- LIBRARY PAGE -->
      <div id="page-library" class="page">
        <div class="gradient-purple" style="padding:40px var(--page-pad-x) 24px;">
          <h1 style="font-size:2rem;font-weight:900;" data-i18n="nav_library">Your Library</h1>
          <p style="color:var(--text-muted);margin-top:6px;" data-i18n="library_subtitle">Your playlists, liked songs & more</p>
        </div>
        <div style="padding:0 var(--page-pad-x);">
          <div style="display:flex;gap:8px;padding:16px 0;flex-wrap:wrap;">
            <button class="btn-green" onclick="openCreatePlaylist()"><i class="fas fa-plus" style="margin-right:6px;"></i><span data-i18n="library_create_playlist">Create Playlist</span></button>
            <button class="btn-outline" onclick="showLikedSongs()"><i class="fas fa-heart" style="margin-right:6px;"></i><span data-i18n="profile_liked_songs">Liked Songs</span></button>
          </div>
          <div id="library-content"></div>
        </div>
      </div>

      <!-- VIDEOS PAGE -->
      <div id="page-videos" class="page">
        <div class="gradient-blue" style="padding:40px var(--page-pad-x) 24px;">
          <h1 style="font-size:2rem;font-weight:900;"><i class="fas fa-film" style="color:var(--green);margin-right:10px;"></i>Videos</h1>
          <p style="color:var(--text-muted);margin-top:6px;">Music videos & worship sessions</p>
        </div>
        <div class="page-scroll" style="padding-top:24px;">
          <div class="video-grid" id="video-grid"></div>
        </div>
      </div>

      <!-- LIVE PAGE -->
      <div id="page-live" class="page">
        <div style="background:linear-gradient(135deg,#3a0000 0%,#121212 60%);padding:40px var(--page-pad-x) 24px;">
          <h1 style="font-size:2rem;font-weight:900;"><span class="live-badge" style="font-size:0.75rem;vertical-align:middle;margin-right:10px;">LIVE</span>Live Stream</h1>
          <p style="color:var(--text-muted);margin-top:6px;">Watch & listen to Rhema Sounds live</p>
        </div>
        <div class="page-scroll" style="padding-top:24px;">
          <div id="live-content"></div>
        </div>
      </div>

      <!-- OFFLINE PAGE -->
      <div id="page-offline" class="page">
        <div class="gradient-purple" style="padding:40px var(--page-pad-x) 24px;">
          <h1 style="font-size:2rem;font-weight:900;"><i class="fas fa-cloud-download-alt" style="color:var(--green);margin-right:10px;"></i><span data-i18n="nav_offline">Offline</span></h1>
          <p style="color:var(--text-muted);margin-top:6px;">Tracks you've saved for offline listening, plus files from your device</p>
        </div>
        <div class="page-scroll" style="padding-top:16px;">
          <div id="offline-content"></div>
        </div>
      </div>

      <!-- PLAYLIST / ALBUM PAGE -->
      <div id="page-playlist" class="page">
        <div id="playlist-hero" class="playlist-hero" style="background:linear-gradient(180deg,#1a3a2a 0%,#121212 100%);">
          <div class="playlist-hero-img" id="playlist-hero-img">🎵</div>
          <div class="playlist-hero-info">
            <div style="font-size:0.78rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.1em;">Playlist</div>
            <h1 id="playlist-hero-title" style="font-size:2.5rem;font-weight:900;"></h1>
            <p id="playlist-hero-desc" class="meta"></p>
            <p id="playlist-hero-meta" class="meta" style="margin-top:4px;"></p>
            <div style="display:flex;gap:12px;margin-top:20px;flex-wrap:wrap;">
              <button class="btn-green" style="font-size:1rem;padding:12px 32px;" onclick="playPlaylist()"><i class="fas fa-play" style="margin-right:8px;"></i>Play</button>
              <button class="btn-outline" onclick="shufflePlaylist()"><i class="fas fa-random" style="margin-right:8px;"></i>Shuffle</button>
              <button class="btn-outline" onclick="downloadLyrics()"><i class="fas fa-download" style="margin-right:8px;"></i>Lyrics</button>
              <button class="btn-outline" onclick="sharePlaylist()"><i class="fas fa-share" style="margin-right:8px;"></i>Share</button>
            </div>
          </div>
        </div>
        <div class="page-scroll" style="padding-top:16px;">
          <div id="playlist-tracks"></div>
        </div>
      </div>

      <!-- ARTISTS PAGE -->
      <div id="page-artists" class="page">
        <div class="gradient-green" style="padding:40px var(--page-pad-x) 24px;">
          <h1 style="font-size:2rem;font-weight:900;"><i class="fas fa-microphone" style="color:var(--green);margin-right:10px;"></i>Artists</h1>
          <p style="color:var(--text-muted);margin-top:6px;">All Rhema Sounds artists</p>
        </div>
        <div class="page-scroll" style="padding-top:24px;" id="artists-page-content"></div>
      </div>

      <!-- CREATOR CHANNEL PAGE -->
      <div id="page-channel" class="page">
        <div id="channel-hero" class="playlist-hero" style="background:linear-gradient(180deg,#1a3a2a 0%,#121212 100%);">
          <div class="playlist-hero-img" id="channel-hero-img" style="border-radius:50%;position:relative;overflow:hidden;">👤</div>
          <div class="playlist-hero-info">
            <div style="font-size:0.78rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.1em;">Channel</div>
            <h1 id="channel-name" style="font-size:2.5rem;font-weight:900;"></h1>
            <p id="channel-bio" class="meta"></p>
            <p id="channel-meta" class="meta" style="margin-top:4px;"></p>
            <div id="channel-actions" style="display:flex;gap:12px;margin-top:20px;flex-wrap:wrap;">
              <button class="btn-green" style="font-size:1rem;padding:12px 32px;" onclick="playPlaylist()"><i class="fas fa-play" style="margin-right:8px;"></i>Play</button>
              <button class="btn-outline" onclick="shufflePlaylist()"><i class="fas fa-random" style="margin-right:8px;"></i>Shuffle</button>
            </div>
          </div>
        </div>
        <div class="page-scroll" style="padding-top:24px;">
          <div id="channel-live-banner"></div>
          <div id="channel-videos-section" style="margin-bottom:32px;display:none;">
            <div class="section-title">Videos</div>
            <div class="video-grid" id="channel-videos"></div>
          </div>
          <div id="channel-tracks-section" style="display:none;">
            <div class="section-title">Tracks</div>
            <div id="channel-tracks"></div>
          </div>
          <div id="channel-empty" style="display:none;text-align:center;padding:60px 20px;color:var(--text-muted);">
            <i class="fas fa-user-circle" style="font-size:3rem;margin-bottom:16px;display:block;color:#333;"></i>
            <p>This channel hasn't published any content yet.</p>
          </div>
        </div>
      </div>

      <!-- NOW PLAYING PAGE -->
      <div id="page-nowplaying" class="page">
        <!-- DESKTOP / TABLET LAYOUT -->
        <div class="np-desktop" style="padding:40px 32px;max-width:700px;margin:0 auto;">
          <div style="text-align:center;margin-bottom:32px;">
            <div id="np-album-art" style="width:280px;height:280px;border-radius:16px;background:#333;margin:0 auto 24px;display:flex;align-items:center;justify-content:center;font-size:6rem;box-shadow:0 16px 64px rgba(0,0,0,0.5);overflow:hidden;">🎵</div>
            <h2 id="np-title" style="font-size:1.6rem;font-weight:900;"></h2>
            <p id="np-artist" style="color:var(--text-muted);margin-top:6px;"></p>
          </div>
          <div style="margin-bottom:24px;">
            <div class="section-title" style="font-size:1rem;">Lyrics</div>
            <div class="lyrics-box" id="lyrics-display"></div>
            <button class="btn-outline" onclick="downloadLyrics()" style="margin-top:12px;font-size:0.82rem;padding:8px 18px;">
              <i class="fas fa-download" style="margin-right:6px;"></i>Download Lyrics
            </button>
          </div>
          <div>
            <div class="section-title" style="font-size:1rem;">Up Next</div>
            <div id="up-next-list"></div>
          </div>
        </div>

        <!-- MOBILE LAYOUT: swipe left to reveal Lyrics -->
        <div class="np-mobile">
          <div class="np-mobile-tabs">
            <span class="np-tab active" id="np-tab-song" onclick="npGoToTab(0)">Song</span>
            <span class="np-tab-sep">|</span>
            <span class="np-tab" id="np-tab-lyrics" onclick="npGoToTab(1)">Lyrics</span>
          </div>
          <div class="np-swipe" id="np-swipe" onscroll="onNpSwipeScroll()">
            <!-- SONG PANEL -->
            <div class="np-panel">
              <div class="np-art-wrap"><div class="np-art" id="npm-album-art">🎵</div></div>
              <h2 class="np-track-title" id="npm-title"></h2>
              <p class="np-track-artist" id="npm-artist"></p>
              <div class="np-action-row">
                <button class="np-action-btn heart-btn" id="npm-heart" onclick="toggleLikeCurrentTrack()" title="Like"><i class="fas fa-heart"></i></button>
                <button class="np-action-btn" onclick="openAddToPlaylist(state.currentTrack && state.currentTrack.id)" title="Add to playlist"><i class="fas fa-list-ul"></i></button>
                <button class="np-action-btn" onclick="openSleepTimerModal()" title="Sleep timer"><i class="fas fa-moon"></i></button>
                <button class="np-action-btn" onclick="state.currentTrack && shareTrack(state.currentTrack.id)" title="Share"><i class="fas fa-share-alt"></i></button>
                <button class="np-action-btn np-offline-btn" id="npm-offline-btn" onclick="downloadCurrentTrackOffline()" title="Download for offline"><i class="fas fa-download"></i></button>
              </div>
              <div class="progress-row np-progress-row">
                <span class="time-label" id="npm-current-time">0:00</span>
                <div class="progress-bar" id="npm-progress-bar" onclick="seekTrackNP(event)">
                  <div class="progress-fill" id="npm-progress-fill" style="width:0%;"><div class="progress-thumb"></div></div>
                </div>
                <span class="time-label" id="npm-total-time">0:00</span>
              </div>
              <div class="player-controls np-controls">
                <button class="ctrl-btn" id="npm-btn-shuffle" onclick="toggleShuffle()" title="Shuffle"><i class="fas fa-random"></i></button>
                <button class="ctrl-btn" onclick="prevTrack()" title="Previous"><i class="fas fa-step-backward"></i></button>
                <button class="play-btn np-play-btn" onclick="togglePlay()"><i class="fas fa-play" id="npm-play-icon"></i></button>
                <button class="ctrl-btn" onclick="nextTrack()" title="Next"><i class="fas fa-step-forward"></i></button>
                <button class="ctrl-btn" id="npm-btn-repeat" onclick="toggleRepeat()" title="Repeat"><i class="fas fa-redo"></i></button>
              </div>
              <div class="np-speed-row" id="npm-speed-row">
                <button class="np-speed-btn" data-rate="0.75" onclick="setPlaybackRate(0.75)">0.75x</button>
                <button class="np-speed-btn" data-rate="1" onclick="setPlaybackRate(1)">1.0x</button>
                <button class="np-speed-btn" data-rate="1.25" onclick="setPlaybackRate(1.25)">1.25x</button>
                <button class="np-speed-btn" data-rate="1.5" onclick="setPlaybackRate(1.5)">1.5x</button>
                <button class="np-speed-btn" data-rate="2" onclick="setPlaybackRate(2)">2.0x</button>
              </div>
            </div>
            <!-- LYRICS PANEL -->
            <div class="np-panel np-panel-lyrics">
              <h2 class="np-track-title" id="npm-lyrics-title"></h2>
              <p class="np-track-artist" id="npm-lyrics-artist"></p>
              <div class="lyrics-box" id="npm-lyrics-display" style="margin-top:20px;background:transparent;padding:0;max-height:none;"></div>
              <button class="btn-outline" onclick="downloadLyrics()" style="margin-top:16px;font-size:0.82rem;padding:8px 18px;">
                <i class="fas fa-download" style="margin-right:6px;"></i>Download Lyrics
              </button>
            </div>
          </div>
        </div>
      </div>

    </div><!-- /page-area -->
  </div><!-- /main-content -->

  <!-- QUEUE PANEL -->
  <div id="queue-panel">
    <div style="padding:20px 16px 12px;font-size:1rem;font-weight:800;display:flex;align-items:center;justify-content:space-between;">
      Queue <button onclick="toggleQueue()" style="background:none;border:none;color:var(--text-muted);cursor:pointer;font-size:1.1rem;"><i class="fas fa-times"></i></button>
    </div>
    <div style="padding:0 8px;font-size:0.78rem;color:var(--text-muted);padding-left:16px;margin-bottom:6px;">Now playing</div>
    <div id="queue-now" style="padding:0 8px;"></div>
    <div style="padding:12px 16px 6px;font-size:0.78rem;color:var(--text-muted);">Next in queue</div>
    <div id="queue-list" style="padding:0 8px;overflow-y:auto;flex:1;"></div>
  </div>

</div><!-- /app -->

<!-- PLAYER BAR -->
<div id="player-bar">
  <!-- LEFT -->
  <div class="player-left">
    <div class="player-thumb" id="player-thumb" style="display:flex;align-items:center;justify-content:center;font-size:1.4rem;cursor:pointer;" onclick="showPage('nowplaying',null)">🎵</div>
    <div class="player-info">
      <div class="player-title" id="player-title">No track selected</div>
      <div class="player-artist" id="player-artist">–</div>
    </div>
    <button class="heart-btn" id="player-heart" onclick="toggleLikeCurrentTrack()" title="Like"><i class="fas fa-heart"></i></button>
  </div>

  <!-- CENTER -->
  <div class="player-center">
    <div class="player-controls">
      <button class="ctrl-btn" id="btn-shuffle" onclick="toggleShuffle()" title="Shuffle"><i class="fas fa-random"></i></button>
      <button class="ctrl-btn" onclick="prevTrack()" title="Previous"><i class="fas fa-step-backward"></i></button>
      <button class="play-btn" id="main-play-btn" onclick="togglePlay()"><i class="fas fa-play" id="play-icon"></i></button>
      <button class="ctrl-btn" onclick="nextTrack()" title="Next"><i class="fas fa-step-forward"></i></button>
      <button class="ctrl-btn" id="btn-repeat" onclick="toggleRepeat()" title="Repeat"><i class="fas fa-redo"></i></button>
    </div>
    <div class="progress-row">
      <span class="time-label" id="current-time">0:00</span>
      <div class="progress-bar" id="progress-bar" onclick="seekTrack(event)">
        <div class="progress-fill" id="progress-fill" style="width:0%;">
          <div class="progress-thumb"></div>
        </div>
      </div>
      <span class="time-label" id="total-time">0:00</span>
    </div>
  </div>

  <!-- RIGHT -->
  <div class="player-right">
    <button class="ctrl-btn" onclick="showPage('nowplaying',null)" title="Now Playing"><i class="fas fa-music"></i></button>
    <button class="ctrl-btn" onclick="downloadCurrentLyrics()" title="Download Lyrics"><i class="fas fa-file-alt"></i></button>
    <button class="ctrl-btn" onclick="toggleQueue()" title="Queue"><i class="fas fa-list-ul"></i></button>
    <i class="fas fa-volume-up ctrl-btn" onclick="toggleMute()" id="vol-icon"></i>
    <input type="range" class="vol-slider" id="vol-slider" min="0" max="100" value="80" oninput="setVolume(this.value)"/>
    <div class="dropdown">
      <button class="ctrl-btn" onclick="toggleSleepMenu()" title="Sleep Timer"><i class="fas fa-moon"></i></button>
      <div class="dropdown-menu" id="sleep-menu">
        <div class="dropdown-item" onclick="setSleep(5)">Sleep in 5 min</div>
        <div class="dropdown-item" onclick="setSleep(15)">Sleep in 15 min</div>
        <div class="dropdown-item" onclick="setSleep(30)">Sleep in 30 min</div>
        <div class="dropdown-item" onclick="setSleep(60)">Sleep in 1 hour</div>
        <div class="dropdown-item" onclick="setSleep(0)">Turn off</div>
      </div>
    </div>
  </div>
</div>

<!-- MODALS -->

<!-- CREATE PLAYLIST MODAL -->
<div class="modal-overlay" id="modal-create-playlist">
  <div class="modal-box">
    <button class="modal-close" onclick="closeModal('modal-create-playlist')">×</button>
    <div class="modal-title">Create Playlist</div>
    <div style="display:flex;flex-direction:column;gap:12px;">
      <label style="font-size:0.85rem;color:var(--text-muted);">Playlist Name</label>
      <input type="text" class="form-input" id="new-playlist-name" placeholder="My Playlist #1"/>
      <label style="font-size:0.85rem;color:var(--text-muted);">Description (optional)</label>
      <input type="text" class="form-input" id="new-playlist-desc" placeholder="Add an optional description"/>
      <button class="btn-green" onclick="createPlaylist()">Create</button>
    </div>
  </div>
</div>

<!-- ADD TO PLAYLIST MODAL -->
<div class="modal-overlay" id="modal-add-to-playlist">
  <div class="modal-box">
    <button class="modal-close" onclick="closeModal('modal-add-to-playlist')">×</button>
    <div class="modal-title">Add to Playlist</div>
    <div id="add-to-playlist-list" style="display:flex;flex-direction:column;gap:8px;"></div>
  </div>
</div>

<!-- SHARE MODAL -->
<div class="modal-overlay" id="modal-share">
  <div class="modal-box">
    <button class="modal-close" onclick="closeModal('modal-share')">×</button>
    <div class="modal-title">Share</div>
    <div style="display:flex;flex-direction:column;gap:12px;">
      <div style="background:#1a1a1a;border-radius:8px;padding:12px;font-size:0.85rem;color:var(--text-muted);word-break:break-all;" id="share-url"></div>
      <button class="btn-green" onclick="copyShareUrl()"><i class="fas fa-copy" style="margin-right:8px;"></i>Copy Link</button>
      <div style="display:flex;gap:10px;justify-content:center;margin-top:8px;">
        <button class="btn-outline" style="border-radius:50%;width:44px;height:44px;padding:0;display:flex;align-items:center;justify-content:center;"><i class="fab fa-whatsapp"></i></button>
        <button class="btn-outline" style="border-radius:50%;width:44px;height:44px;padding:0;display:flex;align-items:center;justify-content:center;"><i class="fab fa-twitter"></i></button>
        <button class="btn-outline" style="border-radius:50%;width:44px;height:44px;padding:0;display:flex;align-items:center;justify-content:center;"><i class="fab fa-facebook"></i></button>
        <button class="btn-outline" style="border-radius:50%;width:44px;height:44px;padding:0;display:flex;align-items:center;justify-content:center;"><i class="fas fa-envelope"></i></button>
      </div>
    </div>
  </div>
</div>

<!-- SLEEP TIMER MODAL (mobile-friendly; works regardless of viewport) -->
<div class="modal-overlay" id="modal-sleep-timer">
  <div class="modal-box" style="max-width:340px;">
    <button class="modal-close" onclick="closeModal('modal-sleep-timer')">×</button>
    <div class="modal-title"><i class="fas fa-moon" style="margin-right:8px;"></i>Sleep Timer</div>
    <div style="display:flex;flex-direction:column;gap:8px;">
      <div class="dropdown-item" style="padding:12px;border-radius:8px;background:#1a1a1a;cursor:pointer;" onclick="setSleep(5)">Sleep in 5 min</div>
      <div class="dropdown-item" style="padding:12px;border-radius:8px;background:#1a1a1a;cursor:pointer;" onclick="setSleep(15)">Sleep in 15 min</div>
      <div class="dropdown-item" style="padding:12px;border-radius:8px;background:#1a1a1a;cursor:pointer;" onclick="setSleep(30)">Sleep in 30 min</div>
      <div class="dropdown-item" style="padding:12px;border-radius:8px;background:#1a1a1a;cursor:pointer;" onclick="setSleep(60)">Sleep in 1 hour</div>
      <div class="dropdown-item" style="padding:12px;border-radius:8px;background:#1a1a1a;cursor:pointer;color:var(--text-muted);" onclick="setSleep(0)">Turn off</div>
    </div>
  </div>
</div>

<!-- SETTINGS MODAL -->
<div class="modal-overlay" id="modal-settings">
  <div class="modal-box" style="max-width:540px;">
    <button class="modal-close" onclick="closeModal('modal-settings')">×</button>
    <div class="modal-title" data-i18n="settings_title">Settings</div>
    <div style="display:flex;flex-direction:column;gap:16px;">
      <div style="display:flex;align-items:center;justify-content:space-between;">
        <div><div style="font-weight:600;" data-i18n="settings_crossfade">Crossfade</div><div style="font-size:0.78rem;color:var(--text-muted);" data-i18n="settings_crossfade_desc">Smooth transition between songs</div></div>
        <label class="switch"><input type="checkbox" id="setting-crossfade" onchange="updateSetting('crossfade',this.checked)"><span class="slider-sw"></span></label>
      </div>
      <div style="display:flex;align-items:center;justify-content:space-between;">
        <div><div style="font-weight:600;" data-i18n="settings_autoplay">Autoplay</div><div style="font-size:0.78rem;color:var(--text-muted);" data-i18n="settings_autoplay_desc">Keep playing similar music when queue ends</div></div>
        <label class="switch"><input type="checkbox" id="setting-autoplay" checked onchange="updateSetting('autoplay',this.checked)"><span class="slider-sw"></span></label>
      </div>
      <div style="display:flex;align-items:center;justify-content:space-between;">
        <div><div style="font-weight:600;" data-i18n="settings_lyrics">Show lyrics</div><div style="font-size:0.78rem;color:var(--text-muted);" data-i18n="settings_lyrics_desc">Display lyrics in now playing</div></div>
        <label class="switch"><input type="checkbox" id="setting-lyrics" checked onchange="updateSetting('lyrics',this.checked)"><span class="slider-sw"></span></label>
      </div>
      <div>
        <div style="font-weight:600;margin-bottom:8px;" data-i18n="settings_quality">Audio quality</div>
        <select class="form-input" style="width:auto;cursor:pointer;" onchange="updateSetting('quality',this.value)">
          <option value="low">Low (24 kbps)</option>
          <option value="normal">Normal (96 kbps)</option>
          <option value="high" selected>High (160 kbps)</option>
          <option value="very_high">Very High (320 kbps)</option>
        </select>
      </div>
      <div>
        <div style="font-weight:600;margin-bottom:8px;" data-i18n="settings_language">Language</div>
        <select class="form-input" style="width:auto;cursor:pointer;" id="setting-language" onchange="applyLanguage(this.value)">
          <option value="en">English</option>
          <option value="fr">Français (French)</option>
          <option value="es">Español (Spanish)</option>
          <option value="pt">Português (Portuguese)</option>
          <option value="de">Deutsch (German)</option>
          <option value="it">Italiano (Italian)</option>
          <option value="nl">Nederlands (Dutch)</option>
          <option value="pl">Polski (Polish)</option>
          <option value="ru">Русский (Russian)</option>
          <option value="tr">Türkçe (Turkish)</option>
          <option value="ar">العربية (Arabic)</option>
          <option value="ur">اردو (Urdu)</option>
          <option value="hi">हिन्दी (Hindi)</option>
          <option value="bn">বাংলা (Bengali)</option>
          <option value="zh">中文 (Chinese)</option>
          <option value="ja">日本語 (Japanese)</option>
          <option value="ko">한국어 (Korean)</option>
          <option value="vi">Tiếng Việt (Vietnamese)</option>
          <option value="id">Bahasa Indonesia (Indonesian)</option>
          <option value="yo">Yorùbá (Yoruba)</option>
          <option value="ig">Igbo</option>
          <option value="ha">Hausa</option>
          <option value="sw">Kiswahili (Swahili)</option>
          <option value="am">አማርኛ (Amharic)</option>
          <option value="zu">isiZulu (Zulu)</option>
          <option value="af">Afrikaans</option>
        </select>
      </div>
    </div>
  </div>
</div>

<!-- AUTH MODAL (Login / Sign up) -->
<div class="modal-overlay" id="modal-auth">
  <div class="modal-box" style="max-width:380px;">
    <button class="modal-close" onclick="closeModal('modal-auth')">×</button>
    <div class="modal-title" id="auth-modal-title">Log in</div>
    <div style="display:flex;flex-direction:column;gap:12px;">
      <div id="auth-google-btn" style="display:flex;justify-content:center;"></div>
      <div style="display:flex;align-items:center;gap:10px;color:var(--text-muted);font-size:0.78rem;">
        <div style="flex:1;height:1px;background:#444;"></div>OR<div style="flex:1;height:1px;background:#444;"></div>
      </div>
      <div id="auth-name-field" style="display:none;flex-direction:column;gap:6px;">
        <label style="font-size:0.85rem;color:var(--text-muted);">Name</label>
        <input type="text" class="form-input" id="auth-name" placeholder="Your name"/>
      </div>
      <div style="display:flex;flex-direction:column;gap:6px;">
        <label style="font-size:0.85rem;color:var(--text-muted);">Email</label>
        <input type="email" class="form-input" id="auth-email" placeholder="you@example.com"/>
      </div>
      <div style="display:flex;flex-direction:column;gap:6px;">
        <label style="font-size:0.85rem;color:var(--text-muted);">Password</label>
        <input type="password" class="form-input" id="auth-password" placeholder="••••••••"/>
      </div>
      <div id="auth-error" style="color:#e87;font-size:0.8rem;display:none;"></div>
      <button class="btn-green" id="auth-submit-btn" onclick="submitAuthForm()">Log in</button>
      <div style="text-align:center;font-size:0.85rem;color:var(--text-muted);">
        <span id="auth-toggle-text">Don't have an account?</span>
        <a href="#" onclick="toggleAuthMode();return false;" style="color:var(--green);font-weight:600;" id="auth-toggle-link">Sign up</a>
      </div>
    </div>
  </div>
</div>

<!-- PROFILE MODAL -->
<div class="modal-overlay" id="modal-profile">
  <div class="modal-box" style="max-width:420px;">
    <button class="modal-close" onclick="closeModal('modal-profile')">×</button>
    <div class="modal-title">Your Profile</div>
    <div style="display:flex;flex-direction:column;align-items:center;gap:14px;">
      <div style="position:relative;">
        <img id="profile-avatar-img" src="" alt="Avatar" style="width:96px;height:96px;border-radius:50%;object-fit:cover;background:#3a3a3a;"/>
        <button onclick="document.getElementById('profile-avatar-input').click()" title="Change photo"
                style="position:absolute;bottom:0;right:0;width:32px;height:32px;border-radius:50%;background:var(--green);border:2px solid #282828;color:#000;display:flex;align-items:center;justify-content:center;cursor:pointer;">
          <i class="fas fa-camera" style="font-size:0.8rem;"></i>
        </button>
        <input type="file" id="profile-avatar-input" accept="image/*" style="display:none;" onchange="onProfileAvatarSelected(event)"/>
      </div>
      <div style="width:100%;display:flex;flex-direction:column;gap:6px;">
        <label style="font-size:0.85rem;color:var(--text-muted);">Name</label>
        <input type="text" class="form-input" id="profile-name-input"/>
      </div>
      <div style="width:100%;display:flex;flex-direction:column;gap:6px;">
        <label style="font-size:0.85rem;color:var(--text-muted);">Email</label>
        <input type="text" class="form-input" id="profile-email-input" disabled style="opacity:0.6;cursor:not-allowed;"/>
      </div>
      <button class="btn-green" style="width:100%;" onclick="saveProfile()">Save changes</button>
      <button class="btn-outline" style="width:100%;color:#e87;border-color:#e87;" onclick="logOutCurrentUser()">
        <i class="fas fa-sign-out-alt" style="margin-right:8px;"></i>Log out
      </button>
    </div>
  </div>
</div>

<!-- VIDEO MODAL — player pinned to the top of the screen (YouTube-style), with
     details flowing below so the layout stays natural on desktop & mobile -->
<div id="video-modal">
  <div id="video-modal-inner">
    <button id="video-modal-close" onclick="closeVideoModal()" title="Close"><i class="fas fa-times"></i></button>
    <div id="video-modal-content"></div>
    <div id="video-modal-info">
      <div id="video-modal-title" style="font-size:1.15rem;font-weight:700;"></div>
      <div id="video-modal-meta" style="font-size:0.82rem;color:var(--text-muted);margin-top:6px;"></div>
    </div>
  </div>
</div>

<style>
.switch { position: relative; display: inline-block; width: 44px; height: 24px; }
.switch input { opacity: 0; width: 0; height: 0; }
.slider-sw { position: absolute; cursor: pointer; inset: 0; background: #535353; border-radius: 24px; transition: 0.3s; }
.slider-sw:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background: white; border-radius: 50%; transition: 0.3s; }
input:checked + .slider-sw { background: var(--green); }
input:checked + .slider-sw:before { transform: translateX(20px); }
</style>

<!-- HIDDEN AUDIO ELEMENT -->
<audio id="main-audio" preload="auto"></audio>

<script>
// ============================================================
//  RHEMA SOUNDS - FULL SPOTIFY-LIKE STREAMING SITE
// ============================================================

// ---- DATA (loaded dynamically from API) ----
let tracks = [];
let artists = [];
let liveStreams = [];

// ---- STATIC ARTISTS (auto-rebuilt from API data after fetch) ----
const _colPalette = ['#1a3a2a','#001a3a','#3a2a00','#2a003a','#3a0a00','#1a2a3a','#3a001a','#1a3a1a','#2a1a00','#003a2a','#1a001a','#002a3a'];
const _emojis     = ['🎵','🎸','🎶','🌊','🙌','✨','💛','⚔️','📖','👑','🏗️','❤️','🎤','🎺','🎷','🥁'];

function buildArtistsFromTracks() {
  const map = {};
  tracks.forEach(t => {
    if (!map[t.artist]) {
      const idx = Object.keys(map).length;
      map[t.artist] = {
        name: t.artist,
        genre: t.genre || 'Gospel',
        emoji: _emojis[idx % _emojis.length],
        color: t.color || _colPalette[idx % _colPalette.length],
        avatar: '',
        creatorId: null,
        tracks: []
      };
    }
    if (!map[t.artist].avatar && t.creator_avatar) {
      map[t.artist].avatar = t.creator_avatar;
    }
    if (!map[t.artist].creatorId && t.creator_id) {
      map[t.artist].creatorId = t.creator_id;
    }
    map[t.artist].tracks.push(t.id);
  });
  artists = Object.values(map);
}

// Renders an artist's circular avatar: a profile photo if the creator has
// uploaded one, falling back to the generated emoji/color circle otherwise.
function artistAvatarHtml(a, size, fontSize, marginBottom, shadow) {
  const mb = marginBottom || 0;
  const shadowCss = shadow ? 'box-shadow:0 4px 16px rgba(0,0,0,0.4);' : '';
  const imgPart = a.avatar
    ? `<img src="${a.avatar}${size > 160 ? '' : '&size=md'}" alt="" loading="lazy" decoding="async" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;border-radius:50%;" onerror="this.style.display='none'">`
    : '';
  return `<div style="position:relative;overflow:hidden;width:${size}px;height:${size}px;border-radius:50%;background:${a.color};margin:0 auto ${mb}px;display:flex;align-items:center;justify-content:center;font-size:${fontSize};${shadowCss}">${a.emoji}${imgPart}</div>`;
}



// videos, genres, featuredBanners, broadcastSchedule, siteSettings,
// liveStreams and state.playlists are all populated from the API in fetchData()
let videos = [];
let genres = [];
let featuredBanners = [];
let broadcastSchedule = [];
let siteSettings = {};

// ---- STATE ----
let state = {
  currentTrack: null,
  currentIndex: -1,
  isPlaying: false,
  queue: [],
  originalQueue: [],
  shuffle: false,
  repeat: 'off', // off | one | all
  likedTracks: new Set(),
  playlists: [], // populated from API in fetchData()
  currentPlaylist: null,
  volume: 0.8,
  muted: false,
  sleepTimer: null,
  playbackRate: 1,
  settings: { crossfade:false, autoplay:true, lyrics:true, quality:'high', language:'en' },
  greetingPeriod: 'evening',
  pageHistory: ['home'],
  historyIndex: 0,
  addToPlaylistTrackId: null,
  currentUser: null,
  userPlaylists: [],
  authMode: 'login',
  localFiles: [], // audio files picked from this device (session only)
  homeViews: { featured: 'card', gospel: 'card' }, // 'card' | 'list' per home section
  queueSource: 'stream', // 'stream' | 'offline' | 'local' — where the current queue's audio comes from
};

const audio = document.getElementById('main-audio');

// ---- FETCH DATA FROM BACKEND ----
async function fetchData() {
  try {
    const [tracksRes, liveRes, videosRes, genresRes, bannersRes, playlistsRes, scheduleRes, settingsRes] = await Promise.all([
      fetch('api.php?action=tracks'),
      fetch('api.php?action=livestreams'),
      fetch('api.php?action=videos'),
      fetch('api.php?action=genres'),
      fetch('api.php?action=banners'),
      fetch('api.php?action=playlists'),
      fetch('api.php?action=schedule'),
      fetch('api.php?action=settings'),
    ]);
    if (tracksRes.ok) {
      const d = await tracksRes.json();
      tracks = d.tracks || [];
    }
    if (liveRes.ok) {
      const d = await liveRes.json();
      liveStreams = d.live_streams || [];
    }
    updateLiveNavDot();
    if (videosRes.ok) {
      const d = await videosRes.json();
      videos = (d.videos || []).map(v => ({ ...v, desc: v.description }));
    }
    if (genresRes.ok) {
      const d = await genresRes.json();
      genres = d.genres || [];
    }
    if (bannersRes.ok) {
      const d = await bannersRes.json();
      featuredBanners = d.banners || [];
    }
    if (playlistsRes.ok) {
      const d = await playlistsRes.json();
      state.playlists = (d.playlists || []).map(pl => ({
        id: pl.id, name: pl.name, desc: pl.description, tracks: pl.tracks || [],
        emoji: pl.emoji, isLiked: !!pl.isLiked,
      }));
    }
    if (scheduleRes.ok) {
      const d = await scheduleRes.json();
      broadcastSchedule = d.schedule || [];
    }
    if (settingsRes.ok) {
      const d = await settingsRes.json();
      siteSettings = d.settings || {};
      applySiteSettings();
    }
    buildArtistsFromTracks();
    state.queue         = [...tracks];
    state.originalQueue = [...tracks];

    // Sync the "Liked Songs" playlist with the listener's locally-stored likes
    const likedPl = state.playlists.find(p => p.isLiked);
    if (likedPl) likedPl.tracks = [...state.likedTracks];
  } catch (e) {
    console.warn('Could not reach backend API:', e);
    tracks = []; liveStreams = []; videos = []; genres = [];
    featuredBanners = []; broadcastSchedule = [];
    updateLiveNavDot();
  }
}

// ---- Apply site branding/settings loaded from the database ----
function applySiteSettings() {
  if (siteSettings.site_title) document.title = siteSettings.site_title;
  if (siteSettings.site_name) {
    document.querySelectorAll('.logo-text').forEach(el => el.textContent = siteSettings.site_name);
  }
  if (siteSettings.site_tagline) {
    const tagline = document.getElementById('site-tagline');
    if (tagline) tagline.textContent = siteSettings.site_tagline;
  }
}

// ---- INIT ----
async function init() {
  try { history.replaceState({ rhemaPage: 'home' }, '', '#home'); } catch (e) {}

  setGreeting();
  loadSavedLanguage();
  loadHomeViewPrefs();

  // Show skeleton / loading hint
  document.getElementById('featured-grid').innerHTML =
    '<p style="color:var(--text-muted);padding:20px;font-size:.9rem;"><i class="fas fa-spinner fa-spin" style="margin-right:8px;color:var(--green);"></i>Loading library…</p>';

  await fetchData();
  await loadCurrentUser();

  renderSidebarPlaylists();
  renderHomePage();
  renderSearchPage();
  renderLibraryPage();
  renderVideoPage();
  renderLivePage();
  refreshOfflineIdsCache();
  renderOfflinePage();
  updateQueuePanel();
  updatePlaybackSpeedUI();
  audio.volume = state.volume;
  audio.playbackRate = state.playbackRate;

  audio.addEventListener('timeupdate', updateProgress);
  audio.addEventListener('ended', onTrackEnded);
  audio.addEventListener('loadedmetadata', () => {
    document.getElementById('total-time').textContent = formatTime(audio.duration);
  });
  audio.addEventListener('error', () => {
    if (state.currentTrack) showToast('Audio error — check your upload.');
  });

  // Handle deep-link ?track=N
  const params = new URLSearchParams(window.location.search);
  if (params.get('track')) {
    const tid = parseInt(params.get('track'));
    if (tracks.find(t => t.id === tid)) playTrack(tid);
  }

  // Close dropdowns on outside click
  document.addEventListener('click', e => {
    if (!e.target.closest('#profile-menu') && !e.target.closest('.avatar-btn'))
      document.getElementById('profile-menu').classList.remove('open');
    if (!e.target.closest('#sleep-menu') && !e.target.closest('[onclick="toggleSleepMenu()"]'))
      document.getElementById('sleep-menu').classList.remove('open');
  });

  guardAgainstAutofilledSearch();
}

// Some browsers auto-fill the page's first text-like field (the search box)
// with a saved username/password and focus it on load — which both shows
// the wrong text in the search box and flips the app to the Search page.
// If that happens before the visitor has touched the keyboard/mouse/screen
// themselves, quietly clear it and return to the Home page.
function guardAgainstAutofilledSearch() {
  let userInteracted = false;
  const markInteracted = () => { userInteracted = true; };
  ['keydown', 'mousedown', 'touchstart'].forEach(evt =>
    window.addEventListener(evt, markInteracted, { once: true, capture: true })
  );

  setTimeout(() => {
    if (userInteracted) return;
    const si = document.getElementById('search-input');
    if (si && si.value) { si.value = ''; doSearch(''); }
    document.getElementById('search-suggestions').classList.remove('open');
    if (state.pageHistory[state.historyIndex] === 'search') {
      showPage('home', null);
      document.querySelectorAll('.nav-item, .mobile-nav-btn').forEach(n => {
        n.classList.toggle('active', n.getAttribute('onclick') === "showPage('home',this)");
      });
    }
  }, 700);
}

function setGreeting() {
  const h = new Date().getHours();
  state.greetingPeriod = h < 12 ? 'morning' : h < 17 ? 'afternoon' : 'evening';
  renderGreeting();
}

function renderGreeting() {
  const dict = I18N[state.settings.language] || I18N.en;
  const key  = 'greeting_' + (state.greetingPeriod || 'evening');
  document.getElementById('greeting-time').textContent = dict[key] || I18N.en[key];
}

// ---- PAGES ----
let _isPopStateNav = false;

function showPage(name, navEl) {
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  const page = document.getElementById('page-' + name);
  if (page) page.classList.add('active');

  // Make sure the Now Playing play/pause icon reflects the audio element's
  // actual state when the page is opened, in case it fell out of sync.
  if (name === 'nowplaying') {
    const npmIcon = document.getElementById('npm-play-icon');
    if (npmIcon) npmIcon.className = (state.currentTrack && !audio.paused) ? 'fas fa-pause' : 'fas fa-play';
  }

  // Update nav
  if (navEl) {
    document.querySelectorAll('.nav-item, .mobile-nav-btn').forEach(n => n.classList.remove('active'));
    navEl.classList.add('active');
  }

  // Tab row visibility
  document.getElementById('tab-row').style.display = ['playlist'].includes(name) ? 'flex' : 'none';

  // History
  if (state.pageHistory[state.historyIndex] !== name) {
    state.pageHistory = state.pageHistory.slice(0, state.historyIndex + 1);
    state.pageHistory.push(name);
    state.historyIndex = state.pageHistory.length - 1;
  }

  // Browser / device back-button integration: keep navigation inside the app
  // instead of letting the hardware/gesture back button close it.
  if (!_isPopStateNav) {
    try { history.pushState({ rhemaPage: name }, '', '#' + name); } catch (e) {}
  }
}

function historyBack() {
  if (state.historyIndex > 0) { state.historyIndex--; showPage(state.pageHistory[state.historyIndex], null); }
}
function historyForward() {
  if (state.historyIndex < state.pageHistory.length - 1) { state.historyIndex++; showPage(state.pageHistory[state.historyIndex], null); }
}

// Respond to the browser/device back & forward gestures by switching pages
// in-app (rather than navigating away from the site).
window.addEventListener('popstate', (e) => {
  const target = (e.state && e.state.rhemaPage) || 'home';
  _isPopStateNav = true;
  showPage(target, null);
  _isPopStateNav = false;
});

// ---- HOME PAGE ----
function renderHomePage() {
  // Banner (loaded from the database — admin-managed)
  document.getElementById('banner-wrap').innerHTML = featuredBanners.map(b => `
    <div class="banner-slide" style="background:${b.gradient};" onclick="${_bannerAction(b)}">
      <div style="position:absolute;right:16px;top:16px;font-size:3.5rem;opacity:0.4;">${b.emoji}</div>
      <div style="position:relative;z-index:1;">
        <div style="font-size:1.3rem;font-weight:900;">${b.title}</div>
        <div style="font-size:0.8rem;color:rgba(255,255,255,0.7);margin-top:2px;">${b.subtitle}</div>
        <button style="margin-top:10px;background:var(--green);color:#000;border:none;border-radius:24px;padding:6px 18px;font-weight:700;font-size:0.8rem;cursor:pointer;">
          <i class="fas fa-play" style="margin-right:4px;"></i>Play
        </button>
      </div>
    </div>
  `).join('');

  // Quick access
  const qa = document.getElementById('quick-access');
  const quickItems = [
    { label:'Liked Songs', icon:'💚', action:"showLikedSongs()" },
    { label:'Gospel Fire', icon:'🔥', action:"openPlaylist('pl2')" },
    { label:'Worship Anthems', icon:'🕊️', action:"openPlaylist('pl1')" },
    { label:'Live Stream', icon:'🔴', action:"showPage('live',null)" },
    { label:'Videos', icon:'🎬', action:"showPage('videos',null)" },
    { label:'All Artists', icon:'🎤', action:"showPage('artists',null);renderArtistsPage()" },
  ];
  qa.innerHTML = quickItems.map(q => `
    <div onclick="${q.action}" style="background:#2a2a2a;border-radius:6px;padding:12px 16px;display:flex;align-items:center;gap:12px;cursor:pointer;transition:background 0.15s;" onmouseover="this.style.background='#3a3a3a'" onmouseout="this.style.background='#2a2a2a'">
      <span style="font-size:1.4rem;">${q.icon}</span>
      <span style="font-weight:700;font-size:0.9rem;">${q.label}</span>
    </div>
  `).join('');

  // Featured
  renderFeaturedSection();

  // Artists row
  document.getElementById('artists-row').innerHTML = artists.map(a => `
    <div onclick="${a.creatorId ? `openChannelPage(${a.creatorId})` : `openArtistPage('${a.name}')`}" style="flex-shrink:0;width:130px;text-align:center;cursor:pointer;padding:12px 8px;border-radius:10px;transition:background 0.2s;" onmouseover="this.style.background='#282828'" onmouseout="this.style.background='transparent'">
      ${artistAvatarHtml(a, 90, '2.2rem', 10)}
      <div style="font-weight:700;font-size:0.82rem;">${a.name}</div>
      <div style="font-size:0.72rem;color:var(--text-muted);">Artist</div>
    </div>
  `).join('');

  // Recent
  renderGospelSection();
}

// ---- HOME SECTION CARD/LIST VIEW TOGGLE ----
const HOME_VIEW_KEY = 'rhema_home_views';

function loadHomeViewPrefs() {
  try {
    const raw = localStorage.getItem(HOME_VIEW_KEY);
    if (raw) {
      const saved = JSON.parse(raw);
      if (saved && saved.featured) state.homeViews.featured = saved.featured;
      if (saved && saved.gospel) state.homeViews.gospel = saved.gospel;
    }
  } catch (e) {}
  applyHomeViewToggleUI();
}

function saveHomeViewPrefs() {
  try { localStorage.setItem(HOME_VIEW_KEY, JSON.stringify(state.homeViews)); } catch (e) {}
}

function applyHomeViewToggleUI() {
  document.querySelectorAll('.view-toggle-group[data-section]').forEach(group => {
    const section = group.dataset.section;
    const mode = state.homeViews[section] || 'card';
    group.querySelectorAll('.view-toggle-btn').forEach(btn => {
      btn.classList.toggle('active', btn.dataset.view === mode);
    });
  });
}

function setHomeSectionView(section, mode) {
  state.homeViews[section] = mode;
  saveHomeViewPrefs();
  applyHomeViewToggleUI();
  if (section === 'featured') renderFeaturedSection();
  else if (section === 'gospel') renderGospelSection();
}

// Renders a list of tracks into a .grid-cards container as either cards
// (default grid) or list rows (same style as the Library page).
function _renderHomeTrackList(containerId, list, mode, emptyHTML) {
  const el = document.getElementById(containerId);
  if (!el) return;
  el.classList.toggle('list-view', mode === 'list');
  if (!list.length) { el.innerHTML = emptyHTML || ''; return; }
  el.innerHTML = mode === 'list'
    ? list.map((t,i) => trackRow(t,i)).join('')
    : list.map(t => trackCard(t)).join('');
}

function renderFeaturedSection() {
  _renderHomeTrackList('featured-grid', tracks.slice(0,6), state.homeViews.featured,
    `<div style="grid-column:1/-1;text-align:center;padding:40px 20px;color:var(--text-muted);">
       <i class="fas fa-music" style="font-size:2.5rem;margin-bottom:12px;display:block;color:#333;"></i>
       <p>No tracks yet. Check back soon!</p>
     </div>`);
}

function renderGospelSection() {
  _renderHomeTrackList('recent-grid', tracks.length > 4 ? tracks.slice(4) : tracks, state.homeViews.gospel, '');
}

// Reconstruct a clickable JS action from a banner's admin-defined action_type/action_target
function _bannerAction(b) {
  switch (b.action_type) {
    case 'track':    return `playTrack(${parseInt(b.action_target) || 0})`;
    case 'playlist': return `openPlaylist('${b.action_target}')`;
    case 'url':      return `window.open('${b.action_target}','_blank')`;
    case 'page':
    default:         return `showPage('${b.action_target || 'home'}',null)`;
  }
}

function trackCard(t) {
  const isPlaying = state.currentTrack && state.currentTrack.id === t.id;
  const fallback = t.emoji || '🎵';
  const eqClass = state.isPlaying ? '' : ' paused';
  return `
    <div class="card ${isPlaying ? 'playing' : ''}" data-track-id="${t.id}" onclick="playTrack(${t.id})" oncontextmenu="showContextMenu(event,${t.id})">
      <div class="card-img" style="background:${t.color||'#1a3a2a'}20;border:1px solid ${t.color||'#1a3a2a'}30;padding:0;overflow:hidden;">
        <img src="api.php?action=thumbnail&id=${t.id}&size=md"
             style="width:100%;height:100%;object-fit:cover;border-radius:6px;"
             alt="${t.title}" loading="lazy" decoding="async"
             onerror="this.replaceWith(Object.assign(document.createElement('span'),{style:'font-size:3rem;display:flex;align-items:center;justify-content:center;width:100%;height:100%;',textContent:'${fallback}'}))"/>
        ${isPlaying ? `<div class="card-now-playing-badge"><div class="eq-bars${eqClass}"><div class="eq-bar"></div><div class="eq-bar"></div><div class="eq-bar"></div><div class="eq-bar"></div></div></div>` : ''}
        <button class="card-play-overlay" onclick="event.stopPropagation();playTrack(${t.id})"><i class="fas fa-play" style="margin-left:2px;"></i></button>
      </div>
      <div class="card-title">${t.title}</div>
      <div class="card-sub">${t.artist}</div>
    </div>
  `;
}

// ---- SEARCH PAGE ----
function renderSearchPage() {
  const g = document.getElementById('genre-grid');
  g.innerHTML = genres.map(genre => `
    <div onclick="filterGenre('${genre.name}')" style="background:${genre.color};border-radius:10px;padding:20px 16px;cursor:pointer;position:relative;overflow:hidden;height:100px;display:flex;align-items:flex-end;transition:filter 0.2s;" onmouseover="this.style.filter='brightness(1.2)'" onmouseout="this.style.filter='brightness(1)'">
      <span style="font-weight:800;font-size:1rem;position:relative;z-index:1;">${genre.name}</span>
      <span style="position:absolute;right:-4px;bottom:-8px;font-size:3.5rem;transform:rotate(15deg);opacity:0.7;">${genre.emoji}</span>
    </div>
  `).join('');
}

function doSearch(q) {
  const section = document.getElementById('search-results-section');
  const container = document.getElementById('search-results');
  const title = document.getElementById('search-results-title');
  document.getElementById('search-clear-btn').style.display = q.trim() ? 'flex' : 'none';
  if (!q.trim()) { section.style.display = 'none'; return; }
  section.style.display = 'block';
  title.textContent = `Results for "${q}"`;
  const found = tracks.filter(t => t.title.toLowerCase().includes(q.toLowerCase()) || t.artist.toLowerCase().includes(q.toLowerCase()) || t.genre.toLowerCase().includes(q.toLowerCase()));
  if (!found.length) { container.innerHTML = '<p style="color:var(--text-muted);">No results found.</p>'; return; }
  container.innerHTML = found.map((t,i) => trackRow(t, i)).join('');
}

function clearSearch() {
  const input = document.getElementById('search-input');
  input.value = '';
  doSearch('');
  input.focus();
  showSearchSuggestions();
}

// ---- RECENT SEARCHES (stored locally, shown like YouTube's search dropdown) ----
const RECENT_SEARCHES_KEY = 'rhema_recent_searches';
const MAX_RECENT_SEARCHES = 8;

function loadRecentSearches() {
  try {
    const raw = localStorage.getItem(RECENT_SEARCHES_KEY);
    return raw ? JSON.parse(raw) : [];
  } catch (e) { return []; }
}

function saveRecentSearch(q) {
  q = q.trim();
  if (!q) return;
  let list = loadRecentSearches().filter(item => item.toLowerCase() !== q.toLowerCase());
  list.unshift(q);
  list = list.slice(0, MAX_RECENT_SEARCHES);
  try { localStorage.setItem(RECENT_SEARCHES_KEY, JSON.stringify(list)); } catch (e) {}
}

function removeRecentSearch(q, event) {
  if (event) event.stopPropagation();
  const list = loadRecentSearches().filter(item => item !== q);
  try { localStorage.setItem(RECENT_SEARCHES_KEY, JSON.stringify(list)); } catch (e) {}
  showSearchSuggestions();
}

function showSearchSuggestions() {
  const box = document.getElementById('search-suggestions');
  const list = loadRecentSearches();
  if (!list.length) { box.classList.remove('open'); box.innerHTML = ''; return; }
  box.innerHTML = `
    <div style="display:flex;align-items:center;justify-content:space-between;padding:6px 10px;color:var(--text-muted);font-size:0.72rem;text-transform:uppercase;letter-spacing:0.05em;">
      <span>Recent searches</span>
    </div>
    ${list.map(q => `
      <div class="search-suggestion-item" onmousedown="event.preventDefault();selectRecentSearch('${q.replace(/'/g, "\\'")}')">
        <i class="fas fa-history" style="color:var(--text-muted);"></i>
        <span class="ss-text">${q}</span>
        <i class="fas fa-times ss-remove" onmousedown="event.preventDefault();event.stopPropagation();removeRecentSearch('${q.replace(/'/g, "\\'")}')" title="Remove"></i>
      </div>
    `).join('')}
  `;
  box.classList.add('open');
}

function hideSearchSuggestions() {
  setTimeout(() => document.getElementById('search-suggestions').classList.remove('open'), 150);
}

function selectRecentSearch(q) {
  const input = document.getElementById('search-input');
  input.value = q;
  doSearch(q);
  saveRecentSearch(q);
  document.getElementById('search-suggestions').classList.remove('open');
  input.focus();
}

function onSearchKeydown(event) {
  if (event.key === 'Enter') {
    const q = event.target.value.trim();
    if (q) { saveRecentSearch(q); document.getElementById('search-suggestions').classList.remove('open'); }
  } else if (event.key === 'Escape') {
    document.getElementById('search-suggestions').classList.remove('open');
    event.target.blur();
  }
}

function filterGenre(genre) {
  document.getElementById('search-input').value = genre;
  doSearch(genre);
  document.getElementById('search-results-section').style.display = 'block';
}

function trackRow(t, i) {
  const isPlaying = state.currentTrack && state.currentTrack.id === t.id;
  const isLiked = state.likedTracks.has(t.id);
  const fallback = t.emoji || '🎵';
  const eqClass = state.isPlaying ? '' : ' paused';
  return `
    <div class="track-row ${isPlaying ? 'playing' : ''}" data-track-id="${t.id}" onclick="playTrack(${t.id})" oncontextmenu="showContextMenu(event,${t.id})">
      <div class="track-num" data-index="${i+1}">
        ${isPlaying ? `<div class="eq-bars${eqClass}"><div class="eq-bar"></div><div class="eq-bar"></div><div class="eq-bar"></div><div class="eq-bar"></div></div>` : (i+1)}
      </div>
      <div class="track-thumb" style="overflow:hidden;">
        <img src="api.php?action=thumbnail&id=${t.id}&size=sm"
             style="width:100%;height:100%;object-fit:cover;border-radius:4px;"
             alt="${t.title}" loading="lazy" decoding="async"
             onerror="this.replaceWith(Object.assign(document.createElement('span'),{style:'font-size:1.2rem;',textContent:'${fallback}'}))"/>
      </div>
      <div class="track-info">
        <div class="track-name" style="${isPlaying ? 'color:var(--green)' : ''}">${t.title}</div>
        <div class="track-artist">${t.artist}</div>
      </div>
      <div class="track-actions">
        <button class="track-action-btn ${isLiked ? 'liked' : ''}" onclick="event.stopPropagation();toggleLike(${t.id})" title="Like"><i class="fas fa-heart"></i></button>
        <button class="track-action-btn" onclick="event.stopPropagation();openAddToPlaylist(${t.id})" title="Add to playlist"><i class="fas fa-plus"></i></button>
        <button class="track-action-btn" onclick="event.stopPropagation();downloadTrackLyrics(${t.id})" title="Download lyrics"><i class="fas fa-download"></i></button>
        <button class="track-action-btn" onclick="event.stopPropagation();shareTrack(${t.id})" title="Share"><i class="fas fa-share"></i></button>
      </div>
      <div class="track-duration">${t.duration}</div>
    </div>
  `;
}

// ---- LIBRARY PAGE ----
function renderLibraryPage() {
  const c = document.getElementById('library-content');
  c.innerHTML = `
    <div style="margin-bottom:28px;">
      <div class="section-title" style="font-size:1rem;">Featured Playlists</div>
      ${state.playlists.map(pl => `
        <div class="track-row" onclick="openPlaylist('${pl.id}')">
          <div class="track-thumb" style="font-size:1.3rem;">${pl.emoji}</div>
          <div class="track-info">
            <div class="track-name">${pl.name}</div>
            <div class="track-artist">${pl.tracks.length} songs · ${pl.desc}</div>
          </div>
          <button class="track-action-btn" onclick="event.stopPropagation();playPlaylistById('${pl.id}')" title="Play"><i class="fas fa-play-circle" style="font-size:1.2rem;"></i></button>
        </div>
      `).join('')}
    </div>
    <div style="margin-bottom:28px;">
      <div class="section-title" style="font-size:1rem;display:flex;align-items:center;justify-content:space-between;">
        <span>My Playlists</span>
        <button class="btn-outline" style="padding:6px 16px;font-size:0.78rem;" onclick="openCreatePlaylist()"><i class="fas fa-plus" style="margin-right:6px;"></i>New playlist</button>
      </div>
      ${state.currentUser ? (
        state.userPlaylists.length ? state.userPlaylists.map(pl => `
          <div class="track-row" onclick="openPlaylist('${pl.id}')">
            <div class="track-thumb" style="font-size:1.3rem;">${pl.emoji}</div>
            <div class="track-info">
              <div class="track-name">${pl.name}</div>
              <div class="track-artist">${pl.tracks.length} songs${pl.desc ? ' · ' + pl.desc : ''}</div>
            </div>
            <button class="track-action-btn" onclick="event.stopPropagation();playPlaylistById('${pl.id}')" title="Play"><i class="fas fa-play-circle" style="font-size:1.2rem;"></i></button>
            <button class="track-action-btn" onclick="event.stopPropagation();deleteUserPlaylist('${pl.id}')" title="Delete playlist"><i class="fas fa-trash"></i></button>
          </div>
        `).join('') : '<p style="color:var(--text-muted);font-size:0.85rem;padding:8px 0;">You haven\'t created any playlists yet.</p>'
      ) : '<p style="color:var(--text-muted);font-size:0.85rem;padding:8px 0;">Log in to create and save your own playlists.</p>'}
    </div>
    <div>
      <div class="section-title" style="font-size:1rem;">All Tracks</div>
      ${tracks.map((t,i) => trackRow(t,i)).join('')}
    </div>
  `;
}

function showLikedSongs() {
  const liked = tracks.filter(t => state.likedTracks.has(t.id));
  openPlaylistByData({ id:'liked', name:'Liked Songs', desc:'Songs you\'ve liked', tracks:liked.map(t=>t.id), emoji:'💚' });
  showPage('playlist', null);
}

// ---- VIDEO PAGE ----

// Pull the 11-char YouTube video id out of any watch/share/youtu.be/embed/live link.
function _youtubeIdFromUrl(url) {
  if (!url) return '';
  const watchMatch = url.match(/[?&]v=([\w-]{11})/);
  const shortMatch = url.match(/youtu\.be\/([\w-]{11})/);
  const embedMatch = url.match(/youtube\.com\/embed\/([\w-]{11})/);
  const liveMatch  = url.match(/youtube\.com\/live\/([\w-]{11})/);
  return (watchMatch && watchMatch[1]) || (shortMatch && shortMatch[1]) ||
         (embedMatch && embedMatch[1]) || (liveMatch && liveMatch[1]) || '';
}

// Clean, emoji-free fallback thumbnail: a title-initial over a deterministic
// gradient derived from the title — used whenever no real thumbnail image
// exists (no YouTube id, broken/missing custom image, etc).
function _videoFallbackThumbHTML(id) {
  const v = videos.find(x => x.id === id);
  const title = (v && v.title) || '';
  const initial = title.trim().charAt(0).toUpperCase() || '♪';
  let hash = 7;
  for (let i = 0; i < title.length; i++) hash = (hash * 31 + title.charCodeAt(i)) >>> 0;
  const hue = hash % 360;
  return `<div class="video-thumb-fallback" style="background:linear-gradient(135deg, hsl(${hue},42%,26%) 0%, hsl(${(hue + 35) % 360},38%,13%) 100%);"><span>${initial}</span></div>`;
}

// Real thumbnail wherever one is available (YouTube provides one for every
// video id); otherwise render the generated fallback above — never an emoji.
function _videoThumbHTML(v) {
  const ytId = v.type === 'youtube' ? _youtubeIdFromUrl(v.src) : '';
  if (ytId) {
    return `<img src="https://img.youtube.com/vi/${ytId}/hqdefault.jpg" alt="" loading="lazy" onerror="this.outerHTML = _videoFallbackThumbHTML(${v.id})"/>`;
  }
  return _videoFallbackThumbHTML(v.id);
}

function videoCardHTML(v) {
  const creatorLine = v.creator_id
    ? `<div style="font-size:0.75rem;color:var(--green);cursor:pointer;margin-top:2px;" onclick="event.stopPropagation();openChannelPage(${v.creator_id})"><i class="fas fa-user-circle"></i> ${v.creator_name || 'View channel'}</div>`
    : '';
  return `
    <div class="video-card" onclick="openVideoModal(${v.id})">
      <div class="video-preview">
        ${_videoThumbHTML(v)}
        <div class="video-play-btn"><i class="fas fa-play" style="margin-left:2px;"></i></div>
      </div>
      <div class="video-card-info">
        <div style="font-weight:700;font-size:0.9rem;margin-bottom:4px;">${v.title}</div>
        <div style="font-size:0.78rem;color:var(--text-muted);">${v.channel} · ${v.duration}</div>
        ${creatorLine}
        <div style="font-size:0.75rem;color:var(--text-muted);margin-top:4px;">${v.desc}</div>
      </div>
    </div>
  `;
}

function renderVideoPage() {
  const g = document.getElementById('video-grid');
  g.innerHTML = videos.map(v => videoCardHTML(v)).join('');
}

// Normalize any YouTube link (watch/share/youtu.be/embed) into an embeddable URL
// and append autoplay with the correct ? or & separator.
function _youtubeEmbedSrc(url) {
  let embedUrl = url;
  // Already an embed URL (incl. special forms like /embed/live_stream?channel=...) — leave the path/query intact
  if (!/youtube\.com\/embed\//.test(url)) {
    const id = _youtubeIdFromUrl(url);
    if (id) embedUrl = `https://www.youtube.com/embed/${id}`;
  }
  return embedUrl + (embedUrl.includes('?') ? '&' : '?') + 'autoplay=1';
}

// Wrap a Facebook video/live post URL in Facebook's embeddable video plugin
// (or pass an already-built plugin URL straight through).
function _facebookEmbedSrc(url) {
  if (/facebook\.com\/plugins\/video\.php/.test(url)) {
    return url + (url.includes('autoplay') ? '' : (url.includes('?') ? '&' : '?') + 'autoplay=true');
  }
  return 'https://www.facebook.com/plugins/video.php?href=' + encodeURIComponent(url) + '&show_text=false&autoplay=true';
}

function openVideoModal(id) {
  const v = videos.find(x => x.id === id);
  if (!v) return;
  document.getElementById('video-modal-title').textContent = v.title;
  document.getElementById('video-modal-meta').textContent = [v.channel, v.duration, v.desc].filter(Boolean).join(' · ');
  document.getElementById('video-modal-content').innerHTML = v.type === 'youtube'
    ? `<iframe src="${_youtubeEmbedSrc(v.src)}" frameborder="0" allow="autoplay;encrypted-media" allowfullscreen></iframe>`
    : `<video controls autoplay><source src="${v.src}"></video>`;
  document.getElementById('video-modal').classList.add('open');
  document.getElementById('video-modal').scrollTop = 0;
}

function closeVideoModal() {
  document.getElementById('video-modal').classList.remove('open');
  document.getElementById('video-modal-content').innerHTML = '';
}

// ---- LIVE PAGE ----
let _hlsInstances = {};

// Show/hide the red notification dot on the Live nav icon based on whether any stream is active
function updateLiveNavDot() {
  const hasLive = liveStreams.some(s => s.active);
  document.querySelectorAll('.live-nav-dot').forEach(d => d.classList.toggle('show', hasLive));
}

function renderLivePage() {
  const c = document.getElementById('live-content');

  // ---- No streams configured ----
  if (!liveStreams.length) {
    c.innerHTML = `
      <div style="text-align:center;padding:60px 20px;color:var(--text-muted);">
        <i class="fas fa-broadcast-tower" style="font-size:3rem;margin-bottom:16px;display:block;color:#333;"></i>
        <p style="font-size:1rem;margin-bottom:12px;">No live streams configured yet.</p>
        <p style="font-size:0.85rem;color:var(--text-muted);">Check back soon for live broadcasts.</p>
      </div>
      ${_scheduleHTML()}
    `;
    return;
  }

  c.innerHTML = `
    <div style="margin-bottom:32px;">
      <div class="section-title" style="font-size:1.1rem;margin-bottom:16px;">
        <span><span class="live-badge" style="margin-right:8px;">LIVE</span>Live Streams</span>
      </div>
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px;">
        ${liveStreams.map((s, i) => _liveCardHTML(s, i)).join('')}
      </div>
    </div>
    ${_scheduleHTML()}
  `;

  // Auto-init HLS streams that are active
  liveStreams.forEach((s, i) => {
    if (s.type === 'hls' && s.active && s.src) {
      setTimeout(() => initHLS(s.src, i), 200);
    }
  });
}

function _liveCardHTML(s, i) {
  const activeBadge = s.active
    ? '<span class="live-badge">LIVE</span>'
    : '<span style="background:#444;color:#aaa;font-size:.65rem;font-weight:800;padding:2px 6px;border-radius:3px;">OFFLINE</span>';

  const header = `
    <div style="padding:16px;border-bottom:1px solid #333;">
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
        ${activeBadge}
        <span style="font-size:.8rem;color:var(--text-muted);">
          <i class="fas fa-eye" style="margin-right:4px;"></i>${s.viewers || '0'} watching
        </span>
      </div>
      <div style="font-weight:700;font-size:1rem;">${s.title}</div>
      <div style="font-size:.82rem;color:var(--text-muted);margin-top:4px;">Hosted by ${s.host}</div>
    </div>`;

  if (s.type === 'youtube' && s.src) {
    // Normalize watch/share/live links into an embeddable URL with autoplay
    const ytSrc = _youtubeEmbedSrc(s.src);
    return `
      <div style="background:var(--bg-card);border-radius:12px;overflow:hidden;">
        ${header}
        <iframe src="${ytSrc}" frameborder="0"
                allow="autoplay;encrypted-media;fullscreen"
                style="width:100%;aspect-ratio:16/9;display:block;"></iframe>
      </div>`;
  }

  if (s.type === 'facebook' && s.src) {
    return `
      <div style="background:var(--bg-card);border-radius:12px;overflow:hidden;">
        ${header}
        <iframe src="${_facebookEmbedSrc(s.src)}" frameborder="0"
                allow="autoplay;encrypted-media;fullscreen"
                style="width:100%;aspect-ratio:16/9;display:block;"></iframe>
      </div>`;
  }

  if (s.type === 'hls') {
    return `
      <div style="background:var(--bg-card);border-radius:12px;overflow:hidden;">
        ${header}
        <div style="background:#000;aspect-ratio:16/9;position:relative;">
          <video id="hls-video-${i}" controls playsinline
                 style="width:100%;height:100%;display:block;"
                 ${!s.src ? 'disabled' : ''}></video>
          ${!s.src ? `<div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:var(--text-muted);font-size:.85rem;">No stream URL configured</div>` : ''}
        </div>
        ${s.src ? `<div style="padding:12px;display:flex;gap:8px;">
          <button onclick="initHLS('${s.src}',${i})" class="btn-green" style="flex:1;padding:10px;font-weight:700;font-size:.9rem;cursor:pointer;border:none;border-radius:24px;">
            <i class="fas fa-play" style="margin-right:8px;"></i>Watch Live
          </button>
          <button onclick="stopHLS(${i})" class="btn-outline" style="padding:10px 18px;font-weight:700;font-size:.9rem;cursor:pointer;border:1px solid #555;background:transparent;color:#fff;border-radius:24px;">
            <i class="fas fa-stop"></i>
          </button>
        </div>` : ''}
      </div>`;
  }

  // Radio / Icecast
  return `
    <div style="background:var(--bg-card);border-radius:12px;overflow:hidden;">
      ${header}
      <div style="padding:20px;display:flex;flex-direction:column;gap:12px;">
        <div style="display:flex;align-items:center;gap:16px;background:#1a1a1a;border-radius:8px;padding:16px;">
          <div style="width:48px;height:48px;border-radius:50%;background:var(--green);display:flex;align-items:center;justify-content:center;${s.active ? 'animation:pulse 1.5s infinite;' : ''}">
            <i class="fas fa-broadcast-tower" style="color:#000;font-size:1.2rem;"></i>
          </div>
          <div>
            <div style="font-weight:700;">${s.title}</div>
            <div style="font-size:.78rem;color:var(--text-muted);">Gospel Radio • 24/7</div>
          </div>
        </div>
        ${s.src ? `
        <button onclick="playLiveRadio('${s.src}','${s.title}')" class="btn-green" style="width:100%;padding:12px;font-size:1rem;cursor:pointer;border:none;border-radius:24px;font-weight:700;">
          <i class="fas fa-play" style="margin-right:8px;"></i>Listen Live
        </button>
        <button onclick="stopLiveRadio()" class="btn-outline" style="width:100%;padding:12px;font-size:1rem;cursor:pointer;border:1px solid #555;background:transparent;color:#fff;border-radius:24px;font-weight:700;">
          <i class="fas fa-stop" style="margin-right:8px;"></i>Stop
        </button>` : `<p style="color:var(--text-muted);font-size:.85rem;text-align:center;padding:8px;">No stream URL configured yet.</p>`}
      </div>
    </div>`;
}

function _scheduleHTML() {
  if (!broadcastSchedule.length) return '';
  return `
    <div>
      <div class="section-title" style="font-size:1.1rem;margin-bottom:16px;">📅 Broadcast Schedule</div>
      <div style="background:var(--bg-card);border-radius:12px;overflow:hidden;">
        ${broadcastSchedule.map((s, i) => `
          <div style="display:flex;align-items:center;gap:16px;padding:16px 20px;${i>0?'border-top:1px solid #333;':''}">
            <div style="min-width:90px;font-weight:700;color:var(--green);">${s.day}</div>
            <div style="flex:1;">
              <div style="font-weight:600;">${s.show_name}</div>
              <div style="font-size:.78rem;color:var(--text-muted);">${s.host}</div>
            </div>
            <div style="font-size:.8rem;color:var(--text-muted);">${s.time_range}</div>
          </div>
        `).join('')}
      </div>
    </div>`;
}

// ============================================================
//  OFFLINE DOWNLOADS (IndexedDB) + LOCAL DEVICE FILES
// ============================================================
let _offlineDB = null;
let _offlineIds = new Set();

function _openOfflineDB() {
  return new Promise((resolve, reject) => {
    if (_offlineDB) return resolve(_offlineDB);
    if (!window.indexedDB) return reject(new Error('IndexedDB not supported'));
    const req = indexedDB.open('rhema_offline_db', 1);
    req.onupgradeneeded = () => {
      if (!req.result.objectStoreNames.contains('tracks')) req.result.createObjectStore('tracks', { keyPath: 'id' });
    };
    req.onsuccess = () => { _offlineDB = req.result; resolve(_offlineDB); };
    req.onerror   = () => reject(req.error);
  });
}
function _idbPutTrack(value) {
  return _openOfflineDB().then(db => new Promise((resolve, reject) => {
    const tx = db.transaction('tracks', 'readwrite');
    tx.objectStore('tracks').put(value);
    tx.oncomplete = () => resolve();
    tx.onerror    = () => reject(tx.error);
  }));
}
function _idbDeleteTrack(id) {
  return _openOfflineDB().then(db => new Promise((resolve, reject) => {
    const tx = db.transaction('tracks', 'readwrite');
    tx.objectStore('tracks').delete(id);
    tx.oncomplete = () => resolve();
    tx.onerror    = () => reject(tx.error);
  }));
}
function _idbGetAllTracks() {
  return _openOfflineDB().then(db => new Promise((resolve, reject) => {
    const tx  = db.transaction('tracks', 'readonly');
    const req = tx.objectStore('tracks').getAll();
    req.onsuccess = () => resolve(req.result || []);
    req.onerror   = () => reject(req.error);
  }));
}

async function refreshOfflineIdsCache() {
  try { _offlineIds = new Set((await _idbGetAllTracks()).map(x => x.id)); }
  catch (e) { _offlineIds = new Set(); }
  updateOfflineDownloadButtons();
}

// Update the download button on the now-playing screen to reflect whether
// the currently-playing track has already been saved for offline listening.
function updateOfflineDownloadButtons() {
  const isSaved = !!(state.currentTrack && _offlineIds.has(state.currentTrack.id));
  document.querySelectorAll('.np-offline-btn').forEach(btn => {
    btn.classList.toggle('saved', isSaved);
    btn.title = isSaved ? 'Saved for offline' : 'Download for offline';
  });
}

// Triggered from the download icon inside the Now Playing full screen
async function downloadCurrentTrackOffline() {
  if (!state.currentTrack) return;
  const t = state.currentTrack;
  if (_offlineIds.has(t.id)) { showToast('Already saved for offline ✓'); return; }
  showToast('Downloading "' + t.title + '" for offline…');
  try {
    const res = await fetch(t.src || ('stream.php?id=' + t.id));
    if (!res.ok) throw new Error('Download failed');
    const blob = await res.blob();
    await _idbPutTrack({
      id: t.id, title: t.title, artist: t.artist, album: t.album || '',
      duration: t.duration || '', color: t.color || '#1a3a2a', emoji: t.emoji || '🎵',
      lyrics: t.lyrics || '', blob,
    });
    _offlineIds.add(t.id);
    updateOfflineDownloadButtons();
    showToast('Saved "' + t.title + '" for offline playback ✓');
    if (document.getElementById('page-offline')?.classList.contains('active')) renderOfflinePage();
  } catch (e) {
    showToast('Could not download — check your connection');
  }
}

async function removeOfflineTrack(id) {
  await _idbDeleteTrack(id);
  _offlineIds.delete(id);
  updateOfflineDownloadButtons();
  showToast('Removed from offline downloads');
  renderOfflinePage();
}

// Plays a track straight from its locally-stored blob — works fully offline
async function playOfflineTrack(id) {
  try {
    const list = await _idbGetAllTracks();
    const item = list.find(x => x.id === id);
    if (!item) return;
    const url = URL.createObjectURL(item.blob);
    const t = { id: item.id, title: item.title, artist: item.artist, album: item.album, duration: item.duration, color: item.color, emoji: item.emoji, lyrics: item.lyrics };
    const queue = list.map(x => ({ id: x.id, title: x.title, artist: x.artist, album: x.album, duration: x.duration, color: x.color, emoji: x.emoji, lyrics: x.lyrics }));
    _playFromCustomSource(t, url, queue, 'offline');
  } catch (e) {
    showToast('Could not play this offline track');
  }
}

// Hands the locally-stored blob to the browser's normal file-save flow
async function saveOfflineTrackToDevice(id) {
  try {
    const list = await _idbGetAllTracks();
    const item = list.find(x => x.id === id);
    if (!item) return;
    const url = URL.createObjectURL(item.blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = (item.title || 'track').replace(/[^\w\-. ]+/g, '').trim() + '.mp3';
    document.body.appendChild(a);
    a.click();
    a.remove();
    setTimeout(() => URL.revokeObjectURL(url), 4000);
    showToast('Saving "' + item.title + '" to your device…');
  } catch (e) {
    showToast('Could not save this file');
  }
}

// ---- Play local audio files picked straight from the device ----
function pickLocalFiles() {
  document.getElementById('local-file-input').click();
}

function handleLocalFiles(fileList) {
  const files = Array.from(fileList || []).filter(f => f.type.startsWith('audio/'));
  if (!files.length) return;
  files.forEach(f => {
    state.localFiles.push({
      id: 'local-' + Date.now() + '-' + Math.random().toString(36).slice(2, 7),
      title: f.name.replace(/\.[^/.]+$/, ''),
      artist: 'On this device',
      album: '', duration: '', color: '#333333', emoji: '📁', lyrics: '',
      file: f,
    });
  });
  showToast(files.length > 1 ? files.length + ' files added' : '"' + files[0].name + '" added');
  renderOfflinePage();
}

function playLocalFile(idx) {
  const f = state.localFiles[idx];
  if (!f) return;
  const url = URL.createObjectURL(f.file);
  const queue = state.localFiles.map(x => ({ id: x.id, title: x.title, artist: x.artist, album: x.album, duration: x.duration, color: x.color, emoji: x.emoji, lyrics: x.lyrics }));
  _playFromCustomSource({ id: f.id, title: f.title, artist: f.artist, album: f.album, duration: f.duration, color: f.color, emoji: f.emoji, lyrics: f.lyrics }, url, queue, 'local');
}

// Shared "now playing" renderer for tracks that don't come from the server
// catalogue (offline downloads & local device files) — bypasses stream.php
// and skips server-side thumbnails since they may be unreachable offline.
function _playFromCustomSource(t, srcUrl, queue, source) {
  state.queueSource   = source;
  state.currentTrack  = t;
  state.queue         = queue;
  state.currentIndex  = queue.findIndex(x => x.id === t.id);

  clearInterval(simInterval);
  audio.src = srcUrl;
  audio.load();
  audio.playbackRate = state.playbackRate;
  audio.play().catch(() => {});
  state.isPlaying = true;
  updatePlaybackSpeedUI();

  const playCls = 'fas fa-pause';
  document.getElementById('play-icon').className = playCls;
  const npmIcon = document.getElementById('npm-play-icon');
  if (npmIcon) npmIcon.className = playCls;

  const emojiHTML = `<span style="font-size:inherit;">${t.emoji || '🎵'}</span>`;

  const thumbEl = document.getElementById('player-thumb');
  thumbEl.style.background = (t.color || '#1a3a2a') + '30';
  thumbEl.innerHTML = emojiHTML;
  document.getElementById('player-title').textContent  = t.title;
  document.getElementById('player-artist').textContent = t.artist;
  document.getElementById('player-heart').classList.remove('liked');

  const npArt = document.getElementById('np-album-art');
  npArt.style.background = (t.color || '#1a3a2a') + '30';
  npArt.innerHTML = emojiHTML;
  document.getElementById('np-title').textContent  = t.title;
  document.getElementById('np-artist').textContent = t.artist;
  document.getElementById('lyrics-display').innerHTML = t.lyrics
    ? t.lyrics.split('\n').map(l => `<p style="margin:2px 0;">${l || '&nbsp;'}</p>`).join('')
    : '<p style="color:var(--text-muted);">Lyrics not available for this track.</p>';

  const npmArt = document.getElementById('npm-album-art');
  if (npmArt) {
    npmArt.style.background = (t.color || '#1a3a2a') + '30';
    npmArt.innerHTML = emojiHTML;
    document.getElementById('npm-title').textContent  = t.title;
    document.getElementById('npm-artist').textContent = t.artist;
    document.getElementById('npm-heart').classList.remove('liked');
    document.getElementById('npm-lyrics-title').textContent  = t.title;
    document.getElementById('npm-lyrics-artist').textContent = t.artist;
    document.getElementById('npm-lyrics-display').innerHTML = t.lyrics
      ? t.lyrics.split('\n').map(l => `<p style="margin:2px 0;">${l || '&nbsp;'}</p>`).join('')
      : '<p style="color:var(--text-muted);">Lyrics not available for this track.</p>';
  }

  updateQueuePanel();
  renderUpNext();
  refreshTrackRows();
  updateMiniPlayer();
  updateOfflineDownloadButtons();
  showToast('Now playing: ' + t.title);
}

function renderOfflinePage() {
  const c = document.getElementById('offline-content');
  if (!c) return;
  const pickerHTML = `
    <div style="margin-bottom:20px;">
      <button class="btn-green" onclick="pickLocalFiles()"><i class="fas fa-folder-open" style="margin-right:8px;"></i>Play files from device</button>
      <input type="file" id="local-file-input" accept="audio/*" multiple style="display:none;" onchange="handleLocalFiles(this.files)"/>
    </div>`;

  _idbGetAllTracks().then(list => {
    let html = pickerHTML;

    if (!list.length && !state.localFiles.length) {
      html += `
        <div style="text-align:center;padding:50px 20px;color:var(--text-muted);">
          <i class="fas fa-cloud-download-alt" style="font-size:3rem;margin-bottom:16px;display:block;color:#333;"></i>
          <p style="font-size:1rem;margin-bottom:6px;">No offline tracks yet.</p>
          <p style="font-size:0.85rem;">Open a track's full-screen player and tap the download icon to save it for offline listening — or play audio files straight from your device above.</p>
        </div>`;
      c.innerHTML = html;
      return;
    }

    if (list.length) {
      html += `<div class="section-title" style="font-size:1rem;margin-bottom:8px;">Downloaded for Offline</div>`;
      html += list.map(t => `
        <div class="track-row" style="display:flex;align-items:center;gap:14px;padding:10px 8px;border-radius:8px;">
          <div onclick="playOfflineTrack(${t.id})" style="display:flex;align-items:center;gap:14px;flex:1;min-width:0;cursor:pointer;">
            <div style="width:44px;height:44px;border-radius:6px;background:${t.color || '#1a3a2a'}30;display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0;">${t.emoji || '🎵'}</div>
            <div style="flex:1;min-width:0;">
              <div style="font-weight:600;font-size:0.92rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${t.title}</div>
              <div style="font-size:0.78rem;color:var(--text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${t.artist}</div>
            </div>
          </div>
          <button class="ctrl-btn" onclick="saveOfflineTrackToDevice(${t.id})" title="Save to device"><i class="fas fa-save"></i></button>
          <button class="ctrl-btn" onclick="removeOfflineTrack(${t.id})" title="Remove from offline"><i class="fas fa-trash"></i></button>
        </div>
      `).join('');
    }

    if (state.localFiles.length) {
      html += `<div class="section-title" style="font-size:1rem;margin:24px 0 8px;">Local Files (this session)</div>`;
      html += state.localFiles.map((f, i) => `
        <div class="track-row" style="display:flex;align-items:center;gap:14px;padding:10px 8px;border-radius:8px;cursor:pointer;" onclick="playLocalFile(${i})">
          <div style="width:44px;height:44px;border-radius:6px;background:#33333330;display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0;">📁</div>
          <div style="flex:1;min-width:0;">
            <div style="font-weight:600;font-size:0.92rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${f.title}</div>
            <div style="font-size:0.78rem;color:var(--text-muted);">Playing from this device</div>
          </div>
        </div>
      `).join('');
    }

    c.innerHTML = html;
  });
}

// ---- HLS (live video stream) ----
function initHLS(url, idx) {
  const video = document.getElementById('hls-video-' + idx);
  if (!video || !url) return;
  if (_hlsInstances[idx]) { _hlsInstances[idx].destroy(); }
  if (window.Hls && Hls.isSupported()) {
    const hls = new Hls({ enableWorker: true, lowLatencyMode: true });
    hls.loadSource(url);
    hls.attachMedia(video);
    hls.on(Hls.Events.MANIFEST_PARSED, () => video.play().catch(() => {}));
    hls.on(Hls.Events.ERROR, (e, data) => {
      if (data.fatal) showToast('Live stream error. Check stream URL.');
    });
    _hlsInstances[idx] = hls;
  } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
    // Safari native HLS
    video.src = url;
    video.play().catch(() => {});
  } else {
    showToast('HLS not supported in this browser. Try Chrome or Firefox.');
  }
}

function stopHLS(idx) {
  const video = document.getElementById('hls-video-' + idx);
  if (video) { video.pause(); video.src = ''; }
  if (_hlsInstances[idx]) { _hlsInstances[idx].destroy(); delete _hlsInstances[idx]; }
}

function playLiveRadio(url, title) {
  audio.src = url;
  audio.play().catch(() => showToast('Radio: connecting…'));
  state.currentTrack = null;
  state.isPlaying = true;
  document.getElementById('play-icon').className = 'fas fa-pause';
  const thumbEl = document.getElementById('player-thumb');
  thumbEl.innerHTML = '📻';
  thumbEl.style.background = '#1a1a1a';
  document.getElementById('player-title').textContent  = (title || 'Rhema FM') + ' – Live Radio';
  document.getElementById('player-artist').textContent = 'Live Stream 🔴';
  refreshTrackRows();
  showToast('Connecting to live radio…');
}

function stopLiveRadio() {
  audio.pause(); audio.src = '';
  state.isPlaying = false;
  document.getElementById('play-icon').className = 'fas fa-play';
  showToast('Radio stopped.');
}

// ---- PLAYLIST ----
function openPlaylist(id) {
  const pl = state.playlists.find(p => p.id === id) || state.userPlaylists.find(p => p.id === id);
  if (!pl) return;
  openPlaylistByData(pl);
}

function openPlaylistByData(pl) {
  state.currentPlaylist = pl;
  const tks = tracks.filter(t => pl.tracks.includes(t.id));

  const heroImg = document.getElementById('playlist-hero-img');
  heroImg.style.background = '';
  heroImg.style.borderRadius = '';
  heroImg.style.position = '';
  heroImg.style.overflow = '';
  heroImg.textContent = pl.emoji;
  document.getElementById('playlist-hero-title').textContent = pl.name;
  document.getElementById('playlist-hero-desc').textContent = pl.desc;
  document.getElementById('playlist-hero-meta').textContent = `${tks.length} songs`;
  document.getElementById('playlist-hero').style.background = `linear-gradient(180deg, ${tks[0]?.color || '#1a3a2a'}50 0%, #121212 100%)`;

  document.getElementById('playlist-tracks').innerHTML = tks.length
    ? tks.map((t,i) => trackRow(t,i)).join('')
    : '<p style="color:var(--text-muted);padding:20px;">No tracks in this playlist yet.</p>';

  showPage('playlist', null);
}

function playPlaylist() {
  if (!state.currentPlaylist) return;
  const tks = tracks.filter(t => state.currentPlaylist.tracks.includes(t.id));
  if (!tks.length) return;
  state.queue = [...tks];
  playTrack(tks[0].id);
}

function playPlaylistById(id) {
  const pl = state.playlists.find(p => p.id === id) || state.userPlaylists.find(p => p.id === id);
  if (!pl) return;
  state.currentPlaylist = pl;
  playPlaylist();
}

function shufflePlaylist() {
  state.shuffle = true;
  document.getElementById('btn-shuffle').classList.add('active');
  playPlaylist();
  shuffleQueue();
  showToast('Shuffle on');
}

// ---- TRACK PLAYBACK ----
function playTrack(id) {
  const t = tracks.find(x => x.id === id);
  if (!t) return;
  state.queueSource = 'stream';
  state.currentTrack = t;
  state.currentIndex = state.queue.findIndex(x => x.id === id);
  if (state.currentIndex === -1) {
    state.queue = [...tracks];
    state.currentIndex = state.queue.findIndex(x => x.id === id);
  }

  // --- Real audio from stream.php ---
  clearInterval(simInterval);
  audio.src = t.src || ('stream.php?id=' + t.id);
  audio.load();
  audio.playbackRate = state.playbackRate;
  audio.play().catch(() => {});
  state.isPlaying = true;
  document.getElementById('play-icon').className = 'fas fa-pause';
  const npmIconStart = document.getElementById('npm-play-icon');
  if (npmIconStart) npmIconStart.className = 'fas fa-pause';
  updatePlaybackSpeedUI();

  // --- Player bar thumbnail ---
  const thumbEl = document.getElementById('player-thumb');
  thumbEl.style.background = (t.color||'#1a3a2a') + '30';
  thumbEl.innerHTML = `<img src="api.php?action=thumbnail&id=${t.id}&size=sm" decoding="async"
      style="width:100%;height:100%;object-fit:cover;border-radius:6px;"
      onerror="this.replaceWith(Object.assign(document.createElement('span'),{style:'font-size:1.4rem;',textContent:'${t.emoji||'🎵'}'}))"/>`;

  document.getElementById('player-title').textContent = t.title;
  document.getElementById('player-artist').textContent = t.artist;
  document.getElementById('player-heart').classList.toggle('liked', state.likedTracks.has(t.id));

  // --- Now playing page ---
  const npArt = document.getElementById('np-album-art');
  npArt.style.background = (t.color||'#1a3a2a') + '30';
  npArt.innerHTML = `<img src="api.php?action=thumbnail&id=${t.id}" decoding="async"
      style="width:100%;height:100%;object-fit:cover;border-radius:16px;"
      onerror="this.replaceWith(Object.assign(document.createElement('span'),{style:'font-size:6rem;',textContent:'${t.emoji||'🎵'}'}))"/>`;

  document.getElementById('np-title').textContent  = t.title;
  document.getElementById('np-artist').textContent = t.artist;
  document.getElementById('lyrics-display').innerHTML = t.lyrics
    ? t.lyrics.split('\n').map(l => `<p style="margin:2px 0;">${l || '&nbsp;'}</p>`).join('')
    : '<p style="color:var(--text-muted);">Lyrics not available for this track.</p>';

  // --- Now playing page (mobile swipeable Song/Lyrics view) ---
  const npmArt = document.getElementById('npm-album-art');
  if (npmArt) {
    npmArt.style.background = (t.color||'#1a3a2a') + '30';
    npmArt.innerHTML = `<img src="api.php?action=thumbnail&id=${t.id}" decoding="async"
        style="width:100%;height:100%;object-fit:cover;border-radius:16px;"
        onerror="this.replaceWith(Object.assign(document.createElement('span'),{style:'font-size:5rem;',textContent:'${t.emoji||'🎵'}'}))"/>`;
    document.getElementById('npm-title').textContent  = t.title;
    document.getElementById('npm-artist').textContent = t.artist;
    document.getElementById('npm-heart').classList.toggle('liked', state.likedTracks.has(t.id));
    document.getElementById('npm-lyrics-title').textContent  = t.title;
    document.getElementById('npm-lyrics-artist').textContent = t.artist;
    document.getElementById('npm-lyrics-display').innerHTML = t.lyrics
      ? t.lyrics.split('\n').map(l => `<p style="margin:2px 0;">${l || '&nbsp;'}</p>`).join('')
      : '<p style="color:var(--text-muted);">Lyrics not available for this track.</p>';
  }

  updateQueuePanel();
  renderUpNext();
  refreshTrackRows();
  updateMiniPlayer();
  showToast('Now playing: ' + t.title);
}

// Real audio drives progress via 'timeupdate' listener.
// Simulated progress kept as no-op for backward compat:
let simInterval = null;
let simProgress = 0;
function simulateProgress() { /* unused — real audio used */ }

function togglePlay() {
  if (!state.currentTrack) { if (tracks.length) playTrack(tracks[0].id); return; }
  state.isPlaying = !state.isPlaying;
  const playCls = state.isPlaying ? 'fas fa-pause' : 'fas fa-play';
  document.getElementById('play-icon').className = playCls;
  const npmIcon = document.getElementById('npm-play-icon');
  if (npmIcon) npmIcon.className = playCls;
  if (audio.src) { state.isPlaying ? audio.play() : audio.pause(); }
  refreshTrackRows();
}

// Plays a track from the current queue, dispatching to the player that
// matches where the queue's audio comes from (server stream, an offline
// IndexedDB download, or a local file picked from the device).
function playQueueTrack(id) {
  if (state.queueSource === 'offline') { playOfflineTrack(id); return; }
  if (state.queueSource === 'local') {
    const idx = state.localFiles.findIndex(f => f.id === id);
    if (idx !== -1) playLocalFile(idx);
    return;
  }
  playTrack(id);
}

function nextTrack() {
  if (!state.queue.length) return;
  let idx = state.currentIndex + 1;
  if (idx >= state.queue.length) idx = 0;
  state.currentIndex = idx;
  playQueueTrack(state.queue[idx].id);
}

function prevTrack() {
  if (audio.currentTime > 3) { audio.currentTime = 0; return; }
  if (!state.queue.length) return;
  let idx = state.currentIndex - 1;
  if (idx < 0) idx = state.queue.length - 1;
  state.currentIndex = idx;
  playQueueTrack(state.queue[idx].id);
}

function onTrackEnded() {
  if (state.repeat === 'one') { audio.currentTime = 0; audio.play().catch(() => {}); return; }
  if (state.repeat === 'all' || state.settings.autoplay) { nextTrack(); return; }
  if (state.currentIndex < state.queue.length - 1) nextTrack();
}

function updateProgress() {
  if (!audio.duration) return;
  const pct = (audio.currentTime / audio.duration) * 100;
  document.getElementById('progress-fill').style.width = pct + '%';
  document.getElementById('current-time').textContent = formatTime(audio.currentTime);
  document.getElementById('total-time').textContent = formatTime(audio.duration);
  const npmFill = document.getElementById('npm-progress-fill');
  if (npmFill) {
    npmFill.style.width = pct + '%';
    document.getElementById('npm-current-time').textContent = formatTime(audio.currentTime);
    document.getElementById('npm-total-time').textContent = formatTime(audio.duration);
  }
}

function seekTrack(e) {
  const bar  = document.getElementById('progress-bar');
  const rect = bar.getBoundingClientRect();
  const pct  = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width));
  if (audio.duration) {
    audio.currentTime = pct * audio.duration;
  }
  document.getElementById('progress-fill').style.width = (pct * 100) + '%';
}

// ---- Mobile "Now Playing" swipeable Song/Lyrics panels ----
function seekTrackNP(e) {
  const bar  = document.getElementById('npm-progress-bar');
  const rect = bar.getBoundingClientRect();
  const pct  = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width));
  if (audio.duration) audio.currentTime = pct * audio.duration;
  document.getElementById('npm-progress-fill').style.width = (pct * 100) + '%';
}

function npGoToTab(idx) {
  const wrap = document.getElementById('np-swipe');
  wrap.scrollTo({ left: idx * wrap.clientWidth, behavior: 'smooth' });
}

function onNpSwipeScroll() {
  const wrap = document.getElementById('np-swipe');
  const idx = Math.round(wrap.scrollLeft / wrap.clientWidth);
  document.getElementById('np-tab-song').classList.toggle('active', idx === 0);
  document.getElementById('np-tab-lyrics').classList.toggle('active', idx === 1);
}

function toggleShuffle() {
  state.shuffle = !state.shuffle;
  document.getElementById('btn-shuffle').classList.toggle('active', state.shuffle);
  const npmShuffle = document.getElementById('npm-btn-shuffle');
  if (npmShuffle) npmShuffle.classList.toggle('active', state.shuffle);
  if (state.shuffle) shuffleQueue(); else state.queue = [...state.originalQueue];
  showToast(state.shuffle ? 'Shuffle on' : 'Shuffle off');
}

function shuffleQueue() {
  state.queue = [...state.queue].sort(() => Math.random() - 0.5);
  state.currentIndex = state.queue.findIndex(t => t.id === state.currentTrack?.id);
}

function toggleRepeat() {
  const modes = ['off','all','one'];
  const i = modes.indexOf(state.repeat);
  state.repeat = modes[(i + 1) % 3];
  const repeatHTML = state.repeat === 'one' ? '<i class="fas fa-redo" style="position:relative;"><span style="position:absolute;top:-4px;right:-6px;font-size:8px;background:var(--green);color:#000;border-radius:50%;width:12px;height:12px;display:flex;align-items:center;justify-content:center;font-weight:900;">1</span></i>' : '<i class="fas fa-redo"></i>';
  const btn = document.getElementById('btn-repeat');
  btn.classList.toggle('active', state.repeat !== 'off');
  btn.innerHTML = repeatHTML;
  const npmBtn = document.getElementById('npm-btn-repeat');
  if (npmBtn) {
    npmBtn.classList.toggle('active', state.repeat !== 'off');
    npmBtn.innerHTML = repeatHTML;
  }
  showToast(state.repeat === 'off' ? 'Repeat off' : state.repeat === 'all' ? 'Repeat all' : 'Repeat one');
}

function setVolume(v) {
  state.volume = v / 100;
  audio.volume = state.volume;
  state.muted = false;
  const icon = document.getElementById('vol-icon');
  icon.className = v == 0 ? 'fas fa-volume-mute ctrl-btn' : v < 50 ? 'fas fa-volume-down ctrl-btn' : 'fas fa-volume-up ctrl-btn';
}

function toggleMute() {
  state.muted = !state.muted;
  audio.muted = state.muted;
  document.getElementById('vol-icon').className = state.muted ? 'fas fa-volume-mute ctrl-btn' : 'fas fa-volume-up ctrl-btn';
}

// ---- PLAYBACK SPEED ----
function setPlaybackRate(rate) {
  state.playbackRate = rate;
  audio.playbackRate = rate;
  updatePlaybackSpeedUI();
  showToast(`Playback speed: ${rate}x`);
}

function updatePlaybackSpeedUI() {
  document.querySelectorAll('.np-speed-btn').forEach(btn => {
    btn.classList.toggle('active', parseFloat(btn.dataset.rate) === state.playbackRate);
  });
}

// ---- QUEUE PANEL ----
function toggleQueue() {
  const q = document.getElementById('queue-panel');
  q.classList.toggle('open');
  updateQueuePanel();
}

function updateQueuePanel() {
  const now  = document.getElementById('queue-now');
  const list = document.getElementById('queue-list');
  if (state.currentTrack) {
    const t = state.currentTrack;
    now.innerHTML = `
      <div class="track-row" style="background:#2a2a2a;border-radius:6px;">
        <div class="track-thumb" style="overflow:hidden;">
          <img src="api.php?action=thumbnail&id=${t.id}&size=sm" loading="lazy" decoding="async" style="width:100%;height:100%;object-fit:cover;border-radius:4px;"
               onerror="this.replaceWith(Object.assign(document.createElement('span'),{style:'font-size:1.2rem;',textContent:'${t.emoji||'🎵'}'}))"/>
        </div>
        <div class="track-info">
          <div class="track-name" style="color:var(--green);">${t.title}</div>
          <div class="track-artist">${t.artist}</div>
        </div>
      </div>`;
  }
  const upcoming = state.queue.slice(state.currentIndex + 1, state.currentIndex + 11);
  list.innerHTML = upcoming.map(t => `
    <div class="track-row" onclick="playTrack(${t.id})">
      <div class="track-thumb" style="overflow:hidden;">
        <img src="api.php?action=thumbnail&id=${t.id}&size=sm" loading="lazy" decoding="async" style="width:100%;height:100%;object-fit:cover;border-radius:4px;"
             onerror="this.replaceWith(Object.assign(document.createElement('span'),{style:'font-size:1.2rem;',textContent:'${t.emoji||'🎵'}'}))"/>
      </div>
      <div class="track-info">
        <div class="track-name">${t.title}</div>
        <div class="track-artist">${t.artist}</div>
      </div>
    </div>
  `).join('') || '<p style="color:var(--text-muted);font-size:0.82rem;padding:12px;">Queue is empty</p>';
}

function renderUpNext() {
  const upcoming = state.queue.slice(state.currentIndex + 1, state.currentIndex + 5);
  document.getElementById('up-next-list').innerHTML = upcoming.map(t => `
    <div class="track-row" onclick="playTrack(${t.id})">
      <div class="track-thumb" style="overflow:hidden;">
        <img src="api.php?action=thumbnail&id=${t.id}&size=sm" loading="lazy" decoding="async" style="width:100%;height:100%;object-fit:cover;border-radius:4px;"
             onerror="this.replaceWith(Object.assign(document.createElement('span'),{style:'font-size:1.2rem;',textContent:'${t.emoji||'🎵'}'}))"/>
      </div>
      <div class="track-info">
        <div class="track-name">${t.title}</div>
        <div class="track-artist">${t.artist}</div>
      </div>
      <div class="track-duration">${t.duration}</div>
    </div>
  `).join('') || '<p style="color:var(--text-muted);font-size:0.82rem;">No more tracks</p>';
}

// ---- LIKES (require login — persisted server-side) ----
async function toggleLike(id) {
  if (!requireLogin('Log in to like songs')) return;

  const wasLiked = state.likedTracks.has(id);
  // Optimistic UI update
  if (wasLiked) state.likedTracks.delete(id);
  else { state.likedTracks.add(id); showToast('Added to Liked Songs'); }
  const pl = state.playlists.find(p => p.isLiked);
  if (pl) pl.tracks = [...state.likedTracks];
  refreshTrackRows();
  renderLibraryPage();
  renderSidebarPlaylists();

  try {
    const body = new URLSearchParams({ track_id: id });
    const res  = await fetch('api.php?action=' + (wasLiked ? 'unlike' : 'like'), { method: 'POST', body });
    if (!res.ok) throw new Error('failed');
  } catch (e) {
    // Roll back on failure
    if (wasLiked) state.likedTracks.add(id); else state.likedTracks.delete(id);
    if (pl) pl.tracks = [...state.likedTracks];
    refreshTrackRows();
    renderLibraryPage();
    renderSidebarPlaylists();
    showToast('Could not update like — try again');
  }
}

function toggleLikeCurrentTrack() {
  if (!state.currentTrack) return;
  toggleLike(state.currentTrack.id);
  const liked = state.likedTracks.has(state.currentTrack.id);
  document.getElementById('player-heart').classList.toggle('liked', liked);
  const npmHeart = document.getElementById('npm-heart');
  if (npmHeart) npmHeart.classList.toggle('liked', liked);
}

// Syncs the "now playing" highlight (and eq-bar animation state) onto every
// rendered track row/card across the whole app — home (featured & gospel,
// in both card and list view), search results, library "All Tracks",
// playlists, artist/channel pages, etc. — even ones not currently visible,
// so the indicator is correct the instant the user navigates to them.
function refreshTrackRows() {
  const curId = state.currentTrack ? state.currentTrack.id : null;
  const eqHTML = `<div class="eq-bars${state.isPlaying ? '' : ' paused'}"><div class="eq-bar"></div><div class="eq-bar"></div><div class="eq-bar"></div><div class="eq-bar"></div></div>`;

  document.querySelectorAll('.track-row[data-track-id]').forEach(row => {
    const playing = curId !== null && Number(row.dataset.trackId) === curId;
    row.classList.toggle('playing', playing);
    const numEl = row.querySelector('.track-num');
    if (numEl) numEl.innerHTML = playing ? eqHTML : numEl.dataset.index;
  });

  document.querySelectorAll('.card[data-track-id]').forEach(card => {
    const playing = curId !== null && Number(card.dataset.trackId) === curId;
    card.classList.toggle('playing', playing);
    const imgEl = card.querySelector('.card-img');
    let badge = card.querySelector('.card-now-playing-badge');
    if (playing) {
      if (!badge && imgEl) {
        imgEl.insertAdjacentHTML('afterbegin', `<div class="card-now-playing-badge">${eqHTML}</div>`);
      } else if (badge) {
        badge.innerHTML = eqHTML;
      }
    } else if (badge) {
      badge.remove();
    }
  });
}

// ---- PLAYLISTS ----
function renderSidebarPlaylists() {
  const container = document.getElementById('sidebar-playlists');
  const all = [...state.playlists, ...state.userPlaylists];
  container.innerHTML = all.map(pl => `
    <div class="playlist-item ${state.currentPlaylist?.id === pl.id ? 'active' : ''}" onclick="openPlaylist('${pl.id}')">
      <div class="playlist-item-img">${pl.emoji}</div>
      <span>${pl.name}</span>
    </div>
  `).join('');
}

function openCreatePlaylist() {
  if (!requireLogin('Log in to create playlists')) return;
  document.getElementById('modal-create-playlist').classList.add('open');
  document.getElementById('new-playlist-name').value = `My Playlist #${state.userPlaylists.length + 1}`;
  document.getElementById('new-playlist-desc').value = '';
}

async function createPlaylist() {
  if (!requireLogin('Log in to create playlists')) return;
  const name = document.getElementById('new-playlist-name').value.trim() || `Playlist #${state.userPlaylists.length + 1}`;
  const desc = document.getElementById('new-playlist-desc').value.trim();

  try {
    const body = new URLSearchParams({ name, description: desc, emoji: '🎵' });
    const res  = await fetch('api.php?action=create_playlist', { method: 'POST', body });
    const d    = await res.json();
    if (!res.ok || d.error) { showToast(d.error || 'Could not create playlist'); return; }

    state.userPlaylists = (d.playlists || []).map(pl => ({
      id: 'u' + pl.id, dbId: pl.id, name: pl.name, desc: pl.description,
      tracks: pl.tracks || [], emoji: pl.emoji, mine: true,
    }));
    renderSidebarPlaylists();
    renderLibraryPage();
    closeModal('modal-create-playlist');
    showToast(`Playlist "${name}" created!`);
    openPlaylist('u' + d.id);
  } catch (e) { showToast('Network error — please try again'); }
}

async function deleteUserPlaylist(plId) {
  const pl = state.userPlaylists.find(p => p.id === plId);
  if (!pl) return;
  try {
    const body = new URLSearchParams({ id: pl.dbId });
    const res  = await fetch('api.php?action=delete_playlist', { method: 'POST', body });
    const d    = await res.json();
    if (!res.ok || d.error) { showToast(d.error || 'Could not delete playlist'); return; }

    state.userPlaylists = (d.playlists || []).map(p => ({
      id: 'u' + p.id, dbId: p.id, name: p.name, desc: p.description,
      tracks: p.tracks || [], emoji: p.emoji, mine: true,
    }));
    if (state.currentPlaylist?.id === plId) showPage('library', null);
    renderSidebarPlaylists();
    renderLibraryPage();
    showToast(`Playlist "${pl.name}" deleted`);
  } catch (e) { showToast('Network error — please try again'); }
}

function openAddToPlaylist(trackId) {
  if (!requireLogin('Log in to add songs to playlists')) return;
  state.addToPlaylistTrackId = trackId;
  const list = document.getElementById('add-to-playlist-list');
  list.innerHTML = state.userPlaylists.length ? state.userPlaylists.map(pl => `
    <div class="track-row" onclick="addTrackToPlaylist('${pl.id}')">
      <div class="track-thumb">${pl.emoji}</div>
      <div class="track-info">
        <div class="track-name">${pl.name}</div>
        <div class="track-artist">${pl.tracks.length} songs</div>
      </div>
      <i class="fas fa-plus track-action-btn"></i>
    </div>
  `).join('') : '<p style="color:var(--text-muted);font-size:0.85rem;">You have no playlists yet. Create one from your Library first.</p>';
  document.getElementById('modal-add-to-playlist').classList.add('open');
}

async function addTrackToPlaylist(plId) {
  const pl = state.userPlaylists.find(p => p.id === plId);
  if (!pl) return;
  if (pl.tracks.includes(state.addToPlaylistTrackId)) { showToast('Already in playlist'); closeModal('modal-add-to-playlist'); return; }

  try {
    const body = new URLSearchParams({ playlist_id: pl.dbId, track_id: state.addToPlaylistTrackId });
    const res  = await fetch('api.php?action=playlist_add_track', { method: 'POST', body });
    const d    = await res.json();
    if (!res.ok || d.error) { showToast(d.error || 'Could not add to playlist'); return; }

    state.userPlaylists = (d.playlists || []).map(p => ({
      id: 'u' + p.id, dbId: p.id, name: p.name, desc: p.description,
      tracks: p.tracks || [], emoji: p.emoji, mine: true,
    }));
    closeModal('modal-add-to-playlist');
    showToast(`Added to ${pl.name}`);
    renderLibraryPage();
    if (state.currentPlaylist?.id === plId) {
      const updated = state.userPlaylists.find(p => p.id === plId);
      if (updated) openPlaylistByData(updated);
    }
  } catch (e) { showToast('Network error — please try again'); }
}

// ---- LYRICS ----
function downloadLyrics() { downloadCurrentLyrics(); }

function downloadCurrentLyrics() {
  const t = state.currentTrack;
  if (!t) { showToast('No track playing'); return; }
  const content = `${t.title}\nBy ${t.artist}\nAlbum: ${t.album}\n\n${t.lyrics || 'Lyrics not available.'}`;
  const blob = new Blob([content], { type: 'text/plain' });
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = `${t.title} - Lyrics.txt`;
  a.click();
  showToast('Lyrics downloaded!');
}

function downloadTrackLyrics(id) {
  const t = tracks.find(x => x.id === id);
  if (!t) return;
  const content = `${t.title}\nBy ${t.artist}\nAlbum: ${t.album}\n\n${t.lyrics || 'Lyrics not available.'}`;
  const blob = new Blob([content], { type: 'text/plain' });
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = `${t.title} - Lyrics.txt`;
  a.click();
  showToast('Lyrics downloaded!');
}

// ---- SHARE ----
function _shareBaseUrl() {
  const base = (siteSettings.share_base_url || '').trim();
  return base ? base.replace(/\/+$/, '') : window.location.origin + window.location.pathname;
}

function shareTrack(id) {
  const t = tracks.find(x => x.id === id);
  document.getElementById('share-url').textContent = `${_shareBaseUrl()}?track=${id}&title=${encodeURIComponent(t?.title || '')}`;
  document.getElementById('modal-share').classList.add('open');
}

function sharePlaylist() {
  const pl = state.currentPlaylist;
  document.getElementById('share-url').textContent = `${_shareBaseUrl()}?playlist=${pl?.id}&title=${encodeURIComponent(pl?.name || '')}`;
  document.getElementById('modal-share').classList.add('open');
}

function copyShareUrl() {
  const url = document.getElementById('share-url').textContent;
  navigator.clipboard.writeText(url).then(() => showToast('Link copied!')).catch(() => showToast('Copy failed'));
}

// ---- SLEEP TIMER ----
function toggleSleepMenu() { document.getElementById('sleep-menu').classList.toggle('open'); }
function openSleepTimerModal() { document.getElementById('modal-sleep-timer').classList.add('open'); }

function setSleep(min) {
  clearTimeout(state.sleepTimer);
  const menu = document.getElementById('sleep-menu');
  if (menu) menu.classList.remove('open');
  closeModal('modal-sleep-timer');
  if (!min) { showToast('Sleep timer off'); return; }
  showToast(`Sleep timer set for ${min} min`);
  state.sleepTimer = setTimeout(() => { audio.pause(); state.isPlaying = false; document.getElementById('play-icon').className = 'fas fa-play'; refreshTrackRows(); showToast('Sleep timer: playback stopped'); }, min * 60000);
}

// ---- SETTINGS ----
function openSettings() {
  document.getElementById('modal-settings').classList.add('open');
  document.getElementById('setting-language').value = state.settings.language;
  closeProfileMenu();
}
function updateSetting(key, val) { state.settings[key] = val; }

// ---- LANGUAGE / TRANSLATIONS ----
const I18N = {
  en: {
    nav_home: 'Home', nav_search: 'Search', nav_library: 'Your Library', nav_videos: 'Videos',
    nav_live: 'Live Stream', nav_artists: 'Artists', nav_offline: 'Offline',
    nav_library_short: 'Library', nav_live_short: 'Live',
    sidebar_playlists: 'Playlists',
    search_placeholder: 'What do you want to listen to?',
    search_browse_genres: 'Browse Genres',
    greeting_prefix: 'Good', greeting_listener: 'Listener',
    greeting_morning: 'morning', greeting_afternoon: 'afternoon', greeting_evening: 'evening',
    home_featured_tracks: 'Featured Tracks', home_popular_artists: 'Popular Artists',
    home_gospel_collection: 'Gospel Collection', see_all: 'See all',
    library_subtitle: 'Your playlists, liked songs & more',
    library_create_playlist: 'Create Playlist',
    profile_profile: 'Profile', profile_liked_songs: 'Liked Songs', profile_logout: 'Log out',
    settings_title: 'Settings', settings_crossfade: 'Crossfade',
    settings_crossfade_desc: 'Smooth transition between songs',
    settings_autoplay: 'Autoplay', settings_autoplay_desc: 'Keep playing similar music when queue ends',
    settings_lyrics: 'Show lyrics', settings_lyrics_desc: 'Display lyrics in now playing',
    settings_quality: 'Audio quality', settings_language: 'Language',
  },
  fr: {
    nav_home: 'Accueil', nav_search: 'Recherche', nav_library: 'Votre bibliothèque', nav_videos: 'Vidéos',
    nav_live: 'Diffusion en direct', nav_artists: 'Artistes', nav_offline: 'Hors ligne',
    nav_library_short: 'Bibliothèque', nav_live_short: 'En direct',
    sidebar_playlists: 'Playlists',
    search_placeholder: 'Que voulez-vous écouter ?',
    search_browse_genres: 'Parcourir les genres',
    greeting_prefix: 'Bon', greeting_listener: 'Auditeur',
    greeting_morning: 'matin', greeting_afternoon: 'après-midi', greeting_evening: 'soir',
    home_featured_tracks: 'Titres en vedette', home_popular_artists: 'Artistes populaires',
    home_gospel_collection: 'Collection gospel', see_all: 'Tout voir',
    library_subtitle: 'Vos playlists, titres aimés et plus encore',
    library_create_playlist: 'Créer une playlist',
    profile_profile: 'Profil', profile_liked_songs: 'Titres aimés', profile_logout: 'Se déconnecter',
    settings_title: 'Paramètres', settings_crossfade: 'Fondu enchaîné',
    settings_crossfade_desc: 'Transition fluide entre les morceaux',
    settings_autoplay: 'Lecture automatique', settings_autoplay_desc: 'Continuer à jouer une musique similaire à la fin de la file',
    settings_lyrics: 'Afficher les paroles', settings_lyrics_desc: 'Afficher les paroles pendant la lecture',
    settings_quality: 'Qualité audio', settings_language: 'Langue',
  },
  es: {
    nav_home: 'Inicio', nav_search: 'Buscar', nav_library: 'Tu biblioteca', nav_videos: 'Vídeos',
    nav_live: 'Transmisión en vivo', nav_artists: 'Artistas', nav_offline: 'Sin conexión',
    nav_library_short: 'Biblioteca', nav_live_short: 'En vivo',
    sidebar_playlists: 'Listas de reproducción',
    search_placeholder: '¿Qué quieres escuchar?',
    search_browse_genres: 'Explorar géneros',
    greeting_prefix: 'Buenas', greeting_listener: 'Oyente',
    greeting_morning: 'días', greeting_afternoon: 'tardes', greeting_evening: 'noches',
    home_featured_tracks: 'Pistas destacadas', home_popular_artists: 'Artistas populares',
    home_gospel_collection: 'Colección gospel', see_all: 'Ver todo',
    library_subtitle: 'Tus listas, canciones que te gustan y más',
    library_create_playlist: 'Crear lista',
    profile_profile: 'Perfil', profile_liked_songs: 'Canciones que te gustan', profile_logout: 'Cerrar sesión',
    settings_title: 'Configuración', settings_crossfade: 'Transición',
    settings_crossfade_desc: 'Transición suave entre canciones',
    settings_autoplay: 'Reproducción automática', settings_autoplay_desc: 'Seguir reproduciendo música similar al terminar la cola',
    settings_lyrics: 'Mostrar letras', settings_lyrics_desc: 'Mostrar la letra en reproducción',
    settings_quality: 'Calidad de audio', settings_language: 'Idioma',
  },
  yo: {
    nav_home: 'Ile', nav_search: 'Wádìí', nav_library: 'Ibi Ìkówèésí Rẹ', nav_videos: 'Fídíò',
    nav_live: 'Ìfilọ́lẹ̀ Tààràtà', nav_artists: 'Awọn Akọrin', nav_offline: 'Aisinipo',
    nav_library_short: 'Ìkówèésí', nav_live_short: 'Tààràtà',
    sidebar_playlists: 'Àtòjọ Orin',
    search_placeholder: 'Kí ni o fẹ́ gbọ́?',
    search_browse_genres: 'Wo Onírúurú Orin',
    greeting_prefix: 'Ẹ kú', greeting_listener: 'Olùgbọ́',
    greeting_morning: 'àárọ̀', greeting_afternoon: 'ọ̀sán', greeting_evening: 'ìrọ̀lẹ́',
    home_featured_tracks: 'Àwọn Orin Pàtàkì', home_popular_artists: 'Àwọn Akọrin Gbajúmọ̀',
    home_gospel_collection: 'Àkójọ Orin Ìhìnrere', see_all: 'Wo gbogbo rẹ̀',
    library_subtitle: 'Àwọn àtòjọ orin rẹ, orin tó o fẹ́ràn àti síwájú sí i',
    library_create_playlist: 'Ṣẹ̀dá Àtòjọ Orin',
    profile_profile: 'Profaìlì', profile_liked_songs: 'Orin Tí O Fẹ́ràn', profile_logout: 'Jáde',
    settings_title: 'Ètò', settings_crossfade: 'Ìrékọjá Orin',
    settings_crossfade_desc: 'Ìyípadà rírọ̀rùn láàrin àwọn orin',
    settings_autoplay: 'Ìṣeré Aládàáṣiṣẹ́', settings_autoplay_desc: 'Máa bá a nìṣó pẹ̀lú irú orin kan náà nígbà tí àtòjọ bá parí',
    settings_lyrics: 'Fi Ọ̀rọ̀ Orin Hàn', settings_lyrics_desc: 'Ṣe àfihàn ọ̀rọ̀ orin nígbà ìṣeré',
    settings_quality: 'Ìdára Ìró', settings_language: 'Èdè',
  },
  ig: {
    nav_home: 'Ụlọ', nav_search: 'Chọọ', nav_library: 'Ọbá Akwụkwọ Gị', nav_videos: 'Vidiyo',
    nav_live: 'Mgbasa Ozi Dị Ndụ', nav_artists: 'Ndị Egwu', nav_offline: 'Na-anọghị n\'ịntanetị',
    nav_library_short: 'Ọbá Akwụkwọ', nav_live_short: 'Dị Ndụ',
    sidebar_playlists: 'Ndepụta Egwu',
    search_placeholder: 'Kedụ ihe ị chọrọ ige?',
    search_browse_genres: 'Chọọ Ụdị Egwu',
    greeting_prefix: 'Ụtụtụ', greeting_listener: 'Onye Na-ege Ntị',
    greeting_morning: 'ọma', greeting_afternoon: 'ehihie ọma', greeting_evening: 'mgbede ọma',
    home_featured_tracks: 'Egwu Ndị A Họọrọ', home_popular_artists: 'Ndị Egwu A Ma Ama',
    home_gospel_collection: 'Nchịkọta Egwu Ozi Ọma', see_all: 'Hụ ihe niile',
    library_subtitle: 'Ndepụta egwu gị, egwu ị hụrụ n\'anya na ihe ọzọ',
    library_create_playlist: 'Mepụta Ndepụta Egwu',
    profile_profile: 'Profaịlụ', profile_liked_songs: 'Egwu Ị Hụrụ N\'anya', profile_logout: 'Pụọ',
    settings_title: 'Ntọala', settings_crossfade: 'Ngafe Egwu',
    settings_crossfade_desc: 'Mgbanwe dị nro n\'etiti egwu',
    settings_autoplay: 'Akpaghachi Onwe Ya', settings_autoplay_desc: 'Gaa n\'ihu na-akpọ egwu yiri ya mgbe ndepụta gwụsịrị',
    settings_lyrics: 'Gosi Okwu Egwu', settings_lyrics_desc: 'Gosipụta okwu egwu mgbe ọ na-akpọ egwu',
    settings_quality: 'Ogo Ụda', settings_language: 'Asụsụ',
  },
  pt: {
    nav_home: 'Início', nav_search: 'Pesquisar', nav_library: 'Sua Biblioteca', nav_videos: 'Vídeos',
    nav_live: 'Transmissão ao Vivo', nav_artists: 'Artistas', nav_offline: 'Offline',
    nav_library_short: 'Biblioteca', nav_live_short: 'Ao Vivo',
    sidebar_playlists: 'Playlists',
    search_placeholder: 'O que você quer ouvir?',
    search_browse_genres: 'Explorar Gêneros',
    greeting_prefix: 'Bom', greeting_listener: 'Ouvinte',
    greeting_morning: 'dia', greeting_afternoon: 'tarde', greeting_evening: 'noite',
    home_featured_tracks: 'Faixas em Destaque', home_popular_artists: 'Artistas Populares',
    home_gospel_collection: 'Coleção Gospel', see_all: 'Ver tudo',
    library_subtitle: 'Suas playlists, músicas curtidas e mais',
    library_create_playlist: 'Criar Playlist',
    profile_profile: 'Perfil', profile_liked_songs: 'Músicas Curtidas', profile_logout: 'Sair',
    settings_title: 'Configurações', settings_crossfade: 'Crossfade',
    settings_crossfade_desc: 'Transição suave entre músicas',
    settings_autoplay: 'Reprodução automática', settings_autoplay_desc: 'Continuar tocando música parecida ao fim da fila',
    settings_lyrics: 'Mostrar letras', settings_lyrics_desc: 'Exibir letras durante a reprodução',
    settings_quality: 'Qualidade de áudio', settings_language: 'Idioma',
  },
  de: {
    nav_home: 'Startseite', nav_search: 'Suche', nav_library: 'Deine Bibliothek', nav_videos: 'Videos',
    nav_live: 'Live-Übertragung', nav_artists: 'Künstler', nav_offline: 'Offline',
    nav_library_short: 'Bibliothek', nav_live_short: 'Live',
    sidebar_playlists: 'Playlists',
    search_placeholder: 'Was möchtest du hören?',
    search_browse_genres: 'Genres durchsuchen',
    greeting_prefix: 'Guten', greeting_listener: 'Hörer',
    greeting_morning: 'Morgen', greeting_afternoon: 'Tag', greeting_evening: 'Abend',
    home_featured_tracks: 'Empfohlene Titel', home_popular_artists: 'Beliebte Künstler',
    home_gospel_collection: 'Gospel-Sammlung', see_all: 'Alle anzeigen',
    library_subtitle: 'Deine Playlists, gelikte Songs und mehr',
    library_create_playlist: 'Playlist erstellen',
    profile_profile: 'Profil', profile_liked_songs: 'Gelikte Songs', profile_logout: 'Abmelden',
    settings_title: 'Einstellungen', settings_crossfade: 'Überblendung',
    settings_crossfade_desc: 'Sanfter Übergang zwischen Songs',
    settings_autoplay: 'Automatische Wiedergabe', settings_autoplay_desc: 'Ähnliche Musik weiterspielen, wenn die Warteschlange endet',
    settings_lyrics: 'Songtexte anzeigen', settings_lyrics_desc: 'Songtexte bei der Wiedergabe anzeigen',
    settings_quality: 'Audioqualität', settings_language: 'Sprache',
  },
  ar: {
    dir: 'rtl',
    nav_home: 'الرئيسية', nav_search: 'بحث', nav_library: 'مكتبتك', nav_videos: 'فيديوهات',
    nav_live: 'بث مباشر', nav_artists: 'فنانون', nav_offline: 'غير متصل',
    nav_library_short: 'مكتبة', nav_live_short: 'مباشر',
    sidebar_playlists: 'قوائم التشغيل',
    search_placeholder: 'ماذا تريد أن تسمع؟',
    search_browse_genres: 'تصفح الأنواع',
    greeting_prefix: 'طاب', greeting_listener: 'أيها المستمع',
    greeting_morning: 'صباحك', greeting_afternoon: 'يومك', greeting_evening: 'مساؤك',
    home_featured_tracks: 'المقاطع المميزة', home_popular_artists: 'الفنانون المشهورون',
    home_gospel_collection: 'مجموعة الترانيم', see_all: 'عرض الكل',
    library_subtitle: 'قوائم التشغيل، الأغاني المفضلة والمزيد',
    library_create_playlist: 'إنشاء قائمة تشغيل',
    profile_profile: 'الملف الشخصي', profile_liked_songs: 'الأغاني المفضلة', profile_logout: 'تسجيل الخروج',
    settings_title: 'الإعدادات', settings_crossfade: 'التداخل الصوتي',
    settings_crossfade_desc: 'انتقال سلس بين الأغاني',
    settings_autoplay: 'التشغيل التلقائي', settings_autoplay_desc: 'متابعة تشغيل موسيقى مشابهة عند انتهاء القائمة',
    settings_lyrics: 'إظهار الكلمات', settings_lyrics_desc: 'عرض كلمات الأغنية أثناء التشغيل',
    settings_quality: 'جودة الصوت', settings_language: 'اللغة',
  },
  ha: {
    nav_home: 'Gida', nav_search: 'Bincike', nav_library: 'Laburarenka', nav_videos: 'Bidiyo',
    nav_live: 'Watsa Kai Tsaye', nav_artists: 'Mawaƙa', nav_offline: 'Babu Intanet',
    nav_library_short: 'Laburare', nav_live_short: 'Kai Tsaye',
    sidebar_playlists: 'Jerin Waƙoƙi',
    search_placeholder: 'Me kake son saurara?',
    search_browse_genres: 'Bincika Nau\'o\'i',
    greeting_prefix: 'Barka da', greeting_listener: 'Mai Saurare',
    greeting_morning: 'safiya', greeting_afternoon: 'rana', greeting_evening: 'yamma',
    home_featured_tracks: 'Fitattun Waƙoƙi', home_popular_artists: 'Shahararrun Mawaƙa',
    home_gospel_collection: 'Tarin Waƙoƙin Bishara', see_all: 'Duba duka',
    library_subtitle: 'Jerin waƙoƙinka, waƙoƙin da kake so da ƙari',
    library_create_playlist: 'Ƙirƙiri Jerin Waƙoƙi',
    profile_profile: 'Bayanin Sirri', profile_liked_songs: 'Waƙoƙin da Nake So', profile_logout: 'Fita',
    settings_title: 'Saituna', settings_crossfade: 'Haɗawar Waƙoƙi',
    settings_crossfade_desc: 'Sauyi mai sauƙi tsakanin waƙoƙi',
    settings_autoplay: 'Kunna Kai Tsaye', settings_autoplay_desc: 'Ci gaba da kunna irin waƙoƙin idan jeri ya ƙare',
    settings_lyrics: 'Nuna Kalmomin Waƙa', settings_lyrics_desc: 'Nuna kalmomin waƙa yayin kunnawa',
    settings_quality: 'Ingancin Sauti', settings_language: 'Harshe',
  },
  sw: {
    nav_home: 'Nyumbani', nav_search: 'Tafuta', nav_library: 'Maktaba Yako', nav_videos: 'Video',
    nav_live: 'Matangazo ya Moja kwa Moja', nav_artists: 'Wasanii', nav_offline: 'Nje ya Mtandao',
    nav_library_short: 'Maktaba', nav_live_short: 'Moja kwa Moja',
    sidebar_playlists: 'Orodha za Nyimbo',
    search_placeholder: 'Unataka kusikiliza nini?',
    search_browse_genres: 'Vinjari Aina',
    greeting_prefix: 'Habari za', greeting_listener: 'Msikilizaji',
    greeting_morning: 'asubuhi', greeting_afternoon: 'mchana', greeting_evening: 'jioni',
    home_featured_tracks: 'Nyimbo Maalum', home_popular_artists: 'Wasanii Maarufu',
    home_gospel_collection: 'Mkusanyiko wa Injili', see_all: 'Ona zote',
    library_subtitle: 'Orodha zako za nyimbo, nyimbo unazopenda na zaidi',
    library_create_playlist: 'Unda Orodha ya Nyimbo',
    profile_profile: 'Wasifu', profile_liked_songs: 'Nyimbo Ulizopenda', profile_logout: 'Toka',
    settings_title: 'Mipangilio', settings_crossfade: 'Mpito wa Sauti',
    settings_crossfade_desc: 'Mpito laini kati ya nyimbo',
    settings_autoplay: 'Kucheza Kiotomatiki', settings_autoplay_desc: 'Endelea kucheza muziki unaofanana foleni inapoisha',
    settings_lyrics: 'Onyesha Maneno', settings_lyrics_desc: 'Onyesha maneno ya wimbo wakati wa kucheza',
    settings_quality: 'Ubora wa Sauti', settings_language: 'Lugha',
  },
  zh: {
    nav_home: '主页', nav_search: '搜索', nav_library: '音乐库', nav_videos: '视频',
    nav_live: '直播', nav_artists: '艺术家', nav_offline: '离线',
    nav_library_short: '音乐库', nav_live_short: '直播',
    sidebar_playlists: '播放列表',
    search_placeholder: '你想听什么？',
    search_browse_genres: '浏览风格',
    greeting_prefix: '', greeting_listener: '听众',
    greeting_morning: '早上好', greeting_afternoon: '下午好', greeting_evening: '晚上好',
    home_featured_tracks: '精选歌曲', home_popular_artists: '热门艺术家',
    home_gospel_collection: '福音歌曲合集', see_all: '查看全部',
    library_subtitle: '你的播放列表、喜欢的歌曲等',
    library_create_playlist: '创建播放列表',
    profile_profile: '个人资料', profile_liked_songs: '喜欢的歌曲', profile_logout: '退出登录',
    settings_title: '设置', settings_crossfade: '交叉淡化',
    settings_crossfade_desc: '歌曲之间的平滑过渡',
    settings_autoplay: '自动播放', settings_autoplay_desc: '队列结束后继续播放相似的音乐',
    settings_lyrics: '显示歌词', settings_lyrics_desc: '播放时显示歌词',
    settings_quality: '音质', settings_language: '语言',
  },
  hi: {
    nav_home: 'होम', nav_search: 'खोजें', nav_library: 'आपकी लाइब्रेरी', nav_videos: 'वीडियो',
    nav_live: 'लाइव स्ट्रीम', nav_artists: 'कलाकार', nav_offline: 'ऑफ़लाइन',
    nav_library_short: 'लाइब्रेरी', nav_live_short: 'लाइव',
    sidebar_playlists: 'प्लेलिस्ट',
    search_placeholder: 'आप क्या सुनना चाहते हैं?',
    search_browse_genres: 'शैलियाँ ब्राउज़ करें',
    greeting_prefix: 'शुभ', greeting_listener: 'श्रोता',
    greeting_morning: 'प्रभात', greeting_afternoon: 'दोपहर', greeting_evening: 'संध्या',
    home_featured_tracks: 'चयनित गीत', home_popular_artists: 'लोकप्रिय कलाकार',
    home_gospel_collection: 'गॉस्पेल संग्रह', see_all: 'सभी देखें',
    library_subtitle: 'आपकी प्लेलिस्ट, पसंदीदा गाने और बहुत कुछ',
    library_create_playlist: 'प्लेलिस्ट बनाएं',
    profile_profile: 'प्रोफ़ाइल', profile_liked_songs: 'पसंदीदा गाने', profile_logout: 'लॉग आउट',
    settings_title: 'सेटिंग्स', settings_crossfade: 'क्रॉसफ़ेड',
    settings_crossfade_desc: 'गानों के बीच सहज परिवर्तन',
    settings_autoplay: 'ऑटोप्ले', settings_autoplay_desc: 'कतार समाप्त होने पर मिलता-जुलता संगीत बजाते रहें',
    settings_lyrics: 'गीत के बोल दिखाएं', settings_lyrics_desc: 'प्लेबैक के दौरान बोल दिखाएं',
    settings_quality: 'ऑडियो गुणवत्ता', settings_language: 'भाषा',
  },
  ru: {
    nav_home: 'Главная', nav_search: 'Поиск', nav_library: 'Ваша библиотека', nav_videos: 'Видео',
    nav_live: 'Прямой эфир', nav_artists: 'Артисты', nav_offline: 'Офлайн',
    nav_library_short: 'Библиотека', nav_live_short: 'Эфир',
    sidebar_playlists: 'Плейлисты',
    search_placeholder: 'Что хотите послушать?',
    search_browse_genres: 'Обзор жанров',
    greeting_prefix: 'Доброе', greeting_listener: 'Слушатель',
    greeting_morning: 'утро', greeting_afternoon: 'день', greeting_evening: 'вечер',
    home_featured_tracks: 'Рекомендуемые треки', home_popular_artists: 'Популярные артисты',
    home_gospel_collection: 'Госпел-коллекция', see_all: 'Смотреть все',
    library_subtitle: 'Ваши плейлисты, любимые песни и многое другое',
    library_create_playlist: 'Создать плейлист',
    profile_profile: 'Профиль', profile_liked_songs: 'Любимые песни', profile_logout: 'Выйти',
    settings_title: 'Настройки', settings_crossfade: 'Кроссфейд',
    settings_crossfade_desc: 'Плавный переход между песнями',
    settings_autoplay: 'Автовоспроизведение', settings_autoplay_desc: 'Продолжать играть похожую музыку по окончании очереди',
    settings_lyrics: 'Показывать текст песни', settings_lyrics_desc: 'Отображать текст песни во время воспроизведения',
    settings_quality: 'Качество звука', settings_language: 'Язык',
  },
  it: {
    nav_home: 'Home', nav_search: 'Cerca', nav_library: 'La tua libreria', nav_videos: 'Video',
    nav_live: 'Diretta streaming', nav_artists: 'Artisti', nav_offline: 'Offline',
    nav_library_short: 'Libreria', nav_live_short: 'Diretta',
    sidebar_playlists: 'Playlist',
    search_placeholder: 'Cosa vuoi ascoltare?',
    search_browse_genres: 'Esplora i generi',
    greeting_prefix: 'Buon', greeting_listener: 'Ascoltatore',
    greeting_morning: 'giorno', greeting_afternoon: 'pomeriggio', greeting_evening: 'sera',
    home_featured_tracks: 'Brani in evidenza', home_popular_artists: 'Artisti popolari',
    home_gospel_collection: 'Collezione Gospel', see_all: 'Vedi tutto',
    library_subtitle: 'Le tue playlist, brani preferiti e altro',
    library_create_playlist: 'Crea playlist',
    profile_profile: 'Profilo', profile_liked_songs: 'Brani preferiti', profile_logout: 'Esci',
    settings_title: 'Impostazioni', settings_crossfade: 'Dissolvenza incrociata',
    settings_crossfade_desc: 'Transizione fluida tra i brani',
    settings_autoplay: 'Riproduzione automatica', settings_autoplay_desc: 'Continua a riprodurre musica simile alla fine della coda',
    settings_lyrics: 'Mostra testi', settings_lyrics_desc: 'Mostra il testo durante la riproduzione',
    settings_quality: 'Qualità audio', settings_language: 'Lingua',
  },
  tr: {
    nav_home: 'Ana Sayfa', nav_search: 'Ara', nav_library: 'Kitaplığın', nav_videos: 'Videolar',
    nav_live: 'Canlı Yayın', nav_artists: 'Sanatçılar', nav_offline: 'Çevrimdışı',
    nav_library_short: 'Kitaplık', nav_live_short: 'Canlı',
    sidebar_playlists: 'Çalma Listeleri',
    search_placeholder: 'Ne dinlemek istersin?',
    search_browse_genres: 'Türlere Göz At',
    greeting_prefix: 'İyi', greeting_listener: 'Dinleyici',
    greeting_morning: 'sabahlar', greeting_afternoon: 'günler', greeting_evening: 'akşamlar',
    home_featured_tracks: 'Öne Çıkan Parçalar', home_popular_artists: 'Popüler Sanatçılar',
    home_gospel_collection: 'Gospel Koleksiyonu', see_all: 'Tümünü gör',
    library_subtitle: 'Çalma listelerin, beğendiğin şarkılar ve daha fazlası',
    library_create_playlist: 'Çalma Listesi Oluştur',
    profile_profile: 'Profil', profile_liked_songs: 'Beğenilen Şarkılar', profile_logout: 'Çıkış yap',
    settings_title: 'Ayarlar', settings_crossfade: 'Geçiş Efekti',
    settings_crossfade_desc: 'Şarkılar arası yumuşak geçiş',
    settings_autoplay: 'Otomatik oynatma', settings_autoplay_desc: 'Sıra bittiğinde benzer müzik çalmaya devam et',
    settings_lyrics: 'Şarkı sözlerini göster', settings_lyrics_desc: 'Çalarken şarkı sözlerini göster',
    settings_quality: 'Ses kalitesi', settings_language: 'Dil',
  },
  am: {
    nav_home: 'መነሻ', nav_search: 'ፈልግ', nav_library: 'ቤተ-መጻሕፍትህ', nav_videos: 'ቪዲዮዎች',
    nav_live: 'ቀጥታ ስርጭት', nav_artists: 'አርቲስቶች', nav_offline: 'ከመስመር ውጪ',
    nav_library_short: 'ቤተ-መጻሕፍት', nav_live_short: 'ቀጥታ',
    sidebar_playlists: 'ማጫወቻ ዝርዝሮች',
    search_placeholder: 'ምን መስማት ትፈልጋለህ?',
    search_browse_genres: 'ዘውጎችን ያስሱ',
    greeting_prefix: 'መልካም', greeting_listener: 'አድማጭ',
    greeting_morning: 'ጠዋት', greeting_afternoon: 'ቀን', greeting_evening: 'ማታ',
    home_featured_tracks: 'ተለይተው የቀረቡ ዘፈኖች', home_popular_artists: 'ታዋቂ አርቲስቶች',
    home_gospel_collection: 'የወንጌል ስብስብ', see_all: 'ሁሉንም ይመልከቱ',
    library_subtitle: 'ማጫወቻ ዝርዝሮችህ፣ የወደድካቸው ዘፈኖች እና ሌሎችም',
    library_create_playlist: 'ማጫወቻ ዝርዝር ፍጠር',
    profile_profile: 'መገለጫ', profile_liked_songs: 'የወደድካቸው ዘፈኖች', profile_logout: 'ውጣ',
    settings_title: 'ቅንብሮች', settings_crossfade: 'ክሮስፌድ',
    settings_crossfade_desc: 'በዘፈኖች መካከል ለስላሳ ሽግግር',
    settings_autoplay: 'ራስ-ሰር ማጫወት', settings_autoplay_desc: 'ወረፋው ሲያልቅ ተመሳሳይ ሙዚቃ ማጫወቱን ይቀጥሉ',
    settings_lyrics: 'ግጥም አሳይ', settings_lyrics_desc: 'በማጫወት ጊዜ ግጥም አሳይ',
    settings_quality: 'የድምጽ ጥራት', settings_language: 'ቋንቋ',
  },
  zu: {
    nav_home: 'Ikhaya', nav_search: 'Sesha', nav_library: 'Umtapo wakho', nav_videos: 'Amavidiyo',
    nav_live: 'Ukusakaza Bukhoma', nav_artists: 'Abaculi', nav_offline: 'Ungaxhunyiwe',
    nav_library_short: 'Umtapo', nav_live_short: 'Bukhoma',
    sidebar_playlists: 'Izinhlu zokudlala',
    search_placeholder: 'Yini ofuna ukuyilalela?',
    search_browse_genres: 'Buka Izinhlobo',
    greeting_prefix: 'Sawubona', greeting_listener: 'Mlaleli',
    greeting_morning: 'ekuseni', greeting_afternoon: 'emini', greeting_evening: 'kusihlwa',
    home_featured_tracks: 'Izingoma Eziqavile', home_popular_artists: 'Abaculi Abathandwayo',
    home_gospel_collection: 'Iqoqo Levangeli', see_all: 'Bona konke',
    library_subtitle: 'Izinhlu zakho zokudlala, izingoma ozithandayo nokunye',
    library_create_playlist: 'Dala Uhlu Lokudlala',
    profile_profile: 'Iphrofayela', profile_liked_songs: 'Izingoma Ozithandayo', profile_logout: 'Phuma',
    settings_title: 'Izilungiselelo', settings_crossfade: 'Ukuxubana kwemisindo',
    settings_crossfade_desc: 'Ukuguquka okushelelayo phakathi kwezingoma',
    settings_autoplay: 'Ukudlala ngokuzenzakalela', settings_autoplay_desc: 'Qhubeka ulalele umculo ofanayo lapho uhlu luphela',
    settings_lyrics: 'Bonisa amazwi engoma', settings_lyrics_desc: 'Bonisa amazwi engoma ngesikhathi sokudlala',
    settings_quality: 'Ikhwalithi yomsindo', settings_language: 'Ulimi',
  },
  af: {
    nav_home: 'Tuis', nav_search: 'Soek', nav_library: 'Jou Biblioteek', nav_videos: 'Video\'s',
    nav_live: 'Regstreekse Uitsending', nav_artists: 'Kunstenaars', nav_offline: 'Vanlyn',
    nav_library_short: 'Biblioteek', nav_live_short: 'Regstreeks',
    sidebar_playlists: 'Speellyste',
    search_placeholder: 'Wat wil jy luister?',
    search_browse_genres: 'Blaai deur Genres',
    greeting_prefix: 'Goeie', greeting_listener: 'Luisteraar',
    greeting_morning: 'môre', greeting_afternoon: 'middag', greeting_evening: 'naand',
    home_featured_tracks: 'Uitgeligte Liedjies', home_popular_artists: 'Gewilde Kunstenaars',
    home_gospel_collection: 'Evangelie-versameling', see_all: 'Sien almal',
    library_subtitle: 'Jou speellyste, gunsteling-liedjies en meer',
    library_create_playlist: 'Skep Speellys',
    profile_profile: 'Profiel', profile_liked_songs: 'Gunsteling-liedjies', profile_logout: 'Teken uit',
    settings_title: 'Instellings', settings_crossfade: 'Kruisvervaging',
    settings_crossfade_desc: 'Gladde oorgang tussen liedjies',
    settings_autoplay: 'Outomatiese aflewering', settings_autoplay_desc: 'Hou aan om soortgelyke musiek te speel wanneer die wagtou eindig',
    settings_lyrics: 'Wys lirieke', settings_lyrics_desc: 'Vertoon lirieke tydens terugspeel',
    settings_quality: 'Klankgehalte', settings_language: 'Taal',
  },
  ko: {
    nav_home: '홈', nav_search: '검색', nav_library: '내 라이브러리', nav_videos: '동영상',
    nav_live: '라이브 방송', nav_artists: '아티스트', nav_offline: '오프라인',
    nav_library_short: '라이브러리', nav_live_short: '라이브',
    sidebar_playlists: '플레이리스트',
    search_placeholder: '무엇을 듣고 싶으신가요?',
    search_browse_genres: '장르 둘러보기',
    greeting_prefix: '좋은', greeting_listener: '청취자',
    greeting_morning: '아침', greeting_afternoon: '오후', greeting_evening: '저녁',
    home_featured_tracks: '추천 트랙', home_popular_artists: '인기 아티스트',
    home_gospel_collection: '가스펠 컬렉션', see_all: '모두 보기',
    library_subtitle: '플레이리스트, 좋아요한 곡 등',
    library_create_playlist: '플레이리스트 만들기',
    profile_profile: '프로필', profile_liked_songs: '좋아요한 곡', profile_logout: '로그아웃',
    settings_title: '설정', settings_crossfade: '크로스페이드',
    settings_crossfade_desc: '곡 사이의 부드러운 전환',
    settings_autoplay: '자동 재생', settings_autoplay_desc: '대기열이 끝나면 비슷한 음악을 계속 재생',
    settings_lyrics: '가사 표시', settings_lyrics_desc: '재생 중 가사 표시',
    settings_quality: '음질', settings_language: '언어',
  },
  ja: {
    nav_home: 'ホーム', nav_search: '検索', nav_library: 'マイライブラリ', nav_videos: '動画',
    nav_live: 'ライブ配信', nav_artists: 'アーティスト', nav_offline: 'オフライン',
    nav_library_short: 'ライブラリ', nav_live_short: 'ライブ',
    sidebar_playlists: 'プレイリスト',
    search_placeholder: '何を聴きたいですか？',
    search_browse_genres: 'ジャンルを見る',
    greeting_prefix: '', greeting_listener: 'リスナー',
    greeting_morning: 'おはようございます', greeting_afternoon: 'こんにちは', greeting_evening: 'こんばんは',
    home_featured_tracks: '注目のトラック', home_popular_artists: '人気アーティスト',
    home_gospel_collection: 'ゴスペルコレクション', see_all: 'すべて表示',
    library_subtitle: 'プレイリスト、好きな曲など',
    library_create_playlist: 'プレイリストを作成',
    profile_profile: 'プロフィール', profile_liked_songs: '好きな曲', profile_logout: 'ログアウト',
    settings_title: '設定', settings_crossfade: 'クロスフェード',
    settings_crossfade_desc: '曲間のスムーズな切り替え',
    settings_autoplay: '自動再生', settings_autoplay_desc: 'キューが終了したら似た音楽を再生し続ける',
    settings_lyrics: '歌詞を表示', settings_lyrics_desc: '再生中に歌詞を表示する',
    settings_quality: '音質', settings_language: '言語',
  },
  nl: {
    nav_home: 'Start', nav_search: 'Zoeken', nav_library: 'Jouw bibliotheek', nav_videos: 'Video\'s',
    nav_live: 'Livestream', nav_artists: 'Artiesten', nav_offline: 'Offline',
    nav_library_short: 'Bibliotheek', nav_live_short: 'Live',
    sidebar_playlists: 'Afspeellijsten',
    search_placeholder: 'Wat wil je beluisteren?',
    search_browse_genres: 'Blader door genres',
    greeting_prefix: 'Goede', greeting_listener: 'Luisteraar',
    greeting_morning: 'morgen', greeting_afternoon: 'middag', greeting_evening: 'avond',
    home_featured_tracks: 'Uitgelichte nummers', home_popular_artists: 'Populaire artiesten',
    home_gospel_collection: 'Gospelcollectie', see_all: 'Alles bekijken',
    library_subtitle: 'Jouw afspeellijsten, leuk gevonden nummers en meer',
    library_create_playlist: 'Afspeellijst maken',
    profile_profile: 'Profiel', profile_liked_songs: 'Leuk gevonden nummers', profile_logout: 'Uitloggen',
    settings_title: 'Instellingen', settings_crossfade: 'Crossfade',
    settings_crossfade_desc: 'Vloeiende overgang tussen nummers',
    settings_autoplay: 'Automatisch afspelen', settings_autoplay_desc: 'Blijf vergelijkbare muziek afspelen als de wachtrij eindigt',
    settings_lyrics: 'Songteksten tonen', settings_lyrics_desc: 'Toon songteksten tijdens het afspelen',
    settings_quality: 'Audiokwaliteit', settings_language: 'Taal',
  },
  pl: {
    nav_home: 'Strona główna', nav_search: 'Szukaj', nav_library: 'Twoja biblioteka', nav_videos: 'Filmy',
    nav_live: 'Transmisja na żywo', nav_artists: 'Artyści', nav_offline: 'Offline',
    nav_library_short: 'Biblioteka', nav_live_short: 'Na żywo',
    sidebar_playlists: 'Playlisty',
    search_placeholder: 'Czego chcesz posłuchać?',
    search_browse_genres: 'Przeglądaj gatunki',
    greeting_prefix: 'Miłego', greeting_listener: 'Słuchaczu',
    greeting_morning: 'poranka', greeting_afternoon: 'popołudnia', greeting_evening: 'wieczoru',
    home_featured_tracks: 'Polecane utwory', home_popular_artists: 'Popularni artyści',
    home_gospel_collection: 'Kolekcja gospel', see_all: 'Zobacz wszystko',
    library_subtitle: 'Twoje playlisty, polubione utwory i więcej',
    library_create_playlist: 'Utwórz playlistę',
    profile_profile: 'Profil', profile_liked_songs: 'Polubione utwory', profile_logout: 'Wyloguj się',
    settings_title: 'Ustawienia', settings_crossfade: 'Płynne przejście',
    settings_crossfade_desc: 'Płynne przejście między utworami',
    settings_autoplay: 'Autoodtwarzanie', settings_autoplay_desc: 'Kontynuuj odtwarzanie podobnej muzyki po zakończeniu kolejki',
    settings_lyrics: 'Pokaż tekst utworu', settings_lyrics_desc: 'Wyświetlaj tekst podczas odtwarzania',
    settings_quality: 'Jakość dźwięku', settings_language: 'Język',
  },
  id: {
    nav_home: 'Beranda', nav_search: 'Cari', nav_library: 'Pustaka Anda', nav_videos: 'Video',
    nav_live: 'Siaran Langsung', nav_artists: 'Artis', nav_offline: 'Offline',
    nav_library_short: 'Pustaka', nav_live_short: 'Langsung',
    sidebar_playlists: 'Daftar Putar',
    search_placeholder: 'Apa yang ingin Anda dengarkan?',
    search_browse_genres: 'Jelajahi Genre',
    greeting_prefix: 'Selamat', greeting_listener: 'Pendengar',
    greeting_morning: 'pagi', greeting_afternoon: 'siang', greeting_evening: 'malam',
    home_featured_tracks: 'Lagu Pilihan', home_popular_artists: 'Artis Populer',
    home_gospel_collection: 'Koleksi Injil', see_all: 'Lihat semua',
    library_subtitle: 'Daftar putar, lagu yang disukai, dan lainnya',
    library_create_playlist: 'Buat Daftar Putar',
    profile_profile: 'Profil', profile_liked_songs: 'Lagu yang Disukai', profile_logout: 'Keluar',
    settings_title: 'Pengaturan', settings_crossfade: 'Crossfade',
    settings_crossfade_desc: 'Transisi mulus antar lagu',
    settings_autoplay: 'Putar otomatis', settings_autoplay_desc: 'Terus putar musik serupa saat antrean berakhir',
    settings_lyrics: 'Tampilkan lirik', settings_lyrics_desc: 'Tampilkan lirik saat memutar',
    settings_quality: 'Kualitas audio', settings_language: 'Bahasa',
  },
  vi: {
    nav_home: 'Trang chủ', nav_search: 'Tìm kiếm', nav_library: 'Thư viện của bạn', nav_videos: 'Video',
    nav_live: 'Phát trực tiếp', nav_artists: 'Nghệ sĩ', nav_offline: 'Ngoại tuyến',
    nav_library_short: 'Thư viện', nav_live_short: 'Trực tiếp',
    sidebar_playlists: 'Danh sách phát',
    search_placeholder: 'Bạn muốn nghe gì?',
    search_browse_genres: 'Khám phá thể loại',
    greeting_prefix: 'Chào buổi', greeting_listener: 'Thính giả',
    greeting_morning: 'sáng', greeting_afternoon: 'chiều', greeting_evening: 'tối',
    home_featured_tracks: 'Bài hát nổi bật', home_popular_artists: 'Nghệ sĩ nổi tiếng',
    home_gospel_collection: 'Bộ sưu tập Gospel', see_all: 'Xem tất cả',
    library_subtitle: 'Danh sách phát, bài hát yêu thích và hơn thế nữa',
    library_create_playlist: 'Tạo danh sách phát',
    profile_profile: 'Hồ sơ', profile_liked_songs: 'Bài hát yêu thích', profile_logout: 'Đăng xuất',
    settings_title: 'Cài đặt', settings_crossfade: 'Chuyển bài mượt',
    settings_crossfade_desc: 'Chuyển tiếp mượt mà giữa các bài hát',
    settings_autoplay: 'Tự động phát', settings_autoplay_desc: 'Tiếp tục phát nhạc tương tự khi hàng đợi kết thúc',
    settings_lyrics: 'Hiện lời bài hát', settings_lyrics_desc: 'Hiển thị lời bài hát khi phát',
    settings_quality: 'Chất lượng âm thanh', settings_language: 'Ngôn ngữ',
  },
  bn: {
    nav_home: 'হোম', nav_search: 'খুঁজুন', nav_library: 'আপনার লাইব্রেরি', nav_videos: 'ভিডিও',
    nav_live: 'লাইভ স্ট্রিম', nav_artists: 'শিল্পীরা', nav_offline: 'অফলাইন',
    nav_library_short: 'লাইব্রেরি', nav_live_short: 'লাইভ',
    sidebar_playlists: 'প্লেলিস্ট',
    search_placeholder: 'আপনি কী শুনতে চান?',
    search_browse_genres: 'ধরন ব্রাউজ করুন',
    greeting_prefix: 'শুভ', greeting_listener: 'শ্রোতা',
    greeting_morning: 'সকাল', greeting_afternoon: 'দুপুর', greeting_evening: 'সন্ধ্যা',
    home_featured_tracks: 'নির্বাচিত গান', home_popular_artists: 'জনপ্রিয় শিল্পী',
    home_gospel_collection: 'গসপেল সংগ্রহ', see_all: 'সব দেখুন',
    library_subtitle: 'আপনার প্লেলিস্ট, পছন্দের গান এবং আরও অনেক কিছু',
    library_create_playlist: 'প্লেলিস্ট তৈরি করুন',
    profile_profile: 'প্রোফাইল', profile_liked_songs: 'পছন্দের গান', profile_logout: 'লগ আউট',
    settings_title: 'সেটিংস', settings_crossfade: 'ক্রসফেড',
    settings_crossfade_desc: 'গানের মধ্যে মসৃণ পরিবর্তন',
    settings_autoplay: 'অটোপ্লে', settings_autoplay_desc: 'সারি শেষ হলে একই ধরনের গান বাজতে থাকুক',
    settings_lyrics: 'গানের কথা দেখান', settings_lyrics_desc: 'প্লেব্যাকের সময় গানের কথা দেখান',
    settings_quality: 'অডিও মান', settings_language: 'ভাষা',
  },
  ur: {
    dir: 'rtl',
    nav_home: 'ہوم', nav_search: 'تلاش کریں', nav_library: 'آپ کی لائبریری', nav_videos: 'ویڈیوز',
    nav_live: 'لائیو نشریات', nav_artists: 'فنکار', nav_offline: 'آف لائن',
    nav_library_short: 'لائبریری', nav_live_short: 'لائیو',
    sidebar_playlists: 'پلے لسٹس',
    search_placeholder: 'آپ کیا سننا چاہتے ہیں؟',
    search_browse_genres: 'اقسام دیکھیں',
    greeting_prefix: 'خوش آمدید', greeting_listener: 'سامع',
    greeting_morning: 'صبح بخیر', greeting_afternoon: 'دوپہر بخیر', greeting_evening: 'شام بخیر',
    home_featured_tracks: 'نمایاں ٹریکس', home_popular_artists: 'مقبول فنکار',
    home_gospel_collection: 'گاسپل مجموعہ', see_all: 'سب دیکھیں',
    library_subtitle: 'آپ کی پلے لسٹس، پسندیدہ گانے اور مزید',
    library_create_playlist: 'پلے لسٹ بنائیں',
    profile_profile: 'پروفائل', profile_liked_songs: 'پسندیدہ گانے', profile_logout: 'لاگ آؤٹ',
    settings_title: 'ترتیبات', settings_crossfade: 'کراس فیڈ',
    settings_crossfade_desc: 'گانوں کے درمیان ہموار منتقلی',
    settings_autoplay: 'خودکار تشغیل', settings_autoplay_desc: 'قطار ختم ہونے پر ملتی جلتی موسیقی چلاتے رہیں',
    settings_lyrics: 'بول دکھائیں', settings_lyrics_desc: 'چلانے کے دوران بول دکھائیں',
    settings_quality: 'آڈیو کوالٹی', settings_language: 'زبان',
  },
};

const LANGUAGE_KEY = 'rhema_language';

function applyLanguage(lang) {
  if (!I18N[lang]) lang = 'en';
  state.settings.language = lang;
  try { localStorage.setItem(LANGUAGE_KEY, lang); } catch (e) {}

  const dict = I18N[lang];
  document.documentElement.lang = lang;
  document.documentElement.dir  = dict.dir === 'rtl' ? 'rtl' : 'ltr';
  document.querySelectorAll('[data-i18n]').forEach(el => {
    const key = el.getAttribute('data-i18n');
    if (dict[key] !== undefined) el.textContent = dict[key];
  });
  document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
    const key = el.getAttribute('data-i18n-placeholder');
    if (dict[key] !== undefined) el.placeholder = dict[key];
  });
  renderGreeting();

  const sel = document.getElementById('setting-language');
  if (sel) sel.value = lang;
}

function loadSavedLanguage() {
  let lang = 'en';
  try { lang = localStorage.getItem(LANGUAGE_KEY) || 'en'; } catch (e) {}
  applyLanguage(lang);
}

// ---- AUTH / PROFILE ----
function toggleProfileMenu() { document.getElementById('profile-menu').classList.toggle('open'); }
function closeProfileMenu() { document.getElementById('profile-menu').classList.remove('open'); }

function onAvatarBtnClick() {
  if (state.currentUser) toggleProfileMenu();
  else openAuthModal('login');
}

// Refreshes the nav avatar/profile-menu to reflect the logged-in state
function refreshAuthUI() {
  const img  = document.getElementById('nav-avatar-img');
  const icon = document.getElementById('nav-avatar-icon');
  if (state.currentUser) {
    if (state.currentUser.avatar) {
      img.src = state.currentUser.avatar;
      img.style.display = 'block';
      icon.style.display = 'none';
    } else {
      img.style.display = 'none';
      icon.style.display = '';
    }
  } else {
    closeProfileMenu();
    img.style.display = 'none';
    icon.style.display = '';
  }
}

// Loads the current session user (and their likes/playlists) from the backend
async function loadCurrentUser() {
  try {
    const res = await fetch('api.php?action=me');
    if (!res.ok) return;
    const d = await res.json();
    state.currentUser = d.user || null;
    if (state.currentUser) {
      state.likedTracks = new Set(d.likes || []);
      state.userPlaylists = (d.playlists || []).map(pl => ({
        id: 'u' + pl.id, dbId: pl.id, name: pl.name, desc: pl.description,
        tracks: pl.tracks || [], emoji: pl.emoji, mine: true,
      }));
      const likedPl = state.playlists.find(p => p.isLiked);
      if (likedPl) likedPl.tracks = [...state.likedTracks];
    } else {
      state.likedTracks = new Set();
      state.userPlaylists = [];
    }
    refreshAuthUI();
  } catch (e) { /* not fatal — stay logged out */ }
}

// Returns true if logged in; otherwise prompts to log in and returns false
function requireLogin(message) {
  if (state.currentUser) return true;
  showToast(message || 'Log in to use this feature');
  openAuthModal('login');
  return false;
}

// ---- Auth modal (login / sign up + Google Sign-In) ----
let googleBtnRendered = false;

function openAuthModal(mode) {
  closeProfileMenu();
  state.authMode = mode === 'signup' ? 'signup' : 'login';
  applyAuthMode();
  document.getElementById('auth-error').style.display = 'none';
  document.getElementById('modal-auth').classList.add('open');
  initGoogleSignIn();
}

function toggleAuthMode() {
  state.authMode = state.authMode === 'signup' ? 'login' : 'signup';
  applyAuthMode();
}

function applyAuthMode() {
  const isSignup = state.authMode === 'signup';
  document.getElementById('auth-modal-title').textContent   = isSignup ? 'Sign up' : 'Log in';
  document.getElementById('auth-name-field').style.display  = isSignup ? 'flex' : 'none';
  document.getElementById('auth-submit-btn').textContent    = isSignup ? 'Sign up' : 'Log in';
  document.getElementById('auth-toggle-text').textContent   = isSignup ? 'Already have an account?' : "Don't have an account?";
  document.getElementById('auth-toggle-link').textContent   = isSignup ? 'Log in' : 'Sign up';
}

function showAuthError(msg) {
  const el = document.getElementById('auth-error');
  el.textContent = msg;
  el.style.display = 'block';
}

async function submitAuthForm() {
  const isSignup = state.authMode === 'signup';
  const email    = document.getElementById('auth-email').value.trim();
  const password = document.getElementById('auth-password').value;
  const name     = document.getElementById('auth-name').value.trim();

  if (!email || !password) { showAuthError('Email and password are required'); return; }
  if (isSignup && !name)   { showAuthError('Please enter your name'); return; }

  const body = new URLSearchParams({ email, password });
  if (isSignup) body.set('name', name);

  try {
    const res = await fetch('api.php?action=' + (isSignup ? 'register' : 'login'), { method: 'POST', body });
    const d   = await res.json();
    if (!res.ok || d.error) { showAuthError(d.error || 'Something went wrong'); return; }
    onAuthSuccess(d.user);
  } catch (e) { showAuthError('Network error — please try again'); }
}

function initGoogleSignIn() {
  if (typeof google === 'undefined' || !google.accounts || googleBtnRendered) return;
  try {
    google.accounts.id.initialize({ client_id: '961864377474-9riqenkcckvg0plvl432bos26e7ajvev.apps.googleusercontent.com', callback: handleGoogleCredential });
    google.accounts.id.renderButton(document.getElementById('auth-google-btn'), { theme: 'filled_black', size: 'large', shape: 'pill', width: 280 });
    googleBtnRendered = true;
  } catch (e) { console.warn('Google Sign-In unavailable:', e); }
}

async function handleGoogleCredential(response) {
  try {
    const body = new URLSearchParams({ id_token: response.credential });
    const res  = await fetch('api.php?action=google_login', { method: 'POST', body });
    const d    = await res.json();
    if (!res.ok || d.error) { showAuthError(d.error || 'Google sign-in failed'); return; }
    onAuthSuccess(d.user);
  } catch (e) { showAuthError('Google sign-in failed — please try again'); }
}

async function onAuthSuccess(user) {
  state.currentUser = user;
  closeModal('modal-auth');
  document.getElementById('auth-email').value = '';
  document.getElementById('auth-password').value = '';
  document.getElementById('auth-name').value = '';
  await loadCurrentUser();
  refreshTrackRows();
  renderLibraryPage();
  renderSidebarPlaylists();
  showToast(`Welcome, ${user.name}!`);
}

async function logOutCurrentUser() {
  closeProfileMenu();
  closeModal('modal-profile');
  try { await fetch('api.php?action=logout', { method: 'POST' }); } catch (e) {}
  state.currentUser = null;
  state.likedTracks = new Set();
  state.userPlaylists = [];
  const likedPl = state.playlists.find(p => p.isLiked);
  if (likedPl) likedPl.tracks = [];
  refreshAuthUI();
  refreshTrackRows();
  renderLibraryPage();
  renderSidebarPlaylists();
  showToast('Logged out');
}

// ---- Profile modal (view/edit name + avatar) ----
function openProfile() {
  closeProfileMenu();
  if (!state.currentUser) { openAuthModal('login'); return; }
  document.getElementById('profile-avatar-img').src = state.currentUser.avatar || ('api.php?action=avatar&id=' + state.currentUser.id);
  document.getElementById('profile-name-input').value  = state.currentUser.name;
  document.getElementById('profile-email-input').value = state.currentUser.email;
  document.getElementById('modal-profile').classList.add('open');
}

function onProfileAvatarSelected(event) {
  const file = event.target.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = e => { document.getElementById('profile-avatar-img').src = e.target.result; };
  reader.readAsDataURL(file);
}

async function saveProfile() {
  const name = document.getElementById('profile-name-input').value.trim();
  if (!name) { showToast('Name cannot be empty'); return; }

  const fd = new FormData();
  fd.set('name', name);
  const fileInput = document.getElementById('profile-avatar-input');
  if (fileInput.files[0]) {
    // Downscale first so a camera original is not a multi-megabyte POST.
    const picked = fileInput.files[0];
    fd.set('avatar', window.RhemaImage ? await RhemaImage.compress(picked, 'avatar') : picked);
  }

  try {
    const res = await fetch('api.php?action=update_profile', { method: 'POST', body: fd });
    const d   = await res.json();
    if (!res.ok || d.error) { showToast(d.error || 'Could not save profile'); return; }
    state.currentUser = d.user;
    fileInput.value = '';
    refreshAuthUI();
    closeModal('modal-profile');
    showToast('Profile updated');
  } catch (e) { showToast('Network error — please try again'); }
}

// ---- MODALS ----
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-overlay').forEach(m => {
  m.addEventListener('click', e => { if (e.target === m) m.classList.remove('open'); });
});

// ---- TABS ----
function switchTab(name, btn) {
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
}

// ---- UTILS ----
function formatTime(s) {
  if (!s || isNaN(s)) return '0:00';
  const m = Math.floor(s / 60);
  const sec = Math.floor(s % 60);
  return `${m}:${sec.toString().padStart(2,'0')}`;
}

let toastTimeout;
function showToast(msg) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.classList.add('show');
  clearTimeout(toastTimeout);
  toastTimeout = setTimeout(() => t.classList.remove('show'), 2800);
}

// ---- KEYBOARD SHORTCUTS ----
document.addEventListener('keydown', e => {
  const tag = e.target.tagName;
  if (tag === 'INPUT' || tag === 'TEXTAREA') return;
  if (e.code === 'Space') { e.preventDefault(); togglePlay(); }
  if (e.code === 'ArrowRight') nextTrack();
  if (e.code === 'ArrowLeft') prevTrack();
  if (e.code === 'KeyM') toggleMute();
  if (e.code === 'KeyS') toggleShuffle();
  if (e.code === 'KeyR') toggleRepeat();
  if (e.code === 'Escape') { document.querySelectorAll('.modal-overlay').forEach(m => m.classList.remove('open')); closeVideoModal(); }
});

// ---- DRAG & DROP PROGRESS ----
let dragging = false;
document.getElementById('progress-bar').addEventListener('mousedown', () => dragging = true);
document.addEventListener('mouseup', () => dragging = false);
document.addEventListener('mousemove', e => {
  if (!dragging) return;
  const bar = document.getElementById('progress-bar');
  const rect = bar.getBoundingClientRect();
  const pct = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width));
  document.getElementById('progress-fill').style.width = (pct * 100) + '%';
});

// ---- ARTISTS PAGE ----
function renderArtistsPage() {
  const c = document.getElementById('artists-page-content');
  c.innerHTML = `
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:20px;">
      ${artists.map(a => `
        <div onclick="${a.creatorId ? `openChannelPage(${a.creatorId})` : `openArtistPage('${a.name}')`}" style="cursor:pointer;text-align:center;padding:16px 8px;border-radius:10px;transition:background 0.2s;" onmouseover="this.style.background='#282828'" onmouseout="this.style.background='transparent'">
          ${artistAvatarHtml(a, 120, '3rem', 14, true)}
          <div style="font-weight:700;font-size:0.95rem;margin-bottom:4px;">${a.name}</div>
          <div style="font-size:0.78rem;color:var(--text-muted);">Artist · ${a.genre}</div>
        </div>
      `).join('')}
    </div>
  `;
}

function openArtistPage(name) {
  const a = artists.find(x => x.name === name);
  if (!a) return;
  const artistTracks = tracks.filter(t => a.tracks.includes(t.id));
  const pl = { id:'artist-'+name, name:a.name, desc:a.genre + ' Artist', tracks:a.tracks, emoji:a.emoji };
  state.currentPlaylist = pl;
  const heroImg = document.getElementById('playlist-hero-img');
  heroImg.style.background = a.color + '40';
  heroImg.style.borderRadius = '50%';
  heroImg.style.position = 'relative';
  heroImg.style.overflow = 'hidden';
  heroImg.innerHTML = a.avatar
    ? `${a.emoji}<img src="${a.avatar}" alt="" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;" onerror="this.style.display='none'">`
    : a.emoji;
  document.getElementById('playlist-hero-title').textContent = a.name;
  document.getElementById('playlist-hero-desc').textContent = a.genre;
  document.getElementById('playlist-hero-meta').textContent = `${artistTracks.length} tracks`;
  document.getElementById('playlist-hero').style.background = `linear-gradient(180deg, ${a.color}80 0%, #121212 100%)`;
  document.getElementById('playlist-tracks').innerHTML = artistTracks.map((t,i) => trackRow(t,i)).join('');
  showPage('playlist', null);
}

// ---- CREATOR CHANNEL PAGE ----
// Builds a public, YouTube-style channel page from a creator's id by
// filtering the already-loaded tracks/videos/liveStreams arrays, then
// fetches the channel profile (name/avatar/bio) for progressive enhancement.
function openChannelPage(creatorId) {
  const id = Number(creatorId);
  if (!id) return;

  const channelTracks = tracks.filter(t => Number(t.creator_id) === id);
  const channelVideos = videos.filter(v => Number(v.creator_id) === id);
  const channelLive = liveStreams.filter(s => Number(s.creator_id) === id && s.active);

  const sample = channelTracks[0] || channelVideos[0] || {};
  const name = sample.creator_name || sample.artist || sample.channel || 'Channel';
  const avatar = sample.creator_avatar || '';

  state.currentPlaylist = { id: 'channel-' + id, name, desc: 'Channel', tracks: channelTracks.map(t => t.id), emoji: '👤' };

  const heroImg = document.getElementById('channel-hero-img');
  heroImg.style.background = '#333';
  heroImg.innerHTML = avatar
    ? `👤<img src="${avatar}" alt="" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;" onerror="this.style.display='none'">`
    : '👤';

  document.getElementById('channel-name').textContent = name;
  document.getElementById('channel-bio').textContent = '';
  document.getElementById('channel-meta').textContent =
    `${channelTracks.length} track${channelTracks.length === 1 ? '' : 's'} · ${channelVideos.length} video${channelVideos.length === 1 ? '' : 's'}`;
  document.getElementById('channel-actions').style.display = channelTracks.length ? 'flex' : 'none';

  document.getElementById('channel-live-banner').innerHTML = channelLive.length
    ? `<div onclick="showPage('live', null); renderLivePage();" style="cursor:pointer;background:linear-gradient(135deg,#7a0000,#c0392b);border-radius:10px;padding:16px 20px;margin-bottom:24px;display:flex;align-items:center;gap:12px;font-weight:700;">
         <span class="live-badge">LIVE</span> This channel is live now &mdash; tap to watch
       </div>`
    : '';

  const videosSection = document.getElementById('channel-videos-section');
  const videosGrid = document.getElementById('channel-videos');
  if (channelVideos.length) {
    videosSection.style.display = '';
    videosGrid.innerHTML = channelVideos.map(v => videoCardHTML(v)).join('');
  } else {
    videosSection.style.display = 'none';
    videosGrid.innerHTML = '';
  }

  const tracksSection = document.getElementById('channel-tracks-section');
  const tracksList = document.getElementById('channel-tracks');
  if (channelTracks.length) {
    tracksSection.style.display = '';
    tracksList.innerHTML = channelTracks.map((t, i) => trackRow(t, i)).join('');
  } else {
    tracksSection.style.display = 'none';
    tracksList.innerHTML = '';
  }

  document.getElementById('channel-empty').style.display =
    (!channelTracks.length && !channelVideos.length && !channelLive.length) ? '' : 'none';

  showPage('channel', null);

  fetch(`api.php?action=channel&id=${id}`)
    .then(r => r.json())
    .then(d => {
      if (!d.channel) return;
      document.getElementById('channel-name').textContent = d.channel.name;
      document.getElementById('channel-bio').textContent = d.channel.bio || '';
      if (d.channel.avatar) {
        heroImg.innerHTML = `👤<img src="${d.channel.avatar}" alt="" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;" onerror="this.style.display='none'">`;
      }
    })
    .catch(() => {});
}

// ---- CONTEXT MENU ----
let ctxTrackId = null;
function showContextMenu(e, trackId) {
  e.preventDefault();
  ctxTrackId = trackId;
  const menu = document.getElementById('context-menu');
  const t = tracks.find(x => x.id === trackId);
  document.getElementById('ctx-like-label').textContent = state.likedTracks.has(trackId) ? 'Unlike' : 'Like';
  menu.classList.add('open');
  menu.style.left = Math.min(e.clientX, window.innerWidth - 220) + 'px';
  menu.style.top = Math.min(e.clientY, window.innerHeight - 280) + 'px';
  e.stopPropagation();
}
function ctxPlay() { if (ctxTrackId) playTrack(ctxTrackId); hideContextMenu(); }
function ctxAddNext() {
  if (!ctxTrackId) return;
  const t = tracks.find(x => x.id === ctxTrackId);
  const insertAt = Math.max(0, state.currentIndex + 1);
  state.queue.splice(insertAt, 0, t);
  showToast('Will play next'); hideContextMenu();
}
function ctxAddToQueue() {
  if (!ctxTrackId) return;
  const t = tracks.find(x => x.id === ctxTrackId);
  state.queue.push(t);
  updateQueuePanel();
  showToast('Added to queue'); hideContextMenu();
}
function ctxLike() { if (ctxTrackId) toggleLike(ctxTrackId); hideContextMenu(); }
function ctxAddToPlaylist() { if (ctxTrackId) openAddToPlaylist(ctxTrackId); hideContextMenu(); }
function ctxDownloadLyrics() { if (ctxTrackId) downloadTrackLyrics(ctxTrackId); hideContextMenu(); }
function ctxShare() { if (ctxTrackId) shareTrack(ctxTrackId); hideContextMenu(); }
function ctxGoToArtist() {
  if (!ctxTrackId) return;
  const t = tracks.find(x => x.id === ctxTrackId);
  if (t.creator_id) openChannelPage(t.creator_id);
  else openArtistPage(t.artist);
  hideContextMenu();
}
function hideContextMenu() { document.getElementById('context-menu').classList.remove('open'); }
document.addEventListener('click', () => hideContextMenu());
document.addEventListener('contextmenu', e => {
  const row = e.target.closest('.track-row, .card');
  if (!row) { hideContextMenu(); }
});

// ---- MINI PLAYER UPDATE ----
function updateMiniPlayer() {
  if (!state.currentTrack) return;
  document.getElementById('mini-thumb').textContent = state.currentTrack.emoji;
  document.getElementById('mini-title').textContent = state.currentTrack.title;
  document.getElementById('mini-artist').textContent = state.currentTrack.artist;
  document.getElementById('mini-play-icon').className = state.isPlaying ? 'fas fa-pause' : 'fas fa-play';
}

// ---- EQUALIZER ANIMATION ON TRACK ROWS ----
function refreshAllTrackRows() {
  document.querySelectorAll('.page.active .track-row').forEach(row => {
    const playingIndicator = row.querySelector('.eq-bars');
    if (playingIndicator && !state.isPlaying) {
      playingIndicator.style.animationPlayState = 'paused';
    }
  });
}

// ---- START ----
init();

// ---- PWA: register service worker so the site can be installed on mobile home screens ----
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('sw.js').catch(err => console.warn('SW registration failed:', err));
  });
}

// ---- PWA: "Install app" banner ----
// `_deferredInstallPrompt` is seeded from `window.__rhemaInstallEvent`, which is
// captured by an inline script at the very top of <head> — see the comment
// there for why that early capture matters (the event can fire before this
// script, loaded near the end of <body>, ever runs).
let _deferredInstallPrompt = window.__rhemaInstallEvent || null;
const INSTALL_DISMISS_KEY = 'rhema_install_dismissed_at';
const INSTALL_DISMISS_DAYS = 14;

function isAppInstalled() {
  return window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
}

function wasInstallBannerDismissedRecently() {
  const ts = parseInt(localStorage.getItem(INSTALL_DISMISS_KEY) || '0', 10);
  if (!ts) return false;
  return (Date.now() - ts) < INSTALL_DISMISS_DAYS * 24 * 60 * 60 * 1000;
}

function showInstallBanner(mode) {
  if (isAppInstalled() || wasInstallBannerDismissedRecently()) return;
  const banner = document.getElementById('install-banner');
  const sub    = document.getElementById('install-banner-sub');
  const btn    = document.getElementById('install-banner-btn');
  if (mode === 'native') {
    // The browser hung us its real install prompt — one tap launches it.
    sub.textContent = 'Install Rhema Sounds for quick access & offline playback.';
    btn.textContent = 'Install';
    btn.style.display = '';
  } else if (mode === 'ios') {
    // iOS Safari never fires beforeinstallprompt — manual steps are the only option.
    sub.textContent = 'Tap the Share icon, then "Add to Home Screen" to install the app.';
    btn.style.display = 'none';
  }
  banner.classList.add('show');
}

function dismissInstallBanner() {
  document.getElementById('install-banner').classList.remove('show');
  localStorage.setItem(INSTALL_DISMISS_KEY, String(Date.now()));
}

async function handleInstallClick() {
  if (!_deferredInstallPrompt) return; // button is hidden whenever this is null — see showInstallBanner()
  _deferredInstallPrompt.prompt();
  const choice = await _deferredInstallPrompt.userChoice;
  _deferredInstallPrompt = null;
  document.getElementById('install-banner').classList.remove('show');
  if (choice.outcome === 'accepted') {
    showToast('Installing Rhema Sounds…');
  } else {
    localStorage.setItem(INSTALL_DISMISS_KEY, String(Date.now()));
  }
}

// Chrome/Android/Edge/desktop: hook up to the (possibly already-captured) native prompt.
window.__rhemaOnInstallPromptReady = (e) => {
  _deferredInstallPrompt = e;
  showInstallBanner('native');
};
window.__rhemaOnAppInstalled = () => {
  _deferredInstallPrompt = null;
  document.getElementById('install-banner').classList.remove('show');
  showToast('Rhema Sounds installed! 🎉');
};
// The event may have already fired (and been stashed on `window`) before this
// script ran — pick it up immediately rather than waiting for a new one.
if (_deferredInstallPrompt) showInstallBanner('native');

const _ua    = window.navigator.userAgent;
const _isIos = /iPad|iPhone|iPod/.test(_ua) && !window.MSStream;

// iOS Safari has no beforeinstallprompt — show manual "Add to Home Screen" instructions instead
if (_isIos && !isAppInstalled()) {
  setTimeout(() => showInstallBanner('ios'), 2500);
}

// Everyone else: only ever show the banner when the browser actually hands us
// a real native install prompt (via __rhemaOnInstallPromptReady above or the
// already-captured event picked up at startup). If beforeinstallprompt never
// fires — e.g. install criteria/engagement heuristics aren't met yet, or the
// app is already installed — show nothing rather than a banner with no
// working button.
</script>
</body>
</html>
