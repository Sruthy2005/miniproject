<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Content-Type: application/json');
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

// Database connection
require_once '../connect.php';

// Set JSON header
header('Content-Type: application/json');

// Check if service_id is provided
if (!isset($_POST['service_id']) || !is_numeric($_POST['service_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid service ID']);
    exit();
}

// Sanitize input
$service_id = mysqli_real_escape_string($conn, $_POST['service_id']);

// Prepare and execute query
$sql = "SELECT id, name, description, price, category FROM service WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . mysqli_error($conn)]);
    exit();
}

mysqli_stmt_bind_param($stmt, "i", $service_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => 'Query failed: ' . mysqli_error($conn)]);
    exit();
}

$service = mysqli_fetch_assoc($result);

if ($service) {
    echo json_encode($service);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Service not found']);
}

mysqli_close($conn);
?> 