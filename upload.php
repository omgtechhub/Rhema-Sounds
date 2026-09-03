<?php
// ============================================================
//  RHEMA SOUNDS – LEGACY ADMIN URL REDIRECT (upload.php)
//  The admin panel now lives at admin.php. This stub keeps old
//  links/bookmarks to upload.php working by redirecting there.
// ============================================================
$qs = $_SERVER['QUERY_STRING'] ?? '';
header('Location: admin.php' . ($qs !== '' ? '?' . $qs : ''));
exit;
