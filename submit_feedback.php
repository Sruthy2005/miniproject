<?php
session_start();
require_once "connect.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    $_SESSION['error'] = "Please submit the feedback form properly";
    header("Location: my_bookings.php");
    exit();
}

// Validate booking ID
if (!isset($_POST['booking_id']) || empty($_POST['booking_id'])) {
    $_SESSION['error'] = "Please provide a valid booking";
    header("Location: my_bookings.php");
    exit();
}

$booking_id = $_POST['booking_id'];
$user_id = $_SESSION['user_id'];
$rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
$comments = isset($_POST['comments']) ? trim($_POST['comments']) : '';

// Validate rating
if ($rating < 1 || $rating > 5) {
    $_SESSION['error'] = "Please select a rating between 1 and 5 stars";
    header("Location: feedback.php?booking_id=" . $booking_id);
    exit();
}

// Verify booking belongs to user and is paid
$verify_query = "SELECT id FROM bookings WHERE id = ? AND user_id = ? AND payment_status = 'paid'";
$verify_stmt = $conn->prepare($verify_query);
$verify_stmt->bind_param("ii", $booking_id, $user_id);
$verify_stmt->execute();
$verify_result = $verify_stmt->get_result();

if ($verify_result->num_rows === 0) {
    $_SESSION['error'] = "Invalid booking or unauthorized access";
    header("Location: my_bookings.php");
    exit();
}

// Check if feedback already exists
$check_query = "SELECT id FROM feedback WHERE booking_id = ?";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param("i", $booking_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    $_SESSION['error'] = "Feedback has already been submitted for this booking";
    header("Location: feedback.php?booking_id=" . $booking_id);
    exit();
}

// Insert feedback
$insert_query = "INSERT INTO feedback (booking_id, user_id, rating, comments, created_at) VALUES (?, ?, ?, ?, NOW())";
$insert_stmt = $conn->prepare($insert_query);
$insert_stmt->bind_param("iiis", $booking_id, $user_id, $rating, $comments);

if ($insert_stmt->execute()) {
    $_SESSION['success'] = "Thank you for your feedback!";
} else {
    $_SESSION['error'] = "Error submitting feedback. Please try again.";
}

header("Location: feedback.php?booking_id=" . $booking_id);
exit();
?> 