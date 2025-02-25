<?php
require_once "connect.php";
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


session_start();

if(isset($_POST['submit'])){
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name']; 
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $password = md5($_POST['password']);

    // Generate 6 digit OTP
    $otp = sprintf("%06d", rand(100000, 999999));
    $_SESSION['otp'] = $otp;
    $_SESSION['otp_time'] = time();
    $_SESSION['otp_attempts'] = 0;
    $_SESSION['registration_data'] = [
        'first_name' => $first_name,
        'last_name' => $last_name,
        'phone' => $phone,
        'email' => $email,
        'password' => $password
    ];

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
        $mail->Subject = 'Email Verification OTP';
        
        // Create an HTML email template with modern styling
        $emailTemplate = "
        <div style='background-color: #f6f9fc; padding: 40px 0; font-family: Arial, sans-serif;'>
            <div style='background-color: #ffffff; max-width: 600px; margin: 0 auto; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);'>
                <div style='text-align: center; margin-bottom: 30px;'>
                    <h1 style='color: #1a73e8; margin: 0; font-size: 24px;'>Email Verification</h1>
                </div>
                <div style='color: #4a4a4a; font-size: 16px; line-height: 1.6;'>
                    <p>Hello,</p>
                    <p>Thank you for registering with us. Please use the following OTP to verify your email address:</p>
                    <div style='background-color: #f8f9fa; padding: 15px; border-radius: 6px; text-align: center; margin: 20px 0;'>
                        <span style='font-size: 32px; font-weight: bold; color: #1a73e8; letter-spacing: 5px;'>$otp</span>
                    </div>
                    <p>This OTP will expire in 10 minutes.</p>
                    <p>If you didn't request this verification, please ignore this email.</p>
                </div>
                <div style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #666; font-size: 14px; text-align: center;'>
                    <p>This is an automated message, please do not reply.</p>
                </div>
            </div>
        </div>";
        
        $mail->Body = $emailTemplate;
        $mail->AltBody = "Your OTP for email verification is: $otp";
        
        $mail->send();
        echo json_encode(['status' => 'success', 'message' => 'OTP sent successfully']);
        exit();

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"]);
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - BELLEZZA</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:300,400,600,700" rel="stylesheet" />
    <link rel="stylesheet" href="css/open-iconic-bootstrap.min.css" />
    <link rel="stylesheet" href="css/animate.css" />
    <link rel="stylesheet" href="css/owl.carousel.min.css" />
    <link rel="stylesheet" href="css/owl.theme.default.min.css" />
    <link rel="stylesheet" href="css/magnific-popup.css" />
    <link rel="stylesheet" href="css/aos.css" />
    <link rel="stylesheet" href="css/ionicons.min.css" />
    <link rel="stylesheet" href="css/flaticon.css" />
    <link rel="stylesheet" href="css/icomoon.css" />
    <link rel="stylesheet" href="css/style.css" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            overflow-y: auto;
        }
        
        .hero-wrap {
            height: 100vh;
            min-height: 100vh;
            background-image: url('images/bggg.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            padding-top: 80px;
        }
        
        .registration-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 90%;
            position: relative;
            margin: 0 auto;
            z-index: 1000;
        }
        
        .ftco-navbar-light {
            background: rgba(255, 255, 255, 0.9) !important;
            position: fixed;
            width: 100%;
            z-index: 1001;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .ftco-navbar-light .navbar-brand {
            color: #000 !important;
        }
        
        .ftco-navbar-light .nav-link {
            color: #000 !important;
        }
        
        .ftco-navbar-light .nav-item.active .nav-link {
            color: #fa5bdd !important;
        }

        .form-control {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            padding: 12px 15px;
            margin-bottom: 15px;
            border-radius: 25px;
            width: 100%;
        }

        button[type="submit"] {
            background: #fa5bdd;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            width: 100%;
            color: #fff;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 1px;
            margin-top: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        button[type="submit"]:hover {
            background: #f941d5;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
            text-decoration: none;
            display: block;
        }

        .login-link:hover {
            color: #fa5bdd;
        }

        .registration-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .registration-header .icon span {
            font-size: 50px;
            color: #fa5bdd;
        }

        .registration-header h1 {
            font-size: 24px;
            color: #000;
            margin-top: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .error-message {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: -10px;
            margin-bottom: 10px;
            margin-left: 15px;
            display: block;
        }

        .divider {
            text-align: center;
            margin: 20px 0;
            color: #666;
        }

        .divider span {
            padding: 0 15px;
            background: #fff;
            position: relative;
            z-index: 1;
        }

        .divider:before {
            content: "";
            display: block;
            width: 100%;
            height: 1px;
            background: #e9ecef;
            position: absolute;
            top: 50%;
            z-index: 0;
        }

        /* Add these new styles for the form layout */
        .form-row {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 15px;
        }

        .form-group {
            flex: 1;
            position: relative;
        }

        .form-group .form-control {
            margin-bottom: 5px;
        }

        .form-group .error-message {
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark ftco_navbar bg-dark ftco-navbar-light" id="ftco-navbar">
        <div class="container">
            <a href="index.php" class="logo">
                <span class="flaticon-flower" style="color: black"></span>
            </a>
            <a class="navbar-brand" href="index.php">BELLEZZA</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ftco-nav" aria-controls="ftco-nav" aria-expanded="false" aria-label="Toggle navigation">
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
                    <li class="nav-item active"><a href="register.php" class="nav-link">Register</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="hero-wrap">
        <div class="overlay"></div>
        <div class="registration-container">
            <div class="registration-header">
                <div class="icon">
                    <span class="flaticon-flower"></span>
                    <h1>BELLEZZA</h1>
                </div>
            </div>
            <form action="#" method="POST" id="registrationForm">
                <div class="form-row">
                    <div class="form-group">
                        <input type="text" name="first_name" placeholder="First Name" id="firstName" class="form-control" required />
                        <span class="error-message" id="firstNameError"></span>
                    </div>
                    <div class="form-group">
                        <input type="text" name="last_name" placeholder="Last Name" id="lastName" class="form-control" required />
                        <span class="error-message" id="lastNameError"></span>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <input type="tel" name="phone" placeholder="Phone Number" id="phone" class="form-control" required />
                        <span class="error-message" id="phoneError"></span>
                    </div>
                    <div class="form-group">
                        <input type="email" name="email" placeholder="Email" id="email" class="form-control" required />
                        <span class="error-message" id="emailError"></span>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <input type="password" name="password" placeholder="Password" id="password" class="form-control" required />
                        <span class="error-message" id="passwordError"></span>
                    </div>
                    <div class="form-group">
                        <input type="password" name="confirm_password" placeholder="Confirm Password" id="confirmPassword" class="form-control" required />
                        <span class="error-message" id="confirmPasswordError"></span>
                    </div>
                </div>

                <button type="submit" name="submit" id="submit">Register</button>
            </form>

            <div class="divider">
                <span>OR</span>
            </div>

            <a href="login.php" class="login-link">Already have an account? Login here</a>
        </div>
    </div>

    <script>
      $(document).ready(function () {
        // Update the name patterns
        var firstNamePattern = /^[A-Za-z]{3,}$/;  // First name - only letters, minimum 3 characters
        var lastNamePattern = /^[A-Za-z]+(?: [A-Za-z]+)*$/;  // Last name - letters with optional spaces between words

        // Email validation regex for common email providers
        var emailPattern = /^[a-zA-Z0-9._-]+@(gmail\.com|yahoo\.com)$/;

        // Password validation regex
        var passwordPattern =
          /^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

        // Phone validation regex - starts with 6,7,8,9 and has 10 digits
        var phonePattern = /^[6-9]\d{9}$/;

        // Name validation
        $("#firstName").on("keyup blur", function () {
            var value = $(this).val();
            if (!firstNamePattern.test(value)) {
                $("#firstNameError").text("First name must be at least 3 letters long and contain only letters");
            } else {
                $("#firstNameError").text("");
            }
        });

        $("#lastName").on("keyup blur", function () {
            var value = $(this).val();
            if (!lastNamePattern.test(value)) {
                $("#lastNameError").text("Last name must contain only letters and spaces");
            } else if (value.trim().length < 1) {
                $("#lastNameError").text("Last name must be at least 3 letters long");
            } else {
                $("#lastNameError").text("");
            }
        });

        // Phone validation
        $("#phone").on("keyup blur", function () {
          var phone = $(this).val();

          // Remove any non-digit characters
          phone = phone.replace(/\D/g, "");
          $(this).val(phone);

          if (!phonePattern.test(phone)) {
            $("#phoneError").text(
              "Please enter a valid 10-digit phone number starting with 6,7,8 or 9"
            );
          } else {
            // Check for more than 4 consecutive same digits
            var consecutiveCount = 1;
            var prevDigit = phone[0];
            var hasMoreThan4Consecutive = false;

            for (var i = 1; i < phone.length; i++) {
              if (phone[i] === prevDigit) {
                consecutiveCount++;
                if (consecutiveCount > 4) {
                  hasMoreThan4Consecutive = true;
                  break;
                }
              } else {
                consecutiveCount = 1;
                prevDigit = phone[i];
              }
            }

            // Check if all digits are same
            var allSameDigits = phone
              .split("")
              .every((digit) => digit === phone[0]);

            if (hasMoreThan4Consecutive) {
              $("#phoneError").text(
                "Phone number cannot have more than 4 consecutive same digits"
              );
            } else if (allSameDigits) {
              $("#phoneError").text("Phone number cannot have all same digits");
            } else {
              $("#phoneError").text("");
            }
          }
        });

        // Email validation on keyup and blur
        $("#email").on("keyup blur", function () {
          var email = $(this).val();
          if (!emailPattern.test(email)) {
            $("#emailError").text(
              "Please enter a valid email (Gmail or Yahoo only)."
            );
          } else {
            // Check if email exists in database
            $.ajax({
              url: "check_email.php",
              type: "POST",
              data: { email: email },
              success: function (response) {
                if (response == "exists") {
                  $("#emailError").text("This email is already registered.");
                } else {
                  $("#emailError").text("");
                }
              },
            });
          }
        });

        // Password validation on keyup and blur
        $("#password").on("keyup blur", function () {
          var password = $(this).val();
          if (!passwordPattern.test(password)) {
            $("#passwordError").text(
              "Password must be at least 8 characters long, and include a number, letter, and special character."
            );
          } else {
            $("#passwordError").text("");
          }
        });

        // Confirm Password validation on keyup and blur
        $("#confirmPassword").on("keyup blur", function () {
          var confirmPassword = $(this).val();
          var password = $("#password").val();
          if (confirmPassword !== password) {
            $("#confirmPasswordError").text("Passwords do not match.");
          } else {
            $("#confirmPasswordError").text("");
          }
        });

        // Form submission
        $("#registrationForm").on("submit", function (e) {
          e.preventDefault();
          
          var firstName = $("#firstName").val();
          var lastName = $("#lastName").val();
          var phone = $("#phone").val();
          var email = $("#email").val();
          var password = $("#password").val();
          var confirmPassword = $("#confirmPassword").val();

          // Check if there are any validation errors
          if (
            $("#firstNameError").text() ||
            $("#lastNameError").text() ||
            $("#phoneError").text() ||
            $("#emailError").text() ||
            $("#passwordError").text() ||
            $("#confirmPasswordError").text()
          ) {
            Swal.fire({
              icon: "error",
              title: "Oops...",
              text: "Please fix the errors before submitting.",
            });
            return; // Stop execution if there are errors
          } 
          
          if (!firstName || !lastName || !phone || !email || !password || !confirmPassword) {
            Swal.fire({
              icon: "warning",
              title: "Incomplete fields!",
              text: "Please fill in all fields.",
            });
            return; // Stop execution if fields are empty
          }

          // If validation passes, send registration data
          $.ajax({
            url: window.location.pathname,
            type: "POST",
            data: {
              submit: true,
              first_name: firstName,
              last_name: lastName,
              phone: phone,
              email: email,
              password: password
            },
            dataType: "json",
            success: function(response) {
              if(response.status === 'success') {
                // Show OTP popup
                Swal.fire({
                  title: 'Enter OTP',
                  text: 'Please enter the OTP sent to your email',
                  input: 'text',
                  inputAttributes: {
                    autocapitalize: 'off',
                    maxlength: 6
                  },
                  showCancelButton: true,
                  confirmButtonText: 'Verify',
                  showLoaderOnConfirm: true,
                  preConfirm: (otp) => {
                    return fetch('./verify_otp.php', {
                      method: 'POST',
                      headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                      },
                      body: `otp=${otp}`
                    })
                    .then(response => response.json())
                    .catch(error => {
                      Swal.showValidationMessage(`Request failed: ${error}`)
                    })
                  },
                  allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                  if (result.isConfirmed && result.value.success) {
                    Swal.fire({
                      icon: 'success',
                      title: 'Success!',
                      text: 'Account created successfully!',
                      showConfirmButton: false,
                      timer: 1500
                    }).then(() => {
                      window.location.href = 'login.php';
                    });
                  } else if (result.isConfirmed) {
                    Swal.fire({
                      icon: 'error',
                      title: 'Error',
                      text: result.value.message
                    });
                  }
                });
              } else {
                Swal.fire({
                  icon: 'error',
                  title: 'Error',
                  text: response.message
                });
              }
            },
            error: function(xhr, status, error) {
              console.log(xhr.responseText);
              Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while processing your request.'
              });
            }
          });
        });
      });
    </script>
</body>
</html>
