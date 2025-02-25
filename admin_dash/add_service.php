<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || 
    $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("Invalid request");
}

// Database connection
require_once '../connect.php';

// Add error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Validate and sanitize inputs
if (!isset($_POST['name']) || !isset($_POST['description']) || !isset($_POST['price']) || !isset($_POST['category'])) {
    die("Missing required fields");
}

$name = mysqli_real_escape_string($conn, $_POST['name']);
$description = mysqli_real_escape_string($conn, $_POST['description']);
$price = floatval($_POST['price']);
$category = mysqli_real_escape_string($conn, $_POST['category']);

// Handle file upload
$image_path = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $file_type = $_FILES['image']['type'];
    
    if (!in_array($file_type, $allowed_types)) {
        die("Invalid file type. Only JPG, PNG and GIF are allowed.");
    }

    $max_size = 5 * 1024 * 1024; // 5MB
    if ($_FILES['image']['size'] > $max_size) {
        die("File is too large. Maximum size is 5MB.");
    }

    $target_dir = "../uploads/services/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        $image_path = "uploads/services/" . $new_filename;
        
        $sql = "INSERT INTO service (name, description, image_path, price, category) 
                VALUES (?, ?, ?, ?, ?)";
                
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sssds", $name, $description, $image_path, $price, $category);
            
            if (mysqli_stmt_execute($stmt)) {
                header("Location: manage_services.php?success=1");
                exit();
            } else {
                die("Error executing query: " . mysqli_stmt_error($stmt));
            }
        } else {
            die("Error preparing statement: " . mysqli_error($conn));
        }
    } else {
        die("Error uploading file");
    }
}

// Add this new section to handle service insertion even without image
$sql = "INSERT INTO service (name, description, image_path, price, category) 
        VALUES (?, ?, ?, ?, ?)";
        
$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "sssds", $name, $description, $image_path, $price, $category);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: manage_services.php?success=1");
        exit();
    } else {
        die("Error executing query: " . mysqli_stmt_error($stmt));
    }
} else {
    die("Error preparing statement: " . mysqli_error($conn));
}

mysqli_close($conn);
?> 