<?php
// cart_service/api/update_cart_item.php

require 'common.php';

$user_id = authenticate();

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true);
    $product_id = sanitizeInput($_GET['product_id'] ?? null);
    $quantity = sanitizeInput($data['quantity'] ?? null);

    if (!$product_id || !$quantity || !is_numeric($quantity) || $quantity <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid product_id or quantity']);
        exit;
    }

    try {
        // Use named placeholders
        $sql = "UPDATE cart_items SET quantity = :quantity WHERE user_id = :user_id AND product_id = :product_id";
        $stmt = $conn->prepare($sql);

        // Bind parameters using named placeholders
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);

        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Item not found in cart']);
            exit;
        }

        echo json_encode(['message' => 'Cart item updated']);

    } catch (PDOException $e) {
        http_response_code(500);
        error_log("Update cart item error: " . $e->getMessage());
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }

} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
}

$conn = null;
?>