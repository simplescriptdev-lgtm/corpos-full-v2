-- init.sql for DB 'corpos'
CREATE TABLE IF NOT EXISTS users (id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(120) NOT NULL,email VARCHAR(190) NOT NULL UNIQUE,password_hash VARCHAR(255) NOT NULL,role VARCHAR(50) NOT NULL DEFAULT 'manager',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS owner_incomes (id INT AUTO_INCREMENT PRIMARY KEY,amount DECIMAL(12,2) NOT NULL,comment VARCHAR(255) NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS owner_capital_withdrawals (id INT AUTO_INCREMENT PRIMARY KEY,amount DECIMAL(12,2) NOT NULL,comment VARCHAR(255) NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO users (name,email,password_hash,role) VALUES ('Керівник','admin@corp.local','$2y$10$abcdefghijklmnopqrstuv.Cy3U2G9b9J2M3O4P5Q6R7S8T9U0','manager') ON DUPLICATE KEY UPDATE email=email;
INSERT INTO owner_incomes (amount,comment,created_at) VALUES (1000.00,'Тест надходження',NOW());
INSERT INTO owner_capital_withdrawals (amount,comment,created_at) VALUES (200.00,'12345',NOW());
