-- Migration: Add coach_id to satelife_events table
-- This makes the relationship between satelife events and coaches more direct

ALTER TABLE `satelife_events` 
ADD COLUMN `coach_id` INT(11) NULL AFTER `church_id`,
ADD INDEX `idx_satelife_events_coach_id` (`coach_id`),
ADD CONSTRAINT `fk_satelife_events_coach_id` 
    FOREIGN KEY (`coach_id`) REFERENCES `users`(`id`) 
    ON DELETE SET NULL ON UPDATE CASCADE;

-- Update existing events to set coach_id based on created_by (assuming the creator is the coach)
-- This is a reasonable default for existing data
UPDATE `satelife_events` 
SET `coach_id` = `created_by` 
WHERE `created_by` IN (SELECT `id` FROM `users` WHERE `role` = 'coach');

-- For events created by non-coaches, we'll leave coach_id as NULL for now
-- These can be manually updated or handled by the application logic
