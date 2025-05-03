<?php
// user_registration/api/verify_session.php

require 'db.php'; // Assuming your database connection is in 'db.php'

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$userId = $data['user_id'] ?? null;
$authKey = $data['auth_key'] ?? null;

if ($userId && $authKey) {
    $pdo = getDatabaseConnection();
    $stmt = $pdo->prepare("SELECT email, auth_key FROM register WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $userId]);
    $user = $stmt->fetch();

    if ($user && $user['auth_key'] === $authKey) {
        echo json_encode(['success' => true, 'email' => $user['email'], 'user_id' => $userId]);
        exit();
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid user ID or authentication key.']);
        http_response_code(401); // Unauthorized
        exit();
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Missing user ID or authentication key.']);
    http_response_code(400); // Bad Request
    exit();
}
?>