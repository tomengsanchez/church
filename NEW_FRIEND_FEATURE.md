# New Friend Feature Documentation

## Overview
The "Add New Friend" feature allows administrators, pastors, coaches, and mentors to quickly add potential members to the system with minimal information. These new friends are automatically assigned a "Pending" status and can be properly assigned to the church hierarchy later.

## Features

### Quick Addition
- **Minimal Required Information**: Only name is required
- **Optional Fields**: Email, phone, and church assignment
- **Automatic Status**: All new friends are set to "Pending" status
- **Role-Based Access**: Available to Super Admin, Pastor, Coach, and Mentor roles

### Visual Indicators
- **Pending Members Section**: Prominently displayed at the top of the members list
- **Highlighted Rows**: Pending members are highlighted with warning colors
- **"New Friend" Badge**: Visual indicator on pending member names
- **Quick Action Button**: "New Friend" button in the navigation bar

### Workflow
1. **Add New Friend**: Use the quick form to add basic information
2. **Review Pending**: See all pending friends in the dedicated section
3. **Assign Later**: Edit the member profile to assign church, coach, mentor, and lifegroup
4. **Activate**: Change status from "Pending" to "Active" when ready

## How to Use

### Adding a New Friend
1. Click the "New Friend" button in the navigation bar or on the members page
2. Fill in the required name field
3. Optionally add email, phone, and church
4. Click "Add New Friend"
5. The friend will appear in the pending members section

### Managing Pending Friends
1. View pending friends in the highlighted section at the top of the members page
2. Click "Assign" to edit their profile
3. Assign them to appropriate church, coach, mentor, and lifegroup
4. Change their status from "Pending" to "Active"
5. Save the changes

## Benefits

### For Church Administration
- **Quick Onboarding**: Add visitors and potential members immediately
- **No Information Loss**: Capture basic contact info even if full details aren't available
- **Organized Workflow**: Clear separation between pending and active members
- **Flexible Assignment**: Assign to hierarchy when more information is available

### For User Experience
- **Simple Process**: Minimal form fields for quick addition
- **Clear Status**: Visual indicators show which members need attention
- **Easy Management**: Dedicated section for pending members
- **Role-Based Access**: Appropriate permissions for different user roles

## Technical Implementation

### New Routes
- `GET /member/add-friend` - Display the add friend form
- `POST /member/add-friend` - Process the friend creation

### New Controller Methods
- `addFriend()` - Display the form
- `storeFriend()` - Process the form submission

### New View
- `app/views/member/add-friend.php` - The add friend form

### Updated Features
- Member listing with pending section
- Enhanced member table with visual indicators
- Updated navigation with quick action button
- Improved edit form with pending member notices

## Security Considerations
- Role-based access control maintained
- Input validation and sanitization
- Proper error handling and logging
- Session-based authentication required

## Future Enhancements
- Bulk import of new friends
- Email notifications for pending members
- Automated reminders for unassigned friends
- Integration with visitor tracking systems
