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
    
    // Get service price from database
    $price = 0;
    $price_query = "SELECT price FROM service WHERE name = ?";
    $stmt_price = $conn->prepare($price_query);
    if (!$stmt_price) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }
    $stmt_price->bind_param("s", $specific_service);
    if ($stmt_price->execute()) {
        $result = $stmt_price->get_result();
        if ($row = $result->fetch_assoc()) {
            $price = $row['price'];
        }
    }
    $stmt_price->close();

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
