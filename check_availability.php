<?php
session_start();
require_once "connect.php";
header('Content-Type: application/json');

// Check if the request is a POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and sanitize input data
    $date = isset($_POST['date']) ? mysqli_real_escape_string($conn, $_POST['date']) : '';
    $time = isset($_POST['time']) ? mysqli_real_escape_string($conn, $_POST['time']) : '';
    $staff_id = isset($_POST['staff_id']) ? intval($_POST['staff_id']) : 0;
    
    // Validate required inputs
    if (empty($date) || empty($time)) {
        echo json_encode([
            'success' => false,
            'message' => 'Date and time are required'
        ]);
        exit();
    }
    
    $response = [
        'success' => true,
        'available' => false,
        'available_staff' => []
    ];
    
    // If a specific staff member is selected
    if ($staff_id > 0) {
        $availability_query = "SELECT id FROM bookings 
                              WHERE staff_member = ? 
                              AND date = ? 
                              AND time = ? 
                              AND status != 'cancelled'";
        
        $stmt = $conn->prepare($availability_query);
        if (!$stmt) {
            echo json_encode([
                'success' => false,
                'message' => 'Database error: ' . htmlspecialchars($conn->error)
            ]);
            exit();
        }
        
        $stmt->bind_param("iss", $staff_id, $date, $time);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            // Staff is available
            $response['available'] = true;
            $response['available_staff'][] = $staff_id;
        }
        
        $stmt->close();
    } else {
        // Get all available staff members
        $staff_query = "SELECT s.id, s.name 
                        FROM staff s 
                        WHERE s.id NOT IN (
                            SELECT b.staff_member 
                            FROM bookings b 
                            WHERE b.date = ? 
                            AND b.time = ? 
                            AND b.status != 'cancelled'
                        )";
        
        $stmt = $conn->prepare($staff_query);
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
        
        if ($result->num_rows > 0) {
            // There are available staff members
            $response['available'] = true;
            
            while ($row = $result->fetch_assoc()) {
                $response['available_staff'][] = [
                    'id' => $row['id'],
                    'name' => $row['name']
                ];
            }
        }
        
        $stmt->close();
    }
    
    echo json_encode($response);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}

$conn->close();
?>

