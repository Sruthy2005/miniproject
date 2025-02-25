<?php
require_once "connect.php";

if(isset($_POST['email'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    $sql = "SELECT * FROM user WHERE email = '$email'";
    $result = $conn->query($sql);
    
    if($result->num_rows > 0) {
        echo "exists";
    } else {
        echo "not_exists";
    }
}
?>
