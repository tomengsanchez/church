-- Lifegroups Table for Church Management System

USE churchapp;

-- Lifegroups table
CREATE TABLE lifegroups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    church_id INT NOT NULL,
    mentor_id INT NOT NULL,
    meeting_day VARCHAR(50),
    meeting_time TIME,
    meeting_location VARCHAR(255),
    max_members INT DEFAULT 20,
    status ENUM('active', 'inactive', 'full') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_church_id (church_id),
    INDEX idx_mentor_id (mentor_id),
    INDEX idx_status (status),
    FOREIGN KEY (church_id) REFERENCES churches(id) ON DELETE CASCADE,
    FOREIGN KEY (mentor_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Lifegroup Members table (for tracking who belongs to which lifegroup)
CREATE TABLE lifegroup_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lifegroup_id INT NOT NULL,
    user_id INT NOT NULL,
    joined_date DATE NOT NULL,
    status ENUM('active', 'inactive', 'left') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_membership (lifegroup_id, user_id),
    INDEX idx_lifegroup_id (lifegroup_id),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    FOREIGN KEY (lifegroup_id) REFERENCES lifegroups(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert sample lifegroups for testing
INSERT INTO lifegroups (name, description, church_id, mentor_id, meeting_day, meeting_time, meeting_location, max_members, status) VALUES 
('Young Adults Group', 'A lifegroup for young adults aged 18-30', 1, 4, 'Tuesday', '19:00:00', 'Conference Room A', 15, 'active'),
('Family Group', 'A lifegroup for families with children', 1, 4, 'Wednesday', '18:30:00', 'Fellowship Hall', 12, 'active'),
('Senior Adults Group', 'A lifegroup for senior adults', 1, 4, 'Thursday', '14:00:00', 'Library', 10, 'active');

-- Insert sample lifegroup members
INSERT INTO lifegroup_members (lifegroup_id, user_id, joined_date, status) VALUES 
(1, 5, '2024-01-01', 'active'),
(1, 6, '2024-01-01', 'active'),
(2, 7, '2024-01-01', 'active'); 