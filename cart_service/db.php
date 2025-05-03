<?php
// cart/service/db.php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cart_db"; // Changed to cart_db

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //echo "Connected successfully"; // Remove this line in production
    }
catch(PDOException $e)
    {
    die("Connection failed: " . $e->getMessage()); // Use die() here to stop execution
    }
?>