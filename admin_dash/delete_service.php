<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

// Validate CSRF token
if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || 
    $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("Invalid request");
}

// Check if service_id is provided
if (!isset($_POST['service_id']) || !is_numeric($_POST['service_id'])) {
    die("Invalid service ID");
}

require_once '../connect.php';

$service_id = mysqli_real_escape_string($conn, $_POST['service_id']);

// First get the image path
$sql = "SELECT image_path FROM service WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $service_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    // Delete the image file if it exists
    if (!empty($row['image_path']) && file_exists("../" . $row['image_path'])) {
        unlink("../" . $row['image_path']);
    }
}

// Delete the service from database
$sql = "DELETE FROM service WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $service_id);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['success_msg'] = "Service deleted successfully";
} else {
    $_SESSION['error_msg'] = "Error deleting service: " . mysqli_error($conn);
}

mysqli_close($conn);
header("Location: manage_services.php");
exit(); 