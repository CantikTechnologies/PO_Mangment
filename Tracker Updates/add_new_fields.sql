-- Migration script to simplify finance_tasks table
-- Run this script if you have an existing database with old structure

USE po_management;

-- Drop old columns if they exist
ALTER TABLE finance_tasks 
DROP COLUMN IF EXISTS task_date,
DROP COLUMN IF EXISTS emp_dept,
DROP COLUMN IF EXISTS emp_id;

-- Reorder columns to match new structure
    ALTER TABLE finance_tasks 
    MODIFY COLUMN action_req_by VARCHAR(100) NOT NULL AFTER id;

    -- Drop old indexes
    ALTER TABLE finance_tasks 
    DROP INDEX IF EXISTS idx_task_date,
    DROP INDEX IF EXISTS idx_emp_dept;

    -- Update sample data if needed (optional)
    -- This will set default values for any existing records
    UPDATE finance_tasks SET 
        action_req_by = 'System' WHERE action_req_by IS NULL OR action_req_by = '';
