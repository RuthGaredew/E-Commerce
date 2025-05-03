<?php
// user_registration/api/logout_api.php

require 'db.php'; // Your DB connection script

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Function to handle error responses
function sendError($message, $code = 400) {
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $message]);
    exit();
}

// Read Authorization header (case-insensitive)
$headers = array_change_key_case(getallheaders(), CASE_LOWER);
$authorizationHeader = $headers['authorization'] ?? null;

$authToken = null;
$userId = null;

// Extract token from Bearer Authorization header
if ($authorizationHeader && preg_match('/Bearer\s(\S+)/', $authorizationHeader, $matches)) {
    $authToken = $matches[1];
} else {
    // Fallback to POST body
    $data = json_decode(file_get_contents('php://input'), true);
    $userId = $data['user_id'] ?? null;
    $authToken = $data['auth_key'] ?? null;
}

// If neither auth_token nor user_id provided, return error
if (!$authToken && !$userId) {
    sendError('Missing authentication token or user ID.');
}

$pdo = getDatabaseConnection();

if ($authToken) {
    // Invalidate auth_key in the database
    $stmt = $pdo->prepare("UPDATE register SET auth_key = NULL WHERE auth_key = :auth_key");
    $stmt->execute([':auth_key' => $authToken]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Logout successful. Token invalidated.']);
    } else {
        sendError('Invalid authentication token.', 401);
    }
} elseif ($userId) {
    // Invalidate based on user ID
    $stmt = $pdo->prepare("UPDATE register SET auth_key = NULL WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $userId]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Logout successful for user ID.']);
    } else {
        sendError('Invalid user ID.', 401);
    }
}
?>
