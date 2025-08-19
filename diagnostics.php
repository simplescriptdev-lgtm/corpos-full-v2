<?php
// diagnostics.php — temporary helper (DELETE AFTER USE)
error_reporting(E_ALL);
ini_set('display_errors','1');
header('Content-Type: text/plain; charset=utf-8');

require __DIR__ . '/db.php';

echo "== CorpOS diagnostics ==\n";

// 1) Check DB connection
try {
  $pdo = db();
  echo "DB: OK\n";
} catch (Throwable $e) {
  echo "DB ERROR: " . $e->getMessage() . "\n";
  exit(1);
}

// 2) Show users table info
try {
  $row = $pdo->query('SELECT COUNT(*) AS c FROM users')->fetch();
  echo "users.count = " . ($row ? $row['c'] : 'n/a') . "\n";
  $users = $pdo->query('SELECT id,name,email,role FROM users ORDER BY id LIMIT 10')->fetchAll();
  foreach($users as $u){
    echo sprintf("- user #%d: %s <%s> role=%s\n", $u['id'], $u['name'], $u['email'], $u['role']);
  }
} catch (Throwable $e) {
  echo "USERS ERROR: " . $e->getMessage() . "\n";
}

// 3) Check PHP sessions
session_start();
$_SESSION['__diag'] = ($_SESSION['__diag'] ?? 0) + 1;
echo "session.status = " . session_status() . " (count=" . $_SESSION['__diag'] . ")\n";
?>