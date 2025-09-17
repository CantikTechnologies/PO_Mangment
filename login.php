<?php
// Use universal includes
include 'src/shared/includes.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users_login_signup WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Check if user is active
            if (!$user['is_active']) {
                $error = "Your account has been deactivated. Please contact administrator.";
            } else {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['department'] = $user['department'];
                
                // Update last login
                $update_sql = "UPDATE users_login_signup SET last_login = NOW() WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("i", $user['id']);
                $update_stmt->execute();
                
                // Log login action
                $auth->logAction('login');
                
                header("Location: index.php");
                exit();
            }
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "No user found with that email address.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login - PO Management</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="assets/login.css"> 
</head>
<body>
  <div class="form-container">
    <div class="form-header">
      <img src="assets/cantik_logo.png" alt="Cantik Logo" class="logo">
      <h2>Welcome Back!</h2>
      <p>Please enter your credentials to log in.</p>
    </div>

    <?php if (!empty($error)): ?>
      <div class="message error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="form-group">
        <label for="email">Email Address</label>
        <div class="input-wrapper">
          <input id="email" type="email" name="email" placeholder="you@example.com" required>
        </div>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <div class="input-wrapper">
          <input id="password" type="password" name="password" placeholder="Enter your password" required>
          <i class="fas fa-eye password-toggle" id="togglePassword"></i>
        </div>
      </div>

      <button class="btn-submit" type="submit" name="login">Login</button>
    </form>

  </div>

  <script src="assets/login.js"></script>
</body>
</html>