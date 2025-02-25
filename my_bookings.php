<?php
session_start();
require_once "connect.php";

// Check if user is logged in, redirect if not
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user's bookings
$user_id = $_SESSION['user_id'];
$query = "SELECT 
            b.*, 
            u.first_name as staff_first_name, 
            u.last_name as staff_last_name,
            s.name as service_name,
            s.price as service_price
          FROM bookings b
          LEFT JOIN user u ON b.staff_member = u.id
          LEFT JOIN service s ON b.service_category = s.id
          WHERE b.user_id = ?
          ORDER BY b.date DESC, b.time DESC";

$stmt = $conn->prepare($query);
if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$bookings = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Bellezza - My Bookings</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    
    <!-- Include your existing CSS files -->
    <link href="https://fonts.googleapis.com/css?family=Montserrat:300,400,600,700" rel="stylesheet">
    <link rel="stylesheet" href="css/open-iconic-bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">

    <style>
        .booking-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            padding: 20px;
            transition: transform 0.2s;
        }

        .booking-card:hover {
            transform: translateY(-5px);
        }

        .booking-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 600;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-confirmed {
            background: #d4edda;
            color: #155724;
        }

        .status-completed {
            background: #cce5ff;
            color: #004085;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .booking-date {
            color: #71caf3;
            font-weight: 600;
        }

        .booking-actions {
            margin-top: 15px;
        }

        .no-bookings {
            text-align: center;
            padding: 40px;
            background: #f8f9fa;
            border-radius: 10px;
            margin: 20px 0;
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<section class="ftco-section">
    <div class="container">
        <div class="row justify-content-center mb-5">
            <div class="col-md-7 text-center">
                <h2 class="mb-4">My Bookings</h2>
                <p>View and manage your appointments</p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <?php if (empty($bookings)): ?>
                    <div class="no-bookings">
                        <h4>No bookings found</h4>
                        <p>You haven't made any appointments yet.</p>
                        <a href="booking.php" class="btn btn-primary">Book Now</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($bookings as $booking): ?>
                        <div class="booking-card">
                            <div class="row">
                                <div class="col-md-8">
                                    <h4><?php echo htmlspecialchars($booking['service_name']); ?></h4>
                                    <p>
                                        <strong>Staff:</strong> 
                                        <?php echo htmlspecialchars($booking['staff_first_name'] . ' ' . $booking['staff_last_name']); ?>
                                    </p>
                                    <p class="booking-date">
                                        <?php 
                                        $date = new DateTime($booking['date']);
                                        $time = new DateTime($booking['time']);
                                        echo $date->format('F j, Y') . ' at ' . $time->format('g:i A');
                                        ?>
                                    </p>
                                    <p><strong>Price:</strong> $<?php echo number_format($booking['service_price'], 2); ?></p>
                                    <?php if ($booking['notes']): ?>
                                        <p><strong>Notes:</strong> <?php echo htmlspecialchars($booking['notes']); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-4 text-right">
                                    <span class="booking-status status-<?php echo strtolower($booking['status']); ?>">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                    
                                    <?php if ($booking['status'] == 'pending' || $booking['status'] == 'confirmed'): ?>
                                        <div class="booking-actions">
                                            <?php if (strtotime($booking['date']) > strtotime('+24 hours')): ?>
                                                <a href="reschedule_booking.php?id=<?php echo $booking['id']; ?>" 
                                                   class="btn btn-sm btn-primary">Reschedule</a>
                                                <a href="cancel_booking.php?id=<?php echo $booking['id']; ?>" 
                                                   class="btn btn-sm btn-danger"
                                                   onclick="return confirm('Are you sure you want to cancel this booking?')">
                                                    Cancel
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>

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