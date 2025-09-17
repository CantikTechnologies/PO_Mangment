-- Fix for Generated Column Error in both billing_details and outsourcing_detail tables
-- This script converts the generated columns to regular columns to allow dynamic calculations

USE po_management;

-- =====================================================
-- FIX BILLING_DETAILS TABLE
-- =====================================================

-- Step 1: Drop the generated columns from billing_details
ALTER TABLE billing_details 
DROP COLUMN tds,
DROP COLUMN receivable;

-- Step 2: Add them back as regular decimal columns
ALTER TABLE billing_details 
ADD COLUMN tds decimal(18,2) DEFAULT 0.00 AFTER cantik_inv_value_taxable,
ADD COLUMN receivable decimal(18,2) DEFAULT 0.00 AFTER tds;

-- Step 3: Update existing records to calculate TDS and receivable
-- (assuming 2% TDS for existing records)
UPDATE billing_details 
SET tds = ROUND(cantik_inv_value_taxable * 0.02, 2),
    receivable = ROUND((cantik_inv_value_taxable * 1.18) - (cantik_inv_value_taxable * 0.02), 2)
WHERE tds = 0 OR receivable = 0;

-- =====================================================
-- FIX OUTSOURCING_DETAIL TABLE
-- =====================================================

-- Step 1: Drop the generated columns from outsourcing_detail
ALTER TABLE outsourcing_detail 
DROP COLUMN tds_ded,
DROP COLUMN net_payble,
DROP COLUMN pending_payment;

-- Step 2: Add them back as regular decimal columns
ALTER TABLE outsourcing_detail 
ADD COLUMN tds_ded decimal(18,2) DEFAULT 0.00 AFTER vendor_inv_value,
ADD COLUMN net_payble decimal(18,2) DEFAULT 0.00 AFTER tds_ded,
ADD COLUMN pending_payment decimal(18,2) DEFAULT 0.00 AFTER payment_date;

-- Step 3: Update existing records to calculate TDS, net payable, and pending payment
-- (assuming 2% TDS for existing records)
UPDATE outsourcing_detail 
SET tds_ded = ROUND(vendor_inv_value * 0.02, 2),
    net_payble = ROUND((vendor_inv_value * 1.18) - (vendor_inv_value * 0.02), 2),
    pending_payment = ROUND((vendor_inv_value * 1.18) - (vendor_inv_value * 0.02) - IFNULL(payment_value, 0), 2)
WHERE tds_ded = 0 OR net_payble = 0;

-- =====================================================
-- VERIFICATION QUERIES
-- =====================================================

-- Verify billing_details changes
SELECT id, cantik_inv_value_taxable, tds, receivable 
FROM billing_details 
ORDER BY id DESC 
LIMIT 5;

-- Verify outsourcing_detail changes
SELECT id, vendor_inv_value, tds_ded, net_payble, payment_value, pending_payment 
FROM outsourcing_detail 
ORDER BY id DESC 
LIMIT 5;
