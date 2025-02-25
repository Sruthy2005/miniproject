<?php
session_start();
require_once 'connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Get user information from database
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM user WHERE id = ?");
if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
if (!$stmt->execute()) {
    die("Error executing statement: " . $stmt->error);
}

$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("User not found in database");
}

// After fetching user data, add default values if any field is missing
if ($user) {
    $user['first_name'] = $user['first_name'] ?? '';
    $user['last_name'] = $user['last_name'] ?? '';
    $user['email'] = $user['email'] ?? '';
    $user['phone'] = $user['phone'] ?? '';
    $user['skin_type'] = $user['skin_type'] ?? '';
    $user['hair_type'] = $user['hair_type'] ?? '';
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $skin_type = trim($_POST['skin_type']);
    $hair_type = trim($_POST['hair_type']);
    
    // Basic validation
    if (!empty($first_name) && !empty($last_name) && !empty($email)) {
        $update_stmt = $conn->prepare("UPDATE user SET first_name = ?, last_name = ?, email = ?, phone = ?, skin_type = ?, hair_type = ? WHERE id = ?");
        $update_stmt->bind_param("ssssssi", $first_name, $last_name, $email, $phone, $skin_type, $hair_type, $user_id);
        
        if ($update_stmt->execute()) {
            $success_message = "Profile updated successfully!";
            // Refresh user data
            $user['first_name'] = $first_name;
            $user['last_name'] = $last_name;
            $user['email'] = $email;
            $user['phone'] = $phone;
        } else {
            $error_message = "Error updating profile.";
        }
    } else {
        $error_message = "All fields are required.";
    }
}

// Add this at the top of your PHP section to handle image upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_image'])) {
    $target_dir = "uploads/profiles/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, recursive: true);
    }
    
    $file = $_FILES['profile_image'];
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_types = ['jpg', 'jpeg', 'png'];
    
    if (in_array($file_extension, $allowed_types)) {
        $new_filename = "profile_" . $user_id . "." . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            $update_image = $conn->prepare("UPDATE user SET profile_image = ? WHERE id = ?");
            $update_image->bind_param("si", $target_file, $user_id);
            if ($update_image->execute()) {
                $user['profile_image'] = $target_file;
                $_SESSION['profile_image'] = $target_file; // Store profile image path in session
            }
        }
    }
}

// When initially fetching user data, also set the profile image in session
if ($user) {
    $_SESSION['profile_image'] = $user['profile_image'] ?? 'images/default-avatar.jpg';
    // ... rest of the user data initialization
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
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
        .logo img {
            width: 40px;
            margin-right: 10px;
        }
        .nav-item {
            padding: 12px 15px;
            color: #666;
            display: flex;
            align-items: center;
            border-radius: 10px;
            margin-bottom: 5px;
            transition: all 0.3s;
        }
        .nav-item.active {
            background: #f0f7ff;
            color: #71caf3;
        }
        .nav-item i {
            margin-right: 10px;
        }
        .profile-section {
            display: flex;
            gap: 30px;
            align-items: flex-start;
        }
        .profile-image-section {
            position: relative;
            width: 200px;
        }
        .profile-image {
            width: 200px;
            height: 200px;
            border-radius: 15px;
            object-fit: cover;
            display: block;
        }
        .upload-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(113,202,243,0.9);
            padding: 10px;
            text-align: center;
            border-bottom-left-radius: 15px;
            border-bottom-right-radius: 15px;
            cursor: pointer;
        }
        .upload-overlay i {
            color: white;
        }
        .profile-info {
            flex: 1;
        }
        .account-status {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            background: #e8f7ff;
            color: #71caf3;
            font-size: 14px;
            margin-top: 10px;
        }
        .bills-section {
            margin-top: 30px;
            background: white;
            border-radius: 20px;
            padding: 20px;
        }
        .bill-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        .bill-item:last-child {
            border-bottom: none;
        }
        .bill-status {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
        }
        .status-paid {
            background: #e8f7ff;
            color: #71caf3;
        }
        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
        }
        .profile-info h2 {
            margin: 0;
            color: #333;
            font-size: 24px;
        }
        .profile-info p {
            color: #666;
            margin: 5px 0;
        }
        .form-section {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        .form-control {
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 9px 15px;
            margin-bottom: 20px;
        }
        .form-control:focus {
            border-color: #71caf3;
            box-shadow: 0 0 0 3px rgba(113,202,243,0.1);
        }
        .btn-update {
            background: #71caf3;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 500;
        }
        .user-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .user-welcome {
            display: flex;
            align-items: center;
        }
        .user-welcome img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-left: 10px;
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
            <?php if ($user['role'] === 'admin' || $user['role'] === 'staff'): ?>
            <a href="admin_dash/admin.php" class="nav-item">
                <i class="fas fa-tachometer-alt"></i>
                Back to Dashboard
            </a>
            <?php endif; ?>
            <a href="profile.php" class="nav-item active">
                <i class="fas fa-user"></i>
                My Profile
            </a>
            <a href="change_password.php" class="nav-item">
                <i class="fas fa-key"></i>
                Change Password
            </a>
            <a href="logout.php" class="nav-item">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </a>
        </div>

        <div class="main-content">
            <div class="user-header">
                <h1>My Profile</h1>
                <div class="user-welcome">
                </div>
            </div>

            <div class="profile-section">
                <div class="profile-image-section">
                    <img src="<?php echo htmlspecialchars($user['profile_image'] ?? 'images/default-avatar.jpg'); ?>" 
                         alt="Profile" class="profile-image">
                    <label for="profile_image" class="upload-overlay">
                        <i class="fas fa-camera"></i>
                    </label>
                    <input type="file" id="profile_image" name="profile_image" 
                           style="display: none" accept="image/jpeg,image/png">
                </div>
                
                <div class="profile-info">
                    <h2><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h2>
                    <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($user['phone']); ?></p>
                    <div class="account-status">
                        <i class="fas fa-check-circle"></i> Active Account
                    </div>
                </div>
            </div>

            <?php if (isset($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle mr-2"></i><?php echo $success_message; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle mr-2"></i><?php echo $error_message; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <div class="form-section">
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="first_name">
                                    <i class="fas fa-user mr-2"></i>First Name
                                </label>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                       value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="last_name">
                                    <i class="fas fa-user mr-2"></i>Last Name
                                </label>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                       value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">
                                    <i class="fas fa-envelope mr-2"></i>Email Address
                                </label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone">
                                    <i class="fas fa-phone mr-2"></i>Phone Number
                                </label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="skin_type">
                                    <i class="fas fa-spa mr-2"></i>Skin Type
                                </label>
                                <select class="form-control" id="skin_type" name="skin_type">
                                    <option value="">Select Skin Type</option>
                                    <option value="Dry" <?php echo ($user['skin_type'] === 'Dry') ? 'selected' : ''; ?>>Dry</option>
                                    <option value="Oily" <?php echo ($user['skin_type'] === 'Oily') ? 'selected' : ''; ?>>Oily</option>
                                    <option value="Normal" <?php echo ($user['skin_type'] === 'Normal') ? 'selected' : ''; ?>>Normal</option>
                                    <option value="Combination" <?php echo ($user['skin_type'] === 'Combination') ? 'selected' : ''; ?>>Combination</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="hair_type">
                                    <i class="fas fa-cut mr-2"></i>Hair Type
                                </label>
                                <select class="form-control" id="hair_type" name="hair_type">
                                    <option value="">Select Hair Type</option>
                                    <option value="Type 1" <?php echo ($user['hair_type'] === 'Type 1') ? 'selected' : ''; ?>>Straight</option>
                                    <option value="Type 2" <?php echo ($user['hair_type'] === 'Type 2') ? 'selected' : ''; ?>>Wavy</option>
                                    <option value="Type 3" <?php echo ($user['hair_type'] === 'Type 3') ? 'selected' : ''; ?>>Curly</option>
                                    <option value="Type 4" <?php echo ($user['hair_type'] === 'Type 4') ? 'selected' : ''; ?>>Tightly Curled</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-right mt-4">
                        <button type="submit" class="btn btn-primary btn-update">
                            <i class="fas fa-save mr-2"></i>Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
    document.getElementById('profile_image').addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const formData = new FormData();
            formData.append('profile_image', file);
            
            fetch('profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(() => {
                window.location.reload();
            })
            .catch(error => console.error('Error:', error));
        }
    });
    </script>
</body>
</html>
