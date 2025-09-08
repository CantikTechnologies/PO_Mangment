<?php
session_start();
require_once 'db.php';

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
            $_SESSION['username'] = $user['username'];
            header("Location: ../index.php");
            exit();
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
  <link rel="stylesheet" href="login.css?v=2"> 
</head>
<body>
  <div class="form-container">
    <div class="form-header">
      <img src="cantik_logo.png" alt="Cantik Logo" class="logo">
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

    <p class="bottom-link">
      Don't have an account? 
      <a href="signup.php">Sign up here</a>
    </p>
  </div>

  <script src="login.js"></script>
</body>
</html>