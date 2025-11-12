<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$post_id) {
    header("Location: profile.php");
    exit();
}

// Fetch post
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ?");
$stmt->execute([$post_id, $user_id]);
$post = $stmt->fetch();

if (!$post) {
    header("Location: profile.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $caption = $_POST['caption'] ?? '';
    $hashtags = $_POST['hashtags'] ?? '';

    $updateStmt = $pdo->prepare("UPDATE posts SET caption = ?, hashtags = ? WHERE id = ? AND user_id = ?");
    $updateStmt->execute([$caption, $hashtags, $post_id, $user_id]);

    header("Location: profile.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Edit Post</title>
<style>
  body { background:#121212; color:#00ffd8; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
  .edit-form { max-width: 400px; margin: 40px auto; background:#222; padding: 20px; border-radius: 12px; }
  label { display:block; margin-top: 15px; }
  input[type="text"], textarea { width: 100%; padding: 8px; border-radius: 6px; border:none; }
  button { margin-top: 20px; background:#00ffd8; border:none; color:#121212; padding:10px 15px; border-radius: 8px; cursor: pointer; }
  a { color:#00ffd8; text-decoration:none; }
</style>
</head>
<body>

<div class="edit-form">
  <h2>Edit Post</h2>
  <form method="POST" action="">
    <label for="caption">Caption:</label>
    <textarea id="caption" name="caption" rows="3"><?= htmlspecialchars($post['caption']) ?></textarea>

    <label for="hashtags">Hashtags (comma separated):</label>
    <input type="text" id="hashtags" name="hashtags" value="<?= htmlspecialchars($post['hashtags']) ?>">

    <button type="submit">Save Changes</button>
  </form>
  <p><a href="profile.php">&larr; Back to Profile</a></p>
</div>

</body>
</html>
