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
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['error_message'] = "CSRF token validation failed";
    header("Location: manage_staff.php");
    exit();
}

// Check if staff_id is provided
if (!isset($_POST['staff_id'])) {
    $_SESSION['error_message'] = "Staff ID is required";
    header("Location: manage_staff.php");
    exit();
}

$staff_id = mysqli_real_escape_string($conn, $_POST['staff_id']);

// Update staff status to inactive
$sql = "UPDATE user SET status = 'inactive' WHERE id = '$staff_id' AND role = 'staff' AND status = 'active'";

if (mysqli_query($conn, $sql)) {
    if (mysqli_affected_rows($conn) > 0) {
        $_SESSION['success_message'] = "Staff member has been deactivated successfully";
    } else {
        $_SESSION['error_message'] = "Staff member not found or already deactivated";
    }
} else {
    $_SESSION['error_message'] = "Error deactivating staff member: " . mysqli_error($conn);
}

// Close database connection
mysqli_close($conn);

// Redirect back to manage staff page
header("Location: manage_staff.php");
exit(); 