<?php
// user_registration/api/register_api_from_product_service.php
require 'db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'] ?? null;
$password = $data['password'] ?? null;

if ($email && $password) {
    // Basic validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'error' => 'Invalid email format.']);
        http_response_code(400);
        exit();
    }
    if (strlen($password) < 8) {
        echo json_encode(['success' => false, 'error' => 'Password must be at least 8 characters long.']);
        http_response_code(400);
        exit();
    }

    $pdo = getDatabaseConnection();

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM register WHERE email = :email");
    $stmt->execute([':email' => $email]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'error' => 'Email address already registered.']);
        http_response_code(409); // Conflict
        exit();
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Generate a unique auth_key
    $authKey = bin2hex(random_bytes(32));

    // Insert the new user
    $stmt = $pdo->prepare("INSERT INTO register (email, password, auth_key, registration_date) VALUES (:email, :password, :auth_key, NOW())");
    $stmt->execute([':email' => $email, ':password' => $hashedPassword, ':auth_key' => $authKey]);

    if ($stmt->rowCount() > 0) {
        $user_id = $pdo->lastInsertId(); // Get the newly inserted user ID
        // Option 1: Redirect to login
        echo json_encode(['success' => true, 'message' => 'Registration successful. Please log in.']);
        // Option 2: Return user_id and auth_key for immediate login (as commented in home_page.php)
        // echo json_encode(['success' => true, 'user_id' => $user_id, 'auth_key' => $authKey]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Error during registration. Please try again.']);
        http_response_code(500); // Internal Server Error
    }

} else {
    echo json_encode(['success' => false, 'error' => 'Email and password are required.']);
    http_response_code(400); // Bad Request
}
?>