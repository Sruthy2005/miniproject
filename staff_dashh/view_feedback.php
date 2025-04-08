<?php
session_start();
require_once "../connect.php";

// Check if user is logged in and is staff
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get staff name
$staff_query = "SELECT CONCAT(first_name, ' ', last_name) as name FROM user WHERE id = ? AND role = 'staff'";
$stmt = mysqli_prepare($conn, $staff_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$staff_result = mysqli_stmt_get_result($stmt);
$staff_row = mysqli_fetch_assoc($staff_result);
$staff_name = htmlspecialchars($staff_row['name'] ?? 'Staff Member');

// Fetch all feedback for this staff member
$query = "SELECT 
            f.*,
            b.date,
            b.time,
            b.specific_service,
            b.service_category,
            u.first_name as client_first_name,
            u.last_name as client_last_name
          FROM feedback f
          JOIN bookings b ON f.booking_id = b.id
          JOIN user u ON b.user_id = u.id
          WHERE b.staff_member = ?
          ORDER BY b.date DESC, b.time DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Calculate feedback statistics
$total_items = $result->num_rows;
$rating_distribution = array_fill(1, 5, 0); // Initialize array with zeros
$total_rating = 0;

// Store results in array since we'll need to loop through them twice
$feedbacks = [];
while ($row = $result->fetch_assoc()) {
    $feedbacks[] = $row;
    $rating = (int)$row['rating'];
    $rating_distribution[$rating]++;
    $total_rating += $rating;
}

// Calculate average rating
$average_rating = $total_items > 0 ? number_format($total_rating / $total_items, 1) : 0;

// Reset result pointer
$result = new ArrayObject($feedbacks);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>View Feedback</title>
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
                <a href="../index.php" class="navbar-brand mx-4 mb-3">
                    <h3 class="text-primary" style="font-family: 'Belleza', sans-serif;">Staff Portal</h3>
                </a>
                <div class="d-flex align-items-center ms-4 mb-4">
                    <div class="position-relative">
                        <i class="fas fa-user-tie fa-2x"></i>
                        <div class="bg-success rounded-circle border border-2 border-white position-absolute end-0 bottom-0 p-1"></div>
                    </div>
                    <div class="ms-3">
                        <h6 class="mb-0"><?php echo $staff_name; ?></h6>
                        <span>Beauty Professional</span>
                    </div>
                </div>
                <div class="navbar-nav w-100">
                    <a href="../index.php" class="nav-item nav-link"><i class="fa fa-home me-2"></i>Home</a>
                    <a href="staff_dashboard.php" class="nav-item nav-link"><i class="fa fa-tachometer-alt me-2"></i>Dashboard</a>
                    <a href="my_appointments.php" class="nav-item nav-link"><i class="fa fa-calendar-check me-2"></i>My Appointments</a>
                    <a href="view_feedback.php" class="nav-item nav-link active"><i class="fa fa-comments me-2"></i>View Feedback</a>
                    <a href="../profile.php" class="nav-item nav-link"><i class="fa fa-user me-2"></i>My Profile</a>
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
                <form class="d-none d-md-flex ms-4">
                    <input class="form-control border-0" type="search" placeholder="Search">
                </form>
                <div class="navbar-nav align-items-center ms-auto">
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-user-tie fa-2x me-lg-2"></i>
                            <span class="d-none d-lg-inline-flex"><?php echo $staff_name; ?></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end bg-light border-0 rounded-0 rounded-bottom m-0">
                            <a href="../profile.php" class="dropdown-item">My Profile</a>
                            <a href="../logout.php" class="dropdown-item">Log Out</a>
                        </div>
                    </div>
                </div>
            </nav>
            <!-- Navbar End -->

            <!-- Welcome Section Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="row g-4">
                    <div class="col-12">
                        <div class="bg-light rounded d-flex align-items-center justify-content-between p-4">
                            <div>
                                <h3 class="mb-0">My Feedback Overview</h3>
                                <p class="mb-0 text-muted">Review feedback from your clients</p>
                            </div>
                            <i class="fa fa-comments fa-3x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Welcome Section End -->

            <!-- Feedback Summary Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="row g-4">
                    <div class="col-sm-6 col-xl-3">
                        <div class="bg-light rounded d-flex align-items-center justify-content-between p-4">
                            <i class="fa fa-star fa-3x text-primary"></i>
                            <div class="ms-3">
                                <p class="mb-2">Average Rating</p>
                                <h6 class="mb-0"><?php echo $average_rating; ?> / 5.0</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="bg-light rounded d-flex align-items-center justify-content-between p-4">
                            <i class="fa fa-comments fa-3x text-primary"></i>
                            <div class="ms-3">
                                <p class="mb-2">Total Feedback</p>
                                <h6 class="mb-0"><?php echo $total_items; ?></h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="bg-light rounded d-flex align-items-center justify-content-between p-4">
                            <i class="fa fa-smile fa-3x text-primary"></i>
                            <div class="ms-3">
                                <p class="mb-2">5-Star Ratings</p>
                                <h6 class="mb-0"><?php echo $rating_distribution[5] ?? 0; ?></h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="bg-light rounded d-flex align-items-center justify-content-between p-4">
                            <i class="fa fa-chart-bar fa-3x text-primary"></i>
                            <div class="ms-3">
                                <p class="mb-2">Recent Rating</p>
                                <h6 class="mb-0"><?php echo $feedbacks[0]['rating'] ?? 0; ?> / 5.0</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Feedback Summary End -->

            <!-- Rating Distribution Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="bg-light rounded p-4">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <h6 class="mb-0">Rating Distribution</h6>
                    </div>
                    <?php 
                    $max_count = max($rating_distribution);
                    foreach ($rating_distribution as $rating => $count) {
                        $percentage = $max_count > 0 ? ($count / $max_count) * 100 : 0;
                    ?>
                    <div class="d-flex align-items-center mb-2">
                        <span class="me-2"><?php echo $rating; ?> ★</span>
                        <div class="progress flex-grow-1" style="height: 10px;">
                            <div class="progress-bar bg-primary" role="progressbar" 
                                 style="width: <?php echo $percentage; ?>%" 
                                 aria-valuenow="<?php echo $percentage; ?>" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100"></div>
                        </div>
                        <span class="ms-2"><?php echo $count; ?></span>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <!-- Rating Distribution End -->

            <!-- Feedback Section Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="bg-light rounded p-4">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <h3 class="mb-0">Client Feedback</h3>
                    </div>
                    <?php if (!empty($feedbacks)): ?>
                        <?php foreach ($feedbacks as $feedback): ?>
                            <div class="bg-white rounded p-3 mb-3 shadow-sm">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">
                                        <?php echo htmlspecialchars($feedback['client_first_name'] . ' ' . $feedback['client_last_name']); ?>
                                    </h5>
                                    <span class="text-muted">
                                        <?php echo date('M d, Y h:i A', strtotime($feedback['date'] . ' ' . $feedback['time'])); ?>
                                    </span>
                                </div>
                                <div class="mt-2">
                                    <strong>Service:</strong> 
                                    <?php echo htmlspecialchars($feedback['specific_service']); ?>
                                    <small class="text-muted">(<?php echo htmlspecialchars($feedback['service_category']); ?>)</small>
                                </div>
                                <div class="mt-2">
                                    <strong>Rating:</strong>
                                    <span class="text-warning">
                                        <?php
                                        for ($i = 1; $i <= 5; $i++) {
                                            echo $i <= $feedback['rating'] ? '★' : '☆';
                                        }
                                        ?>
                                    </span>
                                </div>
                                <?php if (!empty($feedback['comments'])): ?>
                                    <div class="mt-3 p-3 bg-light rounded">
                                        <?php echo nl2br(htmlspecialchars($feedback['comments'])); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-comment-slash fa-3x text-muted mb-3"></i>
                            <h4>No Feedback Yet</h4>
                            <p class="text-muted">You haven't received any feedback from clients yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Feedback Section End -->
        </div>
        <!-- Content End -->

        <!-- Back to Top -->
        <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
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
