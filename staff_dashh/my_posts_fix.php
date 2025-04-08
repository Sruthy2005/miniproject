<?php
session_start();
require_once '../connect.php';

// Basic version with minimal code to get page loading
$user_id = $_SESSION['user_id'] ?? 0;
$staff_name = "Staff Member";

// Try to get staff name
if (!empty($user_id)) {
    $stmt = $conn->prepare("SELECT CONCAT(first_name, ' ', last_name) as name FROM user WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $staff_name = $row['name'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>My Posts - Fixed</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    
    <!-- Bootstrap CSS only -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <h1>My Posts</h1>
        <p>Welcome, <?php echo htmlspecialchars($staff_name); ?></p>
        
        <div class="my-4">
            <a href="staff_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
            <a href="my_posts.php" class="btn btn-primary">Go to Full Posts Page</a>
        </div>
        
        <div class="alert alert-info">
            <h5>Page Status</h5>
            <p>If you can see this page, the basic display is working.</p>
            <p>The issue might be with the JavaScript, CSS, or complex PHP logic in the main posts page.</p>
        </div>
    </div>
    
    <!-- Simple JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 