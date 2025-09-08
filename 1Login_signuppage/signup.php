<?php
session_start();
require_once 'db.php';

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signup'])) {
    $username = $_POST['username'];
    $email    = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // check if email already exists
    $check = $conn->prepare("SELECT id FROM users_login_signup WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $error = "An account with this email already exists.";
    } else {
        $sql = "INSERT INTO users_login_signup (username, email, password) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $username, $email, $password);

        if ($stmt->execute()) {
            $_SESSION['username'] = $username;
            header("Location: ../index.php");
            exit();
        } else {
            $error = "Something went wrong. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sign Up - PO Management</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="login.css?v=2">
</head>
<body>
  <div class="form-container">
    <div class="form-header">
      <img src="cantik_logo.png" alt="Cantik Logo" class="logo">
      <h2>Create Your Account</h2>
      <p>Join us today! It's quick and easy.</p>
    </div>

    <?php if (!empty($error)): ?>
      <div class="message error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="form-group">
        <label for="username">Full Name</label>
        <div class="input-wrapper">
          <input id="username" type="text" name="username" placeholder="Enter your full name" required>
        </div>
      </div>

      <div class="form-group">
        <label for="email">Email Address</label>
        <div class="input-wrapper">
          <input id="email" type="email" name="email" placeholder="you@example.com" required>
        </div>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <div class="input-wrapper">
          <input id="password" type="password" name="password" placeholder="Choose a strong password" required>
          <i class="fas fa-eye password-toggle" id="togglePassword"></i>
        </div>
      </div>

      <button class="btn-submit" type="submit" name="signup">Create Account</button>
    </form>

    <p class="bottom-link">
      Already have an account? 
      <a href="login.php">Login here</a>
    </p>
  </div>

  <script src="login.js"></script>
</body>
</html>