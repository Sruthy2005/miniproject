<?php
// Basic PHP file to diagnose issues
session_start();
require_once '../connect.php';

// Very minimal code to identify issues
echo "<h1>Diagnostic Page</h1>";
echo "<p>PHP is working</p>";

// Check database connection
if ($conn) {
    echo "<p>Database connection successful</p>";
} else {
    echo "<p>Database connection failed</p>";
}

// Check if staff_posts table exists
$table_check = $conn->query("SHOW TABLES LIKE 'staff_posts'");
if ($table_check->num_rows > 0) {
    echo "<p>staff_posts table exists</p>";
} else {
    echo "<p>staff_posts table does not exist</p>";
    
    // Try to create the table
    $create_table_sql = "CREATE TABLE `staff_posts` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `staff_id` int(11) NOT NULL,
        `title` varchar(255) NOT NULL,
        `description` text NOT NULL,
        `image_path` varchar(255) DEFAULT NULL,
        `service_category` varchar(100) NOT NULL,
        `likes` int(11) DEFAULT 0,
        `created_at` datetime NOT NULL,
        PRIMARY KEY (`id`),
        KEY `staff_id` (`staff_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    if ($conn->query($create_table_sql)) {
        echo "<p>Successfully created staff_posts table</p>";
    } else {
        echo "<p>Failed to create staff_posts table: " . $conn->error . "</p>";
    }
}

// Basic information
echo "<p>User ID: " . ($_SESSION['user_id'] ?? 'Not set') . "</p>";
echo "<p>User Role: " . ($_SESSION['role'] ?? 'Not set') . "</p>";
?>

<p>If you see this message, basic PHP execution is working correctly.</p> 