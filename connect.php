<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bellezza";

// Remove the single quotes around the variables
$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    echo 'Connection failed: ' . mysqli_connect_error();
} 
?>
