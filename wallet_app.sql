
-- SQL Dump for wallet_app
CREATE DATABASE IF NOT EXISTS wallet_app CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE wallet_app;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    otp VARCHAR(10),
    otp_expiry DATETIME,
    status ENUM('pending','active') DEFAULT 'pending',
    wallet_balance DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
