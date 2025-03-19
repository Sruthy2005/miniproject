<nav class="navbar navbar-expand-lg navbar-dark ftco_navbar bg-dark ftco-navbar-light" id="ftco-navbar">
    <div class="container">
        <a href="index.php" class="logo">
            <span class="flaticon-flower" style="color: black; font-size: 30px;"></span>
        </a>
        <a class="navbar-brand" href="index.php">BELLEZZA</a>
        <div class="icon"></div>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ftco-nav" aria-controls="ftco-nav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="oi oi-menu"></span> Menu
        </button>

        <div class="collapse navbar-collapse" id="ftco-nav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a href="index.php" class="nav-link">Home</a>
                </li>
                <li class="nav-item">
                    <a href="about.html" class="nav-link">About</a>
                </li>
                <li class="nav-item">
                    <a href="services.php" class="nav-link">Services</a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="services.php">All Services</a>
                        <a class="dropdown-item" href="hair.php">Hair</a>
                        <a class="dropdown-item" href="skin.php">Skin</a>
                        <a class="dropdown-item" href="makeup.php">Makeup</a>
                    </div>
                </li>
                <li class="nav-item">
                    <a href="work.html" class="nav-link">Work</a>
                </li>
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown" data-toggle="dropdown">Booking</a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="booking.php">Book a Service</a>
                        <a class="dropdown-item" href="my_bookings.php">My Bookings</a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                <?php
              // Remove session_start() since session is already active
              if(isset($_SESSION['user_id'])) {
                require_once "connect.php";
                $user_id = $_SESSION['user_id'];
                $query = "SELECT first_name, last_name, role, profile_image FROM user WHERE id = '$user_id'";
                $result = mysqli_query($conn, $query);
                $user = mysqli_fetch_assoc($result);
                
                // Set profile image with fallback to default
                if (!isset($_SESSION['profile_image'])) {
                  $_SESSION['profile_image'] = $user['profile_image'] ?? 'images/default-avatar.jpg';
                }
                
                $profile_image = $_SESSION['profile_image'];
                $username = $user['first_name'] . ' ' . $user['last_name'];
                
                echo '<div class="profile-circle dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <img src="' . htmlspecialchars($profile_image) . '" alt="Profile" class="profile-image" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; margin-right: 10px;">
                        <span class="user-name">' . $username . '</span>
                      </div>
                      <div class="dropdown-menu">';
                
                // Add dashboard link for admin and staff
                if($user['role'] == 'admin') {
                  echo '<a class="dropdown-item" href="admin_dash/admin.php">Dashboard</a>';
                } else if($user['role'] == 'staff') {
                  echo '<a class="dropdown-item" href="staff_dashh/staff_dashboard.php">Dashboard</a>';
                }
                
                echo '<a class="dropdown-item" href="profile.php">Profile</a>
                      <div class="dropdown-divider"></div>
                      <a class="dropdown-item" href="logout.php">Logout</a>
                      </div>';
              } else {
                echo '<a href="login.php" class="nav-link">Login</a>';
              }
              ?>
                </li>
            </ul>
        </div>
    </div>
</nav> 