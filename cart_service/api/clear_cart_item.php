<?php
// cart_service/api/clear_cart.php

require 'common.php';

$user_id = authenticate();

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    try {
        // Use named placeholder
        $sql = "DELETE FROM cart_items WHERE user_id = :user_id";
        $stmt = $conn->prepare($sql);

        // Bind parameter using named placeholder
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        echo json_encode(['message' => 'Cart cleared']);

    } catch (PDOException $e) {
        http_response_code(500);
        error_log("Clear cart error: " . $e->getMessage());
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }

} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
}

$conn = null;
?>