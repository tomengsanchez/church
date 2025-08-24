-- Migration: Add created_by field to specified tables
-- This migration adds the created_by field to churches, event_attendees, lifegroups, lifegroup_members, member_statuses, and users tables

-- Add created_by to churches table
ALTER TABLE churches ADD COLUMN created_by INT NULL AFTER email;
ALTER TABLE churches ADD FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL;

-- Add created_by to event_attendees table
ALTER TABLE event_attendees ADD COLUMN created_by INT NULL AFTER status;
ALTER TABLE event_attendees ADD FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL;

-- Add created_by to lifegroups table
ALTER TABLE lifegroups ADD COLUMN created_by INT NULL AFTER church_id;
ALTER TABLE lifegroups ADD FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL;

-- Add created_by to lifegroup_members table
ALTER TABLE lifegroup_members ADD COLUMN created_by INT NULL AFTER user_id;
ALTER TABLE lifegroup_members ADD FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL;

-- Add created_by to member_statuses table
ALTER TABLE member_statuses ADD COLUMN created_by INT NULL AFTER badge_class;
ALTER TABLE member_statuses ADD FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL;

-- Add created_by to users table
ALTER TABLE users ADD COLUMN created_by INT NULL AFTER status;
ALTER TABLE users ADD FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL;

-- Create indexes for better performance on created_by columns
CREATE INDEX idx_churches_created_by ON churches(created_by);
CREATE INDEX idx_event_attendees_created_by ON event_attendees(created_by);
CREATE INDEX idx_lifegroups_created_by ON lifegroups(created_by);
CREATE INDEX idx_lifegroup_members_created_by ON lifegroup_members(created_by);
CREATE INDEX idx_member_statuses_created_by ON member_statuses(created_by);
CREATE INDEX idx_users_created_by ON users(created_by);

-- Update existing records to set created_by to Super Admin (user_id = 1) where it's NULL
UPDATE churches SET created_by = 1 WHERE created_by IS NULL;
UPDATE event_attendees SET created_by = 1 WHERE created_by IS NULL;
UPDATE lifegroups SET created_by = 1 WHERE created_by IS NULL;
UPDATE lifegroup_members SET created_by = 1 WHERE created_by IS NULL;
UPDATE member_statuses SET created_by = 1 WHERE created_by IS NULL;
UPDATE users SET created_by = 1 WHERE created_by IS NULL AND id != 1;
