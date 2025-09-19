<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: ' . getLoginUrl());
    exit();
}

include '../../../config/db.php';
include '../../../config/auth.php';

// Require admin access
requireAdmin();

$message = '';
$error = '';
$editUser = null;

// Get user ID from URL
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$user_id) {
    header('Location: users.php');
    exit();
}

// Get user details
$sql = "SELECT u.*, p.employee_id FROM users_login_signup u 
        LEFT JOIN user_profiles p ON u.id = p.user_id 
        WHERE u.id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    $error = "Database error: " . $conn->error;
    $editUser = null;
} else {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        header('Location: users.php');
        exit();
    }

    $editUser = $result->fetch_assoc();
    
    // Debug: Log user data
    error_log("Edit target user data retrieved: " . print_r($editUser, true));
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $phone = trim($_POST['phone']);
    $department = trim($_POST['department']);
    $employee_id = trim($_POST['employee_id']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    try {
        // Check if email already exists (excluding current user)
        $sql = "SELECT id FROM users_login_signup WHERE email = ? AND id != ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            throw new Exception("Email already exists.");
        }
        
        // Check if username already exists (excluding current user)
        $sql = "SELECT id FROM users_login_signup WHERE username = ? AND id != ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $username, $user_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            throw new Exception("Username already exists.");
        }
        
        // Update user
        if (!empty($password)) {
            // Update with new password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE users_login_signup SET username = ?, email = ?, password = ?, role = ?, first_name = ?, last_name = ?, phone = ?, department = ?, is_active = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Failed to prepare statement: " . $conn->error);
            }
            $stmt->bind_param("ssssssssii", $username, $email, $hashed_password, $role, $first_name, $last_name, $phone, $department, $is_active, $user_id);
        } else {
            // Update without changing password
            $sql = "UPDATE users_login_signup SET username = ?, email = ?, role = ?, first_name = ?, last_name = ?, phone = ?, department = ?, is_active = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Failed to prepare statement: " . $conn->error);
            }
            $stmt->bind_param("sssssssii", $username, $email, $role, $first_name, $last_name, $phone, $department, $is_active, $user_id);
        }
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to update user: " . $stmt->error);
        }
        
        // Update or create profile
        if ($employee_id) {
            // First check if user_profiles table exists, if not create it
            $check_table = $conn->query("SHOW TABLES LIKE 'user_profiles'");
            if ($check_table->num_rows == 0) {
                $create_table = "CREATE TABLE user_profiles (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    employee_id VARCHAR(50),
                    profile_picture VARCHAR(255),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users_login_signup(id) ON DELETE CASCADE
                )";
                $conn->query($create_table);
            }
            
            $sql = "INSERT INTO user_profiles (user_id, employee_id) VALUES (?, ?) 
                    ON DUPLICATE KEY UPDATE employee_id = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("iss", $user_id, $employee_id, $employee_id);
                if (!$stmt->execute()) {
                    throw new Exception("Failed to update user profile: " . $stmt->error);
                }
            }
        }
        
        $auth->logAction('update_user', 'users_login_signup', $user_id);
        $message = "User updated successfully!";
        
        // Refresh user data
        $sql = "SELECT u.*, p.employee_id FROM users_login_signup u 
                LEFT JOIN user_profiles p ON u.id = p.user_id 
                WHERE u.id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $editUser = $result->fetch_assoc();
        
    } catch (Exception $e) {
        $error = "Error updating user: " . $e->getMessage();
        // Debug information (remove in production)
        error_log("Edit user error: " . $e->getMessage());
        error_log("User ID: " . $user_id);
        error_log("POST data: " . print_r($_POST, true));
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Edit User - Cantik</title>
    <link crossorigin="" href="https://fonts.gstatic.com/" rel="preconnect"/>
    <link as="style" href="https://fonts.googleapis.com/css2?display=swap&amp;family=Noto+Sans%3Awght%40400%3B500%3B700%3B900&amp;family=Public+Sans%3Awght%40400%3B500%3B600%3B700%3B800" onload="this.rel='stylesheet'" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
</head>
<body class="bg-gray-50 text-gray-900" style='font-family: "Public Sans", "Noto Sans", sans-serif;'>
    <div class="relative flex size-full min-h-screen flex-col overflow-x-hidden">
        <?php include getSharedIncludePath('nav.php'); ?>
        
        <main class="flex-1 px-4 sm:px-6 lg:px-8 py-8">
            <div class="max-w-4xl mx-auto">
                <div class="flex items-center gap-4 mb-6">
                    <a href="users.php" class="flex items-center justify-center gap-2 rounded-full bg-gray-100 px-4 py-2 text-gray-700 text-sm font-medium hover:bg-gray-200 transition-colors">
                        <span class="material-symbols-outlined text-lg">arrow_back</span>
                        <span>Back to Users</span>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Edit User</h1>
                        <p class="text-gray-600 mt-2">Update user information and permissions</p>
                    </div>
                </div>

                <?php if ($message): ?>
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                        <p class="text-green-800"><?= htmlspecialchars($message) ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-red-800"><?= htmlspecialchars($error) ?></p>
                        <?php if (isset($_GET['debug'])): ?>
                        <div class="mt-2 text-xs text-red-600">
                            <strong>Debug Info:</strong><br>
                            User ID: <?= $user_id ?><br>
                            User Data: <?= htmlspecialchars(print_r($user, true)) ?><br>
                            POST Data: <?= htmlspecialchars(print_r($_POST, true)) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['debug']) && $editUser): ?>
                    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <h3 class="text-blue-800 font-medium mb-2">Debug Information</h3>
                        <div class="text-xs text-blue-600">
                            <strong>User ID:</strong> <?= $user_id ?><br>
                            <strong>User Data:</strong><br>
                            <pre class="mt-2 bg-white p-2 rounded border text-xs overflow-auto"><?= htmlspecialchars(print_r($editUser, true)) ?></pre>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Edit User Form -->
                <?php if ($editUser): ?>
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">User Information</h2>
                        <p class="text-sm text-gray-500 mt-1">Editing: <?= htmlspecialchars(($editUser['first_name'] ?? '') . ' ' . ($editUser['last_name'] ?? '')) ?: htmlspecialchars($editUser['username'] ?? '') ?></p>
                    </div>
                    <form method="POST" class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Username *</label>
                                <input type="text" name="username" required value="<?= htmlspecialchars($editUser['username']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                                <input type="email" name="email" required value="<?= htmlspecialchars($editUser['email']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                                <input type="password" name="password" minlength="6" placeholder="Leave blank to keep current password"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                                <p class="text-xs text-gray-500 mt-1">Leave blank to keep current password</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Role *</label>
                                <select name="role" required 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                                    <option value="employee" <?= ($editUser['role'] === 'employee') ? 'selected' : '' ?>>Employee</option>
                                    <option value="admin" <?= ($editUser['role'] === 'admin') ? 'selected' : '' ?>>Admin</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                                <input type="text" name="first_name" value="<?= htmlspecialchars($editUser['first_name']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                                <input type="text" name="last_name" value="<?= htmlspecialchars($editUser['last_name']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                <input type="tel" name="phone" value="<?= htmlspecialchars($editUser['phone']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                                <input type="text" name="department" value="<?= htmlspecialchars($editUser['department']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Employee ID</label>
                                <input type="text" name="employee_id" value="<?= htmlspecialchars($editUser['employee_id']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" name="is_active" value="1" <?= ($editUser['is_active'] ? 'checked' : '') ?>
                                       class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                                <label class="ml-2 block text-sm text-gray-900">Active User</label>
                            </div>
                        </div>
                        
                        <!-- User Stats -->
                        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                            <h3 class="text-sm font-medium text-gray-700 mb-2">Account Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-500">Created:</span>
                                    <span class="text-gray-900"><?= date('M j, Y g:i A', strtotime($editUser['created_at'])) ?></span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Last Login:</span>
                                    <span class="text-gray-900"><?= $editUser['last_login'] ? date('M j, Y g:i A', strtotime($editUser['last_login'])) : 'Never' ?></span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Last Updated:</span>
                                    <span class="text-gray-900"><?= date('M j, Y g:i A', strtotime($editUser['updated_at'])) ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex gap-4 mt-6">
                            <button type="submit" class="bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                Update User
                            </button>
                            <a href="users.php" class="bg-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
                <?php else: ?>
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="text-center">
                        <h2 class="text-lg font-semibold text-gray-900 mb-2">User Not Found</h2>
                        <p class="text-gray-600 mb-4">The user you're trying to edit could not be found.</p>
                        <a href="users.php" class="bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                            Back to Users
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
