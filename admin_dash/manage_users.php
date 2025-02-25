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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token validation failed');
    }
    
    // Handle user status changes
    if (isset($_POST['user_id']) && isset($_POST['action'])) {
        $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
        $action = $_POST['action'];
        
        $new_status = ($action === 'activate') ? 'active' : 'inactive';
        $update_sql = "UPDATE user SET status = '$new_status' WHERE id = '$user_id' AND role = 'user'";
        
        if (!mysqli_query($conn, $update_sql)) {
            die("Update failed: " . mysqli_error($conn));
        }
        
        // Redirect to refresh the page
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Admin Dashboard</title>
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
                    <a href="manage_users.php" class="nav-item nav-link active"><i class="fa fa-users me-2"></i>Manage Users</a>
                    <a href="manage_appointments.php" class="nav-item nav-link"><i class="fa fa-calendar-check me-2"></i>Appointments</a>
                    <a href="manage_services.php" class="nav-item nav-link"><i class="fa fa-cut me-2"></i>Services</a>
                    <a href="manage_staff.php" class="nav-item nav-link"><i class="fa fa-user-tie me-2"></i>Staff</a>
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
                            <i class="fas fa-user-shield fa-2x me-lg-2"></i>
                            <span class="d-none d-lg-inline-flex"><?php echo htmlspecialchars($name); ?></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end bg-light border-0 rounded-0 rounded-bottom m-0">
                            <a href="../profile.php" class="dropdown-item">My Profile</a>
                            <a href="settings.php" class="dropdown-item">Settings</a>
                            <a href="../logout.php" class="dropdown-item">Log Out</a>
                        </div>
                    </div>
                </div>
            </nav>
            <!-- Navbar End -->

            <div class="container-fluid pt-4 px-4">
                <!-- Users Table -->
                <div class="bg-light rounded p-4 mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <h6 class="mb-0">Customer List</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table text-start align-middle table-bordered table-hover mb-0">
                            <thead>
                                <tr class="text-dark">
                                    <th scope="col">ID</th>
                                    <th scope="col">First Name</th>
                                    <th scope="col">Last Name</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Phone</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT * FROM user WHERE role = 'user' ORDER BY id DESC";
                                $result = mysqli_query($conn, $sql);
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['first_name']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['last_name']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['phone']) . "</td>";
                                    $status = isset($row['status']) ? $row['status'] : 'active';
                                    $statusClass = $status === 'active' ? 'success' : 'danger';
                                    $statusText = ucfirst($status);
                                    
                                    // Status column
                                    echo "<td>";
                                    echo "<span class='badge bg-{$statusClass}'>{$statusText}</span>";
                                    echo "</td>";
                                    
                                    // Action column
                                    echo "<td>";
                                    if ($status === 'active') {
                                        echo "<form action='deactivate_user.php' method='POST' style='display: inline;'>";
                                        echo "<input type='hidden' name='csrf_token' value='" . $_SESSION['csrf_token'] . "'>";
                                        echo "<input type='hidden' name='user_id' value='" . htmlspecialchars($row['id']) . "'>";
                                        echo "<button type='submit' class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure you want to deactivate this user?\")'>";
                                        echo "<i class='fa fa-user-times'></i> Deactivate";
                                        echo "</button>";
                                        echo "</form>";
                                    } else {
                                        echo "<form action='activate_user.php' method='POST' style='display: inline;'>";
                                        echo "<input type='hidden' name='csrf_token' value='" . $_SESSION['csrf_token'] . "'>";
                                        echo "<input type='hidden' name='user_id' value='" . htmlspecialchars($row['id']) . "'>";
                                        echo "<button type='submit' class='btn btn-sm btn-success' onclick='return confirm(\"Are you sure you want to activate this user?\")'>";
                                        echo "<i class='fa fa-user-check'></i> Activate";
                                        echo "</button>";
                                        echo "</form>";
                                    }
                                    echo "</td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
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