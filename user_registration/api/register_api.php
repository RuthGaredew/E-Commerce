<?php
// user_registration/register_api.php

require 'db.php'; // Assuming your database connection is in 'db.php'

// --- CORS Headers ---
header("Access-Control-Allow-Origin: http://localhost/user_registration/api/"); // Replace with the origin of your registration page
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? ''; // Assuming you're sending this

    if (empty($email) || empty($password) || empty($confirmPassword)) {
        http_response_code(400); // Bad Request
        echo json_encode(['success' => false, 'error' => 'Please fill all fields.']);
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400); // Bad Request
        echo json_encode(['success' => false, 'error' => 'Invalid email format.']);
        exit();
    }

    if ($password !== $confirmPassword) {
        http_response_code(400); // Bad Request
        echo json_encode(['success' => false, 'error' => 'Passwords do not match.']);
        exit();
    }

    $pdo = getDatabaseConnection();

    try {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT user_id FROM register WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $existingUser = $stmt->fetch();

        if ($existingUser) {
            http_response_code(409); // Conflict
            echo json_encode(['success' => false, 'error' => 'Email already exists.']);
            exit();
        }

        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        $stmt = $pdo->prepare("INSERT INTO register (email, password, created_at) VALUES (:email, :password, NOW())");
        $stmt->execute([':email' => $email, ':password' => $hashedPassword]);

        http_response_code(201); // Created
        echo json_encode(['success' => true, 'message' => 'Registration successful.']);

    } catch (PDOException $e) {
        http_response_code(500); // Internal Server Error
        echo json_encode(['success' => false, 'error' => 'Registration failed: ' . $e->getMessage()]);
    }

} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
}
?>