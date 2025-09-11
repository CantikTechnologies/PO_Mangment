<?php
/**
 * Authentication and Role Management System
 * Handles user authentication, role checking, and permissions
 */

class Auth {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    /**
     * Check if user has permission for specific action
     */
    public function hasPermission($permission) {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        $user_id = $_SESSION['user_id'];
        $role = $_SESSION['role'] ?? 'employee';
        
        $sql = "SELECT allowed FROM role_permissions WHERE role = ? AND permission = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $role, $permission);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return (bool)$row['allowed'];
        }
        
        return false;
    }
    
    /**
     * Check if user is admin
     */
    public function isAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }
    
    /**
     * Check if user is employee
     */
    public function isEmployee() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'employee';
    }
    
    /**
     * Require specific permission or redirect
     */
    public function requirePermission($permission, $redirect_url = '../1Login_signuppage/login.php') {
        if (!$this->hasPermission($permission)) {
            $_SESSION['error'] = "You don't have permission to access this page.";
            header("Location: $redirect_url");
            exit();
        }
    }
    
    /**
     * Require admin role or redirect
     */
    public function requireAdmin($redirect_url = '../1Login_signuppage/login.php') {
        if (!$this->isAdmin()) {
            $_SESSION['error'] = "Admin access required.";
            header("Location: $redirect_url");
            exit();
        }
    }
    
    /**
     * Get current user info
     */
    public function getCurrentUser() {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }
        
        $user_id = $_SESSION['user_id'];
        
        // First try to get user with profile info
        $sql = "SELECT u.*, 
                       p.profile_picture, 
                       p.bio, 
                       p.employee_id,
                       u.department AS department
                FROM users_login_signup u 
                LEFT JOIN user_profiles p ON u.id = p.user_id 
                WHERE u.id = ?";
        $stmt = $this->conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                return $result->fetch_assoc();
            }
        }
        
        // Fallback: get user without profile info
        $sql = "SELECT * FROM users_login_signup WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                // Add default profile fields
                $user['profile_picture'] = null;
                $user['bio'] = null;
                $user['department'] = $user['department'] ?? null;
                $user['employee_id'] = null;
                return $user;
            }
        }
        
        return null;
    }
    
    /**
     * Update last login time
     */
    public function updateLastLogin($user_id) {
        $sql = "UPDATE users_login_signup SET last_login = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
    }
    
    /**
     * Log user action for audit trail
     */
    public function logAction($action, $table_name = null, $record_id = null, $old_values = null, $new_values = null) {
        if (!isset($_SESSION['user_id'])) {
            return;
        }
        
        $user_id = $_SESSION['user_id'];
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        $sql = "INSERT INTO audit_log (user_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        
        $old_json = $old_values ? json_encode($old_values) : null;
        $new_json = $new_values ? json_encode($new_values) : null;
        
        $stmt->bind_param("issiisss", $user_id, $action, $table_name, $record_id, $old_json, $new_json, $ip_address, $user_agent);
        $stmt->execute();
    }
    
    /**
     * Get user permissions for display
     */
    public function getUserPermissions() {
        if (!isset($_SESSION['role'])) {
            return [];
        }
        
        $role = $_SESSION['role'];
        $sql = "SELECT permission FROM role_permissions WHERE role = ? AND allowed = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $role);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $permissions = [];
        while ($row = $result->fetch_assoc()) {
            $permissions[] = $row['permission'];
        }
        
        return $permissions;
    }
}

// Initialize auth system
if (!isset($auth)) {
    include_once 'db.php';
    $auth = new Auth($conn);
}

// Helper functions for easy use in templates
function hasPermission($permission) {
    global $auth;
    return $auth->hasPermission($permission);
}

function isAdmin() {
    global $auth;
    return $auth->isAdmin();
}

function isEmployee() {
    global $auth;
    return $auth->isEmployee();
}

function requirePermission($permission) {
    global $auth;
    $auth->requirePermission($permission);
}

function requireAdmin() {
    global $auth;
    $auth->requireAdmin();
}

function getCurrentUser() {
    global $auth;
    return $auth->getCurrentUser();
}
?>
