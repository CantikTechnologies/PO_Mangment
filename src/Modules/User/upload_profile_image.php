<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: ../../public/login.php');
    exit();
}

include '../../../config/db.php';
include '../../../config/auth.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_image'])) {
    $user_id = $_SESSION['user_id'];
    
    // Validate file
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($_FILES['profile_image']['type'], $allowed_types)) {
        $error = "Invalid file type. Please upload JPEG, PNG, GIF, or WebP images only.";
    } elseif ($_FILES['profile_image']['size'] > $max_size) {
        $error = "File too large. Please upload images smaller than 5MB.";
    } else {
        // Create uploads directory if it doesn't exist
        $upload_dir = 'uploads/profile_images/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Generate unique filename
        $file_extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
        $filename = 'user_' . $user_id . '_' . time() . '.' . $file_extension;
        $file_path = $upload_dir . $filename;
        
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $file_path)) {
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
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY unique_user (user_id),
                    FOREIGN KEY (user_id) REFERENCES users_login_signup(id) ON DELETE CASCADE
                )";
                $conn->query($create_table);
            }
            
            // Update database
            $sql = "INSERT INTO user_profiles (user_id, profile_picture) VALUES (?, ?) 
                    ON DUPLICATE KEY UPDATE profile_picture = ?";
            $stmt = $conn->prepare($sql);
            
            if ($stmt) {
                $stmt->bind_param("iss", $user_id, $file_path, $file_path);
                
                if ($stmt->execute()) {
                    // Update session
                    $_SESSION['profile_picture'] = $file_path;
                    
                    // Log the action
                    $auth->logAction('update_profile_picture', 'user_profiles', $user_id);
                    
                    $message = "Profile image updated successfully!";
                } else {
                    $error = "Database error occurred.";
                    unlink($file_path); // Remove uploaded file if DB update fails
                }
            } else {
                $error = "Failed to prepare database statement.";
                unlink($file_path);
            }
        } else {
            $error = "Failed to upload file.";
        }
    }
}

// Get current user profile
$user = getCurrentUser();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Upload Profile Image - Cantik Homemade</title>
    <link crossorigin="" href="https://fonts.gstatic.com/" rel="preconnect"/>
    <link as="style" href="https://fonts.googleapis.com/css2?display=swap&amp;family=Noto+Sans%3Awght%40400%3B500%3B700%3B900&amp;family=Public+Sans%3Awght%40400%3B500%3B600%3B700%3B800" onload="this.rel='stylesheet'" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
</head>
<body class="bg-gray-50 text-gray-900" style='font-family: "Public Sans", "Noto Sans", sans-serif;'>
    <div class="relative flex size-full min-h-screen flex-col overflow-x-hidden">
        <?php include '../../shared/nav.php'; ?>
        
        <main class="flex-1 px-4 sm:px-6 lg:px-8 py-8">
            <div class="max-w-2xl mx-auto">
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Upload Profile Image</h1>
                    <p class="text-gray-600 mt-2">Update your profile picture</p>
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

                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Current Profile Image</h2>
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
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Upload New Image</h2>
                    </div>
                    <form method="POST" enctype="multipart/form-data" class="p-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Select Image</label>
                                <input type="file" name="profile_image" accept="image/*" required 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                                <p class="mt-1 text-sm text-gray-500">JPEG, PNG, GIF, or WebP. Max 5MB.</p>
                            </div>
                            
                            <div class="flex gap-4">
                                <button type="submit" class="bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                    Upload Image
                                </button>
                                <a href="profile.php" class="bg-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                    Back to Profile
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
