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
  <style>
    /* Dark split neon layout */
    * { margin:0; padding:0; box-sizing:border-box; }
    html, body { margin:0; padding:0; min-height:100vh; font-family: "Public Sans", "Noto Sans", system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; color:#e5e7eb; overflow-x:hidden; }
    .shell { display:grid; grid-template-columns: minmax(340px, 560px) 1fr; height:100vh; background:#0d1028; position:fixed; top:0; left:0; right:0; bottom:0; }
    .left { padding:40px 36px; background:#12163a; box-shadow: inset -8px 0 24px rgba(0,0,0,.35); position:relative; width:100%; }
    .brand { display:flex; align-items:center; gap:10px; margin-bottom:36px; background:#ffffff; padding:16px 20px; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,.25); }
    .brand img { height:64px; width:auto; max-width:100%; filter: none; }
    .brand span { font-weight:800; letter-spacing:.02em; color:#1f2937; }
    .panel { max-width: 520px; width:100%; }
    .avatar { height:72px; width:72px; border-radius:50%; display:grid; place-items:center; color:#7dd3fc; border:2px solid #7dd3fc33; margin-bottom:18px; }
    .title { font-weight:900; font-size:22px; letter-spacing:.02em; margin:0 0 4px; color:#ffffff; }
    .sub { color:#b8c0ff; margin:0 0 14px; font-size:13px; }
    .label { font-size:12px; color:#cbd5e1; margin:10px 0 6px; display:block; }
    .field { position:relative; }
    .input { width:100%; padding:12px 14px 12px 40px; border-radius:999px; border:1px solid #334155; background:#0b0f2a; color:#e5e7eb; outline:none; transition:.2s; }
    .input::placeholder{ color:#64748b; }
    .input:focus { border-color:#7c3aed; box-shadow:0 0 0 3px #7c3aed33; }
    .icon { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#64748b; }
    .btn { width:100%; border:none; cursor:pointer; margin-top:14px; padding:12px 14px; border-radius:999px; font-weight:800; letter-spacing:.03em; color:#fff; background:linear-gradient(135deg,#ff2ea6,#7353ff); box-shadow:0 10px 24px rgba(115,83,255,.35); transition:.2s; }
    .btn:hover { transform: translateY(-1px); box-shadow:0 14px 28px rgba(115,83,255,.45); }
    .meta { display:flex; justify-content:space-between; margin-top:10px; font-size:12px; color:#9aa5b1; }
    .right { position:relative; overflow:hidden; display:grid; place-items:center; min-height:100vh; }
    .wave { position:absolute; inset:0; background: radial-gradient(800px 400px at 30% 40%, rgba(162,0,255,.35), transparent 60%),
                                 radial-gradient(700px 360px at 60% 60%, rgba(0,200,255,.25), transparent 60%),
                                 radial-gradient(600px 320px at 70% 30%, rgba(255,0,150,.25), transparent 60%),
                                 linear-gradient(180deg,#0b0f2a,#0d1028 60%);
            filter: blur(6px);
    }
    .welcome { position:relative; z-index:1; max-width:680px; padding:48px; color:#e6e7ff; text-align:left; }
    .welcome h1 { font-size:48px; margin:0 0 12px; font-weight:900; letter-spacing:.01em; }
    .welcome p { color:#b8c0ff; line-height:1.6; margin:0; }
    @media (max-width: 1200px){ .shell{ grid-template-columns: minmax(320px, 480px) 1fr; } }
    @media (max-width: 900px){ 
        .shell{ grid-template-columns: 1fr; }
        .right{ display:grid; min-height: 40vh; }
        .brand img{ height:48px; }
        .panel{ max-width: 92vw; }
        .left{ padding:28px 20px; }
        .welcome{ padding:28px 20px; }
        .welcome h1{ font-size:34px; }
    }
    .alert-error{ background:#3f1a27; border:1px solid #ef444455; color:#fecdd3; padding:10px 12px; border-radius:12px; margin-bottom:10px; font-size:13px; }
  </style>
</head>
<body>
  <style>
  </style>

  <div class="shell">
    <div class="left">
      <div class="brand">
        <img src="assets/cantik_logo.png" alt="Cantik Logo">
        <span>PO Management</span>
      </div>
      <?php if (!empty($error)): ?>
        <div class="alert-error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <div class="panel">
        <div class="avatar"><i class="fas fa-user-shield"></i></div>
        <h2 class="title">Welcome back</h2>
        <p class="sub">Please sign in to continue</p>
        <form method="POST" action="">
          <label for="email" class="label">Email address</label>
          <div class="field">
            <i class="fas fa-envelope icon"></i>
            <input id="email" type="email" name="email" placeholder="you@example.com" class="input" required>
          </div>
          <label for="password" class="label">Password</label>
          <div class="field">
            <i class="fas fa-lock icon"></i>
            <input id="password" type="password" name="password" placeholder="Enter your password" class="input" required>
          </div>
          <button class="btn" type="submit" name="login"><i class="fas fa-sign-in-alt" style="margin-right:8px;"></i>Sign In</button>
        </form>
        <div class="meta">
          <span></span>
          <span></span>
        </div>
      </div>
    </div>
    <div class="right">
      <div class="wave"></div>
      <div class="welcome">
        <h1>Track Revenue with Clarity</h1>
        <p>Manage POs, invoices, and outsourcing seamlessly. Fast, secure, and delightful to use.</p>
      </div>
    </div>
  </div>

  <script src="assets/login.js"></script>
</body>
</html>