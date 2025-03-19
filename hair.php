<!DOCTYPE html>
<html lang="en">
  <head>
    <title>Bellezza - Hair Care Services</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <link href="https://fonts.googleapis.com/css?family=Montserrat:300,400,600,700" rel="stylesheet" />
    <link rel="stylesheet" href="css/open-iconic-bootstrap.min.css" />
    <link rel="stylesheet" href="css/animate.css" />
    <link rel="stylesheet" href="css/owl.carousel.min.css" />
    <link rel="stylesheet" href="css/owl.theme.default.min.css" />
    <link rel="stylesheet" href="css/magnific-popup.css" />
    <link rel="stylesheet" href="css/aos.css" />
    <link rel="stylesheet" href="css/ionicons.min.css" />
    <link rel="stylesheet" href="css/bootstrap-datepicker.css" />
    <link rel="stylesheet" href="css/jquery.timepicker.css" />
    <link rel="stylesheet" href="css/flaticon.css" />
    <link rel="stylesheet" href="css/icomoon.css" />
    <link rel="stylesheet" href="css/style.css" />
  </head>
  <body>
    <nav class="navbar navbar-expand-lg navbar-dark ftco_navbar bg-dark ftco-navbar-light" id="ftco-navbar">
      <div class="container">
        <a href="index.php" class="logo">
          <span class="flaticon-flower" style="color: black"></span>
        </a>
        <a class="navbar-brand" href="index.php">BELLEZZA</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ftco-nav" aria-controls="ftco-nav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="oi oi-menu"></span> Menu
        </button>

        <div class="collapse navbar-collapse" id="ftco-nav">
          <ul class="navbar-nav ml-auto">
            <li class="nav-item">
              <a href="index.php" class="nav-link">Home</a>
            </li>
            <li class="nav-item">
              <a href="about.php" class="nav-link">About</a>
            </li>
            <li class="nav-item dropdown active">
              <a href="services.php" class="nav-link dropdown" data-toggle="dropdown">Services</a>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="services.php">All Services</a>
                <a class="dropdown-item" href="hair.php">Hair</a>
                <a class="dropdown-item" href="skin.php">Skin</a>
                <a class="dropdown-item" href="makeup.php">Makeup</a>
              </div>
            </li>
            <li class="nav-item">
              <a href="work.php" class="nav-link">Work</a>
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
              session_start();
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

    <section class="ftco-section bg-light">
      <div class="container">
        <div class="row justify-content-center mb-5 pb-3">
          <div class="col-md-7 heading-section text-center ftco-animate">
            <h2 class="mb-4">Hair Care Services</h2>
            <p>Professional hair care services for all hair types and styles</p>
          </div>
        </div>
        <div class="row">
          <?php
          require_once 'connect.php';
          
          $sql = "SELECT * FROM service WHERE category = 'hair' ORDER BY name";
          $result = mysqli_query($conn, $sql);

          while ($service = mysqli_fetch_assoc($result)) {
            ?>
            <div class="col-md-4 ftco-animate">
              <div class="block-7">
                <div class="img" style="background-image: url(<?php echo htmlspecialchars($service['image_path']); ?>); height: 200px; background-size: cover; background-position: center; border-radius: 5px 5px 0 0;"></div>
                <div class="text-center p-4">
                  <h2 class="heading" style="font-size: 1.2rem;"><?php echo htmlspecialchars($service['name']); ?></h2>
                  <span class="excerpt d-block" style="font-size: 0.9rem;">Professional Hair Service</span>
                  <span class="price" style="font-size: 1.1rem;">
                    <sup>₱</sup>
                    <span class="number"><?php echo htmlspecialchars($service['price']); ?></span>
                  </span>
                  <a href="booking.php?service=hair&service_id=<?php echo htmlspecialchars($service['id']); ?>" 
                     class="btn btn-primary d-block px-3 py-2 mb-2" style="font-size: 0.9rem;">Book Now</a>
                </div>
              </div>
            </div>
          <?php
          }
          mysqli_close($conn);
          ?>
        </div>
      </div>
    </section>

    <footer class="ftco-footer ftco-section img">
      <div class="overlay"></div>
      <div class="container">
        <div class="row mb-5">
          <div class="col-md-3">
            <div class="ftco-footer-widget mb-4">
              <h2 class="ftco-heading-2">About Us</h2>
              <p>Experience luxury beauty care services that cater to your unique needs. Our professional team is dedicated to making you look and feel your best.</p>
              <ul class="ftco-footer-social list-unstyled float-md-left float-lft mt-5">
                <li class="ftco-animate"><a href="#"><span class="icon-twitter"></span></a></li>
                <li class="ftco-animate"><a href="#"><span class="icon-facebook"></span></a></li>
                <li class="ftco-animate"><a href="#"><span class="icon-instagram"></span></a></li>
              </ul>
            </div>
          </div>
          <div class="col-md-4">
            <div class="ftco-footer-widget mb-4">
              <h2 class="ftco-heading-2">Recent Blog</h2>
              <div class="block-21 mb-4 d-flex">
                <a class="blog-img mr-4" style="background-image: url(images/image_1.jpg)"></a>
                <div class="text">
                  <h3 class="heading"><a href="#">Hair Care Tips for Healthy Hair</a></h3>
                  <div class="meta">
                    <div><a href="#"><span class="icon-calendar"></span> July 12, 2023</a></div>
                    <div><a href="#"><span class="icon-person"></span> Admin</a></div>
                    <div><a href="#"><span class="icon-chat"></span> 19</a></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-2">
            <div class="ftco-footer-widget mb-4 ml-md-4">
              <h2 class="ftco-heading-2">Services</h2>
              <ul class="list-unstyled">
                <li><a href="hair.php" class="py-2 d-block">Hair Care</a></li>
                <li><a href="skin.php" class="py-2 d-block">Skin Care</a></li>
                <li><a href="makeup.php" class="py-2 d-block">Makeup</a></li>
              </ul>
            </div>
          </div>
          <div class="col-md-3">
            <div class="ftco-footer-widget mb-4">
              <h2 class="ftco-heading-2">Have Questions?</h2>
              <div class="block-23 mb-3">
                <ul>
                  <li><span class="icon icon-map-marker"></span><span class="text">203 Fake St. Mountain View, San Francisco, California, USA</span></li>
                  <li><a href="#"><span class="icon icon-phone"></span><span class="text">+2 392 3929 210</span></a></li>
                  <li><a href="#"><span class="icon icon-envelope"></span><span class="text">info@yourdomain.com</span></a></li>
                </ul>
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12 text-center">
            <p>Copyright &copy;<script>document.write(new Date().getFullYear());</script> All rights reserved</p>
          </div>
        </div>
      </div>
    </footer>

    <!-- loader -->
    <div id="ftco-loader" class="show fullscreen">
      <svg class="circular" width="48px" height="48px">
        <circle class="path-bg" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke="#eeeeee"/>
        <circle class="path" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke-miterlimit="10" stroke="#F96D00"/>
      </svg>
    </div>

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
