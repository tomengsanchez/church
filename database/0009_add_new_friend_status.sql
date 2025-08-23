-- Add new_friend status to member_statuses table
INSERT INTO member_statuses (slug, name, is_active, is_default, sort_order, badge_class)
SELECT 'new_friend', 'New Friend', 1, 0, 5, 'info'
WHERE NOT EXISTS (SELECT 1 FROM member_statuses WHERE slug = 'new_friend');
