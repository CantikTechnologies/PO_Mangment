# Role-Based Access Control System

This document explains the role-based access control system implemented in the PO Management application.

## Overview

The system now supports two user roles:
- **Admin**: Full access to all features
- **Employee**: Limited access with restrictions on editing and deleting

## Setup Instructions

### 1. Run the Setup Script
1. Navigate to `http://localhost/PO-Management/setup_roles.php`
2. The script will automatically:
   - Add role columns to the existing users table
   - Create user profiles table
   - Create role permissions table
   - Insert default permissions
   - Update existing admin user
   - Create audit log table

### 2. Delete Setup File
After successful setup, delete `setup_roles.php` for security.

## Database Structure

### New Tables Created

#### `user_profiles`
Extended user information including:
- Profile picture, bio, address
- Emergency contact information
- Employee ID, hire date

#### `role_permissions`
Defines what each role can do:
- `role`: admin or employee
- `permission`: specific action (e.g., 'edit_po_details')
- `allowed`: 1 for allowed, 0 for denied

#### `audit_log`
Tracks all user actions:
- User ID, action performed
- Table and record affected
- Old and new values (JSON)
- IP address and timestamp

## Permission System

### Admin Permissions (Full Access)
- ✅ View Dashboard
- ✅ View/Add/Edit/Delete PO Details
- ✅ View/Add/Edit/Delete Invoices
- ✅ View/Add/Edit/Delete Outsourcing
- ✅ View Reports
- ✅ Manage Users
- ✅ View/Manage Finance Tasks

### Employee Permissions (Limited Access)
- ✅ View Dashboard
- ✅ View/Add PO Details
- ❌ Edit/Delete PO Details
- ✅ View/Add Invoices
- ❌ Edit/Delete Invoices
- ✅ View/Add Outsourcing
- ❌ Edit/Delete Outsourcing
- ✅ View Reports
- ❌ Manage Users
- ✅ View Finance Tasks
- ❌ Manage Finance Tasks

## User Interface Changes

### Navigation Bar
- User dropdown now shows:
  - User name and role
  - Department (if assigned)
  - Profile link
  - Admin-only links (Manage Users, Audit Log)
  - Logout

### Role-Based UI Elements
- "Add New" buttons only show if user has permission
- Edit/Delete buttons hidden for employees
- Admin panel accessible only to admins

## New Pages

### Profile Page (`profile.php`)
- Personal information management
- Password change functionality
- Account information display

### User Management (`admin/users.php`)
- Create new users
- Toggle user active/inactive status
- View all users and their roles
- Edit user information (admin only)

## Authentication Flow

1. User logs in with email/password
2. System checks if account is active
3. Session variables set including role
4. Each page checks required permissions
5. Access denied if insufficient permissions

## Security Features

### Session Management
- Enhanced session variables include role and user details
- Automatic logout logging
- Session validation on each page

### Audit Trail
- All user actions logged
- Includes IP address and user agent
- JSON storage for old/new values
- Searchable by user, action, or date

### Password Security
- Bcrypt hashing for passwords
- Minimum 6 character requirement
- Password change logging

## Usage Examples

### Checking Permissions in PHP
```php
// Check if user can edit PO details
if (hasPermission('edit_po_details')) {
    // Show edit button
}

// Require admin access
requireAdmin();

// Get current user info
$user = getCurrentUser();
```

### Creating New Users (Admin Only)
1. Go to User Management from user dropdown
2. Click "Add New User"
3. Fill in required information
4. Select role (Admin or Employee)
5. User can login immediately

## Troubleshooting

### Common Issues

1. **Permission Denied Errors**
   - Check if user has correct role
   - Verify permissions in `role_permissions` table
   - Ensure user account is active

2. **Setup Script Errors**
   - Check database connection
   - Ensure MySQL user has CREATE/ALTER permissions
   - Check for existing column conflicts

3. **Login Issues**
   - Verify user account is active (`is_active = 1`)
   - Check password hash in database
   - Clear browser cache and cookies

### Database Queries

```sql
-- Check user permissions
SELECT rp.permission, rp.allowed 
FROM role_permissions rp 
JOIN users_login_signup u ON u.role = rp.role 
WHERE u.id = ?;

-- View audit log
SELECT al.*, u.username 
FROM audit_log al 
JOIN users_login_signup u ON al.user_id = u.id 
ORDER BY al.created_at DESC;

-- List all users with roles
SELECT u.username, u.email, u.role, u.is_active, p.employee_id 
FROM users_login_signup u 
LEFT JOIN user_profiles p ON u.id = p.user_id;
```

## Future Enhancements

- Department-based permissions
- Custom permission sets
- User groups and teams
- Advanced audit reporting
- Two-factor authentication
- Password reset functionality
- User activity dashboard

## Support

For issues or questions about the role system:
1. Check the audit log for user actions
2. Verify database table structure
3. Test with different user roles
4. Review permission configurations
