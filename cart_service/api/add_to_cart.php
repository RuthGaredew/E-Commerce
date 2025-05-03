<?php
// Enable error reporting (REMOVE IN PRODUCTION)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include common config (auth + db)
require 'common.php';
error_log("common.php loaded successfully");

header('Content-Type: application/json');

$user_id = authenticate(); // Get user ID from JWT

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Parse and sanitize input
    $data = json_decode(file_get_contents("php://input"), true);

    $product_id = sanitizeInput($data['product_id'] ?? null);
    $quantity = sanitizeInput($data['quantity'] ?? null);

    if (!$product_id || !$quantity || !is_numeric($quantity) || $quantity <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid product_id or quantity']);
        exit;
    }

    try {
        // *** CHECK BEFORE INSERT ***
        // Check if the item is already in the cart
        $sql = "SELECT quantity FROM cart_items WHERE user_id = ? AND product_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(1, $user_id, PDO::PARAM_INT);
        $stmt->bindParam(2, $product_id, PDO::PARAM_INT);
        $stmt->execute();
        $existing_quantity = $stmt->fetchColumn();

        if ($existing_quantity !== false) {
            // Item is already in the cart: Update the quantity
            $new_quantity = $existing_quantity + $quantity;
            $sql = "UPDATE cart_items SET quantity = ? WHERE user_id = ? AND product_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(1, $new_quantity, PDO::PARAM_INT);
            $stmt->bindParam(2, $user_id, PDO::PARAM_INT);
            $stmt->bindParam(3, $product_id, PDO::PARAM_INT);
            $stmt->execute();

            http_response_code(200); // Use 200 OK for updates
            echo json_encode(['message' => 'Cart updated']);

        } else {
            // Item is not in the cart: Insert a new row
            $sql = "INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(1, $user_id, PDO::PARAM_INT);
            $stmt->bindParam(2, $product_id, PDO::PARAM_INT);
            $stmt->bindParam(3, $quantity, PDO::PARAM_INT);
            $stmt->execute();

            http_response_code(201); // 201 Created for new items
            echo json_encode(['message' => 'Item added to cart']);
        }

    } catch (PDOException $e) {
        http_response_code(500);
        error_log("Add to cart error: " . $e->getMessage());
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }

} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Method Not Allowed']);
}

// Close DB connection
$conn = null;
?>