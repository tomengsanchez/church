-- Add Satelife Name field to coaches
-- This field is optional and allows coaches to have a different name for their Satelife ministry

USE churchapp;

-- Add satelife_name column to users table
ALTER TABLE users ADD COLUMN satelife_name VARCHAR(255) NULL AFTER name;

-- Add index for better performance when filtering by satelife_name
CREATE INDEX idx_satelife_name ON users(satelife_name);

-- Update existing coaches with sample satelife names (optional)
-- You can uncomment and modify these if you want to set default satelife names
-- UPDATE users SET satelife_name = CONCAT('Satelife ', name) WHERE role = 'coach' AND satelife_name IS NULL; 