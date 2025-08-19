-- Member Statuses dynamic table and users.status alteration

-- Create member_statuses table
CREATE TABLE IF NOT EXISTS member_statuses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    is_default TINYINT(1) NOT NULL DEFAULT 0,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Seed default statuses if table is empty
INSERT INTO member_statuses (slug, name, is_active, is_default, sort_order)
SELECT * FROM (
    SELECT 'active' AS slug, 'Active' AS name, 1 AS is_active, 1 AS is_default, 1 AS sort_order UNION ALL
    SELECT 'inactive', 'Inactive', 1, 0, 2 UNION ALL
    SELECT 'pending', 'Pending', 1, 0, 3 UNION ALL
    SELECT 'suspended', 'Suspended', 1, 0, 4
) AS defaults
WHERE NOT EXISTS (SELECT 1 FROM member_statuses);

-- Alter users.status from ENUM to VARCHAR to support dynamic values
ALTER TABLE users
    MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'active';

-- Ensure all existing users have valid status slugs
UPDATE users SET status = 'active' WHERE status NOT IN (
    SELECT slug FROM member_statuses
);


