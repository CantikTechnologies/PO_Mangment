# Bulk Upload Debug Guide

## Issue: "Unexpected token '<', "..." is not valid JSON

This error means the server is returning HTML instead of JSON, usually due to a PHP error.

## Debugging Steps:

### 1. **Test Connection First**
- Click the "Test Connection" button in the bulk upload modal
- This will test if the basic PHP setup is working
- If this fails, there's a fundamental PHP configuration issue

### 2. **Check Browser Console**
- Open browser Developer Tools (F12)
- Go to Console tab
- Look for any error messages
- The improved error handling will now show the actual server response

### 3. **Check Server Error Logs**
- Look in your XAMPP error logs
- Path: `C:\xampp\apache\logs\error.log`
- Look for PHP errors around the time of upload

### 4. **Common Issues and Solutions**

#### Issue: Permission Denied
**Error:** "Insufficient permissions"
**Solution:** Check if user has 'add_po_details' permission

#### Issue: Database Connection
**Error:** "Configuration error"
**Solution:** Check database connection in `config/db.php`

#### Issue: File Upload Limits
**Error:** "No file uploaded or upload error"
**Solution:** Check PHP upload limits in `php.ini`:
- `upload_max_filesize`
- `post_max_size`
- `max_execution_time`

#### Issue: Memory Limit
**Error:** PHP memory limit exceeded
**Solution:** Increase `memory_limit` in `php.ini`

### 5. **PHP Configuration Check**

Add this to your `php.ini`:
```ini
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
memory_limit = 256M
```

### 6. **File Path Issues**

Make sure the file paths are correct:
- `bulk_upload.php` is in `src/Modules/po_details/`
- The script can access `../../../config/db.php`
- The script can access `../../../config/auth.php`

### 7. **Session Issues**

Check if session is working:
- User must be logged in
- Session must contain 'username'
- User must have proper role and permissions

## Quick Fixes:

### Fix 1: Enable Error Reporting
The script now has error reporting enabled. Check the browser console for detailed error messages.

### Fix 2: Check File Permissions
Make sure the web server can read all the files:
- `bulk_upload.php`
- `config/db.php`
- `config/auth.php`

### Fix 3: Test with Simple File
Try uploading a very simple CSV file first to isolate the issue.

### Fix 4: Check Database
Make sure the database connection is working and the `po_details` table exists.

## Expected Behavior:

1. **Test Connection** should return JSON with session info
2. **File Upload** should return JSON with success/error status
3. **No HTML output** should be returned (this causes the JSON parse error)

## If Still Not Working:

1. Check the actual server response in browser Network tab
2. Look for any PHP warnings or notices
3. Verify all file paths are correct
4. Check if there are any output buffering issues
5. Make sure no whitespace or output before `<?php` tags

## Contact Support:

If the issue persists, provide:
1. Browser console error messages
2. Server error log entries
3. Response from "Test Connection" button
4. PHP version and configuration
