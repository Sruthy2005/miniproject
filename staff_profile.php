<?php
session_start();
require_once "connect.php";

// Get staff ID from URL parameter
$staff_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch staff details
$staff_query = "SELECT * FROM user WHERE id = ? AND role = 'staff' AND status = 'active'";
$stmt = mysqli_prepare($conn, $staff_query);
mysqli_stmt_bind_param($stmt, "i", $staff_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$staff = mysqli_fetch_assoc($result);

// If staff member not found, redirect to index
if (!$staff) {
    header("Location: index.php");
    exit();
}

// Check if staff_posts table exists and fetch staff posts
$staff_posts = [];
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'staff_posts'");
if (mysqli_num_rows($table_check) > 0) {
    $posts_query = "SELECT * FROM staff_posts WHERE staff_id = ? ORDER BY created_at DESC LIMIT 3";
    $posts_stmt = mysqli_prepare($conn, $posts_query);
    if ($posts_stmt) {
        mysqli_stmt_bind_param($posts_stmt, "i", $staff_id);
        mysqli_stmt_execute($posts_stmt);
        $posts_result = mysqli_stmt_get_result($posts_stmt);
        $staff_posts = mysqli_fetch_all($posts_result, MYSQLI_ASSOC);
        mysqli_stmt_close($posts_stmt);
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <title>Staff Profile - <?php echo htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']); ?></title>
    <!-- Include the same header content as index.php -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    
    <!-- Include all CSS files from index.php -->
    <link href="https://fonts.googleapis.com/css?family=Montserrat:300,400,600,700" rel="stylesheet">
    <link rel="stylesheet" href="css/open-iconic-bootstrap.min.css">
    <link rel="stylesheet" href="css/animate.css">
    <link rel="stylesheet" href="css/owl.carousel.min.css">
    <link rel="stylesheet" href="css/owl.theme.default.min.css">
    <link rel="stylesheet" href="css/magnific-popup.css">
    <link rel="stylesheet" href="css/aos.css">
    <link rel="stylesheet" href="css/ionicons.min.css">
    <link rel="stylesheet" href="css/bootstrap-datepicker.css">
    <link rel="stylesheet" href="css/jquery.timepicker.css">
    <link rel="stylesheet" href="css/flaticon.css">
    <link rel="stylesheet" href="css/icomoon.css">
    <link rel="stylesheet" href="css/style.css">
    
    <style>
        .staff-profile {
            padding: 100px 0;
            background: #f8f9fa;
        }
        .profile-image {
            width: 300px;
            height: 300px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 30px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .staff-info {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }
        .booking-section {
            margin-top: 50px;
        }
        .specialties-list {
            list-style: none;
            padding: 0;
        }
        .specialties-list li {
            margin-bottom: 10px;
            padding: 8px 15px;
            background: #f8f9fa;
            border-radius: 20px;
            display: inline-block;
            margin-right: 10px;
        }
    </style>
</head>
<body>

<!-- Include the same navigation as index.php -->
<?php include 'navbar.php'; ?>



<section class="staff-profile">
    <div class="container">
        <div class="row">
            <div class="col-md-4 text-center">
                <img src="<?php 
                    echo !empty($staff['profile_image']) 
                        ? htmlspecialchars($staff['profile_image']) 
                        : 'images/staff/default-profile.jpg'; 
                ?>" class="profile-image" alt="<?php echo htmlspecialchars($staff['first_name']); ?>">
            </div>
            <div class="col-md-8">
                <div class="staff-info">
                    <h2><?php echo htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']); ?></h2>
                    <h4 class="text-primary mb-4"><?php 
                        echo !empty($staff['specialization']) 
                            ? htmlspecialchars($staff['specialization'])
                            : 'Beauty Professional'; 
                    ?></h4>
                    
                    <div class="mb-4">
                        <h5>About</h5>
                        <p><?php 
                            echo !empty($staff['bio']) 
                                ? htmlspecialchars($staff['bio'])
                                : 'A dedicated beauty professional passionate about helping clients look and feel their best.'; 
                        ?></p>
                    </div>
                    
                    <div class="mb-4">
                        <h5>Specialties</h5>
                        <ul class="specialties-list">
                            <?php
                            // You would need to implement a specialties table and relationship
                            // This is just an example
                            $specialties = ['Facial Treatment', 'Makeup', 'Hair Styling'];
                            foreach($specialties as $specialty) {
                                echo '<li>' . htmlspecialchars($specialty) . '</li>';
                            }
                            ?>
                        </ul>
                    </div>
                    
                    <div class="booking-section">
                        <h5 class="mb-4">Book an Appointment</h5>
                        <a href="index.php#appointment-form" class="btn btn-primary py-3 px-4">Schedule Now</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Staff Posts Section -->
<section class="ftco-section bg-light">
    <div class="container">
        <div class="row justify-content-center mb-5">
            <div class="col-md-7 heading-section text-center ftco-animate">
                <h2 class="mb-4">Posts by <?php echo htmlspecialchars($staff['first_name']); ?></h2>
                <p>Read the latest beauty tips, tutorials and insights from <?php echo htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']); ?>.</p>
            </div>
        </div>
        <div class="row">
            <?php if (empty($staff_posts)): ?>
                <div class="col-md-12 text-center">
                    <p>No posts available yet.</p>
                </div>
            <?php else: ?>
                <?php foreach ($staff_posts as $post): ?>
                    <div class="col-md-4 ftco-animate">
                        <div class="blog-entry">
                            <a href="view_post_detail.php?id=<?php echo $post['id']; ?>" class="block-20" style="background-image: url('<?php echo !empty($post['image_path']) ? htmlspecialchars($post['image_path']) : 'images/default-post.jpg'; ?>');">
                            </a>
                            <div class="text p-4">
                                <div class="meta">
                                    <div><a href="#"><?php echo date('M j, Y', strtotime($post['created_at'])); ?></a></div>
                                    <div><span class="badge badge-light"><?php echo htmlspecialchars($post['service_category']); ?></span></div>
                                </div>
                                <h3 class="heading"><a href="view_post_detail.php?id=<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></h3>
                                <p><?php echo htmlspecialchars(substr($post['description'], 0, 100)) . '...'; ?></p>
                                <p><a href="view_post_detail.php?id=<?php echo $post['id']; ?>" class="btn btn-primary">Read more</a></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
    </div>
</section>

<!-- Include footer from index.php -->
<?php include 'footer.php'; ?>

<!-- loader -->
<div id="ftco-loader" class="show fullscreen">
    <svg class="circular" width="48px" height="48px">
        <circle class="path-bg" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke="#eeeeee"/>
        <circle class="path" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke-miterlimit="10" stroke="#F96D00"/>
    </svg>
</div>

<!-- Include all JavaScript files from index.php -->
<script src="js/jquery.min.js"></script>
<script src="js/jquery-migrate-3.0.1.min.js"></script>
<script src="js/popper.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/jquery.easing.1.3.js"></script>
<script src="js/jquery.waypoints.min.js"></script>
<script src="js/jquery.stellar.min.js"></script>
<script src="js/owl.carousel.min.js"></script>
<script src="js/jquery.magnific-popup.min.js"></script>
<script src="js/aos.js"></script>
<script src="js/jquery.animateNumber.min.js"></script>
<script src="js/bootstrap-datepicker.js"></script>
<script src="js/jquery.timepicker.min.js"></script>
<script src="js/scrollax.min.js"></script>
<script src="js/main.js"></script>

</body>
</html> 