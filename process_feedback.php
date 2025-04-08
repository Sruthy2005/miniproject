<?php
session_start();
require_once "connect.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Invalid request method";
    header("Location: my_bookings.php");
    exit();
}

// Validate required fields
if (!isset($_POST['booking_id']) || !isset($_POST['rating'])) {
    $_SESSION['error'] = "Missing required fields";
    header("Location: my_bookings.php");
    exit();
}

$booking_id = $_POST['booking_id'];
$user_id = $_SESSION['user_id'];
$rating = intval($_POST['rating']);
$comments = isset($_POST['comments']) ? trim($_POST['comments']) : '';

// Validate rating (1-5)
if ($rating < 1 || $rating > 5) {
    $_SESSION['error'] = "Invalid rating value";
    header("Location: feedback.php?booking_id=" . $booking_id);
    exit();
}

// Verify that the booking belongs to the current user and is paid
$query = "SELECT * FROM bookings WHERE id = ? AND user_id = ? AND payment_status = 'paid'";
$stmt = $conn->prepare($query);
if ($stmt === false) {
    $_SESSION['error'] = "Database error: " . $conn->error;
    header("Location: my_bookings.php");
    exit();
}

$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Booking not found or payment not completed";
    header("Location: my_bookings.php");
    exit();
}

// Check if feedback already exists
$check_query = "SELECT * FROM feedback WHERE booking_id = ?";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param("i", $booking_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    $_SESSION['error'] = "Feedback has already been submitted for this booking";
    header("Location: feedback.php?booking_id=" . $booking_id);
    exit();
}

// Save feedback to database
try {
    // Start transaction
    $conn->begin_transaction();

    // Insert feedback
    $insert_query = "INSERT INTO feedback (booking_id, user_id, rating, comments, created_at) 
                    VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)";
    
    $insert_stmt = $conn->prepare($insert_query);
    if ($insert_stmt === false) {
        throw new Exception("Failed to prepare insert statement: " . $conn->error);
    }

    $insert_stmt->bind_param("iiis", $booking_id, $user_id, $rating, $comments);
    if (!$insert_stmt->execute()) {
        throw new Exception("Failed to save feedback: " . $insert_stmt->error);
    }

    // Update booking to mark feedback as received
    $update_query = "UPDATE bookings SET has_feedback = 1 WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    if ($update_stmt === false) {
        throw new Exception("Failed to prepare update statement: " . $conn->error);
    }

    $update_stmt->bind_param("i", $booking_id);
    if (!$update_stmt->execute()) {
        throw new Exception("Failed to update booking: " . $update_stmt->error);
    }

    // Commit transaction
    $conn->commit();

    // Set success message
    $_SESSION['success'] = "Thank you for your feedback! We appreciate your input.";
    
    // Redirect to feedback page
    header("Location: feedback.php?booking_id=" . $booking_id);
    exit();

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    $_SESSION['error'] = "Failed to save feedback: " . $e->getMessage();
    header("Location: feedback.php?booking_id=" . $booking_id);
    exit();
}
?> 