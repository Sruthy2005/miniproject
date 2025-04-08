<?php
// PostgreSQL connection details
$host = "dpg-cvqc5v15pdvs73aa5na0-a.oregon-postgres.render.com";
$dbname = "bellezza";
$username = "bellezza_user";
$password = "QRlbQVv0vk2GqPct2CQiCmL5eUVBoARt";
$port = "5432";

try {
    // Create a PDO connection
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $username, $password);
    
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set default fetch mode to associative array
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit;
}
?>
