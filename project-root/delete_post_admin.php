<?php
session_start();
include __DIR__ . '/includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Check if user is admin
$checkAdmin = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
$checkAdmin->execute([$_SESSION['user_id']]);
$user = $checkAdmin->fetch(PDO::FETCH_ASSOC);

if (!$user || $user['is_admin'] != 1) {
    echo "Unauthorized action.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_id'])) {
    $post_id = $_POST['post_id'];

    // Optionally delete the image file from server here (if desired)

    $deleteStmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
    $deleteStmt->execute([$post_id]);

    header("Location: admin_panel.php");
    exit;
}
?>
