<?php
session_start();
require_once '../connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

// Get staff name
$staff_query = "SELECT CONCAT(first_name, ' ', last_name) as name FROM user WHERE id = ? AND role = 'staff'";
$stmt = mysqli_prepare($conn, $staff_query);
if ($stmt === false) {
    die('Error preparing statement: ' . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $user_id);
if (!mysqli_stmt_execute($stmt)) {
    die('Error executing statement: ' . mysqli_stmt_error($stmt));
}
$staff_result = mysqli_stmt_get_result($stmt);
$staff_row = mysqli_fetch_assoc($staff_result);
$staff_name = htmlspecialchars($staff_row['name'] ?? 'Staff Member');

// Handle appointment status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user_role === 'staff') {
    $appointment_id = $_POST['appointment_id'];
    $new_status = $_POST['status'];
    
    $update_query = "UPDATE bookings SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    
    if ($stmt === false) {
        die('Error preparing statement: ' . $conn->error);
    }
    
    $stmt->bind_param("si", $new_status, $appointment_id);
    $stmt->execute();
}

// Fetch bookings based on user role
if ($user_role === 'staff') {
    $query = "SELECT b.*, u.first_name, u.last_name, u.email, u.phone, 
              u.address, u.city, u.state, u.zip_code,
              p.status as payment_status, p.amount as payment_amount,
              p.payment_id
              FROM bookings b 
              JOIN user u ON b.user_id = u.id 
              LEFT JOIN payments p ON b.id = p.booking_id
              WHERE b.staff_member = ?
              ORDER BY b.date DESC, b.time DESC";
} else {
    $query = "SELECT b.*,
              p.status as payment_status, p.amount as payment_amount,
              p.payment_id
              FROM bookings b 
              LEFT JOIN payments p ON b.id = p.booking_id
              WHERE b.user_id = ?
              ORDER BY b.date DESC, b.time DESC";
}

$stmt = $conn->prepare($query);

if ($stmt === false) {
    die('Error preparing statement: ' . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>My Appointments</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Belleza&display=swap" rel="stylesheet">
    
    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet" />

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
</head>

<body>
    <div class="container-xxl position-relative bg-white d-flex p-0">
        <!-- Spinner Start -->
        <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
        <!-- Spinner End -->

        <!-- Sidebar Start -->
        <div class="sidebar pe-4 pb-3">
            <nav class="navbar bg-light navbar-light">
                <a href="../index.php" class="navbar-brand mx-4 mb-3">
                    <h3 class="text-primary" style="font-family: 'Belleza', sans-serif;">Staff Portal</h3>
                </a>
                <div class="d-flex align-items-center ms-4 mb-4">
                    <div class="position-relative">
                        <i class="fas fa-user-tie fa-2x"></i>
                        <div class="bg-success rounded-circle border border-2 border-white position-absolute end-0 bottom-0 p-1"></div>
                    </div>
                    <div class="ms-3">
                        <h6 class="mb-0"><?php echo $staff_name; ?></h6>
                        <span>Beauty Professional</span>
                    </div>
                </div>
                <div class="navbar-nav w-100">
                    <a href="../index.php" class="nav-item nav-link"><i class="fa fa-home me-2"></i>Home</a>
                    <a href="staff_dashboard.php" class="nav-item nav-link"><i class="fa fa-tachometer-alt me-2"></i>Dashboard</a>
                    <a href="my_appointments.php" class="nav-item nav-link active"><i class="fa fa-calendar-check me-2"></i>My Appointments</a>
                    <a href="view_feedback.php" class="nav-item nav-link"><i class="fa fa-comments me-2"></i>View Feedback</a>
                    <a href="my_posts.php" class="nav-item nav-link"><i class="fa fa-image me-2"></i>My Posts</a>
                    <a href="../profile.php" class="nav-item nav-link"><i class="fa fa-user me-2"></i>My Profile</a>
                    <a href="../logout.php" class="nav-item nav-link"><i class="fa fa-sign-out-alt me-2"></i>Logout</a>
                </div>
            </nav>
        </div>
        <!-- Sidebar End -->

        <!-- Content Start -->
        <div class="content">
            <!-- Navbar Start -->
            <nav class="navbar navbar-expand bg-light navbar-light sticky-top px-4 py-0">
                <a href="#" class="sidebar-toggler flex-shrink-0">
                    <i class="fa fa-bars"></i>
                </a>
                <form class="d-none d-md-flex ms-4">
                    <input class="form-control border-0" type="search" placeholder="Search">
                </form>
                <div class="navbar-nav align-items-center ms-auto">
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-user-tie fa-2x me-lg-2"></i>
                            <span class="d-none d-lg-inline-flex"><?php echo $staff_name; ?></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end bg-light border-0 rounded-0 rounded-bottom m-0">
                            <a href="../profile.php" class="dropdown-item">My Profile</a>
                            <a href="../logout.php" class="dropdown-item">Log Out</a>
                        </div>
                    </div>
                </div>
            </nav>
            <!-- Navbar End -->

            <!-- Appointments Section Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="bg-light rounded p-4">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <h3 class="mb-0">My Appointments</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date & Time</th>
                                    <?php if ($user_role === 'staff'): ?>
                                        <th>Client Name</th>
                                    <?php else: ?>
                                        <th>Staff Name</th>
                                    <?php endif; ?>
                                    <th>Service</th>
                                    <th>Status</th>
                                    <th>Payment</th>
                                    <?php if ($user_role === 'staff'): ?>
                                        <th>Actions</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo date('M d, Y h:i A', strtotime($row['date'] . ' ' . $row['time'])); ?></td>
                                        <?php if ($user_role === 'staff'): ?>
                                            <td>
                                                <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?><br>
                                                <small class="text-muted">
                                                    Email: <?php echo htmlspecialchars($row['email']); ?><br>
                                                    Phone: <?php echo htmlspecialchars($row['phone']); ?><br>
                                                    Address: <?php echo htmlspecialchars($row['address'] ?? ''); ?><br>
                                                    <?php if (!empty($row['city']) || !empty($row['state']) || !empty($row['zip_code'])): ?>
                                                        <?php echo htmlspecialchars($row['city'] ?? ''); ?>, 
                                                        <?php echo htmlspecialchars($row['state'] ?? ''); ?> 
                                                        <?php echo htmlspecialchars($row['zip_code'] ?? ''); ?>
                                                    <?php endif; ?>
                                                </small>
                                            </td>
                                        <?php else: ?>
                                            <td><?php echo htmlspecialchars($row['staff_member']); ?></td>
                                        <?php endif; ?>
                                        <td><?php echo htmlspecialchars($row['specific_service']); ?><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($row['service_category']); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo getStatusColor($row['status']); ?>">
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo getPaymentStatusColor($row['payment_status']); ?>">
                                                <?php echo $row['payment_status'] ? ucfirst($row['payment_status']) : 'Not Paid'; ?>
                                            </span>
                                            <?php if ($row['payment_amount']): ?>
                                                <br>
                                                <small class="text-muted">
                                                    Amount: <?php echo number_format($row['payment_amount'], 2); ?>
                                                </small>
                                            <?php elseif ($row['status'] === 'completed'): ?>
                                                <br>
                                                <small class="text-danger">
                                                    <i class="fas fa-exclamation-circle"></i> Payment Required
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <?php if ($user_role === 'staff'): ?>
                                            <td>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="appointment_id" value="<?php echo $row['id']; ?>">
                                                    <select name="status" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                                                        <option value="pending" <?php echo $row['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                        <option value="confirmed" <?php echo $row['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                                        <option value="completed" <?php echo $row['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                        <option value="cancelled" <?php echo $row['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                    </select>
                                                </form>
                                                <?php if ($row['status'] === 'confirmed'): ?>
                                                    <button class="btn btn-sm btn-primary mt-2" onclick="scanQRCode(<?php echo $row['id']; ?>)">
                                                        <i class="fas fa-qrcode"></i> Scan QR
                                                    </button>
                                                <?php endif; ?>
                                                <?php if ($row['status'] === 'completed' && !$row['payment_status']): ?>
                                                    <br>
                                                    <small class="text-muted mt-1">
                                                        Service completed, awaiting payment
                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Appointments Section End -->
        </div>
        <!-- Content End -->

        <!-- Back to Top -->
        <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/tempusdominus/js/moment.min.js"></script>
    <script src="lib/tempusdominus/js/moment-timezone.min.js"></script>
    <script src="lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>

    <!-- Add QR Scanner Modal before closing body tag -->
    <div class="modal fade" id="qrScannerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Verify Appointment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="reader"></div>
                    <div id="qr-result" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add QR Scanner Script before closing body tag -->
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
    let html5QrcodeScanner;
    let currentAppointmentId;

    function scanQRCode(appointmentId) {
        currentAppointmentId = appointmentId;
        
        // Clear previous results
        $('#qr-result').html('');
        
        // Show the modal
        $('#qrScannerModal').modal('show');
        
        // Wait for modal to be fully shown before initializing scanner
        $('#qrScannerModal').on('shown.bs.modal', function() {
            startScanner();
        });
    }

    function startScanner() {
        if (html5QrcodeScanner) {
            html5QrcodeScanner.clear();
        }

        const html5QrCode = new Html5Qrcode("reader");
        const config = {
            fps: 10,
            qrbox: { width: 250, height: 250 },
            aspectRatio: 1.0
        };

        html5QrCode.start(
            { facingMode: "environment" },
            config,
            onScanSuccess,
            onScanFailure
        )
        .catch(function(err) {
            $('#qr-result').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> Error starting scanner: ${err}
                </div>
            `);
        });

        // Store the instance for cleanup
        html5QrcodeScanner = html5QrCode;
    }

    function onScanSuccess(decodedText, decodedResult) {
        // Stop scanning after successful scan
        if (html5QrcodeScanner) {
            html5QrcodeScanner.stop().then(() => {
                verifyQRCode(decodedText);
            });
        }
    }

    function onScanFailure(error) {
        // Handle scan failure, usually ignore it unless it's a specific error
        console.warn(`QR Code scanning failure: ${error}`);
    }

    function verifyQRCode(decodedText) {
        try {
            // Try to parse the QR data to verify it's valid JSON
            const qrData = JSON.parse(decodedText);
            
            // Show loading indicator
            $('#qr-result').html(`
                <div class="alert alert-info">
                    <i class="fas fa-spinner fa-spin"></i> Verifying appointment...
                </div>
            `);
            
            // Send verification request to server
            fetch('verify_qr.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    appointment_id: currentAppointmentId,
                    qr_data: decodedText
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    $('#qr-result').html(`
                        <div class="alert alert-success">
                            <h5>Appointment Verified!</h5>
                            <p>Client: ${data.client_name}<br>
                            Service: ${data.service}<br>
                            Date: ${data.date}<br>
                            Time: ${data.time}</p>
                        </div>
                    `);
                    
                    // Reload page after 2 seconds
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    $('#qr-result').html(`
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i> ${data.message}
                        </div>
                    `);
                    // Restart scanner after error
                    startScanner();
                }
            })
            .catch(error => {
                $('#qr-result').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> Error verifying appointment: ${error.message}
                    </div>
                `);
                // Restart scanner after error
                startScanner();
            });
            
        } catch (error) {
            $('#qr-result').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> Invalid QR code format: ${error.message}
                </div>
            `);
            // Restart scanner after error
            startScanner();
        }
    }

    // Clean up scanner when modal is closed
    $('#qrScannerModal').on('hidden.bs.modal', function () {
        if (html5QrcodeScanner) {
            html5QrcodeScanner.stop().then(() => {
                html5QrcodeScanner = null;
            }).catch(error => {
                console.error("Failed to clear scanner:", error);
            });
        }
        $('#qr-result').html('');
    });
    </script>
</body>
</html>

<?php
function getStatusColor($status) {
    switch ($status) {
        case 'pending':
            return 'warning';
        case 'confirmed':
            return 'primary';
        case 'completed':
            return 'success';
        case 'cancelled':
            return 'danger';
        default:
            return 'secondary';
    }
}

function getPaymentStatusColor($status) {
    switch ($status) {
        case 'completed':
        case 'success':
            return 'success';
        case 'pending':
            return 'warning';
        case 'failed':
            return 'danger';
        default:
            return 'secondary';
    }
}
?>
