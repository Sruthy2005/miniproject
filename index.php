<!DOCTYPE html>
<html lang="en">
  <head>
    <title>Bellezza</title>
    <style>
    .nav-item .dropdown-toggle {
      display: inline-flex;
      align-items: center;
      cursor: pointer;
      background: rgba(255, 255, 255, 0.9);
      padding: 8px 15px;
      border-radius: 25px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }

    .nav-item .icon-user {
      margin-right: 8px;
      font-size: 16px;
      color: #000;
    }

    .profile-circle {
      display: flex;
      align-items: center;
    }

    .user-name {
      color: #000;
      font-weight: 500;
    }

    .dropdown-menu {
      margin-top: 5px;
      border-radius: 10px;
      box-shadow: 0 3px 6px rgba(0,0,0,0.16);
    }
    </style>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1, shrink-to-fit=no"
    />

    <link
      href="https://fonts.googleapis.com/css?family=Montserrat:300,400,600,700"
      rel="stylesheet"
    />

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
    <link rel="stylesheet" href="css/profile.css" />
  </head>
  <body>
    <nav
      class="navbar navbar-expand-lg navbar-dark ftco_navbar bg-dark ftco-navbar-light"
      id="ftco-navbar"
    >
      <div class="container">
        <a href="index.php" class="logo">
          <span class="flaticon-flower"  style="color: black; font-size: 30px;" ></span>
        </a>
        <a class="navbar-brand" href="index.php">BELLEZZA</a>
        <div class="icon"></div>
        <button
          class="navbar-toggler"
          type="button"
          data-toggle="collapse"
          data-target="#ftco-nav"
          aria-controls="ftco-nav"
          aria-expanded="false"
          aria-label="Toggle navigation"
        >
          <span class="oi oi-menu"></span> Menu
        </button>

        <div class="collapse navbar-collapse" id="ftco-nav">
          <ul class="navbar-nav ml-auto">
            <li class="nav-item active">
              <a href="index.php" class="nav-link">Home</a>
            </li>
            <li class="nav-item">
              <a href="about.html"s class="nav-link">About</a>
            </li>
            <li class="nav-item dropdown">
              <a href="services.php" class="nav-link dropdown" data-toggle="dropdown">Services</a>
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
              session_start();
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
    <div
      class="hero-wrap js-fullheight"
      style="background-image: url('images/bggg.jpg')"
      data-stellar-background-ratio="0.5"
    >
      <div class="overlay"></div>
      <div class="container">
        <div
          class="row no-gutters slider-text js-fullheight align-items-center justify-content-center"
          data-scrollax-parent="true"
        >
          <div
            class="col-md-8 ftco-animate text-center"
            data-scrollax=" properties: { translateY: '70%' }"
          >
            <div class="icon">
              <a href="index.php" class="logo">
                <span class="flaticon-flower" style="color: black"></span>
                <h1>BELLEZZA</h1>
              </a>
            </div>
            <h1
              class="mb-4"
              data-scrollax="properties: { translateY: '30%', opacity: 1.6 }"
            >
              BEAUTY SERVICES AT HOME
            </h1>
          </div>
        </div>
      </div>
    </div>

    <!-- END nav -->

    <section class="ftco-section">
      <div class="container">
        <div class="row">
          <div class="col-md-4 ftco-animate">
            <div class="media d-block text-center block-6 services">
              <div class="icon d-flex mb-3">
                <span class="flaticon-facial-treatment"></span>
              </div>
              <div class="media-body">
                <a href="skin.php">
                  <h3 class="heading">Skin &amp; Beauty Care</h3>
                </a>
                <p>
                  Even the all-powerful Pointing has no control about the blind
                  texts it is an almost unorthographic.
                </p>
              </div>
            </div>
          </div>
          <div class="col-md-4 ftco-animate">
            <div class="media d-block text-center block-6 services">
              <div class="icon d-flex mb-3">
                <span class="flaticon-cosmetics"></span>
              </div>
              <div class="media-body">
                <a href="makeup.php">
                <h3 class="heading">Makeup Pro</h3>
                </a>
                <p>
                  Even the all-powerful Pointing has no control about the blind
                  texts it is an almost unorthographic.
                </p>
              </div>
            </div>
          </div>
          <div class="col-md-4 ftco-animate">
            <div class="media d-block text-center block-6 services">
              <div class="icon d-flex mb-3">
                <span class="flaticon-curl"></span>
              </div>
                <div class="media-body">
                <a href="hair.php">
                <h3 class="heading">Hair Style</h3>
                </a>
                <p>
                  Even the all-powerful Pointing has no control about the blind
                  texts it is an almost unorthographic.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="ftco-section bg-light">
      <div class="container">
        <div class="row justify-content-center mb-5 pb-3">
          <div class="col-md-7 heading-section ftco-animate text-center">
            <h2 class="mb-4">Our Beauty Experts</h2>
            <p>
              Meet our talented team of beauty professionals who are dedicated to helping you look and feel your best.
            </p>
          </div>
        </div>
        <div class="row">
          <?php
          require_once "connect.php";
          
          // Query to get active staff members
          $sql = "SELECT * FROM user WHERE role='staff' AND status='active'";
          $result = mysqli_query($conn, $sql);

          while($staff = mysqli_fetch_assoc($result)) {
            ?>
            <div class="col-lg-3 d-flex mb-sm-4 ftco-animate">
              <div class="staff">
                <div class="img mb-4" style="width: 200px; height: 200px; border-radius: 50%; overflow: hidden; margin: 0 auto; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                  <img src="<?php 
                    $profile_query = "SELECT profile_image FROM user WHERE id = " . $staff['id'];
                    $profile_result = mysqli_query($conn, $profile_query);
                    $profile_data = mysqli_fetch_assoc($profile_result);
                    
                    if ($profile_data && !empty($profile_data['profile_image'])) {
                      echo htmlspecialchars($profile_data['profile_image']);
                    } else {
                      echo 'images/staff/default-profile.jpg';
                    }
                  ?>" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                </div>
                <div class="info text-center">
                  <h3>
                    <a href="staff_profile.php?id=<?php echo htmlspecialchars($staff['id']); ?>" 
                       style="color: #333; text-decoration: none; font-size: 1.2em; font-weight: 600;">
                      <?php echo htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']); ?>
                    </a>
                  </h3>
                  <span class="position" style="color: #71caf3; font-weight: 500; display: block; margin: 8px 0;">
                    <?php 
                      echo !empty($staff['specialization']) 
                        ? htmlspecialchars($staff['specialization'])
                        : 'Beauty Professional'; 
                    ?>
                  </span>
                  <div class="text">
                    <p style="color: #666; font-size: 0.9em; line-height: 1.6;">
                      <?php 
                        echo !empty($staff['bio'])
                          ? htmlspecialchars($staff['bio'])
                          : 'A dedicated beauty professional passionate about helping clients look and feel their best.';
                      ?>
                    </p>
                  </div>
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

    

    <section class="ftco-section">
      <div class="container">
        <div class="row justify-content-center mb-5 pb-3">
          <div class="col-md-7 heading-section text-center ftco-animate">
            <h2 class="mb-4">Our Work</h2>
            <p>
              Far far away, behind the word mountains, far from the countries
              Vokalia and Consonantia, there live the blind texts.
            </p>
          </div>
        </div>
        <div class="row">
          <div class="col-md-4 ftco-animate">
            <a href="#" class="work-entry">
              <img
                src="images/work-1.jpg"
                class="img-fluid"
                alt="Colorlib Template"
              />
              <div class="info d-flex align-items-center">
                <div>
                  <div
                    class="icon mb-4 d-flex align-items-center justify-content-center"
                  >
                    <span class="icon-search"></span>
                  </div>
                  <h3>Lips Makeover</h3>
                </div>
              </div>
            </a>
          </div>
          <div class="col-md-4 ftco-animate">
            <a href="#" class="work-entry">
              <img
                src="images/work-2.jpg"
                class="img-fluid"
                alt="Colorlib Template"
              />
              <div class="info d-flex align-items-center">
                <div>
                  <div
                    class="icon mb-4 d-flex align-items-center justify-content-center"
                  >
                    <span class="icon-search"></span>
                  </div>
                  <h3>Hair Style</h3>
                </div>
              </div>
            </a>
          </div>
          <div class="col-md-4 ftco-animate">
            <a href="#" class="work-entry">
              <img
                src="images/work-3.jpg"
                class="img-fluid"
                alt="Colorlib Template"
              />
              <div class="info d-flex align-items-center">
                <div>
                  <div
                    class="icon mb-4 d-flex align-items-center justify-content-center"
                  >
                    <span class="icon-search"></span>
                  </div>
                  <h3>Makeup</h3>
                </div>
              </div>
            </a>
          </div>
        </div>
      </div>
    </section>

    <section class="ftco-partner bg-light">
      <div class="container">
        <div class="row partner justify-content-center">
          <div class="col-md-10">
            <div class="row">
              <div class="col-md-3 ftco-animate">
                <a href="#" class="partner-entry">
                  <img
                    src="images/partner-1.jpg"
                    class="img-fluid"
                    alt="Colorlib template"
                  />
                </a>
              </div>
              <div class="col-md-3 ftco-animate">
                <a href="#" class="partner-entry">
                  <img
                    src="images/partner-2.jpg"
                    class="img-fluid"
                    alt="Colorlib template"
                  />
                </a>
              </div>
              <div class="col-md-3 ftco-animate">
                <a href="#" class="partner-entry">
                  <img
                    src="images/partner-3.jpg"
                    class="img-fluid"
                    alt="Colorlib template"
                  />
                </a>
              </div>
              <div class="col-md-3 ftco-animate">
                <a href="#" class="partner-entry">
                  <img
                    src="images/partner-4.jpg"
                    class="img-fluid"
                    alt="Colorlib template"
                  />
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="ftco-section">
      <div class="container">
        <div class="row justify-content-center mb-5 pb-3">
          <div class="col-md-7 heading-section text-center ftco-animate">
            <h2 class="mb-4">Our Services & Pricing</h2>
            <p>Choose from our range of professional beauty services</p>
          </div>
        </div>
        <div class="row">
          <div class="col-md-4 ftco-animate">
            <div class="pricing-entry pb-5 text-center">
              <div>
                <h3 class="mb-4">Skin Care Services</h3>
              </div>
              <ul class="pricing-list">
                <li>Facial Basic <span class="price">$45</span></li>
                <li>Deep Cleansing Facial <span class="price">$65</span></li>
                <li>Anti-Aging Treatment <span class="price">$85</span></li>
                <li>Acne Treatment <span class="price">$55</span></li>
                <li>Skin Brightening <span class="price">$75</span></li>
              </ul>
              <p class="button text-center">
                <a href="booking.php" class="btn btn-primary px-4 py-3">Book Now</a>
              </p>
            </div>
          </div>
          <div class="col-md-4 ftco-animate">
            <div class="pricing-entry pb-5 text-center">
              <div>
                <h3 class="mb-4">Makeup Services</h3>
              </div>
              <ul class="pricing-list">
                <li>Natural Makeup <span class="price">$50</span></li>
                <li>Party Makeup <span class="price">$75</span></li>
                <li>Bridal Makeup <span class="price">$150</span></li>
                <li>Eye Makeup <span class="price">$35</span></li>
                <li>Makeup Lesson <span class="price">$80</span></li>
              </ul>
              <p class="button text-center">
                <a href="booking.php" class="btn btn-primary px-4 py-3">Book Now</a>
              </p>
            </div>
          </div>
          <div class="col-md-4 ftco-animate">
            <div class="pricing-entry pb-5 text-center">
              <div>
                <h3 class="mb-4">Hair Services</h3>
              </div>
              <ul class="pricing-list">
                <li>Haircut & Styling <span class="price">$40</span></li>
                <li>Hair Coloring <span class="price">$85</span></li>
                <li>Hair Treatment <span class="price">$65</span></li>
                <li>Hair Extension <span class="price">$150</span></li>
                <li>Bridal Hairstyle <span class="price">$120</span></li>
              </ul>
              <p class="button text-center">
                <a href="booking.php" class="btn btn-primary px-4 py-3">Book Now</a>
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section
      class="ftco-section ftco-counter img"
      id="section-counter"
      style="background-image: url(images/bg_4.jpg)"
    >
      <div class="overlay"></div>
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-md-10">
            <div class="row">
              <div
                class="col-md-6 col-lg-3 d-flex justify-content-center counter-wrap ftco-animate"
              >
                <div class="block-18 text-center">
                  <div class="text">
                    <div class="icon">
                      <span class="flaticon-flower"></span>
                    </div>
                    <span>Makeup Over Done</span>
                    <strong class="number" data-number="3500">0</strong>
                  </div>
                </div>
              </div>
              <div
                class="col-md-6 col-lg-3 d-flex justify-content-center counter-wrap ftco-animate"
              >
                <div class="block-18 text-center">
                  <div class="text">
                    <div class="icon">
                      <span class="flaticon-flower"></span>
                    </div>
                    <span>Procedure</span>
                    <strong class="number" data-number="1000">0</strong>
                  </div>
                </div>
              </div>
              <div
                class="col-md-6 col-lg-3 d-flex justify-content-center counter-wrap ftco-animate"
              >
                <div class="block-18 text-center">
                  <div class="text">
                    <div class="icon">
                      <span class="flaticon-flower"></span>
                    </div>
                    <span>Happy Client</span>
                    <strong class="number" data-number="3000">0</strong>
                  </div>
                </div>
              </div>
              <div
                class="col-md-6 col-lg-3 d-flex justify-content-center counter-wrap ftco-animate"
              >
                <div class="block-18 text-center">
                  <div class="text">
                    <div class="icon">
                      <span class="flaticon-flower"></span>
                    </div>
                    <span>Skin Treatment</span>
                    <strong class="number" data-number="900">0</strong>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="ftco-section">
      <div class="container">
        <div class="row justify-content-center mb-5 pb-3">
          <div class="col-md-7 heading-section ftco-animate text-center">
            <h2 class="mb-4">Recent from blog</h2>
            <p>
              Far far away, behind the word mountains, far from the countries
              Vokalia and Consonantia, there live the blind texts.
            </p>
          </div>
        </div>
        <div class="row d-flex">
          <div class="col-md-4 d-flex ftco-animate">
            <div class="blog-entry align-self-stretch">
              <a
                href="blog-single.html"
                class="block-20"
                style="background-image: url('images/image_1.jpg')"
              >
              </a>
              <div class="text py-4 d-block">
                <div class="meta">
                  <div><a href="#">Sept 10, 2018</a></div>
                  <div><a href="#">Admin</a></div>
                  <div>
                    <a href="#" class="meta-chat"
                      ><span class="icon-chat"></span> 3</a
                    >
                  </div>
                </div>
                <h3 class="heading mt-2">
                  <a href="#">Skin Care for Teen Skin</a>
                </h3>
                <p>
                  A small river named Duden flows by their place and supplies it
                  with the necessary regelialia.
                </p>
              </div>
            </div>
          </div>
          <div class="col-md-4 d-flex ftco-animate">
            <div class="blog-entry align-self-stretch">
              <a
                href="blog-single.html"
                class="block-20"
                style="background-image: url('images/image_2.jpg')"
              >
              </a>
              <div class="text py-4 d-block">
                <div class="meta">
                  <div><a href="#">Sept 10, 2018</a></div>
                  <div><a href="#">Admin</a></div>
                  <div>
                    <a href="#" class="meta-chat"
                      ><span class="icon-chat"></span> 3</a
                    >
                  </div>
                </div>
                <h3 class="heading mt-2">
                  <a href="#">Skin Care for Teen Skin</a>
                </h3>
                <p>
                  A small river named Duden flows by their place and supplies it
                  with the necessary regelialia.
                </p>
              </div>
            </div>
          </div>
          <div class="col-md-4 d-flex ftco-animate">
            <div class="blog-entry align-self-stretch">
              <a
                href="blog-single.html"
                class="block-20"
                style="background-image: url('images/image_3.jpg')"
              >
              </a>
              <div class="text py-4 d-block">
                <div class="meta">
                  <div><a href="#">Sept 10, 2018</a></div>
                  <div><a href="#">Admin</a></div>
                  <div>
                    <a href="#" class="meta-chat"
                      ><span class="icon-chat"></span> 3</a
                    >
                  </div>
                </div>
                <h3 class="heading mt-2">
                  <a href="#">Skin Care for Teen Skin</a>
                </h3>
                <p>
                  A small river named Duden flows by their place and supplies it
                  with the necessary regelialia.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="ftco-section ftco-appointment" id="my-bookings">
      <div class="overlay"></div>
      <div class="container">
        <div class="row d-md-flex align-items-center">
          <div class="col-md-2"></div>
          <div class="col-md-4 d-flex align-self-stretch ftco-animate">
            <div class="appointment-info text-center p-5">
              <div class="mb-4">
                <h3 class="mb-3">Address</h3>
                <p>
                  203 Fake St. Mountain View, San Francisco, California, USA
                </p>
              </div>
              <div class="mb-4">
                <h3 class="mb-3">Phone</h3>
                <p class="day">
                  <strong>09123456789</strong> or <strong>09876543210</strong>
                </p>
              </div>
              <div>
                <h3 class="mb-3">Opening Hours</h3>
                <p class="day"><strong>Monday - Sunday</strong></p>
                <span>7:00am - 10:00pm</span>
              </div>
            </div>
          </div>
          <div class="col-md-6 appointment pl-md-5 ftco-animate">
            <h3 class="mb-3">Booking Management</h3>
            <form action="booking.php" method="GET" class="appointment-form">
              <div class="form-group">
                <input type="submit" value="Bookings" class="btn btn-white btn-outline-white py-3 px-4">
              </div>
            </form>
          </div>
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
              <p>
                Far far away, behind the word mountains, far from the countries
                Vokalia and Consonantia, there live the blind texts.
              </p>
              <ul
                class="ftco-footer-social list-unstyled float-md-left float-lft mt-5"
              >
                <li class="ftco-animate">
                  <a href="#"><span class="icon-twitter"></span></a>
                </li>
                <li class="ftco-animate">
                  <a href="#"><span class="icon-facebook"></span></a>
                </li>
                <li class="ftco-animate">
                  <a href="#"><span class="icon-instagram"></span></a>
                </li>
              </ul>
            </div>
          </div>
          <div class="col-md-4">
            <div class="ftco-footer-widget mb-4">
              <h2 class="ftco-heading-2">Recent Blog</h2>
              <div class="block-21 mb-4 d-flex">
                <a
                  class="blog-img mr-4"
                  style="background-image: url(images/image_1.jpg)"
                ></a>
                <div class="text">
                  <h3 class="heading">
                    <a href="#"
                      >Even the all-powerful Pointing has no control about</a
                    >
                  </h3>
                  <div class="meta">
                    <div>
                      <a href="#"
                        ><span class="icon-calendar"></span> July 12, 2018</a
                      >
                    </div>
                    <div>
                      <a href="#"><span class="icon-person"></span> Admin</a>
                    </div>
                    <div>
                      <a href="#"><span class="icon-chat"></span> 19</a>
                    </div>
                  </div>
                </div>
              </div>
              <div class="block-21 mb-4 d-flex">
                <a
                  class="blog-img mr-4"
                  style="background-image: url(images/image_2.jpg)"
                ></a>
                <div class="text">
                  <h3 class="heading">
                    <a href="#"
                      >Even the all-powerful Pointing has no control about</a
                    >
                  </h3>
                  <div class="meta">
                    <div>
                      <a href="#"
                        ><span class="icon-calendar"></span> July 12, 2018</a
                      >
                    </div>
                    <div>
                      <a href="#"><span class="icon-person"></span> Admin</a>
                    </div>
                    <div>
                      <a href="#"><span class="icon-chat"></span> 19</a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-2">
            <div class="ftco-footer-widget mb-4 ml-md-4">
              <h2 class="ftco-heading-2">Spa Center</h2>
              <ul class="list-unstyled">
                <li><a href="#" class="py-2 d-block">Body Care</a></li>
                <li><a href="#" class="py-2 d-block">Massage</a></li>
                <li><a href="#" class="py-2 d-block">Hydrotherapy</a></li>
                <li><a href="#" class="py-2 d-block">Yoga</a></li>
                <li><a href="#" class="py-2 d-block">Sauna</a></li>
                <li><a href="#" class="py-2 d-block">Aquazone</a></li>
              </ul>
            </div>
          </div>
          <div class="col-md-3">
            <div class="ftco-footer-widget mb-4">
              <h2 class="ftco-heading-2">Have a Questions?</h2>
              <div class="block-23 mb-3">
                <ul>
                  <li>
                    <span class="icon icon-map-marker"></span
                    ><span class="text"
                      >203 Fake St. Mountain View, San Francisco, California,
                      USA</span
                    >
                  </li>
                  <li>
                    <a href="#"
                      ><span class="icon icon-phone"></span
                      ><span class="text">+2 392 3929 210</span></a
                    >
                  </li>
                  <li>
                    <a href="#"
                      ><span class="icon icon-envelope"></span
                      ><span class="text">info@yourdomain.com</span></a
                    >
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12 text-center">
            <p>
              <!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. -->
              Copyright &copy;
              <script>
                document.write(new Date().getFullYear());
              </script>
              All rights reserved | This template is made with
              <i class="icon-heart" aria-hidden="true"></i> by
              <a href="https://colorlib.com" target="_blank">Colorlib</a>
              <!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. -->
            </p>
          </div>
        </div>
      </div>
    </footer>

    <!-- loader -->
    <div id="ftco-loader" class="show fullscreen">
      <svg class="circular" width="48px" height="48px">
        <circle
          class="path-bg"
          cx="24"
          cy="24"
          r="22"
          fill="none"
          stroke-width="4"
          stroke="#eeeeee"
        />
        <circle
          class="path"
          cx="24"
          cy="24"
          r="22"
          fill="none"
          stroke-width="4"
          stroke-miterlimit="10"
          stroke="#F96D00"
        />
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
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBVWaKrjvy3MaE7SQ74_uJiULgl1JY0H2s&sensor=false"></script>
    <script src="js/google-map.js"></script>
    <script src="js/main.js"></script>
  </body>
</html>
