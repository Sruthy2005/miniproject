<?php
session_start();
require_once "connect.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Clear any old error messages
unset($_SESSION['error']);

// Check if booking ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Invalid booking ID";
    header("Location: my_bookings.php");
    exit();
}

$booking_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Fetch booking details to verify it belongs to the current user
$query = "SELECT 
            b.*, 
            u.first_name,
            u.last_name,
            u.email,
            u.phone,
            s.name as service_name,
            s.price as service_price
          FROM bookings b
          JOIN user u ON b.user_id = u.id
          JOIN service s ON b.specific_service = s.id
          WHERE b.id = ? AND b.user_id = ? AND b.status = 'pending'";

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
    $_SESSION['error'] = "Booking not found or payment not required";
    header("Location: my_bookings.php");
    exit();
}

$booking = $result->fetch_assoc();
$amount = $booking['service_price'] * 100; // Convert to smallest currency unit (centavos for PHP)
$service_name = $booking['service_name'];
$customer_name = $booking['first_name'] . ' ' . $booking['last_name'];
$customer_email = $booking['email'];
$customer_phone = $booking['phone'];

// Razorpay API key
$razorpay_key_id = "rzp_test_Ubp5458VM3YLw0";
$currency = "PHP"; // Philippines Peso
$receipt_id = 'booking_' . $booking_id;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Bellezza - Payment</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    
    <!-- Include your existing CSS files -->
    <link href="https://fonts.googleapis.com/css?family=Montserrat:300,400,600,700" rel="stylesheet">
    <link rel="stylesheet" href="css/open-iconic-bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">

    <style>
        .payment-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .payment-details {
            margin-bottom: 30px;
        }
        
        .payment-amount {
            font-size: 2em;
            color: #28a745;
            margin: 15px 0;
        }
        
        .razorpay-button {
            background: #528FF0;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .razorpay-button:hover {
            background: #3A7BE0;
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<section class="ftco-section">
    <div class="container">
        <div class="row justify-content-center mb-5">
            <div class="col-md-7 text-center">
                <h2 class="mb-4">Payment for Booking</h2>
                <p>Complete your payment to confirm your appointment</p>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="payment-container">
                    <div class="payment-details">
                        <h4><?php echo htmlspecialchars($service_name); ?></h4>
                        <p>
                            <strong>Date & Time:</strong> 
                            <?php 
                            $date = new DateTime($booking['date']);
                            $time = new DateTime($booking['time']);
                            echo $date->format('F j, Y') . ' at ' . $time->format('g:i A');
                            ?>
                        </p>
                        <div class="payment-amount">
                            ₱<?php echo number_format($booking['service_price'], 2); ?>
                        </div>
                    </div>

                    <div class="text-center">
                        <button id="pay-button" class="razorpay-button">Proceed to Payment</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Add this success modal HTML before the footer -->
<div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel" aria-hidden="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <i class="fas fa-check-circle text-success" style="font-size: 48px;"></i>
                <h3 class="mt-3">Payment Successful!</h3>
                <p class="mb-4">Your appointment has been confirmed. You will be redirected to view your booking details.</p>
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<!-- Add Font Awesome for the checkmark icon -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<!-- Razorpay JavaScript -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
document.getElementById('pay-button').onclick = function(e) {
    var options = {
        "key": "<?php echo $razorpay_key_id; ?>",
        "amount": "<?php echo $amount; ?>", 
        "currency": "<?php echo $currency; ?>",
        "name": "Bellezza",
        "description": "<?php echo htmlspecialchars($service_name); ?>",
        "image": "https://your-website.com/logo.png", // Replace with your logo URL
        "prefill": {
            "name": "<?php echo htmlspecialchars($customer_name); ?>",
            "email": "<?php echo htmlspecialchars($customer_email); ?>",
            "contact": "<?php echo htmlspecialchars($customer_phone); ?>"
        },
        "notes": {
            "booking_id": "<?php echo $booking_id; ?>",
            "service": "<?php echo htmlspecialchars($service_name); ?>"
        },
        "theme": {
            "color": "#71caf3"
        },
        "handler": function (response) {
            // Show success modal before redirecting
            $('#successModal').modal('show');
            
            // Redirect after 2 seconds
            setTimeout(function() {
                window.location.href = "verify_payment.php?booking_id=<?php echo $booking_id; ?>&payment_id=" + response.razorpay_payment_id;
            }, 2000);
        },
        "modal": {
            "ondismiss": function() {
                console.log("Payment canceled");
            }
        }
    };
    
    var rzp = new Razorpay(options);
    rzp.open();
    e.preventDefault();
}
</script>

<!-- Other scripts -->
<script src="js/jquery.min.js"></script>
<script src="js/jquery-migrate-3.0.1.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/main.js"></script>

</body>
</html>