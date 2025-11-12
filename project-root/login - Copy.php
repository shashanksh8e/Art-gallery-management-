<?php
session_start();
require 'includes/db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$username || !$password) {
        $errors[] = "All fields are required.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: profile.php");
            exit;
        } else {
            $errors[] = "Invalid username or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width" />
  <title>Login - Neon Social</title>
  <link rel="stylesheet" href="css/style.css" />
</head>
<body class="bg-black neon-text">
  <div class="form-container neon-glow">
    <h2>Login</h2>
    <?php if ($errors): ?>
      <div class="error neon-text">
        <?php foreach ($errors as $error): ?>
          <p><?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <form method="post" action="login.php">
      <label>Username: <input type="text" name="username" required /></label><br /><br />
      <label>Password: <input type="password" name="password" required /></label><br /><br />
      <button type="submit" class="btn-neon">Login</button>
    </form>
    <p>Don't have an account? <a href="register.php" class="neon-link">Register here</a></p>
  </div>
</body>
</html>
