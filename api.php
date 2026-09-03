<?php
// ============================================================
//  RHEMA SOUNDS – REST API  (api.php)
// ============================================================
//  GET  ?action=tracks              → all tracks (JSON)
//  GET  ?action=track&id=N          → single track (JSON)
//  GET  ?action=thumbnail&id=N      → serve thumbnail image
//  GET  ?action=livestreams         → live stream rows (JSON)
//  GET  ?action=search&q=...        → search tracks (JSON)
//  GET  ?action=videos              → videos (JSON)
//  GET  ?action=channel&id=N        → public creator channel profile (JSON)
//  GET  ?action=genres              → genres (JSON)
//  GET  ?action=banners             → active featured banners (JSON)
//  GET  ?action=playlists           → playlists incl. track ids (JSON)
//  GET  ?action=schedule            → broadcast schedule rows (JSON)
//  GET  ?action=settings            → site settings key/value (JSON)
//  GET  ?action=avatar&id=N         → serve user avatar image
//  GET  ?action=me                  → current logged-in user + likes + playlists (JSON)
//  POST ?action=register            → create user account (name, email, password)
//  POST ?action=login               → log in with email + password
//  POST ?action=google_login        → log in/sign up with Google ID token
//  POST ?action=logout              → log out current user
//  POST ?action=update_profile      → update name / avatar (logged-in user)
//  POST ?action=like                → like a track (logged-in user)
//  POST ?action=unlike              → unlike a track (logged-in user)
//  POST ?action=create_playlist     → create a personal playlist (logged-in user)
//  POST ?action=delete_playlist     → delete own playlist (logged-in user)
//  POST ?action=playlist_add_track    → add track to own playlist (logged-in user)
//  POST ?action=playlist_remove_track → remove track from own playlist (logged-in user)
//  POST ?action=become_creator      → self-upgrade own account to creator (no admin needed)
//  POST ?action=upload              → upload new track (admin)
//  POST ?action=delete&id=N         → delete track (admin)
//  POST ?action=update_track&id=N   → edit track metadata (admin)
//  POST ?action=update_live         → save live stream config (admin)
//  POST ?action=update_videos       → save videos (admin)
//  POST ?action=update_genres       → save genres (admin)
//  POST ?action=update_banners      → save featured banners (admin)
//  POST ?action=update_playlists    → save playlists (admin)
//  POST ?action=update_schedule     → save broadcast schedule (admin)
//  POST ?action=update_settings     → save site settings (admin)
//
//  ---- Creator endpoints (role: creator, session-protected) ----
//  GET  ?action=creator_me              → creator profile + own content (JSON)
//  POST ?action=creator_upload_track    → upload a new track (status=pending)
//  POST ?action=creator_update_track&id=N → edit own track
//  POST ?action=creator_delete_track&id=N → delete own track
//  POST ?action=creator_upload_video    → add a new video (status=pending)
//  POST ?action=creator_update_video&id=N → edit own video
//  POST ?action=creator_delete_video&id=N → delete own video
//  POST ?action=creator_update_live     → create/update own live stream entry
//
//  ---- Admin oversight endpoints (admin) ----
//  GET  ?action=admin_users             → list all user accounts (JSON)
//  POST ?action=admin_set_role          → change a user's role (listener/creator)
//  POST ?action=admin_set_user_status   → suspend/activate a user account
//  POST ?action=admin_delete_user       → permanently terminate a user account
//  POST ?action=admin_set_content_status → approve/reject creator content
// ============================================================

require_once __DIR__ . '/config.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

// ---- Helper: attach creator profile info (name + avatar) to a content row ----
function decorateCreatorInfo(array $row): array {
    if (!empty($row['creator_id'])) {
        $creator = getUserById((int)$row['creator_id']);
        if ($creator) {
            $row['creator_name']   = $creator['creator_name'] ?: $creator['name'];
            $row['creator_avatar'] = !empty($creator['avatar'])
                ? ('api.php?action=avatar&id=' . $creator['id'])
                : '';
        }
    }
    return $row;
}

// ---- Helper: attach stream/thumbnail URLs (and creator info) to a track row ----
function decorateTrack(array $t): array {
    $t['src']       = 'stream.php?id=' . $t['id'];
    $t['thumbnail'] = 'api.php?action=thumbnail&id=' . $t['id'];
    return decorateCreatorInfo($t);
}

// ============================================================
//  PUBLIC ENDPOINTS
// ============================================================

if ($action === 'tracks' && $method === 'GET') {
    $tracks = array_map('decorateTrack', getTracks(true));
    jsonResponse(['tracks' => $tracks]);
}

if ($action === 'track' && $method === 'GET') {
    $id = (int)($_GET['id'] ?? 0);
    $t  = getTrackById($id);
    if (!$t) jsonResponse(['error' => 'Track not found'], 404);
    $isOwner = isUserLoggedIn() && currentUserId() === (int)($t['creator_id'] ?? 0);
    if (($t['status'] ?? 'approved') !== 'approved' && !isAdminLoggedIn() && !$isOwner) {
        jsonResponse(['error' => 'Track not found'], 404);
    }
    jsonResponse(decorateTrack($t));
}

if ($action === 'search' && $method === 'GET') {
    $q      = sanitizeString($_GET['q'] ?? '', 200);
    $tracks = array_map('decorateTrack', searchTracks($q, true));
    jsonResponse(['tracks' => $tracks, 'query' => $q]);
}

if ($action === 'thumbnail' && $method === 'GET') {
    $id   = (int)($_GET['id'] ?? 0);
    $path = getThumbnailPath($id);

    // Generate on-the-fly if missing
    if (!$path && $id > 0) {
        $t = getTrackById($id);
        if ($t) {
            $path = generateThumbnail($id, $t['title'], $t['artist'], $t['color'] ?? '');
        }
    }

    if ($path && file_exists($path)) {
        $ext      = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $extMimes = ['svg' => 'image/svg+xml', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
                     'png' => 'image/png', 'webp' => 'image/webp', 'gif' => 'image/gif'];
        $mime     = $extMimes[$ext] ?? 'image/jpeg';
        header('Content-Type: ' . $mime);
        header('Cache-Control: public, max-age=86400');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }

    // Ultimate fallback: inline SVG (no file I/O needed)
    $color = trackColor($id);
    header('Content-Type: image/svg+xml');
    header('Cache-Control: public, max-age=3600');
    echo '<svg width="400" height="400" xmlns="http://www.w3.org/2000/svg">'
       . '<rect width="400" height="400" fill="' . $color . '"/>'
       . '<text x="200" y="230" font-size="100" text-anchor="middle" fill="white">🎵</text>'
       . '</svg>';
    exit;
}

if ($action === 'livestreams' && $method === 'GET') {
    jsonResponse(['live_streams' => getLiveStreams(true)]);
}

if ($action === 'videos' && $method === 'GET') {
    jsonResponse(['videos' => array_map('decorateCreatorInfo', getVideos(true))]);
}

// ---- PUBLIC CREATOR CHANNEL PROFILE ----
if ($action === 'channel' && $method === 'GET') {
    $id      = (int)($_GET['id'] ?? 0);
    $creator = $id ? getUserById($id) : null;
    if (!$creator || ($creator['status'] ?? 'active') === 'suspended')
        jsonResponse(['error' => 'Channel not found'], 404);

    jsonResponse(['channel' => [
        'id'     => (int)$creator['id'],
        'name'   => $creator['creator_name'] ?: $creator['name'],
        'avatar' => !empty($creator['avatar']) ? ('api.php?action=avatar&id=' . $creator['id']) : '',
        'bio'    => $creator['bio'] ?: '',
    ]]);
}

if ($action === 'genres' && $method === 'GET') {
    jsonResponse(['genres' => getGenres()]);
}

if ($action === 'banners' && $method === 'GET') {
    jsonResponse(['banners' => getFeaturedBanners(true)]);
}

if ($action === 'playlists' && $method === 'GET') {
    jsonResponse(['playlists' => getPlaylists()]);
}

if ($action === 'schedule' && $method === 'GET') {
    jsonResponse(['schedule' => getBroadcastSchedule()]);
}

if ($action === 'settings' && $method === 'GET') {
    jsonResponse(['settings' => getSiteSettings()]);
}

// ============================================================
//  USER ACCOUNT ENDPOINTS (public sign-up / sign-in)
// ============================================================

if ($action === 'avatar' && $method === 'GET') {
    $id   = (int)($_GET['id'] ?? 0);
    $path = $id > 0 ? getAvatarPath($id) : '';

    if ($path && file_exists($path)) {
        $ext      = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $extMimes = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
                     'webp' => 'image/webp', 'gif' => 'image/gif'];
        header('Content-Type: ' . ($extMimes[$ext] ?? 'image/jpeg'));
        header('Cache-Control: public, max-age=86400');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }

    header('Content-Type: image/svg+xml');
    header('Cache-Control: public, max-age=3600');
    echo '<svg width="200" height="200" xmlns="http://www.w3.org/2000/svg">'
       . '<rect width="200" height="200" fill="#1db954"/>'
       . '<text x="100" y="125" font-size="90" text-anchor="middle" fill="white">👤</text>'
       . '</svg>';
    exit;
}

if ($action === 'me' && $method === 'GET') {
    if (!isUserLoggedIn()) jsonResponse(['user' => null]);
    $u = getUserById(currentUserId());
    if (!$u) { logOutUser(); jsonResponse(['user' => null]); }
    jsonResponse([
        'user'      => decorateUser($u),
        'likes'     => getUserLikedTrackIds($u['id']),
        'playlists' => getUserPlaylists($u['id']),
    ]);
}

// ============================================================
//  CREATOR ENDPOINTS (role: creator, session-protected)
// ============================================================

if ($action === 'creator_me' && $method === 'GET') {
    $creator = requireCreator();
    jsonResponse([
        'user'         => decorateUser($creator),
        'tracks'       => array_map('decorateTrack', getTracksByCreator((int)$creator['id'])),
        'videos'       => array_map('decorateCreatorInfo', getVideosByCreator((int)$creator['id'])),
        'live_streams' => array_map('decorateCreatorInfo', getLiveStreamsByCreator((int)$creator['id'])),
    ]);
}

if ($action === 'creator_upload_track' && $method === 'POST') {
    $creator = requireCreator();
    if (empty($_FILES['audio'])) jsonResponse(['error' => 'No file uploaded'], 400);

    $autoApprove = (getSiteSettings()['auto_approve_tracks'] ?? '1') !== '0';
    $result = processTrackUpload($_FILES['audio'], $_POST, (int)$creator['id'], $autoApprove ? 'approved' : 'pending');
    if (isset($result['error'])) jsonResponse(['error' => $result['error']], $result['code']);
    $id = $result['id'];

    if (!empty($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        saveCustomThumbnail($id, $_FILES['thumbnail']);
    }

    jsonResponse(['success' => true, 'track' => decorateTrack(getTrackById($id))]);
}

if ($action === 'creator_update_track' && $method === 'POST') {
    $creator = requireCreator();
    $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
    if (!$id || !trackBelongsToCreator($id, (int)$creator['id']))
        jsonResponse(['error' => 'Track not found'], 404);

    $fields = [];
    foreach (['title','artist','album','genre','lyrics'] as $f) {
        if (isset($_POST[$f])) $fields[$f] = sanitizeString($_POST[$f], $f === 'lyrics' ? 10000 : 255);
    }
    if (isset($_POST['color']) && preg_match('/^#[0-9a-fA-F]{6}$/', $_POST['color'])) {
        $fields['color'] = $_POST['color'];
    }
    updateTrack($id, $fields);

    if (!empty($fields['color'])) {
        $t = getTrackById($id);
        generateThumbnail($id, $t['title'], $t['artist'], $t['color']);
    }

    if (!empty($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        saveCustomThumbnail($id, $_FILES['thumbnail']);
    } elseif (!empty($_POST['remove_thumbnail'])) {
        deleteCustomThumbnail($id);
    }

    jsonResponse(['success' => true, 'track' => decorateTrack(getTrackById($id))]);
}

if ($action === 'creator_delete_track' && $method === 'POST') {
    $creator = requireCreator();
    $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
    if (!$id || !trackBelongsToCreator($id, (int)$creator['id']))
        jsonResponse(['error' => 'Track not found'], 404);

    $t = getTrackById($id);
    $audioPath = AUDIO_DIR . ($t['filename'] ?? '');
    if ($audioPath && file_exists($audioPath)) unlink($audioPath);
    foreach (['.jpg', '.svg'] as $ext) {
        $tp = THUMB_DIR . $id . $ext;
        if (file_exists($tp)) unlink($tp);
    }
    deleteCustomThumbnail($id);
    deleteTrackFromDb($id);
    jsonResponse(['success' => true]);
}

if ($action === 'creator_upload_video' && $method === 'POST') {
    $creator = requireCreator();
    $title = sanitizeString($_POST['title'] ?? '', 255);
    $src   = filter_var($_POST['src'] ?? '', FILTER_SANITIZE_URL);
    if ($title === '' || $src === '') jsonResponse(['error' => 'Title and video URL are required'], 400);

    $id = insertVideo([
        'title'       => $title,
        'channel'     => $_POST['channel']     ?? ($creator['creator_name'] ?: $creator['name']),
        'duration'    => $_POST['duration']    ?? '0:00',
        'type'        => $_POST['type']        ?? 'youtube',
        'src'         => $src,
        'thumb'       => $_POST['thumb']       ?? '🎬',
        'description' => $_POST['description'] ?? '',
        'creator_id'  => (int)$creator['id'],
        'status'      => 'pending',
    ]);
    jsonResponse(['success' => true, 'video' => decorateCreatorInfo(getVideoById($id))]);
}

if ($action === 'creator_update_video' && $method === 'POST') {
    $creator = requireCreator();
    $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
    if (!$id || !videoBelongsToCreator($id, (int)$creator['id']))
        jsonResponse(['error' => 'Video not found'], 404);

    $fields = [];
    foreach (['title','channel','duration','type','src','thumb','description'] as $f) {
        if (isset($_POST[$f])) $fields[$f] = $_POST[$f];
    }
    updateVideo($id, $fields);
    jsonResponse(['success' => true, 'video' => decorateCreatorInfo(getVideoById($id))]);
}

if ($action === 'creator_delete_video' && $method === 'POST') {
    $creator = requireCreator();
    $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
    if (!$id || !videoBelongsToCreator($id, (int)$creator['id']))
        jsonResponse(['error' => 'Video not found'], 404);

    deleteVideoFromDb($id);
    jsonResponse(['success' => true]);
}

if ($action === 'creator_update_live' && $method === 'POST') {
    $creator = requireCreator();
    $data = [
        'title'   => $_POST['title']   ?? (($creator['creator_name'] ?: $creator['name']) . ' Live'),
        'host'    => $_POST['host']    ?? ($creator['creator_name'] ?: $creator['name']),
        'viewers' => $_POST['viewers'] ?? '0',
        'type'    => $_POST['type']    ?? 'youtube',
        'src'     => $_POST['src']     ?? '',
        'active'  => !empty($_POST['active']),
    ];

    $existing = getLiveStreamsByCreator((int)$creator['id']);
    if ($existing) {
        $id = (int)$existing[0]['id'];
        updateLiveStream($id, $data);
    } else {
        $data['creator_id'] = (int)$creator['id'];
        $data['status']     = 'pending';
        $id = insertLiveStream($data);
    }
    jsonResponse(['success' => true, 'live_stream' => decorateCreatorInfo(getLiveStreamById($id))]);
}

// ============================================================
//  ADMIN: USER / CREATOR ACCOUNT MANAGEMENT (GET, session-protected)
// ============================================================

if ($action === 'admin_users' && $method === 'GET') {
    if (!isAdminLoggedIn()) jsonResponse(['error' => 'Unauthorized'], 401);
    jsonResponse(['users' => getAllUsers()]);
}

if ($action === 'register' && $method === 'POST') {
    $name     = sanitizeString($_POST['name'] ?? '', 255);
    $email    = strtolower(trim($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if ($name === '' || $email === '' || $password === '')
        jsonResponse(['error' => 'Name, email and password are required'], 400);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        jsonResponse(['error' => 'Invalid email address'], 400);
    if (strlen($password) < 6)
        jsonResponse(['error' => 'Password must be at least 6 characters'], 400);
    if (getUserByEmail($email))
        jsonResponse(['error' => 'An account with this email already exists'], 409);

    $id = createUser($name, $email, password_hash($password, PASSWORD_DEFAULT));
    logInUser($id);
    jsonResponse(['success' => true, 'user' => decorateUser(getUserById($id))]);
}

if ($action === 'login' && $method === 'POST') {
    $email    = strtolower(trim($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    $u = $email !== '' ? getUserByEmail($email) : null;
    if (!$u || empty($u['password_hash']) || !password_verify($password, $u['password_hash']))
        jsonResponse(['error' => 'Incorrect email or password'], 401);
    if (($u['status'] ?? 'active') === 'suspended')
        jsonResponse(['error' => 'This account has been suspended. Contact the site admin for assistance.'], 403);

    logInUser((int)$u['id']);
    jsonResponse(['success' => true, 'user' => decorateUser($u)]);
}

if ($action === 'google_login' && $method === 'POST') {
    $idToken = (string)($_POST['id_token'] ?? '');
    if ($idToken === '') jsonResponse(['error' => 'Missing Google ID token'], 400);

    $claims = verifyGoogleIdToken($idToken);
    if (!$claims) jsonResponse(['error' => 'Could not verify Google sign-in'], 401);

    $googleId = (string)$claims['sub'];
    $email    = strtolower((string)$claims['email']);
    $name     = (string)($claims['name'] ?? $email);

    $u = getUserByGoogleId($googleId);
    if (!$u) {
        $u = getUserByEmail($email);
        if ($u) {
            updateUser((int)$u['id'], ['google_id' => $googleId]);
            $u = getUserById((int)$u['id']);
        } else {
            $id = createUser($name, $email, null, $googleId);
            $u  = getUserById($id);
        }
    }

    if (($u['status'] ?? 'active') === 'suspended')
        jsonResponse(['error' => 'This account has been suspended. Contact the site admin for assistance.'], 403);

    logInUser((int)$u['id']);
    jsonResponse(['success' => true, 'user' => decorateUser($u)]);
}

if ($action === 'logout' && $method === 'POST') {
    logOutUser();
    jsonResponse(['success' => true]);
}

// ---- Everything below requires a logged-in user ----
if (in_array($action, ['update_profile','like','unlike','create_playlist','delete_playlist',
                       'playlist_add_track','playlist_remove_track','become_creator'], true) && $method === 'POST') {
    if (!isUserLoggedIn()) jsonResponse(['error' => 'You must be logged in'], 401);
    $userId = currentUserId();

    // ---- SELF-SERVICE: ACTIVATE CREATOR ACCESS (no admin needed) ----
    if ($action === 'become_creator') {
        setUserRole($userId, 'creator');
        jsonResponse(['success' => true, 'user' => decorateUser(getUserById($userId))]);
    }

    if ($action === 'update_profile') {
        $fields = [];
        if (isset($_POST['name'])) {
            $name = sanitizeString($_POST['name'], 255);
            if ($name !== '') $fields['name'] = $name;
        }
        if (isset($_POST['creator_name'])) {
            $fields['creator_name'] = sanitizeString($_POST['creator_name'], 255);
        }
        if (isset($_POST['bio'])) {
            $fields['bio'] = sanitizeString($_POST['bio'], 1000);
        }
        if ($fields) updateUser($userId, $fields);

        if (!empty($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            if (!saveAvatar($userId, $_FILES['avatar']))
                jsonResponse(['error' => 'Could not save avatar image (check file type/size)'], 400);
        }

        jsonResponse(['success' => true, 'user' => decorateUser(getUserById($userId))]);
    }

    if ($action === 'like') {
        $trackId = (int)($_POST['track_id'] ?? 0);
        if (!$trackId || !getTrackById($trackId)) jsonResponse(['error' => 'Track not found'], 404);
        likeTrack($userId, $trackId);
        jsonResponse(['success' => true]);
    }

    if ($action === 'unlike') {
        $trackId = (int)($_POST['track_id'] ?? 0);
        if (!$trackId) jsonResponse(['error' => 'Missing track_id'], 400);
        unlikeTrack($userId, $trackId);
        jsonResponse(['success' => true]);
    }

    if ($action === 'create_playlist') {
        $name = sanitizeString($_POST['name'] ?? '', 255);
        $desc = sanitizeString($_POST['description'] ?? '', 500);
        $emoji = sanitizeString($_POST['emoji'] ?? '🎵', 10);
        if ($name === '') jsonResponse(['error' => 'Playlist name is required'], 400);

        $id = createUserPlaylist($userId, $name, $desc, $emoji ?: '🎵');
        jsonResponse(['success' => true, 'playlists' => getUserPlaylists($userId), 'id' => $id]);
    }

    if ($action === 'delete_playlist') {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) jsonResponse(['error' => 'Missing id'], 400);
        if (!deleteUserPlaylist($userId, $id)) jsonResponse(['error' => 'Playlist not found'], 404);
        jsonResponse(['success' => true, 'playlists' => getUserPlaylists($userId)]);
    }

    if ($action === 'playlist_add_track') {
        $playlistId = (int)($_POST['playlist_id'] ?? 0);
        $trackId    = (int)($_POST['track_id'] ?? 0);
        if (!$playlistId || !$trackId) jsonResponse(['error' => 'Missing playlist_id or track_id'], 400);
        if (!userOwnsPlaylist($userId, $playlistId)) jsonResponse(['error' => 'Playlist not found'], 404);
        if (!getTrackById($trackId)) jsonResponse(['error' => 'Track not found'], 404);

        addTrackToUserPlaylist($playlistId, $trackId);
        jsonResponse(['success' => true, 'playlists' => getUserPlaylists($userId)]);
    }

    if ($action === 'playlist_remove_track') {
        $playlistId = (int)($_POST['playlist_id'] ?? 0);
        $trackId    = (int)($_POST['track_id'] ?? 0);
        if (!$playlistId || !$trackId) jsonResponse(['error' => 'Missing playlist_id or track_id'], 400);
        if (!userOwnsPlaylist($userId, $playlistId)) jsonResponse(['error' => 'Playlist not found'], 404);

        removeTrackFromUserPlaylist($playlistId, $trackId);
        jsonResponse(['success' => true, 'playlists' => getUserPlaylists($userId)]);
    }
}

// ============================================================
//  ADMIN ENDPOINTS (session-protected)
// ============================================================

if ($method === 'POST') {
    if (!isAdminLoggedIn()) jsonResponse(['error' => 'Unauthorized'], 401);

    // ---- UPLOAD ----
    if ($action === 'upload') {
        if (empty($_FILES['audio'])) jsonResponse(['error' => 'No file uploaded'], 400);

        $result = processTrackUpload($_FILES['audio'], $_POST, null, 'approved');
        if (isset($result['error'])) jsonResponse(['error' => $result['error']], $result['code']);
        $id = $result['id'];

        // If the admin supplied a custom thumbnail image, it takes precedence
        if (!empty($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
            saveCustomThumbnail($id, $_FILES['thumbnail']);
        }

        $track = getTrackById($id);
        jsonResponse(['success' => true, 'track' => decorateTrack($track)]);
    }

    // ---- DELETE ----
    if ($action === 'delete') {
        $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        if (!$id) jsonResponse(['error' => 'Missing id'], 400);

        $t = getTrackById($id);
        if (!$t) jsonResponse(['error' => 'Track not found'], 404);

        // Remove audio file
        $audioPath = AUDIO_DIR . ($t['filename'] ?? '');
        if ($audioPath && file_exists($audioPath)) unlink($audioPath);

        // Remove thumbnails
        foreach (['.jpg', '.svg'] as $ext) {
            $tp = THUMB_DIR . $id . $ext;
            if (file_exists($tp)) unlink($tp);
        }
        deleteCustomThumbnail($id);

        deleteTrackFromDb($id);
        jsonResponse(['success' => true]);
    }

    // ---- UPDATE TRACK METADATA ----
    if ($action === 'update_track') {
        $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        if (!$id) jsonResponse(['error' => 'Missing id'], 400);
        if (!getTrackById($id)) jsonResponse(['error' => 'Track not found'], 404);

        $fields = [];
        foreach (['title','artist','album','genre','lyrics'] as $f) {
            if (isset($_POST[$f])) $fields[$f] = sanitizeString($_POST[$f], $f === 'lyrics' ? 10000 : 255);
        }
        if (isset($_POST['color']) && preg_match('/^#[0-9a-fA-F]{6}$/', $_POST['color'])) {
            $fields['color'] = $_POST['color'];
        }
        updateTrack($id, $fields);

        // Regenerate thumbnail if color changed
        if (!empty($fields['color'])) {
            $t = getTrackById($id);
            generateThumbnail($id, $t['title'], $t['artist'], $t['color']);
        }

        // Custom thumbnail upload/removal (mutually exclusive)
        if (!empty($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
            saveCustomThumbnail($id, $_FILES['thumbnail']);
        } elseif (!empty($_POST['remove_thumbnail'])) {
            deleteCustomThumbnail($id);
        }

        jsonResponse(['success' => true, 'track' => decorateTrack(getTrackById($id))]);
    }

    // ---- UPDATE LIVE STREAM CONFIG ----
    if ($action === 'update_live') {
        $raw  = file_get_contents('php://input');
        $data = json_decode($raw, true);
        if (!$data || !isset($data['live_streams']))
            jsonResponse(['error' => 'Invalid JSON body'], 400);

        saveLiveStreams($data['live_streams']);
        jsonResponse(['success' => true]);
    }

    // ---- UPDATE VIDEOS ----
    if ($action === 'update_videos') {
        $raw  = file_get_contents('php://input');
        $data = json_decode($raw, true);
        if (!$data || !isset($data['videos']))
            jsonResponse(['error' => 'Invalid JSON body'], 400);

        saveVideos($data['videos']);
        jsonResponse(['success' => true, 'videos' => getVideos()]);
    }

    // ---- UPDATE GENRES ----
    if ($action === 'update_genres') {
        $raw  = file_get_contents('php://input');
        $data = json_decode($raw, true);
        if (!$data || !isset($data['genres']))
            jsonResponse(['error' => 'Invalid JSON body'], 400);

        saveGenres($data['genres']);
        jsonResponse(['success' => true, 'genres' => getGenres()]);
    }

    // ---- UPDATE FEATURED BANNERS ----
    if ($action === 'update_banners') {
        $raw  = file_get_contents('php://input');
        $data = json_decode($raw, true);
        if (!$data || !isset($data['banners']))
            jsonResponse(['error' => 'Invalid JSON body'], 400);

        saveFeaturedBanners($data['banners']);
        jsonResponse(['success' => true, 'banners' => getFeaturedBanners()]);
    }

    // ---- UPDATE PLAYLISTS ----
    if ($action === 'update_playlists') {
        $raw  = file_get_contents('php://input');
        $data = json_decode($raw, true);
        if (!$data || !isset($data['playlists']))
            jsonResponse(['error' => 'Invalid JSON body'], 400);

        savePlaylists($data['playlists']);
        jsonResponse(['success' => true, 'playlists' => getPlaylists()]);
    }

    // ---- UPDATE BROADCAST SCHEDULE ----
    if ($action === 'update_schedule') {
        $raw  = file_get_contents('php://input');
        $data = json_decode($raw, true);
        if (!$data || !isset($data['schedule']))
            jsonResponse(['error' => 'Invalid JSON body'], 400);

        saveBroadcastSchedule($data['schedule']);
        jsonResponse(['success' => true, 'schedule' => getBroadcastSchedule()]);
    }

    // ---- UPDATE SITE SETTINGS ----
    if ($action === 'update_settings') {
        $raw  = file_get_contents('php://input');
        $data = json_decode($raw, true);
        if (!$data || !isset($data['settings']))
            jsonResponse(['error' => 'Invalid JSON body'], 400);

        saveSiteSettings($data['settings']);
        jsonResponse(['success' => true, 'settings' => getSiteSettings()]);
    }

    // ---- SET USER ROLE (promote/demote creator) ----
    if ($action === 'admin_set_role') {
        $id   = (int)($_POST['id'] ?? 0);
        $role = (string)($_POST['role'] ?? '');
        if (!$id || !setUserRole($id, $role)) jsonResponse(['error' => 'Invalid id or role'], 400);
        jsonResponse(['success' => true, 'users' => getAllUsers()]);
    }

    // ---- SUSPEND / ACTIVATE A USER ACCOUNT ----
    if ($action === 'admin_set_user_status') {
        $id     = (int)($_POST['id'] ?? 0);
        $status = (string)($_POST['status'] ?? '');
        if (!$id || !setUserStatus($id, $status)) jsonResponse(['error' => 'Invalid id or status'], 400);
        jsonResponse(['success' => true, 'users' => getAllUsers()]);
    }

    // ---- TERMINATE (PERMANENTLY DELETE) A USER ACCOUNT ----
    if ($action === 'admin_delete_user') {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id || !deleteUser($id)) jsonResponse(['error' => 'Invalid id or account not found'], 400);
        jsonResponse(['success' => true, 'users' => getAllUsers()]);
    }

    // ---- APPROVE / REJECT CREATOR-SUBMITTED CONTENT ----
    if ($action === 'admin_set_content_status') {
        $table  = (string)($_POST['table'] ?? '');
        $id     = (int)($_POST['id'] ?? 0);
        $status = (string)($_POST['status'] ?? '');
        if (!$id || !setContentStatus($table, $id, $status))
            jsonResponse(['error' => 'Invalid table, id or status'], 400);
        jsonResponse(['success' => true]);
    }
}

jsonResponse(['error' => 'Unknown or unsupported action'], 400);