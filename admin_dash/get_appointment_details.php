<?php
session_start();
require_once "../connect.php";

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo "Unauthorized access";
    exit();
}

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "Invalid appointment ID";
    exit();
}

$appointment_id = $_GET['id'];

// Fetch appointment details
$sql = "SELECT b.*, 
        u.first_name, u.last_name, u.email, u.phone,
        s.name as service_name, s.price as service_price, s.description as service_description,
        staff.first_name as staff_first_name, staff.last_name as staff_last_name
        FROM bookings b
        JOIN user u ON b.user_id = u.id
        JOIN service s ON b.specific_service = s.id
        LEFT JOIN user staff ON b.staff_member = staff.id
        WHERE b.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Appointment not found";
    exit();
}

$appointment = $result->fetch_assoc();

// Get feedback if it exists
$feedback_sql = "SELECT f.* FROM feedback f WHERE f.booking_id = ?";
$feedback_stmt = $conn->prepare($feedback_sql);
$feedback_stmt->bind_param("i", $appointment_id);
$feedback_stmt->execute();
$feedback_result = $feedback_stmt->get_result();

$has_feedback = $feedback_result->num_rows > 0;
$feedback = $has_feedback ? $feedback_result->fetch_assoc() : null;

// Format the appointment date and time
$appointment_date = new DateTime($appointment['date']);
$appointment_time = new DateTime($appointment['time']);

// Format the payment date if it exists
$payment_date = !empty($appointment['payment_date']) ? new DateTime($appointment['payment_date']) : null;

// Get the status class for styling
$status_class = 'status-' . strtolower($appointment['status']);
?>

<div class="appointment-details">
    <div class="row mb-4">
        <div class="col-md-6">
            <h4><?php echo htmlspecialchars($appointment['service_name']); ?></h4>
            <span class="status-badge <?php echo $status_class; ?>">
                <?php echo ucfirst($appointment['status']); ?>
            </span>
        </div>
        <div class="col-md-6 text-md-end">
            <h5>₱<?php echo number_format($appointment['service_price'], 2); ?></h5>
            <?php if (!empty($appointment['payment_id'])): ?>
                <small class="text-success">
                    <i class="fas fa-check-circle"></i> Paid
                </small>
            <?php else: ?>
                <small class="text-muted">
                    <i class="fas fa-clock"></i> Payment Pending
                </small>
            <?php endif; ?>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="detail-section">
                <h5>Customer Information</h5>
                <p>
                    <i class="fas fa-user text-muted"></i> 
                    <?php echo htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?>
                </p>
                <p>
                    <i class="fas fa-envelope text-muted"></i> 
                    <?php echo htmlspecialchars($appointment['email']); ?>
                </p>
                <p>
                    <i class="fas fa-phone text-muted"></i> 
                    <?php echo htmlspecialchars($appointment['phone']); ?>
                </p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="detail-section">
                <h5>Appointment Details</h5>
                <p>
                    <i class="fas fa-calendar text-muted"></i>
                    <?php echo $appointment_date->format('F j, Y'); ?>
                </p>
                <p>
                    <i class="fas fa-clock text-muted"></i>
                    <?php echo $appointment_time->format('g:i A'); ?>
                </p>
                <p>
                    <i class="fas fa-user-tie text-muted"></i>
                    <?php 
                    if (!empty($appointment['staff_first_name']) && !empty($appointment['staff_last_name'])) {
                        echo htmlspecialchars($appointment['staff_first_name'] . ' ' . $appointment['staff_last_name']);
                    } else {
                        echo 'Not assigned';
                    }
                    ?>
                </p>
            </div>
        </div>
    </div>

    <?php if (!empty($appointment['notes'])): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="detail-section">
                <h5>Customer Notes</h5>
                <p class="p-3 bg-light rounded">
                    <?php echo nl2br(htmlspecialchars($appointment['notes'])); ?>
                </p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($has_feedback): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="detail-section">
                <h5>Customer Feedback</h5>
                <div class="feedback-content">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rating-stars me-3">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <?php if ($i <= $feedback['rating']): ?>
                                    <i class="fas fa-star text-warning"></i>
                                <?php else: ?>
                                    <i class="far fa-star text-warning"></i>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                        <div class="rating-text">
                            <strong><?php echo $feedback['rating']; ?>/5</strong>
                        </div>
                    </div>
                    
                    <?php if (!empty($feedback['comments'])): ?>
                        <div class="feedback-comments p-3 bg-light rounded">
                            <?php echo nl2br(htmlspecialchars($feedback['comments'])); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="feedback-date mt-2">
                        <small class="text-muted">
                            Submitted on <?php echo date('F j, Y', strtotime($feedback['created_at'])); ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($appointment['payment_id'])): ?>
    <div class="row">
        <div class="col-12">
            <div class="detail-section">
                <h5>Payment Information</h5>
                <p>
                    <strong>Payment ID:</strong> 
                    <?php echo htmlspecialchars($appointment['payment_id']); ?>
                </p>
                <?php if ($payment_date): ?>
                <p>
                    <strong>Payment Date:</strong> 
                    <?php echo $payment_date->format('F j, Y g:i A'); ?>
                </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
    .detail-section {
        margin-bottom: 15px;
    }
    .detail-section h5 {
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 1px solid #eee;
    }
    .detail-section p {
        margin-bottom: 8px;
    }
    .detail-section i {
        width: 20px;
        margin-right: 5px;
    }
    .status-badge {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 15px;
        font-size: 12px;
        font-weight: 500;
    }
    .status-pending { background-color: #fff3cd; color: #856404; }
    .status-confirmed { background-color: #d4edda; color: #155724; }
    .status-cancelled { background-color: #f8d7da; color: #721c24; }
    .status-completed { background-color: #cce5ff; color: #004085; }
    .rating-stars {
        color: #FFD700;
    }
</style> 