<?php
// Debug mode - set to false in production
$debug_mode = true;

if ($debug_mode) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

session_start();
require_once '../connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

// Verify user is staff
if ($user_role !== 'staff') {
    header("Location: ../index.php");
    exit();
}

// Get staff name
$staff_query = "SELECT CONCAT(first_name, ' ', last_name) as name FROM user WHERE id = ? AND role = 'staff'";
$stmt = mysqli_prepare($conn, $staff_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$staff_result = mysqli_stmt_get_result($stmt);
$staff_row = mysqli_fetch_assoc($staff_result);
$staff_name = htmlspecialchars($staff_row['name'] ?? 'Staff Member');

// Handle post creation
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'create_post') {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $service_category = "Other Services"; // Set a default category
        
        // Image upload handling
        $image_path = '';
        if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] === 0) {
            $upload_dir = '../uploads/posts/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_name = time() . '_' . basename($_FILES['post_image']['name']);
            $target_file = $upload_dir . $file_name;
            
            // Check file type
            $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
            if (in_array($_FILES['post_image']['type'], $allowed_types)) {
                if (move_uploaded_file($_FILES['post_image']['tmp_name'], $target_file)) {
                    $image_path = 'uploads/posts/' . $file_name;
                } else {
                    $message = "Error uploading image.";
                }
            } else {
                $message = "Only JPG, JPEG, PNG & GIF files are allowed.";
            }
        }
        
        if (empty($message)) {
            // Insert post into database
            $insert_query = "INSERT INTO staff_posts (staff_id, title, description, image_path, service_category, created_at) 
                            VALUES (?, ?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($insert_query);
            
            if ($stmt) {
                $stmt->bind_param("issss", $user_id, $title, $description, $image_path, $service_category);
                if ($stmt->execute()) {
                    $message = "Post created successfully!";
                } else {
                    $message = "Error creating post: " . $stmt->error;
                }
            } else {
                $message = "Error preparing statement: " . $conn->error;
            }
        }
    }
    
    // Handle post deletion
    if (isset($_POST['action']) && $_POST['action'] === 'delete_post') {
        $post_id = $_POST['post_id'];
        
        // Get post info to delete image
        $get_post = "SELECT image_path FROM staff_posts WHERE id = ? AND staff_id = ?";
        $stmt = $conn->prepare($get_post);
        $stmt->bind_param("ii", $post_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            // Delete the image file if it exists
            if (!empty($row['image_path']) && file_exists('../' . $row['image_path'])) {
                unlink('../' . $row['image_path']);
            }
            
            // Delete post from database
            $delete_query = "DELETE FROM staff_posts WHERE id = ? AND staff_id = ?";
            $stmt = $conn->prepare($delete_query);
            $stmt->bind_param("ii", $post_id, $user_id);
            
            if ($stmt->execute()) {
                $message = "Post deleted successfully!";
            } else {
                $message = "Error deleting post: " . $stmt->error;
            }
        }
    }
}

// Remove the service categories processing and error handling
$db_error = "";

// Check if staff_posts table exists
$table_check = $conn->query("SHOW TABLES LIKE 'staff_posts'");
if ($table_check->num_rows == 0) {
    // Table doesn't exist, create it
    $create_table_sql = "CREATE TABLE `staff_posts` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `staff_id` int(11) NOT NULL,
        `title` varchar(255) NOT NULL,
        `description` text NOT NULL,
        `image_path` varchar(255) DEFAULT NULL,
        `service_category` varchar(100) DEFAULT NULL,
        `likes` int(11) DEFAULT 0,
        `created_at` datetime NOT NULL,
        PRIMARY KEY (`id`),
        KEY `staff_id` (`staff_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    if (!$conn->query($create_table_sql)) {
        $db_error = "Error creating staff_posts table: " . $conn->error;
    }
}

// Fetch staff posts with error handling
$posts_query = "SELECT * FROM staff_posts WHERE staff_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($posts_query);

if (!$stmt) {
    $db_error .= "Error preparing posts query: " . $conn->error;
} else {
    $stmt->bind_param("i", $user_id);
    if (!$stmt->execute()) {
        $db_error .= "Error executing posts query: " . $stmt->error;
        $posts_result = null;
    } else {
        $posts_result = $stmt->get_result();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>My Posts - Staff Portal</title>
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
        .post-card {
            transition: transform 0.3s;
            height: 100%;
        }
        
        .post-card:hover {
            transform: translateY(-5px);
        }
        
        .post-image {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }
        
        .post-preview {
            max-height: 100px;
            overflow: hidden;
        }
        
        .view-likes {
            color: #0d6efd; /* Bootstrap primary color */
            text-decoration: none;
            padding: 0.25rem 0.5rem;
            transition: all 0.2s;
        }
        
        .view-likes:hover {
            background-color: rgba(13, 110, 253, 0.1);
            border-radius: 0.25rem;
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="container-xxl position-relative bg-white d-flex p-0">
        <!-- Spinner Start - Disabled -->
        <div id="spinner" class="bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center" style="display: none !important;">
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
                    <a href="view_feedback.php" class="nav-item nav-link"><i class="fa fa-comments me-2"></i>View Feedback</a>
                    <a href="my_posts.php" class="nav-item nav-link active"><i class="fa fa-image me-2"></i>My Posts</a>
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

            <!-- Posts Content Start -->
            <div class="container-fluid pt-4 px-4">
                <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo (strpos($message, 'successfully') !== false) ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <?php if ($posts_result->num_rows === 0): ?>
                <div class="container-fluid pt-4 px-4">
                    <div class="bg-light rounded p-4">
                        <div class="alert alert-info">
                            <h5><i class="fas fa-info-circle me-2"></i>Getting Started with Posts</h5>
                            <p>Posts you create will be displayed on the public portfolio page that clients can view. This is a great way to showcase your work and attract new clients!</p>
                            <ol class="mt-3">
                                <li>Click "Create New Post" to share your work</li>
                                <li>Add a high-quality image of your completed work</li>
                                <li>Provide a detailed description to help clients understand the service</li>
                                <li>Choose the correct service category to help clients find your work</li>
                            </ol>
                            <p>Your posts will automatically appear in the <a href="../view_staff_posts.php" target="_blank">public gallery</a>.</p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($db_error)): ?>
                <div class="container-fluid pt-4 px-4">
                    <div class="alert alert-danger">
                        <h5><i class="fas fa-exclamation-triangle me-2"></i>Database Error</h5>
                        <p><?php echo $db_error; ?></p>
                        <p>Please contact the system administrator if this problem persists.</p>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="row g-4">
                    <div class="col-12">
                        <div class="bg-light rounded p-4">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <h3 class="mb-0">My Service Posts</h3>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPostModal">
                                    <i class="fa fa-plus me-2"></i>Create New Post
                                </button>
                            </div>
                            
                            <!-- Posts Cards -->
                            <div class="row g-4">
                                <?php if ($posts_result->num_rows > 0): ?>
                                    <?php while ($post = $posts_result->fetch_assoc()): ?>
                                        <div class="col-md-4 col-lg-3">
                                            <div class="card post-card shadow-sm">
                                                <?php if (!empty($post['image_path'])): ?>
                                                    <img src="../<?php echo htmlspecialchars($post['image_path']); ?>" class="card-img-top post-image" alt="Post Image">
                                                <?php else: ?>
                                                    <div class="card-img-top post-image bg-light d-flex align-items-center justify-content-center">
                                                        <i class="fas fa-image fa-3x text-secondary"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="card-body">
                                                    <h5 class="card-title"><?php echo htmlspecialchars($post['title']); ?></h5>
                                                    <div class="mb-2 d-flex align-items-center flex-wrap">
                                                        <span class="badge bg-success me-2">
                                                            <i class="fas fa-globe me-1"></i> Visible to Public
                                                        </span>
                                                        
                                                        <?php 
                                                        // Get like count for this post
                                                        $likes_query = "SELECT COUNT(*) as total FROM post_likes WHERE post_id = ?";
                                                        $likes_stmt = $conn->prepare($likes_query);
                                                        $likes_stmt->bind_param("i", $post['id']);
                                                        $likes_stmt->execute();
                                                        $likes_result = $likes_stmt->get_result();
                                                        $likes_count = $likes_result->fetch_assoc()['total'];
                                                        ?>
                                                        
                                                        <span class="badge bg-primary me-2">
                                                            <i class="fas fa-heart me-1"></i> Likes: <?php echo $likes_count; ?>
                                                            
                                                        </span>
                                                        <?php if ($likes_count > 0): ?>
                                                        <button type="button" class="btn btn-sm btn-link view-likes ms-0 ps-0" 
                                                                data-bs-toggle="modal" data-bs-target="#likesModal" 
                                                                data-post-id="<?php echo $post['id']; ?>" 
                                                                data-bs-post-id="<?php echo $post['id']; ?>"
                                                                data-post-title="<?php echo htmlspecialchars($post['title']); ?>">
                                                            View who liked
                                                        </button>
                                                        <?php endif; ?>
                                                        
                                                       
                                                    </div>
                                                    
                                                    <p class="card-text post-preview"><?php echo htmlspecialchars($post['description']); ?></p>
                                                    <div class="d-flex justify-content-between mt-3">
                                                        <small class="text-muted">
                                                            <i class="far fa-clock me-1"></i>
                                                            <?php echo date('M d, Y', strtotime($post['created_at'])); ?>
                                                        </small>
                                                        <div>
                                                            <a href="../view_post_detail.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-info me-1" target="_blank">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <form method="POST" class="d-inline">
                                                                <input type="hidden" name="action" value="delete_post">
                                                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this post?')">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="col-12 text-center py-5">
                                        <i class="fas fa-image fa-4x text-muted mb-3"></i>
                                        <h5 class="text-muted">You haven't created any posts yet</h5>
                                        <p>Share your work with clients by creating your first post.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Posts Content End -->
        </div>
        <!-- Content End -->

        <!-- Back to Top -->
        <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
    </div>

    <!-- Create Post Modal -->
    <div class="modal fade" id="createPostModal" tabindex="-1" aria-labelledby="createPostModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createPostModalLabel">Create New Post</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create_post">
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="post_image" class="form-label">Upload Image</label>
                            <input class="form-control" type="file" id="post_image" name="post_image" accept="image/*">
                            <div class="form-text">Select an image to showcase your work (max 5MB).</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                        </div>
                        
                        <input type="hidden" name="service_category" value="Other Services">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Post</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Likes Modal -->
    <div class="modal fade" id="likesModal" tabindex="-1" aria-labelledby="likesModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="likesModalLabel">Post Likes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="postTitleDisplay"></p>
                    <div class="text-center" id="likesLoader">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <div id="likesList"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Remove or comment out these lines if they're causing issues -->
    <!-- <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/tempusdominus/js/moment.min.js"></script>
    <script src="lib/tempusdominus/js/moment-timezone.min.js"></script>
    <script src="lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script> -->

    <!-- Comment out this line if it's causing issues -->
    <!-- <script src="js/main.js"></script> -->

    <!-- Add this script at the bottom to forcefully hide the spinner -->
    <script>
        // Try multiple methods to hide the spinner
        window.onload = function() {
            document.getElementById('spinner').classList.remove('show');
            document.getElementById('spinner').style.display = 'none';
        }
        
        // Also try immediately
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('spinner').classList.remove('show');
            document.getElementById('spinner').style.display = 'none';
        });
        
        // Last resort - hide after a timeout
        setTimeout(function() {
            var spinner = document.getElementById('spinner');
            if (spinner) {
                spinner.classList.remove('show');
                spinner.style.display = 'none';
            }
        }, 1000);
    </script>

    <script>
    $(document).ready(function() {
        $('.view-likes').click(function() {
            // Try getting the post ID using getAttribute instead of data()
            const postId = this.getAttribute('data-post-id');
            const postTitle = $(this).data('post-title');
            
            console.log("Post ID from attribute:", postId);
            
            // Set the post title in the modal
            $('#postTitleDisplay').text('Likes for: ' + postTitle);
            
            // Show loader, hide list
            $('#likesLoader').show();
            $('#likesList').empty().hide();
            
            // Fetch likes data
            $.ajax({
                url: 'get_post_likes.php',
                type: 'GET',
                data: {post_id: postId},
                dataType: 'json',
                success: function(response) {
                    console.log("Response:", response); // Debug log
                    $('#likesLoader').hide();
                    
                    if (response.status === 'success') {
                        const likes = response.likes;
                        
                        if (likes.length > 0) {
                            let likesHtml = '<ul class="list-group">';
                            
                            likes.forEach(function(like) {
                                likesHtml += `
                                    <li class="list-group-item">
                                        <div class="d-flex align-items-center">
                                            ${like.profile_image ? 
                                                `<img src="../${like.profile_image}" class="rounded-circle me-3" width="40" height="40" alt="${like.name}">` : 
                                                `<div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;"><i class="fa fa-user"></i></div>`
                                            }
                                            <div>
                                                <h6 class="mb-0">${like.name}</h6>
                                                <small class="text-muted">Liked on ${like.liked_on}</small>
                                            </div>
                                        </div>
                                    </li>
                                `;
                            });
                            
                            likesHtml += '</ul>';
                            $('#likesList').html(likesHtml).show();
                        } else {
                            $('#likesList').html('<p class="text-center">No likes found for this post.</p>').show();
                        }
                    } else {
                        $('#likesList').html('<p class="text-center text-danger">Error: ' + response.message + '</p>').show();
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", xhr.responseText);
                    $('#likesLoader').hide();
                    $('#likesList').html('<p class="text-center text-danger">An error occurred while fetching likes data.</p>').show();
                }
            });
        });
    });
    </script>

    <?php if ($debug_mode): ?>
    <div class="container-fluid mt-5 p-4 bg-light">
        <h5>Debug Information</h5>
        <div class="small text-muted">
            <p>PHP Version: <?php echo phpversion(); ?></p>
            <p>Session User ID: <?php echo $_SESSION['user_id'] ?? 'Not set'; ?></p>
            <p>Session Role: <?php echo $_SESSION['role'] ?? 'Not set'; ?></p>
            <p>Memory Usage: <?php echo round(memory_get_usage() / 1024 / 1024, 2); ?> MB</p>
            <p>Loaded Extensions: <?php echo implode(', ', get_loaded_extensions()); ?></p>
        </div>
    </div>
    <?php endif; ?>
</body>
</html> 