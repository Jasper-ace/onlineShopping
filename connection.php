<?php
    $user_name = "root";
    $password = "127956";  
    $database = "onlineshopping";
    $server = "localhost";
    $port = "3307";

    $conn = new mysqli($server, $user_name, $password, $database, $port);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
?>
