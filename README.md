# PO Management System - Cantik Homemade

A comprehensive Purchase Order Management System built with PHP, MySQL, and modern web technologies. This system handles purchase orders, invoices, outsourcing records, and provides detailed reporting capabilities.

## 🚀 Features

### Core Modules
- **Purchase Orders Management** - Create, edit, view, and track purchase orders
- **Invoice Management** - Handle customer invoices with TDS calculations
- **Outsourcing Management** - Track vendor outsourcing records and payments
- **Reports & Analytics** - SO Form with comprehensive project summaries
- **Task Tracker** - Project task management and updates
- **User Management** - Role-based access control with admin features

### Key Capabilities
- ✅ **Bulk Upload** - CSV/TSV import for all modules
- ✅ **Advanced Filtering** - Search and filter across all records
- ✅ **Financial Calculations** - Automatic TDS, receivable, and margin calculations
- ✅ **Audit Logging** - Complete action tracking and user activity logs
- ✅ **Responsive Design** - Modern UI that works on all devices
- ✅ **Role-Based Security** - Admin, employee, and permission-based access

## 📋 Requirements

### Server Requirements
- **PHP** 7.4 or higher
- **MySQL** 5.7 or higher / **MariaDB** 10.2 or higher
- **Web Server** (Apache/Nginx)
- **Extensions**: mysqli, session, json

### Local Development
- **XAMPP** (recommended) or similar local server stack
- **Git** for version control

## 🛠️ Installation

### 1. Clone/Download the Project
```bash
git clone <repository-url>
# or download and extract the ZIP file
```

### 2. Database Setup
1. Create a MySQL database named `po_management`
2. Import the database schema:
   ```sql
   mysql -u root -p po_management < database/po_management.sql
   ```

### 3. Configuration
1. Update database credentials in `config/db.php`:
   ```php
   $host = "localhost";
   $user = "your_username";
   $pass = "your_password";
   $db   = "po_management";
   ```

### 4. File Permissions
Ensure the following directories are writable:
```bash
chmod 755 storage/uploads/
chmod 755 storage/uploads/profile_images/
```

### 5. Access the Application
- **Local**: `http://localhost/po-mgmt/`
- **Production**: `https://yourdomain.com/`

## 👥 Default Login

The system comes with a default admin account:
- **Email**: `admin@cantik.com`
- **Password**: `admin123`

> ⚠️ **Important**: Change the default password immediately after first login!

## 📁 Project Structure

```
po-mgmt/
├── assets/                 # Static assets (CSS, JS, images)
├── config/                 # Configuration files
│   ├── auth.php           # Authentication system
│   ├── db.php             # Database configuration
│   └── paths.php          # Path resolution system
├── database/              # Database schema and migrations
├── docs/                  # Documentation and sample files
├── src/
│   ├── Modules/           # Application modules
│   │   ├── admin/         # User management
│   │   ├── invoices/      # Invoice management
│   │   ├── outsourcing/   # Outsourcing management
│   │   ├── po_details/    # Purchase order management
│   │   ├── Tracker/       # Task tracking
│   │   └── User/          # User profiles
│   └── shared/            # Shared components
│       ├── includes.php   # Universal includes
│       └── nav.php        # Navigation component
├── storage/               # File uploads and storage
├── index.php             # Dashboard
├── login.php             # Login page
├── logout.php            # Logout handler
└── so_form.php           # Reports page
```

## 🔧 Configuration

### Path System
The system uses a dynamic path resolution system that automatically adapts to different deployment environments:

- **Local Development**: Works with XAMPP/htdocs structure
- **Production**: Adapts to any directory structure
- **Subdirectories**: Automatically detects and adjusts paths

### Environment Detection
The system automatically detects:
- Local vs production environment
- Directory structure
- Base path calculation
- Asset path resolution

## 📊 Modules Overview

### Purchase Orders (`src/Modules/po_details/`)
- Create and manage purchase orders
- Link to customer POs and vendors
- Track PO status and values
- Bulk upload from CSV/TSV

### Invoices (`src/Modules/invoices/`)
- Generate customer invoices
- Automatic TDS calculations (2% or 10%)
- Receivable amount computation
- Payment tracking

### Outsourcing (`src/Modules/outsourcing/`)
- Manage vendor outsourcing records
- Track vendor invoices and payments
- Calculate pending payments
- Vendor performance monitoring

### Reports (`so_form.php`)
- Comprehensive project summaries
- Financial analytics and margins
- Variance calculations
- Export to Excel functionality

### Task Tracker (`src/Modules/Tracker/`)
- Project task management
- Status updates and progress tracking
- Team collaboration features

## 🔐 Security Features

### Authentication & Authorization
- **Session-based authentication**
- **Role-based access control** (Admin, Employee)
- **Permission-based module access**
- **Secure password hashing**

### Data Protection
- **SQL injection prevention** (prepared statements)
- **XSS protection** (input sanitization)
- **CSRF protection** (session validation)
- **Audit logging** for all actions

### User Roles
- **Admin**: Full system access, user management
- **Employee**: Limited access based on permissions

## 📈 Bulk Upload Features

### Supported Formats
- **CSV** (comma-separated values)
- **TSV** (tab-separated values)
- **Excel serial dates** (automatic conversion)

### Upload Process
1. Download sample templates
2. Fill data in required format
3. Upload via bulk upload interface
4. Validation and error reporting
5. Dry run option for testing

### Sample Templates
Available for all modules:
- `src/Modules/po_details/sample_template.csv`
- `src/Modules/invoices/sample_template.csv`
- `src/Modules/outsourcing/sample_template.csv`

## 🚀 Deployment

### Local Development
1. Install XAMPP
2. Place project in `htdocs/po-mgmt/`
3. Start Apache and MySQL
4. Access via `http://localhost/po-mgmt/`

### Production Deployment
1. Upload files to web server
2. Configure database connection
3. Set proper file permissions
4. Update `config/db.php` with production credentials
5. Test all functionality

### Environment Variables
For production, consider using environment variables:
```php
$host = $_ENV['DB_HOST'] ?? 'localhost';
$user = $_ENV['DB_USER'] ?? 'root';
$pass = $_ENV['DB_PASS'] ?? '';
$db   = $_ENV['DB_NAME'] ?? 'po_management';
```

## 🐛 Troubleshooting

### Common Issues

#### 500 Internal Server Error
- Check database connection in `config/db.php`
- Verify file permissions
- Check PHP error logs
- Ensure all required PHP extensions are installed

#### Path Issues
- The system automatically handles path resolution
- If issues persist, check `config/paths.php`
- Verify web server configuration

#### Database Connection Issues
- Verify MySQL service is running
- Check database credentials
- Ensure database exists and is accessible
- Import the schema from `database/po_management.sql`

#### Permission Issues
- Ensure `storage/uploads/` is writable
- Check file ownership
- Verify web server has read access to all files

### Debug Mode
Add `?debug_paths=1` to any URL to see path debugging information.

## 📝 API Endpoints

### AJAX Endpoints
- `src/Modules/po_details/get_po.php` - Fetch PO details
- `src/Modules/outsourcing/get_po_vendor_sum.php` - Get vendor summaries
- `src/Modules/Tracker/get_task.php` - Fetch task details

### Bulk Upload Endpoints
- `src/Modules/po_details/bulk_upload.php`
- `src/Modules/invoices/bulk_upload.php`
- `src/Modules/outsourcing/bulk_upload.php`

## 🔄 Updates & Maintenance

### Regular Maintenance
- Monitor audit logs for suspicious activity
- Regular database backups
- Update user passwords periodically
- Review and clean up old records

### Backup Strategy
```bash
# Database backup
mysqldump -u username -p po_management > backup_$(date +%Y%m%d).sql

# File backup
tar -czf files_backup_$(date +%Y%m%d).tar.gz po-mgmt/
```

## 📞 Support

### Documentation
- Check this README for common issues
- Review code comments for technical details
- Check `docs/` folder for additional documentation

### Development
- Follow PSR-4 autoloading standards
- Use prepared statements for database queries
- Implement proper error handling
- Follow the existing code structure

## 📄 License

This project is proprietary software developed for Cantik Homemade. All rights reserved.

## 🏆 Credits

Developed for **Cantik Homemade** - A comprehensive PO Management solution for modern business operations.

---

**Version**: 1.0.0  
**Last Updated**: January 2025  
**PHP Version**: 7.4+  
**Database**: MySQL 5.7+
