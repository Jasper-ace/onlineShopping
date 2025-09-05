<?php
    $user_name = "root";
    $password = "1279";  
    $database = "onlineshopping";
    $server = "localhost";
    $port = "3306";

    $conn = new mysqli($server, $user_name, $password, $database, $port);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
?>
