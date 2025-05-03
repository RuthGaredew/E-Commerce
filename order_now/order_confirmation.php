<?php
// order_service/order-confirmation.php

// **Database Connection (Hardcoded Credentials - **DANGER!**)**
$servername = "localhost";  // **REPLACE WITH YOUR ACTUAL HOSTNAME**
$username = "root";       // **REPLACE WITH YOUR ACTUAL USERNAME**
$password = "";          // **REPLACE WITH YOUR ACTUAL PASSWORD**
$dbname = "order_db";     // **REPLACE WITH YOUR ACTUAL DATABASE NAME**

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// **Input Validation (Sanitize and Validate)**
$orderId = $_GET['orderId'] ?? null;
$orderId = filter_var($orderId, FILTER_VALIDATE_INT);

if ($orderId === null || $orderId === false) {
    die("Invalid or missing Order ID.");
}

// **Retrieve Order Details (Prepared Statement)**
$sql = "SELECT * FROM orders WHERE order_id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Failed to prepare statement: " . $conn->error);
}

$stmt->bind_param("i", $orderId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Order not found.");
}

$order = $result->fetch_assoc();

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f4;
        }

        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 0 auto;
        }

        h1 {
            color: #333;
        }

        p {
            color: #666;
            line-height: 1.6;
        }

        .success {
            color: green;
            font-weight: bold;
        }

        .failure {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Order Confirmation</h1>

        <?php
        // Get the order ID from the URL
        $orderId = isset($_GET['orderId']) ? $_GET['orderId'] : null;

        if ($orderId) {
            echo "<p>Thank you for your order! Your order ID is: <strong>" . htmlspecialchars($orderId) . "</strong></p>";

            // Simulate retrieving payment status and Telebirr transaction ID from the database
            // In a real application, you would query your database to get this information
            $payment_success = true; // Assume payment was successful
            $telebirr_transaction_id = "TB" . uniqid(); // Simulate a Telebirr transaction ID

            if ($payment_success) {
                echo "<p class='success'>Telebirr Payment Successful!</p>";
                echo "<p>Telebirr Transaction ID: <strong>" . htmlspecialchars($telebirr_transaction_id) . "</strong></p>";
            } else {
                echo "<p class='failure'>Telebirr Payment Failed.</p>";
            }
        } else {
            echo "<p class='failure'>Error: Order ID not found.</p>";
        }
        ?>

        <p>You will receive an email with further details about your order.</p>
    </div>
</body>
</html>