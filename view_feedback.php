<?php
session_start();
require_once "connect.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if booking_id is provided
if (!isset($_GET['booking_id'])) {
    header("Location: past_services.php");
    exit();
}

$booking_id = $_GET['booking_id'];
$user_id = $_SESSION['user_id'];

// Fetch feedback and booking details
$query = "SELECT 
            f.*,
            s.name as service_name,
            b.date as booking_date,
            staff.first_name as staff_first_name,
            staff.last_name as staff_last_name
          FROM feedback f
          JOIN bookings b ON f.booking_id = b.id
          JOIN service s ON b.specific_service = s.id
          LEFT JOIN user staff ON b.staff_member = staff.id
          WHERE f.booking_id = ? AND b.user_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: past_services.php");
    exit();
}

// Add this after the query to debug
if (!$stmt) {
    die("Query failed: " . $conn->error);
}

// After fetching the feedback, let's debug the data
$feedback = $result->fetch_assoc();
// var_dump($feedback); // Uncomment this line temporarily to see all available columns

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Bellezza - View Feedback</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    
    <link href="https://fonts.googleapis.com/css?family=Montserrat:300,400,600,700" rel="stylesheet">
    <link rel="stylesheet" href="css/open-iconic-bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">

    <style>
        .feedback-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 30px;
        }
        .rating-stars {
            color: #ffd700;
            font-size: 24px;
            margin: 15px 0;
        }
        .feedback-date {
            color: #6c757d;
            font-size: 0.9em;
        }
        .feedback-text {
            margin: 20px 0;
            font-size: 1.1em;
            line-height: 1.6;
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<section class="ftco-section">
    <div class="container">
        <div class="row justify-content-center mb-5">
            <div class="col-md-7 text-center">
                <h2 class="mb-4">Your Feedback</h2>
                <p>Review the feedback you provided</p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="mb-4">
                    <a href="past_services.php" class="btn btn-secondary">
                        <i class="oi oi-arrow-left"></i> Back to Past Services
                    </a>
                </div>

                <div class="feedback-card">
                    <h4><?php echo htmlspecialchars($feedback['service_name']); ?></h4>
                    <p>
                        <strong>Staff:</strong> 
                        <?php echo htmlspecialchars($feedback['staff_first_name'] . ' ' . $feedback['staff_last_name']); ?>
                    </p>
                    <p>
                        <strong>Service Date:</strong> 
                        <?php 
                        $date = new DateTime($feedback['booking_date']);
                        echo $date->format('F j, Y');
                        ?>
                    </p>
                    
                    <div class="rating-stars">
                        <?php
                        for ($i = 1; $i <= 5; $i++) {
                            echo ($i <= $feedback['rating']) ? '★' : '☆';
                        }
                        ?>
                    </div>

                    <div class="feedback-text">
                        <?php 
                        // Check if the column is 'comment' or 'feedback_text' or another name
                        $feedbackText = isset($feedback['comment']) ? $feedback['comment'] : 
                                       (isset($feedback['feedback_text']) ? $feedback['feedback_text'] : 
                                       (isset($feedback['message']) ? $feedback['message'] : ''));
                        echo nl2br(htmlspecialchars($feedbackText)); 
                        ?>
                    </div>

                    <p class="feedback-date">
                        Feedback submitted on: 
                        <?php 
                        $submitDate = new DateTime($feedback['created_at']);
                        echo $submitDate->format('F j, Y g:i A');
                        ?>
                    </p>
                </div>
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