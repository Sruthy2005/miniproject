<!DOCTYPE html>
<html lang="en">
  <head>
    <title>Bellezza</title>
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
          <span class="flaticon-flower" style="color: black"></span>
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
              <a href="about.html" class="nav-link">About</a>
            </li>
            <li class="nav-item">
              <a href="services.php" class="nav-link">Services</a>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="hair.php">Hair</a>
                <a class="dropdown-item" href="skin.php">Skin</a>
                <a class="dropdown-item" href="makeup.php">Makeup</a>
              </div>
            </li>
            <li class="nav-item dropdown">
              <?php
              session_start();
              if(isset($_SESSION['user_id'])) {
                // Fetch user details from database
                require_once "connect.php";
                $user_id = $_SESSION['user_id'];
                $query = "SELECT first_name, last_name FROM user WHERE id = '$user_id'";
                $result = mysqli_query($conn, $query);
                $user = mysqli_fetch_assoc($result);
                
                echo '<div class="profile-circle dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="icon-user"></span>
                        <span class="user-name">' . $user['first_name'] . ' ' . $user['last_name'] . '</span>
                      </div>
                      <div class="dropdown-menu">
                        <a class="dropdown-item" href="profile.php">Profile</a>
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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Beauty Care Services</title>
    <style>
      * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
        font-family: "Arial", sans-serif;
      }

      body {
        background-color: #f9f9f9;
        padding: 2rem;
      }

      h1 {
        text-align: center;
        font-size: 2.5rem;
        color: #333;
        margin-bottom: 2rem;
      }

      .service-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 2rem;
        max-width: 1200px;
        margin: 0 auto;
      }

      .service-box {
        background-color: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        padding: 2rem;
        text-align: center;
        transition: transform 0.2s, box-shadow 0.2s;
        display: flex;
        flex-direction: column;
        align-items: center;
      }

      .service-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
      }

      .service-image {
        width: 200px;
        height: 150px;
        object-fit: cover;
        margin-bottom: 1.5rem;
        padding: 5px;
      }

      .service-name {
        font-size: 1.5rem;
        color: #333;
        margin-bottom: 0.75rem;
        font-weight: 600;
      }

      .service-price {
        font-size: 1.25rem;
        color: #666;
        margin-bottom: 1.5rem;
        font-weight: 500;
      }

      .appointment-button {
        background-color: #31aee7;
        color: white;
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 1rem;
        transition: background-color 0.3s, transform 0.2s;
        width: 100%;
        max-width: 200px;
      }

      .appointment-button:hover {
        background-color: blue;
        transform: scale(1.05);
      }

      @media (max-width: 768px) {
        h1 {
          font-size: 2rem;
        }

        .service-grid {
          gap: 1.5rem;
        }

        .service-box {
          padding: 1.5rem;
        }

        .service-name {
          font-size: 1.4rem;
        }

        .service-price {
          font-size: 1.1rem;
        }

        .appointment-button {
          padding: 0.5rem 1rem;
          font-size: 0.9rem;
        }
      }
    </style>
  </head>
  <body>
    <h1>Our Beauty Care Services</h1>
    <div class="service-grid">
      <?php
      require_once 'connect.php';
      
      $sql = "SELECT * FROM service WHERE category = 'skin' ORDER BY name";
      $result = mysqli_query($conn, $sql);

      while ($service = mysqli_fetch_assoc($result)) {
        ?>
        <div class="service-box">
          <img
            src="<?php echo htmlspecialchars($service['image_path']); ?>"
            alt="<?php echo htmlspecialchars($service['name']); ?>"
            class="service-image"
          />
          <h2 class="service-name"><?php echo htmlspecialchars($service['name']); ?></h2>
          <p class="service-price">₱<?php echo htmlspecialchars($service['price']); ?></p>
          <a href="booking.php" class="appointment-button">Book Appointment</a>
        </div>
      <?php
      }
      mysqli_close($conn);
      ?>
    </div>
  </body>
</html>
