<?php
// user_registration/login_api.php

require 'db.php'; // Assuming your database connection is in 'db.php'
require __DIR__ . '/vendor/autoload.php'; // Include Composer autoloader
use Firebase\JWT\JWT;

// --- CORS Headers ---
header("Access-Control-Allow-Origin: http://localhost/product_service/api"); // Replace with your Product Service's origin
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204); // No Content
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $identifier = $data['identifier'] ?? ''; // Read 'identifier' from JSON body
    $password = $data['password'] ?? '';     // Read 'password' from JSON body

    if (empty($identifier) || empty($password)) {
        http_response_code(400); // Bad Request
        echo json_encode(['success' => false, 'error' => 'Please fill all fields.']);
        exit();
    }

    $pdo = getDatabaseConnection();

    try {
        $stmt = $pdo->prepare("SELECT user_id, password, email FROM register WHERE username = :identifier OR email = :identifier"); // Select email as well
        $stmt->execute([':identifier' => $identifier]);
        $user = $stmt->fetch();

        error_log("Login API - Login attempt with identifier: " . $identifier);
        error_log("Login API - User data from database: " . print_r($user, true));

        // Log the secret key being used for encoding
        $secretKey = 'Merhawit@2014'; // IMPORTANT: Replace with a strong, secret key
        error_log("Login API - Secret Key (Encoding): " . $secretKey);

        if ($user && password_verify($password, $user['password'])) {
            $userId = $user['user_id'];
            $email = $user['email'];
            $issuedAt = time();
            $expire = $issuedAt + (60 * 60);       // Token valid for 1 hour
            $issuer = 'http://localhost/user_registration/api'; // Your service URL
            $audience = 'http://localhost/product_service/api'; // Audience

            $payload = [
                'iss' => $issuer,
                'aud' => $audience,
                'iat' => $issuedAt,           // Issued at
                'nbf' => $issuedAt,           // Not before
                'exp' => $expire,             // Expire
                'user_id' => $userId,
                'email' => $email
            ];

            $jwt = JWT::encode($payload, $secretKey, 'HS256');

            http_response_code(200); // OK
            echo json_encode(['success' => true, 'email' => $email, 'user_id' => $userId, 'auth_key' => $jwt]);

            // Consider removing or commenting out the database update for auth_key
            // $updateStmt = $pdo->prepare("UPDATE register SET auth_key = :auth_key WHERE user_id = :user_id");
            // $updateStmt->execute([':auth_key' => $jwt, ':user_id' => $user['user_id']]);

        } else {
            http_response_code(401); // Unauthorized
            echo json_encode(['success' => false, 'error' => 'Invalid username or password']);
            error_log("Login API - Password verification failed for user: " . $identifier);
        }
    } catch (PDOException $e) {
        http_response_code(500); // Internal Server Error
        echo json_encode(['success' => false, 'error' => 'Login failed: ' . $e->getMessage()]);
        error_log("Login API - PDOException during login: " . $e->getMessage());
    } catch (\Exception $e) {
        http_response_code(500); // Internal Server Error
        echo json_encode(['success' => false, 'error' => 'JWT Encoding failed: ' . $e->getMessage()]);
        error_log("Login API - JWT Encoding failed during login: " . $e->getMessage());
    }
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>