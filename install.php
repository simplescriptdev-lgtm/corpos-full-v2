<?php
require __DIR__ . '/db.php';
try{
  db()->exec("CREATE TABLE IF NOT EXISTS users (id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(120) NOT NULL,email VARCHAR(190) NOT NULL UNIQUE,password_hash VARCHAR(255) NOT NULL,role VARCHAR(50) NOT NULL DEFAULT 'manager',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
  db()->exec("CREATE TABLE IF NOT EXISTS owner_incomes (id INT AUTO_INCREMENT PRIMARY KEY,amount DECIMAL(12,2) NOT NULL,comment VARCHAR(255) NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
  db()->exec("CREATE TABLE IF NOT EXISTS owner_capital_withdrawals (id INT AUTO_INCREMENT PRIMARY KEY,amount DECIMAL(12,2) NOT NULL,comment VARCHAR(255) NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
  $row=db()->query('SELECT COUNT(*) c FROM users')->fetch(); if((int)$row['c']===0){ $st=db()->prepare('INSERT INTO users (name,email,password_hash,role) VALUES (?,?,?,?)'); $st->execute(['Керівник','admin@corp.local',password_hash('admin12345',PASSWORD_DEFAULT),'manager']); }
  echo "<meta charset='utf-8'><h3>OK. Таблиці створені. Логін: admin@corp.local / admin12345</h3><a href='/corpos-full-v2/index.php'>На головну</a>";
}catch(Throwable $e){ http_response_code(500); echo '<pre>'.htmlspecialchars($e->getMessage()).'</pre>'; }
