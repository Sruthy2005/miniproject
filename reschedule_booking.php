<?php
session_start();
require_once "connect.php";

// Check if user is logged in, redirect if not
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch the booking details
if (isset($_GET['id'])) {
    $booking_id = $_GET['id'];
    $query = "SELECT * FROM bookings WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        die("Booking not found or you do not have permission to access it.");
    }

    $booking = $result->fetch_assoc();
} else {
    die("No booking ID provided.");
}

// Handle the rescheduling form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_date = $_POST['date'];
    $new_time = $_POST['time'];

    // Update the booking with the new date and time
    $update_query = "UPDATE bookings SET date = ?, time = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("ssi", $new_date, $new_time, $booking_id);
    if ($update_stmt->execute()) {
        header("Location: my_bookings.php?message=Booking rescheduled successfully.");
        exit();
    } else {
        die("Error updating booking: " . $conn->error);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Reschedule Booking</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container">
    <h2>Reschedule Booking</h2>
    <form method="POST" action="">
        <div class="form-group">
            <label for="date">New Date:</label>
            <input type="date" id="date" name="date" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="time">New Time:</label>
            <input type="time" id="time" name="time" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Reschedule</button>
    </form>
</div>

<?php include 'footer.php'; ?>

</body>
</html> 