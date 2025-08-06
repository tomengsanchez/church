-- Migration to make email and password optional for members
-- This allows new friends to be added without email/password

USE churchapp;

-- Modify email field to allow NULL values
ALTER TABLE users MODIFY COLUMN email VARCHAR(255) NULL;

-- Remove the UNIQUE constraint on email (since we want to allow NULL values)
ALTER TABLE users DROP INDEX email;

-- Add a new UNIQUE constraint that only applies to non-NULL email values
ALTER TABLE users ADD UNIQUE KEY unique_email (email);

-- Update existing NULL email values to have a unique placeholder
-- This is needed because we can't have multiple NULL values with a UNIQUE constraint
-- We'll use a pattern like 'no-email-{id}@placeholder.local' for existing records
UPDATE users SET email = CONCAT('no-email-', id, '@placeholder.local') WHERE email IS NULL OR email = '';

-- Now we can safely add the UNIQUE constraint
-- (The above UPDATE ensures no duplicates)

-- Add a comment to document the change
ALTER TABLE users COMMENT = 'Email and password are now optional for members (new friends)'; 