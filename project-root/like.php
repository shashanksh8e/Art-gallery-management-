<?php
session_start();
require_once 'includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['success' => false, 'error' => 'Invalid JSON input']);
    exit;
}

if (!isset($input['post_id']) || !isset($input['type'])) {
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    exit;
}

$user_id = $_SESSION['user_id'];
$post_id = (int)$input['post_id'];
$type = $input['type']; // 'like' or 'dislike'

try {
    // Check if user already liked/disliked this post
    $stmt = $pdo->prepare("SELECT * FROM likes WHERE user_id = ? AND post_id = ?");
    $stmt->execute([$user_id, $post_id]);
    $existingLike = $stmt->fetch();

    if ($existingLike) {
        if ($existingLike['type'] === $type) {
            // User is clicking the same button again - remove the like/dislike
            $deleteStmt = $pdo->prepare("DELETE FROM likes WHERE user_id = ? AND post_id = ?");
            $deleteStmt->execute([$user_id, $post_id]);
            $user_like_type = null;
        } else {
            // User is changing from like to dislike or vice versa
            $updateStmt = $pdo->prepare("UPDATE likes SET type = ? WHERE user_id = ? AND post_id = ?");
            $updateStmt->execute([$type, $user_id, $post_id]);
            $user_like_type = $type;
        }
    } else {
        // New like/dislike
        $insertStmt = $pdo->prepare("INSERT INTO likes (user_id, post_id, type) VALUES (?, ?, ?)");
        $insertStmt->execute([$user_id, $post_id, $type]);
        $user_like_type = $type;
    }

    // Get updated counts
    $likeCount = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE post_id = ? AND type = 'like'");
    $likeCount->execute([$post_id]);
    $like_count = $likeCount->fetchColumn();

    $dislikeCount = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE post_id = ? AND type = 'dislike'");
    $dislikeCount->execute([$post_id]);
    $dislike_count = $dislikeCount->fetchColumn();

    echo json_encode([
        'success' => true,
        'like_count' => $like_count,
        'dislike_count' => $dislike_count,
        'user_like_type' => $user_like_type
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>