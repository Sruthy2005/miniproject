<?php
require_once "../connect.php";
session_start();

// Debug log
error_log("Request received: " . json_encode($_GET));

// Check if user is logged in and is staff
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    error_log("Auth error: User ID = " . ($_SESSION['user_id'] ?? 'not set') . ", Role = " . ($_SESSION['role'] ?? 'not set'));
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

// Validate request
if (!isset($_GET['post_id']) || !is_numeric($_GET['post_id'])) {
    error_log("Invalid request: post_id = " . ($_GET['post_id'] ?? 'not set'));
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit();
}

$staff_id = $_SESSION['user_id'];
$post_id = intval($_GET['post_id']);

// First, verify this post belongs to the logged-in staff member
$post_check = $conn->prepare("SELECT id FROM staff_posts WHERE id = ? AND staff_id = ?");
$post_check->bind_param("ii", $post_id, $staff_id);
$post_check->execute();
$post_result = $post_check->get_result();

if ($post_result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Post not found or access denied']);
    exit();
}

// Get likes with user information
$likes_query = "SELECT pl.*, 
                CONCAT(u.first_name, ' ', u.last_name) AS name,
                u.profile_image,
                DATE_FORMAT(pl.created_at, '%M %d, %Y at %h:%i %p') AS liked_on
                FROM post_likes pl
                JOIN user u ON pl.user_id = u.id
                WHERE pl.post_id = ?
                ORDER BY pl.created_at DESC";

$stmt = $conn->prepare($likes_query);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();

$likes = [];
while ($row = $result->fetch_assoc()) {
    $likes[] = [
        'id' => $row['id'],
        'user_id' => $row['user_id'],
        'name' => $row['name'],
        'profile_image' => $row['profile_image'],
        'liked_on' => $row['liked_on']
    ];
}

echo json_encode([
    'status' => 'success',
    'likes' => $likes
]); 