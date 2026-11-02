-- Product Catalog database
-- Import this file in phpMyAdmin (Import tab) or run it in the SQL tab.

CREATE DATABASE IF NOT EXISTS catalog_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE catalog_db;

CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Default admin: username = admin, password = admin123  (CHANGE THIS after first login)
INSERT INTO admins (username, password_hash) VALUES
('admin', '$2y$10$xwPBm6bPjhb8c26NRpKBeOUfUknr/Ll3dElYQSlyqqy3lS3R0No.i');

INSERT INTO categories (name) VALUES
('Electronics'), ('Fashion'), ('Home & Kitchen'), ('Books');

INSERT INTO products (category_id, name, description, price) VALUES
(1, 'Wireless Headphones', 'Over-ear Bluetooth headphones with noise isolation and 20 hours of battery life.', 1999.00),
(1, 'Smart Watch', 'Fitness tracker with heart-rate monitor, step counter and sleep tracking.', 2499.00),
(1, 'Power Bank 10000mAh', 'Slim power bank with fast charging and two USB ports.', 899.00),
(2, 'Cotton T-Shirt', 'Soft, breathable round-neck cotton t-shirt. Available in several colours.', 399.00),
(2, 'Canvas Sneakers', 'Lightweight everyday sneakers with a cushioned sole.', 1299.00),
(3, 'Non-stick Frying Pan', '26 cm non-stick pan, suitable for gas and induction cooktops.', 749.00),
(3, 'Stainless Steel Bottle', 'Insulated 750 ml bottle that keeps drinks hot or cold for 12 hours.', 549.00),
(4, 'PHP and MySQL for Beginners', 'A practical guide to building dynamic websites with PHP and MySQL.', 450.00),
(4, 'Learn SQL in 30 Days', 'Step-by-step exercises covering queries, joins and database design.', 350.00);
