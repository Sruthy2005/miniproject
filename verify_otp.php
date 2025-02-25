<?php
require_once "connect.php";
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_otp = $_POST['otp'];
    $stored_otp = $_SESSION['otp'] ?? null;
    $otp_time = $_SESSION['otp_time'] ?? 0;
    $registration_data = $_SESSION['registration_data'] ?? null;
    
    // Check if OTP is expired (10 minutes)
    if (time() - $otp_time > 600) {
        echo json_encode(['success' => false, 'message' => 'OTP has expired']);
        exit();
    }

    // Verify OTP
    if ($entered_otp === $stored_otp) {
        // Insert user data into database
        $sql = "INSERT INTO user (first_name, last_name, phone, email, password) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", 
            $registration_data['first_name'],
            $registration_data['last_name'],
            $registration_data['phone'],
            $registration_data['email'],
            $registration_data['password']
        );
        
        if($stmt->execute()) {
            // Clear session data
            unset($_SESSION['otp']);
            unset($_SESSION['otp_time']);
            unset($_SESSION['otp_attempts']);
            unset($_SESSION['registration_data']);
            
            echo json_encode(['success' => true, 'message' => 'Account created successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error']);
        }
    } else {
        $_SESSION['otp_attempts'] = ($_SESSION['otp_attempts'] ?? 0) + 1;
        
        if ($_SESSION['otp_attempts'] >= 3) {
            unset($_SESSION['otp']);
            unset($_SESSION['otp_time']);
            unset($_SESSION['otp_attempts']);
            unset($_SESSION['registration_data']);
            echo json_encode(['success' => false, 'message' => 'Too many failed attempts. Please register again.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Incorrect OTP']);
        }
    }
    exit();
}
