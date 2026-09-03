<?php
// ============================================================
//  RHEMA SOUNDS – CREATOR DASHBOARD  (creator_dashboard.php)
//  Self-service page for accounts with role = 'creator'.
//  Creators manage their own profile, tracks, videos and live
//  stream here. Admins can still oversee/approve/intervene on
//  any of this content from admin.php.
// ============================================================
require_once __DIR__ . '/config.php';

session_start();

// ---- Handle login / logout ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_pass'])) {
    $loginEmail = strtolower(trim($_POST['login_email'] ?? ''));
    $loginPass  = (string)($_POST['login_pass'] ?? '');
    $u = $loginEmail !== '' ? getUserByEmail($loginEmail) : null;
    if ($u && !empty($u['password_hash']) && password_verify($loginPass, $u['password_hash'])) {
        logInUser((int)$u['id']);
        header('Location: creator_dashboard.php');
        exit;
    }
    $loginError = 'Incorrect email or password.';
}

if (isset($_GET['logout'])) {
    logOutUser();
    header('Location: creator_dashboard.php');
    exit;
}

$loggedIn = isUserLoggedIn();
$user     = $loggedIn ? getUserById(currentUserId()) : null;
if ($loggedIn && !$user) { logOutUser(); $loggedIn = false; }

$role     = $user['role']   ?? 'listener';
$status   = $user['status'] ?? 'active';
$isCreator = $user && $role === 'creator';
$isActive  = $status === 'active';
$showDashboard = $isCreator && $isActive;

$userView = $user ? decorateUser($user) : null;
$myTracks = $showDashboard ? getTracksByCreator((int)$user['id']) : [];
$myVideos = $showDashboard ? getVideosByCreator((int)$user['id']) : [];
$myLive   = $showDashboard ? getLiveStreamsByCreator((int)$user['id']) : [];
$myLiveStream = $myLive[0] ?? null;

$myPendingCount = count(array_filter(
    array_merge($myTracks, $myVideos, $myLive),
    fn($r) => ($r['status'] ?? 'approved') === 'pending'
));

$autoApproveTracks = (getSiteSettings()['auto_approve_tracks'] ?? '1') !== '0';

function statusBadgeClass(string $s): string {
    return ['approved' => 'badge-green', 'pending' => 'badge-gold', 'rejected' => 'badge-red'][$s] ?? 'badge-green';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Rhema Sounds – Creator Dashboard</title>
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

/* LOGIN / GATE SCREENS */
.login-wrap{max-width:440px;margin:100px auto;padding:0 16px;}
.login-card{background:var(--card);border-radius:16px;padding:40px 36px;box-shadow:0 16px 64px rgba(0,0,0,.6);}
.login-card h2{font-size:1.5rem;font-weight:900;margin-bottom:6px;}
.login-card p{color:var(--muted);font-size:.88rem;margin-bottom:18px;line-height:1.6;}
.form-group{margin-bottom:16px;}
.form-group label{display:block;font-size:.82rem;color:var(--muted);margin-bottom:6px;font-weight:600;}
.form-input{width:100%;background:#2a2a2a;border:1.5px solid var(--border);border-radius:8px;
            padding:11px 14px;color:var(--text);font-size:.9rem;outline:none;transition:border .2s;}
.form-input:focus{border-color:var(--green);}
.form-input:disabled{cursor:not-allowed;}
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
.upload-card h3{font-size:1.1rem;font-weight:800;margin-bottom:8px;display:flex;align-items:center;gap:10px;}
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

/* TRACK / CONTENT LIST TABLE */
.track-table{width:100%;border-collapse:collapse;}
.track-table th{text-align:left;padding:10px 14px;font-size:.75rem;text-transform:uppercase;
                letter-spacing:.08em;color:var(--muted);border-bottom:1px solid var(--border);}
.track-table td{padding:12px 14px;border-bottom:1px solid #1e1e1e;vertical-align:middle;}
.track-table tr:hover td{background:#1e1e1e;}
.track-thumb{width:44px;height:44px;border-radius:6px;object-fit:cover;background:#333;flex-shrink:0;}
.track-thumb-emoji{width:44px;height:44px;border-radius:6px;background:#333;flex-shrink:0;display:flex;
                    align-items:center;justify-content:center;font-size:1.4rem;}
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
.live-indicator{width:10px;height:10px;border-radius:50%;background:#444;display:inline-block;flex-shrink:0;}
.live-indicator.on{background:var(--red);animation:pulse 1.5s infinite;}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.5}}
.type-badge{padding:3px 10px;border-radius:10px;font-size:.72rem;font-weight:700;}
.type-yt{background:#1a0a0a;color:#ff4444;}
.type-fb{background:#0a1530;color:#4267ff;}
.type-hls{background:#0a1a2a;color:#4499ff;}
.type-radio{background:#0a1a0a;color:var(--green);}
.type-mp4{background:#1a1a0a;color:#e8b84b;}

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
        <i class="fas fa-microphone-alt" style="color:#000;font-size:1.1rem;"></i>
      </div>
      <div>
        <div style="font-weight:900;font-size:1.1rem;">Rhema Sounds</div>
        <div style="font-size:.75rem;color:var(--muted);">Creator Dashboard</div>
      </div>
    </div>
    <h2>Creator Sign In</h2>
    <p>Sign in with your Rhema Sounds account email &amp; password to manage your music, videos and live stream.</p>
    <form method="POST">
      <div class="form-group">
        <label><i class="fas fa-envelope" style="margin-right:6px;"></i>Email</label>
        <input type="email" name="login_email" class="form-input" placeholder="you@example.com" autofocus required/>
      </div>
      <div class="form-group">
        <label><i class="fas fa-lock" style="margin-right:6px;"></i>Password</label>
        <input type="password" name="login_pass" class="form-input" placeholder="Enter password" required/>
      </div>
      <?php if (!empty($loginError)): ?>
        <p class="error-msg"><i class="fas fa-exclamation-circle" style="margin-right:6px;"></i><?= htmlspecialchars($loginError) ?></p>
      <?php endif; ?>
      <button type="submit" class="btn btn-green" style="width:100%;margin-top:8px;padding:12px;">
        <i class="fas fa-sign-in-alt" style="margin-right:8px;"></i>Sign In
      </button>
    </form>
    <p style="margin-top:18px;">
      Don't have a Rhema Sounds account yet? <a href="index.html">Sign up here</a> — every new account
      includes creator access, so you can come straight back and start uploading.
    </p>
    <div style="text-align:center;margin-top:16px;">
      <a href="index.html" style="color:var(--muted);font-size:.82rem;">&larr; Back to Rhema Sounds</a>
    </div>
  </div>
</div>

<?php elseif (!$isCreator): ?>
<!-- ==================== LOGGED IN BUT NOT A CREATOR ==================== -->
<div class="login-wrap">
  <div class="login-card" style="text-align:center;">
    <i class="fas fa-user-tie" style="font-size:2.6rem;color:var(--muted);margin-bottom:16px;display:block;"></i>
    <h2>Start Your Channel</h2>
    <p>
      Your account (<strong><?= htmlspecialchars($user['email']) ?></strong>) doesn't have creator
      access yet. Activate it instantly below to start uploading your own tracks, videos and live streams.
    </p>
    <div style="display:flex;gap:10px;justify-content:center;margin-top:8px;flex-wrap:wrap;">
      <button id="become-creator-btn" class="btn btn-green" onclick="becomeCreator(this)">
        <i class="fas fa-broadcast-tower" style="margin-right:6px;"></i>Activate Creator Access
      </button>
      <a href="index.html" class="btn btn-outline"><i class="fas fa-home" style="margin-right:6px;"></i>Back to Rhema Sounds</a>
      <a href="?logout" class="btn btn-outline" onclick="return confirm('Log out?')">
        <i class="fas fa-sign-out-alt" style="margin-right:6px;"></i>Log out
      </a>
    </div>
  </div>
</div>
<script>
function becomeCreator(btn) {
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin" style="margin-right:6px;"></i>Activating...';
  fetch('api.php?action=become_creator', { method: 'POST' })
    .then(r => r.json())
    .then(d => {
      if (d.success) {
        location.reload();
      } else {
        alert(d.error || 'Could not activate creator access.');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-broadcast-tower" style="margin-right:6px;"></i>Activate Creator Access';
      }
    })
    .catch(() => {
      alert('Network error. Please try again.');
      btn.disabled = false;
      btn.innerHTML = '<i class="fas fa-broadcast-tower" style="margin-right:6px;"></i>Activate Creator Access';
    });
}
</script>

<?php elseif (!$isActive): ?>
<!-- ==================== SUSPENDED CREATOR ==================== -->
<div class="login-wrap">
  <div class="login-card" style="text-align:center;">
    <i class="fas fa-ban" style="font-size:2.6rem;color:var(--red);margin-bottom:16px;display:block;"></i>
    <h2>Account Suspended</h2>
    <p>Your creator account has been suspended. Please contact the site admin for assistance.</p>
    <div style="display:flex;gap:10px;justify-content:center;margin-top:8px;flex-wrap:wrap;">
      <a href="index.html" class="btn btn-outline"><i class="fas fa-home" style="margin-right:6px;"></i>Back to Rhema Sounds</a>
      <a href="?logout" class="btn btn-outline" onclick="return confirm('Log out?')">
        <i class="fas fa-sign-out-alt" style="margin-right:6px;"></i>Log out
      </a>
    </div>
  </div>
</div>

<?php else: ?>
<!-- ==================== CREATOR DASHBOARD ==================== -->

<div class="topbar">
  <div class="topbar-logo">
    <div><i class="fas fa-microphone-alt"></i></div>
    Rhema Sounds <span style="font-size:.75rem;color:var(--muted);font-weight:400;margin-left:4px;">Creator</span>
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
      <div class="val"><?= count($myTracks) ?></div>
      <div class="lbl"><i class="fas fa-music" style="margin-right:6px;color:var(--green);"></i>My Tracks</div>
    </div>
    <div class="stat-card">
      <div class="val"><?= count($myVideos) ?></div>
      <div class="lbl"><i class="fas fa-film" style="margin-right:6px;color:var(--green);"></i>My Videos</div>
    </div>
    <div class="stat-card">
      <div class="val" style="<?= $myPendingCount ? 'color:var(--gold);' : '' ?>"><?= $myPendingCount ?></div>
      <div class="lbl"><i class="fas fa-hourglass-half" style="margin-right:6px;color:var(--gold);"></i>Pending Review</div>
    </div>
  </div>

  <!-- TABS -->
  <button class="tabs-toggle" id="tabs-toggle" onclick="toggleTabsMenu()">
    <i class="fas fa-bars"></i>
    <span id="tabs-toggle-label">My Profile</span>
    <i class="fas fa-chevron-down"></i>
  </button>
  <div class="tabs" id="tabs-menu">
    <button class="tab-btn active" onclick="switchTab('profile',this)"><i class="fas fa-id-badge" style="margin-right:6px;"></i>My Profile</button>
    <button class="tab-btn" onclick="switchTab('tracks',this)"><i class="fas fa-music" style="margin-right:6px;"></i>My Tracks</button>
    <button class="tab-btn" onclick="switchTab('videos',this)"><i class="fas fa-film" style="margin-right:6px;"></i>My Videos</button>
    <button class="tab-btn" onclick="switchTab('live',this)"><i class="fas fa-broadcast-tower" style="margin-right:6px;"></i>My Live</button>
  </div>

  <!-- ========== TAB: MY PROFILE ========== -->
  <div id="tab-profile" class="tab-pane active">
    <div class="upload-card">
      <h3><i class="fas fa-id-badge"></i>Creator Profile</h3>
      <p style="color:var(--muted);font-size:.85rem;margin-bottom:24px;">
        This information is shown publicly on your artist page and in artist listings.
      </p>

      <div style="display:flex;align-items:center;gap:20px;margin-bottom:24px;flex-wrap:wrap;">
        <div id="profile-avatar-preview" onclick="document.getElementById('profile-avatar-input').click()"
             style="width:88px;height:88px;border-radius:50%;cursor:pointer;overflow:hidden;background:var(--card2);
                    border:2px solid var(--border);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
          <?php if (!empty($userView['avatar'])): ?>
          <img src="<?= htmlspecialchars($userView['avatar']) ?>" style="width:100%;height:100%;object-fit:cover;"/>
          <?php else: ?>
          <i class="fas fa-user" style="font-size:2rem;color:var(--muted);"></i>
          <?php endif; ?>
        </div>
        <div>
          <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('profile-avatar-input').click()">
            <i class="fas fa-camera" style="margin-right:6px;"></i>Change Photo
          </button>
          <input type="file" id="profile-avatar-input" accept="image/*" style="display:none;" onchange="profileAvatarSelected(this)"/>
          <p style="color:var(--muted);font-size:.78rem;margin-top:8px;">JPG, PNG, WEBP or GIF &middot; Max <?= MAX_THUMB_MB ?>MB</p>
        </div>
      </div>

      <div style="display:flex;flex-direction:column;gap:14px;max-width:560px;">
        <div class="form-group" style="margin:0;">
          <label>Display / Artist Name</label>
          <input type="text" class="form-input" id="profile-creator-name"
                 value="<?= htmlspecialchars($user['creator_name'] ?: $user['name']) ?>"
                 placeholder="Shown publicly as your artist name"/>
        </div>
        <div class="form-group" style="margin:0;">
          <label>Bio</label>
          <textarea class="form-input" id="profile-bio" rows="4" style="resize:vertical;"
                    placeholder="Tell listeners about yourself…"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
        </div>
        <div class="form-group" style="margin:0;">
          <label>Account Email</label>
          <input type="text" class="form-input" value="<?= htmlspecialchars($user['email']) ?>" disabled style="opacity:.6;"/>
        </div>
      </div>
      <div style="margin-top:20px;">
        <button class="btn btn-green" onclick="saveProfile()"><i class="fas fa-save" style="margin-right:8px;"></i>Save Profile</button>
      </div>
    </div>
  </div>

  <!-- ========== TAB: MY TRACKS ========== -->
  <div id="tab-tracks" class="tab-pane">
    <div class="upload-card">
      <h3><i class="fas fa-cloud-upload-alt"></i>Upload New Track</h3>
      <p style="color:var(--muted);font-size:.85rem;margin-bottom:20px;">
        <?php if ($autoApproveTracks): ?>
          New uploads are <span class="badge badge-green">Approved</span> automatically and go live right away.
        <?php else: ?>
          New uploads are marked <span class="badge badge-gold">Pending</span> until an admin approves them.
        <?php endif; ?>
      </p>

      <div class="drop-zone" id="drop-zone" onclick="document.getElementById('audio-file-input').click()">
        <i class="fas fa-music"></i>
        <p>Drag &amp; drop your audio file here, or <span>browse</span></p>
        <p style="font-size:.78rem;margin-top:6px;">MP3, MPEG, OGG, WAV, FLAC, AAC, M4A &middot; Max <?= MAX_UPLOAD_MB ?>MB</p>
        <div class="file-selected" id="file-name-display" style="display:none;"></div>
      </div>
      <input type="file" id="audio-file-input" accept="audio/*,.mpeg,.mpg,.mp3" style="display:none;" onchange="fileSelected(this)"/>
      <div class="progress-bar-wrap" id="progress-wrap">
        <div class="progress-bar-fill" id="progress-fill"></div>
      </div>

      <div style="margin-top:20px;display:flex;flex-direction:column;gap:14px;">
        <div class="form-row">
          <div class="form-group" style="margin:0;">
            <label>Track Title *</label>
            <input type="text" class="form-input" id="up-title" placeholder="Enter song title"/>
          </div>
          <div class="form-group" style="margin:0;">
            <label>Artist *</label>
            <input type="text" class="form-input" id="up-artist"
                   value="<?= htmlspecialchars($user['creator_name'] ?: $user['name']) ?>" placeholder="Artist or choir name"/>
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
                <span style="color:var(--muted);font-size:.78rem;">JPG, PNG, WEBP or GIF &middot; Max <?= MAX_THUMB_MB ?>MB. Leave empty for an auto-generated thumbnail.</span>
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
        <button class="btn btn-green" onclick="uploadMyTrack()" id="upload-btn" style="padding:12px 32px;width:fit-content;">
          <i class="fas fa-upload" style="margin-right:8px;"></i>Upload Track
        </button>
        <div id="upload-result" style="display:none;align-items:center;gap:12px;padding:14px 18px;border-radius:10px;border:1px solid;margin-top:12px;">
          <span class="result-icon" style="font-size:1.2rem;"></span>
          <span class="result-msg" style="font-size:.88rem;font-weight:600;"></span>
        </div>
      </div>
    </div>

    <div class="upload-card">
      <h3><i class="fas fa-list-music"></i>My Track Library
        <span style="font-size:.8rem;color:var(--muted);font-weight:400;"><?= count($myTracks) ?> tracks</span>
      </h3>

      <?php if (empty($myTracks)): ?>
      <div class="empty-state">
        <i class="fas fa-music"></i>
        <p>You haven't uploaded any tracks yet.<br>Use the form above to add your first song.</p>
      </div>
      <?php else: ?>
      <div style="overflow-x:auto;">
        <table class="track-table" id="my-tracks-table">
          <thead>
            <tr><th>#</th><th>Track</th><th>Genre</th><th>Duration</th><th>Status</th><th>Uploaded</th><th>Actions</th></tr>
          </thead>
          <tbody>
          <?php foreach ($myTracks as $t): ?>
            <tr id="track-row-<?= $t['id'] ?>">
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
              <td><span class="badge <?= statusBadgeClass($t['status'] ?? 'approved') ?>"><?= htmlspecialchars(ucfirst($t['status'] ?? 'approved')) ?></span></td>
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
                          onclick="openTrackEditModal(<?= htmlspecialchars(json_encode($t)) ?>)"
                          title="Edit metadata">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button class="btn btn-danger btn-sm" onclick="deleteMyTrack(<?= $t['id'] ?>)" title="Delete">
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

  <!-- ========== TAB: MY VIDEOS ========== -->
  <div id="tab-videos" class="tab-pane">
    <div class="upload-card">
      <h3><i class="fas fa-film"></i>My Videos
        <span style="font-size:.8rem;color:var(--muted);font-weight:400;"><?= count($myVideos) ?> videos</span>
      </h3>
      <p style="color:var(--muted);font-size:.85rem;margin-bottom:20px;">
        New videos are marked <span class="badge badge-gold">Pending</span> until an admin approves them.
      </p>

      <button class="btn btn-green" onclick="openVideoModal(null)" style="margin-bottom:20px;">
        <i class="fas fa-plus" style="margin-right:8px;"></i>Add Video
      </button>

      <?php if (empty($myVideos)): ?>
      <div class="empty-state">
        <i class="fas fa-film"></i>
        <p>You haven't added any videos yet.<br>Click "Add Video" to share your first video.</p>
      </div>
      <?php else: ?>
      <div style="overflow-x:auto;">
        <table class="track-table" id="my-videos-table">
          <thead>
            <tr><th>#</th><th>Video</th><th>Type</th><th>Status</th><th>Actions</th></tr>
          </thead>
          <tbody>
          <?php foreach ($myVideos as $v): ?>
            <tr id="video-row-<?= $v['id'] ?>">
              <td style="color:var(--muted);font-size:.82rem;"><?= $v['id'] ?></td>
              <td>
                <div class="track-info-cell">
                  <div class="track-thumb-emoji"><?= htmlspecialchars($v['thumb'] ?: '🎬') ?></div>
                  <div>
                    <div style="font-weight:700;font-size:.88rem;"><?= htmlspecialchars($v['title']) ?></div>
                    <div style="font-size:.78rem;color:var(--muted);"><?= htmlspecialchars($v['channel']) ?></div>
                  </div>
                </div>
              </td>
              <td><span class="type-badge type-<?= $v['type']==='youtube' ? 'yt' : ($v['type']==='hls' ? 'hls' : 'mp4') ?>"><?= strtoupper($v['type']) ?></span></td>
              <td><span class="badge <?= statusBadgeClass($v['status'] ?? 'approved') ?>"><?= htmlspecialchars(ucfirst($v['status'] ?? 'approved')) ?></span></td>
              <td>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                  <?php if (!empty($v['src'])): ?>
                  <a href="<?= htmlspecialchars($v['src']) ?>" target="_blank" class="btn btn-outline btn-sm" title="Open source">
                    <i class="fas fa-external-link-alt"></i>
                  </a>
                  <?php endif; ?>
                  <button class="btn btn-outline btn-sm"
                          onclick="openVideoModal(<?= htmlspecialchars(json_encode($v)) ?>)"
                          title="Edit video">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button class="btn btn-danger btn-sm" onclick="deleteMyVideo(<?= $v['id'] ?>)" title="Delete">
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

  <!-- ========== TAB: MY LIVE ========== -->
  <div id="tab-live" class="tab-pane">
    <div class="upload-card">
      <h3><i class="fas fa-broadcast-tower"></i>My Live Stream</h3>
      <p style="color:var(--muted);font-size:.85rem;margin-bottom:20px;">
        Configure your live stream. A newly created stream is marked
        <span class="badge badge-gold">Pending</span> until an admin approves it.
        <?php if ($myLiveStream): ?>
        Current status:
        <span class="badge <?= statusBadgeClass($myLiveStream['status'] ?? 'approved') ?>"><?= htmlspecialchars(ucfirst($myLiveStream['status'] ?? 'approved')) ?></span>
        <?php endif; ?>
      </p>

      <div style="display:flex;flex-direction:column;gap:14px;max-width:640px;">
        <div class="form-row">
          <div class="form-group" style="margin:0;"><label>Stream Title</label>
            <input type="text" class="form-input" id="live-title"
                   value="<?= htmlspecialchars($myLiveStream['title'] ?? (($user['creator_name'] ?: $user['name']) . ' Live')) ?>"/></div>
          <div class="form-group" style="margin:0;"><label>Host Name</label>
            <input type="text" class="form-input" id="live-host"
                   value="<?= htmlspecialchars($myLiveStream['host'] ?? ($user['creator_name'] ?: $user['name'])) ?>"/></div>
        </div>
        <div class="form-row">
          <div class="form-group" style="margin:0;"><label>Viewer Count</label>
            <input type="text" class="form-input" id="live-viewers"
                   value="<?= htmlspecialchars($myLiveStream['viewers'] ?? '0') ?>" placeholder="e.g. 128 or live"/></div>
          <div class="form-group" style="margin:0;"><label>Stream Type</label>
            <select class="form-input" id="live-type">
              <?php $lt = $myLiveStream['type'] ?? 'youtube'; ?>
              <option value="youtube"  <?= $lt==='youtube'  ?'selected':'' ?>>YouTube</option>
              <option value="facebook" <?= $lt==='facebook' ?'selected':'' ?>>Facebook</option>
              <option value="hls"      <?= $lt==='hls'      ?'selected':'' ?>>HLS (.m3u8)</option>
              <option value="radio"    <?= $lt==='radio'    ?'selected':'' ?>>Radio (audio stream)</option>
            </select>
          </div>
        </div>
        <div class="form-group" style="margin:0;">
          <label>Stream Source URL</label>
          <input type="url" class="form-input" id="live-src"
                 value="<?= htmlspecialchars($myLiveStream['src'] ?? '') ?>"
                 placeholder="https://www.youtube.com/embed/VIDEO_ID  or stream URL"/>
        </div>
        <div class="form-group" style="margin:0;display:flex;align-items:center;gap:10px;">
          <input type="checkbox" id="live-active" style="width:18px;height:18px;" <?= !empty($myLiveStream['active']) ? 'checked' : '' ?>/>
          <label style="margin:0;" for="live-active">
            <span class="live-indicator <?= !empty($myLiveStream['active']) ? 'on' : '' ?>" style="margin-right:6px;"></span>
            Mark as currently live / active
          </label>
        </div>
      </div>
      <div style="margin-top:20px;">
        <button class="btn btn-green" onclick="saveMyLive()"><i class="fas fa-save" style="margin-right:8px;"></i>Save Live Stream</button>
      </div>
    </div>
  </div>

</div><!-- /container -->

<!-- TRACK EDIT MODAL -->
<div class="modal-overlay" id="track-edit-modal">
  <div class="modal-box">
    <button class="modal-close" onclick="closeTrackEditModal()">&times;</button>
    <div class="modal-title"><i class="fas fa-edit" style="margin-right:8px;color:var(--green);"></i>Edit Track</div>
    <input type="hidden" id="track-edit-id"/>
    <div style="display:flex;flex-direction:column;gap:14px;">
      <div class="form-group" style="margin:0;">
        <label>Title</label>
        <input type="text" class="form-input" id="track-edit-title"/>
      </div>
      <div class="form-group" style="margin:0;">
        <label>Artist</label>
        <input type="text" class="form-input" id="track-edit-artist"/>
      </div>
      <div class="form-group" style="margin:0;">
        <label>Album</label>
        <input type="text" class="form-input" id="track-edit-album"/>
      </div>
      <div class="form-group" style="margin:0;">
        <label>Genre</label>
        <select class="form-input" id="track-edit-genre">
          <option>Gospel</option><option>Worship</option><option>Contemporary Worship</option>
          <option>Hymns</option><option>Praise</option><option>Classical Gospel</option>
          <option>Live</option><option>Christmas</option><option>Other</option>
        </select>
      </div>
      <div class="form-group" style="margin:0;">
        <label>Thumbnail Image <span style="font-weight:400;color:var(--muted);">(optional)</span></label>
        <input type="hidden" id="track-edit-remove-thumbnail" value=""/>
        <div style="display:flex;align-items:center;gap:14px;">
          <div id="track-edit-thumb-preview" onclick="document.getElementById('track-edit-thumb-input').click()"
               style="width:64px;height:64px;border-radius:10px;border:1px dashed var(--border);cursor:pointer;
                      display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0;background:var(--card);">
            <i class="fas fa-image" style="color:var(--muted);"></i>
          </div>
          <div style="display:flex;flex-direction:column;gap:6px;">
            <div style="display:flex;gap:8px;">
              <button type="button" class="btn btn-outline" style="padding:8px 16px;font-size:.82rem;"
                      onclick="document.getElementById('track-edit-thumb-input').click()">Replace</button>
              <button type="button" class="btn btn-outline" style="padding:8px 16px;font-size:.82rem;"
                      onclick="removeTrackEditThumbnail()">Remove</button>
            </div>
            <span style="color:var(--muted);font-size:.78rem;">JPG, PNG, WEBP or GIF &middot; Max <?= MAX_THUMB_MB ?>MB</span>
          </div>
          <input type="file" id="track-edit-thumb-input" accept="image/*" style="display:none;" onchange="trackEditThumbSelected(this)"/>
        </div>
      </div>
      <div class="form-group" style="margin:0;">
        <label>Fallback Color</label>
        <div style="display:flex;align-items:center;gap:10px;">
          <input type="color" id="track-edit-color" style="width:44px;height:38px;border:none;background:none;cursor:pointer;border-radius:6px;"/>
          <span style="color:var(--muted);font-size:.82rem;">Used to generate a thumbnail when no image is set</span>
        </div>
      </div>
      <div class="form-group" style="margin:0;">
        <label>Lyrics</label>
        <textarea class="form-input" id="track-edit-lyrics" rows="5" style="resize:vertical;"></textarea>
      </div>
      <div style="display:flex;gap:10px;">
        <button class="btn btn-green" onclick="saveTrackEdit()"><i class="fas fa-save" style="margin-right:6px;"></i>Save</button>
        <button class="btn btn-outline" onclick="closeTrackEditModal()">Cancel</button>
      </div>
    </div>
  </div>
</div>

<!-- VIDEO ADD/EDIT MODAL -->
<div class="modal-overlay" id="video-modal">
  <div class="modal-box">
    <button class="modal-close" onclick="closeVideoModal()">&times;</button>
    <div class="modal-title" id="video-modal-title"><i class="fas fa-film" style="margin-right:8px;color:var(--green);"></i>Add Video</div>
    <input type="hidden" id="video-edit-id"/>
    <div style="display:flex;flex-direction:column;gap:14px;">
      <div class="form-group" style="margin:0;">
        <label>Title</label>
        <input type="text" class="form-input" id="video-title" placeholder="Video title"/>
      </div>
      <div class="form-group" style="margin:0;">
        <label>Channel</label>
        <input type="text" class="form-input" id="video-channel" placeholder="Channel / artist name"/>
      </div>
      <div class="form-row">
        <div class="form-group" style="margin:0;">
          <label>Duration</label>
          <input type="text" class="form-input" id="video-duration" placeholder="e.g. 7:21"/>
        </div>
        <div class="form-group" style="margin:0;">
          <label>Type</label>
          <select class="form-input" id="video-type">
            <option value="youtube">YouTube</option>
            <option value="hls">HLS (.m3u8)</option>
            <option value="mp4">Direct MP4</option>
          </select>
        </div>
      </div>
      <div class="form-group" style="margin:0;">
        <label>Video Source URL</label>
        <input type="url" class="form-input" id="video-src" placeholder="https://www.youtube.com/embed/VIDEO_ID"/>
      </div>
      <div class="form-row">
        <div class="form-group" style="margin:0;">
          <label>Thumbnail Emoji</label>
          <input type="text" class="form-input" id="video-thumb" placeholder="🎬" maxlength="4" value="🎬"/>
        </div>
        <div></div>
      </div>
      <div class="form-group" style="margin:0;">
        <label>Description</label>
        <textarea class="form-input" id="video-description" rows="3" style="resize:vertical;" placeholder="Short description"></textarea>
      </div>
      <div style="display:flex;gap:10px;">
        <button class="btn btn-green" onclick="saveVideo()"><i class="fas fa-save" style="margin-right:6px;"></i>Save</button>
        <button class="btn btn-outline" onclick="closeVideoModal()">Cancel</button>
      </div>
    </div>
  </div>
</div>

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

// ---- Profile ----
let profileAvatarFile = null;
function profileAvatarSelected(input) {
  const f = input.files[0];
  if (!f) return;
  profileAvatarFile = f;
  const url = URL.createObjectURL(f);
  document.getElementById('profile-avatar-preview').innerHTML = '<img src="' + url + '" style="width:100%;height:100%;object-fit:cover;"/>';
}
function saveProfile() {
  const fd = new FormData();
  fd.append('creator_name', document.getElementById('profile-creator-name').value.trim());
  fd.append('bio', document.getElementById('profile-bio').value.trim());
  if (profileAvatarFile) fd.append('avatar', profileAvatarFile);
  fetch('api.php?action=update_profile', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(d => {
      if (d.success) { toast('Profile saved!'); setTimeout(() => location.reload(), 900); }
      else toast(d.error || 'Save failed.', true);
    });
}

// ---- Drop Zone (track upload) ----
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
let trackEditThumbFile = null;

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

  const title = document.getElementById('up-title');
  if (!title.value) {
    title.value = f.name.replace(/\.[^.]+$/, '').replace(/[_-]/g, ' ');
  }

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

// ---- Upload (creator track) ----
function uploadMyTrack() {
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
  fd.append('client_duration', detectedDuration);
  if (selectedThumbFile) fd.append('thumbnail', selectedThumbFile);

  const btn = document.getElementById('upload-btn');
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin" style="margin-right:8px;"></i>Uploading… please wait';

  const pw = document.getElementById('progress-wrap');
  const pf = document.getElementById('progress-fill');
  pw.style.display = 'block'; pf.style.width = '5%';

  const xhr = new XMLHttpRequest();
  xhr.open('POST', 'api.php?action=creator_upload_track', true);
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
      const isApproved = r.track?.status === 'approved';
      showUploadResult(true, '✅ "' + (r.track?.title || title) + '" uploaded' + (isApproved ? ' and is now live!' : ' — pending admin review.'));
      selectedFile = null; detectedDuration = ''; selectedThumbFile = null;
      document.getElementById('file-name-display').style.display = 'none';
      document.getElementById('up-title').value  = '';
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
  if (r && r.error) {
    const e = r.error.toLowerCase();
    if (e.includes('file too large'))    return 'This file is too large. Please use an audio file under <?= MAX_UPLOAD_MB ?>MB.';
    if (e.includes('invalid file type')) return 'This file type isn\'t supported. Please upload an MP3, WAV, AAC, FLAC, or OGG file.';
    if (e.includes('no file'))           return 'No file was received. Please select your audio file and try again.';
    if (e.includes('unauthorized') || e.includes('logged in') || e.includes('creator access') || e.includes('suspended'))
      return 'Your session has expired or your access has changed. Please refresh the page.';
    if (e.includes('failed to save'))    return 'We couldn\'t save your file. Please try again in a moment.';
  }
  if (status === 413) return 'This file is too large for the server. Please use a smaller audio file.';
  if (status === 401 || status === 403) return 'Your session has expired or your access has changed. Please refresh the page.';
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

// ---- Delete my track ----
function deleteMyTrack(id) {
  if (!confirm('Delete this track? This cannot be undone.')) return;
  fetch('api.php?action=creator_delete_track&id=' + id, { method: 'POST' })
    .then(r => r.json())
    .then(d => {
      if (d.success) {
        document.getElementById('track-row-' + id)?.remove();
        toast('Track deleted.');
      } else {
        toast(d.error || 'Delete failed.', true);
      }
    });
}

// ---- Track edit modal ----
function openTrackEditModal(track) {
  document.getElementById('track-edit-id').value     = track.id;
  document.getElementById('track-edit-title').value  = track.title;
  document.getElementById('track-edit-artist').value = track.artist;
  document.getElementById('track-edit-album').value  = track.album || '';
  document.getElementById('track-edit-lyrics').value = track.lyrics || '';
  document.getElementById('track-edit-color').value  = track.color || '#1a3a2a';

  const genreEl = document.getElementById('track-edit-genre');
  [...genreEl.options].forEach(o => { o.selected = (o.value === track.genre); });

  trackEditThumbFile = null;
  document.getElementById('track-edit-remove-thumbnail').value = '';
  document.getElementById('track-edit-thumb-input').value = '';
  document.getElementById('track-edit-thumb-preview').innerHTML =
    '<img src="api.php?action=thumbnail&id=' + track.id + '&t=' + Date.now() + '" style="width:100%;height:100%;object-fit:cover;"/>';

  document.getElementById('track-edit-modal').classList.add('open');
}
function closeTrackEditModal() {
  document.getElementById('track-edit-modal').classList.remove('open');
}
function trackEditThumbSelected(input) {
  const f = input.files[0];
  if (!f) return;
  trackEditThumbFile = f;
  document.getElementById('track-edit-remove-thumbnail').value = '';
  showThumbPreview(f, document.getElementById('track-edit-thumb-preview'));
}
function removeTrackEditThumbnail() {
  trackEditThumbFile = null;
  document.getElementById('track-edit-thumb-input').value = '';
  document.getElementById('track-edit-remove-thumbnail').value = '1';
  document.getElementById('track-edit-thumb-preview').innerHTML = '<i class="fas fa-image" style="color:var(--muted);"></i>';
}
function saveTrackEdit() {
  const id = document.getElementById('track-edit-id').value;
  const fd = new FormData();
  fd.append('title',  document.getElementById('track-edit-title').value);
  fd.append('artist', document.getElementById('track-edit-artist').value);
  fd.append('album',  document.getElementById('track-edit-album').value);
  fd.append('genre',  document.getElementById('track-edit-genre').value);
  fd.append('color',  document.getElementById('track-edit-color').value);
  fd.append('lyrics', document.getElementById('track-edit-lyrics').value);
  if (trackEditThumbFile) fd.append('thumbnail', trackEditThumbFile);
  else if (document.getElementById('track-edit-remove-thumbnail').value === '1') fd.append('remove_thumbnail', '1');
  fetch('api.php?action=creator_update_track&id=' + id, { method: 'POST', body: fd })
    .then(r => r.json())
    .then(d => {
      if (d.success) { toast('Track updated!'); closeTrackEditModal(); setTimeout(() => location.reload(), 900); }
      else toast(d.error || 'Update failed.', true);
    });
}

// ---- Video add/edit modal ----
function openVideoModal(video) {
  const isEdit = !!video;
  document.getElementById('video-modal-title').innerHTML =
    '<i class="fas fa-film" style="margin-right:8px;color:var(--green);"></i>' + (isEdit ? 'Edit Video' : 'Add Video');
  document.getElementById('video-edit-id').value      = isEdit ? video.id : '';
  document.getElementById('video-title').value        = isEdit ? video.title : '';
  document.getElementById('video-channel').value      = isEdit ? video.channel : '<?= htmlspecialchars(addslashes($user['creator_name'] ?: $user['name'])) ?>';
  document.getElementById('video-duration').value     = isEdit ? video.duration : '';
  document.getElementById('video-src').value          = isEdit ? video.src : '';
  document.getElementById('video-thumb').value        = isEdit ? (video.thumb || '🎬') : '🎬';
  document.getElementById('video-description').value  = isEdit ? (video.description || '') : '';

  const typeEl = document.getElementById('video-type');
  const type = isEdit ? video.type : 'youtube';
  [...typeEl.options].forEach(o => { o.selected = (o.value === type); });

  document.getElementById('video-modal').classList.add('open');
}
function closeVideoModal() {
  document.getElementById('video-modal').classList.remove('open');
}
function saveVideo() {
  const id = document.getElementById('video-edit-id').value;
  const fd = new FormData();
  fd.append('title',       document.getElementById('video-title').value.trim());
  fd.append('channel',     document.getElementById('video-channel').value.trim());
  fd.append('duration',    document.getElementById('video-duration').value.trim() || '0:00');
  fd.append('type',        document.getElementById('video-type').value);
  fd.append('src',         document.getElementById('video-src').value.trim());
  fd.append('thumb',       document.getElementById('video-thumb').value.trim() || '🎬');
  fd.append('description', document.getElementById('video-description').value.trim());

  if (!fd.get('title') || !fd.get('src')) { toast('Title and video source URL are required.', true); return; }

  const action = id ? 'creator_update_video&id=' + id : 'creator_upload_video';
  fetch('api.php?action=' + action, { method: 'POST', body: fd })
    .then(r => r.json())
    .then(d => {
      if (d.success) { toast(id ? 'Video updated!' : 'Video added — pending admin review.'); closeVideoModal(); setTimeout(() => location.reload(), 900); }
      else toast(d.error || 'Save failed.', true);
    });
}

// ---- Delete my video ----
function deleteMyVideo(id) {
  if (!confirm('Delete this video? This cannot be undone.')) return;
  fetch('api.php?action=creator_delete_video&id=' + id, { method: 'POST' })
    .then(r => r.json())
    .then(d => {
      if (d.success) {
        document.getElementById('video-row-' + id)?.remove();
        toast('Video deleted.');
      } else {
        toast(d.error || 'Delete failed.', true);
      }
    });
}

// ---- My Live ----
function saveMyLive() {
  const fd = new FormData();
  fd.append('title',   document.getElementById('live-title').value.trim());
  fd.append('host',    document.getElementById('live-host').value.trim());
  fd.append('viewers', document.getElementById('live-viewers').value.trim() || '0');
  fd.append('type',    document.getElementById('live-type').value);
  fd.append('src',     document.getElementById('live-src').value.trim());
  if (document.getElementById('live-active').checked) fd.append('active', '1');

  fetch('api.php?action=creator_update_live', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(d => {
      if (d.success) {
        const msg = d.live_stream && d.live_stream.status === 'pending'
          ? 'Live stream saved — pending admin review.'
          : 'Live stream saved.';
        toast(msg);
        setTimeout(() => location.reload(), 900);
      } else toast(d.error || 'Save failed.', true);
    });
}
</script>

<?php endif; ?>
</body>
</html>
