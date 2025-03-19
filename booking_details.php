<?php
session_start();
require_once "connect.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if booking ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Invalid booking ID";
    header("Location: my_bookings.php");
    exit();
}

$booking_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Fetch booking details
$query = "SELECT 
            b.*,
            s.name as service_name,
            s.description as service_description,
            s.price as service_price,
            p.payment_id,
            p.created_at as payment_date
          FROM bookings b
          JOIN service s ON b.specific_service = s.id
          LEFT JOIN payments p ON b.payment_id = p.payment_id
          WHERE b.id = ? AND b.user_id = ?";

$stmt = $conn->prepare($query);
if ($stmt === false) {
    $_SESSION['error'] = "Database error: " . $conn->error;
    header("Location: my_bookings.php");
    exit();
}

$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Booking not found";
    header("Location: my_bookings.php");
    exit();
}

$booking = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Bellezza - Booking Details</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    
    <link href="https://fonts.googleapis.com/css?family=Montserrat:300,400,600,700" rel="stylesheet">
    <link rel="stylesheet" href="css/open-iconic-bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    
    <style>
        .booking-details {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .booking-status {
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: 600;
            display: inline-block;
        }
        .status-confirmed {
            background: #d4edda;
            color: #155724;
        }
        .payment-info {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<section class="ftco-section">
    <div class="container">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <div class="row justify-content-center mb-5">
            <div class="col-md-7 text-center">
                <h2 class="mb-4">Booking Details</h2>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="booking-details">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4><?php echo htmlspecialchars($booking['service_name']); ?></h4>
                        <span class="booking-status status-confirmed">
                            <?php echo ucfirst(htmlspecialchars($booking['status'])); ?>
                        </span>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Date:</strong><br>
                            <?php 
                            $date = new DateTime($booking['date']);
                            echo $date->format('F j, Y');
                            ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Time:</strong><br>
                            <?php 
                            $time = new DateTime($booking['time']);
                            echo $time->format('g:i A');
                            ?>
                            </p>
                        </div>
                    </div>

                    <p><strong>Staff Member:</strong><br>
                    <?php echo htmlspecialchars($booking['staff_member']); ?></p>

                    <?php if (!empty($booking['notes'])): ?>
                        <p><strong>Special Notes:</strong><br>
                        <?php echo nl2br(htmlspecialchars($booking['notes'])); ?></p>
                    <?php endif; ?>

                    <div class="payment-info">
                        <h5>Payment Information</h5>
                        <p><strong>Amount Paid:</strong> ₱<?php echo number_format($booking['service_price'], 2); ?></p>
                        <p><strong>Payment ID:</strong> <?php echo htmlspecialchars($booking['payment_id']); ?></p>
                        <p><strong>Payment Date:</strong> 
                        <?php 
                        $payment_date = new DateTime($booking['payment_date']);
                        echo $payment_date->format('F j, Y g:i A');
                        ?>
                        </p>
                    </div>

                    <div class="text-center mt-4">
                        <a href="my_bookings.php" class="btn btn-primary">Back to My Bookings</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>

<!-- Scripts -->
<script src="js/jquery.min.js"></script>
<script src="js/jquery-migrate-3.0.1.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/main.js"></script>

</body>
</html> 