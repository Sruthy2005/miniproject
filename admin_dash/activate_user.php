<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

// Database connection
require_once '../connect.php';

// CSRF Protection
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $_SESSION['error_message'] = "Invalid request";
    header("Location: manage_users.php");
    exit();
}

// Check if user_id is provided
if (!isset($_POST['user_id'])) {
    $_SESSION['error_message'] = "User ID is required";
    header("Location: manage_users.php");
    exit();
}

$user_id = mysqli_real_escape_string($conn, $_POST['user_id']);

// Update user status to active
$sql = "UPDATE user SET status = 'active' WHERE id = ? AND role = 'user' AND status != 'active'";
$stmt = mysqli_prepare($conn, $sql);

if ($stmt === false) {
    $_SESSION['error_message'] = "Error preparing statement: " . mysqli_error($conn);
    header("Location: manage_users.php");
    exit();
}

mysqli_stmt_bind_param($stmt, "i", $user_id);

if (mysqli_stmt_execute($stmt)) {
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        $_SESSION['success_message'] = "User has been activated successfully";
    } else {
        $_SESSION['error_message'] = "User not found or already active";
    }
} else {
    $_SESSION['error_message'] = "Error activating user";
}

mysqli_stmt_close($stmt);
mysqli_close($conn);

header("Location: manage_users.php");
exit();
?> 