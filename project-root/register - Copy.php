<?php
session_start();
require 'includes/db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    // Basic validation
    if (!$username || !$email || !$password || !$password_confirm) {
        $errors[] = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email address.";
    } elseif ($password !== $password_confirm) {
        $errors[] = "Passwords do not match.";
    } else {
        // Check if username or email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $errors[] = "Username or email already taken.";
        }
    }

    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
        if ($stmt->execute([$username, $email, $password_hash])) {
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['username'] = $username;
            header("Location: index.php");
            exit;
        } else {
            $errors[] = "Registration failed, please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width" />
  <title>Register - Neon Social</title>
  <link rel="stylesheet" href="css/style.css" />
</head>
<body class="bg-black neon-text">
  <div class="form-container neon-glow">
    <h2>Register</h2>
    <?php if ($errors): ?>
      <div class="error neon-text">
        <?php foreach ($errors as $error): ?>
          <p><?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <form method="post" action="register.php">
      <label>Username: <input type="text" name="username" required /></label><br /><br />
      <label>Email: <input type="email" name="email" required /></label><br /><br />
      <label>Password: <input type="password" name="password" required /></label><br /><br />
      <label>Confirm Password: <input type="password" name="password_confirm" required /></label><br /><br />
      <button type="submit" class="btn-neon">Register</button>
    </form>
    <p>Already have an account? <a href="login.php" class="neon-link">Login here</a></p>
  </div>
</body>
</html>
