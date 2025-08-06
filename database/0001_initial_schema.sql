-- Church Management System Database Schema

-- Create database
CREATE DATABASE IF NOT EXISTS churchapp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE churchapp;

-- Churches table
CREATE TABLE churches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    address TEXT,
    phone VARCHAR(50),
    email VARCHAR(255),
    pastor_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_pastor_id (pastor_id)
);

-- Users table (for all roles: super_admin, pastor, coach, mentor, member)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    address TEXT,
    role ENUM('super_admin', 'pastor', 'coach', 'mentor', 'member') NOT NULL DEFAULT 'member',
    church_id INT,
    status ENUM('active', 'inactive', 'pending', 'suspended') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_church_id (church_id),
    INDEX idx_status (status),
    FOREIGN KEY (church_id) REFERENCES churches(id) ON DELETE SET NULL
);

-- Hierarchy table to manage relationships between users
CREATE TABLE hierarchy (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    parent_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_relationship (user_id, parent_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_parent_id (parent_id)
);

-- Add foreign key constraint for churches.pastor_id
ALTER TABLE churches ADD FOREIGN KEY (pastor_id) REFERENCES users(id) ON DELETE SET NULL;

-- Insert default super admin user
INSERT INTO users (name, email, password, role, status) VALUES 
('Super Admin', 'admin@churchapp.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin', 'active');

-- Insert sample data for testing
INSERT INTO churches (name, address, phone, email) VALUES 
('Dynamic Church', '123 Main Street, City', '+1234567890', 'info@dynamicchurch.local');

-- Insert sample pastor
INSERT INTO users (name, email, password, role, church_id, status) VALUES 
('John Pastor', 'pastor@dynamicchurch.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'pastor', 1, 'active');

-- Update church with pastor
UPDATE churches SET pastor_id = 2 WHERE id = 1;

-- Insert sample coach
INSERT INTO users (name, email, password, role, church_id, status) VALUES 
('Sarah Coach', 'coach@dynamicchurch.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'coach', 1, 'active');

-- Insert sample mentor
INSERT INTO users (name, email, password, role, church_id, status) VALUES 
('Mike Mentor', 'mentor@dynamicchurch.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mentor', 1, 'active');

-- Insert sample members
INSERT INTO users (name, email, password, role, church_id, status) VALUES 
('Alice Member', 'alice@dynamicchurch.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member', 1, 'active'),
('Bob Member', 'bob@dynamicchurch.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member', 1, 'active'),
('Carol Member', 'carol@dynamicchurch.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member', 1, 'active');

-- Set up hierarchy relationships
-- Coach reports to Pastor
INSERT INTO hierarchy (user_id, parent_id) VALUES (3, 2);

-- Mentor reports to Coach
INSERT INTO hierarchy (user_id, parent_id) VALUES (4, 3);

-- Members report to Mentor
INSERT INTO hierarchy (user_id, parent_id) VALUES (5, 4);
INSERT INTO hierarchy (user_id, parent_id) VALUES (6, 4);
INSERT INTO hierarchy (user_id, parent_id) VALUES (7, 4); 