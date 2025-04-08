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

    <!-- Google Analytics tracking code -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=YOUR-GA-ID"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'YOUR-GA-ID');
    </script>

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

        .staff-availability-container {
            margin-top: 25px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
        }
        
        .staff-availability-container table {
            margin-bottom: 0;
        }
        
        .staff-availability-container th {
            background-color: #f8f9fa;
        }
        
        .staff-availability-container .table-primary {
            background-color: #e0f7fa !important;
        }
        
        .select-staff {
            border-radius: 20px;
            padding: 3px 10px;
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
                                    <option value="">Select date and time first</option>
                                </select>
                            </div>

                            <!-- Add the staff availability table container -->
                            <div class="staff-availability-container mt-4" style="display: none;">
                                <h4 class="mb-3">Available Staff Members</h4>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>Staff Name</th>
                                                <th>Date</th>
                                                <th>Time</th>
                                                <th>Select</th>
                                            </tr>
                                        </thead>
                                        <tbody id="availableStaffTable">
                                            <!-- Staff availability data will be populated here -->
                                        </tbody>
                                    </table>
                                </div>
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

                            <!-- Address Fields -->
                            <div class="form-group">
                                <label>Address Information</label>
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <input type="text" class="form-control" name="street_address" placeholder="Street address" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <input type="text" class="form-control" name="city" placeholder="City" required>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" class="form-control" name="state" placeholder="State" required>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" class="form-control" name="postcode" placeholder="Postcode" required>
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
        checkStaffAvailability();
    });

    // Date and time change handlers
    $('#appointmentDate, [name="time"]').change(function() {
        checkStaffAvailability();
    });

    function updateServiceOptions() {
        const category = $('#serviceCategory').val();
        const specificService = $('#specificService');
        specificService.empty();
        
        // Add default option
        specificService.append('<option value="">Select a service</option>');
        
        // Add services from database
        if (category && servicesData[category]) {
            servicesData[category].forEach(function(service) {
                specificService.append(`
                    <option value="${service.id}">
                        ${service.name} - ${service.price}
                    </option>
                `);
            });
        }

        // Clear staff dropdown
        $('#staffMember').empty().append('<option value="">Select date and time first</option>');
    }

    function checkStaffAvailability() {
        const date = $('#appointmentDate').val();
        const time = $('[name="time"]').val();
        const category = $('#serviceCategory').val();
        const staffMember = $('#staffMember');
        const staffTableContainer = $('.staff-availability-container');
        const staffTableBody = $('#availableStaffTable');

        // Clear staff dropdown and table
        staffMember.empty().append('<option value="">Loading available staff...</option>');
        staffTableBody.empty();
        staffTableContainer.hide();

        // Only proceed if we have all required values
        if (!date || !time || !category) {
            staffMember.empty().append('<option value="">Please select date and time first</option>');
            return;
        }

        console.log('Checking staff availability with:', {date, time, category});

        // Make AJAX call to check staff availability
        $.ajax({
            url: 'check_staff_availability.php',
            method: 'POST',
            data: {
                date: date,
                time: time,
                service_category: category
            },
            dataType: 'json',
            success: function(response) {
                console.log('Response received:', response);
                staffMember.empty();
                
                if (response.success && response.available_staff && response.available_staff.length > 0) {
                    // Populate dropdown
                    staffMember.append('<option value="">Select a staff member</option>');
                    
                    // Populate table
                    response.available_staff.forEach(function(staff) {
                        // Add to dropdown
                        staffMember.append(`<option value="${staff.id}">${staff.name}</option>`);
                        
                        // Add to table
                        const timeFormatted = formatTime(time);
                        const dateFormatted = formatDate(date);
                        
                        staffTableBody.append(`
                            <tr>
                                <td>${staff.name}</td>
                                <td>${dateFormatted}</td>
                                <td>${timeFormatted}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary select-staff" 
                                            data-staff-id="${staff.id}" data-staff-name="${staff.name}">
                                        Select
                                    </button>
                                </td>
                            </tr>
                        `);
                    });
                    
                    // Show the table
                    staffTableContainer.show();
                    
                    // Handle select staff button clicks
                    $('.select-staff').click(function() {
                        const staffId = $(this).data('staff-id');
                        const staffName = $(this).data('staff-name');
                        
                        // Select in dropdown
                        staffMember.val(staffId);
                        
                        // Highlight selected row
                        $('#availableStaffTable tr').removeClass('table-primary');
                        $(this).closest('tr').addClass('table-primary');
                    });
                    
                    console.log(`Found ${response.available_staff.length} available staff members`);
                } else {
                    staffMember.append('<option value="" disabled>No staff available with selected specialization</option>');
                    // Don't show the table when no staff are available
                    staffTableContainer.hide();
                    console.log('No staff available', response);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', {
                    status: status,
                    error: error,
                    response: xhr.responseText
                });
                
                try {
                    // Try to parse the error response
                    const errorResponse = JSON.parse(xhr.responseText);
                    console.log('Parsed error response:', errorResponse);
                } catch (e) {
                    console.log('Raw response text:', xhr.responseText);
                }
                
                staffMember.empty();
                staffMember.append('<option value="">Connection error - please try again</option>');
                staffTableContainer.hide();
            }
        });
    }

    // Helper functions to format date and time
    function formatDate(dateStr) {
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const date = new Date(dateStr);
        return date.toLocaleDateString('en-US', options);
    }

    function formatTime(timeStr) {
        const timeParts = timeStr.split(':');
        let hours = parseInt(timeParts[0]);
        const minutes = timeParts[1];
        const ampm = hours >= 12 ? 'PM' : 'AM';
        
        hours = hours % 12;
        hours = hours ? hours : 12; // Handle midnight (0 hours)
        
        return `${hours}:${minutes} ${ampm}`;
    }

    // Pre-select service category and specific service if passed in URL
    const urlParams = new URLSearchParams(window.location.search);
    const serviceParam = urlParams.get('service');
    const serviceIdParam = urlParams.get('service_id');
    
    if (serviceParam) {
        $('#serviceCategory').val(serviceParam);
        updateServiceOptions();
        
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
