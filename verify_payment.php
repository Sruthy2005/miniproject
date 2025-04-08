<?php
session_start();
require_once "connect.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Clear any old error messages
unset($_SESSION['error']);

// Check if required parameters are present
if (!isset($_GET['booking_id']) || !isset($_GET['payment_id'])) {
    $_SESSION['error'] = "Missing payment information";
    header("Location: my_bookings.php");
    exit();
}

$booking_id = $_GET['booking_id'];
$payment_id = $_GET['payment_id'];
$user_id = $_SESSION['user_id'];

// Verify that the booking belongs to the current user
$query = "SELECT b.*, s.price 
          FROM bookings b 
          JOIN service s ON b.specific_service = s.id 
          WHERE b.id = ? AND b.user_id = ? AND b.status = 'pending'";

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
    $_SESSION['error'] = "Invalid booking or payment already processed";
    header("Location: my_bookings.php");
    exit();
}

$booking = $result->fetch_assoc();

try {
    // Start transaction
    $conn->begin_transaction();

    // Update booking status
    $update_query = "UPDATE bookings 
                    SET status = 'confirmed', 
                        payment_status = 'paid',
                        payment_id = ?
                    WHERE id = ? AND user_id = ?";
    
    $update_stmt = $conn->prepare($update_query);
    if ($update_stmt === false) {
        throw new Exception("Failed to prepare update statement: " . $conn->error);
    }

    $update_stmt->bind_param("sii", $payment_id, $booking_id, $user_id);
    if (!$update_stmt->execute()) {
        throw new Exception("Failed to update booking: " . $update_stmt->error);
    }

    // Insert into payments table (not payment_transactions)
    $payment_query = "INSERT INTO payments 
                     (booking_id, payment_id, amount, status, created_at) 
                     VALUES (?, ?, ?, 'completed', CURRENT_TIMESTAMP)";
    
    $payment_stmt = $conn->prepare($payment_query);
    if ($payment_stmt === false) {
        throw new Exception("Failed to prepare payment statement: " . $conn->error);
    }

    $amount = $booking['price'];
    $payment_stmt->bind_param("isd", $booking_id, $payment_id, $amount);
    if (!$payment_stmt->execute()) {
        throw new Exception("Failed to record payment: " . $payment_stmt->error);
    }

    // Commit transaction
    $conn->commit();

    // Set success message
    $_SESSION['success'] = "Payment processed successfully! Your booking is now confirmed.";
    
    // Redirect to feedback page instead of booking details
    header("Location: feedback.php?booking_id=" . $booking_id);
    exit();

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    $_SESSION['error'] = "Payment verification failed: " . $e->getMessage();
    header("Location: my_bookings.php");
    exit();
}
?>