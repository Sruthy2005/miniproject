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
                <li class="nav-item">
                    <a href="contact.html" class="nav-link">Contact</a>
                </li>
                <li class="nav-item dropdown">
                    <?php
                    if(isset($_SESSION['user_id'])) {
                        require_once "connect.php";
                        $user_id = $_SESSION['user_id'];
                        $query = "SELECT first_name, last_name, role FROM user WHERE id = '$user_id'";
                        $result = mysqli_query($conn, $query);
                        $user = mysqli_fetch_assoc($result);
                        
                        echo '<div class="profile-circle dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="icon-user"></span>
                                <span class="user-name">' . $user['first_name'] . ' ' . $user['last_name'] . '</span>
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