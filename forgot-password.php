<?php
require_once "connect.php";
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

if(isset($_POST['check_email'])) {
    $email = $_POST['email'];
    
    // Check if email exists in database
    $stmt = $conn->prepare("SELECT * FROM user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        // Generate OTP
        $otp = sprintf("%06d", rand(100000, 999999));
        $_SESSION['reset_otp'] = $otp;
        $_SESSION['reset_email'] = $email;
        $_SESSION['otp_time'] = time();

        // Send OTP via email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'sruthyms200504@gmail.com';
            $mail->Password = 'hqlu ylzq oohn wkkc';
            $mail->SMTPSecure = 'ssl';
            $mail->Port = 465;

            $mail->setFrom('sruthyms200504@gmail.com');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset OTP';
           
            $emailTemplate = "
            <div style='background-color: #f6f9fc; padding: 40px 0; font-family: Arial, sans-serif;'>
                <div style='background-color: #ffffff; max-width: 600px; margin: 0 auto; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);'>
                    <h1 style='color: #1a73e8; margin: 0; font-size: 24px; text-align: center;'>Password Reset</h1>
                    <p>Your password reset OTP is:</p>
                    <div style='background-color: #f8f9fa; padding: 15px; border-radius: 6px; text-align: center; margin: 20px 0;'>
                        <span style='font-size: 32px; font-weight: bold; color: #1a73e8; letter-spacing: 5px;'>$otp</span>
                    </div>
                    <p>This OTP will expire in 10 minutes.</p>
                </div>
            </div>";
           
            $mail->Body = $emailTemplate;
            $mail->send();
            echo json_encode(['status' => 'success', 'message' => 'OTP sent successfully']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'An error occurred while sending OTP: ' . $mail->ErrorInfo]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Email not found']);
    }
    exit();
}

if(isset($_POST['verify_otp'])) {
    $entered_otp = $_POST['otp'];
    if(isset($_SESSION['reset_otp']) && $_SESSION['reset_otp'] == $entered_otp) {
        // Check if OTP is expired (10 minutes)
        if(time() - $_SESSION['otp_time'] <= 600) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'OTP has expired']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid OTP']);
    }
    exit();
}

if(isset($_POST['reset_password'])) {
    $new_password = md5($_POST['new_password']);
    $email = $_SESSION['reset_email'];
    
    $stmt = $conn->prepare("UPDATE user SET password = ? WHERE email = ?");
    $stmt->bind_param("ss", $new_password, $email);
    
    if($stmt->execute()) {
        // Clear session variables
        unset($_SESSION['reset_otp']);
        unset($_SESSION['reset_email']);
        unset($_SESSION['otp_time']);
        echo json_encode(['status' => 'success', 'message' => 'Password updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update password']);
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Bellezza</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Add these CSS files -->
    <link href="https://fonts.googleapis.com/css?family=Montserrat:300,400,600,700" rel="stylesheet" />
    <link rel="stylesheet" href="css/open-iconic-bootstrap.min.css" />
    <link rel="stylesheet" href="css/animate.css" />
    <link rel="stylesheet" href="css/style.css" />
    
    <style>
        body {
            font-family: "Montserrat", sans-serif;
            background: url('images/bggg.jpg') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            min-height: 100vh;
            padding-top: 100px;
        }

        .logo {
            margin-bottom: 2rem;
            text-align: center;
        }

        .logo .flaticon-flower {
            font-size: 3rem;
            color: #fa5bdd;
            display: block;
            margin-bottom: 0.5rem;
        }

        .logo h1 {
            color: #fa5bdd;
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
        }

        .forgot-password-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 3rem;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
            margin: 0 auto;
        }

        .forgot-password-container h2 {
            margin-bottom: 1.5rem;
            color: #fa5bdd;
            font-size: 2rem;
            font-weight: 600;
        }

        .step {
            display: none;
        }

        .step.active {
            display: block;
        }

        input {
            width: 90%;
            padding: 0.8rem;
            margin: 0.75rem 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            color: #333;
            transition: all 0.3s ease;
        }

        input::placeholder {
            color: #999;
        }

        input:focus {
            outline: none;
            border-color: #fa5bdd;
            box-shadow: 0 0 5px rgba(250, 91, 221, 0.2);
        }

        button {
            width: 100%;
            padding: 1rem;
            background: #fa5bdd;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 1.1rem;
            cursor: pointer;
            margin-top: 1rem;
            transition: all 0.3s ease;
        }

        button:hover {
            background: #e640c7;
            transform: translateY(-2px);
        }

        button:disabled {
            background: #fca5e9;
            cursor: not-allowed;
            transform: none;
        }

        .divider {
            margin: 2rem 0;
            display: flex;
            align-items: center;
            color: #666;
        }

        .divider::before,
        .divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: #ddd;
        }

        .divider span {
            padding: 0 1rem;
            color: #666;
            font-size: 0.9rem;
        }

        .login-link {
            color: #fa5bdd;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .login-link:hover {
            color: #e640c7;
            text-decoration: underline;
        }

        .error-message {
            color: #dc3545;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            display: none;
        }

        /* Navbar theme adjustments */
        .ftco-navbar-light {
            background: #fff !important;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .ftco-navbar-light .navbar-brand {
            color: #fa5bdd;
        }

        .ftco-navbar-light .nav-link {
            color: #333 !important;
        }

        .ftco-navbar-light .nav-link:hover {
            color: #fa5bdd !important;
        }
    </style>
</head>
<body>
    <!-- Add Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark ftco_navbar bg-dark ftco-navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <span class="flaticon-flower"></span>
                Bellezza
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ftco-nav">
                <span class="oi oi-menu"></span> Menu
            </button>
            <div class="collapse navbar-collapse" id="ftco-nav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item"><a href="index.php" class="nav-link">Home</a></li>
                    <li class="nav-item"><a href="about.php" class="nav-link">About</a></li>
                    <li class="nav-item"><a href="services.php" class="nav-link">Services</a></li>
                    <li class="nav-item"><a href="work.php" class="nav-link">Work</a></li>
                    <li class="nav-item"><a href="blog.php" class="nav-link">Blog</a></li>
                    <li class="nav-item"><a href="contact.php" class="nav-link">Contact</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Your existing forgot password container -->
    <div class="container d-flex justify-content-center align-items-center">
        <div class="forgot-password-container">
            <div class="logo">
                <span class="flaticon-flower"></span>
                <h1>BELLEZZA</h1>
            </div>
            
            <h2>Forgot Password</h2>
            
            <div id="step1" class="step active">
                <input type="email" id="email" placeholder="Enter your email" required>
                <button id="sendOtp">Send OTP</button>
            </div>
            
            <div id="step2" class="step">
                <input type="text" id="otp" placeholder="Enter OTP" maxlength="6" required>
                <button id="verifyOtp">Verify OTP</button>
            </div>
            
            <div id="step3" class="step">
                <input type="password" id="newPassword" placeholder="New Password" required>
                <span id="passwordError" class="error-message"></span>
                <input type="password" id="confirmPassword" placeholder="Confirm Password" required>
                <span id="confirmPasswordError" class="error-message"></span>
                <button id="resetPassword">Reset Password</button>
            </div>
            
            <div class="divider">
                <span>OR</span>
            </div>
            
            <a href="login.php" class="login-link">Back to Login</a>
        </div>
    </div>

    <!-- Add required scripts -->
    <script src="js/jquery.min.js"></script>
    <script src="js/jquery-migrate-3.0.1.min.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery.easing.1.3.js"></script>
    <script src="js/jquery.waypoints.min.js"></script>
    <script src="js/main.js"></script>

    <script>
        $(document).ready(function() {
            // Password validation regex
            var passwordPattern = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

            // Password validation
            $('#newPassword').on('keyup blur', function() {
                var password = $(this).val();
                if (!passwordPattern.test(password)) {
                    $('#passwordError').text('Password must be at least 8 characters long, and include a number, letter, and special character.').show();
                } else {
                    $('#passwordError').hide();
                }
            });

            // Confirm password validation
            $('#confirmPassword').on('keyup blur', function() {
                var confirmPassword = $(this).val();
                var password = $('#newPassword').val();
                if (confirmPassword !== password) {
                    $('#confirmPasswordError').text('Passwords do not match').show();
                } else {
                    $('#confirmPasswordError').hide();
                }
            });

            // Send OTP
            $('#sendOtp').click(function() {
                const email = $('#email').val();
                if(!email) {
                    Swal.fire('Error', 'Please enter your email', 'error');
                    return;
                }

                $(this).prop('disabled', true).text('Sending...');

                $.ajax({
                    url: window.location.href,
                    type: 'POST',
                    data: { check_email: true, email: email },
                    dataType: 'json',
                    success: function(response) {
                        if(response.status === 'success') {
                            $('#step1').removeClass('active');
                            $('#step2').addClass('active');
                            Swal.fire('Success', 'OTP sent to your email', 'success');
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', error);
                        Swal.fire('Error', 'An error occurred while sending OTP. Please try again later.', 'error');
                    },
                    complete: function() {
                        $('#sendOtp').prop('disabled', false).text('Send OTP');
                    }
                });
            });

            // Verify OTP
            $('#verifyOtp').click(function() {
                const otp = $('#otp').val();
                if(!otp) {
                    Swal.fire('Error', 'Please enter OTP', 'error');
                    return;
                }

                $(this).prop('disabled', true).text('Verifying...');

                $.ajax({
                    url: window.location.href,
                    type: 'POST',
                    data: { verify_otp: true, otp: otp },
                    dataType: 'json',
                    success: function(response) {
                        if(response.status === 'success') {
                            $('#step2').removeClass('active');
                            $('#step3').addClass('active');
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    complete: function() {
                        $('#verifyOtp').prop('disabled', false).text('Verify OTP');
                    }
                });
            });

            // Reset Password
            $('#resetPassword').click(function() {
                const newPassword = $('#newPassword').val();
                const confirmPassword = $('#confirmPassword').val();

                if(!newPassword || !confirmPassword) {
                    Swal.fire('Error', 'Please fill all fields', 'error');
                    return;
                }

                if(!passwordPattern.test(newPassword)) {
                    Swal.fire('Error', 'Password must be at least 8 characters long, and include a number, letter, and special character.', 'error');
                    return;
                }

                if(newPassword !== confirmPassword) {
                    Swal.fire('Error', 'Passwords do not match', 'error');
                    return;
                }

                $(this).prop('disabled', true).text('Updating...');

                $.ajax({
                    url: window.location.href,
                    type: 'POST',
                    data: { 
                        reset_password: true, 
                        new_password: newPassword 
                    },
                    dataType: 'json',
                    success: function(response) {
                        if(response.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: 'Password updated successfully',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                window.location.href = 'login.php';
                            });
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    complete: function() {
                        $('#resetPassword').prop('disabled', false).text('Reset Password');
                    }
                });
            });
        });
    </script>
</body>
</html>
