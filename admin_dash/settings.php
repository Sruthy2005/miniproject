<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

// Get user name from database
require_once '../connect.php';
$user_id = $_SESSION['user_id'];
$sql = "SELECT first_name FROM user WHERE id = $user_id";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

$user = mysqli_fetch_assoc($result);
$name = $user['first_name'];

// Add CSRF protection
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token validation failed');
    }
    
    // Process settings update here
    // You can add database updates for the settings
    $_SESSION['settings_updated'] = true;
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Settings - Admin Dashboard</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    
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
                    <a href="manage_staff.php" class="nav-item nav-link"><i class="fa fa-user-tie me-2"></i>Staff</a>
                    <a href="view_feedback.php" class="nav-item nav-link"><i class="fa fa-comments me-2"></i>View Feedback</a>
                    <a href="reports.php" class="nav-item nav-link"><i class="fa fa-chart-bar me-2"></i>Reports</a>
                    <a href="settings.php" class="nav-item nav-link active"><i class="fa fa-cog me-2"></i>Settings</a>
                    <a href="../logout.php" class="nav-item nav-link"><i class="fa fa-sign-out-alt me-2"></i>Logout</a>
                </div>
            </nav>
        </div>
        <!-- Sidebar End -->

        <!-- Content Start -->
        <div class="content">
            <!-- Navbar Start -->
            <nav class="navbar navbar-expand bg-light navbar-light sticky-top px-4 py-0">
                <a href="#" class="sidebar-toggler flex-shrink-0">
                    <i class="fa fa-bars"></i>
                </a>
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

            <!-- Settings Start -->
            <div class="container-fluid pt-4 px-4">
                <?php if (isset($_SESSION['settings_updated'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        Settings updated successfully!
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['settings_updated']); ?>
                <?php endif; ?>

                <div class="row g-4">
                    <!-- General Settings -->
                    <div class="col-12">
                        <div class="bg-light rounded h-100 p-4">
                            <h6 class="mb-4">General Settings</h6>
                            <form method="POST" action="save_general_settings.php">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                
                                <div class="mb-3">
                                    <label for="siteName" class="form-label">Site Name</label>
                                    <input type="text" class="form-control" id="siteName" name="siteName" 
                                           value="<?php echo isset($_SESSION['siteName']) ? htmlspecialchars($_SESSION['siteName']) : 'Bellezza'; ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="siteEmail" class="form-label">Contact Email</label>
                                    <input type="email" class="form-control" id="siteEmail" name="siteEmail"
                                           value="<?php echo isset($_SESSION['siteEmail']) ? htmlspecialchars($_SESSION['siteEmail']) : ''; ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="sitePhone" class="form-label">Contact Phone</label>
                                    <input type="tel" class="form-control" id="sitePhone" name="sitePhone"
                                           value="<?php echo isset($_SESSION['sitePhone']) ? htmlspecialchars($_SESSION['sitePhone']) : ''; ?>">
                                </div>

                                <div class="mb-3">
                                    <label for="businessHours" class="form-label">Business Hours</label>
                                    <textarea class="form-control" id="businessHours" name="businessHours" rows="3"><?php echo isset($_SESSION['businessHours']) ? htmlspecialchars($_SESSION['businessHours']) : ''; ?></textarea>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Save General Settings</button>
                            </form>
                        </div>
                    </div>

                    <!-- Security Settings -->
                    <div class="col-12">
                        <div class="bg-light rounded h-100 p-4">
                            <h6 class="mb-4">Security Settings</h6>
                            <form method="POST" action="save_security_settings.php">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="twoFactorAuth" name="twoFactorAuth" 
                                            <?php echo isset($_SESSION['twoFactorAuth']) && $_SESSION['twoFactorAuth'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="twoFactorAuth">Enable Two-Factor Authentication</label>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="loginNotifications" name="loginNotifications"
                                            <?php echo isset($_SESSION['loginNotifications']) && $_SESSION['loginNotifications'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="loginNotifications">Email Notifications on Login</label>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Save Security Settings</button>
                            </form>
                        </div>
                    </div>

                    <!-- Notification Settings -->
                    <div class="col-12">
                        <div class="bg-light rounded h-100 p-4">
                            <h6 class="mb-4">Notification Settings</h6>
                            <form method="POST" action="save_notification_settings.php">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="newBookingNotif" name="newBookingNotif"
                                            <?php echo isset($_SESSION['newBookingNotif']) && $_SESSION['newBookingNotif'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="newBookingNotif">New Booking Notifications</label>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="cancelBookingNotif" name="cancelBookingNotif"
                                            <?php echo isset($_SESSION['cancelBookingNotif']) && $_SESSION['cancelBookingNotif'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="cancelBookingNotif">Booking Cancellation Notifications</label>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="newUserNotif" name="newUserNotif"
                                            <?php echo isset($_SESSION['newUserNotif']) && $_SESSION['newUserNotif'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="newUserNotif">New User Registration Notifications</label>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Save Notification Settings</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Settings End -->
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