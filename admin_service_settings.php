<?php

// Add buffer time logic to process_booking.php
$service_query = "SELECT duration, buffer_time FROM services WHERE id = ?";
$stmt_service = $conn->prepare($service_query);
$stmt_service->bind_param("i", $service_id);
$stmt_service->execute();
$result_service = $stmt_service->get_result();
$service_data = $result_service->fetch_assoc();

$total_duration = $service_data['duration'] + $service_data['buffer_time'];

// Check if the booking with buffer time conflicts with other appointments
$availability_query = "SELECT id FROM bookings 
                      WHERE staff_member = ? 
                      AND date = ? 
                      AND (
                          (TIME_TO_SEC(time) BETWEEN TIME_TO_SEC(?) - ? AND TIME_TO_SEC(?) + ?)
                          OR 
                          (TIME_TO_SEC(time) + (SELECT duration * 60 FROM services WHERE id = bookings.service_id) 
                           BETWEEN TIME_TO_SEC(?) - ? AND TIME_TO_SEC(?) + ?)
                      )
                      AND status != 'cancelled'"; 