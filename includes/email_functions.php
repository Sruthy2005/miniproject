<?php
require_once __DIR__ . '/../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendStaffBookingNotification($staff_id, $booking_details) {
    global $conn;
    
    // Get staff email
    $staff_query = "SELECT email, first_name, last_name FROM user WHERE id = ? AND role = 'staff'";
    $stmt = mysqli_prepare($conn, $staff_query);
    mysqli_stmt_bind_param($stmt, "i", $staff_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $staff = mysqli_fetch_assoc($result);
    
    if (!$staff) {
        error_log("Staff member not found for ID: " . $staff_id);
        return false;
    }
    
    // Create PHPMailer instance
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'sruthyms200504@gmail.com'; 
        $mail->Password = 'syoo wxqm wqjd fisg'; 
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;

        // Recipients
        $mail->setFrom('sruthyms200504@gmail.com', 'Bellezza Beauty');
        $mail->addAddress($staff['email'], $staff['first_name'] . ' ' . $staff['last_name']);

        // Content
        $mail->isHTML(true);
        $mail->Subject = "New Booking Notification";
        
        // Format the date and time
        $formatted_date = date('F j, Y', strtotime($booking_details['date']));
        $formatted_time = date('g:i A', strtotime($booking_details['time']));
        
        $mail->Body = "
            <div style='background-color: #f6f9fc; padding: 40px 0; font-family: Arial, sans-serif;'>
                <div style='background-color: #ffffff; max-width: 600px; margin: 0 auto; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);'>
                    <div style='text-align: center; margin-bottom: 30px;'>
                        <h1 style='color: #1a73e8; margin: 0; font-size: 24px;'>New Booking Notification</h1>
                    </div>
                    <div style='color: #4a4a4a; font-size: 16px; line-height: 1.6;'>
                        <p>Dear {$staff['first_name']} {$staff['last_name']},</p>
                        <p>You have a new booking scheduled:</p>
                        <div style='background-color: #f8f9fa; padding: 15px; border-radius: 6px; margin: 20px 0;'>
                            <p><strong>Service:</strong> {$booking_details['service_category']} - {$booking_details['specific_service']}</p>
                            <p><strong>Date:</strong> {$formatted_date}</p>
                            <p><strong>Time:</strong> {$formatted_time}</p>
                            <p><strong>Client Name:</strong> {$booking_details['client_name']}</p>
                            <p><strong>Client Email:</strong> {$booking_details['client_email']}</p>
                            <p><strong>Client Phone:</strong> {$booking_details['client_phone']}</p>
                            " . (!empty($booking_details['notes']) ? "<p><strong>Notes:</strong> {$booking_details['notes']}</p>" : "") . "
                        </div>
                        <p>Please review this booking in your staff dashboard.</p>
                    </div>
                    <div style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #666; font-size: 14px; text-align: center;'>
                        <p>Best regards,<br>Bellezza Beauty</p>
                    </div>
                </div>
            </div>";
            
        $mail->AltBody = "Dear {$staff['first_name']} {$staff['last_name']},\n\n"
            . "You have a new booking scheduled:\n\n"
            . "Service: {$booking_details['service_category']} - {$booking_details['specific_service']}\n"
            . "Date: {$formatted_date}\n"
            . "Time: {$formatted_time}\n"
            . "Client Name: {$booking_details['client_name']}\n"
            . "Client Email: {$booking_details['client_email']}\n"
            . "Client Phone: {$booking_details['client_phone']}\n"
            . (!empty($booking_details['notes']) ? "Notes: {$booking_details['notes']}\n" : "")
            . "\nPlease review this booking in your staff dashboard.\n\n"
            . "Best regards,\nBellezza Beauty";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Failed to send booking notification email to staff: " . $mail->ErrorInfo);
        return false;
    }
} 