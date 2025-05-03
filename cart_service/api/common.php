<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Firebase\JWT\JWT;

// --- CORS Headers ---
$cors_origin = "http://localhost"; // Adjust this if needed
header("Access-Control-Allow-Origin: " . $cors_origin);
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// --- Error Logging ---
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// --- Database Connection ---
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cart_db";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// --- Authentication Function ---
function authenticate() {
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? null;

    if (!$authHeader) {
        error_log("No Authorization header found");
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized: No token provided']);
        exit;
    }

    $token = str_replace('Bearer ', '', $authHeader);
    $secretKey  = "Merhawit@2014"; // ✅ Your actual key

    try {
        $decoded = Firebase\JWT\JWT::decode($token, new Firebase\JWT\Key($secretKey, 'HS256'));
        error_log("Token decoded successfully. User ID: " . $decoded->user_id);
        return $decoded->user_id;
    } catch (Exception $e) {
        error_log("JWT decode error: " . $e->getMessage());
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized: Invalid token: ' . $e->getMessage()]);
        exit;
    }
}

// --- Sanitize Input Function ---
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}
?>
