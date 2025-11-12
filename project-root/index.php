<?php
session_start();
include __DIR__ . '/includes/db.php';
$is_logged_in = isset($_SESSION['user_id']);

$user_id = $_SESSION['user_id'] ?? null;  // null if not logged in

// Handle search input
$search = $_GET['search'] ?? '';
$search = trim($search);

$params = [$user_id];
$whereClause = '';

if ($search !== '') {
  // Filter posts where username or hashtags LIKE search
  $whereClause = "AND (u.username LIKE ? OR p.hashtags LIKE ?)";
  $searchParam = "%$search%";
  $params[] = $searchParam;
  $params[] = $searchParam;
}

// Fetch posts with poster username, profile pic, and counts
$sql = "
SELECT 
  p.*, 
  u.username, 
  u.profile_pic,
  (SELECT COUNT(*) FROM likes WHERE post_id = p.id AND type = 'like') AS like_count,
  (SELECT COUNT(*) FROM likes WHERE post_id = p.id AND type = 'dislike') AS dislike_count,
  (SELECT COUNT(*) FROM comments WHERE post_id = p.id) AS comment_count,
  (SELECT type FROM likes WHERE post_id = p.id AND user_id = ?) AS user_like_type
FROM posts p
JOIN users u ON p.user_id = u.id
WHERE 1=1
  $whereClause
ORDER BY p.created_at DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Main Feed</title>
  <script src="js/script.js"></script>
  <style>
    body {
         margin: 0;
            padding: 0;
            min-height: 100vh;
            font-family: Arial, sans-serif;
            color: white;
            background-color: black;
            <?php if (!$is_logged_in): ?>
            background-image: url('images/arthome.jpg');
            background-size: contain;
            background-color:#050C14;
            background-repeat: no-repeat;
            background-position: top center;
            background-attachment: fixed;
            <?php endif; ?>
        }
    header {
      background: #1e1e1e;
      padding: 10px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 2px solid #00ffd8;
    }
    header a {
      color: #00ffd8;
      text-decoration: none;
      font-weight: bold;
      margin-left: 15px;
    }
    .container {
      max-width: 900px;
      margin: 20px auto;
      padding: 0 15px;
    }
    .search-bar {
      margin-bottom: 20px;
    }
    .search-bar input[type="text"] {
      width: 100%;
      padding: 10px 15px;
      font-size: 1rem;
      border-radius: 25px;
      border: none;
      outline: none;
      box-sizing: border-box;
      background: #222;
      color: #eee;
      border: 1.5px solid #00ffd8;
      transition: border-color 0.3s ease;
    }
    .search-bar input[type="text"]:focus {
      border-color: #00bba3;
    }
    .post-card {
      background: #222;
      border-radius: 12px;
      margin-bottom: 20px;
      padding: 15px;
      color: #ccc;
    }
    .post-header {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 8px;
      font-weight: bold;
    }
    .post-header img.profile-pic {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid #00ffd8;
    }
    .post-header a.username {
      color: #00ffd8;
      text-decoration: none;
      font-size: 1.1rem;
    }
    .post-header .post-date {
      font-weight: normal;
      font-size: 0.85rem;
      color: #666;
    }
   .post-image {
  width: 100%;
    max-width: 500px;   /* or larger, like 800px */
    height: auto;
    display: block;
    margin: 20px auto;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0, 255, 255, 0.2); /
}


    
    .post-caption {
      margin-bottom: 5px;
      white-space: pre-line;
    }
    .hashtags {
      color: #00a3a3;
      margin-bottom: 10px;
      font-size: 0.9rem;
    }
    .post-actions {
      display: flex;
      align-items: center;
      gap: 15px;
      font-size: 0.9rem;
    }
    .post-actions button {
      background: transparent;
      border: none;
      color: #00ffd8;
      cursor: pointer;
      font-size: 1rem;
      display: flex;
      align-items: center;
      gap: 5px;
      padding: 5px 10px;
      border-radius: 6px;
      transition: background 0.2s ease;
    }
    .post-actions button:hover {
      background: #00bba3;
      color: #121212;
    }
    .post-actions button.liked {
      color: #4CAF50;
      font-weight: bold;
    }
    .post-actions button.disliked {
      color: #f44336;
      font-weight: bold;
    }
    .comments-link {
      color: #00ffd8;
      cursor: pointer;
      text-decoration: underline;
    }
  </style>
</head>
<body>

<header>
  
    <div style="font-size: 1.5rem; font-weight: bold; color: #00ffd8;">Art Gallery</div>
  <div><a href="profile.php">My Info</a></div>
  <div>
    <a href="upload.php">My Art</a>
    
  </div>
</header>

<div class="container">
  <form class="search-bar" method="GET" action="index.php" role="search" aria-label="Search posts" id="searchForm">
    <input type="text" name="search" id="searchInput" placeholder="Search by username or hashtag..." value="<?= htmlspecialchars($search) ?>" autocomplete="off" />
  </form>

  <div id="postsContainer">
    <?php if ($search !== '' && !$posts): ?>
      <p>No posts found.</p>
    <?php else: ?>
      <?php foreach ($posts as $post): ?>
        <div class="post-card" data-postid="<?= $post['id'] ?>">
          <div class="post-header">
        <?php
          $profileImage = 'images/post/' . $post['profile_pic'];
          if (!empty($post['profile_pic']) && file_exists($profileImage)):
        ?>
          <img src="<?= htmlspecialchars($profileImage) ?>" alt="Profile picture of <?= htmlspecialchars($post['username']) ?>" class="profile-pic" />
        <?php endif; ?>
        
        <a href="profile.php?user=<?= urlencode($post['username']) ?>" class="username"><?= htmlspecialchars($post['username']) ?></a>
        <span class="post-date"><?= htmlspecialchars(date('M d, Y', strtotime($post['created_at']))) ?></span>
      </div>

          <?php if ($post['image_path'] && file_exists($post['image_path'])): ?>
            <img class="post-image" src="<?= htmlspecialchars($post['image_path']) ?>" alt="Post image" />
          <?php endif; ?>
          <p class="post-caption"><?= nl2br(htmlspecialchars($post['caption'])) ?></p>
          <p class="hashtags"><?= htmlspecialchars($post['hashtags']) ?></p>

          <div class="post-actions">
            <button class="like-btn <?= $post['user_like_type'] === 'like' ? 'liked' : '' ?>" data-type="like" aria-label="Like">
              👍 <span class="like-count"><?= $post['like_count'] ?></span>
            </button>
            <button class="dislike-btn <?= $post['user_like_type'] === 'dislike' ? 'disliked' : '' ?>" data-type="dislike" aria-label="Dislike">
              👎 <span class="dislike-count"><?= $post['dislike_count'] ?></span>
            </button>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<script>
  const searchInput = document.getElementById('searchInput');
  const searchForm = document.getElementById('searchForm');

  // Listen for input to detect clearing
  searchInput.addEventListener('input', () => {
    if (searchInput.value.trim() === '') {
      // Redirect to index.php with no search param to reset page
      window.location.href = 'index.php';
    }
  });

  // Like/dislike button handlers
  document.querySelectorAll('.like-btn, .dislike-btn').forEach(button => {
    button.addEventListener('click', () => {
      const postCard = button.closest('.post-card');
      const postId = postCard.dataset.postid;
      const type = button.dataset.type;

      fetch('like.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ post_id: postId, type: type })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          const likeBtn = postCard.querySelector('.like-btn');
          const dislikeBtn = postCard.querySelector('.dislike-btn');
          likeBtn.querySelector('span').textContent = data.like_count;
          dislikeBtn.querySelector('span').textContent = data.dislike_count;

          if (data.user_like_type === 'like') {
            likeBtn.classList.add('liked');
            dislikeBtn.classList.remove('disliked');
          } else if (data.user_like_type === 'dislike') {
            dislikeBtn.classList.add('disliked');
            likeBtn.classList.remove('liked');
          } else {
            likeBtn.classList.remove('liked');
            dislikeBtn.classList.remove('disliked');
          }
        } else {
          alert('Error: ' + (data.error || 'Unknown error'));
        }
      })
      .catch(() => alert('Network error'));
    });
  });
</script>

</body>
</html>
