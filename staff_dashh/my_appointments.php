<?php
session_start();
require_once "../connect.php";

// Check if user is logged in and is staff
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header('Location: ../login.php');
    exit();
}

$staff_id = $_SESSION['user_id'];

// Fetch appointments for the logged-in staff member
$query = "SELECT 
    b.id,
    b.date,
    b.time,
    b.status,
    b.notes,
    u.first_name as client_first_name,
    u.last_name as client_last_name,
    s.name as service_name,
    s.price
    FROM bookings b
    JOIN user u ON b.client_id = u.id
    JOIN service s ON b.service_id = s.id
    WHERE b.staff_member = ?
    ORDER BY b.date ASC, b.time ASC";

if ($stmt = $conn->prepare($query)) {
    $stmt->bind_param("i", $staff_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $appointments = $result->fetch_all(MYSQLI_ASSOC);
} else {
    // Handle query preparation error
    die("Error preparing query: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Appointments</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <style>
        .appointment-card {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
        }
        .appointment-pending { background-color: #fff3cd; }
        .appointment-confirmed { background-color: #d4edda; }
        .appointment-completed { background-color: #e2e3e5; }
        .appointment-cancelled { background-color: #f8d7da; }
    </style>
</head>
<body>
    <?php include 'staff_navbar.php'; ?>

    <div class="container mt-4">
        <h2>My Appointments</h2>
        
        <?php if (empty($appointments)): ?>
            <div class="alert alert-info">No appointments scheduled.</div>
        <?php else: ?>
            <?php foreach ($appointments as $appointment): ?>
                <div class="appointment-card appointment-<?php echo strtolower($appointment['status']); ?>">
                    <div class="row">
                        <div class="col-md-4">
                            <h5>Client: <?php echo htmlspecialchars($appointment['client_first_name'] . ' ' . $appointment['client_last_name']); ?></h5>
                            <p>Service: <?php echo htmlspecialchars($appointment['service_name']); ?></p>
                        </div>
                        <div class="col-md-4">
                            <p>Date: <?php echo htmlspecialchars($appointment['date']); ?></p>
                            <p>Time: <?php echo htmlspecialchars($appointment['time']); ?></p>
                        </div>
                        <div class="col-md-4">
                            <p>Status: <?php echo htmlspecialchars($appointment['status']); ?></p>
                            <p>Price: $<?php echo htmlspecialchars($appointment['price']); ?></p>
                            <?php if ($appointment['notes']): ?>
                                <p>Notes: <?php echo htmlspecialchars($appointment['notes']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="mt-2">
                        <?php if ($appointment['status'] === 'pending'): ?>
                            <button class="btn btn-success btn-sm" onclick="updateStatus(<?php echo $appointment['id']; ?>, 'confirmed')">Confirm</button>
                            <button class="btn btn-danger btn-sm" onclick="updateStatus(<?php echo $appointment['id']; ?>, 'cancelled')">Cancel</button>
                        <?php elseif ($appointment['status'] === 'confirmed'): ?>
                            <button class="btn btn-primary btn-sm" onclick="updateStatus(<?php echo $appointment['id']; ?>, 'completed')">Mark Complete</button>
                            <button class="btn btn-danger btn-sm" onclick="updateStatus(<?php echo $appointment['id']; ?>, 'cancelled')">Cancel</button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script src="../js/jquery.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script>
        function updateStatus(appointmentId, status) {
            if (confirm('Are you sure you want to update this appointment status?')) {
                $.post('update_appointment_status.php', {
                    appointment_id: appointmentId,
                    status: status
                }, function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error updating appointment status');
                    }
                });
            }
        }
    </script>
</body>
</html>