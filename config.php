<?php
// ============================================================
//  RHEMA SOUNDS – SHARED CONFIGURATION & HELPERS
// ============================================================

// ---- Load Environment Configuration ----
$envVars = [];
if (file_exists(__DIR__ . '/env/env.php')) {
    $envVars = require __DIR__ . '/env/env.php';
} elseif (file_exists(__DIR__ . '/env/.env')) {
    $lines = file(__DIR__ . '/env/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strncmp($line, '#', 1) === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $val) = explode('=', $line, 2);
            $envVars[trim($key)] = trim($val, "\"' ");
        }
    }
}

// ---- MySQL credentials ----
define('DB_HOST',    $envVars['DB_HOST']    ?? getenv('DB_HOST')    ?: 'localhost');
define('DB_PORT',    (int)($envVars['DB_PORT']    ?? getenv('DB_PORT')    ?: 3306));
define('DB_NAME',    $envVars['DB_NAME']    ?? getenv('DB_NAME')    ?: 'rhema_sounds');
define('DB_USER',    $envVars['DB_USER']    ?? getenv('DB_USER')    ?: 'root');
define('DB_PASS',    $envVars['DB_PASS']    ?? getenv('DB_PASS')    ?: '');
define('DB_CHARSET', 'utf8mb4');

// ---- Image compression pipeline (shared by avatars & thumbnails) ----
require_once __DIR__ . '/imagecompress.php';

// ---- File paths ----
define('AUDIO_DIR',  __DIR__ . '/uploads/audio/');
define('THUMB_DIR',  __DIR__ . '/uploads/thumbs/');
define('AVATAR_DIR', __DIR__ . '/uploads/avatars/');

// ---- Google Sign-In (Google Identity Services) ----
define('GOOGLE_CLIENT_ID', $envVars['GOOGLE_CLIENT_ID'] ?? getenv('GOOGLE_CLIENT_ID') ?: '');

// ---- Admin login ----
define('ADMIN_USERNAME',     $envVars['ADMIN_USERNAME']     ?? getenv('ADMIN_USERNAME')     ?: 'admin');
define('ADMIN_PASSWORD_HASH', $envVars['ADMIN_PASSWORD_HASH'] ?? getenv('ADMIN_PASSWORD_HASH') ?: '');

// ---- Upload limits ----
define('ALLOWED_MIME',  ['audio/mpeg','audio/mp3','audio/mpeg3','audio/x-mpeg-3','audio/x-mpeg',
                          'audio/mpg','audio/x-mp3','video/mpeg',
                          'audio/ogg','audio/wav','audio/flac','audio/aac','audio/x-m4a','audio/mp4']);
define('MAX_UPLOAD_MB', 50);

// ---- Custom thumbnail image upload limits ----
define('ALLOWED_IMAGE_MIME', ['image/jpeg','image/png','image/webp','image/gif']);
// Ceiling on the *incoming* file only. Everything is re-encoded and
// downscaled on arrival (see imagecompress.php), so a straight-from-the-
// phone photo is accepted and lands on disk at a few dozen KB.
define('MAX_THUMB_MB', 12);

// ---- Roles & content moderation ----
const VALID_ROLES      = ['listener', 'creator'];
const VALID_USER_STATUSES = ['active', 'suspended'];
const CONTENT_STATUSES = ['approved', 'pending', 'rejected'];

// ---- Create required directories ----
foreach ([AUDIO_DIR, THUMB_DIR, AVATAR_DIR] as $dir) {
    if (!is_dir($dir)) mkdir($dir, 0755, true);
}

// ============================================================
//  DATABASE CONNECTION (singleton PDO)
// ============================================================

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
        );
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(503);
            header('Content-Type: application/json');
            echo json_encode([
                'error'   => 'Database connection failed.',
                'details' => $e->getMessage(),
                'hint'    => 'Check DB_HOST, DB_NAME, DB_USER, DB_PASS in config.php',
            ]);
            exit;
        }
    }
    return $pdo;
}

// ============================================================
//  TRACK HELPERS
// ============================================================

function getTracks(bool $approvedOnly = false): array {
    $sql = 'SELECT * FROM tracks';
    if ($approvedOnly) $sql .= " WHERE status = 'approved'";
    $sql .= ' ORDER BY id ASC';
    return db()->query($sql)->fetchAll() ?: [];
}

function getTrackById(int $id): ?array {
    $st = db()->prepare('SELECT * FROM tracks WHERE id = ? LIMIT 1');
    $st->execute([$id]);
    $row = $st->fetch();
    return $row ?: null;
}

function insertTrack(array $data): int {
    $st = db()->prepare(
        'INSERT INTO tracks (title, artist, album, genre, duration, filename, color, lyrics, creator_id, status)
         VALUES (:title, :artist, :album, :genre, :duration, :filename, :color, :lyrics, :creator_id, :status)'
    );
    $st->execute([
        ':title'      => $data['title'],
        ':artist'     => $data['artist'],
        ':album'      => $data['album']    ?? '',
        ':genre'      => $data['genre']    ?? 'Gospel',
        ':duration'   => $data['duration'] ?? '0:00',
        ':filename'   => $data['filename'],
        ':color'      => $data['color']    ?? '#1a3a2a',
        ':lyrics'     => $data['lyrics']   ?? '',
        ':creator_id' => $data['creator_id'] ?? null,
        ':status'     => in_array($data['status'] ?? '', CONTENT_STATUSES, true) ? $data['status'] : 'approved',
    ]);
    return (int) db()->lastInsertId();
}

function updateTrack(int $id, array $fields): void {
    $allowed = ['title', 'artist', 'album', 'genre', 'color', 'lyrics', 'status'];
    $sets    = [];
    $params  = [];
    foreach ($allowed as $col) {
        if (!array_key_exists($col, $fields)) continue;
        if ($col === 'status' && !in_array($fields[$col], CONTENT_STATUSES, true)) continue;
        $sets[]          = "`$col` = :$col";
        $params[":$col"] = $fields[$col];
    }
    if (!$sets) return;
    $params[':id'] = $id;
    db()->prepare('UPDATE tracks SET ' . implode(', ', $sets) . ' WHERE id = :id')
       ->execute($params);
}

function deleteTrackFromDb(int $id): void {
    db()->prepare('DELETE FROM tracks WHERE id = ?')->execute([$id]);
}

function searchTracks(string $q, bool $approvedOnly = false): array {
    if (!trim($q)) return getTracks($approvedOnly);
    $like = '%' . $q . '%';
    $sql  = 'SELECT * FROM tracks WHERE (title LIKE :q OR artist LIKE :q OR album LIKE :q OR genre LIKE :q)';
    if ($approvedOnly) $sql .= " AND status = 'approved'";
    $sql .= ' ORDER BY id ASC';
    $st   = db()->prepare($sql);
    $st->execute([':q' => $like]);
    return $st->fetchAll() ?: [];
}

// Handles a track audio upload shared by the admin and creator upload
// endpoints. Saves the file, inserts the DB row (with the given creator/
// status), renames the file using the real id and generates its thumbnail.
// Returns ['id' => int] on success or ['error' => string, 'code' => int] on failure.
function processTrackUpload(array $file, array $post, ?int $creatorId, string $status): array {
    if ($file['error'] !== UPLOAD_ERR_OK)
        return ['error' => 'Upload error code: ' . $file['error'], 'code' => 400];

    if ($file['size'] > MAX_UPLOAD_MB * 1024 * 1024)
        return ['error' => 'File too large (max ' . MAX_UPLOAD_MB . 'MB)', 'code' => 400];

    $mime = mime_content_type($file['tmp_name']);
    if (!in_array($mime, ALLOWED_MIME))
        return ['error' => 'Invalid file type: ' . $mime, 'code' => 400];

    $title  = sanitizeString($post['title']  ?? pathinfo($file['name'], PATHINFO_FILENAME));
    $artist = sanitizeString($post['artist'] ?? 'Unknown Artist');
    $album  = sanitizeString($post['album']  ?? '');
    $genre  = sanitizeString($post['genre']  ?? 'Gospel');
    $lyrics = sanitizeString($post['lyrics'] ?? '', 10000);
    $color  = preg_match('/^#[0-9a-fA-F]{6}$/', $post['color'] ?? '') ? $post['color'] : '';

    $tempId   = time();
    $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) ?: 'mp3';
    $safeName = preg_replace('/[^a-z0-9_-]/', '_', strtolower($title));
    $tmpFile  = AUDIO_DIR . 'tmp_' . $tempId . '.' . $ext;

    if (!move_uploaded_file($file['tmp_name'], $tmpFile))
        return ['error' => 'Failed to save file on server', 'code' => 500];

    $duration = getAudioDuration($tmpFile);
    if ($duration === '0:00' && !empty($post['client_duration'])) {
        $cd = trim($post['client_duration']);
        if (preg_match('/^\d+:\d{2}$/', $cd)) $duration = $cd;
    }

    $id = insertTrack([
        'title'      => $title,
        'artist'     => $artist,
        'album'      => $album,
        'genre'      => $genre,
        'duration'   => $duration,
        'filename'   => 'tmp_' . $tempId . '.' . $ext, // updated below
        'color'      => $color ?: trackColor(0),       // placeholder; re-applied with real id
        'lyrics'     => $lyrics,
        'creator_id' => $creatorId,
        'status'     => $status,
    ]);

    // Rename file to final name using real DB id
    $finalName = $id . '_' . $safeName . '.' . $ext;
    $finalPath = AUDIO_DIR . $finalName;
    rename($tmpFile, $finalPath);

    // Update filename and correct color in DB
    $finalColor = $color ?: trackColor($id);
    db()->prepare('UPDATE tracks SET filename = ?, color = ? WHERE id = ?')
        ->execute([$finalName, $finalColor, $id]);

    // Generate the color-based thumbnail (always — acts as the fallback)
    generateThumbnail($id, $title, $artist, $finalColor);

    return ['id' => $id];
}

// ---- Creator ownership: tracks ----
function getTracksByCreator(int $creatorId): array {
    $st = db()->prepare('SELECT * FROM tracks WHERE creator_id = ? ORDER BY id DESC');
    $st->execute([$creatorId]);
    return $st->fetchAll() ?: [];
}

function trackBelongsToCreator(int $trackId, int $creatorId): bool {
    $st = db()->prepare('SELECT 1 FROM tracks WHERE id = ? AND creator_id = ?');
    $st->execute([$trackId, $creatorId]);
    return (bool)$st->fetchColumn();
}

// ============================================================
//  LIVE STREAM HELPERS
// ============================================================

function getLiveStreams(bool $approvedOnly = false): array {
    $sql = 'SELECT * FROM live_streams';
    if ($approvedOnly) $sql .= " WHERE status = 'approved'";
    $sql .= ' ORDER BY sort_order ASC, id ASC';
    return db()->query($sql)->fetchAll() ?: [];
}

function saveLiveStreams(array $streams): void {
    $pdo = db();
    // Only the admin-managed (non-creator) rows are bulk-replaced here so
    // that creator-owned live streams (added via the creator dashboard)
    // are preserved.
    $pdo->exec('DELETE FROM live_streams WHERE creator_id IS NULL');
    $st  = $pdo->prepare(
        'INSERT INTO live_streams (title, host, viewers, type, src, active, sort_order, creator_id, status)
         VALUES (:title, :host, :viewers, :type, :src, :active, :sort_order, NULL, "approved")'
    );
    foreach ($streams as $i => $s) {
        $st->execute([
            ':title'      => sanitizeString($s['title']   ?? ''),
            ':host'       => sanitizeString($s['host']    ?? ''),
            ':viewers'    => sanitizeString($s['viewers'] ?? '0'),
            ':type'       => in_array($s['type'] ?? '', ['youtube','facebook','hls','radio'])
                                ? $s['type'] : 'radio',
            ':src'        => filter_var($s['src'] ?? '', FILTER_SANITIZE_URL),
            ':active'     => (int)(bool)($s['active'] ?? false),
            ':sort_order' => $i,
        ]);
    }
}

function getLiveStreamById(int $id): ?array {
    $st = db()->prepare('SELECT * FROM live_streams WHERE id = ?');
    $st->execute([$id]);
    return $st->fetch() ?: null;
}

// ---- Creator ownership: live streams ----
function getLiveStreamsByCreator(int $creatorId): array {
    $st = db()->prepare('SELECT * FROM live_streams WHERE creator_id = ? ORDER BY id DESC');
    $st->execute([$creatorId]);
    return $st->fetchAll() ?: [];
}

function liveStreamBelongsToCreator(int $streamId, int $creatorId): bool {
    $st = db()->prepare('SELECT 1 FROM live_streams WHERE id = ? AND creator_id = ?');
    $st->execute([$streamId, $creatorId]);
    return (bool)$st->fetchColumn();
}

function insertLiveStream(array $data): int {
    $sortOrder = (int)db()->query('SELECT COALESCE(MAX(sort_order),0)+1 FROM live_streams')->fetchColumn();
    $st = db()->prepare(
        'INSERT INTO live_streams (title, host, viewers, type, src, active, sort_order, creator_id, status)
         VALUES (:title, :host, :viewers, :type, :src, :active, :sort_order, :creator_id, :status)'
    );
    $st->execute([
        ':title'      => sanitizeString($data['title']   ?? '', 255),
        ':host'       => sanitizeString($data['host']    ?? '', 255),
        ':viewers'    => sanitizeString($data['viewers'] ?? '0', 50),
        ':type'       => in_array($data['type'] ?? '', ['youtube','facebook','hls','radio'])
                            ? $data['type'] : 'radio',
        ':src'        => filter_var($data['src'] ?? '', FILTER_SANITIZE_URL),
        ':active'     => (int)(bool)($data['active'] ?? false),
        ':sort_order' => $sortOrder,
        ':creator_id' => $data['creator_id'] ?? null,
        ':status'     => in_array($data['status'] ?? '', CONTENT_STATUSES, true) ? $data['status'] : 'approved',
    ]);
    return (int)db()->lastInsertId();
}

function updateLiveStream(int $id, array $fields): void {
    $allowed = ['title','host','viewers','type','src','active','status'];
    $sets    = [];
    $params  = [];
    foreach ($allowed as $col) {
        if (!array_key_exists($col, $fields)) continue;
        if ($col === 'type' && !in_array($fields[$col], ['youtube','facebook','hls','radio'], true)) continue;
        if ($col === 'status' && !in_array($fields[$col], CONTENT_STATUSES, true)) continue;
        if ($col === 'active') $fields[$col] = (int)(bool)$fields[$col];
        $sets[]          = "`$col` = :$col";
        $params[":$col"] = $fields[$col];
    }
    if (!$sets) return;
    $params[':id'] = $id;
    db()->prepare('UPDATE live_streams SET ' . implode(', ', $sets) . ' WHERE id = :id')->execute($params);
}

function deleteLiveStreamFromDb(int $id): void {
    db()->prepare('DELETE FROM live_streams WHERE id = ?')->execute([$id]);
}

// ============================================================
//  VIDEO HELPERS
// ============================================================

function getVideos(bool $approvedOnly = false): array {
    $sql = 'SELECT * FROM videos';
    if ($approvedOnly) $sql .= " WHERE status = 'approved'";
    $sql .= ' ORDER BY sort_order ASC, id ASC';
    return db()->query($sql)->fetchAll() ?: [];
}

function saveVideos(array $videos): void {
    $pdo = db();
    // Only the admin-managed (non-creator) rows are bulk-replaced here so
    // that creator-owned videos (added via the creator dashboard) are preserved.
    $pdo->exec('DELETE FROM videos WHERE creator_id IS NULL');
    $st  = $pdo->prepare(
        'INSERT INTO videos (title, channel, duration, type, src, thumb, description, sort_order, creator_id, status)
         VALUES (:title, :channel, :duration, :type, :src, :thumb, :description, :sort_order, NULL, "approved")'
    );
    foreach ($videos as $i => $v) {
        $st->execute([
            ':title'       => sanitizeString($v['title']    ?? ''),
            ':channel'     => sanitizeString($v['channel']  ?? ''),
            ':duration'    => sanitizeString($v['duration'] ?? '0:00', 20),
            ':type'        => in_array($v['type'] ?? '', ['youtube','hls','mp4'])
                                  ? $v['type'] : 'youtube',
            ':src'         => filter_var($v['src'] ?? '', FILTER_SANITIZE_URL),
            ':thumb'       => sanitizeString($v['thumb'] ?? '🎬', 10),
            ':description' => sanitizeString($v['description'] ?? '', 1000),
            ':sort_order'  => $i,
        ]);
    }
}

function getVideoById(int $id): ?array {
    $st = db()->prepare('SELECT * FROM videos WHERE id = ?');
    $st->execute([$id]);
    return $st->fetch() ?: null;
}

// ---- Creator ownership: videos ----
function getVideosByCreator(int $creatorId): array {
    $st = db()->prepare('SELECT * FROM videos WHERE creator_id = ? ORDER BY id DESC');
    $st->execute([$creatorId]);
    return $st->fetchAll() ?: [];
}

function videoBelongsToCreator(int $videoId, int $creatorId): bool {
    $st = db()->prepare('SELECT 1 FROM videos WHERE id = ? AND creator_id = ?');
    $st->execute([$videoId, $creatorId]);
    return (bool)$st->fetchColumn();
}

function insertVideo(array $data): int {
    $sortOrder = (int)db()->query('SELECT COALESCE(MAX(sort_order),0)+1 FROM videos')->fetchColumn();
    $st = db()->prepare(
        'INSERT INTO videos (title, channel, duration, type, src, thumb, description, sort_order, creator_id, status)
         VALUES (:title, :channel, :duration, :type, :src, :thumb, :description, :sort_order, :creator_id, :status)'
    );
    $st->execute([
        ':title'       => sanitizeString($data['title']    ?? '', 255),
        ':channel'     => sanitizeString($data['channel']  ?? '', 255),
        ':duration'    => sanitizeString($data['duration'] ?? '0:00', 20),
        ':type'        => in_array($data['type'] ?? '', ['youtube','hls','mp4'])
                              ? $data['type'] : 'youtube',
        ':src'         => filter_var($data['src'] ?? '', FILTER_SANITIZE_URL),
        ':thumb'       => sanitizeString($data['thumb'] ?? '🎬', 10),
        ':description' => sanitizeString($data['description'] ?? '', 1000),
        ':sort_order'  => $sortOrder,
        ':creator_id'  => $data['creator_id'] ?? null,
        ':status'      => in_array($data['status'] ?? '', CONTENT_STATUSES, true) ? $data['status'] : 'approved',
    ]);
    return (int)db()->lastInsertId();
}

function updateVideo(int $id, array $fields): void {
    $allowed = ['title','channel','duration','type','src','thumb','description','status'];
    $sets    = [];
    $params  = [];
    foreach ($allowed as $col) {
        if (!array_key_exists($col, $fields)) continue;
        if ($col === 'type' && !in_array($fields[$col], ['youtube','hls','mp4'], true)) continue;
        if ($col === 'status' && !in_array($fields[$col], CONTENT_STATUSES, true)) continue;
        $sets[]          = "`$col` = :$col";
        $params[":$col"] = $fields[$col];
    }
    if (!$sets) return;
    $params[':id'] = $id;
    db()->prepare('UPDATE videos SET ' . implode(', ', $sets) . ' WHERE id = :id')->execute($params);
}

function deleteVideoFromDb(int $id): void {
    db()->prepare('DELETE FROM videos WHERE id = ?')->execute([$id]);
}

// ============================================================
//  GENRE HELPERS
// ============================================================

function getGenres(): array {
    return db()->query('SELECT * FROM genres ORDER BY sort_order ASC, id ASC')->fetchAll() ?: [];
}

function saveGenres(array $genres): void {
    $pdo = db();
    $pdo->exec('DELETE FROM genres');
    $st  = $pdo->prepare(
        'INSERT INTO genres (name, color, emoji, sort_order) VALUES (:name, :color, :emoji, :sort_order)'
    );
    foreach ($genres as $i => $g) {
        $st->execute([
            ':name'       => sanitizeString($g['name'] ?? '', 100),
            ':color'      => preg_match('/^#[0-9a-fA-F]{6}$/', $g['color'] ?? '') ? $g['color'] : '#1db954',
            ':emoji'      => sanitizeString($g['emoji'] ?? '🎵', 10),
            ':sort_order' => $i,
        ]);
    }
}

// ============================================================
//  FEATURED BANNER HELPERS
// ============================================================

function getFeaturedBanners(bool $activeOnly = false): array {
    $sql = 'SELECT * FROM featured_banners';
    if ($activeOnly) $sql .= ' WHERE active = 1';
    $sql .= ' ORDER BY sort_order ASC, id ASC';
    return db()->query($sql)->fetchAll() ?: [];
}

function saveFeaturedBanners(array $banners): void {
    $pdo = db();
    $pdo->exec('DELETE FROM featured_banners');
    $st  = $pdo->prepare(
        'INSERT INTO featured_banners (title, subtitle, gradient, emoji, action_type, action_target, sort_order, active)
         VALUES (:title, :subtitle, :gradient, :emoji, :action_type, :action_target, :sort_order, :active)'
    );
    foreach ($banners as $i => $b) {
        $st->execute([
            ':title'         => sanitizeString($b['title']    ?? ''),
            ':subtitle'      => sanitizeString($b['subtitle'] ?? ''),
            ':gradient'      => sanitizeString($b['gradient'] ?? 'linear-gradient(135deg,#1a3a2a,#0d7a3a)', 255),
            ':emoji'         => sanitizeString($b['emoji']    ?? '🎵', 10),
            ':action_type'   => in_array($b['action_type'] ?? '', ['track','playlist','page','url'])
                                    ? $b['action_type'] : 'page',
            ':action_target' => sanitizeString($b['action_target'] ?? 'home', 255),
            ':sort_order'    => $i,
            ':active'        => (int)(bool)($b['active'] ?? true),
        ]);
    }
}

// ============================================================
//  PLAYLIST HELPERS
// ============================================================

function getPlaylists(): array {
    $playlists = db()->query('SELECT * FROM playlists ORDER BY sort_order ASC, id ASC')->fetchAll() ?: [];
    if (!$playlists) return [];

    $st = db()->prepare('SELECT track_id FROM playlist_tracks WHERE playlist_id = ? ORDER BY sort_order ASC');
    foreach ($playlists as &$pl) {
        $st->execute([$pl['id']]);
        $pl['tracks']   = array_map('intval', array_column($st->fetchAll(), 'track_id'));
        $pl['isLiked']  = (bool) $pl['is_liked'];
        unset($pl['is_liked']);
    }
    return $playlists;
}

function savePlaylists(array $playlists): void {
    $pdo = db();
    $pdo->exec('DELETE FROM playlist_tracks');
    $pdo->exec('DELETE FROM playlists');
    $stPl = $pdo->prepare(
        'INSERT INTO playlists (id, name, description, emoji, is_liked, sort_order)
         VALUES (:id, :name, :description, :emoji, :is_liked, :sort_order)'
    );
    $stTr = $pdo->prepare(
        'INSERT INTO playlist_tracks (playlist_id, track_id, sort_order) VALUES (:pid, :tid, :sort)'
    );
    foreach ($playlists as $i => $pl) {
        $id = sanitizeString($pl['id'] ?? ('pl' . ($i + 1)), 64);
        $stPl->execute([
            ':id'          => $id,
            ':name'        => sanitizeString($pl['name']        ?? '', 255),
            ':description' => sanitizeString($pl['description'] ?? $pl['desc'] ?? '', 500),
            ':emoji'       => sanitizeString($pl['emoji'] ?? '🎵', 10),
            ':is_liked'    => (int)(bool)($pl['isLiked'] ?? $pl['is_liked'] ?? false),
            ':sort_order'  => $i,
        ]);
        foreach (($pl['tracks'] ?? []) as $j => $trackId) {
            $stTr->execute([':pid' => $id, ':tid' => (int)$trackId, ':sort' => $j]);
        }
    }
}

// ============================================================
//  BROADCAST SCHEDULE HELPERS
// ============================================================

function getBroadcastSchedule(): array {
    return db()->query('SELECT * FROM broadcast_schedule ORDER BY sort_order ASC, id ASC')->fetchAll() ?: [];
}

function saveBroadcastSchedule(array $rows): void {
    $pdo = db();
    $pdo->exec('DELETE FROM broadcast_schedule');
    $st  = $pdo->prepare(
        'INSERT INTO broadcast_schedule (day, time_range, show_name, host, sort_order)
         VALUES (:day, :time_range, :show_name, :host, :sort_order)'
    );
    foreach ($rows as $i => $r) {
        $st->execute([
            ':day'        => sanitizeString($r['day']        ?? '', 20),
            ':time_range' => sanitizeString($r['time_range'] ?? $r['time'] ?? '', 50),
            ':show_name'  => sanitizeString($r['show_name']  ?? $r['show'] ?? '', 255),
            ':host'       => sanitizeString($r['host']       ?? '', 255),
            ':sort_order' => $i,
        ]);
    }
}

// ============================================================
//  SITE SETTINGS HELPERS
// ============================================================

function getSiteSettings(): array {
    $rows = db()->query('SELECT setting_key, setting_value FROM site_settings')->fetchAll() ?: [];
    $out = [];
    foreach ($rows as $r) $out[$r['setting_key']] = $r['setting_value'];
    return $out;
}

function saveSiteSettings(array $settings): void {
    $st = db()->prepare(
        'INSERT INTO site_settings (setting_key, setting_value) VALUES (:k, :v)
         ON DUPLICATE KEY UPDATE setting_value = :v2'
    );
    foreach ($settings as $key => $value) {
        $k = preg_replace('/[^a-z0-9_]/', '', strtolower((string)$key));
        if ($k === '') continue;
        $v = sanitizeString((string)$value, 1000);
        $st->execute([':k' => $k, ':v' => $v, ':v2' => $v]);
    }
}

// ============================================================
//  UTILITIES
// ============================================================

function trackColor(int $index): string {
    $palette = [
        '#1a3a2a','#001a3a','#3a2a00','#2a003a',
        '#3a0a00','#1a2a3a','#3a001a','#1a3a1a',
        '#2a1a00','#003a2a','#1a001a','#002a3a',
    ];
    return $palette[$index % count($palette)];
}

function sanitizeString(string $s, int $max = 255): string {
    return mb_substr(trim(strip_tags($s)), 0, $max);
}

// ============================================================
//  AUDIO DURATION (ffprobe)
// ============================================================

function getAudioDuration(string $filepath): string {
    if (isCommandAvailable('ffprobe')) {
        $out = @shell_exec(
            'ffprobe -v quiet -print_format json -show_format ' .
            escapeshellarg($filepath) . ' 2>/dev/null'
        );
        if ($out) {
            $data = json_decode($out, true);
            if (!empty($data['format']['duration'])) {
                $secs = (int) $data['format']['duration'];
                return floor($secs / 60) . ':' . str_pad($secs % 60, 2, '0', STR_PAD_LEFT);
            }
        }
    }
    return '0:00';
}

function isCommandAvailable(string $cmd): bool {
    if (!function_exists('shell_exec')) return false;
    $disabled = array_map('trim', explode(',', ini_get('disable_functions')));
    if (in_array('shell_exec', $disabled)) return false;
    return !empty(trim(@shell_exec('which ' . escapeshellarg($cmd) . ' 2>/dev/null') ?? ''));
}

// ============================================================
//  THUMBNAIL GENERATION
// ============================================================

// User-uploaded thumbnail images are stored as "<id>_custom.<ext>" so they
// never collide with the auto-generated "<id>.jpg" / "<id>.svg" fallback —
// this lets an admin remove a custom thumbnail and fall back cleanly.
const CUSTOM_THUMB_EXTS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

function getCustomThumbnailPath(int $trackId): string {
    foreach (CUSTOM_THUMB_EXTS as $ext) {
        $p = THUMB_DIR . $trackId . '_custom.' . $ext;
        if (file_exists($p)) return $p;
    }
    return '';
}

function deleteCustomThumbnail(int $trackId): void {
    // Clears the primary plus every generated variant (@md / @sm).
    imgDeleteVariants(THUMB_DIR, $trackId . '_custom');
}

// Accepts an uploaded cover image and stores a compressed WebP (or
// JPEG/PNG fallback) set instead of the original: a 640px primary for
// the now-playing art plus 256px and 96px variants for cards and rows.
function saveCustomThumbnail(int $trackId, array $file): bool {
    if ($file['error'] !== UPLOAD_ERR_OK) return false;
    if ($file['size'] > MAX_THUMB_MB * 1024 * 1024) return false;

    // Validate against the real file header, not the submitted name.
    $mime = mime_content_type($file['tmp_name']);
    if (!in_array($mime, ALLOWED_IMAGE_MIME, true)) return false;
    if (!imgProbe($file['tmp_name'])) return false;

    $res = imgCompressTo($file['tmp_name'], THUMB_DIR, $trackId . '_custom', 'thumb', true);
    if ($res['ok']) @unlink($file['tmp_name']);
    return $res['ok'];
}

// Resolves the stored cover at the requested variant, building it on
// demand for images that predate the compression pipeline.
function getCustomThumbnailVariant(int $trackId, string $suffix): string {
    return imgEnsureVariant(THUMB_DIR, $trackId . '_custom', $suffix, 'thumb', true);
}

function getThumbnailPath(int $trackId): string {
    $custom = getCustomThumbnailPath($trackId);
    if ($custom) return $custom;
    foreach (['.jpg', '.svg'] as $ext) {
        $p = THUMB_DIR . $trackId . $ext;
        if (file_exists($p)) return $p;
    }
    return '';
}

function generateThumbnail(int $trackId, string $title, string $artist, string $colorHex = ''): string {
    if (!$colorHex) $colorHex = trackColor($trackId);
    return extension_loaded('gd')
        ? _generateGdThumbnail($trackId, $title, $artist, $colorHex)
        : _generateSvgThumbnail($trackId, $title, $artist, $colorHex);
}

function _generateGdThumbnail(int $id, string $title, string $artist, string $hex): string {
    $size = 400;
    $img  = imagecreatetruecolor($size, $size);
    $r    = hexdec(substr(ltrim($hex, '#'), 0, 2));
    $g    = hexdec(substr(ltrim($hex, '#'), 2, 2));
    $b    = hexdec(substr(ltrim($hex, '#'), 4, 2));

    for ($y = 0; $y < $size; $y++) {
        $ratio = $y / $size;
        $lc    = imagecolorallocate($img, (int)($r*(1-$ratio*.5)), (int)($g*(1-$ratio*.5)), (int)($b*(1-$ratio*.5)));
        imageline($img, 0, $y, $size, $y, $lc);
    }
    $g1 = imagecolorallocatealpha($img, min(255,$r+100), min(255,$g+100), min(255,$b+100), 90);
    $g2 = imagecolorallocatealpha($img, min(255,$r+60),  min(255,$g+60),  min(255,$b+60),  100);
    imagefilledellipse($img, $size, 0, 380, 380, $g1);
    imagefilledellipse($img, 0, $size, 260, 260, $g2);

    $white  = imagecolorallocate($img, 255, 255, 255);
    $shadow = imagecolorallocate($img, max(0,$r-40), max(0,$g-40), max(0,$b-40));
    imagefilledrectangle($img, 192, 110, 208, 265, $white);
    imagefilledrectangle($img, 238, 92,  254, 248, $white);
    imagefilledrectangle($img, 192, 110, 254, 128, $white);
    imagefilledellipse($img, 168, 275, 75, 58, $white);
    imagefilledellipse($img, 218, 258, 68, 52, $white);
    imagefilledellipse($img, 168, 280, 38, 28, $shadow);
    imagefilledellipse($img, 218, 262, 32, 24, $shadow);

    $white2 = imagecolorallocate($img, 255, 255, 255);
    $gray   = imagecolorallocate($img, 170, 170, 170);
    $titleStr  = mb_strlen($title)  > 24 ? mb_substr($title,  0, 21).'…' : $title;
    $artistStr = mb_strlen($artist) > 30 ? mb_substr($artist, 0, 27).'…' : $artist;

    $fontPaths = [
        '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
        '/usr/share/fonts/dejavu/DejaVuSans-Bold.ttf',
        '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
        '/usr/share/fonts/truetype/freefont/FreeSansBold.ttf',
        'C:\\Windows\\Fonts\\arialbd.ttf',
        '/System/Library/Fonts/Helvetica.ttc',
    ];
    $font = null;
    foreach ($fontPaths as $fp) { if (file_exists($fp)) { $font = $fp; break; } }

    if ($font) {
        imagettftext($img, 18, 0, 18, 352, $white2, $font, $titleStr);
        imagettftext($img, 13, 0, 18, 380, $gray,   $font, $artistStr);
    } else {
        imagestring($img, 5, 14, 340, $titleStr,  $white2);
        imagestring($img, 3, 14, 368, $artistStr, $gray);
    }

    $path = THUMB_DIR . $id . '.jpg';
    imagejpeg($img, $path, 92);
    imagedestroy($img);
    return $path;
}

function _generateSvgThumbnail(int $id, string $title, string $artist, string $hex): string {
    $et   = fn($s) => htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    $r    = hexdec(substr(ltrim($hex,'#'), 0, 2));
    $g    = hexdec(substr(ltrim($hex,'#'), 2, 2));
    $b    = hexdec(substr(ltrim($hex,'#'), 4, 2));
    $dark = sprintf('#%02x%02x%02x', max(0,$r-40), max(0,$g-40), max(0,$b-40));
    $svg  = <<<SVG
<svg width="400" height="400" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="bg{$id}" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0%" stop-color="{$hex}"/>
      <stop offset="100%" stop-color="{$dark}"/>
    </linearGradient>
  </defs>
  <rect width="400" height="400" fill="url(#bg{$id})"/>
  <circle cx="400" cy="0" r="190" fill="rgba(255,255,255,0.07)"/>
  <circle cx="0" cy="400" r="140" fill="rgba(255,255,255,0.04)"/>
  <g fill="white" opacity="0.92">
    <rect x="192" y="112" width="16" height="154"/>
    <rect x="238" y="94"  width="16" height="154"/>
    <rect x="192" y="112" width="62" height="18"/>
    <ellipse cx="168" cy="275" rx="38" ry="29"/>
    <ellipse cx="218" cy="258" rx="34" ry="26"/>
  </g>
  <ellipse cx="168" cy="279" rx="19" ry="14" fill="{$dark}"/>
  <ellipse cx="218" cy="262" rx="16" ry="12" fill="{$dark}"/>
  <text x="200" y="350" font-size="19" text-anchor="middle" fill="white"
        font-family="Arial,Helvetica,sans-serif" font-weight="bold">{$et($title)}</text>
  <text x="200" y="378" font-size="14" text-anchor="middle" fill="#aaa"
        font-family="Arial,Helvetica,sans-serif">{$et($artist)}</text>
</svg>
SVG;
    $path = THUMB_DIR . $id . '.svg';
    file_put_contents($path, $svg);
    return $path;
}

// ============================================================
//  SECURITY HELPERS
// ============================================================

function isAdminLoggedIn(): bool {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return !empty($_SESSION['rhema_admin']);
}

// ============================================================
//  ROLES & CREATOR PERMISSIONS
// ============================================================

// Returns the logged-in user's row if they are an active creator, else null.
function currentCreator(): ?array {
    if (!isUserLoggedIn()) return null;
    $u = getUserById(currentUserId());
    if (!$u || ($u['role'] ?? 'listener') !== 'creator') return null;
    return $u;
}

function isCreatorLoggedIn(): bool {
    $u = currentCreator();
    return $u !== null && ($u['status'] ?? 'active') === 'active';
}

// Aborts with a JSON error unless the current session is an active creator.
// Returns the creator's user row on success.
function requireCreator(): array {
    if (!isUserLoggedIn()) jsonResponse(['error' => 'You must be logged in'], 401);
    $u = getUserById(currentUserId());
    if (!$u || ($u['role'] ?? 'listener') !== 'creator')
        jsonResponse(['error' => 'Creator access required'], 403);
    if (($u['status'] ?? 'active') !== 'active')
        jsonResponse(['error' => 'Your creator account has been suspended'], 403);
    return $u;
}

// ---- Admin: user / creator account management ----
function getAllUsers(): array {
    return db()->query(
        "SELECT id, name, email, avatar, role, status, creator_name, bio, created_at FROM users ORDER BY id ASC"
    )->fetchAll() ?: [];
}

function setUserRole(int $userId, string $role): bool {
    if (!in_array($role, VALID_ROLES, true)) return false;
    db()->prepare('UPDATE users SET role = ? WHERE id = ?')->execute([$role, $userId]);
    return true;
}

function setUserStatus(int $userId, string $status): bool {
    if (!in_array($status, VALID_USER_STATUSES, true)) return false;
    db()->prepare('UPDATE users SET status = ? WHERE id = ?')->execute([$status, $userId]);
    return true;
}

// ---- Admin: content moderation ----
const MODERATABLE_TABLES = ['tracks' => true, 'videos' => true, 'live_streams' => true];

function setContentStatus(string $table, int $id, string $status): bool {
    if (!isset(MODERATABLE_TABLES[$table])) return false;
    if (!in_array($status, CONTENT_STATUSES, true)) return false;
    db()->prepare("UPDATE `$table` SET status = ? WHERE id = ?")->execute([$status, $id]);
    return true;
}

// Permanently terminates an account: their tracks/videos/live streams are
// hidden from the public site (status -> rejected) and detached from the
// account (creator_id -> NULL) rather than deleted, so the admin can still
// review/restore individual items afterwards if needed.
function deleteUser(int $userId): bool {
    if (!getUserById($userId)) return false;

    foreach (array_keys(MODERATABLE_TABLES) as $table) {
        db()->prepare("UPDATE `$table` SET creator_id = NULL, status = 'rejected' WHERE creator_id = ?")
            ->execute([$userId]);
    }

    deleteAvatar($userId);
    db()->prepare('DELETE FROM users WHERE id = ?')->execute([$userId]);
    return true;
}

// ============================================================
//  USER ACCOUNTS (public sign-up / sign-in)
// ============================================================

function isUserLoggedIn(): bool {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return !empty($_SESSION['rhema_user_id']);
}

function currentUserId(): ?int {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return !empty($_SESSION['rhema_user_id']) ? (int)$_SESSION['rhema_user_id'] : null;
}

function logInUser(int $userId): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    session_regenerate_id(true);
    $_SESSION['rhema_user_id'] = $userId;
}

function logOutUser(): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    unset($_SESSION['rhema_user_id']);
}

function decorateUser(array $u): array {
    $avatar = '';
    if (!empty($u['avatar'])) {
        $path   = AVATAR_DIR . $u['avatar'];
        $v      = file_exists($path) ? filemtime($path) : time();
        $avatar = 'api.php?action=avatar&id=' . $u['id'] . '&v=' . $v;
    }
    return [
        'id'           => (int)$u['id'],
        'name'         => $u['name'],
        'email'        => $u['email'],
        'avatar'       => $avatar,
        'role'         => $u['role'] ?? 'listener',
        'status'       => $u['status'] ?? 'active',
        'creator_name' => $u['creator_name'] ?? '',
        'bio'          => $u['bio'] ?? '',
    ];
}

function getUserById(int $id): ?array {
    $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$id]);
    $u = $stmt->fetch();
    return $u ?: null;
}

function getUserByEmail(string $email): ?array {
    $stmt = db()->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $u = $stmt->fetch();
    return $u ?: null;
}

function getUserByGoogleId(string $googleId): ?array {
    $stmt = db()->prepare('SELECT * FROM users WHERE google_id = ?');
    $stmt->execute([$googleId]);
    $u = $stmt->fetch();
    return $u ?: null;
}

function createUser(string $name, string $email, ?string $passwordHash = null, ?string $googleId = null, string $avatar = ''): int {
    // Every new sign-up gets a creator account by default (like a YouTube
    // channel) — the admin can revoke creator access or suspend/terminate
    // the account from the Creators tab if needed.
    $stmt = db()->prepare('INSERT INTO users (name, email, password_hash, google_id, avatar, role) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([$name, $email, $passwordHash, $googleId, $avatar, 'creator']);
    return (int)db()->lastInsertId();
}

function updateUser(int $id, array $fields): void {
    if (!$fields) return;
    $sets = [];
    $vals = [];
    foreach ($fields as $col => $val) {
        $sets[] = "`$col` = ?";
        $vals[] = $val;
    }
    $vals[] = $id;
    db()->prepare('UPDATE users SET ' . implode(', ', $sets) . ' WHERE id = ?')->execute($vals);
}

// ---- Verify a Google Identity Services ID token via Google's tokeninfo endpoint ----
function verifyGoogleIdToken(string $idToken): ?array {
    $url = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($idToken);
    $ch  = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $httpCode !== 200) return null;

    $claims = json_decode($response, true);
    if (!is_array($claims) || empty($claims['aud']) || $claims['aud'] !== GOOGLE_CLIENT_ID) return null;
    if (empty($claims['email']) || empty($claims['sub'])) return null;

    return $claims;
}

// ---- User avatar image storage ----
const AVATAR_EXTS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

function getAvatarPath(int $userId): string {
    foreach (AVATAR_EXTS as $ext) {
        $p = AVATAR_DIR . $userId . '.' . $ext;
        if (file_exists($p)) return $p;
    }
    return '';
}

function deleteAvatar(int $userId): void {
    // Clears the primary plus every generated variant (@md / @sm).
    imgDeleteVariants(AVATAR_DIR, (string)$userId);
}

// Avatars are always rendered in a circle with object-fit:cover, so the
// upload is centre-cropped square and stored at 320/128/64px. A 5 MB
// selfie becomes a ~2 KB file in the 32px nav slot.
function saveAvatar(int $userId, array $file): bool {
    if ($file['error'] !== UPLOAD_ERR_OK) return false;
    if ($file['size'] > MAX_THUMB_MB * 1024 * 1024) return false;

    // Validate against the real file header, not the submitted name.
    $mime = mime_content_type($file['tmp_name']);
    if (!in_array($mime, ALLOWED_IMAGE_MIME, true)) return false;
    if (!imgProbe($file['tmp_name'])) return false;

    $res = imgCompressTo($file['tmp_name'], AVATAR_DIR, (string)$userId, 'avatar', true);
    if (!$res['ok']) return false;
    @unlink($file['tmp_name']);

    // Store the basename so decorateUser() can cache-bust on mtime.
    updateUser($userId, ['avatar' => basename($res['primary'])]);
    return true;
}

// Resolves the stored avatar at the requested variant, building it on
// demand for images that predate the compression pipeline.
function getAvatarVariant(int $userId, string $suffix): string {
    return imgEnsureVariant(AVATAR_DIR, (string)$userId, $suffix, 'avatar', true);
}

// ---- Liked tracks ----
function getUserLikedTrackIds(int $userId): array {
    $stmt = db()->prepare('SELECT track_id FROM user_likes WHERE user_id = ? ORDER BY created_at DESC');
    $stmt->execute([$userId]);
    return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
}

function likeTrack(int $userId, int $trackId): void {
    db()->prepare('INSERT IGNORE INTO user_likes (user_id, track_id) VALUES (?, ?)')->execute([$userId, $trackId]);
}

function unlikeTrack(int $userId, int $trackId): void {
    db()->prepare('DELETE FROM user_likes WHERE user_id = ? AND track_id = ?')->execute([$userId, $trackId]);
}

// ---- User playlists ----
function getUserPlaylists(int $userId): array {
    $stmt = db()->prepare('SELECT * FROM user_playlists WHERE user_id = ? ORDER BY created_at ASC');
    $stmt->execute([$userId]);
    $playlists = $stmt->fetchAll();

    $trackStmt = db()->prepare('SELECT track_id FROM user_playlist_tracks WHERE playlist_id = ? ORDER BY sort_order ASC');
    foreach ($playlists as &$pl) {
        $trackStmt->execute([$pl['id']]);
        $pl['id']     = (int)$pl['id'];
        $pl['tracks'] = array_map('intval', $trackStmt->fetchAll(PDO::FETCH_COLUMN));
    }
    return $playlists;
}

function createUserPlaylist(int $userId, string $name, string $description = '', string $emoji = '🎵'): int {
    $stmt = db()->prepare('INSERT INTO user_playlists (user_id, name, description, emoji) VALUES (?, ?, ?, ?)');
    $stmt->execute([$userId, $name, $description, $emoji]);
    return (int)db()->lastInsertId();
}

function deleteUserPlaylist(int $userId, int $playlistId): bool {
    $stmt = db()->prepare('DELETE FROM user_playlists WHERE id = ? AND user_id = ?');
    $stmt->execute([$playlistId, $userId]);
    if ($stmt->rowCount() > 0) {
        db()->prepare('DELETE FROM user_playlist_tracks WHERE playlist_id = ?')->execute([$playlistId]);
        return true;
    }
    return false;
}

function userOwnsPlaylist(int $userId, int $playlistId): bool {
    $stmt = db()->prepare('SELECT 1 FROM user_playlists WHERE id = ? AND user_id = ?');
    $stmt->execute([$playlistId, $userId]);
    return (bool)$stmt->fetchColumn();
}

function addTrackToUserPlaylist(int $playlistId, int $trackId): void {
    $stmt = db()->prepare('SELECT COALESCE(MAX(sort_order), -1) + 1 FROM user_playlist_tracks WHERE playlist_id = ?');
    $stmt->execute([$playlistId]);
    $nextOrder = (int)$stmt->fetchColumn();
    db()->prepare('INSERT IGNORE INTO user_playlist_tracks (playlist_id, track_id, sort_order) VALUES (?, ?, ?)')
        ->execute([$playlistId, $trackId, $nextOrder]);
}

function removeTrackFromUserPlaylist(int $playlistId, int $trackId): void {
    db()->prepare('DELETE FROM user_playlist_tracks WHERE playlist_id = ? AND track_id = ?')->execute([$playlistId, $trackId]);
}

function jsonResponse($data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data);
    exit;
}