-- Church Management System Database Schema
-- LIFEGIVER CHURCH

-- Create database
CREATE DATABASE IF NOT EXISTS church_management;
USE church_management;

-- Churches table
CREATE TABLE churches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    address TEXT,
    phone VARCHAR(50),
    email VARCHAR(255),
    website VARCHAR(255),
    founded_date DATE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Users table (for all roles: pastors, coaches, mentors, members)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    address TEXT,
    date_of_birth DATE,
    gender ENUM('male', 'female', 'other'),
    
    -- Role and hierarchy
    role ENUM('super_admin', 'pastor', 'coach', 'mentor', 'member') NOT NULL,
    church_id INT,
    pastor_id INT NULL,
    coach_id INT NULL,
    mentor_id INT NULL,
    
    -- Status
    status ENUM('active', 'inactive', 'pending', 'suspended') DEFAULT 'active',
    
    -- Additional fields for different roles
    specialization VARCHAR(255), -- For coaches and mentors
    experience_years INT, -- For mentors
    ordination_date DATE, -- For pastors
    joining_date DATE, -- For coaches and mentors
    
    -- Member-specific fields
    emergency_contact VARCHAR(255),
    emergency_phone VARCHAR(50),
    occupation VARCHAR(255),
    marital_status ENUM('single', 'married', 'divorced', 'widowed'),
    notes TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (church_id) REFERENCES churches(id) ON DELETE SET NULL,
    FOREIGN KEY (pastor_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (coach_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (mentor_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Sessions table for user sessions
CREATE TABLE sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_id VARCHAR(255) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert default super admin user
INSERT INTO users (name, email, password, role, status) VALUES 
('Super Admin', 'admin@lifegiver.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin', 'active');

-- Insert sample church
INSERT INTO churches (name, address, phone, email, website, description) VALUES 
('LIFEGIVER CHURCH', '123 Main Street, City, State 12345', '+1-555-123-4567', 'info@lifegiver.com', 'https://lifegiver.com', 'A vibrant community of believers dedicated to spreading the love of Christ.');

-- Insert sample pastor
INSERT INTO users (name, email, password, role, church_id, status, ordination_date) VALUES 
('Pastor John Smith', 'pastor@lifegiver.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'pastor', 1, 'active', '2020-01-15');

-- Insert sample coach
INSERT INTO users (name, email, password, role, church_id, pastor_id, status, specialization, joining_date) VALUES 
('Coach Sarah Johnson', 'coach@lifegiver.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'coach', 1, 2, 'active', 'Youth Ministry', '2021-03-20');

-- Insert sample mentor
INSERT INTO users (name, email, password, role, church_id, pastor_id, coach_id, status, specialization, experience_years, joining_date) VALUES 
('Mentor David Wilson', 'mentor@lifegiver.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mentor', 1, 2, 3, 'active', 'Counseling', 5, '2022-06-10');

-- Insert sample member
INSERT INTO users (name, email, password, role, church_id, pastor_id, coach_id, mentor_id, status, gender, occupation, marital_status, emergency_contact, emergency_phone) VALUES 
('Member Mary Brown', 'member@lifegiver.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member', 1, 2, 3, 4, 'active', 'female', 'Teacher', 'married', 'John Brown', '+1-555-987-6543');

-- Create indexes for better performance
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_church_id ON users(church_id);
CREATE INDEX idx_users_status ON users(status);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_churches_name ON churches(name); 