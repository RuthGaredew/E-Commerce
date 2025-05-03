<?php
// cart_service/api/cart_items_count.php

header('Content-Type: application/json');
require_once __DIR__ . '/../vendor/autoload.php';
require '../db.php'; // Your database connection file

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// --- CORS Headers ---
header("Access-Control-Allow-Origin: http://localhost"); // Adjust if needed
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// --- Error Logging ---
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// --- Authentication ---
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? null;

if (!$authHeader) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized: No token provided']);
    exit;
}

$token = str_replace('Bearer ', '', $authHeader);

// ✅ Use the same secret key as the rest of the system
$secretKey = "Merhawit@2014";

try {
    $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
    $user_id = $decoded->user_id;
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized: Invalid token: ' . $e->getMessage()]);
    exit;
}

// --- Handle GET request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        if (!isset($conn) || !$conn) {
            throw new Exception("Database connection not established.");
        }

        $sql = "SELECT SUM(quantity) AS count FROM cart_items WHERE user_id = :user_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = $result['count'] ?? 0;

        echo json_encode(['count' => (int)$count]);
    } catch (PDOException $e) {
        http_response_code(500);
        error_log("Database error in cart_items_count.php: " . $e->getMessage(), 0);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    } catch (Exception $e) {
        http_response_code(500);
        error_log("General error in cart_items_count.php: " . $e->getMessage(), 0);
        echo json_encode(['error' => 'General error: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
}

$conn = null; // Close the database connection
?>
