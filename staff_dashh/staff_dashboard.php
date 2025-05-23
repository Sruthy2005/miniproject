<?php
session_start();
require_once '../connect.php';

// Ensure only authenticated staff can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header('Location: login.php');
    exit();
}

$staff_id = $_SESSION['user_id'];

// Get staff details from database
$query = "SELECT id, CONCAT(first_name, ' ', last_name) as name, role FROM user WHERE id = ? AND role = 'staff'";
$stmt = mysqli_prepare($conn, $query);
if ($stmt === false) {
    die('Error preparing statement: ' . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $staff_id);
if (!mysqli_stmt_execute($stmt)) {
    die('Error executing statement: ' . mysqli_stmt_error($stmt));
}
$result = mysqli_stmt_get_result($stmt);
$staff = mysqli_fetch_assoc($result);

if (!$staff) {
    header('Location: login.php');
    exit();
}

// Add fallback for name
$staff_name = htmlspecialchars($staff['name'] ?? 'Staff Member');
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Staff Dashboard</title>
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
    <script type="text/javascript" src="https://rawgit.com/schmich/instascan-builds/master/instascan.min.js"></script>
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
                    <a href="staff_dashboard.php" class="nav-item nav-link active"><i class="fa fa-tachometer-alt me-2"></i>Dashboard</a>
                    <a href="my_appointments.php" class="nav-item nav-link"><i class="fa fa-calendar-check me-2"></i>My Appointments</a>
                    <a href="view_feedback.php" class="nav-item nav-link"><i class="fa fa-comments me-2"></i>View Feedback</a>
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
                            <i class="fas fa-user-tie fa-2x me-lg-2"></i>
                            <span class="d-none d-lg-inline-flex"><?php echo $staff_name; ?></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end bg-light border-0 rounded-0 rounded-bottom m-0">
                            <a href="../profile.php" class="dropdown-item">My Profile</a>
                            <a href="view_feedback.php" class="dropdown-item">View Feedback</a>
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
                                <h3 class="mb-0">Welcome, <?php echo $staff_name; ?>!</h3>
                                <p class="mb-0 text-muted">Here's an overview of your appointments and schedule</p>
                            </div>
                            <i class="fa fa-calendar-check fa-3x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Welcome Section End -->

            <!-- Staff Posts Section -->
            <div class="container-fluid pt-4 px-4">
                <div class="bg-light rounded p-4">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <h3 class="mb-0">My Recent Posts</h3>
                        <a href="my_posts.php" class="btn btn-sm btn-primary">Manage All Posts</a>
                    </div>
                    
                    <div class="row g-4">
                        <?php
                        // Get recent posts by this staff member
                        $posts_query = "SELECT * FROM staff_posts WHERE staff_id = ? ORDER BY created_at DESC LIMIT 3";
                        $stmt = $conn->prepare($posts_query);
                        $stmt->bind_param("i", $staff_id);
                        $stmt->execute();
                        $posts_result = $stmt->get_result();
                        
                        if ($posts_result->num_rows > 0):
                            while ($post = $posts_result->fetch_assoc()):
                        ?>
                            <div class="col-md-4">
                                <div class="card h-100">
                                    <?php if (!empty($post['image_path'])): ?>
                                        <img src="../<?php echo htmlspecialchars($post['image_path']); ?>" class="card-img-top" style="height: 200px; object-fit: cover;" alt="<?php echo htmlspecialchars($post['title']); ?>">
                                    <?php else: ?>
                                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                            <i class="fas fa-image fa-3x text-secondary"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($post['title']); ?></h5>
                                        <span class="badge bg-primary mb-2"><?php echo htmlspecialchars($post['service_category']); ?></span>
                                        <p class="card-text text-truncate"><?php echo htmlspecialchars($post['description']); ?></p>
                                        <div class="d-flex justify-content-between align-items-center mt-3">
                                            <small class="text-muted"><?php echo date('M d, Y', strtotime($post['created_at'])); ?></small>
                                            <a href="../view_post_detail.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-outline-primary" target="_blank">View</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php 
                            endwhile;
                        else:
                        ?>
                            <div class="col-12 text-center py-3">
                                <p class="text-muted mb-2">You haven't created any posts yet</p>
                                <a href="my_posts.php" class="btn btn-primary">Create Your First Post</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
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