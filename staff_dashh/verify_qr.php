<?php
session_start();
require_once '../connect.php';

// Check if user is logged in and is staff
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Get JSON data from request
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

// Validate input
if (!isset($data['appointment_id']) || !isset($data['qr_data'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit();
}

$appointment_id = $data['appointment_id'];
$qr_data = $data['qr_data'];

try {
    // Parse QR data
    $qr_info = json_decode($qr_data, true);
    
    if (!$qr_info || !isset($qr_info['booking_id'])) {
        throw new Exception('Invalid QR code format');
    }
    
    $qr_booking_id = $qr_info['booking_id'];
    
    // Verify that the QR code matches the appointment
    if ($qr_booking_id != $appointment_id) {
        throw new Exception('QR code does not match this appointment');
    }
    
    // Get appointment details
    $query = "SELECT b.*, CONCAT(u.first_name, ' ', u.last_name) as client_name 
              FROM bookings b 
              JOIN user u ON b.user_id = u.id 
              WHERE b.id = ? AND b.status = 'confirmed'";
    
    $stmt = $conn->prepare($query);
    
    if ($stmt === false) {
        throw new Exception('Database error: ' . $conn->error);
    }
    
    $stmt->bind_param("i", $appointment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Appointment not found or not in confirmed status');
    }
    
    $appointment = $result->fetch_assoc();
    
    // Update appointment status to completed
    $update_query = "UPDATE bookings SET status = 'completed' WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    
    if ($update_stmt === false) {
        throw new Exception('Database error: ' . $conn->error);
    }
    
    $update_stmt->bind_param("i", $appointment_id);
    $update_result = $update_stmt->execute();
    
    if (!$update_result) {
        throw new Exception('Failed to update appointment status');
    }
    
    // Format date and time for response
    $formatted_date = date('M d, Y', strtotime($appointment['date']));
    $formatted_time = date('h:i A', strtotime($appointment['time']));
    
    // Return success response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Appointment verified successfully',
        'client_name' => $appointment['client_name'],
        'service' => $appointment['specific_service'],
        'date' => $formatted_date,
        'time' => $formatted_time
    ]);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 