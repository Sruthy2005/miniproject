<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('CSRF token validation failed');
}

// Check if user_id is provided
if (!isset($_POST['user_id'])) {
    die('User ID is required');
}

require_once '../connect.php';

// Sanitize input
$user_id = mysqli_real_escape_string($conn, $_POST['user_id']);

// Update user status to inactive
$sql = "UPDATE user SET status = 'inactive' WHERE id = '$user_id' AND role = 'user'";

if (mysqli_query($conn, $sql)) {
    header("Location: manage_users.php");
} else {
    die("Error updating user: " . mysqli_error($conn));
}

mysqli_close($conn);
exit(); 