<?php
session_start();
require_once "../connect.php";

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Get feedback data with pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$items_per_page = 10;
$offset = ($page - 1) * $items_per_page;

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM feedback";
$count_result = $conn->query($count_query);
$total_items = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_items / $items_per_page);

// Get feedback with related data
$query = "SELECT 
            f.*,
            u.first_name, 
            u.last_name,
            b.date,
            b.time,
            s.name as service_name,
            staff.first_name as staff_first_name,
            staff.last_name as staff_last_name
          FROM feedback f
          JOIN user u ON f.user_id = u.id
          JOIN bookings b ON f.booking_id = b.id
          JOIN service s ON b.specific_service = s.id
          LEFT JOIN user staff ON b.staff_member = staff.id
          ORDER BY f.created_at DESC
          LIMIT ? OFFSET ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $items_per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();
$feedback_items = [];

while ($row = $result->fetch_assoc()) {
    $feedback_items[] = $row;
}

// Calculate average rating
$avg_query = "SELECT AVG(rating) as average_rating FROM feedback";
$avg_result = $conn->query($avg_query);
$average_rating = round($avg_result->fetch_assoc()['average_rating'], 1);

// Get rating distribution
$dist_query = "SELECT rating, COUNT(*) as count FROM feedback GROUP BY rating ORDER BY rating";
$dist_result = $conn->query($dist_query);
$rating_distribution = [];

while ($row = $dist_result->fetch_assoc()) {
    $rating_distribution[$row['rating']] = $row['count'];
}

// Fill in missing ratings with 0
for ($i = 1; $i <= 5; $i++) {
    if (!isset($rating_distribution[$i])) {
        $rating_distribution[$i] = 0;
    }
}
ksort($rating_distribution);

// Get user name from database
$user_id = $_SESSION['user_id'];
$sql = "SELECT first_name FROM user WHERE id = $user_id";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

$user = mysqli_fetch_assoc($result);
$name = $user['first_name'];

// Fetch feedback from database
$feedback_sql = "SELECT f.*, u.first_name, u.last_name 
                 FROM feedback f 
                 LEFT JOIN user u ON f.user_id = u.id 
                 ORDER BY f.created_at DESC";
$feedback_result = mysqli_query($conn, $feedback_sql);

// Add CSRF protection
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Feedback - Admin Dashboard</title>
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

    <style>
        .rating-stars {
            color: #FFD700;
        }
        
        .feedback-card {
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .feedback-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .feedback-header {
            background-color: #f8f9fa;
            border-radius: 10px 10px 0 0;
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .rating-bar {
            height: 20px;
            background-color: #e9ecef;
            border-radius: 10px;
            margin-bottom: 10px;
        }
        
        .rating-fill {
            height: 100%;
            background-color: #71caf3;
            border-radius: 10px;
        }
    </style>
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
                    <a href="view_feedback.php" class="nav-item nav-link active"><i class="fa fa-star me-2"></i>Feedback</a>
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

            <!-- Welcome Section Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="row g-4">
                    <div class="col-12">
                        <div class="bg-light rounded d-flex align-items-center justify-content-between p-4">
                            <div>
                                <h3 class="mb-0">Feedback Management</h3>
                                <p class="mb-0 text-muted">Review and manage customer feedback</p>
                            </div>
                            <i class="fa fa-star fa-3x text-primary"></i>
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
                                <p class="mb-2">Average This Month</p>
                                <h6 class="mb-0"><?php echo number_format($average_rating, 1); ?></h6>
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
                        <div class="rating-bar flex-grow-1">
                            <div class="rating-fill" style="width: <?php echo $percentage; ?>%"></div>
                        </div>
                        <span class="ms-2"><?php echo $count; ?></span>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <!-- Rating Distribution End -->

            <!-- Feedback List Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="bg-light rounded p-4">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <h6 class="mb-0">Recent Feedback</h6>
                    </div>
                    <?php if (count($feedback_items) > 0): ?>
                        <?php foreach ($feedback_items as $feedback): ?>
                            <div class="card feedback-card">
                                <div class="feedback-header d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-0"><?php echo htmlspecialchars($feedback['first_name'] . ' ' . $feedback['last_name']); ?></h5>
                                        <small class="text-muted">
                                            <?php 
                                            $date = new DateTime($feedback['created_at']);
                                            echo $date->format('F j, Y g:i A');
                                            ?>
                                        </small>
                                    </div>
                                    <div class="rating-stars">
                                        <?php 
                                        for ($i = 1; $i <= 5; $i++) {
                                            echo $i <= $feedback['rating'] 
                                                ? '<i class="fas fa-star"></i>' 
                                                : '<i class="far fa-star"></i>';
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Service:</strong> <?php echo htmlspecialchars($feedback['service_name']); ?></p>
                                            <p><strong>Staff Member:</strong> 
                                                <?php 
                                                if (!empty($feedback['staff_first_name']) && !empty($feedback['staff_last_name'])) {
                                                    echo htmlspecialchars($feedback['staff_first_name'] . ' ' . $feedback['staff_last_name']);
                                                } else {
                                                    echo 'Not assigned';
                                                }
                                                ?>
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Appointment Date:</strong> 
                                                <?php 
                                                $appt_date = new DateTime($feedback['date']);
                                                echo $appt_date->format('F j, Y');
                                                ?>
                                            </p>
                                            <p><strong>Appointment Time:</strong> 
                                                <?php 
                                                $appt_time = new DateTime($feedback['time']);
                                                echo $appt_time->format('g:i A');
                                                ?>
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($feedback['comments'])): ?>
                                        <div class="mt-3">
                                            <h6>Comments:</h6>
                                            <p class="p-3 bg-light rounded"><?php echo nl2br(htmlspecialchars($feedback['comments'])); ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <nav aria-label="Feedback pagination">
                                <ul class="pagination">
                                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                    
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>
                        
                    <?php else: ?>
                        <div class="alert alert-info">
                            No feedback has been submitted yet.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Feedback List End -->
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