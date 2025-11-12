<?php
session_start();
require 'includes/db.php'; // your DB connection

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    header("Location: profile.php");
    exit();
}

$post_id = (int)$_GET['id'];




// Verify post belongs to user
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ?");
$stmt->execute([$post_id, $user_id]);
$post = $stmt->fetch();

if (!$post) {
    header("Location: profile.php");
    exit();
}

// Optionally delete image file from server
if ($post['image_path'] && file_exists($post['image_path'])) {
    unlink($post['image_path']);
}

$stmt = $pdo->prepare("DELETE FROM likes WHERE post_id = ?");
$stmt->execute([$post_id]);

// Now delete the post
$stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
$stmt->execute([$post_id]);
// Delete post
$delStmt = $pdo->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
$delStmt->execute([$post_id, $user_id]);

header("Location: profile.php");
exit();
?>
