<?php
// cart_service/api/delete_cart_item.php

require 'common.php';

$user_id = authenticate();

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Sanitize input
    $product_id = sanitizeInput($_GET['product_id'] ?? null);

    if (!$product_id) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing product_id']);
        exit;
    }

    try {
        // Use named placeholders
        $sql = "DELETE FROM cart_items WHERE user_id = :user_id AND product_id = :product_id";
        $stmt = $conn->prepare($sql);

        // Bind parameters using named placeholders
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);

        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Item not found in cart']);
            exit;
        }

        echo json_encode(['message' => 'Cart item deleted']);

    } catch (PDOException $e) {
        http_response_code(500);
        error_log("Delete cart item error: " . $e->getMessage());
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }

} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
}

$conn = null;
?>