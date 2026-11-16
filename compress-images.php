<?php
// ============================================================
//  RHEMA SOUNDS – BACKFILL EXISTING IMAGES
// ------------------------------------------------------------
//  New uploads are compressed on arrival (imagecompress.php). This
//  one-off pass does the same to everything already on disk from
//  before the pipeline existed: avatars, custom covers and the
//  auto-generated colour art all get downscaled, re-encoded and
//  given their @md / @sm variants.
//
//  Run from the command line:
//      php compress-images.php --dry-run     # report only
//      php compress-images.php               # apply
//
//  Or open it in a browser while logged in as admin.
//  Safe to re-run: images already at target size are skipped.
// ============================================================

require_once __DIR__ . '/config.php';

$isCli = (PHP_SAPI === 'cli');

if (!$isCli) {
    if (!isAdminLoggedIn()) {
        http_response_code(403);
        exit('Admin login required. Log in via admin.php first.');
    }
    header('Content-Type: text/plain; charset=utf-8');
}

$dryRun = $isCli
    ? in_array('--dry-run', $argv ?? [], true)
    : isset($_GET['dry-run']);

@set_time_limit(0);

echo "Rhema Sounds - image backfill" . ($dryRun ? "  [DRY RUN - nothing written]" : "") . "\n";
echo str_repeat('=', 64) . "\n";

if (!imgGdAvailable()) {
    exit("GD extension is not available - cannot compress. Nothing done.\n");
}
echo "Encoder: " . (imgWebpAvailable() ? "WebP" : "JPEG/PNG (no WebP in this GD build)") . "\n\n";

$totalBefore = 0;
$totalAfter  = 0;
$converted   = 0;
$skipped     = 0;
$failed      = 0;

/**
 * Decides whether a stored primary still needs work: either it is
 * larger than its preset allows, or one of its variants is missing.
 */
function needsWork(string $dir, string $base, string $preset): bool {
    $primary = imgFindVariant($dir, $base, '');
    if ($primary === '') return false;

    $sizes = IMG_PRESETS[$preset];
    $probe = imgProbe($primary);
    if (!$probe) return false;

    // Oversized for the slot it serves.
    if (max($probe['width'], $probe['height']) > $sizes[''] + 1) return true;

    // Missing a variant.
    foreach ($sizes as $suffix => $_) {
        if ($suffix === '') continue;
        if (imgFindVariant($dir, $base, $suffix) === '') return true;
    }
    return false;
}

/**
 * Recompresses one stored image in place through a temp copy, since
 * imgCompressTo() clears the destination before writing.
 */
function recompress(string $dir, string $base, string $preset, bool $dryRun, array &$stat): ?array {
    $primary = imgFindVariant($dir, $base, '');
    if ($primary === '') return null;

    $before = 0;
    foreach (IMG_PRESETS[$preset] as $suffix => $_) {
        $p = imgFindVariant($dir, $base, $suffix);
        if ($p !== '') $before += (int)filesize($p);
    }

    if ($dryRun) {
        $probe = imgProbe($primary);
        return [
            'ok' => true, 'dry' => true,
            'before' => $before, 'after' => 0,
            'primary' => $primary,
            'dims' => $probe ? ($probe['width'] . 'x' . $probe['height']) : '?',
        ];
    }

    // Work from a copy so the source survives the destination wipe.
    $tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'rhema_' . $base . '_' . uniqid() . '.' . pathinfo($primary, PATHINFO_EXTENSION);
    if (!@copy($primary, $tmp)) return ['ok' => false, 'error' => 'could not stage a temp copy'];

    $res = imgCompressTo($tmp, $dir, $base, $preset, true);
    @unlink($tmp);

    if (!$res['ok']) return ['ok' => false, 'error' => $res['error'] ?: 'compression failed'];
    return ['ok' => true, 'dry' => false, 'before' => $before, 'after' => $res['bytes_out'], 'primary' => $res['primary']];
}

// ============================================================
//  1. USER AVATARS
// ============================================================
echo "-- Avatars (uploads/avatars/) " . str_repeat('-', 34) . "\n";

$avatarBases = [];
foreach (glob(AVATAR_DIR . '*') ?: [] as $f) {
    if (!is_file($f)) continue;
    $name = pathinfo($f, PATHINFO_FILENAME);
    if (strpos($name, '@') !== false) continue;          // a variant, not a primary
    if (!ctype_digit($name)) continue;                   // avatars are named by user id
    $avatarBases[$name] = true;
}

foreach (array_keys($avatarBases) as $base) {
    if (!needsWork(AVATAR_DIR, $base, 'avatar')) {
        $skipped++;
        continue;
    }
    $stat = [];
    $r = recompress(AVATAR_DIR, $base, 'avatar', $dryRun, $stat);
    if (!$r) { $skipped++; continue; }

    if (!$r['ok']) {
        $failed++;
        printf("  user %-6s FAILED: %s\n", $base, $r['error']);
        continue;
    }

    $totalBefore += $r['before'];
    $totalAfter  += $r['after'];
    $converted++;

    if ($r['dry']) {
        printf("  user %-6s %-12s %9s -> would compress\n", $base, $r['dims'], round($r['before'] / 1024) . ' KB');
    } else {
        // The stored filename may have changed extension (jpg -> webp).
        updateUser((int)$base, ['avatar' => basename($r['primary'])]);
        printf("  user %-6s %9s -> %9s  (%s)\n", $base,
            round($r['before'] / 1024) . ' KB', round($r['after'] / 1024) . ' KB', basename($r['primary']));
    }
}
echo "\n";

// ============================================================
//  2. TRACK COVER ART (uploaded + auto-generated)
// ============================================================
echo "-- Thumbnails (uploads/thumbs/) " . str_repeat('-', 31) . "\n";

$thumbBases = [];
foreach (glob(THUMB_DIR . '*') ?: [] as $f) {
    if (!is_file($f)) continue;
    $name = pathinfo($f, PATHINFO_FILENAME);
    if (strpos($name, '@') !== false) continue;          // a variant
    $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
    if ($ext === 'svg') continue;                        // GD cannot resize SVG
    // "<id>_custom" (uploaded) and "<id>" (generated colour art).
    if (!preg_match('/^\d+(_custom)?$/', $name)) continue;
    $thumbBases[$name] = true;
}

foreach (array_keys($thumbBases) as $base) {
    if (!needsWork(THUMB_DIR, $base, 'thumb')) {
        $skipped++;
        continue;
    }
    $stat = [];
    $r = recompress(THUMB_DIR, $base, 'thumb', $dryRun, $stat);
    if (!$r) { $skipped++; continue; }

    if (!$r['ok']) {
        $failed++;
        printf("  %-14s FAILED: %s\n", $base, $r['error']);
        continue;
    }

    $totalBefore += $r['before'];
    $totalAfter  += $r['after'];
    $converted++;

    if ($r['dry']) {
        printf("  %-14s %-12s %9s -> would compress\n", $base, $r['dims'], round($r['before'] / 1024) . ' KB');
    } else {
        printf("  %-14s %9s -> %9s  (%s)\n", $base,
            round($r['before'] / 1024) . ' KB', round($r['after'] / 1024) . ' KB', basename($r['primary']));
    }
}

// ============================================================
//  SUMMARY
// ============================================================
echo "\n" . str_repeat('=', 64) . "\n";
printf("processed %d   already optimal %d   failed %d\n", $converted, $skipped, $failed);

if (!$dryRun && $totalBefore > 0) {
    printf("disk: %s -> %s  (%.1f%% smaller)\n",
        number_format($totalBefore / 1024, 1) . ' KB',
        number_format($totalAfter / 1024, 1) . ' KB',
        100 - ($totalAfter / max(1, $totalBefore) * 100));
} elseif ($dryRun) {
    printf("would reprocess %s of images. Re-run without --dry-run to apply.\n",
        number_format($totalBefore / 1024, 1) . ' KB');
}
echo "Done.\n";
