<?php
session_start();
include __DIR__ . '/includes/db.php'; // Adjust path if needed

// Hardcoded admin credentials
define('ADMIN_USERNAME', 'adminuser');
define('ADMIN_PASSWORD', 'adminpass');

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin_panel.php');
    exit;
}

// Handle admin login form submission
if (isset($_POST['admin_login'])) {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
        $_SESSION['is_admin'] = true;
        header('Location: admin_panel.php');
        exit;
    } else {
        $login_error = "Invalid admin username or password.";
    }
}

// Redirect to login if not admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    // Show login form below
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Admin Login</title>
        <style>
            :root {
                --bg-color: #121212;
                --text-color: #ddd;
                --primary-color: #00ffd8;
                --primary-color-dark: #00bfa1;
                --error-color: #f44336;
                --table-header-bg: #222;
                --table-row-even-bg: #1e1e1e;
                --table-border-color: #333;
                --button-bg-gradient-start: #00ffd8;
                --button-bg-gradient-end: #00bfa1;
                --button-hover-gradient-start: #00bfa1;
                --button-hover-gradient-end: #00ffd8;
                --font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }

            *,
            *::before,
            *::after {
                box-sizing: border-box;
            }

            body {
                background-color: var(--bg-color);
                color: var(--text-color);
                font-family: var(--font-family);
                margin: 0;
                padding: 20px;
                min-height: 100vh;
                display: flex;
                justify-content: center;
                align-items: center;
                transition: background-color 0.3s ease, color 0.3s ease;
            }

            form {
                background: #222;
                padding: 30px;
                border-radius: 10px;
                width: 320px;
                box-sizing: border-box;
                box-shadow: 0 0 15px rgba(0, 255, 216, 0.25);
            }

            input[type="text"], input[type="password"] {
                width: 100%;
                padding: 10px;
                margin: 10px 0;
                border-radius: 5px;
                border: none;
                background: #333;
                color: #ddd;
                box-sizing: border-box;
                font-size: 1rem;
                transition: background 0.3s ease;
            }
            input[type="text"]:focus, input[type="password"]:focus {
                background: #444;
                outline: none;
            }

            button {
                background: linear-gradient(135deg, var(--button-bg-gradient-start), var(--button-bg-gradient-end));
                border: none;
                padding: 12px 0;
                width: 100%;
                border-radius: 8px;
                color: var(--bg-color);
                font-weight: 700;
                font-size: 1.1rem;
                cursor: pointer;
                user-select: none;
                box-shadow: 0 4px 15px rgba(0, 255, 216, 0.6);
                transition: background 0.3s ease, transform 0.2s ease;
            }

            button:hover {
                background: linear-gradient(135deg, var(--button-hover-gradient-start), var(--button-hover-gradient-end));
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(0, 255, 216, 0.8);
            }

            h2 {
                color: var(--primary-color);
                text-align: center;
                margin-bottom: 20px;
                letter-spacing: 1.5px;
                text-shadow: 0 0 6px var(--primary-color);
            }

            .error {
                color: var(--error-color);
                font-weight: 700;
                margin-bottom: 15px;
                text-align: center;
                text-shadow: 0 0 6px var(--error-color);
            }
        </style>
    </head>
    <body>
        <form method="post" action="admin_panel.php" autocomplete="off">
            <h2>Admin Login</h2>
            <?php if (isset($login_error)): ?>
                <div class="error"><?php echo htmlspecialchars($login_error); ?></div>
            <?php endif; ?>
            <input type="text" name="username" placeholder="Admin Username" required autofocus />
            <input type="password" name="password" placeholder="Password" required />
            <button type="submit" name="admin_login">Login</button>
        </form>
    </body>
    </html>
    <?php
    exit;
}

// ---------------------
// If here, admin is logged in
// ---------------------

// Handle delete user request
if (isset($_GET['delete_user'])) {
    $userId = $_GET['delete_user'];

    // 1. Get all posts by the user
    $stmt = $pdo->prepare("SELECT id FROM posts WHERE user_id = ?");
    $stmt->execute([$userId]);
    $posts = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if ($posts) {
        // 2. Delete likes associated with user's posts
        $inQuery = implode(',', array_fill(0, count($posts), '?'));
        $stmt = $pdo->prepare("DELETE FROM likes WHERE post_id IN ($inQuery)");
        $stmt->execute($posts);

        // 3. Delete comments (if any)
        $stmt = $pdo->prepare("DELETE FROM comments WHERE post_id IN ($inQuery)");
        $stmt->execute($posts);

        // 4. Delete posts
        $stmt = $pdo->prepare("DELETE FROM posts WHERE user_id = ?");
        $stmt->execute([$userId]);
    }

    // 5. Finally, delete the user
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$userId]);

    header("Location: admin_panel.php");
    exit();
}


// Handle delete post request
if (isset($_GET['delete_post'])) {
    $delete_post_id = (int)$_GET['delete_post'];

    // Delete likes related to this post first if foreign keys prevent deletion
    $delLikes = $pdo->prepare("DELETE FROM likes WHERE post_id = ?");
    $delLikes->execute([$delete_post_id]);

    // Delete the post
    $delPost = $pdo->prepare("DELETE FROM posts WHERE id = ?");
    $delPost->execute([$delete_post_id]);

    header('Location: admin_panel.php');
    exit;
}

// Fetch all users
$userStmt = $pdo->query("SELECT id, username, bio FROM users ORDER BY id DESC");
$users = $userStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all posts with username of uploader
$postStmt = $pdo->query("SELECT posts.id, posts.caption, posts.image_path, posts.created_at, users.username FROM posts JOIN users ON posts.user_id = users.id ORDER BY posts.created_at DESC");
$posts = $postStmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Admin Panel</title>
<style>
    :root {
        --bg-color: #121212;
        --text-color: #ddd;
        --primary-color: #00ffd8;
        --primary-color-dark: #00bfa1;
        --error-color: #f44336;
        --table-header-bg: #222;
        --table-row-even-bg: #1e1e1e;
        --table-border-color: #333;
        --button-bg-gradient-start: #00ffd8;
        --button-bg-gradient-end: #00bfa1;
        --button-hover-gradient-start: #00bfa1;
        --button-hover-gradient-end: #00ffd8;
        --font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    *,
    *::before,
    *::after {
        box-sizing: border-box;
    }

    body {
        background-color: var(--bg-color);
        color: var(--text-color);
        font-family: var(--font-family);
        margin: 0;
        padding: 20px;
        min-height: 100vh;
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    a {
        color: var(--primary-color);
        text-decoration: none;
        transition: color 0.3s ease;
    }

    a:hover {
        text-decoration: underline;
        color: var(--primary-color-dark);
    }

    h1, h2 {
        color: var(--primary-color);
        font-weight: 700;
        margin-top: 0;
        letter-spacing: 1.5px;
        text-shadow: 0 0 6px var(--primary-color);
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
    }

    .logout {
        float: right;
        margin-top: -40px;
        font-weight: 700;
        color: var(--error-color);
        cursor: pointer;
        user-select: none;
        transition: color 0.3s ease;
    }

    .logout:hover {
        color: #ff7961;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 40px;
        box-shadow: 0 0 10px rgba(0, 255, 216, 0.15);
        border-radius: 8px;
        overflow: hidden;
    }

    th, td {
        border: 1px solid var(--table-border-color);
        padding: 12px 15px;
        text-align: left;
        vertical-align: middle;
        transition: background-color 0.25s ease;
    }

    th {
        background-color: var(--table-header-bg);
        font-weight: 700;
        letter-spacing: 1px;
    }

    tr:nth-child(even) {
        background-color: var(--table-row-even-bg);
    }

    tr:hover {
        background-color: rgba(0, 255, 216, 0.1);
    }

    .delete-link {
        color: var(--error-color);
        font-weight: 700;
        cursor: pointer;
        user-select: none;
        transition: color 0.3s ease;
    }

    .delete-link:hover {
        text-decoration: underline;
        color: #ff7961;
    }

    img.post-image {
        max-width: 100px;
        border-radius: 6px;
        box-shadow: 0 0 10px var(--primary-color);
        transition: transform 0.3s ease;
        cursor: pointer;
    }

    img.post-image:hover {
        transform: scale(1.05);
    }
</style>
</head>
<body>

<div class="container">
    <h1>Admin Panel</h1>
    <a href="admin_panel.php?logout=1" class="logout">Logout</a>

    <section>
        <h2>Registered Users</h2>
        <?php if (count($users) === 0): ?>
            <p>No registered users found.</p>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Bio</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['bio']); ?></td>
                        <td>
                            <a class="delete-link" href="admin_panel.php?delete_user=<?php echo (int)$user['id']; ?>" onclick="return confirm('Delete user <?php echo htmlspecialchars($user['username']); ?>? This will delete their posts too.')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </section>

    <section>
        <h2>Uploaded Posts</h2>
        <?php if (count($posts) === 0): ?>
            <p>No posts uploaded yet.</p>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Uploader</th>
                    <th>Caption</th>
                    <th>Image</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($posts as $post): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($post['id']); ?></td>
                        <td><?php echo htmlspecialchars($post['username']); ?></td>
                        <td><?php echo htmlspecialchars($post['caption']); ?></td>
                        <td>
                            <?php if ($post['image_path']): ?>
                                <img class="post-image" src="<?php echo htmlspecialchars($post['image_path']); ?>" alt="Post Image" />
                            <?php else: ?>
                                No image
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($post['created_at']); ?></td>
                        <td>
                            <a class="delete-link" href="admin_panel.php?delete_post=<?php echo (int)$post['id']; ?>" onclick="return confirm('Delete this post?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </section>
</div>

</body>
</html>
