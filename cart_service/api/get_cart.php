<?php
// cart_service/api/get_cart_items.php

require_once __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/common.php'; // Your common authentication and database connection file

header('Content-Type: application/json');

// --- CORS Headers ---
header("Access-Control-Allow-Origin: http://localhost"); // Adjust if needed
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$user_id = authenticate(); // Authenticate the user and get the user ID

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // SQL query to fetch cart items with product details
        // IMPORTANT:  Prefix the products table with the database name
        $sql = "SELECT ci.product_id, ci.quantity, p.product_name, p.price, p.discount_price, p.image_path
                FROM cart_items ci
                JOIN product_db.products p ON ci.product_id = p.product_id
                WHERE ci.user_id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(1, $user_id, PDO::PARAM_INT);
        $stmt->execute();

        $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Return the cart items as JSON
        echo json_encode($cart_items);

    } catch (PDOException $e) {
        http_response_code(500);
        error_log("Get cart items error: PDOException: " . $e->getMessage());  // More specific logging
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    } catch (Exception $e) {
        http_response_code(500);
        error_log("Get cart items error: General Exception: " . $e->getMessage()); // More specific logging
        echo json_encode(['error' => 'General error: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
}

$conn = null; // Close the database connection
?>