<?php
require_once "connect.php";

if(isset($_POST['email']) && isset($_POST['password'])) {
    $email = $_POST['email'];
    $password = md5($_POST['password']); // Using MD5 to match registration
    
    $query = "SELECT * FROM user WHERE email='$email' AND password='$password'";
    $result = mysqli_query($conn, $query);

    if(mysqli_num_rows($result) == 1) {
        session_start();
        $user = mysqli_fetch_assoc($result);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['profile_image'] = $user['profile_image'] ?? 'images/default-avatar.jpg';
        $_SESSION['role'] = $user['role'];

        // Redirect based on role
        $redirect_url = '';
        switch($user['role']) {
            case 'admin':
                $redirect_url = "admin_dash/admin.php";
                break;
            case 'staff':
                $redirect_url = "staff_dashh/staff_dashboard.php"; 
                break;
            case 'user':
                $redirect_url = "index.php";
                break;
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
                    text: 'Welcome back, <?php echo $user['first_name']; ?>!',
                    icon: 'success',
                    showConfirmButton: false,
                    timer: 1500,
                    timerProgressBar: true
                }).then(function() {
                    window.location.href = "<?php echo $redirect_url; ?>";
                });
            </script>
        </body>
        </html>
        <?php
        exit();
    } else {
        // Show error message for invalid credentials
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
                });
            </script>
        </body>
        </html>
        <?php
    }
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
        }
        .login-header .icon span {
            font-size: 50px;
            color: #fa5bdd;
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
