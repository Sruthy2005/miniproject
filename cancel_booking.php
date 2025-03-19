<?php
session_start();
require_once "connect.php";

// Check if user is logged in, redirect if not
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if booking ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "Invalid booking ID";
    header("Location: my_bookings.php");
    exit();
}

$booking_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Verify that the booking belongs to the user
$verify_query = "SELECT * FROM bookings WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($verify_query);
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Booking not found or unauthorized";
    header("Location: my_bookings.php");
    exit();
}

$booking = $result->fetch_assoc();

// Check if booking is already cancelled or completed
if ($booking['status'] === 'cancelled' || $booking['status'] === 'completed') {
    $_SESSION['error'] = "This booking cannot be cancelled";
    header("Location: my_bookings.php");
    exit();
}

// Update booking status to cancelled
$update_query = "UPDATE bookings SET status = 'cancelled' WHERE id = ?";
$stmt = $conn->prepare($update_query);
$stmt->bind_param("i", $booking_id);

if ($stmt->execute()) {
    $_SESSION['success'] = "Booking cancelled successfully";
} else {
    $_SESSION['error'] = "Error cancelling booking";
}

header("Location: my_bookings.php");
exit();
?> 