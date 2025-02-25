<?php
session_start();
require_once "connect.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Validate and sanitize input
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $service_category = mysqli_real_escape_string($conn, $_POST['service_category']);
    $specific_service = mysqli_real_escape_string($conn, $_POST['specific_service']); 
    $staff_member = mysqli_real_escape_string($conn, $_POST['staff_member']);
    $date = mysqli_real_escape_string($conn, $_POST['date']);
    $time = mysqli_real_escape_string($conn, $_POST['time']);
    $notes = mysqli_real_escape_string($conn, $_POST['notes']);
    
    // Get service price based on specific service
    $price = 0;
    switch($specific_service) {
        case 'haircut': $price = 40; break;
        case 'coloring': $price = 85; break;
        case 'treatment': $price = 65; break;
        case 'extension': $price = 150; break;
        case 'bridal': $price = 120; break;
        case 'facial': $price = 45; break;
        case 'deep_cleansing': $price = 65; break;
        case 'anti_aging': $price = 85; break;
        case 'acne': $price = 55; break;
        case 'brightening': $price = 75; break;
        case 'natural': $price = 50; break;
        case 'party': $price = 75; break;
        case 'bridal_makeup': $price = 150; break;
        case 'eye': $price = 35; break;
        case 'lesson': $price = 80; break;
    }

    // Insert booking into database
    $sql = "INSERT INTO bookings (user_id, staff_member, service_category, specific_service, date, time, notes, price, status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";

    // First check if connection is still valid
    if ($conn->connect_error) {
        die('Connection failed: ' . htmlspecialchars($conn->connect_error));
    }

    $stmt = $conn->prepare($sql);

    // Improved error handling for statement preparation
    if (!$stmt) {
        die('Prepare failed: ' . htmlspecialchars($conn->error) . ' (Error #' . $conn->errno . ')');
    }
    
    $stmt->bind_param("iisssssd", $user_id, $staff_member, $service_category, $specific_service, $date, $time, $notes, $price);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Booking successful! We will confirm your appointment shortly.";
        header("Location: my_bookings.php");
        exit();
    } else {
        $_SESSION['error'] = "Error creating booking. Please try again.";
        header("Location: booking.php");
        exit();
    }

} else {
    header("Location: booking.php");
    exit();
}

$conn->close();
?>
