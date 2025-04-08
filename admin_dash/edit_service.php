<?php
session_start();

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || 
    $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo json_encode(['error' => 'Invalid CSRF token']);
    exit();
}

// Database connection
require_once '../connect.php';

// Validate required fields
if (!isset($_POST['service_id']) || !isset($_POST['name']) || !isset($_POST['description']) || 
    !isset($_POST['price']) || !isset($_POST['category'])) {
    echo json_encode(['error' => 'Missing required fields']);
    exit();
}

try {
    // Sanitize inputs
    $service_id = mysqli_real_escape_string($conn, $_POST['service_id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);

    // Handle image upload if provided
    $image_update = "";
    if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
        $target_dir = "../uploads/services/";
        
        // Create directory if it doesn't exist
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        // Check if image file is valid
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($file_extension, $allowed_types)) {
            throw new Exception('Invalid file type. Allowed types: ' . implode(', ', $allowed_types));
        }
        
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_path = mysqli_real_escape_string($conn, 'uploads/services/' . $new_filename);
            $image_update = ", image_path = '$image_path'";
            
            // Delete old image
            $old_image_sql = "SELECT image_path FROM service WHERE id = ?";
            $stmt = mysqli_prepare($conn, $old_image_sql);
            mysqli_stmt_bind_param($stmt, "i", $service_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if ($old_image = mysqli_fetch_assoc($result)) {
                if ($old_image['image_path'] && file_exists("../" . $old_image['image_path'])) {
                    unlink("../" . $old_image['image_path']);
                }
            }
        }
    }

    // Update service in database
    $sql = "UPDATE service SET 
            name = ?, 
            description = ?, 
            price = ?, 
            category = ? 
            $image_update 
            WHERE id = ?";

    $stmt = mysqli_prepare($conn, $sql);
    
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "ssdsi", $name, $description, $price, $category, $service_id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode([
            'success' => true,
            'message' => 'Service updated successfully'
        ]);
    } else {
        throw new Exception('Failed to execute statement: ' . mysqli_error($conn));
    }

} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) {
        mysqli_stmt_close($stmt);
    }
    mysqli_close($conn);
}
?>