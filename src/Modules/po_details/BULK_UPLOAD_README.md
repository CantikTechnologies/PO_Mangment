# PO Details Bulk Upload Feature

## Overview
The bulk upload feature allows users to upload multiple Purchase Orders at once using a CSV file. This feature includes comprehensive validation, error handling, and user-friendly interface.

## Features

### 1. User Interface
- **Bulk Upload Button**: Located next to the "New Purchase Order" button on the PO details list page
- **Modal Dialog**: Clean, responsive modal with detailed instructions
- **CSV Preview**: Shows first 5 rows of the CSV file before upload
- **Progress Indicator**: Real-time progress bar during upload
- **Error Reporting**: Detailed error messages for each problematic row
- **Success Feedback**: Clear success message with statistics

### 2. CSV Format Requirements

#### Required Columns (case-sensitive):
- `project_description` - Project name/description (max 500 chars)
- `cost_center` - Cost center code (max 100 chars)
- `sow_number` - Statement of Work number (max 100 chars)
- `start_date` - Start date in Excel serial number format
- `end_date` - End date in Excel serial number format
- `po_number` - Purchase Order number (max 50 chars, must be unique)
- `po_date` - PO date in Excel serial number format
- `po_value` - PO value (numeric, positive)
- `billing_frequency` - Billing frequency (max 50 chars)
- `target_gm` - Target gross margin (decimal 0-1, e.g., 0.05 for 5%)

#### Optional Columns:
- `vendor_name` - Vendor name (max 200 chars)
- `remarks` - Additional remarks

### 3. Date Format
Dates must be in Excel serial number format:
- Example: 45668 represents a specific date
- Use Excel's DATEVALUE function to convert regular dates
- Start date cannot be after end date

### 4. Validation Features
- **Header Validation**: Checks for required columns
- **Data Type Validation**: Ensures numeric fields are valid numbers
- **Length Validation**: Enforces maximum field lengths
- **Duplicate Check**: Prevents duplicate PO numbers
- **Date Validation**: Validates date formats and relationships
- **Business Logic**: Validates target GM range (0-1)

### 5. Error Handling
- **Row-by-row Validation**: Each row is validated individually
- **Detailed Error Messages**: Specific error messages for each issue
- **Transaction Safety**: Uses database transactions for data integrity
- **Partial Success**: Allows partial uploads (some rows succeed, others fail)

### 6. Security Features
- **Authentication**: Requires user login
- **Permission Check**: Requires 'add_po_details' permission
- **File Type Validation**: Only accepts CSV files
- **SQL Injection Protection**: Uses prepared statements
- **Audit Logging**: Logs all bulk upload activities

## File Structure

```
src/Modules/po_details/
├── list.php                 # Main PO list page with bulk upload button
├── bulk_upload.php          # Backend processing script
├── sample_template.csv      # Sample CSV template for users
└── BULK_UPLOAD_README.md    # This documentation
```

## Usage Instructions

### For Users:
1. Click the "Bulk Upload" button on the PO details page
2. Download the sample template if needed
3. Prepare your CSV file with the required columns
4. Select your CSV file - a preview will be shown
5. Review the preview and row count
6. Click "Upload CSV" to process the file
7. Review the results and any error messages

### For Developers:
1. The bulk upload feature is integrated into the existing PO details module
2. All validation logic is in `bulk_upload.php`
3. The modal and JavaScript are embedded in `list.php`
4. Database operations use prepared statements for security
5. All uploads are logged in the audit_log table

## Technical Details

### Database Operations:
- Uses MySQL transactions for data integrity
- Prepared statements prevent SQL injection
- Duplicate PO numbers are automatically skipped
- Audit logging tracks all upload activities

### Frontend Features:
- Responsive design using Tailwind CSS
- Real-time CSV preview
- Progress indication during upload
- Comprehensive error reporting
- Automatic page refresh after successful upload

### Error Handling:
- Validates CSV structure before processing
- Checks each row individually
- Provides specific error messages
- Allows partial uploads (some rows succeed, others fail)
- Rolls back transaction on critical errors

## Sample CSV Format

```csv
project_description,cost_center,sow_number,start_date,end_date,po_number,po_date,po_value,billing_frequency,target_gm,vendor_name,remarks
"Sample Project - Development Team","Sample PT","SOW-2024-001",45668,45742,"4500099999",45679,150000.00,"Monthly",0.05,"Sample Vendor Ltd","Sample project for testing"
"Another Project - QA Team","QA PT","SOW-2024-002",45680,45750,"4500099998",45680,200000.00,"Quarterly",0.08,"Another Vendor Inc","QA project example"
```

## Troubleshooting

### Common Issues:
1. **Missing Headers**: Ensure all required columns are present and spelled correctly
2. **Date Format**: Use Excel serial numbers for dates
3. **Duplicate PO Numbers**: Each PO number must be unique
4. **Data Types**: Ensure numeric fields contain valid numbers
5. **File Size**: Large files may take longer to process

### Error Messages:
- Each error includes the row number and specific issue
- Review all errors before fixing and re-uploading
- Some rows may succeed while others fail

## Future Enhancements

Potential improvements for future versions:
1. Excel file support (.xlsx)
2. Batch processing for very large files
3. Email notifications for upload completion
4. Advanced data validation rules
5. Import history and rollback functionality
