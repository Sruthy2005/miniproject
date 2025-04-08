<?php
session_start();
require_once '../connect.php';

// Check if user is logged in and has admin privileges
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

// Get user name from database
$user_id = $_SESSION['user_id'];
$sql = "SELECT first_name FROM user WHERE id = $user_id";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

$user = mysqli_fetch_assoc($result);
$name = $user['first_name'];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    // Get the user ID from the form
    $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
    
    // Use prepared statement to update the user role
    $sql = "UPDATE user SET role = 'staff' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        // Get user details for notification
        $sql = "SELECT first_name, last_name, email FROM user WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $first_name = $row['first_name'];
            $last_name = $row['last_name'];
            $email = $row['email'];
            
            // Optional: Notify user of role change via email
            // Use PHPMailer\PHPMailer\PHPMailer;
            // Use PHPMailer\PHPMailer\Exception;
            require '../vendor/autoload.php';
            
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
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
                $mail->Subject = "Your Account Has Been Upgraded to Staff";
                $mail->Body = "
                    <p>Dear $first_name $last_name,</p>
                    <p>Your account has been upgraded to staff level access.</p>
                    <p>You now have additional permissions and access to the staff dashboard.</p>
                    <p>If you have any questions about your new role, please contact the administrator.</p>
                    <p>Best regards,<br>
                    Admin Team</p>";
                $mail->AltBody = "Dear $first_name $last_name,\n\n"
                    . "Your account has been upgraded to staff level access.\n\n"
                    . "You now have additional permissions and access to the staff dashboard.\n\n"
                    . "If you have any questions about your new role, please contact the administrator.\n\n"
                    . "Best regards,\n"
                    . "Admin Team";

                $mail->send();
                $_SESSION['success_message'] = "User successfully converted to staff member and notification email sent.";
            } catch (Exception $e) {
                $_SESSION['success_message'] = "User successfully converted to staff member, but notification email failed to send.";
                $_SESSION['error_detail'] = "Email error: {$mail->ErrorInfo}";
            }
        } else {
            $_SESSION['success_message'] = "User successfully converted to staff member.";
        }
        
        header("Location: manage_staff.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Error converting user to staff member: " . mysqli_error($conn);
        header("Location: manage_staff.php");
        exit();
    }
}

// Get all users who are not already staff
$sql = "SELECT id, first_name, last_name, email, role FROM user WHERE role != 'staff' AND role != 'admin' ORDER BY last_name, first_name";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Convert User to Staff - Admin Dashboard</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Belleza&display=swap" rel="stylesheet">
    
    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet" />

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
</head>

<body>
    <div class="container-xxl position-relative bg-white d-flex p-0">
        <!-- Spinner Start -->
        <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
        <!-- Spinner End -->

        <!-- Sidebar Start -->
        <div class="sidebar pe-4 pb-3">
            <nav class="navbar bg-light navbar-light">
                <a href="admin.php" class="navbar-brand mx-4 mb-3">
                    <h3 class="text-primary" style="font-family: 'Belleza', sans-serif;">Admin Panel</h3>
                </a>
                <div class="d-flex align-items-center ms-4 mb-4">
                    <div class="position-relative">
                        <i class="fas fa-user-shield fa-2x"></i>
                        <div class="bg-success rounded-circle border border-2 border-white position-absolute end-0 bottom-0 p-1"></div>
                    </div>
                    <div class="ms-3">
                        <h6 class="mb-0"><?php echo htmlspecialchars($name); ?></h6>
                        <span>Admin</span>
                    </div>
                </div>
                <div class="navbar-nav w-100">
                    <a href="../index.php" class="nav-item nav-link"><i class="fa fa-home me-2"></i>Bellezza</a>
                    <a href="admin.php" class="nav-item nav-link"><i class="fa fa-tachometer-alt me-2"></i>Dashboard</a>
                    <a href="manage_users.php" class="nav-item nav-link"><i class="fa fa-users me-2"></i>Manage Users</a>
                    <a href="manage_appointments.php" class="nav-item nav-link"><i class="fa fa-calendar-check me-2"></i>Appointments</a>
                    <a href="manage_services.php" class="nav-item nav-link"><i class="fa fa-cut me-2"></i>Services</a>
                    <a href="manage_staff.php" class="nav-item nav-link active"><i class="fa fa-user-tie me-2"></i>Staff</a>
                    <a href="view_feedback.php" class="nav-item nav-link"><i class="fa fa-star me-2"></i>Feedback</a>
                    <a href="reports.php" class="nav-item nav-link"><i class="fa fa-chart-bar me-2"></i>Reports</a>
                    <a href="settings.php" class="nav-item nav-link"><i class="fa fa-cog me-2"></i>Settings</a>
                    <a href="../logout.php" class="nav-item nav-link"><i class="fa fa-sign-out-alt me-2"></i>Logout</a>
                </div>
            </nav>
        </div>
        <!-- Sidebar End -->

        <!-- Content Start -->
        <div class="content">
            <!-- Navbar Start -->
            <nav class="navbar navbar-expand bg-light navbar-light sticky-top px-4 py-0">
                <a href="index.html" class="navbar-brand d-flex d-lg-none me-4">
                    <h2 class="text-primary mb-0"><i class="fa fa-hashtag"></i></h2>
                </a>
                <a href="#" class="sidebar-toggler flex-shrink-0">
                    <i class="fa fa-bars"></i>
                </a>
                <form class="d-none d-md-flex ms-4">
                    <input class="form-control border-0" type="search" placeholder="Search">
                </form>
                <div class="navbar-nav align-items-center ms-auto">
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            <?php if (!empty($profile_image)): ?>
                                <img src="<?php echo htmlspecialchars($profile_image_path); ?>" 
                                     alt="Profile" 
                                     class="rounded-circle me-lg-2" 
                                     width="40" height="40" 
                                     style="width:40px; height:40px; object-fit:cover;">
                            <?php else: ?>
                                <i class="fas fa-user-shield fa-2x me-lg-2"></i>
                            <?php endif; ?>
                            <span class="d-none d-lg-inline-flex"><?php echo htmlspecialchars($name); ?></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end bg-light border-0 rounded-0 rounded-bottom m-0">
                            <a href="../profile.php" class="dropdown-item">My Profile</a>
                            <a href="settings.php" class="dropdown-item">Settings</a>
                            <a href="../logout.php" class="dropdown-item">Log Out</a>
                        </div>
                    </div>
            </nav>
            <!-- Navbar End -->

            <!-- Main Content Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="row g-4">
                    <div class="col-12">
                        <div class="bg-light rounded p-4">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <h3 class="mb-0">Convert User to Staff</h3>
                                <a href="manage_staff.php" class="btn btn-sm btn-primary">Back to Staff Management</a>
                            </div>
                            
                            <?php if (isset($_SESSION['error_message'])): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fa fa-exclamation-circle me-2"></i>
                                    <?php 
                                        echo $_SESSION['error_message']; 
                                        unset($_SESSION['error_message']);
                                    ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (isset($_SESSION['success_message'])): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="fa fa-check-circle me-2"></i>
                                    <?php 
                                        echo $_SESSION['success_message']; 
                                        unset($_SESSION['success_message']);
                                    ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($result->num_rows > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col">Name</th>
                                                <th scope="col">Email</th>
                                                <th scope="col">Current Role</th>
                                                <th scope="col">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($row = $result->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                                    <td>
                                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($row['role']); ?></span>
                                                    </td>
                                                    <td>
                                                        <form method="post" action="" onsubmit="return confirm('Are you sure you want to convert this user to a staff member?');">
                                                            <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                                            <button type="submit" class="btn btn-sm btn-primary">
                                                                <i class="fa fa-user-plus me-1"></i> Convert to Staff
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="col-12 text-center py-5">
                                    <i class="fa fa-info-circle fa-3x text-primary mb-3"></i>
                                    <p class="mb-0">No eligible users found to convert to staff members.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Main Content End -->

            <!-- Footer Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="bg-light rounded-top p-4">
                    <div class="row">
                        <div class="col-12 col-sm-6 text-center text-sm-start">
                            &copy; <a href="#">Bellezza Beauty</a>, All Right Reserved. 
                        </div>
                        <div class="col-12 col-sm-6 text-center text-sm-end">
                            Designed By <a href="#">Admin Team</a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Footer End -->
        </div>
        <!-- Content End -->

        <!-- Back to Top -->
        <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/chart/chart.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/tempusdominus/js/moment.min.js"></script>
    <script src="lib/tempusdominus/js/moment-timezone.min.js"></script>
    <script src="lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>
</body>

</html> 