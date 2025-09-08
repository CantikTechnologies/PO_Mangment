-- Create database
CREATE DATABASE IF NOT EXISTS po_management;
USE po_management;

-- Create tracker_updates table with the exact fields specified
CREATE TABLE IF NOT EXISTS finance_tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    action_requested_by VARCHAR(100) NOT NULL,
    request_date DATE NOT NULL,
    cost_center VARCHAR(100) NOT NULL,
    action_required TEXT NOT NULL,
    action_owner VARCHAR(100) NOT NULL,
    status_of_action ENUM('Pending', 'In Progress', 'Completed', 'On Hold') DEFAULT 'Pending',
    completion_date DATE NULL,
    remark TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_request_date (request_date),
    INDEX idx_cost_center (cost_center),
    INDEX idx_status (status_of_action),
    INDEX idx_action_owner (action_owner),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data based on the user's requirements
INSERT INTO finance_tasks (action_requested_by, request_date, cost_center, action_required, action_owner, status_of_action, completion_date, remark) VALUES
('Naveen', '2025-06-25', 'Raptokos - PT', 'Vratatech - Raptokos PT One month payment to be released immediately', 'Sanjay', 'Pending', NULL, NULL),
('Naveen', '2025-06-25', 'Raptokos - PT', 'Renewal to be followed with Priya', 'Sneha', 'Pending', NULL, NULL),
('Naveen', '2025-06-25', 'BMW-OA', 'Renewal to be followed with Priya', 'Sneha', 'Pending', NULL, NULL),
('Maneesh', '2025-06-25', 'Finder Fees - PT', 'Xpheno GST payment to be released', 'Sanjay', 'Pending', NULL, NULL),
('Maneesh', '2025-06-25', 'Finder Fees - PT', 'PO # 4500092198 - Check billing status', 'Sanjay', 'Completed', '2025-06-26', ''),
('Maneesh', '2025-06-26', 'Finder Fees - PT', 'PO # 4500092198 - Check if payment has been made to vendor, else release PO', 'Akshay', 'Completed', '2025-06-27', 'Checked Invoice is pending from Vendor Auropro, Request Sanjay to issue PO once approved, hence the vendor can submit their invoice.'),
('Maneesh', '2025-06-26', 'HCIL PT', '25-26/10 - WinoVision Invoice - Get the CN Against the invoice', 'Sneha', 'Pending', NULL, NULL);
