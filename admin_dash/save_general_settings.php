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
$siteName = trim($_POST['siteName']);
$siteEmail = trim($_POST['siteEmail']);
$sitePhone = trim($_POST['sitePhone']);
$businessHours = trim($_POST['businessHours']);

// Validate inputs
if (empty($siteName) || empty($siteEmail) || empty($sitePhone)) {
    $_SESSION['settings_error'] = "All fields are required";
    header("Location: settings.php");
    exit();
}

if (!filter_var($siteEmail, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['settings_error'] = "Invalid email format";
    header("Location: settings.php");
    exit();
}

// Connect to database
require_once '../connect.php';

// Prepare the SQL statement
$sql = "INSERT INTO site_settings (site_name, contact_email, contact_phone, business_hours) 
        VALUES (?, ?, ?, ?) 
        ON DUPLICATE KEY UPDATE 
        site_name = VALUES(site_name),
        contact_email = VALUES(contact_email),
        contact_phone = VALUES(contact_phone),
        business_hours = VALUES(business_hours)";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ssss", 
    $siteName,
    $siteEmail,
    $sitePhone,
    $businessHours
);

if (mysqli_stmt_execute($stmt)) {
    // Update session variables
    $_SESSION['siteName'] = $siteName;
    $_SESSION['siteEmail'] = $siteEmail;
    $_SESSION['sitePhone'] = $sitePhone;
    $_SESSION['businessHours'] = $businessHours;
    
    // Set success message
    $_SESSION['settings_updated'] = true;
} else {
    // Set error message
    $_SESSION['settings_error'] = "Failed to update general settings: " . mysqli_error($conn);
}

// Close database connection
mysqli_stmt_close($stmt);
mysqli_close($conn);

// Redirect back to settings page
header("Location: settings.php");
exit(); 