CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(80) NOT NULL UNIQUE,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role VARCHAR(20) NOT NULL DEFAULT 'student',
  wallet_balance DECIMAL(12,2) NOT NULL DEFAULT 100.00,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(140) NOT NULL,
  description TEXT NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  category VARCHAR(60) NOT NULL,
  stock INT NOT NULL DEFAULT 20
);
CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  total DECIMAL(10,2) NOT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'Placed',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id)
);
CREATE TABLE IF NOT EXISTS order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  product_id INT NOT NULL,
  quantity INT NOT NULL,
  unit_price DECIMAL(10,2) NOT NULL,
  CONSTRAINT fk_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  CONSTRAINT fk_items_product FOREIGN KEY (product_id) REFERENCES products(id)
);
CREATE TABLE IF NOT EXISTS flags (
  id INT AUTO_INCREMENT PRIMARY KEY,
  flag_key VARCHAR(80) NOT NULL UNIQUE,
  flag_value VARCHAR(160) NOT NULL UNIQUE,
  title VARCHAR(160) NOT NULL,
  points INT NOT NULL DEFAULT 10
);
CREATE TABLE IF NOT EXISTS submissions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  flag_key VARCHAR(80) NOT NULL,
  submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY one_flag_per_user (user_id, flag_key),
  CONSTRAINT fk_sub_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_sub_flag FOREIGN KEY (flag_key) REFERENCES flags(flag_key)
);
INSERT INTO products (name, description, price, category, stock) VALUES
('SecureKey USB', 'Hardware security key for account protection (fictional training product).', 1299.00, 'Security', 20),
('Privacy Webcam Cover', 'Simple privacy accessory for workstations.', 199.00, 'Accessories', 35),
('Network Lab Kit', 'Fictional beginner networking practice kit.', 2499.00, 'Learning', 12),
('Cybersecurity Field Notebook', 'Notebook for recording authorized test observations.', 299.00, 'Learning', 50),
('Encrypted Storage Drive', 'Fictional encrypted storage product for the shop demo.', 1899.00, 'Storage', 18),
('ICSS Academy Hoodie', 'Training merchandise for the fictional ICSS store.', 899.00, 'Merchandise', 25)
ON DUPLICATE KEY UPDATE name=VALUES(name);
INSERT INTO flags (flag_key, flag_value, title, points) VALUES
('access', 'ICSS{a01_access_control_found}', 'A01:2021 Broken Access Control', 10),
('crypto', 'ICSS{a02_crypto_exposure_found}', 'A02:2021 Cryptographic Failures', 10),
('injection', 'ICSS{a03_injection_found}', 'A03:2021 Injection', 10),
('design', 'ICSS{a04_insecure_design_found}', 'A04:2021 Insecure Design', 10),
('misconfig', 'ICSS{a05_misconfiguration_found}', 'A05:2021 Security Misconfiguration', 10),
('outdated', 'ICSS{a06_outdated_component_found}', 'A06:2021 Vulnerable and Outdated Components', 10),
('auth', 'ICSS{a07_authentication_failure_found}', 'A07:2021 Identification and Authentication Failures', 10),
('integrity', 'ICSS{a08_integrity_failure_found}', 'A08:2021 Software and Data Integrity Failures', 10),
('logging', 'ICSS{a09_logging_failure_found}', 'A09:2021 Security Logging and Monitoring Failures', 10),
('ssrf', 'ICSS{a10_ssrf_found}', 'A10:2021 Server-Side Request Forgery', 10)
ON DUPLICATE KEY UPDATE title=VALUES(title), points=VALUES(points);
