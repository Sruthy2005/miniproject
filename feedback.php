<?php
session_start();
require_once "connect.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if booking ID is provided
if (!isset($_GET['booking_id']) || empty($_GET['booking_id'])) {
    $_SESSION['error'] = "Invalid booking ID";
    header("Location: my_bookings.php");
    exit();
}

$booking_id = $_GET['booking_id'];
$user_id = $_SESSION['user_id'];

// Fetch booking details to verify it belongs to the current user
$query = "SELECT 
            b.*,
            s.name as service_name,
            staff.first_name as staff_first_name,
            staff.last_name as staff_last_name
          FROM bookings b
          JOIN service s ON b.specific_service = s.id
          LEFT JOIN user staff ON b.staff_member = staff.id
          WHERE b.id = ? AND b.user_id = ? AND b.payment_status = 'paid'";

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
    $_SESSION['error'] = "Booking not found or payment not completed";
    header("Location: my_bookings.php");
    exit();
}

$booking = $result->fetch_assoc();

// Check if feedback has already been submitted
$feedback_query = "SELECT * FROM feedback WHERE booking_id = ?";
$feedback_stmt = $conn->prepare($feedback_query);
$feedback_stmt->bind_param("i", $booking_id);
$feedback_stmt->execute();
$feedback_result = $feedback_stmt->get_result();

$has_feedback = $feedback_result->num_rows > 0;
$feedback = $has_feedback ? $feedback_result->fetch_assoc() : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Bellezza - Feedback</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    
    <link href="https://fonts.googleapis.com/css?family=Montserrat:300,400,600,700" rel="stylesheet">
    <link rel="stylesheet" href="css/open-iconic-bootstrap.min.css">
    <link rel="stylesheet" href="css/animate.css">
    <link rel="stylesheet" href="css/style.css">
    
    <!-- Font Awesome for stars -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <style>
        .feedback-container {
            background: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            margin: 40px auto;
        }
        
        .rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: center;
            margin: 30px 0;
        }
        
        .rating > input {
            display: none;
        }
        
        .rating > label {
            position: relative;
            width: 1.1em;
            font-size: 3em;
            color: #FFD700;
            cursor: pointer;
        }
        
        .rating > label::before {
            content: "\2605";
            position: absolute;
            opacity: 0;
        }
        
        .rating > label:hover:before,
        .rating > label:hover ~ label:before {
            opacity: 1 !important;
        }
        
        .rating > input:checked ~ label:before {
            opacity: 1;
        }
        
        .rating:hover > input:checked ~ label:before {
            opacity: 0.4;
        }
        
        .feedback-submitted {
            text-align: center;
            padding: 20px;
        }
        
        .feedback-submitted i {
            color: #28a745;
            font-size: 4em;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<section class="ftco-section">
    <div class="container">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

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
                <h2 class="mb-4">Your Feedback Matters!</h2>
                <p>Tell us about your experience at Bellezza</p>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="feedback-container">
                    <?php if ($has_feedback): ?>
                        <!-- Display submitted feedback -->
                        <div class="feedback-submitted">
                            <i class="fas fa-check-circle"></i>
                            <h4>Thank you for your feedback!</h4>
                            <p>You gave this service a rating of <strong><?php echo $feedback['rating']; ?> out of 5</strong>.</p>
                            
                            <?php if (!empty($feedback['comments'])): ?>
                                <div class="mt-4">
                                    <h5>Your Comments:</h5>
                                    <p class="p-3 bg-light rounded"><?php echo nl2br(htmlspecialchars($feedback['comments'])); ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <div class="mt-4">
                                <a href="booking_details.php?id=<?php echo $booking_id; ?>" class="btn btn-primary">View Booking Details</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Feedback form -->
                        <form action="submit_feedback.php" method="POST">
                            <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
                            
                            <div class="form-group">
                                <label>Service:</label>
                                <p class="font-weight-bold"><?php echo htmlspecialchars($booking['service_name']); ?></p>
                            </div>
                            
                            <div class="form-group">
                                <label>Staff Member:</label>
                                <p class="font-weight-bold">
                                    <?php 
                                    if (!empty($booking['staff_first_name']) && !empty($booking['staff_last_name'])) {
                                        echo htmlspecialchars($booking['staff_first_name'] . ' ' . $booking['staff_last_name']);
                                    } else {
                                        echo 'Not assigned';
                                    }
                                    ?>
                                </p>
                            </div>
                            
                            <div class="form-group text-center">
                                <label>How would you rate your experience?</label>
                                <div class="rating">
                                    <input type="radio" name="rating" value="5" id="star5" required><label for="star5">★</label>
                                    <input type="radio" name="rating" value="4" id="star4"><label for="star4">★</label>
                                    <input type="radio" name="rating" value="3" id="star3"><label for="star3">★</label>
                                    <input type="radio" name="rating" value="2" id="star2"><label for="star2">★</label>
                                    <input type="radio" name="rating" value="1" id="star1"><label for="star1">★</label>
                                </div>
                                <style>
                                .rating {
                                    display: flex;
                                    flex-direction: row-reverse;
                                    justify-content: center;
                                }
                                .rating input {
                                    display: none;
                                }
                                .rating label {
                                    cursor: pointer;
                                    font-size: 30px;
                                    color: #ddd;
                                    padding: 5px;
                                }
                                .rating input:checked ~ label {
                                    color: #ffd700;
                                }
                                .rating label:hover,
                                .rating label:hover ~ label {
                                    color: #ffd700;
                                }
                                </style>
                            </div>
                            
                            <div class="form-group">
                                <label for="comments">Additional Comments (Optional):</label>
                                <textarea class="form-control" id="comments" name="comments" rows="4" placeholder="Tell us more about your experience..."></textarea>
                            </div>
                            
                            <div class="form-group text-center mt-4">
                                <button type="submit" class="btn btn-primary px-5">Submit Feedback</button>
                                <a href="booking_details.php?id=<?php echo $booking_id; ?>" class="btn btn-secondary px-5 ms-2">View Booking Details</a>
                            </div>
                        </form>
                    <?php endif; ?>
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