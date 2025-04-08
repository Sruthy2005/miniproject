<?php
require_once "connect.php";
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to like posts']);
    exit();
}

// Validate request
if (!isset($_POST['post_id']) || !is_numeric($_POST['post_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit();
}

$user_id = $_SESSION['user_id'];
$post_id = intval($_POST['post_id']);

// Check if post exists
$post_check = $conn->prepare("SELECT id FROM staff_posts WHERE id = ?");
$post_check->bind_param("i", $post_id);
$post_check->execute();
$post_result = $post_check->get_result();

if ($post_result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Post not found']);
    exit();
}

// Check if user already liked this post
$like_check = $conn->prepare("SELECT id FROM post_likes WHERE post_id = ? AND user_id = ?");
$like_check->bind_param("ii", $post_id, $user_id);
$like_check->execute();
$like_result = $like_check->get_result();

// Start transaction
$conn->begin_transaction();

try {
    if ($like_result->num_rows > 0) {
        // User already liked this post, so unlike it
        $unlike = $conn->prepare("DELETE FROM post_likes WHERE post_id = ? AND user_id = ?");
        $unlike->bind_param("ii", $post_id, $user_id);
        $unlike->execute();
        $action = 'unliked';
    } else {
        // User hasn't liked this post yet, so like it
        $like = $conn->prepare("INSERT INTO post_likes (post_id, user_id, created_at) VALUES (?, ?, NOW())");
        $like->bind_param("ii", $post_id, $user_id);
        $like->execute();
        $action = 'liked';
    }
    
    // Get updated like count
    $count = $conn->prepare("SELECT COUNT(*) as total FROM post_likes WHERE post_id = ?");
    $count->bind_param("i", $post_id);
    $count->execute();
    $count_result = $count->get_result();
    $likes = $count_result->fetch_assoc()['total'];
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'status' => 'success',
        'action' => $action,
        'likes' => $likes
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
} 