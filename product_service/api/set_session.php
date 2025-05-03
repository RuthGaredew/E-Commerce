<?php
session_start();
header('Content-Type: application/json');

// Get the input data from the JSON body
$data = json_decode(file_get_contents('php://input'), true);

// Ensure all necessary data is provided
if (!isset($data['logged_in'], $data['email'], $data['user_id'], $data['token'])) {
    echo json_encode(['success' => false, 'error' => 'Missing necessary login information.']);
    exit();
}

// Set session variables
$_SESSION['product_service_logged_in'] = $data['logged_in'];
$_SESSION['product_service_email'] = $data['email'];
$_SESSION['product_service_user_id'] = $data['user_id'];
$_SESSION['product_service_auth_token'] = $data['token'];

// Respond with success
echo json_encode(['success' => true, 'message' => 'Session set successfully.']);
?>
