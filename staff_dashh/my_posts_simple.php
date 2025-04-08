<?php
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
$staff_query = "SELECT CONCAT(first_name, ' ', last_name) as name FROM user WHERE id = ?";
$stmt = mysqli_prepare($conn, $staff_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$staff_result = mysqli_stmt_get_result($stmt);
$staff_row = mysqli_fetch_assoc($staff_result);
$staff_name = htmlspecialchars($staff_row['name'] ?? 'Staff Member');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Posts Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <h1>My Posts (Simple View)</h1>
        <p>Welcome, <?php echo $staff_name; ?></p>
        
        <div class="my-4">
            <a href="staff_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPostModal">
                Create New Post
            </button>
        </div>
        
        <div class="row">
            <?php
            // Simple query to get posts
            $query = "SELECT * FROM staff_posts WHERE staff_id = ? ORDER BY created_at DESC";
            $stmt = $conn->prepare($query);
            
            if ($stmt) {
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    while ($post = $result->fetch_assoc()) {
                        echo '<div class="col-md-4 mb-4">';
                        echo '<div class="card">';
                        
                        if (!empty($post['image_path'])) {
                            echo '<img src="../' . htmlspecialchars($post['image_path']) . '" class="card-img-top" alt="Post image" style="height: 200px; object-fit: cover;">';
                        }
                        
                        echo '<div class="card-body">';
                        echo '<h5 class="card-title">' . htmlspecialchars($post['title']) . '</h5>';
                        echo '<p class="card-text">' . htmlspecialchars(substr($post['description'], 0, 100)) . '...</p>';
                        echo '<p class="text-muted">Category: ' . htmlspecialchars($post['service_category']) . '</p>';
                        echo '<p class="text-muted">Posted on: ' . date('M d, Y', strtotime($post['created_at'])) . '</p>';
                        echo '</div></div></div>';
                    }
                } else {
                    echo '<div class="col-12"><div class="alert alert-info">You have no posts yet.</div></div>';
                }
            } else {
                echo '<div class="col-12"><div class="alert alert-danger">Error preparing statement: ' . $conn->error . '</div></div>';
            }
            ?>
        </div>
    </div>
    
    <!-- Create Post Modal -->
    <div class="modal fade" id="createPostModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Post</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" enctype="multipart/form-data" action="my_posts.php">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create_post">
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="service_category" class="form-label">Service Category</label>
                            <input type="text" class="form-control" id="service_category" name="service_category" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Post</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 