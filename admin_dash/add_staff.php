<?php
session_start();
require_once '../connect.php';
// Add PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php'; // Path to autoload.php from Composer

// ... existing session and CSRF checks ...

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input data
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    
    // Generate temporary password - using more secure method
    $temp_password = bin2hex(random_bytes(4)); // Generate 8 character password
    $hashed_password = password_hash($temp_password, PASSWORD_DEFAULT);
    
    // Add this after generating the password for debugging
    error_log("Generated temp password: " . $temp_password);
    error_log("Generated hash: " . $hashed_password);
    
    // Use prepared statements instead of mysqli_real_escape_string
    $sql = "INSERT INTO user (id, first_name, last_name, email, phone, password, role, temp_password) 
            VALUES (NULL, ?, ?, ?, ?, ?, 'staff', 1)";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", 
        $_POST['first_name'],
        $_POST['last_name'],
        $_POST['email'],
        $_POST['phone'],
        $hashed_password
    );
    
    if ($stmt->execute()) {
        // Create PHPMailer instance
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'sruthyms200504@gmail.com'; 
            $mail->Password = 'hqlu ylzq oohn wkkc'; 
            $mail->SMTPSecure = 'tls'; 
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('sruthyms200504@gmail.com', 'Bellezza Beauty');
            $mail->addAddress($email, "$first_name $last_name");

            // Content
            $mail->isHTML(true);
            $mail->Subject = "Welcome to Our Team - Your Account Details";
            $mail->Body = "
                <p>Dear $first_name $last_name,</p>
                <p>Welcome to our team! Your account has been created successfully.</p>
                <p>Here are your login credentials:</p>
                <p><strong>Email:</strong> $email<br>
                <strong>Temporary Password:</strong> $temp_password</p>
                <p>Please login and change your password as soon as possible.</p>
                <p>Best regards,<br>
                Admin Team</p>";
            $mail->AltBody = "Dear $first_name $last_name,\n\n"
                . "Welcome to our team! Your account has been created successfully.\n\n"
                . "Here are your login credentials:\n"
                . "Email: $email\n"
                . "Temporary Password: $temp_password\n\n"
                . "Please login and change your password as soon as possible.\n\n"
                . "Best regards,\n"
                . "Admin Team";

            $mail->send();
            $_SESSION['success_message'] = "Staff member added successfully and login credentials sent via email.";
        } catch (Exception $e) {
            $_SESSION['error_message'] = "Staff member added but failed to send email. Error: {$mail->ErrorInfo}";
        }
        
        header("Location: manage_staff.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Error adding staff member: " . mysqli_error($conn);
        header("Location: manage_staff.php");
        exit();
    }
}
?>
