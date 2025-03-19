<?php
session_start();
require_once "connect.php";

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);

// Fetch staff members from the database
$staff_members = [];
$query = "SELECT id, first_name, last_name FROM user WHERE role = 'staff'"; // Added id to selection
$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $staff_members[] = [
            'id' => $row['id'],
            'name' => $row['first_name'] . ' ' . $row['last_name']
        ];
    }
}

// Add this PHP code near the top of the file after the existing database queries
$services_by_category = [];
$services_query = "SELECT id, name, price, category FROM service ORDER BY name";
$services_result = $conn->query($services_query);
if ($services_result) {
    while ($service = $services_result->fetch_assoc()) {
        $services_by_category[$service['category']][] = [
            'id' => $service['id'],
            'name' => $service['name'],
            'price' => $service['price']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Bellezza - Booking</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    
    <!-- Include your existing CSS files -->
    <link href="https://fonts.googleapis.com/css?family=Montserrat:300,400,600,700" rel="stylesheet">
    <link rel="stylesheet" href="css/open-iconic-bootstrap.min.css">
    <link rel="stylesheet" href="css/animate.css">
    <link rel="stylesheet" href="css/owl.carousel.min.css">
    <link rel="stylesheet" href="css/owl.theme.default.min.css">
    <link rel="stylesheet" href="css/magnific-popup.css">
    <link rel="stylesheet" href="css/aos.css">
    <link rel="stylesheet" href="css/ionicons.min.css">
    <link rel="stylesheet" href="css/bootstrap-datepicker.css">
    <link rel="stylesheet" href="css/jquery.timepicker.css">
    <link rel="stylesheet" href="css/flaticon.css">
    <link rel="stylesheet" href="css/icomoon.css">
    <link rel="stylesheet" href="css/style.css">

    <style>
        .booking-form-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            margin: 40px auto;
        }

        .service-card {
            border: 1px solid #ddd;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .service-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }

        .service-card.selected {
            border-color: #71caf3;
            background-color: #f8fdff;
        }

        .price-tag {
            color: #71caf3;
            font-size: 1.2em;
            font-weight: bold;
        }

        .booking-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .form-group label {
            font-weight: 500;
        }

        .btn-book-now {
            background: #71caf3;
            color: white;
            padding: 12px 30px;
            border-radius: 30px;
            border: none;
            transition: all 0.3s ease;
        }

        .btn-book-now:hover {
            background: #5bb1d9;
            transform: translateY(-2px);
        }

        .login-prompt {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<!-- Include your existing navigation -->
<?php include 'navbar.php'; ?>


<!-- Booking Section -->
<section class="ftco-section" style="padding-top: 2em;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <?php if (!$is_logged_in): ?>
                    <div class="login-prompt">
                        <h4>Please login to book an appointment</h4>
                        <p>Create an account or login to book your beauty services</p>
                        <a href="login.php" class="btn btn-primary">Login Now</a>
                    </div>
                <?php else: ?>
                    <div class="booking-form-container">
                        <div class="booking-header">
                            <h2>Book Your Appointment</h2>
                            <p>Select your preferred service and time</p>
                        </div>

                        <form action="process_booking.php" method="POST">
                            <!-- Service Selection -->
                            <div class="form-group">
                                <label>Select Service Category</label>
                                <select class="form-control" name="service_category" id="serviceCategory" required>
                                    <option value="">Choose a category</option>
                                    <option value="hair">Hair Services</option>
                                    <option value="skin">Skin Care</option>
                                    <option value="makeup">Makeup</option>
                                </select>
                            </div>

                            <!-- Specific Service Selection -->
                            <div class="form-group">
                                <label>Select Specific Service</label>
                                <select class="form-control" name="specific_service" id="specificService" required>
                                    <option value="">First select a category</option>
                                </select>
                            </div>

                            <!-- Staff Member Selection -->
                            <div class="form-group">
                                <label>Select Staff Member</label>
                                <select class="form-control" name="staff_member" id="staffMember" required>
                                    <option value="">First select a service category</option>
                                    <?php
                                    // Fetch active staff members based on selected service category
                                    if (isset($_POST['service_category'])) {
                                        $service_category = $_POST['service_category'];
                                        require_once "connect.php";
                                        $sql = "SELECT * FROM user WHERE role='staff' AND status='active' AND specialization='$service_category'";
                                        $result = mysqli_query($conn, $sql);

                                        while ($staff = mysqli_fetch_assoc($result)) {
                                            echo '<option value="' . htmlspecialchars($staff['id']) . '">' . htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']) . '</option>';
                                        }
                                        mysqli_close($conn);
                                    }
                                    ?>
                                </select>
                            </div>

                            <!-- Date and Time Selection -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Preferred Date</label>
                                        <input type="text" class="form-control" id="appointmentDate" name="date" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Preferred Time</label>
                                        <input type="time" class="form-control" name="time" min="09:00" max="17:00" step="3600" required>
                                    </div>
                                </div>
                                </div>
                            </div>

                            <!-- Additional Notes -->
                            <div class="form-group">
                                <label>Special Requirements or Notes</label>
                                <textarea class="form-control" name="notes" rows="3"></textarea>
                            </div>

                            <!-- Submit Button -->
                            <div class="form-group text-center">
                                <button type="submit" class="btn btn-book-now">Book Appointment</button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Include your existing footer -->
<?php include 'footer.php'; ?>

<!-- loader -->
<div id="ftco-loader" class="show fullscreen">
    <svg class="circular" width="48px" height="48px">
        <circle class="path-bg" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke="#eeeeee"/>
        <circle class="path" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke-miterlimit="10" stroke="#F96D00"/>
    </svg>
</div>

<!-- Include your existing JavaScript files -->
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

<script>
$(document).ready(function() {
    // Initialize datepicker
    $('#appointmentDate').datepicker({
        startDate: new Date(),
        format: 'yyyy-mm-dd',
        autoclose: true
    });

    // Get services data from PHP
    const servicesData = <?php echo json_encode($services_by_category); ?>;

    // Service category change handler
    $('#serviceCategory').change(function() {
        updateServiceOptions();
    });

    function updateServiceOptions() {
        const category = $('#serviceCategory').val();
        const specificService = $('#specificService');
        const staffMember = $('#staffMember');
        specificService.empty();
        staffMember.empty();
        
        // Add default option
        specificService.append('<option value="">Select a service</option>');
        
        // Add services from database
        if (category && servicesData[category]) {
            servicesData[category].forEach(function(service) {
                specificService.append(`
                    <option value="${service.id}">
                        ${service.name} - ₱${service.price}
                    </option>
                `);
            });
        }

        // Populate staff members based on the fetched data
        const staffOptions = <?php echo json_encode($staff_members); ?>;
        staffMember.append('<option value="">Select a staff member</option>');
        staffOptions.forEach(function(staff) {
            staffMember.append(`<option value="${staff.id}">${staff.name}</option>`);
        });
    }

    // Pre-select service category and specific service if passed in URL
    const urlParams = new URLSearchParams(window.location.search);
    const serviceParam = urlParams.get('service');
    const serviceIdParam = urlParams.get('service_id');
    
    if (serviceParam) {
        $('#serviceCategory').val(serviceParam);
        updateServiceOptions(); // Update the specific services dropdown
        
        // If a specific service ID was passed, select it after a short delay
        // to ensure the options are populated
        if (serviceIdParam) {
            setTimeout(() => {
                $('#specificService').val(serviceIdParam);
            }, 100);
        }
    }
});
</script>

</body>
</html> 