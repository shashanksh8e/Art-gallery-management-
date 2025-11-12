<?php
session_start();
include __DIR__ . '/includes/db.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT username, bio, profile_picture FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $bio = trim($_POST['bio']);
    $profile_picture = $user['profile_picture'];

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['profile_picture']['tmp_name'];
        $fileName = $_FILES['profile_picture']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($fileExtension, $allowedExts)) {
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $uploadFileDir = './images/profile_pictures/';
            if (!is_dir($uploadFileDir)) mkdir($uploadFileDir, 0755, true);
            $dest_path = $uploadFileDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $profile_picture = $dest_path;
            } else {
                $error = "Error uploading file.";
            }
        } else {
            $error = "Allowed types: " . implode(", ", $allowedExts);
        }
    }

    if (!isset($error)) {
        $updateStmt = $pdo->prepare("UPDATE users SET username = ?, bio = ?, profile_picture = ? WHERE id = ?");
        $updateStmt->execute([$username, $bio, $profile_picture, $user_id]);
        header("Location: profile.php?updated=1");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Your Profile</title>
  <style>
    * {
      box-sizing: border-box;
    }
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #121212;
      color: #ddd;
    }
    a {
      text-decoration: none;
    }
    .page-container {
      display: flex;
      min-height: 100vh;
    }
    .profile-info {
      flex: 0 0 22%;
      background: #1e1e1e;
      padding: 30px 20px;
      border-right: 2px solid #00ffd8;
      display: flex;
      flex-direction: column;
      align-items: center;
      position: relative;
    }
    .profile-pic-container {
      position: relative;
      margin-bottom: 25px;
    }
    .profile-pic {
      width: 140px;
      height: 140px;
      border-radius: 50%;
      border: 3px solid #00ffd8;
      object-fit: cover;
      background: #333;
    }
    .edit-icon {
      position: absolute;
      bottom: 0;
      right: 0;
      background: #00ffd8;
      border-radius: 50%;
      padding: 6px;
      cursor: pointer;
    }
    .edit-icon svg {
      width: 20px;
      height: 20px;
      fill: #121212;
    }
    input[type="file"] {
      display: none;
    }
    label {
      font-weight: 600;
      font-size: 0.9rem;
      margin-top: 15px;
      color: #00ffd8;
      align-self: flex-start;
    }
    input[type="text"], textarea {
      width: 100%;
      padding: 8px 12px;
      border-radius: 6px;
      border: none;
      background: #2a2a2a;
      color: #ddd;
      margin-top: 5px;
      font-size: 1rem;
      resize: vertical;
    }
    textarea {
      min-height: 100px;
    }
    button.save-btn {
      margin-top: 25px;
      width: 100%;
      background: #00ffd8;
      border: none;
      color: #121212;
      padding: 12px;
      font-weight: bold;
      border-radius: 8px;
      cursor: pointer;
      font-size: 1.1rem;
      transition: background 0.3s ease;
    }
    button.save-btn:hover {
      background: #00bba3;
    }
    .posts-section {
      flex: 1;
      background: #181818;
      padding: 30px 40px;
      overflow-y: auto;
    }
    .posts-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }
    .posts-header h2 {
      margin: 0;
      color: #00ffd8;
    }
    .create-post-btn {
      background: #00ffd8;
      color: #121212;
      padding: 12px 20px;
      border-radius: 8px;
      font-weight: 700;
      cursor: pointer;
      border: none;
      font-size: 1rem;
      transition: background 0.3s ease;
    }
    .create-post-btn:hover {
      background: #00bba3;
    }
    .post-card {
      background: #222;
      padding: 15px;
      border-radius: 12px;
      margin-bottom: 20px;
      color: #ccc;
    }
    .post-card img {
      max-width: 100%;
      border-radius: 8px;
      margin-bottom: 10px;
    }
    .post-caption {
      font-size: 1rem;
    }
    .message {
      text-align: center;
      margin-bottom: 20px;
      font-weight: bold;
      font-size: 1rem;
    }
    .success {
      color: #4CAF50;
    }
    .error {
      color: #f44336;
    }
    @media (max-width: 900px) {
      .page-container {
        flex-direction: column;
      }
      .profile-info {
        flex: none;
        width: 100%;
        border-right: none;
        border-bottom: 2px solid #00ffd8;
        padding-bottom: 40px;
      }
      .posts-section {
        padding: 20px;
      }
    }
    .post-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 20px;
  }
  .post-card {
    position: relative;
    background: #222;
    padding: 10px;
    border-radius: 12px;
    color: #ccc;
    transition: transform 0.2s ease;
  }
  .post-card:hover {
    transform: scale(1.02);
  }
  .post-card img {
    width: 100%;
    border-radius: 8px;
    margin-bottom: 10px;
  }
  .post-caption {
    margin-bottom: 5px;
  }
  .post-menu {
    position: absolute;
    top: 10px;
    right: 10px;
  }
  .menu-btn {
    background: transparent;
    border: none;
    color: black;
    font-size: 18px;
    cursor: pointer;
  }
  .dropdown-menu {
    display: none;
    position: absolute;
    top: 20px;
    right: 0;
    background: #333;
    border: 1px solid #00ffd8;
    border-radius: 6px;
    overflow: hidden;
    z-index: 10;
  }
  .dropdown-menu a {
    display: block;
    padding: 8px 12px;
    color: #00ffd8;
    text-decoration: none;
    font-size: 0.9rem;
  }
  .dropdown-menu a:hover {
    background: #00ffd8;
    color: #121212;
  }
  .post-menu:hover .dropdown-menu {
    display: block;
  }
  .logout-link {
  display: block;       /* Makes the link take full width and appear on its own line */
  margin-top: 10px;     /* Adds space above the logout link */
  color: red;           /* Red text color */
  font-weight: bold;    /* Optional: make it bold */
  text-decoration: none; /* Optional: remove underline */
}

.logout-link:hover {
  text-decoration: underline; /* underline on hover */
  cursor: pointer;
}

  </style>
</head>
<body>

<div class="page-container">
  
  <form class="profile-info" method="post" enctype="multipart/form-data" autocomplete="off" novalidate>
    
    <div class="profile-pic-container">
      <img class="profile-pic" src="<?= $user['profile_picture'] ? htmlspecialchars($user['profile_picture']) : 'https://via.placeholder.com/140?text=No+Image' ?>" alt="Profile Picture" />
      <label for="profile_picture" class="edit-icon">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04a1.003 1.003 0 0 0 0-1.41l-2.34-2.34a1.003 1.003 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
      </label>
      
      <input type="file" id="profile_picture" name="profile_picture" accept="image/*" />
    </div>

    <label for="username">Username</label>
    <input type="text" id="username" name="username" required value="<?= htmlspecialchars($user['username']) ?>" />

    <label for="bio">Bio</label>
    <textarea id="bio" name="bio"><?= htmlspecialchars($user['bio']) ?></textarea>

    <button class="save-btn" type="submit">Save Changes</button>

    <?php if (isset($_GET['updated'])): ?>
      <div class="message success">Profile updated successfully!</div>
    <?php endif; ?>
 <a href="logout.php" class="logout-link">Logout</a>
<a href="index.php" style="color:#00ffd8; margin-top: 20px; display: block;">← Back to Home</a>
    <?php if (isset($error)): ?>
      <div class="message error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
  </form>

<section class="posts-section">
  <div class="posts-header">
    <h2>Your Posts</h2>
    
    <a href="upload.php" class="create-post-btn">+ Create Post</a>
    

  </div>

  <div class="post-grid">
    <?php
    $postStmt = $pdo->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC");
    $postStmt->execute([$user_id]);
    $posts = $postStmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$posts) {
      echo "<p style='color:#777;'>No posts yet. Create your first post!</p>";
    } else {
      foreach ($posts as $post): ?>
        <div class="post-card">
          <div class="post-menu">
            <button class="menu-btn">⋮</button>
            <div class="dropdown-menu">
              <a href="edit_post.php?id=<?= $post['id'] ?>">Edit</a>
              <a href="delete_post.php?id=<?= $post['id'] ?>" onclick="return confirm('Delete this post?')">Delete</a>
            </div>
          </div>
          <?php if ($post['image_path']): ?>
            <img src="<?= htmlspecialchars($post['image_path']) ?>" alt="Post Image" />
          <?php endif; ?>
          <p class="post-caption"><?= htmlspecialchars($post['caption']) ?></p>
          <p style="font-size: 0.8rem; color: #888;"><?= htmlspecialchars($post['hashtags']) ?></p>
        </div>
      <?php endforeach;
    }
    ?>
  </div>
</section>


</div>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const successMsg = document.querySelector('.message.success');
    if (successMsg) {
      setTimeout(() => {
        successMsg.style.display = 'none';
      }, 2000);
    }
  });
</script>
</body>
</html>
