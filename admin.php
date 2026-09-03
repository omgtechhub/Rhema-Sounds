<?php
// ============================================================
//  RHEMA SOUNDS – CENTRAL ADMIN PAGE  (admin.php)
//  Full control over all accounts and content. Creators manage
//  their own content via creator_dashboard.php; admins can
//  oversee, approve, or intervene on anything here.
// ============================================================
require_once __DIR__ . '/config.php';

session_start();

// ---- Handle login / logout ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_pass'])) {
    $loginUser = $_POST['login_user'] ?? '';
    $loginPass = $_POST['login_pass'] ?? '';
    if (hash_equals(ADMIN_USERNAME, $loginUser) && password_verify($loginPass, ADMIN_PASSWORD_HASH)) {
        $_SESSION['rhema_admin'] = true;
        header('Location: admin.php');
        exit;
    }
    $loginError = 'Incorrect username or password.';
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

$loggedIn     = !empty($_SESSION['rhema_admin']);
$tracks       = $loggedIn ? getTracks() : [];
$liveStreams  = $loggedIn ? array_values(array_filter(getLiveStreams(), fn($ls) => empty($ls['creator_id']))) : [];
$videos       = $loggedIn ? array_values(array_filter(getVideos(), fn($v) => empty($v['creator_id']))) : [];
$genreList    = $loggedIn ? getGenres() : [];
$banners      = $loggedIn ? getFeaturedBanners() : [];
$playlists    = $loggedIn ? getPlaylists() : [];
$schedule     = $loggedIn ? getBroadcastSchedule() : [];
$siteSettings = $loggedIn ? getSiteSettings() : [];

// ---- Users / creators + pending content moderation queue ----
$allUsers       = $loggedIn ? getAllUsers() : [];
$creatorNames   = [];
foreach ($allUsers as $u) {
    $creatorNames[(int)$u['id']] = $u['creator_name'] ?: $u['name'];
}
$pendingTracks  = $loggedIn ? array_values(array_filter($tracks, fn($t) => ($t['status'] ?? 'approved') === 'pending')) : [];
$pendingVideos  = $loggedIn ? array_values(array_filter(getVideos(), fn($v) => ($v['status'] ?? 'approved') === 'pending')) : [];
$pendingLive    = $loggedIn ? array_values(array_filter(getLiveStreams(), fn($ls) => ($ls['status'] ?? 'approved') === 'pending')) : [];
$pendingCount   = count($pendingTracks) + count($pendingVideos) + count($pendingLive);
$creatorCount   = count(array_filter($allUsers, fn($u) => ($u['role'] ?? 'listener') === 'creator'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Rhema Sounds – Admin</title>
<link rel="manifest" href="manifest-admin.json" crossorigin="use-credentials"/>
<meta name="theme-color" content="#0a0a0a"/>
<link rel="apple-touch-icon" href="admin-apple-touch-icon.png"/>
<meta name="mobile-web-app-capable" content="yes"/>
<meta name="apple-mobile-web-app-capable" content="yes"/>
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent"/>
<meta name="apple-mobile-web-app-title" content="Rhema Admin"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
<style>
:root {
  --green:#1db954; --green-d:#17a347; --bg:#0a0a0a; --card:#1a1a1a;
  --card2:#242424; --border:#333; --text:#fff; --muted:#aaa;
  --red:#e8312a; --gold:#e8b84b;
}
*{box-sizing:border-box;margin:0;padding:0;}
body{background:var(--bg);color:var(--text);font-family:'Segoe UI',Arial,sans-serif;min-height:100vh;}
a{color:var(--green);text-decoration:none;}
a:hover{text-decoration:underline;}
::-webkit-scrollbar{width:6px} ::-webkit-scrollbar-thumb{background:#444;border-radius:3px}

/* TOPBAR */
.topbar{background:#111;border-bottom:1px solid var(--border);padding:14px 28px;display:flex;
        align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;}
.topbar-logo{display:flex;align-items:center;gap:10px;font-size:1.2rem;font-weight:800;color:var(--green);}
.topbar-logo i{background:var(--green);color:#000;width:34px;height:34px;border-radius:8px;
               display:flex;align-items:center;justify-content:center;font-size:.9rem;}
.topbar-right{display:flex;align-items:center;gap:12px;}
.btn{padding:9px 20px;border-radius:24px;font-weight:700;font-size:.85rem;cursor:pointer;border:none;transition:all .15s;}
.btn-green{background:var(--green);color:#000;}
.btn-green:hover{background:var(--green-d);}
.btn-outline{background:transparent;color:var(--text);border:1px solid var(--border);}
.btn-outline:hover{border-color:#888;background:#222;}
.btn-danger{background:var(--red);color:#fff;}
.btn-danger:hover{background:#c0271f;}
.btn-sm{padding:6px 14px;font-size:.78rem;border-radius:20px;}

/* LOGIN */
.login-wrap{max-width:420px;margin:100px auto;padding:0 16px;}
.login-card{background:var(--card);border-radius:16px;padding:40px 36px;box-shadow:0 16px 64px rgba(0,0,0,.6);}
.login-card h2{font-size:1.5rem;font-weight:900;margin-bottom:6px;}
.login-card p{color:var(--muted);font-size:.88rem;margin-bottom:28px;}
.form-group{margin-bottom:16px;}
.form-group label{display:block;font-size:.82rem;color:var(--muted);margin-bottom:6px;font-weight:600;}
.form-input{width:100%;background:#2a2a2a;border:1.5px solid var(--border);border-radius:8px;
            padding:11px 14px;color:var(--text);font-size:.9rem;outline:none;transition:border .2s;}
.form-input:focus{border-color:var(--green);}
.error-msg{color:var(--red);font-size:.82rem;margin-top:10px;font-weight:600;}

/* LAYOUT */
.container{max-width:1200px;margin:0 auto;padding:28px 24px;}
.tabs{display:flex;gap:4px;margin-bottom:28px;border-bottom:1px solid var(--border);padding-bottom:0;
      overflow-x:auto;}
.tab-btn{padding:11px 22px;background:none;border:none;color:var(--muted);font-weight:700;font-size:.88rem;
         cursor:pointer;border-bottom:3px solid transparent;transition:all .15s;margin-bottom:-1px;
         white-space:nowrap;flex-shrink:0;}
.tab-btn.active{color:var(--text);border-bottom-color:var(--green);}
.tab-btn:hover{color:var(--text);}
.tab-pane{display:none;} .tab-pane.active{display:block;}

/* MOBILE NAV (collapsible tabs menu) */
.tabs-toggle{display:none;align-items:center;gap:10px;width:100%;padding:13px 18px;margin-bottom:12px;
             background:var(--card);border:1px solid var(--border);border-radius:10px;color:var(--text);
             font-weight:700;font-size:.9rem;cursor:pointer;text-align:left;}
.tabs-toggle i.fa-chevron-down{margin-left:auto;transition:transform .2s;}
.tabs-toggle.open i.fa-chevron-down{transform:rotate(180deg);}
@media(max-width:768px){
  .tabs-toggle{display:flex;}
  .tabs{display:none;flex-direction:column;gap:0;border-bottom:none;overflow-x:visible;
        background:var(--card);border:1px solid var(--border);border-radius:10px;margin-bottom:20px;
        overflow:hidden;}
  .tabs.open{display:flex;}
  .tab-btn{width:100%;text-align:left;padding:14px 18px;border-bottom:1px solid var(--border);
           border-left:3px solid transparent;margin-bottom:0;}
  .tab-btn:last-child{border-bottom:none;}
  .tab-btn.active{border-bottom-color:var(--border);border-left-color:var(--green);background:#1e1e1e;}
}

/* UPLOAD CARD */
.upload-card{background:var(--card);border-radius:14px;padding:28px;margin-bottom:24px;}
.upload-card h3{font-size:1.1rem;font-weight:800;margin-bottom:20px;display:flex;align-items:center;gap:10px;}
.upload-card h3 i{color:var(--green);}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
@media(max-width:600px){.form-row{grid-template-columns:1fr;}}
.drop-zone{border:2px dashed var(--border);border-radius:10px;padding:40px 20px;text-align:center;
           cursor:pointer;transition:all .2s;background:#111;}
.drop-zone:hover,.drop-zone.drag-over{border-color:var(--green);background:#0d1a0d;}
.drop-zone i{font-size:2.5rem;color:var(--green);margin-bottom:12px;display:block;}
.drop-zone p{color:var(--muted);font-size:.9rem;}
.drop-zone span{color:var(--green);font-weight:700;cursor:pointer;}
.drop-zone .file-selected{color:var(--text);font-weight:700;margin-top:8px;font-size:.9rem;}
.progress-bar-wrap{width:100%;height:8px;background:#333;border-radius:4px;overflow:hidden;margin-top:12px;display:none;}
.progress-bar-fill{height:100%;background:var(--green);border-radius:4px;width:0;transition:width .2s;}

/* TRACK LIST TABLE */
.track-table{width:100%;border-collapse:collapse;}
.track-table th{text-align:left;padding:10px 14px;font-size:.75rem;text-transform:uppercase;
                letter-spacing:.08em;color:var(--muted);border-bottom:1px solid var(--border);}
.track-table td{padding:12px 14px;border-bottom:1px solid #1e1e1e;vertical-align:middle;}
.track-table tr:hover td{background:#1e1e1e;}
.track-thumb{width:44px;height:44px;border-radius:6px;object-fit:cover;background:#333;flex-shrink:0;}
.user-avatar{width:36px;height:36px;border-radius:50%;object-fit:cover;background:#333;flex-shrink:0;}
.track-info-cell{display:flex;align-items:center;gap:12px;}
.badge{display:inline-block;padding:2px 8px;border-radius:12px;font-size:.72rem;font-weight:700;
       background:#2a2a2a;color:var(--muted);}
.badge-green{background:#0d3a1a;color:var(--green);}
.badge-gold{background:#3a2f0d;color:var(--gold);}
.badge-red{background:#3a0d0d;color:#ff6b6b;}
.badge-gray{background:#2a2a2a;color:var(--muted);}
.empty-state{text-align:center;padding:60px 20px;color:var(--muted);}
.empty-state i{font-size:3rem;margin-bottom:16px;display:block;color:#333;}

/* LIVE STREAM CONFIG */
.live-card{background:var(--card);border-radius:12px;padding:22px;margin-bottom:16px;border:1px solid var(--border);}
.live-card h4{font-size:.95rem;font-weight:800;margin-bottom:16px;display:flex;align-items:center;gap:8px;}
.live-card h4 i{color:var(--red);}
.live-indicator{width:10px;height:10px;border-radius:50%;background:#444;display:inline-block;flex-shrink:0;}
.live-indicator.on{background:var(--red);animation:pulse 1.5s infinite;}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.5}}
.type-badge{padding:3px 10px;border-radius:10px;font-size:.72rem;font-weight:700;}
.type-yt{background:#1a0a0a;color:#ff4444;}
.type-fb{background:#0a1530;color:#4267ff;}
.type-hls{background:#0a1a2a;color:#4499ff;}
.type-radio{background:#0a1a0a;color:var(--green);}

/* STATS */
.stats-row{display:flex;gap:16px;flex-wrap:wrap;margin-bottom:28px;}
.stat-card{background:var(--card);border-radius:12px;padding:20px 24px;flex:1;min-width:160px;}
.stat-card .val{font-size:2rem;font-weight:900;color:var(--green);}
.stat-card .lbl{font-size:.8rem;color:var(--muted);margin-top:4px;}

/* TOAST */
#toast{position:fixed;bottom:30px;left:50%;transform:translateX(-50%);background:var(--green);
       color:#000;font-weight:700;padding:10px 24px;border-radius:24px;font-size:.88rem;
       z-index:999;opacity:0;transition:opacity .3s;pointer-events:none;}
#toast.show{opacity:1;}

/* MODAL */
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.85);z-index:100;
               display:none;align-items:center;justify-content:center;}
.modal-overlay.open{display:flex;}
.modal-box{background:#242424;border-radius:14px;padding:28px;max-width:540px;
           width:90%;max-height:80vh;overflow-y:auto;}
.modal-title{font-size:1.1rem;font-weight:800;margin-bottom:18px;}
.modal-close{float:right;background:none;border:none;color:#fff;font-size:1.4rem;cursor:pointer;margin-top:-4px;}
</style>
</head>
<body>

<div id="toast"></div>

<?php if (!$loggedIn): ?>
<!-- ==================== LOGIN ==================== -->
<div class="login-wrap">
  <div class="login-card">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
      <div style="background:var(--green);width:42px;height:42px;border-radius:10px;display:flex;align-items:center;justify-content:center;">
        <i class="fas fa-music" style="color:#000;font-size:1.1rem;"></i>
      </div>
      <div>
        <div style="font-weight:900;font-size:1.1rem;">Rhema Sounds</div>
        <div style="font-size:.75rem;color:var(--muted);">Admin Panel</div>
      </div>
    </div>
    <h2>Welcome back</h2>
    <p>Sign in to manage your music library.</p>
    <form method="POST">
      <div class="form-group">
        <label><i class="fas fa-user" style="margin-right:6px;"></i>Admin Username</label>
        <input type="text" name="login_user" class="form-input" placeholder="Enter username" autocomplete="username" autofocus required/>
      </div>
      <div class="form-group">
        <label><i class="fas fa-lock" style="margin-right:6px;"></i>Admin Password</label>
        <input type="password" name="login_pass" class="form-input" placeholder="Enter password" autocomplete="current-password" required/>
      </div>
      <?php if (!empty($loginError)): ?>
        <p class="error-msg"><i class="fas fa-exclamation-circle" style="margin-right:6px;"></i><?= htmlspecialchars($loginError) ?></p>
      <?php endif; ?>
      <button type="submit" class="btn btn-green" style="width:100%;margin-top:8px;padding:12px;">
        <i class="fas fa-sign-in-alt" style="margin-right:8px;"></i>Sign In
      </button>
    </form>
    <div style="text-align:center;margin-top:20px;">
      <a href="index.html" style="color:var(--muted);font-size:.82rem;">← Back to Rhema Sounds</a>
    </div>
  </div>
</div>

<?php else: ?>
<!-- ==================== ADMIN UI ==================== -->

<div class="topbar">
  <div class="topbar-logo">
    <div><i class="fas fa-music"></i></div>
    Rhema Sounds <span style="font-size:.75rem;color:var(--muted);font-weight:400;margin-left:4px;">Admin</span>
  </div>
  <div class="topbar-right">
    <a href="index.html" class="btn btn-outline btn-sm"><i class="fas fa-eye" style="margin-right:6px;"></i>View Site</a>
    <a href="?logout" class="btn btn-outline btn-sm" onclick="return confirm('Log out?')">
      <i class="fas fa-sign-out-alt" style="margin-right:6px;"></i>Log out
    </a>
  </div>
</div>

<div class="container">

  <!-- STATS -->
  <div class="stats-row">
    <div class="stat-card">
      <div class="val"><?= count($tracks) ?></div>
      <div class="lbl"><i class="fas fa-music" style="margin-right:6px;color:var(--green);"></i>Total Tracks</div>
    </div>
    <div class="stat-card">
      <?php
        $totalBytes = 0;
        foreach ($tracks as $t) {
            $f = AUDIO_DIR . ($t['filename'] ?? '');
            if (file_exists($f)) $totalBytes += filesize($f);
        }
        $size = $totalBytes > 1048576 ? round($totalBytes/1048576, 1).' MB' : round($totalBytes/1024).' KB';
      ?>
      <div class="val"><?= $size ?></div>
      <div class="lbl"><i class="fas fa-hdd" style="margin-right:6px;color:var(--green);"></i>Storage Used</div>
    </div>
    <div class="stat-card">
      <?php $liveActive = count(array_filter($liveStreams, fn($ls) => $ls['active'])); ?>
      <div class="val"><?= $liveActive ?></div>
      <div class="lbl"><i class="fas fa-broadcast-tower" style="margin-right:6px;color:var(--red);"></i>Live Streams Active</div>
    </div>
    <?php
      $genres = array_count_values(array_column($tracks, 'genre'));
      $topGenre = $genres ? array_key_first($genres) : '–';
    ?>
    <div class="stat-card">
      <div class="val" style="font-size:1.3rem;"><?= htmlspecialchars($topGenre) ?></div>
      <div class="lbl"><i class="fas fa-tag" style="margin-right:6px;color:var(--green);"></i>Top Genre</div>
    </div>
    <div class="stat-card">
      <div class="val"><?= $creatorCount ?></div>
      <div class="lbl"><i class="fas fa-user-tie" style="margin-right:6px;color:var(--green);"></i>Creators</div>
    </div>
    <div class="stat-card">
      <div class="val" style="<?= $pendingCount ? 'color:var(--gold);' : '' ?>"><?= $pendingCount ?></div>
      <div class="lbl"><i class="fas fa-hourglass-half" style="margin-right:6px;color:var(--gold);"></i>Pending Review</div>
    </div>
  </div>

  <!-- TABS -->
  <button class="tabs-toggle" id="tabs-toggle" onclick="toggleTabsMenu()">
    <i class="fas fa-bars"></i>
    <span id="tabs-toggle-label">Upload Track</span>
    <i class="fas fa-chevron-down"></i>
  </button>
  <div class="tabs" id="tabs-menu">
    <button class="tab-btn active" onclick="switchTab('upload',this)"><i class="fas fa-upload" style="margin-right:6px;"></i>Upload Track</button>
    <button class="tab-btn" onclick="switchTab('tracks',this)"><i class="fas fa-music" style="margin-right:6px;"></i>Manage Tracks</button>
    <button class="tab-btn" onclick="switchTab('creators',this)"><i class="fas fa-user-tie" style="margin-right:6px;"></i>Creators<?php if ($pendingCount): ?> <span class="badge" style="background:var(--gold);color:#000;margin-left:4px;"><?= $pendingCount ?></span><?php endif; ?></button>
    <button class="tab-btn" onclick="switchTab('live',this)"><i class="fas fa-broadcast-tower" style="margin-right:6px;"></i>Live Streams</button>
    <button class="tab-btn" onclick="switchTab('videos',this)"><i class="fas fa-film" style="margin-right:6px;"></i>Videos</button>
    <button class="tab-btn" onclick="switchTab('genres',this)"><i class="fas fa-tags" style="margin-right:6px;"></i>Genres</button>
    <button class="tab-btn" onclick="switchTab('banners',this)"><i class="fas fa-bullhorn" style="margin-right:6px;"></i>Banners</button>
    <button class="tab-btn" onclick="switchTab('playlists',this)"><i class="fas fa-list-music" style="margin-right:6px;"></i>Playlists</button>
    <button class="tab-btn" onclick="switchTab('schedule',this)"><i class="fas fa-calendar-alt" style="margin-right:6px;"></i>Schedule</button>
    <button class="tab-btn" onclick="switchTab('settings',this)"><i class="fas fa-cog" style="margin-right:6px;"></i>Site Settings</button>
  </div>

  <!-- ========== TAB: UPLOAD ========== -->
  <div id="tab-upload" class="tab-pane active">
    <div class="upload-card">
      <h3><i class="fas fa-cloud-upload-alt"></i>Upload New Track</h3>

      <!-- Drop Zone -->
      <div class="drop-zone" id="drop-zone" onclick="document.getElementById('audio-file-input').click()">
        <i class="fas fa-music"></i>
        <p>Drag &amp; drop your audio file here, or <span>browse</span></p>
        <p style="font-size:.78rem;margin-top:6px;">MP3, MPEG, OGG, WAV, FLAC, AAC, M4A · Max <?= MAX_UPLOAD_MB ?>MB</p>
        <div class="file-selected" id="file-name-display" style="display:none;"></div>
      </div>
      <input type="file" id="audio-file-input" accept="audio/*,.mpeg,.mpg,.mp3" style="display:none;" onchange="fileSelected(this)"/>
      <div class="progress-bar-wrap" id="progress-wrap">
        <div class="progress-bar-fill" id="progress-fill-admin"></div>
      </div>

      <div style="margin-top:20px;display:flex;flex-direction:column;gap:14px;">
        <div class="form-row">
          <div class="form-group" style="margin:0;">
            <label>Track Title *</label>
            <input type="text" class="form-input" id="up-title" placeholder="Enter song title"/>
          </div>
          <div class="form-group" style="margin:0;">
            <label>Artist *</label>
            <input type="text" class="form-input" id="up-artist" placeholder="Artist or choir name"/>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group" style="margin:0;">
            <label>Album</label>
            <input type="text" class="form-input" id="up-album" placeholder="Album name (optional)"/>
          </div>
          <div class="form-group" style="margin:0;">
            <label>Genre</label>
            <select class="form-input" id="up-genre">
              <option>Gospel</option><option>Worship</option><option>Contemporary Worship</option>
              <option>Hymns</option><option>Praise</option><option>Classical Gospel</option>
              <option>Live</option><option>Christmas</option><option>Other</option>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group" style="margin:0;">
            <label>Thumbnail Image <span style="font-weight:400;color:var(--muted);">(optional)</span></label>
            <div style="display:flex;align-items:center;gap:14px;">
              <div id="up-thumb-preview" onclick="document.getElementById('up-thumb-input').click()"
                   style="width:64px;height:64px;border-radius:10px;border:1px dashed var(--border);cursor:pointer;
                          display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0;background:var(--card);">
                <i class="fas fa-image" style="color:var(--muted);"></i>
              </div>
              <div style="display:flex;flex-direction:column;gap:6px;">
                <button type="button" class="btn btn-outline" style="padding:8px 16px;font-size:.82rem;width:fit-content;"
                        onclick="document.getElementById('up-thumb-input').click()">Choose Image</button>
                <span style="color:var(--muted);font-size:.78rem;">JPG, PNG, WEBP or GIF · Max <?= MAX_THUMB_MB ?>MB. Leave empty to use an auto-generated thumbnail.</span>
              </div>
              <input type="file" id="up-thumb-input" accept="image/*" style="display:none;" onchange="thumbSelected(this,'up-thumb-preview')"/>
            </div>
          </div>
          <div class="form-group" style="margin:0;">
            <label>Fallback Color</label>
            <div style="display:flex;align-items:center;gap:10px;">
              <input type="color" id="up-color" value="#1a3a2a"
                     style="width:44px;height:38px;border:none;background:none;cursor:pointer;border-radius:6px;"/>
              <span style="color:var(--muted);font-size:.82rem;">Used to generate a thumbnail when no image is uploaded</span>
            </div>
          </div>
        </div>
        <div class="form-group" style="margin:0;">
          <label>Lyrics <span style="font-weight:400;color:var(--muted);">(optional)</span></label>
          <textarea class="form-input" id="up-lyrics" rows="5"
                    placeholder="Paste song lyrics here…" style="resize:vertical;"></textarea>
        </div>
        <button class="btn btn-green" onclick="uploadTrack()" id="upload-btn" style="padding:12px 32px;width:fit-content;">
          <i class="fas fa-upload" style="margin-right:8px;"></i>Upload Track
        </button>
        <div id="upload-result" style="display:none;align-items:center;gap:12px;padding:14px 18px;border-radius:10px;border:1px solid;margin-top:12px;">
          <span class="result-icon" style="font-size:1.2rem;"></span>
          <span class="result-msg" style="font-size:.88rem;font-weight:600;"></span>
        </div>
      </div>
    </div>
  </div>

  <!-- ========== TAB: MANAGE TRACKS ========== -->
  <div id="tab-tracks" class="tab-pane">
    <div class="upload-card">
      <h3><i class="fas fa-list-music"></i>Track Library
        <span style="font-size:.8rem;color:var(--muted);font-weight:400;"><?= count($tracks) ?> tracks</span>
      </h3>

      <?php if (empty($tracks)): ?>
      <div class="empty-state">
        <i class="fas fa-music"></i>
        <p>No tracks uploaded yet.<br>Go to the Upload tab to add your first song.</p>
      </div>
      <?php else: ?>
      <div style="overflow-x:auto;">
        <table class="track-table" id="tracks-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Track</th>
              <th>Genre</th>
              <th>Duration</th>
              <th>Owner</th>
              <th>Status</th>
              <th>Uploaded</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($tracks as $i => $t): ?>
            <?php
              $tStatus = $t['status'] ?? 'approved';
              $tOwnerId = !empty($t['creator_id']) ? (int)$t['creator_id'] : 0;
              $tOwnerName = $tOwnerId ? ($creatorNames[$tOwnerId] ?? 'Creator #'.$tOwnerId) : 'Admin';
              $statusClass = ['approved'=>'badge-green','pending'=>'badge-gold','rejected'=>'badge-red'][$tStatus] ?? 'badge-green';
            ?>
            <tr id="row-<?= $t['id'] ?>">
              <td style="color:var(--muted);font-size:.82rem;"><?= $t['id'] ?></td>
              <td>
                <div class="track-info-cell">
                  <img src="api.php?action=thumbnail&id=<?= $t['id'] ?>"
                       class="track-thumb" alt="thumb"
                       onerror="this.src='data:image/svg+xml,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'44\' height=\'44\'><rect width=\'44\' height=\'44\' fill=\'%23333\'/><text x=\'50%25\' y=\'55%25\' font-size=\'22\' text-anchor=\'middle\' dominant-baseline=\'middle\' fill=\'white\'>♪</text></svg>'"/>
                  <div>
                    <div style="font-weight:700;font-size:.88rem;"><?= htmlspecialchars($t['title']) ?></div>
                    <div style="font-size:.78rem;color:var(--muted);"><?= htmlspecialchars($t['artist']) ?></div>
                    <?php if (!empty($t['album'])): ?>
                    <div style="font-size:.72rem;color:#666;"><?= htmlspecialchars($t['album']) ?></div>
                    <?php endif; ?>
                  </div>
                </div>
              </td>
              <td><span class="badge badge-green"><?= htmlspecialchars($t['genre']) ?></span></td>
              <td style="color:var(--muted);font-size:.82rem;"><?= htmlspecialchars($t['duration']) ?></td>
              <td style="font-size:.8rem;"><?= htmlspecialchars($tOwnerName) ?></td>
              <td><span class="badge <?= $statusClass ?>"><?= htmlspecialchars(ucfirst($tStatus)) ?></span></td>
              <td style="color:var(--muted);font-size:.78rem;">
                <?= !empty($t['uploaded_at']) ? date('d M Y', strtotime($t['uploaded_at'])) : '–' ?>
              </td>
              <td>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                  <a href="stream.php?id=<?= $t['id'] ?>" target="_blank"
                     class="btn btn-outline btn-sm" title="Preview audio">
                    <i class="fas fa-play"></i>
                  </a>
                  <button class="btn btn-outline btn-sm"
                          onclick="openEditModal(<?= htmlspecialchars(json_encode($t)) ?>)"
                          title="Edit metadata">
                    <i class="fas fa-edit"></i>
                  </button>
                  <?php if ($tStatus === 'pending'): ?>
                  <button class="btn btn-sm" style="background:var(--green);color:#000;"
                          onclick="moderateContent('tracks', <?= $t['id'] ?>, 'approved', this)" title="Approve">
                    <i class="fas fa-check"></i>
                  </button>
                  <button class="btn btn-outline btn-sm"
                          onclick="moderateContent('tracks', <?= $t['id'] ?>, 'rejected', this)" title="Reject">
                    <i class="fas fa-times"></i>
                  </button>
                  <?php endif; ?>
                  <button class="btn btn-danger btn-sm" onclick="deleteTrack(<?= $t['id'] ?>)" title="Delete">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- ========== TAB: LIVE STREAMS ========== -->
  <div id="tab-live" class="tab-pane">
    <div class="upload-card">
      <h3><i class="fas fa-broadcast-tower" style="color:var(--red);"></i>Live Stream Configuration</h3>
      <p style="color:var(--muted);font-size:.85rem;margin-bottom:24px;">
        Configure your live streams. Supported sources: YouTube Live, Facebook Live, HLS (.m3u8), and internet radio streams.
      </p>

      <div id="live-streams-editor">
        <?php foreach ($liveStreams as $i => $ls): ?>
        <div class="live-card" id="live-card-<?= $i ?>">
          <h4>
            <span class="live-indicator <?= $ls['active'] ? 'on' : '' ?>"></span>
            Stream <?= $i + 1 ?>
            <span class="type-badge type-<?= ['youtube'=>'yt','facebook'=>'fb','hls'=>'hls','radio'=>'radio'][$ls['type']] ?? 'radio' ?>">
              <?= strtoupper($ls['type']) ?>
            </span>
          </h4>
          <div style="display:flex;flex-direction:column;gap:12px;">
            <div class="form-row">
              <div class="form-group" style="margin:0;">
                <label>Stream Title</label>
                <input type="text" class="form-input live-title" data-idx="<?= $i ?>"
                       value="<?= htmlspecialchars($ls['title']) ?>" placeholder="Stream name"/>
              </div>
              <div class="form-group" style="margin:0;">
                <label>Host / DJ</label>
                <input type="text" class="form-input live-host" data-idx="<?= $i ?>"
                       value="<?= htmlspecialchars($ls['host']) ?>" placeholder="Host name"/>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group" style="margin:0;">
                <label>Stream Type</label>
                <select class="form-input live-type" data-idx="<?= $i ?>" onchange="updateLiveTypeHint(<?= $i ?>)">
                  <option value="youtube"  <?= $ls['type']==='youtube' ?'selected':'' ?>>YouTube Live</option>
                  <option value="facebook" <?= $ls['type']==='facebook'?'selected':'' ?>>Facebook Live</option>
                  <option value="hls"      <?= $ls['type']==='hls'     ?'selected':'' ?>>HLS Stream (.m3u8)</option>
                  <option value="radio"    <?= $ls['type']==='radio'   ?'selected':'' ?>>Radio / Icecast</option>
                </select>
              </div>
              <div class="form-group" style="margin:0;">
                <label>Viewers (display)</label>
                <input type="text" class="form-input live-viewers" data-idx="<?= $i ?>"
                       value="<?= htmlspecialchars($ls['viewers']) ?>" placeholder="e.g. 1,204"/>
              </div>
            </div>
            <div class="form-group" style="margin:0;">
              <label>
                Stream URL
                <span id="url-hint-<?= $i ?>" style="color:var(--muted);font-weight:400;font-size:.78rem;"></span>
              </label>
              <input type="url" class="form-input live-src" data-idx="<?= $i ?>"
                     value="<?= htmlspecialchars($ls['src']) ?>"
                     placeholder="Paste stream URL…"/>
            </div>
            <div style="display:flex;align-items:center;gap:12px;">
              <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:.88rem;">
                <input type="checkbox" class="live-active" data-idx="<?= $i ?>"
                       <?= $ls['active']?'checked':'' ?> style="width:16px;height:16px;cursor:pointer;accent-color:var(--green);"/>
                <span>Mark as Active (shows LIVE badge)</span>
              </label>
              <button onclick="this.closest('.live-card').remove()" class="btn btn-danger btn-sm" style="margin-left:auto;">
                <i class="fas fa-trash"></i> Remove
              </button>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:8px;">
        <button class="btn btn-green" onclick="saveLiveConfig()">
          <i class="fas fa-save" style="margin-right:8px;"></i>Save Configuration
        </button>
        <button class="btn btn-outline" onclick="addLiveStream()">
          <i class="fas fa-plus" style="margin-right:8px;"></i>Add Stream
        </button>
      </div>

      <div style="margin-top:28px;background:#111;border-radius:10px;padding:20px;">
        <div style="font-weight:700;margin-bottom:10px;font-size:.9rem;"><i class="fas fa-info-circle" style="color:var(--green);margin-right:8px;"></i>URL Format Guide</div>
        <div style="display:flex;flex-direction:column;gap:8px;font-size:.82rem;color:var(--muted);">
          <div><strong style="color:#fff;">YouTube Live:</strong> https://www.youtube.com/embed/LIVE_VIDEO_ID?autoplay=1</div>
          <div><strong style="color:#fff;">Facebook Live:</strong> paste the public video/live post link, e.g. https://www.facebook.com/YourPage/videos/1234567890 (it's embedded automatically)</div>
          <div><strong style="color:#fff;">HLS (.m3u8):</strong> https://your-server.com/stream/live.m3u8</div>
          <div><strong style="color:#fff;">Icecast Radio:</strong> https://your-server.com:8000/stream</div>
          <div><strong style="color:#fff;">Shoutcast:</strong> https://your-server.com:8000/;stream.mp3</div>
        </div>
      </div>
    </div>
  </div>

  <!-- ========== TAB: VIDEOS ========== -->
  <div id="tab-videos" class="tab-pane">
    <div class="upload-card">
      <h3><i class="fas fa-film" style="color:var(--green);"></i>Videos</h3>
      <p style="color:var(--muted);font-size:.85rem;margin-bottom:24px;">
        Manage the music videos &amp; worship sessions shown on the Videos page.
      </p>
      <div id="videos-editor">
        <?php foreach ($videos as $i => $v): ?>
        <div class="live-card" id="video-card-<?= $i ?>">
          <h4>
            <span class="type-badge type-<?= $v['type']==='youtube' ? 'yt' : ($v['type']==='hls' ? 'hls' : 'radio') ?>"><?= strtoupper($v['type']) ?></span>
            Video <?= $i + 1 ?>
          </h4>
          <div style="display:flex;flex-direction:column;gap:12px;">
            <div class="form-row">
              <div class="form-group" style="margin:0;"><label>Title</label>
                <input type="text" class="form-input video-title" value="<?= htmlspecialchars($v['title']) ?>" placeholder="Video title"/></div>
              <div class="form-group" style="margin:0;"><label>Channel</label>
                <input type="text" class="form-input video-channel" value="<?= htmlspecialchars($v['channel']) ?>" placeholder="Channel name"/></div>
            </div>
            <div class="form-row">
              <div class="form-group" style="margin:0;"><label>Duration</label>
                <input type="text" class="form-input video-duration" value="<?= htmlspecialchars($v['duration']) ?>" placeholder="e.g. 7:21"/></div>
              <div class="form-group" style="margin:0;"><label>Type</label>
                <select class="form-input video-type">
                  <option value="youtube" <?= $v['type']==='youtube'?'selected':'' ?>>YouTube</option>
                  <option value="hls"     <?= $v['type']==='hls'    ?'selected':'' ?>>HLS (.m3u8)</option>
                  <option value="mp4"     <?= $v['type']==='mp4'    ?'selected':'' ?>>Direct MP4</option>
                </select></div>
            </div>
            <div class="form-row">
              <div class="form-group" style="margin:0;"><label>Thumbnail Emoji</label>
                <input type="text" class="form-input video-thumb" value="<?= htmlspecialchars($v['thumb']) ?>" placeholder="🎬" maxlength="4"/></div>
              <div></div>
            </div>
            <div class="form-group" style="margin:0;"><label>Video URL
              <span style="font-weight:400;color:var(--muted);">— for YouTube, paste any link (watch/share/embed); it's converted automatically. For HLS/MP4 use a direct stream/file URL.</span></label>
              <input type="url" class="form-input video-src" value="<?= htmlspecialchars($v['src']) ?>" placeholder="https://www.youtube.com/watch?v=VIDEO_ID  or  https://www.youtube.com/embed/VIDEO_ID"/></div>
            <div class="form-group" style="margin:0;"><label>Description</label>
              <input type="text" class="form-input video-desc" value="<?= htmlspecialchars($v['description']) ?>" placeholder="Short description"/></div>
            <button onclick="this.closest('.live-card').remove()" class="btn btn-danger btn-sm" style="width:fit-content;"><i class="fas fa-trash"></i> Remove</button>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:8px;">
        <button class="btn btn-green" onclick="saveVideos()"><i class="fas fa-save" style="margin-right:8px;"></i>Save Videos</button>
        <button class="btn btn-outline" onclick="addVideo()"><i class="fas fa-plus" style="margin-right:8px;"></i>Add Video</button>
      </div>
    </div>
  </div>

  <!-- ========== TAB: GENRES ========== -->
  <div id="tab-genres" class="tab-pane">
    <div class="upload-card">
      <h3><i class="fas fa-tags" style="color:var(--green);"></i>Genres</h3>
      <p style="color:var(--muted);font-size:.85rem;margin-bottom:24px;">
        Manage the genre tiles shown on the Search/Browse page (and offered when uploading tracks).
      </p>
      <div id="genres-editor">
        <?php foreach ($genreList as $i => $g): ?>
        <div class="live-card" id="genre-card-<?= $i ?>">
          <div class="form-row" style="grid-template-columns:2fr 1fr 1fr;">
            <div class="form-group" style="margin:0;"><label>Name</label>
              <input type="text" class="form-input genre-name" value="<?= htmlspecialchars($g['name']) ?>" placeholder="Genre name"/></div>
            <div class="form-group" style="margin:0;"><label>Color</label>
              <input type="color" class="form-input genre-color" value="<?= htmlspecialchars($g['color']) ?>" style="height:42px;cursor:pointer;"/></div>
            <div class="form-group" style="margin:0;"><label>Emoji</label>
              <input type="text" class="form-input genre-emoji" value="<?= htmlspecialchars($g['emoji']) ?>" maxlength="4"/></div>
          </div>
          <button onclick="this.closest('.live-card').remove()" class="btn btn-danger btn-sm" style="margin-top:10px;"><i class="fas fa-trash"></i> Remove</button>
        </div>
        <?php endforeach; ?>
      </div>
      <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:8px;">
        <button class="btn btn-green" onclick="saveGenres()"><i class="fas fa-save" style="margin-right:8px;"></i>Save Genres</button>
        <button class="btn btn-outline" onclick="addGenre()"><i class="fas fa-plus" style="margin-right:8px;"></i>Add Genre</button>
      </div>
    </div>
  </div>

  <!-- ========== TAB: FEATURED BANNERS ========== -->
  <div id="tab-banners" class="tab-pane">
    <div class="upload-card">
      <h3><i class="fas fa-bullhorn" style="color:var(--green);"></i>Featured Banners</h3>
      <p style="color:var(--muted);font-size:.85rem;margin-bottom:24px;">
        Hero slides shown at the top of the homepage. "Action Target" depends on the action type:
        a page name (e.g. <code>live</code>, <code>videos</code>), a track ID, a playlist ID (e.g. <code>pl1</code>), or a full URL.
      </p>
      <div id="banners-editor">
        <?php foreach ($banners as $i => $b): ?>
        <div class="live-card" id="banner-card-<?= $i ?>">
          <div style="display:flex;flex-direction:column;gap:12px;">
            <div class="form-row">
              <div class="form-group" style="margin:0;"><label>Title</label>
                <input type="text" class="form-input banner-title" value="<?= htmlspecialchars($b['title']) ?>"/></div>
              <div class="form-group" style="margin:0;"><label>Subtitle</label>
                <input type="text" class="form-input banner-subtitle" value="<?= htmlspecialchars($b['subtitle']) ?>"/></div>
            </div>
            <div class="form-row">
              <div class="form-group" style="margin:0;"><label>Gradient (CSS background)</label>
                <input type="text" class="form-input banner-gradient" value="<?= htmlspecialchars($b['gradient']) ?>" placeholder="linear-gradient(135deg,#1a3a2a,#0d7a3a)"/></div>
              <div class="form-group" style="margin:0;"><label>Emoji</label>
                <input type="text" class="form-input banner-emoji" value="<?= htmlspecialchars($b['emoji']) ?>" maxlength="4"/></div>
            </div>
            <div class="form-row">
              <div class="form-group" style="margin:0;"><label>Click Action</label>
                <select class="form-input banner-action-type">
                  <option value="page"     <?= $b['action_type']==='page'    ?'selected':'' ?>>Open page</option>
                  <option value="track"    <?= $b['action_type']==='track'   ?'selected':'' ?>>Play track (by ID)</option>
                  <option value="playlist" <?= $b['action_type']==='playlist'?'selected':'' ?>>Open playlist (by ID)</option>
                  <option value="url"      <?= $b['action_type']==='url'     ?'selected':'' ?>>Open external URL</option>
                </select></div>
              <div class="form-group" style="margin:0;"><label>Action Target</label>
                <input type="text" class="form-input banner-action-target" value="<?= htmlspecialchars($b['action_target']) ?>" placeholder="e.g. live / 6 / pl1 / https://..."/></div>
            </div>
            <div style="display:flex;align-items:center;gap:12px;">
              <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:.88rem;">
                <input type="checkbox" class="banner-active" <?= $b['active'] ? 'checked' : '' ?> style="width:16px;height:16px;cursor:pointer;accent-color:var(--green);"/>
                <span>Active (visible on homepage)</span>
              </label>
              <button onclick="this.closest('.live-card').remove()" class="btn btn-danger btn-sm" style="margin-left:auto;"><i class="fas fa-trash"></i> Remove</button>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:8px;">
        <button class="btn btn-green" onclick="saveBanners()"><i class="fas fa-save" style="margin-right:8px;"></i>Save Banners</button>
        <button class="btn btn-outline" onclick="addBanner()"><i class="fas fa-plus" style="margin-right:8px;"></i>Add Banner</button>
      </div>
    </div>
  </div>

  <!-- ========== TAB: PLAYLISTS ========== -->
  <div id="tab-playlists" class="tab-pane">
    <div class="upload-card">
      <h3><i class="fas fa-list-music" style="color:var(--green);"></i>Playlists</h3>
      <p style="color:var(--muted);font-size:.85rem;margin-bottom:24px;">
        Manage curated playlists shown in the sidebar &amp; library. Enter Track IDs separated by commas
        (see the <strong>Manage Tracks</strong> tab for IDs).
      </p>
      <div id="playlists-editor">
        <?php foreach ($playlists as $i => $pl): ?>
        <div class="live-card" id="playlist-card-<?= $i ?>">
          <input type="hidden" class="playlist-id" value="<?= htmlspecialchars($pl['id']) ?>"/>
          <div style="display:flex;flex-direction:column;gap:12px;">
            <div class="form-row">
              <div class="form-group" style="margin:0;"><label>Name</label>
                <input type="text" class="form-input playlist-name" value="<?= htmlspecialchars($pl['name']) ?>"/></div>
              <div class="form-group" style="margin:0;"><label>Emoji</label>
                <input type="text" class="form-input playlist-emoji" value="<?= htmlspecialchars($pl['emoji']) ?>" maxlength="4"/></div>
            </div>
            <div class="form-group" style="margin:0;"><label>Description</label>
              <input type="text" class="form-input playlist-desc" value="<?= htmlspecialchars($pl['description']) ?>"/></div>
            <div class="form-group" style="margin:0;"><label>Track IDs (comma separated)</label>
              <input type="text" class="form-input playlist-tracks" value="<?= htmlspecialchars(implode(',', $pl['tracks'])) ?>" placeholder="e.g. 1,2,3"/></div>
            <div style="display:flex;align-items:center;gap:12px;">
              <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:.88rem;">
                <input type="checkbox" class="playlist-liked" <?= $pl['isLiked'] ? 'checked' : '' ?> style="width:16px;height:16px;cursor:pointer;accent-color:var(--green);"/>
                <span>System "Liked Songs" playlist (tracks managed automatically for each listener)</span>
              </label>
              <button onclick="this.closest('.live-card').remove()" class="btn btn-danger btn-sm" style="margin-left:auto;"><i class="fas fa-trash"></i> Remove</button>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:8px;">
        <button class="btn btn-green" onclick="savePlaylists()"><i class="fas fa-save" style="margin-right:8px;"></i>Save Playlists</button>
        <button class="btn btn-outline" onclick="addPlaylist()"><i class="fas fa-plus" style="margin-right:8px;"></i>Add Playlist</button>
      </div>
    </div>
  </div>

  <!-- ========== TAB: BROADCAST SCHEDULE ========== -->
  <div id="tab-schedule" class="tab-pane">
    <div class="upload-card">
      <h3><i class="fas fa-calendar-alt" style="color:var(--green);"></i>Broadcast Schedule</h3>
      <p style="color:var(--muted);font-size:.85rem;margin-bottom:24px;">
        Shown on the Live page under "Broadcast Schedule".
      </p>
      <div id="schedule-editor">
        <?php foreach ($schedule as $i => $s): ?>
        <div class="live-card" id="schedule-card-<?= $i ?>">
          <div class="form-row">
            <div class="form-group" style="margin:0;"><label>Day</label>
              <select class="form-input schedule-day">
                <?php foreach (['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'] as $day): ?>
                <option <?= $s['day']===$day?'selected':'' ?>><?= $day ?></option>
                <?php endforeach; ?>
              </select></div>
            <div class="form-group" style="margin:0;"><label>Time Range</label>
              <input type="text" class="form-input schedule-time" value="<?= htmlspecialchars($s['time_range']) ?>" placeholder="e.g. 9:00 AM – 1:00 PM"/></div>
          </div>
          <div class="form-row">
            <div class="form-group" style="margin:0;"><label>Show Name</label>
              <input type="text" class="form-input schedule-show" value="<?= htmlspecialchars($s['show_name']) ?>"/></div>
            <div class="form-group" style="margin:0;"><label>Host</label>
              <input type="text" class="form-input schedule-host" value="<?= htmlspecialchars($s['host']) ?>"/></div>
          </div>
          <button onclick="this.closest('.live-card').remove()" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Remove</button>
        </div>
        <?php endforeach; ?>
      </div>
      <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:8px;">
        <button class="btn btn-green" onclick="saveSchedule()"><i class="fas fa-save" style="margin-right:8px;"></i>Save Schedule</button>
        <button class="btn btn-outline" onclick="addScheduleRow()"><i class="fas fa-plus" style="margin-right:8px;"></i>Add Row</button>
      </div>
    </div>
  </div>

  <!-- ========== TAB: SITE SETTINGS ========== -->
  <div id="tab-settings" class="tab-pane">
    <div class="upload-card">
      <h3><i class="fas fa-cog" style="color:var(--green);"></i>Site Settings</h3>
      <p style="color:var(--muted);font-size:.85rem;margin-bottom:24px;">
        General branding &amp; text shown across the site.
      </p>
      <div style="display:flex;flex-direction:column;gap:14px;max-width:560px;">
        <div class="form-group" style="margin:0;"><label>Site Name</label>
          <input type="text" class="form-input setting-input" data-key="site_name" value="<?= htmlspecialchars($siteSettings['site_name'] ?? '') ?>"/></div>
        <div class="form-group" style="margin:0;"><label>Browser Tab Title</label>
          <input type="text" class="form-input setting-input" data-key="site_title" value="<?= htmlspecialchars($siteSettings['site_title'] ?? '') ?>"/></div>
        <div class="form-group" style="margin:0;"><label>Tagline</label>
          <input type="text" class="form-input setting-input" data-key="site_tagline" value="<?= htmlspecialchars($siteSettings['site_tagline'] ?? '') ?>"/></div>
        <div class="form-group" style="margin:0;"><label>Logo Emoji</label>
          <input type="text" class="form-input setting-input" data-key="logo_emoji" value="<?= htmlspecialchars($siteSettings['logo_emoji'] ?? '') ?>" maxlength="4"/></div>
        <div class="form-group" style="margin:0;"><label>Default Profile Name</label>
          <input type="text" class="form-input setting-input" data-key="default_profile_name" value="<?= htmlspecialchars($siteSettings['default_profile_name'] ?? '') ?>"/></div>
        <div class="form-group" style="margin:0;">
          <label>Share Link Base URL <span style="font-weight:400;color:var(--muted);">(leave blank to use the site's current URL automatically)</span></label>
          <input type="text" class="form-input setting-input" data-key="share_base_url" value="<?= htmlspecialchars($siteSettings['share_base_url'] ?? '') ?>" placeholder="https://yourdomain.com/"/></div>
        <div class="form-group" style="margin:0;display:flex;align-items:center;gap:10px;">
          <input type="checkbox" class="setting-input" id="setting-auto-approve-tracks" data-key="auto_approve_tracks" style="width:auto;"
                 <?= (($siteSettings['auto_approve_tracks'] ?? '1') !== '0') ? 'checked' : '' ?>/>
          <label for="setting-auto-approve-tracks" style="margin:0;">Automatically approve tracks uploaded by creators (skip the Pending review step)</label>
        </div>
      </div>
      <div style="margin-top:20px;">
        <button class="btn btn-green" onclick="saveSettings()"><i class="fas fa-save" style="margin-right:8px;"></i>Save Settings</button>
      </div>
    </div>
  </div>

  <!-- ========== TAB: CREATORS & MODERATION ========== -->
  <div id="tab-creators" class="tab-pane">

    <div class="upload-card">
      <h3><i class="fas fa-hourglass-half" style="color:var(--gold);"></i>Pending Review
        <span style="font-size:.8rem;color:var(--muted);font-weight:400;"><?= $pendingCount ?> item<?= $pendingCount === 1 ? '' : 's' ?></span>
      </h3>

      <?php if ($pendingCount === 0): ?>
      <div class="empty-state">
        <i class="fas fa-check-circle"></i>
        <p>Nothing pending review. All creator submissions are up to date.</p>
      </div>
      <?php else: ?>
      <div style="overflow-x:auto;">
        <table class="track-table" id="pending-table">
          <thead>
            <tr><th>Type</th><th>Title</th><th>Creator</th><th>Actions</th></tr>
          </thead>
          <tbody>
          <?php foreach ($pendingTracks as $t):
              $ownerName = $creatorNames[(int)($t['creator_id'] ?? 0)] ?? 'Unknown'; ?>
            <tr id="pending-tracks-<?= $t['id'] ?>">
              <td><span class="badge badge-green"><i class="fas fa-music" style="margin-right:4px;"></i>Track</span></td>
              <td>
                <div style="font-weight:700;font-size:.88rem;"><?= htmlspecialchars($t['title']) ?></div>
                <div style="font-size:.78rem;color:var(--muted);"><?= htmlspecialchars($t['artist']) ?></div>
              </td>
              <td style="font-size:.85rem;"><?= htmlspecialchars($ownerName) ?></td>
              <td>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                  <a href="stream.php?id=<?= $t['id'] ?>" target="_blank" class="btn btn-outline btn-sm" title="Preview"><i class="fas fa-play"></i></a>
                  <button class="btn btn-sm" style="background:var(--green);color:#000;" onclick="moderateContent('tracks', <?= $t['id'] ?>, 'approved', this)"><i class="fas fa-check"></i> Approve</button>
                  <button class="btn btn-outline btn-sm" onclick="moderateContent('tracks', <?= $t['id'] ?>, 'rejected', this)"><i class="fas fa-times"></i> Reject</button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php foreach ($pendingVideos as $v):
              $ownerName = $creatorNames[(int)($v['creator_id'] ?? 0)] ?? 'Unknown'; ?>
            <tr id="pending-videos-<?= $v['id'] ?>">
              <td><span class="badge badge-green"><i class="fas fa-film" style="margin-right:4px;"></i>Video</span></td>
              <td>
                <div style="font-weight:700;font-size:.88rem;"><?= htmlspecialchars($v['title']) ?></div>
                <div style="font-size:.78rem;color:var(--muted);"><?= htmlspecialchars($v['channel'] ?? '') ?></div>
              </td>
              <td style="font-size:.85rem;"><?= htmlspecialchars($ownerName) ?></td>
              <td>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                  <button class="btn btn-sm" style="background:var(--green);color:#000;" onclick="moderateContent('videos', <?= $v['id'] ?>, 'approved', this)"><i class="fas fa-check"></i> Approve</button>
                  <button class="btn btn-outline btn-sm" onclick="moderateContent('videos', <?= $v['id'] ?>, 'rejected', this)"><i class="fas fa-times"></i> Reject</button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php foreach ($pendingLive as $ls):
              $ownerName = $creatorNames[(int)($ls['creator_id'] ?? 0)] ?? 'Unknown'; ?>
            <tr id="pending-live_streams-<?= $ls['id'] ?>">
              <td><span class="badge badge-green"><i class="fas fa-broadcast-tower" style="margin-right:4px;"></i>Live</span></td>
              <td>
                <div style="font-weight:700;font-size:.88rem;"><?= htmlspecialchars($ls['title']) ?></div>
                <div style="font-size:.78rem;color:var(--muted);"><?= htmlspecialchars($ls['host'] ?? '') ?></div>
              </td>
              <td style="font-size:.85rem;"><?= htmlspecialchars($ownerName) ?></td>
              <td>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                  <button class="btn btn-sm" style="background:var(--green);color:#000;" onclick="moderateContent('live_streams', <?= $ls['id'] ?>, 'approved', this)"><i class="fas fa-check"></i> Approve</button>
                  <button class="btn btn-outline btn-sm" onclick="moderateContent('live_streams', <?= $ls['id'] ?>, 'rejected', this)"><i class="fas fa-times"></i> Reject</button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>

    <div class="upload-card">
      <h3><i class="fas fa-users" style="color:var(--green);"></i>All Accounts
        <span style="font-size:.8rem;color:var(--muted);font-weight:400;"><?= count($allUsers) ?> users &middot; <?= $creatorCount ?> creators</span>
      </h3>

      <?php if (empty($allUsers)): ?>
      <div class="empty-state">
        <i class="fas fa-users"></i>
        <p>No registered listener or creator accounts yet.</p>
      </div>
      <?php else: ?>
      <div style="overflow-x:auto;">
        <table class="track-table" id="users-table">
          <thead>
            <tr><th>#</th><th>User</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th><th>Actions</th></tr>
          </thead>
          <tbody>
          <?php foreach ($allUsers as $u):
              $uRole    = $u['role'] ?? 'listener';
              $uStatus  = $u['status'] ?? 'active';
              $uName    = $u['creator_name'] ?: $u['name'];
              $uAvatar  = !empty($u['avatar']) ? ('api.php?action=avatar&id=' . (int)$u['id']) : '';
          ?>
            <tr id="user-row-<?= $u['id'] ?>">
              <td style="color:var(--muted);font-size:.82rem;"><?= $u['id'] ?></td>
              <td>
                <div class="track-info-cell">
                  <img src="<?= $uAvatar ?: 'data:image/svg+xml,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'36\' height=\'36\'><rect width=\'36\' height=\'36\' fill=\'%23333\'/><text x=\'50%25\' y=\'56%25\' font-size=\'18\' text-anchor=\'middle\' dominant-baseline=\'middle\' fill=\'white\'>%F0%9F%91%A4</text></svg>' ?>"
                       class="user-avatar" alt="avatar"
                       onerror="this.src='data:image/svg+xml,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'36\' height=\'36\'><rect width=\'36\' height=\'36\' fill=\'%23333\'/><text x=\'50%25\' y=\'56%25\' font-size=\'18\' text-anchor=\'middle\' dominant-baseline=\'middle\' fill=\'white\'>%F0%9F%91%A4</text></svg>'"/>
                  <div>
                    <div style="font-weight:700;font-size:.88rem;"><?= htmlspecialchars($uName) ?></div>
                    <?php if ($uRole === 'creator' && $u['creator_name'] && $u['creator_name'] !== $u['name']): ?>
                    <div style="font-size:.72rem;color:#666;"><?= htmlspecialchars($u['name']) ?></div>
                    <?php endif; ?>
                  </div>
                </div>
              </td>
              <td style="font-size:.82rem;color:var(--muted);"><?= htmlspecialchars($u['email']) ?></td>
              <td><span class="badge <?= $uRole === 'creator' ? 'badge-green' : 'badge-gray' ?>"><?= htmlspecialchars(ucfirst($uRole)) ?></span></td>
              <td><span class="badge <?= $uStatus === 'active' ? 'badge-green' : 'badge-red' ?>"><?= htmlspecialchars(ucfirst($uStatus)) ?></span></td>
              <td style="color:var(--muted);font-size:.78rem;">
                <?= !empty($u['created_at']) ? date('d M Y', strtotime($u['created_at'])) : '–' ?>
              </td>
              <td>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                  <?php if ($uRole === 'creator'): ?>
                  <button class="btn btn-outline btn-sm" onclick="setUserRole(<?= $u['id'] ?>, 'listener', this)" title="Remove creator access">
                    <i class="fas fa-user-minus"></i> Remove Creator
                  </button>
                  <?php else: ?>
                  <button class="btn btn-sm" style="background:var(--green);color:#000;" onclick="setUserRole(<?= $u['id'] ?>, 'creator', this)" title="Grant creator access">
                    <i class="fas fa-user-tie"></i> Make Creator
                  </button>
                  <?php endif; ?>
                  <?php if ($uStatus === 'active'): ?>
                  <button class="btn btn-danger btn-sm" onclick="setUserStatus(<?= $u['id'] ?>, 'suspended', this)" title="Suspend account">
                    <i class="fas fa-ban"></i> Suspend
                  </button>
                  <?php else: ?>
                  <button class="btn btn-outline btn-sm" onclick="setUserStatus(<?= $u['id'] ?>, 'active', this)" title="Reactivate account">
                    <i class="fas fa-check-circle"></i> Activate
                  </button>
                  <?php endif; ?>
                  <button class="btn btn-danger btn-sm" style="background:#7a0000;" onclick="deleteUserAccount(<?= $u['id'] ?>, <?= json_encode($uName) ?>, this)" title="Permanently delete this account">
                    <i class="fas fa-trash-alt"></i> Terminate
                  </button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>

  </div>

</div><!-- /container -->

<!-- EDIT TRACK MODAL -->
<div class="modal-overlay" id="edit-modal">
  <div class="modal-box">
    <button class="modal-close" onclick="closeEditModal()">×</button>
    <div class="modal-title"><i class="fas fa-edit" style="margin-right:8px;color:var(--green);"></i>Edit Track</div>
    <input type="hidden" id="edit-id"/>
    <div style="display:flex;flex-direction:column;gap:14px;">
      <div class="form-group" style="margin:0;">
        <label>Title</label>
        <input type="text" class="form-input" id="edit-title"/>
      </div>
      <div class="form-group" style="margin:0;">
        <label>Artist</label>
        <input type="text" class="form-input" id="edit-artist"/>
      </div>
      <div class="form-group" style="margin:0;">
        <label>Album</label>
        <input type="text" class="form-input" id="edit-album"/>
      </div>
      <div class="form-group" style="margin:0;">
        <label>Genre</label>
        <select class="form-input" id="edit-genre">
          <option>Gospel</option><option>Worship</option><option>Contemporary Worship</option>
          <option>Hymns</option><option>Praise</option><option>Classical Gospel</option>
          <option>Live</option><option>Christmas</option><option>Other</option>
        </select>
      </div>
      <div class="form-group" style="margin:0;">
        <label>Thumbnail Image <span style="font-weight:400;color:var(--muted);">(optional)</span></label>
        <input type="hidden" id="edit-remove-thumbnail" value=""/>
        <div style="display:flex;align-items:center;gap:14px;">
          <div id="edit-thumb-preview" onclick="document.getElementById('edit-thumb-input').click()"
               style="width:64px;height:64px;border-radius:10px;border:1px dashed var(--border);cursor:pointer;
                      display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0;background:var(--card);">
            <i class="fas fa-image" style="color:var(--muted);"></i>
          </div>
          <div style="display:flex;flex-direction:column;gap:6px;">
            <div style="display:flex;gap:8px;">
              <button type="button" class="btn btn-outline" style="padding:8px 16px;font-size:.82rem;"
                      onclick="document.getElementById('edit-thumb-input').click()">Replace</button>
              <button type="button" class="btn btn-outline" style="padding:8px 16px;font-size:.82rem;"
                      onclick="removeEditThumbnail()">Remove</button>
            </div>
            <span style="color:var(--muted);font-size:.78rem;">JPG, PNG, WEBP or GIF · Max <?= MAX_THUMB_MB ?>MB</span>
          </div>
          <input type="file" id="edit-thumb-input" accept="image/*" style="display:none;" onchange="editThumbSelected(this)"/>
        </div>
      </div>
      <div class="form-group" style="margin:0;">
        <label>Fallback Color</label>
        <div style="display:flex;align-items:center;gap:10px;">
          <input type="color" id="edit-color" style="width:44px;height:38px;border:none;background:none;cursor:pointer;border-radius:6px;"/>
          <span style="color:var(--muted);font-size:.82rem;">Used to generate a thumbnail when no image is set</span>
        </div>
      </div>
      <div class="form-group" style="margin:0;">
        <label>Lyrics</label>
        <textarea class="form-input" id="edit-lyrics" rows="5" style="resize:vertical;"></textarea>
      </div>
      <div style="display:flex;gap:10px;">
        <button class="btn btn-green" onclick="saveEdit()"><i class="fas fa-save" style="margin-right:6px;"></i>Save</button>
        <button class="btn btn-outline" onclick="closeEditModal()">Cancel</button>
      </div>
    </div>
  </div>
</div>

<?php endif; // loggedIn ?>

<script>
// ---- Tab switching ----
function switchTab(name, el) {
  document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  document.getElementById('tab-' + name).classList.add('active');
  if (el) {
    el.classList.add('active');
    const label = document.getElementById('tabs-toggle-label');
    if (label) label.textContent = el.textContent.trim();
  }
  closeTabsMenu();
}

// ---- Mobile collapsible tabs menu ----
function toggleTabsMenu() {
  document.getElementById('tabs-menu').classList.toggle('open');
  document.getElementById('tabs-toggle').classList.toggle('open');
}
function closeTabsMenu() {
  document.getElementById('tabs-menu').classList.remove('open');
  document.getElementById('tabs-toggle').classList.remove('open');
}

// ---- Toast ----
function toast(msg, isErr) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.style.background = isErr ? '#e8312a' : '#1db954';
  t.style.color = isErr ? '#fff' : '#000';
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 3200);
}

// ---- Drop Zone ----
const dz = document.getElementById('drop-zone');
if (dz) {
  dz.addEventListener('dragover', e => { e.preventDefault(); dz.classList.add('drag-over'); });
  dz.addEventListener('dragleave', () => dz.classList.remove('drag-over'));
  dz.addEventListener('drop', e => {
    e.preventDefault();
    dz.classList.remove('drag-over');
    const f = e.dataTransfer.files[0];
    if (f) setAudioFile(f);
  });
}

let selectedFile = null;
let detectedDuration = '';
let selectedThumbFile = null;
let editThumbFile = null;

// ---- Thumbnail preview helpers ----
function thumbSelected(input, previewId) {
  const f = input.files[0];
  if (!f) return;
  if (previewId === 'up-thumb-preview') selectedThumbFile = f;
  showThumbPreview(f, document.getElementById(previewId));
}
function showThumbPreview(file, previewEl) {
  const url = URL.createObjectURL(file);
  previewEl.innerHTML = '<img src="' + url + '" style="width:100%;height:100%;object-fit:cover;"/>';
}

function fileSelected(input) {
  if (input.files[0]) setAudioFile(input.files[0]);
}
function setAudioFile(f) {
  selectedFile = f;
  detectedDuration = '';

  const disp = document.getElementById('file-name-display');
  disp.textContent = '📁 ' + f.name + ' (' + (f.size / 1048576).toFixed(1) + ' MB) — detecting duration…';
  disp.style.display = 'block';

  // Auto-fill title from filename
  const title = document.getElementById('up-title');
  if (!title.value) {
    title.value = f.name.replace(/\.[^.]+$/, '').replace(/[_-]/g, ' ');
  }

  // Detect audio duration using browser Audio API
  const tempUrl = URL.createObjectURL(f);
  const tempAudio = new Audio();
  tempAudio.addEventListener('loadedmetadata', () => {
    const totalSecs = Math.floor(tempAudio.duration);
    const mins = Math.floor(totalSecs / 60);
    const secs = totalSecs % 60;
    detectedDuration = mins + ':' + String(secs).padStart(2, '0');
    disp.textContent = '📁 ' + f.name + ' (' + (f.size / 1048576).toFixed(1) + ' MB) — ' + detectedDuration;
    URL.revokeObjectURL(tempUrl);
  });
  tempAudio.addEventListener('error', () => {
    disp.textContent = '📁 ' + f.name + ' (' + (f.size / 1048576).toFixed(1) + ' MB)';
    URL.revokeObjectURL(tempUrl);
  });
  tempAudio.src = tempUrl;
}

// ---- Upload ----
function uploadTrack() {
  if (!selectedFile) { toast('Please select an audio file first.', true); return; }
  const title  = document.getElementById('up-title').value.trim();
  const artist = document.getElementById('up-artist').value.trim();
  if (!title)  { toast('Please enter a track title.', true); return; }
  if (!artist) { toast('Please enter the artist name.', true); return; }

  const fd = new FormData();
  fd.append('audio',           selectedFile);
  fd.append('title',           title);
  fd.append('artist',          artist);
  fd.append('album',           document.getElementById('up-album').value.trim());
  fd.append('genre',           document.getElementById('up-genre').value);
  fd.append('color',           document.getElementById('up-color').value);
  fd.append('lyrics',          document.getElementById('up-lyrics').value.trim());
  fd.append('client_duration', detectedDuration); // browser-detected fallback
  if (selectedThumbFile) fd.append('thumbnail', selectedThumbFile);

  const btn = document.getElementById('upload-btn');
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin" style="margin-right:8px;"></i>Uploading… please wait';

  const pw = document.getElementById('progress-wrap');
  const pf = document.getElementById('progress-fill-admin');
  pw.style.display = 'block'; pf.style.width = '5%';

  const xhr = new XMLHttpRequest();
  xhr.open('POST', 'api.php?action=upload', true);
  xhr.upload.onprogress = e => {
    if (e.lengthComputable) pf.style.width = Math.max(5, (e.loaded / e.total * 90)) + '%';
  };
  xhr.onload = () => {
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-upload" style="margin-right:8px;"></i>Upload Track';
    pw.style.display = 'none';

    let r = null;
    try { r = JSON.parse(xhr.responseText); } catch(e) { r = null; }

    if (xhr.status === 200 && r && r.success) {
      pf.style.width = '100%';
      showUploadResult(true, '✅ "' + (r.track?.title || title) + '" uploaded successfully!');
      selectedFile = null; detectedDuration = ''; selectedThumbFile = null;
      document.getElementById('file-name-display').style.display = 'none';
      document.getElementById('up-title').value  = '';
      document.getElementById('up-artist').value = '';
      document.getElementById('up-album').value  = '';
      document.getElementById('up-lyrics').value = '';
      document.getElementById('audio-file-input').value = '';
      document.getElementById('up-thumb-input').value = '';
      document.getElementById('up-thumb-preview').innerHTML = '<i class="fas fa-image" style="color:var(--muted);"></i>';
      setTimeout(() => location.reload(), 1800);
    } else {
      const friendly = getFriendlyError(xhr.status, r);
      showUploadResult(false, friendly);
    }
  };
  xhr.onerror = () => {
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-upload" style="margin-right:8px;"></i>Upload Track';
    pw.style.display = 'none';
    showUploadResult(false, 'Connection lost. Please check your internet and try again.');
  };
  xhr.send(fd);
}

function getFriendlyError(status, r) {
  // Map specific known API errors to clean messages
  if (r && r.error) {
    const e = r.error.toLowerCase();
    if (e.includes('file too large'))    return 'This file is too large. Please use an audio file under 50MB.';
    if (e.includes('invalid file type')) return 'This file type isn\'t supported. Please upload an MP3, WAV, AAC, FLAC, or OGG file.';
    if (e.includes('no file'))           return 'No file was received. Please select your audio file and try again.';
    if (e.includes('unauthorized'))      return 'Your session has expired. Please refresh the page and log in again.';
    if (e.includes('failed to save'))    return 'We couldn\'t save your file. Please try again in a moment.';
  }
  if (status === 413) return 'This file is too large for the server. Please use an audio file under 50MB.';
  if (status === 401) return 'Your session has expired. Please refresh the page and log in again.';
  if (status === 0)   return 'Upload was interrupted. Please check your connection and try again.';
  return 'Something went wrong with your upload. Please try again.';
}

function showUploadResult(success, message) {
  const box = document.getElementById('upload-result');
  box.style.display = 'flex';
  box.style.background   = success ? '#0d2a14' : '#2a0d0d';
  box.style.borderColor  = success ? '#1db954' : '#e8312a';
  box.style.color        = success ? '#1db954' : '#ff6b6b';
  box.querySelector('.result-icon').textContent = success ? '✅' : '⚠️';
  box.querySelector('.result-msg').textContent  = message;
  if (!success) {
    setTimeout(() => { box.style.display = 'none'; }, 6000);
  }
}

// ---- Delete Track ----
function deleteTrack(id) {
  if (!confirm('Delete this track? This cannot be undone.')) return;
  fetch('api.php?action=delete&id=' + id, { method: 'POST' })
    .then(r => r.json())
    .then(d => {
      if (d.success) {
        document.getElementById('row-' + id)?.remove();
        toast('Track deleted.');
      } else {
        toast(d.error || 'Delete failed.', true);
      }
    });
}

// ---- Creators & Moderation ----
function setUserRole(id, role, btn) {
  const label = role === 'creator' ? 'grant creator access to' : 'remove creator access from';
  if (!confirm('Are you sure you want to ' + label + ' this account?')) return;
  if (btn) btn.disabled = true;
  const fd = new FormData();
  fd.append('id', id);
  fd.append('role', role);
  fetch('api.php?action=admin_set_role', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(d => {
      if (d.success) {
        toast('Account updated.');
        setTimeout(() => location.reload(), 600);
      } else {
        toast(d.error || 'Update failed.', true);
        if (btn) btn.disabled = false;
      }
    });
}

function setUserStatus(id, status, btn) {
  const label = status === 'suspended' ? 'suspend' : 'reactivate';
  if (!confirm('Are you sure you want to ' + label + ' this account?')) return;
  if (btn) btn.disabled = true;
  const fd = new FormData();
  fd.append('id', id);
  fd.append('status', status);
  fetch('api.php?action=admin_set_user_status', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(d => {
      if (d.success) {
        toast('Account updated.');
        setTimeout(() => location.reload(), 600);
      } else {
        toast(d.error || 'Update failed.', true);
        if (btn) btn.disabled = false;
      }
    });
}

function deleteUserAccount(id, name, btn) {
  if (!confirm('Permanently terminate the account "' + name + '"?\n\nThis cannot be undone. Their tracks/videos/live streams will be hidden and detached from the account, but not deleted.')) return;
  if (btn) btn.disabled = true;
  const fd = new FormData();
  fd.append('id', id);
  fetch('api.php?action=admin_delete_user', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(d => {
      if (d.success) {
        toast('Account terminated.');
        setTimeout(() => location.reload(), 600);
      } else {
        toast(d.error || 'Delete failed.', true);
        if (btn) btn.disabled = false;
      }
    });
}

function moderateContent(table, id, status, btn) {
  if (btn) btn.disabled = true;
  const fd = new FormData();
  fd.append('table', table);
  fd.append('id', id);
  fd.append('status', status);
  fetch('api.php?action=admin_set_content_status', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(d => {
      if (d.success) {
        toast(status === 'approved' ? 'Content approved.' : 'Content rejected.');
        setTimeout(() => location.reload(), 600);
      } else {
        toast(d.error || 'Update failed.', true);
        if (btn) btn.disabled = false;
      }
    });
}

// ---- Edit Modal ----
function openEditModal(track) {
  document.getElementById('edit-id').value    = track.id;
  document.getElementById('edit-title').value  = track.title;
  document.getElementById('edit-artist').value = track.artist;
  document.getElementById('edit-album').value  = track.album || '';
  document.getElementById('edit-lyrics').value = track.lyrics || '';
  document.getElementById('edit-color').value  = track.color || '#1a3a2a';
  const genreEl = document.getElementById('edit-genre');
  [...genreEl.options].forEach(o => { if (o.value === track.genre) o.selected = true; });

  editThumbFile = null;
  document.getElementById('edit-remove-thumbnail').value = '';
  document.getElementById('edit-thumb-input').value = '';
  document.getElementById('edit-thumb-preview').innerHTML =
    '<img src="api.php?action=thumbnail&id=' + track.id + '&t=' + Date.now() + '" style="width:100%;height:100%;object-fit:cover;"/>';

  document.getElementById('edit-modal').classList.add('open');
}
function closeEditModal() {
  document.getElementById('edit-modal').classList.remove('open');
}
function editThumbSelected(input) {
  const f = input.files[0];
  if (!f) return;
  editThumbFile = f;
  document.getElementById('edit-remove-thumbnail').value = '';
  showThumbPreview(f, document.getElementById('edit-thumb-preview'));
}
function removeEditThumbnail() {
  editThumbFile = null;
  document.getElementById('edit-thumb-input').value = '';
  document.getElementById('edit-remove-thumbnail').value = '1';
  document.getElementById('edit-thumb-preview').innerHTML = '<i class="fas fa-image" style="color:var(--muted);"></i>';
}
function saveEdit() {
  const id = document.getElementById('edit-id').value;
  const fd = new FormData();
  fd.append('title',  document.getElementById('edit-title').value);
  fd.append('artist', document.getElementById('edit-artist').value);
  fd.append('album',  document.getElementById('edit-album').value);
  fd.append('genre',  document.getElementById('edit-genre').value);
  fd.append('color',  document.getElementById('edit-color').value);
  fd.append('lyrics', document.getElementById('edit-lyrics').value);
  if (editThumbFile) fd.append('thumbnail', editThumbFile);
  else if (document.getElementById('edit-remove-thumbnail').value === '1') fd.append('remove_thumbnail', '1');
  fetch('api.php?action=update_track&id=' + id, { method: 'POST', body: fd })
    .then(r => r.json())
    .then(d => {
      if (d.success) { toast('Track updated!'); closeEditModal(); setTimeout(() => location.reload(), 900); }
      else toast(d.error || 'Update failed.', true);
    });
}

// ---- Live Stream Config ----
let liveIdx = <?= count($liveStreams) ?>;

function getLiveData() {
  const streams = [];
  const cards = document.querySelectorAll('[id^="live-card-"]');
  cards.forEach((c, i) => {
    streams.push({
      id:      i + 1,
      title:   c.querySelector('.live-title')?.value || '',
      host:    c.querySelector('.live-host')?.value  || '',
      viewers: c.querySelector('.live-viewers')?.value || '0',
      type:    c.querySelector('.live-type')?.value  || 'radio',
      src:     c.querySelector('.live-src')?.value   || '',
      active:  c.querySelector('.live-active')?.checked || false,
    });
  });
  return streams;
}

function saveLiveConfig() {
  fetch('api.php?action=update_live', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ live_streams: getLiveData() }),
  })
  .then(r => r.json())
  .then(d => {
    if (d.success) toast('✅ Live stream configuration saved!');
    else toast(d.error || 'Save failed.', true);
  });
}

function addLiveStream() {
  const ed = document.getElementById('live-streams-editor');
  const i  = liveIdx++;
  const div = document.createElement('div');
  div.className = 'live-card';
  div.id = 'live-card-' + i;
  div.innerHTML = `
    <h4><span class="live-indicator"></span>Stream ${i+1} <span class="type-badge type-radio">RADIO</span></h4>
    <div style="display:flex;flex-direction:column;gap:12px;">
      <div class="form-row">
        <div class="form-group" style="margin:0;"><label>Stream Title</label>
          <input type="text" class="form-input live-title" data-idx="${i}" placeholder="Stream name"/></div>
        <div class="form-group" style="margin:0;"><label>Host / DJ</label>
          <input type="text" class="form-input live-host" data-idx="${i}" placeholder="Host name"/></div>
      </div>
      <div class="form-row">
        <div class="form-group" style="margin:0;"><label>Stream Type</label>
          <select class="form-input live-type" data-idx="${i}" onchange="updateLiveTypeHint(${i})">
            <option value="youtube">YouTube Live</option>
            <option value="facebook">Facebook Live</option>
            <option value="hls">HLS Stream (.m3u8)</option>
            <option value="radio" selected>Radio / Icecast</option>
          </select></div>
        <div class="form-group" style="margin:0;"><label>Viewers (display)</label>
          <input type="text" class="form-input live-viewers" data-idx="${i}" placeholder="e.g. 0"/></div>
      </div>
      <div class="form-group" style="margin:0;"><label>Stream URL
        <span id="url-hint-${i}" style="color:var(--muted);font-weight:400;font-size:.78rem;"></span></label>
        <input type="url" class="form-input live-src" data-idx="${i}" placeholder="Paste stream URL…"/></div>
      <div style="display:flex;align-items:center;gap:8px;">
        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:.88rem;">
          <input type="checkbox" class="live-active" data-idx="${i}"
                 style="width:16px;height:16px;cursor:pointer;accent-color:var(--green);"/>
          <span>Mark as Active</span>
        </label>
        <button onclick="this.closest('.live-card').remove()" class="btn btn-danger btn-sm" style="margin-left:auto;">
          <i class="fas fa-trash"></i> Remove
        </button>
      </div>
    </div>`;
  ed.appendChild(div);
}

function updateLiveTypeHint(i) {
  const type  = document.querySelector('.live-type[data-idx="' + i + '"]')?.value;
  const hint  = document.getElementById('url-hint-' + i);
  if (!hint) return;
  const hints = {
    youtube:  '— e.g. https://www.youtube.com/embed/VIDEO_ID?autoplay=1',
    facebook: '— paste the Facebook video/live post URL, e.g. https://www.facebook.com/YourPage/videos/1234567890',
    hls:      '— e.g. https://server.com/stream.m3u8',
    radio:    '— e.g. https://server.com:8000/stream',
  };
  hint.textContent = hints[type] || '';
}

// Init hints
document.querySelectorAll('.live-type').forEach(el => {
  const i = parseInt(el.dataset.idx);
  updateLiveTypeHint(i);
});

// ---- Videos ----
let videoIdx = <?= count($videos) ?>;

function getVideosData() {
  const items = [];
  document.querySelectorAll('[id^="video-card-"]').forEach((c, i) => {
    items.push({
      id:          i + 1,
      title:       c.querySelector('.video-title')?.value || '',
      channel:     c.querySelector('.video-channel')?.value || '',
      duration:    c.querySelector('.video-duration')?.value || '0:00',
      type:        c.querySelector('.video-type')?.value || 'youtube',
      thumb:       c.querySelector('.video-thumb')?.value || '🎬',
      src:         c.querySelector('.video-src')?.value || '',
      description: c.querySelector('.video-desc')?.value || '',
    });
  });
  return items;
}

function saveVideos() {
  fetch('api.php?action=update_videos', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ videos: getVideosData() }),
  })
  .then(r => r.json())
  .then(d => {
    if (d.success) toast('✅ Videos saved!');
    else toast(d.error || 'Save failed.', true);
  });
}

function addVideo() {
  const ed = document.getElementById('videos-editor');
  const i  = videoIdx++;
  const div = document.createElement('div');
  div.className = 'live-card';
  div.id = 'video-card-' + i;
  div.innerHTML = `
    <h4><span class="type-badge type-yt">YOUTUBE</span> Video ${i+1}</h4>
    <div style="display:flex;flex-direction:column;gap:12px;">
      <div class="form-row">
        <div class="form-group" style="margin:0;"><label>Title</label>
          <input type="text" class="form-input video-title" placeholder="Video title"/></div>
        <div class="form-group" style="margin:0;"><label>Channel</label>
          <input type="text" class="form-input video-channel" placeholder="Channel name"/></div>
      </div>
      <div class="form-row">
        <div class="form-group" style="margin:0;"><label>Duration</label>
          <input type="text" class="form-input video-duration" placeholder="e.g. 7:21"/></div>
        <div class="form-group" style="margin:0;"><label>Type</label>
          <select class="form-input video-type">
            <option value="youtube">YouTube</option>
            <option value="hls">HLS (.m3u8)</option>
            <option value="mp4">Direct MP4</option>
          </select></div>
      </div>
      <div class="form-row">
        <div class="form-group" style="margin:0;"><label>Thumbnail Emoji</label>
          <input type="text" class="form-input video-thumb" placeholder="🎬" maxlength="4"/></div>
        <div></div>
      </div>
      <div class="form-group" style="margin:0;"><label>Video URL (embed / stream URL)</label>
        <input type="url" class="form-input video-src" placeholder="https://www.youtube.com/embed/VIDEO_ID"/></div>
      <div class="form-group" style="margin:0;"><label>Description</label>
        <input type="text" class="form-input video-desc" placeholder="Short description"/></div>
      <button onclick="this.closest('.live-card').remove()" class="btn btn-danger btn-sm" style="width:fit-content;"><i class="fas fa-trash"></i> Remove</button>
    </div>`;
  ed.appendChild(div);
}

// ---- Genres ----
let genreIdx = <?= count($genreList) ?>;

function getGenresData() {
  const items = [];
  document.querySelectorAll('[id^="genre-card-"]').forEach((c, i) => {
    items.push({
      id:    i + 1,
      name:  c.querySelector('.genre-name')?.value || '',
      color: c.querySelector('.genre-color')?.value || '#1db954',
      emoji: c.querySelector('.genre-emoji')?.value || '🎵',
    });
  });
  return items;
}

function saveGenres() {
  fetch('api.php?action=update_genres', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ genres: getGenresData() }),
  })
  .then(r => r.json())
  .then(d => {
    if (d.success) toast('✅ Genres saved!');
    else toast(d.error || 'Save failed.', true);
  });
}

function addGenre() {
  const ed = document.getElementById('genres-editor');
  const i  = genreIdx++;
  const div = document.createElement('div');
  div.className = 'live-card';
  div.id = 'genre-card-' + i;
  div.innerHTML = `
    <div class="form-row" style="grid-template-columns:2fr 1fr 1fr;">
      <div class="form-group" style="margin:0;"><label>Name</label>
        <input type="text" class="form-input genre-name" placeholder="Genre name"/></div>
      <div class="form-group" style="margin:0;"><label>Color</label>
        <input type="color" class="form-input genre-color" value="#1db954" style="height:42px;cursor:pointer;"/></div>
      <div class="form-group" style="margin:0;"><label>Emoji</label>
        <input type="text" class="form-input genre-emoji" value="🎵" maxlength="4"/></div>
    </div>
    <button onclick="this.closest('.live-card').remove()" class="btn btn-danger btn-sm" style="margin-top:10px;"><i class="fas fa-trash"></i> Remove</button>`;
  ed.appendChild(div);
}

// ---- Featured Banners ----
let bannerIdx = <?= count($banners) ?>;

function getBannersData() {
  const items = [];
  document.querySelectorAll('[id^="banner-card-"]').forEach((c, i) => {
    items.push({
      id:            i + 1,
      title:         c.querySelector('.banner-title')?.value || '',
      subtitle:      c.querySelector('.banner-subtitle')?.value || '',
      gradient:      c.querySelector('.banner-gradient')?.value || '',
      emoji:         c.querySelector('.banner-emoji')?.value || '🎵',
      action_type:   c.querySelector('.banner-action-type')?.value || 'page',
      action_target: c.querySelector('.banner-action-target')?.value || 'home',
      active:        c.querySelector('.banner-active')?.checked || false,
    });
  });
  return items;
}

function saveBanners() {
  fetch('api.php?action=update_banners', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ banners: getBannersData() }),
  })
  .then(r => r.json())
  .then(d => {
    if (d.success) toast('✅ Banners saved!');
    else toast(d.error || 'Save failed.', true);
  });
}

function addBanner() {
  const ed = document.getElementById('banners-editor');
  const i  = bannerIdx++;
  const div = document.createElement('div');
  div.className = 'live-card';
  div.id = 'banner-card-' + i;
  div.innerHTML = `
    <div style="display:flex;flex-direction:column;gap:12px;">
      <div class="form-row">
        <div class="form-group" style="margin:0;"><label>Title</label>
          <input type="text" class="form-input banner-title"/></div>
        <div class="form-group" style="margin:0;"><label>Subtitle</label>
          <input type="text" class="form-input banner-subtitle"/></div>
      </div>
      <div class="form-row">
        <div class="form-group" style="margin:0;"><label>Gradient (CSS background)</label>
          <input type="text" class="form-input banner-gradient" placeholder="linear-gradient(135deg,#1a3a2a,#0d7a3a)"/></div>
        <div class="form-group" style="margin:0;"><label>Emoji</label>
          <input type="text" class="form-input banner-emoji" maxlength="4"/></div>
      </div>
      <div class="form-row">
        <div class="form-group" style="margin:0;"><label>Click Action</label>
          <select class="form-input banner-action-type">
            <option value="page">Open page</option>
            <option value="track">Play track (by ID)</option>
            <option value="playlist">Open playlist (by ID)</option>
            <option value="url">Open external URL</option>
          </select></div>
        <div class="form-group" style="margin:0;"><label>Action Target</label>
          <input type="text" class="form-input banner-action-target" placeholder="e.g. live / 6 / pl1 / https://..."/></div>
      </div>
      <div style="display:flex;align-items:center;gap:12px;">
        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:.88rem;">
          <input type="checkbox" class="banner-active" checked style="width:16px;height:16px;cursor:pointer;accent-color:var(--green);"/>
          <span>Active (visible on homepage)</span>
        </label>
        <button onclick="this.closest('.live-card').remove()" class="btn btn-danger btn-sm" style="margin-left:auto;"><i class="fas fa-trash"></i> Remove</button>
      </div>
    </div>`;
  ed.appendChild(div);
}

// ---- Playlists ----
let playlistIdx = <?= count($playlists) ?>;

function getPlaylistsData() {
  const items = [];
  document.querySelectorAll('[id^="playlist-card-"]').forEach((c, i) => {
    const idField = c.querySelector('.playlist-id')?.value || '';
    const tracks = (c.querySelector('.playlist-tracks')?.value || '')
      .split(',').map(s => parseInt(s.trim())).filter(n => !isNaN(n));
    items.push({
      id:          idField || ('pl' + (i + 1)),
      name:        c.querySelector('.playlist-name')?.value || '',
      description: c.querySelector('.playlist-desc')?.value || '',
      emoji:       c.querySelector('.playlist-emoji')?.value || '🎵',
      isLiked:     c.querySelector('.playlist-liked')?.checked || false,
      tracks:      tracks,
    });
  });
  return items;
}

function savePlaylists() {
  fetch('api.php?action=update_playlists', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ playlists: getPlaylistsData() }),
  })
  .then(r => r.json())
  .then(d => {
    if (d.success) toast('✅ Playlists saved!');
    else toast(d.error || 'Save failed.', true);
  });
}

function addPlaylist() {
  const ed = document.getElementById('playlists-editor');
  const i  = playlistIdx++;
  const div = document.createElement('div');
  div.className = 'live-card';
  div.id = 'playlist-card-' + i;
  div.innerHTML = `
    <input type="hidden" class="playlist-id" value="pl${Date.now()}"/>
    <div style="display:flex;flex-direction:column;gap:12px;">
      <div class="form-row">
        <div class="form-group" style="margin:0;"><label>Name</label>
          <input type="text" class="form-input playlist-name"/></div>
        <div class="form-group" style="margin:0;"><label>Emoji</label>
          <input type="text" class="form-input playlist-emoji" value="🎵" maxlength="4"/></div>
      </div>
      <div class="form-group" style="margin:0;"><label>Description</label>
        <input type="text" class="form-input playlist-desc"/></div>
      <div class="form-group" style="margin:0;"><label>Track IDs (comma separated)</label>
        <input type="text" class="form-input playlist-tracks" placeholder="e.g. 1,2,3"/></div>
      <div style="display:flex;align-items:center;gap:12px;">
        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:.88rem;">
          <input type="checkbox" class="playlist-liked" style="width:16px;height:16px;cursor:pointer;accent-color:var(--green);"/>
          <span>System "Liked Songs" playlist (tracks managed automatically for each listener)</span>
        </label>
        <button onclick="this.closest('.live-card').remove()" class="btn btn-danger btn-sm" style="margin-left:auto;"><i class="fas fa-trash"></i> Remove</button>
      </div>
    </div>`;
  ed.appendChild(div);
}

// ---- Broadcast Schedule ----
let scheduleIdx = <?= count($schedule) ?>;
const SCHEDULE_DAYS = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

function getScheduleData() {
  const items = [];
  document.querySelectorAll('[id^="schedule-card-"]').forEach((c, i) => {
    items.push({
      id:         i + 1,
      day:        c.querySelector('.schedule-day')?.value || 'Sunday',
      time_range: c.querySelector('.schedule-time')?.value || '',
      show_name:  c.querySelector('.schedule-show')?.value || '',
      host:       c.querySelector('.schedule-host')?.value || '',
    });
  });
  return items;
}

function saveSchedule() {
  fetch('api.php?action=update_schedule', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ schedule: getScheduleData() }),
  })
  .then(r => r.json())
  .then(d => {
    if (d.success) toast('✅ Broadcast schedule saved!');
    else toast(d.error || 'Save failed.', true);
  });
}

function addScheduleRow() {
  const ed = document.getElementById('schedule-editor');
  const i  = scheduleIdx++;
  const div = document.createElement('div');
  div.className = 'live-card';
  div.id = 'schedule-card-' + i;
  const dayOptions = SCHEDULE_DAYS.map(d => `<option>${d}</option>`).join('');
  div.innerHTML = `
    <div class="form-row">
      <div class="form-group" style="margin:0;"><label>Day</label>
        <select class="form-input schedule-day">${dayOptions}</select></div>
      <div class="form-group" style="margin:0;"><label>Time Range</label>
        <input type="text" class="form-input schedule-time" placeholder="e.g. 9:00 AM – 1:00 PM"/></div>
    </div>
    <div class="form-row">
      <div class="form-group" style="margin:0;"><label>Show Name</label>
        <input type="text" class="form-input schedule-show"/></div>
      <div class="form-group" style="margin:0;"><label>Host</label>
        <input type="text" class="form-input schedule-host"/></div>
    </div>
    <button onclick="this.closest('.live-card').remove()" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Remove</button>`;
  ed.appendChild(div);
}

// ---- Site Settings ----
function saveSettings() {
  const settings = {};
  document.querySelectorAll('.setting-input[data-key]').forEach(el => {
    settings[el.dataset.key] = el.type === 'checkbox' ? (el.checked ? '1' : '0') : el.value;
  });
  fetch('api.php?action=update_settings', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ settings }),
  })
  .then(r => r.json())
  .then(d => {
    if (d.success) toast('✅ Site settings saved!');
    else toast(d.error || 'Save failed.', true);
  });
}
</script>
</body>
</html>