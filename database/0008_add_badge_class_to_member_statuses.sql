-- Add badge_class column to member_statuses table
ALTER TABLE member_statuses ADD COLUMN badge_class VARCHAR(20) DEFAULT NULL AFTER sort_order;

-- Update existing statuses with default badge classes
UPDATE member_statuses SET badge_class = 'success' WHERE slug = 'active';
UPDATE member_statuses SET badge_class = 'secondary' WHERE slug = 'inactive';
UPDATE member_statuses SET badge_class = 'warning' WHERE slug = 'pending';
UPDATE member_statuses SET badge_class = 'danger' WHERE slug = 'suspended';
