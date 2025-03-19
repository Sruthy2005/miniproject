<?php
// google_auth_handler.php
session_start();
require_once "connect.php";

// Check if necessary data was sent
if (!isset($_POST['email']) || !isset($_POST['google_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Sanitize inputs
$google_id = mysqli_real_escape_string($conn, $_POST['google_id']);
$email = mysqli_real_escape_string($conn, $_POST['email']);
$display_name = mysqli_real_escape_string($conn, $_POST['display_name'] ?? '');
$photo_url = mysqli_real_escape_string($conn, $_POST['photo_url'] ?? '');

// Debug log
error_log("Google login attempt - Email: " . $email . ", Google ID: " . $google_id);

// Check if user exists by Google ID
$query = "SELECT * FROM user WHERE google_id = ?";
$stmt = mysqli_prepare($conn, $query);
if (!$stmt) {
    error_log("Prepare failed: " . mysqli_error($conn));
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

mysqli_stmt_bind_param($stmt, "s", $google_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    // Try finding user by email
    $query = "SELECT * FROM user WHERE email = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    
    if ($user) {
        // User exists but Google ID not set, update it
        $update_query = "UPDATE user SET google_id = ? WHERE id = ?";
        $update_stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($update_stmt, "si", $google_id, $user['id']);
        mysqli_stmt_execute($update_stmt);
        
        error_log("Updated existing user with Google ID: " . $user['id']);
    } else {
        // Create new user
        // Extract first name and last name from display name
        $name_parts = explode(' ', $display_name);
        $first_name = $name_parts[0] ?? '';
        $last_name = isset($name_parts[1]) ? implode(' ', array_slice($name_parts, 1)) : '';
        
        $insert_query = "INSERT INTO user (email, google_id, first_name, last_name, profile_image, role, created_at) 
                        VALUES (?, ?, ?, ?, ?, 'user', NOW())";
        $insert_stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($insert_stmt, "sssss", $email, $google_id, $first_name, $last_name, $photo_url);
        
        if (mysqli_stmt_execute($insert_stmt)) {
            $user_id = mysqli_insert_id($conn);
            error_log("Created new user: " . $user_id);
            
            // Get the newly created user
            $query = "SELECT * FROM user WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $user = mysqli_fetch_assoc($result);
        } else {
            error_log("Failed to create user: " . mysqli_error($conn));
            echo json_encode(['success' => false, 'message' => 'Failed to create account']);
            exit;
        }
    }
}

// Set session variables
$_SESSION['user_id'] = $user['id'];
$_SESSION['profile_image'] = $user['profile_image'] ?? 'images/default-avatar.jpg';
$_SESSION['role'] = $user['role'];

// Debug session
error_log("Session data set - User ID: " . $_SESSION['user_id'] . ", Role: " . $_SESSION['role']);

// Determine redirect URL based on role
$redirect_url = '';
switch($user['role']) {
    case 'admin':
        $redirect_url = "admin_dash/admin.php";
        break;
    case 'staff':
        $redirect_url = "staff_dash/staff_dashboard.php";
        break;
    case 'user':
    default:
        $redirect_url = "index.php";
}

// Return success response
echo json_encode([
    'success' => true, 
    'redirect_url' => $redirect_url,
    'user_id' => $user['id'],
    'role' => $user['role']
]);
exit;
?>