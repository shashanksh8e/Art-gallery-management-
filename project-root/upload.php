<?php
session_start();
include __DIR__ . '/includes/db.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $caption = trim($_POST['caption']);
  $hashtags = trim($_POST['hashtags']);
  $imagePath = '';

  if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $fileTmp = $_FILES['image']['tmp_name'];
    $fileName = $_FILES['image']['name'];
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($ext, $allowed)) {
      $newName = md5(time() . $fileName) . '.' . $ext;
      $uploadDir = './images/posts/';
      if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
      $fullPath = $uploadDir . $newName;

      if (move_uploaded_file($fileTmp, $fullPath)) {
        $imagePath = $fullPath;
      } else {
        $error = "Failed to upload image.";
      }
    } else {
      $error = "Allowed image types: jpg, jpeg, png, gif.";
    }
  }

  if (!$error && $imagePath) {
    $stmt = $pdo->prepare("INSERT INTO posts (user_id, image_path, caption, hashtags) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $imagePath, $caption, $hashtags]);
    $success = "Post uploaded successfully!";
    header("Location: profile.php");
exit();
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Create Post</title>
  <style>
    body {
      background: #121212;
      color: #ddd;
      font-family: Arial, sans-serif;
      display: flex;
      justify-content: center;
      padding: 60px;
    }
    .form-container {
      background: #1e1e1e;
      border: 2px solid #00ffd8;
      padding: 30px;
      border-radius: 12px;
      width: 100%;
      max-width: 500px;
    }
    input[type="file"],
    input[type="text"],
    textarea {
      width: 100%;
      margin-bottom: 15px;
      padding: 10px;
      border-radius: 8px;
      border: none;
      background: #2a2a2a;
      color: #ddd;
      font-size: 1rem;
    }
    button {
      background: #00ffd8;
      color: #121212;
      border: none;
      padding: 12px;
      font-weight: bold;
      border-radius: 8px;
      width: 100%;
      font-size: 1.1rem;
      cursor: pointer;
    }
    button:hover {
      background: #00bba3;
    }
    .message {
      text-align: center;
      margin-bottom: 15px;
      font-weight: bold;
    }
    .success { color: #4CAF50; }
    .error { color: #f44336; }
  </style>
</head>
<body>
  <form class="form-container" method="post" enctype="multipart/form-data">
    <h2 style="color: #00ffd8;">Create a Post</h2>
    <?php if ($success): ?>
      <div class="message success"><?= $success ?></div>
    <?php elseif ($error): ?>
      <div class="message error"><?= $error ?></div>
    <?php endif; ?>

    <label>Choose Image</label>
    <input type="file" name="image" required accept="image/*">

    <label>Caption</label>
    <textarea name="caption" placeholder="Write a caption..." required></textarea>

    <label>Hashtags</label>
    <input type="text" name="hashtags" placeholder="#example #fun #cool" />

    <button type="submit">Upload Post</button>
  </form>
</body>
</html>
