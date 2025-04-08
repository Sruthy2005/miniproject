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
    $user['address'] = $user['address'] ?? '';
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $skin_type = trim($_POST['skin_type']);
    $hair_type = trim($_POST['hair_type']);
    $street_address = trim($_POST['street_address']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $postcode = trim($_POST['postcode']);
    
    // Basic validation
    if (!empty($first_name) && !empty($last_name) && !empty($email)) {
        $address = $street_address . ',' . $city . ',' . $state . ',' . $postcode;
        $update_stmt = $conn->prepare("UPDATE user SET first_name = ?, last_name = ?, email = ?, phone = ?, skin_type = ?, hair_type = ?, address = ? WHERE id = ?");
        $update_stmt->bind_param("sssssssi", $first_name, $last_name, $email, $phone, $skin_type, $hair_type, $address, $user_id);
        
        if ($update_stmt->execute()) {
            $success_message = "Profile updated successfully!";
            // Refresh user data
            $user['first_name'] = $first_name;
            $user['last_name'] = $last_name;
            $user['email'] = $email;
            $user['phone'] = $phone;
            $user['skin_type'] = $skin_type;
            $user['hair_type'] = $hair_type;
            $user['address'] = $address;
        } else {
            $error_message = "Error updating profile.";
        }
    } else {
        $error_message = "All fields are required.";
    }
}

// Handle image upload
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
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | BELLEZZA</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #ff7eb3;
            --primary-dark: #ff5c9c;
            --secondary: #7571ff;
            --accent: #71caf3;
            --light: #f8f9fa;
            --dark: #2d3436;
            --gray: #636e72;
            --success: #00b894;
            --danger: #ff7675;
            --card-border-radius: 16px;
            --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f5f7fc;
            color: var(--dark);
            min-height: 100vh;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: white;
            box-shadow: var(--box-shadow);
            padding: 30px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transition: all 0.3s ease;
            z-index: 100;
        }

        .sidebar-brand {
            padding: 0 30px 30px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }

        .sidebar-brand a {
            display: flex;
            align-items: center;
            text-decoration: none;
        }

        .sidebar-brand h1 {
            font-size: 24px;
            font-weight: 700;
            margin: 0;
            background:black;
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .nav-menu {
            padding: 30px 0;
        }

        .nav-item {
            padding: 12px 30px;
            display: flex;
            align-items: center;
            color: var(--gray);
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }

        .nav-item:hover {
            color: var(--primary);
            background: rgba(255, 126, 179, 0.05);
        }

        .nav-item.active {
            color: var(--primary);
            background: rgba(255, 126, 179, 0.1);
            border-left: 3px solid var(--primary);
        }

        .nav-item i {
            margin-right: 15px;
            width: 20px;
            text-align: center;
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 40px;
            transition: all 0.3s ease;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .header h1 {
            font-weight: 700;
            font-size: 28px;
            color: var(--dark);
            margin: 0;
        }

        /* Profile Section Styles */
        .profile-section {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            margin-bottom: 30px;
        }

        .profile-card {
            background: white;
            border-radius: var(--card-border-radius);
            box-shadow: var(--box-shadow);
            padding: 30px;
            width: 100%;
            overflow: hidden;
            position: relative;
        }

        .profile-header {
            display: flex;
            align-items: center;
            gap: 30px;
            margin-bottom: 30px;
        }

        .profile-image-container {
            position: relative;
            width: 150px;
            height: 150px;
            border-radius: 50%;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border: 5px solid white;
        }

        .profile-image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .image-upload-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);
            padding: 15px 0 10px;
            text-align: center;
            opacity: 0;
            transition: all 0.3s;
            cursor: pointer;
        }

        .profile-image-container:hover .image-upload-overlay {
            opacity: 1;
        }

        .image-upload-overlay i {
            color: white;
            font-size: 20px;
        }

        .profile-info h2 {
            font-size: 28px;
            font-weight: 600;
            margin: 0 0 10px;
            color: var(--dark);
        }

        .profile-info p {
            color: var(--gray);
            margin: 0 0 5px;
            display: flex;
            align-items: center;
        }

        .profile-info p i {
            width: 20px;
            margin-right: 10px;
            color: var(--primary);
        }

        .account-badge {
            display: inline-flex;
            align-items: center;
            background: linear-gradient(135deg, #e0f7ff, #c8f5fe);
            color: var(--accent);
            font-weight: 500;
            font-size: 14px;
            padding: 5px 15px;
            border-radius: 30px;
            margin-top: 15px;
        }

        .account-badge i {
            margin-right: 8px;
        }

        /* Form Styles */
        .form-card {
            background: white;
            border-radius: var(--card-border-radius);
            box-shadow: var(--box-shadow);
            padding: 30px;
            margin-bottom: 30px;
        }

        .form-card h3 {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            color: var(--dark);
        }

        .form-group label {
            font-weight: 500;
            color: var(--dark);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }

        .form-group label i {
            margin-right: 10px;
            color: var(--primary);
            width: 20px;
        }

        .form-control {
            height: 50px;
            padding: 10px 15px;
            border-radius: 10px;
            border: 1px solid rgba(0,0,0,0.1);
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(255, 126, 179, 0.2);
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23636e72' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            padding-right: 35px;
        }

        .btn-update {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            font-weight: 500;
            font-size: 15px;
            padding: 12px 30px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(255, 126, 179, 0.3);
        }

        .btn-update:hover {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 126, 179, 0.4);
        }

        .btn-update i {
            margin-right: 10px;
        }

        /* Alert Styles */
        .alert {
            border-radius: 10px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
        }

        .alert i {
            margin-right: 15px;
            font-size: 18px;
        }

        .alert-success {
            background-color: rgba(0, 184, 148, 0.1);
            color: var(--success);
        }

        .alert-danger {
            background-color: rgba(255, 118, 117, 0.1);
            color: var(--danger);
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .menu-toggle {
                display: block;
            }
            
            .profile-header {
                flex-direction: column;
                text-align: center;
            }
            
            .profile-image-container {
                margin: 0 auto 20px;
            }
            
            .profile-info {
                text-align: center;
            }
            
            .profile-info p {
                justify-content: center;
            }
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in {
            animation: fadeIn 0.5s ease forwards;
        }

        .fade-in-delay-1 {
            opacity: 0;
            animation: fadeIn 0.5s ease forwards;
            animation-delay: 0.1s;
        }

        .fade-in-delay-2 {
            opacity: 0;
            animation: fadeIn 0.5s ease forwards;
            animation-delay: 0.2s;
        }

        .menu-toggle {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: white;
            box-shadow: var(--box-shadow);
            z-index: 200;
            text-align: center;
            line-height: 40px;
            cursor: pointer;
            color: var(--primary);
        }

        textarea.form-control {
            height: auto;
            resize: vertical;
            min-height: 80px;
        }
    </style>
</head>
<body>
    <!-- Mobile Menu Toggle -->
    <div class="menu-toggle">
        <i class="fas fa-bars"></i>
    </div>

    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-brand">
                <a href="index.php">
                    <h1>BELLEZZA</h1>
                </a>
            </div>
            
            <div class="nav-menu">
                <?php if ($user['role'] === 'admin'): ?>
                <a href="admin_dash/admin.php" class="nav-item">
                    <i class="fas fa-tachometer-alt"></i>
                    Back to Dashboard
                </a>
                <?php elseif ($user['role'] === 'staff'): ?>
                <a href="staff_dashh/staff_dashboard.php" class="nav-item">
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
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header fade-in">
                <h1>My Profile</h1>
            </div>

            <!-- Alerts -->
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show fade-in" role="alert">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success_message; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show fade-in" role="alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error_message; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Profile Card -->
            <div class="profile-section fade-in">
                <div class="profile-card">
                    <div class="profile-header">
                        <div class="profile-image-container">
                            <img src="<?php echo htmlspecialchars($user['profile_image'] ?? 'images/default-avatar.jpg'); ?>" alt="Profile Image">
                            <label for="profile_image" class="image-upload-overlay">
                                <i class="fas fa-camera"></i>
                            </label>
                            <input type="file" id="profile_image" name="profile_image" style="display: none" accept="image/jpeg,image/png">
                        </div>
                        
                        <div class="profile-info">
                            <h2><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h2>
                            <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?></p>
                            <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($user['phone']); ?></p>
                            <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($user['address'] ?? 'No address set'); ?></p>
                            
                            <div class="account-badge">
                                <i class="fas fa-check-circle"></i> Active Account
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Card -->
            <div class="form-card fade-in-delay-1">
                <h3>Edit Profile Information</h3>
                
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="first_name">
                                    <i class="fas fa-user"></i> First Name
                                </label>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                    value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="last_name">
                                    <i class="fas fa-user"></i> Last Name
                                </label>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                    value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">
                                    <i class="fas fa-envelope"></i> Email Address
                                </label>
                                <input type="email" class="form-control" id="email" name="email" 
                                    value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone">
                                    <i class="fas fa-phone"></i> Phone Number
                                </label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                    value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="address">
                                    <i class="fas fa-map-marker-alt"></i> Address
                                </label>
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <input type="text" class="form-control" name="street_address" id="street_address" placeholder="Street address" value="<?php echo !empty($user['address']) ? explode(',', $user['address'])[0] ?? '' : ''; ?>">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <input type="text" class="form-control" name="city" id="city" placeholder="City" value="<?php echo !empty($user['address']) ? (explode(',', $user['address'])[1] ?? '') : ''; ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" class="form-control" name="state" id="state" placeholder="State" value="<?php echo !empty($user['address']) ? (explode(',', $user['address'])[2] ?? '') : ''; ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" class="form-control" name="postcode" id="postcode" placeholder="Postcode" value="<?php echo !empty($user['address']) ? (explode(',', $user['address'])[3] ?? '') : ''; ?>">
                                    </div>
                                </div>
                                <input type="hidden" name="address" id="address">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="skin_type">
                                    <i class="fas fa-spa"></i> Skin Type
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
                                    <i class="fas fa-cut"></i> Hair Type
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
                        <button type="submit" class="btn btn-update">
                            <i class="fas fa-save"></i> Update Profile
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
    // Profile image upload
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

    // Mobile menu toggle
    document.querySelector('.menu-toggle').addEventListener('click', function() {
        document.querySelector('.sidebar').classList.toggle('active');
    });

    document.querySelector('form').addEventListener('submit', function(e) {
        // Get values from address fields
        const street = document.getElementById('street_address').value.trim();
        const city = document.getElementById('city').value.trim();
        const state = document.getElementById('state').value.trim();
        const postcode = document.getElementById('postcode').value.trim();
        
        // Combine into formatted address
        const fullAddress = [street, city, state, postcode].filter(Boolean).join(', ');
        
        // Set the value of the hidden address field
        document.getElementById('address').value = fullAddress;
    });
    </script>
</body>
</html>
