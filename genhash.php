<?php
// ============================================================
//  RHEMA SOUNDS – ADMIN PASSWORD HASH GENERATOR
//
//  Use this once to create the hash for your admin password,
//  paste the result into config.php as ADMIN_PASSWORD_HASH,
//  then DELETE this file from the server.
// ============================================================

$hash = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password']) && $_POST['password'] !== '') {
    $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Generate Admin Password Hash</title>
  <style>
    body { font-family: -apple-system, Segoe UI, Roboto, sans-serif; background: #121212; color: #fff; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
    .box { background: #181818; border-radius: 12px; padding: 32px; max-width: 480px; width: 90%; }
    h1 { font-size: 1.3rem; margin: 0 0 16px; }
    p { color: #b3b3b3; font-size: 0.9rem; line-height: 1.5; }
    input[type=password] { width: 100%; padding: 12px; border-radius: 6px; border: 1px solid #333; background: #1f1f1f; color: #fff; font-size: 0.95rem; box-sizing: border-box; }
    button { margin-top: 12px; width: 100%; padding: 12px; border: none; border-radius: 6px; background: #1db954; color: #000; font-weight: 700; font-size: 0.95rem; cursor: pointer; }
    .result { margin-top: 20px; background: #1a1a1a; border-radius: 8px; padding: 14px; word-break: break-all; font-family: monospace; font-size: 0.85rem; color: #1db954; }
    .warn { margin-top: 16px; color: #f15e6c; font-size: 0.82rem; }
  </style>
</head>
<body>
  <div class="box">
    <h1>Generate Admin Password Hash</h1>
    <p>Type the password you want to use for the admin panel. This page will generate a secure hash — copy it into <code>config.php</code> as <code>ADMIN_PASSWORD_HASH</code>.</p>
    <form method="POST">
      <input type="password" name="password" placeholder="Enter your chosen admin password" required autofocus/>
      <button type="submit">Generate Hash</button>
    </form>
    <?php if ($hash): ?>
      <div class="result"><?= htmlspecialchars($hash) ?></div>
      <p>Paste this into <code>config.php</code>:</p>
      <div class="result">define('ADMIN_PASSWORD_HASH', '<?= htmlspecialchars($hash) ?>');</div>
    <?php endif; ?>
    <p class="warn"><i class="fas fa-exclamation-triangle"></i> Delete this file (genhash.php) from the server once you're done — leaving it online lets anyone generate hashes.</p>
  </div>
</body>
</html>
