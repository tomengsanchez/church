# Hierarchy Issue Fix Guide

## Problem Description
The Coach and Mentor are preselected on the create and edit forms, but they are not reflected in the Members list. This is caused by incorrect hierarchy relationships in the database.

## Root Cause
The issue is in the hierarchy table structure. The system expects a **two-level hierarchy**:
1. Member → Mentor
2. Mentor → Coach

But the current data has members assigned to BOTH mentors AND coaches directly, which breaks the expected hierarchy flow.

## Current Incorrect Data Structure
```sql
-- Current problematic relationships:
(7,11,9)  -- Mhel (11) is assigned to Tomeng Sanchez (9) as coach ❌
(8,11,10) -- Mhel (11) is assigned to RJ Salvador (10) as mentor ✓
(6,10,9)  -- RJ Salvador (10) is assigned to Tomeng Sanchez (9) as coach ✓
```

## Correct Data Structure Should Be
```sql
-- Correct hierarchy:
(8,11,10) -- Mhel (11) → RJ Salvador (10) [mentor]
(6,10,9)  -- RJ Salvador (10) → Tomeng Sanchez (9) [coach]
-- This creates: Member → Mentor → Coach
```

## Files Modified
1. **app/Controllers/MemberController.php** - Fixed hierarchy management logic
2. **app/Models/UserModel.php** - Added method to remove incorrect relationships
3. **fix_hierarchy.sql** - SQL script to fix current database

## Steps to Fix

### Step 1: Run the SQL Fix Script
Execute the `fix_hierarchy.sql` script in your database to remove incorrect relationships:

```sql
-- Remove the incorrect direct relationship between Mhel (11) and Tomeng Sanchez (9)
DELETE FROM hierarchy WHERE user_id = 11 AND parent_id = 9;
```

### Step 2: Verify the Fix
After running the script, the hierarchy should show:
- Mhel (11) → RJ Salvador (10) [mentor]
- RJ Salvador (10) → Tomeng Sanchez (9) [coach]

### Step 3: Test the Application
1. Go to the Members list
2. Mhel should now show:
   - **Mentor**: RJ Salvador
   - **Coach**: Tomeng Sanchez (via the mentor relationship)

## How the Fix Works

### Before (Broken)
- Members were assigned to both mentors AND coaches directly
- The SQL query couldn't properly resolve the two-level hierarchy
- Coach and Mentor fields showed "Not Assigned"

### After (Fixed)
- Members are only assigned to mentors
- Mentors are assigned to coaches
- The SQL query can properly traverse: Member → Mentor → Coach
- Coach and Mentor fields now display correctly

## Prevention
The updated code now:
1. **Only** assigns members to mentors (not to coaches)
2. **Ensures** mentors are assigned to coaches
3. **Removes** any direct member-to-coach relationships
4. **Maintains** the proper two-level hierarchy structure

## Testing
After applying the fix:
1. Create a new member with both coach and mentor selected
2. Edit an existing member to change coach/mentor assignments
3. Verify that the Members list shows the correct hierarchy
4. Check that the hierarchy is maintained when updating members

## Database Verification
You can verify the fix worked by running:
```sql
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
```

This should show only the correct two-level hierarchy relationships.
