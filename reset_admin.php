<?php
// reset_admin.php — temporary helper to (re)create admin user. DELETE AFTER USE.
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';

try {
  $pdo = db();
  $pdo->exec("CREATE TABLE IF NOT EXISTS users (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL, email VARCHAR(190) NOT NULL UNIQUE, password_hash VARCHAR(255) NOT NULL, role VARCHAR(50) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
  $email = 'admin@corp.local';
  $pass  = 'admin12345';
  $hash  = password_hash($pass, PASSWORD_DEFAULT);
  $s = $pdo->prepare("INSERT INTO users (name,email,password_hash,role) VALUES ('Керівник',?,?, 'manager') ON DUPLICATE KEY UPDATE name=VALUES(name), password_hash=VALUES(password_hash), role=VALUES(role)");
  $s->execute([$email, $hash]);
  echo "OK: admin user is now: $email / $pass\n";
} catch (Throwable $e) {
  http_response_code(500);
  echo "ERROR: " . $e->getMessage();
}
?>