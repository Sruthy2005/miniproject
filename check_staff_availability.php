<?php
session_start();
require_once "connect.php";
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and sanitize input data
    $date = isset($_POST['date']) ? mysqli_real_escape_string($conn, $_POST['date']) : '';
    $time = isset($_POST['time']) ? mysqli_real_escape_string($conn, $_POST['time']) : '';
    $service_category = isset($_POST['service_category']) ? mysqli_real_escape_string($conn, $_POST['service_category']) : '';
    
    if (empty($date) || empty($time)) {
        echo json_encode([
            'success' => false,
            'message' => 'Date and time are required'
        ]);
        exit();
    }
    
    // Debug info - Check how many staff exist with this specialization
    $check_query = "SELECT COUNT(*) as count FROM user WHERE role = 'staff' AND status = 'active' AND specialization = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("s", $service_category);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $check_row = $check_result->fetch_assoc();
    $staff_count = $check_row['count'];
    
    // Debug info - Check existing bookings at this time
    $booking_query = "SELECT COUNT(*) as count FROM bookings WHERE date = ? AND time = ? AND status != 'cancelled'";
    $booking_stmt = $conn->prepare($booking_query);
    $booking_stmt->bind_param("ss", $date, $time);
    $booking_stmt->execute();
    $booking_result = $booking_stmt->get_result();
    $booking_row = $booking_result->fetch_assoc();
    $booking_count = $booking_row['count'];

    // Step 2: Find all staff members (removing specialization requirement)
    $staff_query = "SELECT id, first_name, last_name FROM user 
                  WHERE role = 'staff' 
                  AND status = 'active'";

    // Get all staff members for the service category who are available at the specified time
    $query = "SELECT u.id, u.first_name, u.last_name 
              FROM user u 
              WHERE u.role = 'staff' 
              AND u.status = 'active'
              AND u.id NOT IN (
                  SELECT b.staff_member 
                  FROM bookings b 
                  WHERE b.date = ? 
                  AND b.time = ? 
                  AND b.status != 'cancelled'
              )
              ORDER BY u.first_name, u.last_name";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . htmlspecialchars($conn->error)
        ]);
        exit();
    }

    $stmt->bind_param("ss", $date, $time);
    $stmt->execute();
    $result = $stmt->get_result();

    $available_staff = [];
    while ($row = $result->fetch_assoc()) {
        $available_staff[] = [
            'id' => $row['id'],
            'name' => $row['first_name'] . ' ' . $row['last_name']
        ];
    }

    echo json_encode([
        'success' => true,
        'available_staff' => $available_staff,
        'debug' => [
            'service_category' => $service_category,
            'date' => $date,
            'time' => $time,
            'total_staff_with_specialization' => $staff_count,
            'total_bookings_at_time' => $booking_count
        ]
    ]);

    $stmt->close();
    $check_stmt->close();
    $booking_stmt->close();
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}

$conn->close();
?> 