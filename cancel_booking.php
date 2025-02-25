s<?php
session_start();
require_once "connect.php";

// Check if user is logged in, redirect if not
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if booking ID is provided
if (!isset($_GET['id'])) {
    die("Booking ID not specified.");
}

$booking_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Prepare the SQL statement to delete the booking
$query = "DELETE FROM bookings WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}

$stmt->bind_param("ii", $booking_id, $user_id);
if ($stmt->execute()) {
    // Redirect to my_bookings.php with a success message
    header("Location: my_bookings.php?message=Booking cancelled successfully.");
} else {
    die("Error cancelling booking: " . $stmt->error);
}

$stmt->close();
$conn->close();
?> 