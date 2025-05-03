<?php
// order_service/api/create_order.php

header('Content-Type: application/json');

// **Authentication (Crucially Important - Replace Placeholder!)**
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';

if (empty($authHeader) || !preg_match('/Bearer\s+(.*)/i', $authHeader, $matches)) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized: Missing or invalid Authorization header']);
    exit;
}
$authToken = $matches[1];

// **Token Validation (Replace with your actual logic)**
$validToken = validateToken($authToken);
if (!$validToken) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized: Invalid token']);
    exit;
}

function validateToken($token) {
    // **VERY IMPORTANT:** This is a placeholder!
    // Replace with your actual token validation logic (JWT, OAuth, etc.)
    return !empty($token);
}

// **Database Connection (Hardcoded Credentials - **DANGER!**)**
$servername = "localhost";  // **REPLACE WITH YOUR ACTUAL HOSTNAME**
$username = "root";       // **REPLACE WITH YOUR ACTUAL USERNAME**
$password = "";          // **REPLACE WITH YOUR ACTUAL PASSWORD**
$dbname = "order_db";     // **REPLACE WITH YOUR ACTUAL DATABASE NAME**

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// **Input Validation and Sanitization**
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!isset($data['productId']) || !isset($data['quantity']) || !isset($data['userId'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameters (productId, quantity, userId)']);
    exit;
}

$productId = filter_var($data['productId'], FILTER_VALIDATE_INT);
$quantity = filter_var($data['quantity'], FILTER_VALIDATE_INT);
$userId = filter_var($data['userId'], FILTER_VALIDATE_INT);

if ($productId === false || $quantity === false || $userId === false) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid data types for productId, quantity, or userId']);
    exit;
}

if ($quantity <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Quantity must be greater than zero.']);
    exit;
}

// **Create the Order (Prepared Statement)**
$sql = "INSERT INTO orders (user_id, product_id, quantity, order_date) VALUES (?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to prepare statement: ' . $conn->error]);
    exit;
}

$stmt->bind_param("iii", $userId, $productId, $quantity);

if ($stmt->execute()) {
    $orderId = $conn->insert_id;
    http_response_code(201); // Created
    echo json_encode(['orderId' => $orderId, 'message' => 'Order created successfully']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to create order: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>