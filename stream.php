<?php
// ============================================================
//  RHEMA SOUNDS – AUDIO STREAMING  (stream.php)
//  Supports HTTP Range requests so scrubbing works in browsers
// ============================================================

require_once __DIR__ . '/config.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { http_response_code(400); echo 'Missing id'; exit; }

$track = getTrackById($id);
if (!$track) { http_response_code(404); echo 'Track not found'; exit; }

$file = AUDIO_DIR . ($track['filename'] ?? '');
if (!file_exists($file)) { http_response_code(404); echo 'Audio file not found'; exit; }

// Determine MIME type
$ext  = strtolower(pathinfo($file, PATHINFO_EXTENSION));
$mimeMap = [
    'mp3'  => 'audio/mpeg',
    'ogg'  => 'audio/ogg',
    'wav'  => 'audio/wav',
    'flac' => 'audio/flac',
    'aac'  => 'audio/aac',
    'm4a'  => 'audio/mp4',
    'mp4'  => 'audio/mp4',
];
$mime = $mimeMap[$ext] ?? 'audio/mpeg';

$size = filesize($file);

// ---- Handle Range requests ----
$start = 0;
$end   = $size - 1;

if (isset($_SERVER['HTTP_RANGE'])) {
    preg_match('/bytes=(\d+)-(\d*)/', $_SERVER['HTTP_RANGE'], $m);
    $start = (int)$m[1];
    $end   = !empty($m[2]) ? (int)$m[2] : $size - 1;
    $end   = min($end, $size - 1);

    if ($start > $end || $start >= $size) {
        header('HTTP/1.1 416 Range Not Satisfiable');
        header('Content-Range: bytes */' . $size);
        exit;
    }

    $length = $end - $start + 1;
    header('HTTP/1.1 206 Partial Content');
    header('Content-Range: bytes ' . $start . '-' . $end . '/' . $size);
} else {
    $length = $size;
    header('HTTP/1.1 200 OK');
}

header('Content-Type: ' . $mime);
header('Content-Length: ' . $length);
header('Accept-Ranges: bytes');
header('Content-Disposition: inline; filename="' . basename($file) . '"');
header('Cache-Control: public, max-age=3600');
header('X-Content-Type-Options: nosniff');

// Stream the file chunk
$fp = fopen($file, 'rb');
fseek($fp, $start);

$bufSize  = 8192;
$remaining = $length;

while (!feof($fp) && $remaining > 0) {
    $read  = min($bufSize, $remaining);
    $chunk = fread($fp, $read);
    if ($chunk === false) break;
    echo $chunk;
    flush();
    $remaining -= strlen($chunk);
}

fclose($fp);
exit;
