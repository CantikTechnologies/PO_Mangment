-- Create table for Tracker Updates (finance_tasks)
-- Use your existing database (do not create DB here)

CREATE TABLE IF NOT EXISTS finance_tasks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  action_requested_by VARCHAR(100) NOT NULL,
  request_date DATE NOT NULL,
  cost_center VARCHAR(100) NOT NULL,
  action_required TEXT NOT NULL,
  action_owner VARCHAR(100) NOT NULL,
  status_of_action ENUM('Pending','In Progress','Completed','On Hold') DEFAULT 'Pending',
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

-- Optional sample rows
INSERT INTO finance_tasks (action_requested_by, request_date, cost_center, action_required, action_owner, status_of_action, completion_date, remark) VALUES
('Naveen', '2025-06-25', 'Raptokos - PT', 'Vratatech - Raptokos PT One month payment to be released immediately', 'Sanjay', 'Pending', NULL, NULL),
('Naveen', '2025-06-25', 'Raptokos - PT', 'Renewal to be followed with Priya', 'Sneha', 'Pending', NULL, NULL),
('Naveen', '2025-06-25', 'BMW-OA', 'Renewal to be followed with Priya', 'Sneha', 'Pending', NULL, NULL);


