-- Events Tables for Church Management System

USE churchapp;

-- Church Events table (for pastors only)
CREATE TABLE church_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    event_date DATE NOT NULL,
    event_time TIME,
    location VARCHAR(255),
    church_id INT NOT NULL,
    created_by INT NOT NULL,
    status ENUM('active', 'cancelled', 'completed') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_church_id (church_id),
    INDEX idx_created_by (created_by),
    INDEX idx_event_date (event_date),
    INDEX idx_status (status),
    FOREIGN KEY (church_id) REFERENCES churches(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Satelife Events table (for coaches only)
CREATE TABLE satelife_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    event_date DATE NOT NULL,
    event_time TIME,
    location VARCHAR(255),
    church_id INT NOT NULL,
    created_by INT NOT NULL,
    status ENUM('active', 'cancelled', 'completed') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_church_id (church_id),
    INDEX idx_created_by (created_by),
    INDEX idx_event_date (event_date),
    INDEX idx_status (status),
    FOREIGN KEY (church_id) REFERENCES churches(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Lifegroup Events table (for mentors only)
CREATE TABLE lifegroup_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    event_date DATE NOT NULL,
    event_time TIME,
    location VARCHAR(255),
    church_id INT NOT NULL,
    created_by INT NOT NULL,
    status ENUM('active', 'cancelled', 'completed') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_church_id (church_id),
    INDEX idx_created_by (created_by),
    INDEX idx_event_date (event_date),
    INDEX idx_status (status),
    FOREIGN KEY (church_id) REFERENCES churches(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Event Attendees table (for tracking who attends events)
CREATE TABLE event_attendees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_type ENUM('church', 'satelife', 'lifegroup') NOT NULL,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    status ENUM('registered', 'attended', 'cancelled') NOT NULL DEFAULT 'registered',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_attendance (event_type, event_id, user_id),
    INDEX idx_event_type (event_type),
    INDEX idx_event_id (event_id),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert sample events for testing
-- Church Event (created by pastor)
INSERT INTO church_events (title, description, event_date, event_time, location, church_id, created_by, status) VALUES 
('Sunday Service', 'Weekly Sunday worship service', '2025-01-12', '09:00:00', 'Main Sanctuary', 1, 2, 'active');

-- Satelife Event (created by coach)
INSERT INTO satelife_events (title, description, event_date, event_time, location, church_id, created_by, status) VALUES 
('Youth Group Meeting', 'Weekly youth group gathering', '2025-01-15', '18:00:00', 'Youth Hall', 1, 3, 'active');

-- Lifegroup Event (created by mentor)
INSERT INTO lifegroup_events (title, description, event_date, event_time, location, church_id, created_by, status) VALUES 
('Bible Study', 'Weekly Bible study session', '2025-01-14', '19:00:00', 'Conference Room', 1, 4, 'active'); 