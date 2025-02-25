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

// Get the form values
$twoFactorAuth = isset($_POST['twoFactorAuth']) ? 1 : 0;
$loginNotifications = isset($_POST['loginNotifications']) ? 1 : 0;

// Connect to database
require_once '../connect.php';

// Update settings in database
$user_id = $_SESSION['user_id'];
$sql = "UPDATE user_settings 
        SET two_factor_auth = ?, login_notifications = ? 
        WHERE user_id = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "iii", $twoFactorAuth, $loginNotifications, $user_id);

if (mysqli_stmt_execute($stmt)) {
    // Update session variables
    $_SESSION['twoFactorAuth'] = $twoFactorAuth;
    $_SESSION['loginNotifications'] = $loginNotifications;
    
    // Set success message
    $_SESSION['settings_updated'] = true;
} else {
    // Set error message
    $_SESSION['settings_error'] = "Failed to update security settings: " . mysqli_error($conn);
}

// Close database connection
mysqli_stmt_close($stmt);
mysqli_close($conn);

// Redirect back to settings page
header("Location: settings.php");
exit(); 