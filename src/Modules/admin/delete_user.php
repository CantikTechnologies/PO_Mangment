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
$user = null;

// Get user ID from URL
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$user_id) {
    header('Location: users.php');
    exit();
}

// Prevent admin from deleting themselves
if ($user_id == $_SESSION['user_id']) {
    header('Location: users.php?error=' . urlencode('You cannot delete your own account.'));
    exit();
}

// Get user details
$sql = "SELECT u.*, p.employee_id FROM users_login_signup u 
        LEFT JOIN user_profiles p ON u.id = p.user_id 
        WHERE u.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: users.php');
    exit();
}

$user = $result->fetch_assoc();

// Handle deletion confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Delete user profile first (if exists)
        $sql = "DELETE FROM user_profiles WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        // Delete audit logs for this user
        $sql = "DELETE FROM audit_log WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        // Delete the user
        $sql = "DELETE FROM users_login_signup WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        // Log the deletion action
        $auth->logAction('delete_user', 'users_login_signup', $user_id, null, ['deleted_user' => $user['username']]);
        
        // Commit transaction
        $conn->commit();
        
        header('Location: users.php?message=' . urlencode('User deleted successfully!'));
        exit();
        
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();
        $error = "Error deleting user: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Delete User - Cantik</title>
    <link crossorigin="" href="https://fonts.gstatic.com/" rel="preconnect"/>
    <link as="style" href="https://fonts.googleapis.com/css2?display=swap&amp;family=Noto+Sans%3Awght%40400%3B500%3B700%3B900&amp;family=Public+Sans%3Awght%40400%3B500%3B600%3B700%3B800" onload="this.rel='stylesheet'" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
</head>
<body class="bg-gray-50 text-gray-900" style='font-family: "Public Sans", "Noto Sans", sans-serif;'>
    <div class="relative flex size-full min-h-screen flex-col overflow-x-hidden">
        <?php include getSharedIncludePath('nav.php'); ?>
        
        <main class="flex-1 px-4 sm:px-6 lg:px-8 py-8">
            <div class="max-w-2xl mx-auto">
                <div class="flex items-center gap-4 mb-6">
                    <a href="users.php" class="flex items-center justify-center gap-2 rounded-full bg-gray-100 px-4 py-2 text-gray-700 text-sm font-medium hover:bg-gray-200 transition-colors">
                        <span class="material-symbols-outlined text-lg">arrow_back</span>
                        <span>Back to Users</span>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-red-600">Delete User</h1>
                        <p class="text-gray-600 mt-2">Permanently remove user from the system</p>
                    </div>
                </div>

                <?php if ($error): ?>
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-red-800"><?= htmlspecialchars($error) ?></p>
                    </div>
                <?php endif; ?>

                <!-- Delete Confirmation -->
                <div class="bg-white rounded-lg shadow-sm border border-red-200">
                    <div class="px-6 py-4 border-b border-red-200 bg-red-50">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-red-600 text-2xl">warning</span>
                            <h2 class="text-lg font-semibold text-red-800">Confirm User Deletion</h2>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                            <p class="text-red-800 font-medium mb-2">⚠️ This action cannot be undone!</p>
                            <p class="text-red-700 text-sm">Deleting this user will permanently remove:</p>
                            <ul class="text-red-700 text-sm mt-2 ml-4 list-disc">
                                <li>User account and login credentials</li>
                                <li>User profile information</li>
                                <li>All audit log entries for this user</li>
                                <li>All associated data and permissions</li>
                            </ul>
                        </div>

                        <!-- User Information -->
                        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                            <h3 class="text-sm font-medium text-gray-700 mb-3">User to be deleted:</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-500">Name:</span>
                                    <span class="text-gray-900 font-medium"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?: htmlspecialchars($user['username']) ?></span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Email:</span>
                                    <span class="text-gray-900"><?= htmlspecialchars($user['email']) ?></span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Role:</span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium <?= $user['role'] === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' ?>">
                                        <?= ucfirst($user['role']) ?>
                                    </span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Department:</span>
                                    <span class="text-gray-900"><?= htmlspecialchars($user['department'] ?: 'Not assigned') ?></span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Created:</span>
                                    <span class="text-gray-900"><?= date('M j, Y g:i A', strtotime($user['created_at'])) ?></span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Last Login:</span>
                                    <span class="text-gray-900"><?= $user['last_login'] ? date('M j, Y g:i A', strtotime($user['last_login'])) : 'Never' ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Confirmation Form -->
                        <form method="POST">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Type "DELETE" to confirm:
                                </label>
                                <input type="text" name="confirmation" id="confirmation" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500"
                                       placeholder="Type DELETE to confirm"
                                       required>
                            </div>
                            
                            <div class="flex gap-4">
                                <button type="submit" name="confirm_delete" id="deleteBtn" disabled
                                        class="bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 disabled:bg-gray-400 disabled:cursor-not-allowed">
                                    <span class="material-symbols-outlined mr-2">delete</span>
                                    Delete User Permanently
                                </button>
                                <a href="users.php" class="bg-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.getElementById('confirmation').addEventListener('input', function() {
            const deleteBtn = document.getElementById('deleteBtn');
            if (this.value === 'DELETE') {
                deleteBtn.disabled = false;
                deleteBtn.classList.remove('disabled:bg-gray-400', 'disabled:cursor-not-allowed');
                deleteBtn.classList.add('bg-red-600', 'hover:bg-red-700');
            } else {
                deleteBtn.disabled = true;
                deleteBtn.classList.add('disabled:bg-gray-400', 'disabled:cursor-not-allowed');
                deleteBtn.classList.remove('bg-red-600', 'hover:bg-red-700');
            }
        });
    </script>
</body>
</html>
