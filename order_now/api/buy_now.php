<?php
// Enable CORS for cross-origin requests (if needed)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database connection details (replace with your actual credentials)
$host = "localhost";
$db_name = "order_db";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=" . $host . ";dbname=" . $db_name, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    error_log("Connection failed: " . $e->getMessage());  // Log the error
    echo json_encode(array("success" => false, "error" => "Database connection failed.", "payment_success" => false));
    exit; // Stop further execution
}

// Get data from the request
$data = json_decode(file_get_contents("php://input"));

// Validate data
if (!isset($data->product_id) || !isset($data->quantity)) {
    echo json_encode(array("success" => false, "error" => "Missing product_id or quantity.", "payment_success" => false));
    exit;
}

$product_id = $data->product_id;
$quantity = $data->quantity;

// Basic input validation (you should add more robust validation)
if (!is_numeric($product_id) || !is_numeric($quantity)) {
    echo json_encode(array("success" => false, "error" => "Invalid product_id or quantity.", "payment_success" => false));
    exit;
}

// Create order in the database
try {
    $query = "INSERT INTO orders (product_id, quantity, order_date) VALUES (:product_id, :quantity, NOW())";  // Replace 'orders' and column names with your actual table
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":product_id", $product_id);
    $stmt->bindParam(":quantity", $quantity);
    $stmt->execute();

    $order_id = $conn->lastInsertId();  // Get the ID of the newly inserted order

    // Simulate Telebirr payment processing
    $telebirr_transaction_id = uniqid(); // Generate a unique transaction ID

    // In a real integration, you would:
    // 1. Redirect the user to Telebirr's payment page (if they have one)
    // 2. Store the $telebirr_transaction_id in your database

    // Simulate Telebirr callback (payment verification)
    $payment_success = true; // Assume payment is successful for this example

    if ($payment_success) {
        // Update order status (if applicable)
        // $query = "UPDATE orders SET order_status = 'paid' WHERE order_id = :order_id";
        // $stmt = $conn->prepare($query);
        // $stmt->bindParam(":order_id", $order_id);
        // $stmt->execute();

        echo json_encode(array("success" => true, "orderId" => $order_id, "payment_success" => true, "telebirr_transaction_id" => $telebirr_transaction_id));
    } else {
        echo json_encode(array("success" => false, "error" => "Telebirr payment failed.", "orderId" => $order_id, "payment_success" => false));
    }


} catch(PDOException $e) {
    error_log("Error creating order: " . $e->getMessage()); // Log the error
    echo json_encode(array("success" => false, "error" => "Error creating order in the database.", "payment_success" => false));
    exit;
}
?>