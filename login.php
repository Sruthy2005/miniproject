<?php
session_start();
require_once "connect.php";

// Debug database connection
if (mysqli_connect_errno()) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Add handling for Google Sign-In
if(isset($_POST['google_signin'])) {
    header('Content-Type: application/json');
    
    try {
        // Get data from Google sign-in
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
        $google_id = filter_var($_POST['google_id'], FILTER_SANITIZE_STRING);
        
        // Check if user exists
        $query = "SELECT * FROM user WHERE email = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        
        if($user) {
            // User exists, log them in
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['profile_image'] = $user['profile_image'] ?? 'images/default-avatar.jpg';
            $_SESSION['role'] = $user['role'];
            
            $redirect_url = '';
            switch($user['role']) {
                case 'admin':
                    $redirect_url = "admin_dash/admin.php";
                    break;
                case 'staff':
                    $redirect_url = "staff_dash/staff_dashboard.php";
                    break;
                case 'user':
                default:
                    $redirect_url = "index.php";
            }
            
            echo json_encode([
                "status" => "success",
                "message" => "Logged in successfully",
                "redirect" => $redirect_url
            ]);
        } else {
            // New user, register them
            $random_password = bin2hex(random_bytes(16));
            $hashed_password = password_hash($random_password, PASSWORD_DEFAULT);
            $default_role = 'user';
            
            $insert_query = "INSERT INTO user (email, first_name, password, google_id, role) VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $insert_query);
            mysqli_stmt_bind_param($stmt, "sssss", $email, $name, $hashed_password, $google_id, $default_role);
            
            if(mysqli_stmt_execute($stmt)) {
                $user_id = mysqli_insert_id($conn);
                $_SESSION['user_id'] = $user_id;
                $_SESSION['profile_image'] = 'images/default-avatar.jpg';
                $_SESSION['role'] = $default_role;
                
                echo json_encode([
                    "status" => "success",
                    "message" => "Account created successfully",
                    "redirect" => "index.php"
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Error creating account"
                ]);
            }
        }
    } catch (Exception $e) {
        echo json_encode([
            "status" => "error",
            "message" => "Error: " . $e->getMessage()
        ]);
    }
    exit();
}

if(isset($_POST['email']) && isset($_POST['password'])) {
    // Sanitize inputs
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    // Debug submitted data
    error_log("Login attempt - Email: " . $email);
    
    // Use prepared statement to prevent SQL injection
    $query = "SELECT * FROM user WHERE email = ?";
    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        error_log("Prepare failed: " . mysqli_error($conn));
        die("Database error");
    }
    
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if($user = mysqli_fetch_assoc($result)) {
        error_log("User found in database");
        error_log("Stored password hash: " . $user['password']);
        error_log("Submitted password: " . $_POST['password']);
        
        // First try password_verify
        if(password_verify($_POST['password'], $user['password'])) {
            error_log("Password verified successfully with password_verify");
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['profile_image'] = $user['profile_image'] ?? 'images/default-avatar.jpg';
            $_SESSION['role'] = $user['role'];
            
            // Debug session
            error_log("Session data set - User ID: " . $_SESSION['user_id'] . ", Role: " . $_SESSION['role']);

            $redirect_url = '';
            switch($user['role']) {
                case 'admin':
                    $redirect_url = "admin_dash/admin.php";
                    break;
                case 'staff':
                    $redirect_url = "staff_dashh/staff_dashboard.php";
                    break;
                case 'user':
                default:
                    $redirect_url = "index.php";
            }
            ?>
            <!DOCTYPE html>
            <html>
            <head>
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            </head>
            <body>
                <script>
                    Swal.fire({
                        title: 'Login Successful!',
                        text: 'Welcome back, <?php echo htmlspecialchars($user['first_name']); ?>!',
                        icon: 'success',
                        showConfirmButton: false,
                        timer: 1500,
                        timerProgressBar: true
                    }).then(function() {
                        window.location.href = "<?php echo htmlspecialchars($redirect_url); ?>";
                    });
                </script>
            </body>
            </html>
            <?php
            exit();
        } 
        // Fallback for MD5 passwords (temporary)
        else if ($user['password'] === md5($_POST['password'])) {
            error_log("Password verified successfully with MD5");
            // Update to new password hashing
            $new_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $update_query = "UPDATE user SET password = ? WHERE id = ?";
            $update_stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($update_stmt, "si", $new_hash, $user['id']);
            mysqli_stmt_execute($update_stmt);
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['profile_image'] = $user['profile_image'] ?? 'images/default-avatar.jpg';
            $_SESSION['role'] = $user['role'];
            
            $redirect_url = '';
            switch($user['role']) {
                case 'admin':
                    $redirect_url = "admin_dash/admin.php";
                    break;
                case 'staff':
                    $redirect_url = "staff_dash/staff_dashboard.php";
                    break;
                case 'user':
                default:
                    $redirect_url = "index.php";
            }
            ?>
            <!DOCTYPE html>
            <html>
            <head>
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            </head>
            <body>
                <script>
                    Swal.fire({
                        title: 'Login Successful!',
                        text: 'Welcome back, <?php echo htmlspecialchars($user['first_name']); ?>!',
                        icon: 'success',
                        showConfirmButton: false,
                        timer: 1500,
                        timerProgressBar: true
                    }).then(function() {
                        window.location.href = "<?php echo htmlspecialchars($redirect_url); ?>";
                    });
                </script>
            </body>
            </html>
            <?php
            exit();
        } else {
            error_log("Password verification failed");
        }
    } else {
        error_log("No user found with email: " . $email);
    }
    
    // Invalid login - show error
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </head>
    <body>
        <script>
            Swal.fire({
                title: 'Error!',
                text: 'Invalid email or password',
                icon: 'error',
                showConfirmButton: true
            }).then(function() {
                window.history.back();
            });
        </script>
    </body>
    </html>
    <?php
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BELLEZZA</title>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="main.js" defer type = "module"></script>
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <script src="https://unpkg.com/jwt-decode/build/jwt-decode.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-migrate/3.3.2/jquery-migrate.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/jquery.magnific-popup.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/magnific-popup.min.css">
    <script type="module">
        import { initializeApp } from 'https://www.gstatic.com/firebasejs/10.8.0/firebase-app.js';
        import { getAuth, GoogleAuthProvider, signInWithPopup } from 'https://www.gstatic.com/firebasejs/10.8.0/firebase-auth.js';
        window.initFirebase = function() {
            const app = initializeApp({
                apiKey: "AIzaSyAJwxki9es9pgM3QEakxnOs5EXsTU2iQxk",
                authDomain: "bellezza-beauty.firebaseapp.com",
                projectId: "bellezza-beauty",
                storageBucket: "bellezza-beauty.appspot.com",
                messagingSenderId: "322155484792",
                appId: "1:322155484792:web:f909a6cfa83082316d7f8d"
            });
            window.auth = getAuth(app);
            window.GoogleAuthProvider = GoogleAuthProvider;
            window.signInWithPopup = signInWithPopup;
        }
    </script>
    <style>
        body {
            overflow: hidden; /* Prevents scrolling */
        }
        
        .hero-wrap {
            height: 100vh;
            min-height: 100vh;
            background-image: url('images/bggg.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed; /* Fixed background */
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 90%;
            position: fixed; /* Fixed position */
            left: 50%;
            top: 55%; /* Adjusted from 50% to move form down slightly */
            transform: translate(-50%, -50%); /* Center the container */
            margin-top: 20px; /* Added margin-top */
            z-index: 1000; /* Ensure it's above other elements */
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
            margin-top: -15px;
        }
        .login-header .icon span {
            font-size: 30px;
            color:#000000;
        }
        .login-header h1 {
            font-size: 24px;
            color: #000;
            margin-top: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .form-control {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            padding: 12px 15px;
            margin-bottom: 15px;
            border-radius: 25px;
        }
        .btn-primary {
            background: #fa5bdd;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            width: 100%;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 1px;
            margin-top: 15px;
        }
        .btn-primary:hover {
            background: #f941d5;
        }
        .register-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
        .register-link a {
            color: #fa5bdd;
            text-decoration: none;
        }
        .forgot-password-link {
            text-align: center;
            margin-top: 10px;
            color: #666;
        }
        .forgot-password-link a {
            color: #fa5bdd;
            text-decoration: none;
        }
        .ftco-navbar-light {
            background: rgba(255, 255, 255, 0.9) !important;
            position: fixed;
            width: 100%;
            z-index: 1001; /* Ensure navbar is above login container */
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .ftco-navbar-light.scrolled {
            background: #fff !important;
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
            <div class="icon"></div>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ftco-nav" aria-controls="ftco-nav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="oi oi-menu"></span> Menu
            </button>

            <div class="collapse navbar-collapse" id="ftco-nav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item"><a href="index.php" class="nav-link">Home</a></li>
                    <li class="nav-item"><a href="about.html" class="nav-link">About</a></li>
                    <li class="nav-item"><a href="services.php" class="nav-link">Services</a></li>
                    <li class="nav-item"><a href="work.php" class="nav-link">Work</a></li>
                    <li class="nav-item"><a href="blog.html" class="nav-link">Blog</a></li>
                    <li class="nav-item"><a href="contact.php" class="nav-link">Contact</a></li>
                    <li class="nav-item active"><a href="login.php" class="nav-link">Login</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="hero-wrap">
        <div class="overlay"></div>
        <div class="container">
            <div class="login-container">
                <div class="login-header">
                    <div class="icon">
                        <span class="flaticon-flower"></span>
                        <h1>BELLEZZA</h1>
                    </div>
                </div>
                <form action="login.php" method="POST">
                    <input type="email" name="email" class="form-control" placeholder="Email" required>
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                    <button type="submit" class="btn btn-primary">Login</button>
                </form>
                <div class="register-link">
                    Don't have an account? <a href="registration.php">Register</a>
                </div>
                <div class="forgot-password-link">
                    <a href="forgot-password.php">Forgot Password?</a>
                </div>
                <div class="google-login-link">
                    <button id="google-login-button" class="btn btn-light w-100 mt-3" style="background: white; border: 1px solid #ddd;">
                        <img src="https://www.google.com/favicon.ico" alt="Google" style="width: 20px; margin-right: 10px;">
                        Login with Google
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="js/jquery.min.js"></script>
    <script src="js/jquery-migrate-3.0.1.min.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery.easing.1.3.js"></script>
    <script src="js/jquery.waypoints.min.js"></script>
    <script src="js/jquery.stellar.min.js"></script>
    <script src="js/owl.carousel.min.js"></script>
    <script src="js/aos.js"></script>
    <script src="js/scrollax.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>
