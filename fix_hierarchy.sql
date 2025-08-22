-- Fix Hierarchy Relationships
-- This script removes incorrect direct member-to-coach relationships
-- Members should only be assigned to mentors, not directly to coaches

-- Remove the incorrect direct relationship between Mhel (11) and Tomeng Sanchez (9)
-- Mhel should only be assigned to RJ Salvador (10) as mentor
DELETE FROM hierarchy WHERE user_id = 11 AND parent_id = 9;

-- Verify the correct hierarchy structure:
-- Mhel (11) -> RJ Salvador (10) [mentor]
-- RJ Salvador (10) -> Tomeng Sanchez (9) [coach]
-- This creates the proper two-level hierarchy: Member -> Mentor -> Coach

-- Check current hierarchy after fix
SELECT 
    h.id,
    u1.name as member_name,
    u1.role as member_role,
    u2.name as parent_name,
    u2.role as parent_role
FROM hierarchy h
JOIN users u1 ON h.user_id = u1.id
JOIN users u2 ON h.parent_id = u2.id
WHERE u1.id IN (9, 10, 11) OR u2.id IN (9, 10, 11)
ORDER BY h.id;
