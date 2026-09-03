<?php
// ============================================================
//  RHEMA SOUNDS – ONE-TIME SCHEMA MIGRATION (migrate.php)
//  Adds the columns needed for creator accounts / role-based
//  permissions / content moderation. Safe to run multiple
//  times — every change is checked against information_schema
//  before being applied.
// ============================================================
require_once __DIR__ . '/config.php';

session_start();
if (empty($_SESSION['rhema_admin'])) {
    header('Location: admin.php');
    exit;
}

function columnExists(string $table, string $column): bool {
    $st = db()->prepare(
        'SELECT 1 FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
    );
    $st->execute([$table, $column]);
    return (bool)$st->fetchColumn();
}

function indexExists(string $table, string $index): bool {
    $st = db()->prepare(
        'SELECT 1 FROM information_schema.STATISTICS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?'
    );
    $st->execute([$table, $index]);
    return (bool)$st->fetchColumn();
}

$columns = [
    ['users',        'role',         "VARCHAR(20)  NOT NULL DEFAULT 'listener'"],
    ['users',        'status',       "VARCHAR(20)  NOT NULL DEFAULT 'active'"],
    ['users',        'creator_name', "VARCHAR(255) NOT NULL DEFAULT ''"],
    ['users',        'bio',          "TEXT NULL"],
    ['tracks',       'creator_id',   "INT UNSIGNED NULL"],
    ['tracks',       'status',       "VARCHAR(20)  NOT NULL DEFAULT 'approved'"],
    ['videos',       'creator_id',   "INT UNSIGNED NULL"],
    ['videos',       'status',       "VARCHAR(20)  NOT NULL DEFAULT 'approved'"],
    ['live_streams', 'creator_id',   "INT UNSIGNED NULL"],
    ['live_streams', 'status',       "VARCHAR(20)  NOT NULL DEFAULT 'approved'"],
];

$indexes = [
    ['tracks',       'idx_creator', 'creator_id'],
    ['videos',       'idx_creator', 'creator_id'],
    ['live_streams', 'idx_creator', 'creator_id'],
];

$log = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($columns as [$table, $column, $def]) {
        if (columnExists($table, $column)) {
            $log[] = "✓ `$table`.`$column` already exists — skipped";
            continue;
        }
        try {
            db()->exec("ALTER TABLE `$table` ADD COLUMN `$column` $def");
            $log[] = "✅ Added `$table`.`$column` ($def)";
        } catch (Throwable $e) {
            $log[] = "❌ Failed adding `$table`.`$column`: " . $e->getMessage();
        }
    }
    foreach ($indexes as [$table, $index, $col]) {
        if (indexExists($table, $index)) {
            $log[] = "✓ Index `$index` on `$table` already exists — skipped";
            continue;
        }
        try {
            db()->exec("ALTER TABLE `$table` ADD INDEX `$index` (`$col`)");
            $log[] = "✅ Added index `$index` on `$table`($col)";
        } catch (Throwable $e) {
            $log[] = "❌ Failed adding index `$index` on `$table`: " . $e->getMessage();
        }
    }
}

// Current status (for display)
$status = [];
foreach ($columns as [$table, $column, $def]) {
    $status[] = [$table, $column, columnExists($table, $column)];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Rhema Sounds – Schema Migration</title>
<style>
:root{--green:#1db954;--bg:#0a0a0a;--card:#1a1a1a;--border:#333;--text:#fff;--muted:#aaa;--red:#e8312a;}
*{box-sizing:border-box;margin:0;padding:0;}
body{background:var(--bg);color:var(--text);font-family:'Segoe UI',Arial,sans-serif;padding:40px 20px;}
.wrap{max-width:680px;margin:0 auto;}
h1{font-size:1.4rem;font-weight:900;margin-bottom:6px;}
p.sub{color:var(--muted);font-size:.88rem;margin-bottom:24px;}
.card{background:var(--card);border-radius:12px;padding:24px;margin-bottom:20px;border:1px solid var(--border);}
table{width:100%;border-collapse:collapse;font-size:.85rem;}
th,td{text-align:left;padding:8px 10px;border-bottom:1px solid #1e1e1e;}
th{color:var(--muted);text-transform:uppercase;font-size:.72rem;letter-spacing:.06em;}
.ok{color:var(--green);font-weight:700;}
.missing{color:var(--red);font-weight:700;}
.btn{display:inline-block;padding:11px 24px;border-radius:24px;font-weight:700;font-size:.88rem;
     cursor:pointer;border:none;background:var(--green);color:#000;}
.log{background:#111;border-radius:8px;padding:14px 18px;font-family:monospace;font-size:.82rem;
     line-height:1.8;margin-top:16px;white-space:pre-wrap;}
a{color:var(--green);}
</style>
</head>
<body>
<div class="wrap">
  <h1>Rhema Sounds – Schema Migration</h1>
  <p class="sub">Adds creator-account / role-based-permission columns to the database. Safe to run more than once.</p>

  <div class="card">
    <table>
      <tr><th>Table</th><th>Column</th><th>Status</th></tr>
      <?php foreach ($status as [$table, $column, $exists]): ?>
      <tr>
        <td><?= htmlspecialchars($table) ?></td>
        <td><?= htmlspecialchars($column) ?></td>
        <td><?= $exists ? '<span class="ok">present</span>' : '<span class="missing">missing</span>' ?></td>
      </tr>
      <?php endforeach; ?>
    </table>

    <form method="POST" style="margin-top:20px;">
      <button type="submit" class="btn"><i class="fas fa-database"></i> Run Migration</button>
    </form>

    <?php if ($log): ?>
    <div class="log"><?= htmlspecialchars(implode("\n", $log)) ?></div>
    <?php endif; ?>
  </div>

  <p><a href="admin.php">&larr; Back to Admin</a></p>
</div>
</body>
</html>
