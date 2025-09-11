<?php
session_start();
if (!isset($_SESSION['username'])) {
  header('Location: ../../../login.php');
  exit();
}
include '../../../config/db.php';
include '../../../config/auth.php';

$user = getCurrentUser();
$message = '';
$error = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $phone = trim($_POST['phone']);
    $department = trim($_POST['department']);
    $bio = trim($_POST['bio']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $country = trim($_POST['country']);
    $postal_code = trim($_POST['postal_code']);
    $emergency_contact_name = trim($_POST['emergency_contact_name']);
    $emergency_contact_phone = trim($_POST['emergency_contact_phone']);
    
    try {
        // Update main user table
        $sql = "UPDATE users_login_signup SET first_name = ?, last_name = ?, phone = ?, department = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $first_name, $last_name, $phone, $department, $_SESSION['user_id']);
        $stmt->execute();
        
        // Check if user_profiles table exists, if not create it
        $check_table = $conn->query("SHOW TABLES LIKE 'user_profiles'");
        if ($check_table->num_rows == 0) {
            $create_table = "CREATE TABLE user_profiles (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                profile_picture VARCHAR(255),
                bio TEXT,
                department VARCHAR(100),
                employee_id VARCHAR(50),
                address TEXT,
                city VARCHAR(100),
                state VARCHAR(100),
                country VARCHAR(100),
                postal_code VARCHAR(20),
                emergency_contact_name VARCHAR(100),
                emergency_contact_phone VARCHAR(20),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY unique_user (user_id),
                FOREIGN KEY (user_id) REFERENCES users_login_signup(id) ON DELETE CASCADE
            )";
            $conn->query($create_table);
        }
        
        // Update or insert profile
        $sql = "INSERT INTO user_profiles (user_id, bio, address, city, state, country, postal_code, emergency_contact_name, emergency_contact_phone) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE 
                bio = VALUES(bio), address = VALUES(address), city = VALUES(city), state = VALUES(state), 
                country = VALUES(country), postal_code = VALUES(postal_code), 
                emergency_contact_name = VALUES(emergency_contact_name), emergency_contact_phone = VALUES(emergency_contact_phone)";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("issssssss", $_SESSION['user_id'], $bio, $address, $city, $state, $country, $postal_code, $emergency_contact_name, $emergency_contact_phone);
            $stmt->execute();
        }
        
        // Update session variables
        $_SESSION['first_name'] = $first_name;
        $_SESSION['last_name'] = $last_name;
        $_SESSION['department'] = $department;
        
        // Log the action
        $auth->logAction('update_profile', 'users_login_signup', $_SESSION['user_id']);
        
        $message = "Profile updated successfully!";
        $user = getCurrentUser(); // Refresh user data
    } catch (Exception $e) {
        $error = "Error updating profile: " . $e->getMessage();
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($new_password !== $confirm_password) {
        $error = "New passwords do not match.";
    } elseif (strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        // Verify current password
        $sql = "SELECT password FROM users_login_signup WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_data = $result->fetch_assoc();
        
        if (password_verify($current_password, $user_data['password'])) {
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql = "UPDATE users_login_signup SET password = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $hashed_password, $_SESSION['user_id']);
            $stmt->execute();
            
            $auth->logAction('change_password', 'users_login_signup', $_SESSION['user_id']);
            $message = "Password changed successfully!";
        } else {
            $error = "Current password is incorrect.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>My Profile - Cantik Homemade</title>
    <link crossorigin="" href="https://fonts.gstatic.com/" rel="preconnect"/>
    <link as="style" href="https://fonts.googleapis.com/css2?display=swap&amp;family=Noto+Sans%3Awght%40400%3B500%3B700%3B900&amp;family=Public+Sans%3Awght%40400%3B500%3B600%3B700%3B800" onload="this.rel='stylesheet'" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
</head>
<body class="bg-gray-50 text-gray-900" style='font-family: "Public Sans", "Noto Sans", sans-serif;'>
    <div class="relative flex size-full min-h-screen flex-col overflow-x-hidden">
        <?php include '../../shared/nav.php'; ?>
        
        <main class="flex-1 px-4 sm:px-6 lg:px-8 py-8">
            <div class="max-w-4xl mx-auto">
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">My Profile</h1>
                    <p class="text-gray-600 mt-2">Manage your account settings and personal information</p>
                </div>

                <?php if ($message): ?>
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                        <p class="text-green-800"><?= htmlspecialchars($message) ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-red-800"><?= htmlspecialchars($error) ?></p>
                    </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Profile Information -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">Personal Information</h2>
                        </div>
                        <form method="POST" class="p-6 space-y-4">
                            <input type="hidden" name="update_profile" value="1">
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                                    <input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                                    <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-500">
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                    <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                                    <input type="text" name="department" value="<?= htmlspecialchars($user['department'] ?? '') ?>" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Bio</label>
                                <textarea name="bio" rows="3" 
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                                <textarea name="address" rows="2" 
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                                    <input type="text" name="city" value="<?= htmlspecialchars($user['city'] ?? '') ?>" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">State</label>
                                    <input type="text" name="state" value="<?= htmlspecialchars($user['state'] ?? '') ?>" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Postal Code</label>
                                    <input type="text" name="postal_code" value="<?= htmlspecialchars($user['postal_code'] ?? '') ?>" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                                <input type="text" name="country" value="<?= htmlspecialchars($user['country'] ?? '') ?>" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>

                            <div class="pt-4">
                                <button type="submit" class="w-full bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                    Update Profile
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Security Settings -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">Security Settings</h2>
                        </div>
                        <form method="POST" class="p-6 space-y-4">
                            <input type="hidden" name="change_password" value="1">
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                                <input type="password" name="current_password" required 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                                <input type="password" name="new_password" required minlength="6"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                                <input type="password" name="confirm_password" required minlength="6"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>

                            <div class="pt-4">
                                <button type="submit" class="w-full bg-gray-600 text-white py-2 px-4 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                    Change Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Profile Image -->
                <div class="mt-8 bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Profile Picture</h2>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center gap-6">
                            <div class="relative">
                                <?php if (isset($user['profile_picture']) && $user['profile_picture'] && file_exists($user['profile_picture'])): ?>
                                    <img src="<?= htmlspecialchars($user['profile_picture']) ?>" 
                                         alt="Profile Picture" 
                                         class="w-24 h-24 rounded-full object-cover border-4 border-gray-200">
                                <?php else: ?>
                                    <div class="w-24 h-24 rounded-full bg-gray-200 flex items-center justify-center border-4 border-gray-200">
                                        <span class="material-symbols-outlined text-4xl text-gray-400">person</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div>
                                <h3 class="text-lg font-medium text-gray-900"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?: htmlspecialchars($user['username']) ?></h3>
                                <p class="text-gray-500"><?= htmlspecialchars($user['email']) ?></p>
                                <div class="mt-4">
                                    <a href="upload_profile_image.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700">
                                        <span class="material-symbols-outlined mr-2 text-sm">photo_camera</span>
                                        <?= $user['profile_picture'] ? 'Change Photo' : 'Upload Photo' ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Account Information -->
                <div class="mt-8 bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Account Information</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                                <p class="text-gray-900"><?= htmlspecialchars($user['username']) ?></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                                <p class="text-gray-900 capitalize"><?= htmlspecialchars($user['role']) ?></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Employee ID</label>
                                <p class="text-gray-900"><?= htmlspecialchars($user['employee_id'] ?? 'Not assigned') ?></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Last Login</label>
                                <p class="text-gray-900"><?= $user['last_login'] ? date('M j, Y g:i A', strtotime($user['last_login'])) : 'Never' ?></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Account Created</label>
                                <p class="text-gray-900"><?= date('M j, Y', strtotime($user['created_at'])) ?></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $user['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                    <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
