-- Salamtak Database Schema
CREATE DATABASE IF NOT EXISTS salamtak_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE salamtak_db;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id VARCHAR(50) PRIMARY KEY,
    national_id VARCHAR(14) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(15) NOT NULL,
    address TEXT NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    user_type ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_national_id (national_id),
    INDEX idx_user_type (user_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reports Table
CREATE TABLE IF NOT EXISTS reports (
    id VARCHAR(50) PRIMARY KEY,
    uid VARCHAR(50) NOT NULL,
    national_id VARCHAR(14) NOT NULL,
    name VARCHAR(100) NOT NULL,
    type VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    image_path VARCHAR(255),
    status ENUM('pending', 'in_progress', 'resolved') DEFAULT 'pending',
    severity ENUM('Low', 'Medium', 'High', 'Critical') DEFAULT 'Medium',
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    location_address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (uid) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_uid (uid),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert Default Admin User
-- National ID: 12345678901234, Password: admin123456
INSERT INTO users (id, national_id, name, email, phone, address, password_hash, user_type)
VALUES (
    'admin_001',
    '12345678901234',
    'Admin User',
    'admin@salamtak.com',
    '01000000000',
    'Admin Address',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- admin123456
    'admin'
) ON DUPLICATE KEY UPDATE id=id;

-- Insert Test User
-- National ID: 11111111111111, Password: user123456
INSERT INTO users (id, national_id, name, email, phone, address, password_hash, user_type)
VALUES (
    'user_001',
    '11111111111111',
    'Test User',
    'user@salamtak.com',
    '01111111111',
    'Test Address',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- user123456
    'user'
) ON DUPLICATE KEY UPDATE id=id;
