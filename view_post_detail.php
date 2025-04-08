<?php
require_once "connect.php";
session_start(); // Start the session to access login variables

// Check login status
$is_logged_in = isset($_SESSION['user_id']) ? true : false;
$user_id = $is_logged_in ? $_SESSION['user_id'] : 0;

// Get post ID from URL
$post_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch post with staff details
$post_query = "SELECT p.*, CONCAT(u.first_name, ' ', u.last_name) AS staff_name, 
               u.id AS staff_id, u.profile_image, u.specialization, u.bio
               FROM staff_posts p 
               JOIN user u ON p.staff_id = u.id 
               WHERE p.id = ? AND u.status = 'active'";

$stmt = $conn->prepare($post_query);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();

// If post not found, redirect to gallery
if ($result->num_rows === 0) {
    header("Location: view_staff_posts.php");
    exit();
}

$post = $result->fetch_assoc();

// Get likes count for this post
$likes_query = "SELECT COUNT(*) as total FROM post_likes WHERE post_id = ?";
$stmt = $conn->prepare($likes_query);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$likes_result = $stmt->get_result();
$likes_count = $likes_result->fetch_assoc()['total'];

// Check if current user has liked this post
$user_liked = false;
if ($is_logged_in) {
    $user_like_query = "SELECT * FROM post_likes WHERE post_id = ? AND user_id = ?";
    $stmt = $conn->prepare($user_like_query);
    $stmt->bind_param("ii", $post_id, $user_id);
    $stmt->execute();
    $user_like_result = $stmt->get_result();
    $user_liked = ($user_like_result->num_rows > 0);
}

// Format date
$post_date = date('F j, Y', strtotime($post['created_at']));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo htmlspecialchars($post['title']); ?> - Bellezza</title>
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
        .post-image-container {
            max-height: 500px;
            overflow: hidden;
            margin-bottom: 30px;
        }
        
        .post-image {
            width: 100%;
            object-fit: contain;
            max-height: 500px;
        }
        
        .staff-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-top: 30px;
        }
        
        .staff-image {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .likes-badge {
            background: #f8f9fa;
            padding: 5px 15px;
            border-radius: 20px;
            display: inline-block;
        }
        
        .category-badge {
            background: #f1eaff;
            color: #6c5ce7;
            padding: 5px 15px;
            border-radius: 20px;
            display: inline-block;
            margin-bottom: 15px;
        }
        
        .like-btn {
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            color: #6c757d;
            border: none;
            background: transparent;
            transition: all 0.2s;
            padding: 8px 15px;
            border-radius: 20px;
        }
        
        .like-btn.liked {
            color: #e3342f;
        }
        
        .like-btn:hover {
            background: #f8f9fa;
        }
        
        .like-btn i {
            margin-right: 5px;
            font-size: 18px;
        }
        
        .like-count {
            font-size: 14px;
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<section class="ftco-section">
    <div class="container">
        <div class="row">
            <div class="col-md-8">
                <!-- Post Image -->
                <?php if (!empty($post['image_path'])): ?>
                <div class="post-image-container">
                    <img src="<?php echo htmlspecialchars($post['image_path']); ?>" class="post-image" alt="<?php echo htmlspecialchars($post['title']); ?>">
                </div>
                <?php endif; ?>
                
                <!-- Post Information -->
                <div class="post-content">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="category-badge">
                            <?php echo htmlspecialchars($post['service_category']); ?>
                        </span>
                        
                        <div class="likes-section">
                            <?php if ($is_logged_in): ?>
                                <button class="like-btn <?php echo $user_liked ? 'liked' : ''; ?>" id="likeBtn" data-post-id="<?php echo $post_id; ?>">
                                    <i class="icon-heart<?php echo $user_liked ? '' : '-o'; ?>"></i>
                                    <span class="like-count"><?php echo $likes_count; ?></span>
                                </button>
                            <?php else: ?>
                                <a href="login.php?redirect=view_post_detail.php?id=<?php echo $post_id; ?>" class="like-btn">
                                    <i class="icon-heart-o"></i>
                                    <span class="like-count"><?php echo $likes_count; ?></span>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <h1><?php echo htmlspecialchars($post['title']); ?></h1>
                    
                    <div class="mb-4">
                        <small class="text-muted">Posted on <?php echo $post_date; ?></small>
                    </div>
                    
                    <div class="description mb-5">
                        <?php echo nl2br(htmlspecialchars($post['description'])); ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <!-- Staff Information -->
                <div class="staff-card">
                    <div class="d-flex align-items-center mb-3">
                        <?php if (!empty($post['profile_image'])): ?>
                            <img src="<?php echo htmlspecialchars($post['profile_image']); ?>" class="staff-image mr-3" alt="<?php echo htmlspecialchars($post['staff_name']); ?>">
                        <?php else: ?>
                            <div class="staff-image mr-3 bg-secondary d-flex align-items-center justify-content-center">
                                <i class="icon-user text-light"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div>
                            <h5 class="mb-1"><?php echo htmlspecialchars($post['staff_name']); ?></h5>
                            <p class="text-primary mb-0">
                                <?php echo !empty($post['specialization']) ? htmlspecialchars($post['specialization']) : 'Beauty Professional'; ?>
                            </p>
                        </div>
                    </div>
                    
                    <?php if (!empty($post['bio'])): ?>
                    <div class="mb-4">
                        <p class="small"><?php echo substr(htmlspecialchars($post['bio']), 0, 150); ?>...</p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="d-grid gap-2">
                        <a href="staff_profile.php?id=<?php echo $post['staff_id']; ?>" class="btn btn-outline-primary btn-block">
                            View Full Profile
                        </a>
                        <?php if ($is_logged_in): ?>
                        <a href="book_appointment.php?staff_id=<?php echo $post['staff_id']; ?>&service=<?php echo urlencode($post['service_category']); ?>" class="btn btn-primary btn-block">
                            Book an Appointment
                        </a>
                        <?php else: ?>
                        <a href="login.php?redirect=view_post_detail.php?id=<?php echo $post_id; ?>" class="btn btn-primary btn-block">
                            Log In to Book
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Other services by this provider -->
                <?php
                $related_query = "SELECT id, title FROM staff_posts 
                                  WHERE staff_id = ? AND id != ? 
                                  ORDER BY created_at DESC LIMIT 5";
                $stmt = $conn->prepare($related_query);
                $stmt->bind_param("ii", $post['staff_id'], $post_id);
                $stmt->execute();
                $related_result = $stmt->get_result();
                
                if ($related_result->num_rows > 0):
                ?>
                <div class="mt-4">
                    <h5>More by <?php echo htmlspecialchars($post['staff_name']); ?></h5>
                    <ul class="list-group">
                        <?php while($related = $related_result->fetch_assoc()): ?>
                        <li class="list-group-item">
                            <a href="view_post_detail.php?id=<?php echo $related['id']; ?>">
                                <?php echo htmlspecialchars($related['title']); ?>
                            </a>
                        </li>
                        <?php endwhile; ?>
                    </ul>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

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

<?php if ($is_logged_in): ?>
<script>
$(document).ready(function() {
    // Handle like button click
    $('#likeBtn').click(function() {
        var postId = $(this).data('post-id');
        var likeBtn = $(this);
        var likeCount = likeBtn.find('.like-count');
        
        $.ajax({
            url: 'handle_like.php',
            type: 'POST',
            data: {post_id: postId},
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    // Update like count
                    likeCount.text(response.likes);
                    
                    // Toggle liked class and icon
                    if (response.action === 'liked') {
                        likeBtn.addClass('liked');
                        likeBtn.find('i').removeClass('icon-heart-o').addClass('icon-heart');
                    } else {
                        likeBtn.removeClass('liked');
                        likeBtn.find('i').removeClass('icon-heart').addClass('icon-heart-o');
                    }
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('An error occurred while processing your request.');
            }
        });
    });
});
</script>
<?php endif; ?>

</body>
</html> 