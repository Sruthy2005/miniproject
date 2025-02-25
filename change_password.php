<?php
session_start();
require_once 'connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Handle password change
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($new_password === $confirm_password) {
        if (strlen($new_password) >= 8) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE user SET password = ? WHERE id = ?");
            $update_stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($update_stmt->execute()) {
                $success_message = "Password updated successfully!";
            } else {
                $error_message = "Error updating password.";
            }
        } else {
            $error_message = "New password must be at least 8 characters long.";
        }
    } else {
        $error_message = "New passwords do not match.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Inter', sans-serif;
        }
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            display: flex;
            gap: 30px;
        }
        .sidebar {
            width: 280px;
            background: white;
            border-radius: 20px;
            padding: 30px;
        }
        .main-content {
            flex: 1;
            background: white;
            border-radius: 20px;
            padding: 30px;
        }
        .logo {
            font-size: 24px;
            color: #333;
            margin-bottom: 40px;
            display: flex;
            align-items: center;
        }
        .nav-item {
            padding: 12px 15px;
            color: #666;
            display: flex;
            align-items: center;
            border-radius: 10px;
            margin-bottom: 5px;
            transition: all 0.3s;
            text-decoration: none;
        }
        .nav-item:hover {
            background: #f0f7ff;
            color: #71caf3;
            text-decoration: none;
        }
        .nav-item.active {
            background: #f0f7ff;
            color: #71caf3;
        }
        .nav-item i {
            margin-right: 10px;
        }
        .form-control {
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 12px 15px;
        }
        .btn-update {
            background: #71caf3;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <div class="logo">
                <a href="index.php">
                    <span class="flaticon-flower" style="color: black"></span>
                    <h1>BELLEZZA</h1>
                </a>
            </div>
            <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff')): ?>
            <a href="admin_dash/admin.php" class="nav-item">
                <i class="fas fa-tachometer-alt"></i>
                Back to Dashboard
            </a>
            <?php endif; ?>
            <a href="profile.php" class="nav-item">
                <i class="fas fa-user"></i>
                My Profile
            </a>
            <a href="logout.php" class="nav-item">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </a>
            <a href="change_password.php" class="nav-item active">
                <i class="fas fa-key"></i>
                Change Password
            </a>
        </div>

        <div class="main-content">
            <h2 class="mb-4">Change Password</h2>

            <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle mr-2"></i><?php echo $success_message; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle mr-2"></i><?php echo $error_message; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="new_password">
                        <i class="fas fa-key mr-2"></i>New Password
                    </label>
                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">
                        <i class="fas fa-check-circle mr-2"></i>Confirm New Password
                    </label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>

                <button type="submit" class="btn btn-update">
                    <i class="fas fa-save mr-2"></i>Update Password
                </button>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html> 