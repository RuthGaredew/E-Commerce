<?php
require __DIR__ . '/vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// ************************************************************************
// IMPORTANT: Ensure this $secretKey is EXACTLY the same as the one used
// in your login_api.php file. Any difference will cause token verification
// to fail.
// ************************************************************************
$secretKey = 'Merhawit@2014'; // Replace with your actual secret key
error_log("Profile API - Secret Key (Decoding): " . $secretKey);

// Function to get the user ID from the JWT
function getUserIdFromToken() {
    global $secretKey;
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

    // Debugging: Log the raw Authorization header
    error_log("Profile API - Raw Authorization Header: " . $authHeader);

    if (preg_match('/Bearer\s(.*)/', $authHeader, $matches)) {
        $token = $matches[1];
        // Debugging: Log the extracted token
        error_log("Profile API - Extracted Token: " . $token);
        try {
            $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
            // Debugging: Log the decoded payload (if successful)
            error_log("Profile API - Decoded Payload: " . json_encode($decoded));
            return $decoded->user_id ?? null;
        } catch (\Exception $e) {
            // Debugging: Log the specific JWT decoding error
            error_log("Profile API - JWT Decoding Error: " . $e->getMessage());
            return null;
        }
    }
    return null;
}

// Check if the user is authenticated via JWT
$user_id = getUserIdFromToken();
if (!$user_id) {
    http_response_code(401); // Unauthorized
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Invalid or missing token.']);
    exit();
}

require 'db.php';
$pdo = getDatabaseConnection();

$action = $_GET['action'] ?? '';

if ($action === 'get_profile') {
    $stmt = $pdo->prepare("SELECT email, profile_photo FROM register WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo json_encode(['success' => true, 'email' => $user['email'], 'profile_photo' => $user['profile_photo']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'User profile not found.']);
    }
    exit();
} elseif ($action === 'upload_photo') {
    // You might want to reconsider how you handle file uploads in a stateless API.
    // Passing the token might be needed here as well, depending on how you trigger this.
    // For simplicity, I'll assume the token is still valid via the Authorization header.
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === 0) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($_FILES['profile_photo']['type'], $allowedTypes)) {
            $uploadDir = 'uploads/';
            $fileExt = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
            $newFileName = uniqid('profile_') . '.' . $fileExt;
            $destination = $uploadDir . $newFileName;

            if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $destination)) {
                // Update database
                $stmt = $pdo->prepare("UPDATE register SET profile_photo = :profile_photo WHERE user_id = :user_id");
                $stmt->execute([':profile_photo' => $newFileName, ':user_id' => $user_id]);

                echo json_encode(['success' => true, 'message' => 'Profile photo uploaded successfully.', 'profile_photo_url' => $newFileName]);
                exit();
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file.']);
                exit();
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and GIF are allowed.']);
            exit();
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No file uploaded or an error occurred.']);
        exit();
    }
} elseif ($action === 'change_password') {
    // Similar to upload_photo, ensure you handle the token appropriately if this is called via a separate request.
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            echo json_encode(['success' => false, 'message' => 'All password fields are required.']);
            exit();
        }

        if ($newPassword !== $confirmPassword) {
            echo json_encode(['success' => false, 'message' => 'New passwords do not match.']);
            exit();
        }

        if (strlen($newPassword) < 6) {
            echo json_encode(['success' => false, 'message' => 'New password must be at least 6 characters long.']);
            exit();
        }

        // Verify current password
        $stmt = $pdo->prepare("SELECT password FROM register WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($currentPassword, $user['password'])) {
            // Update password
            $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE register SET password = :password WHERE user_id = :user_id");
            $stmt->execute([':password' => $hashedNewPassword, ':user_id' => $user_id]);

            echo json_encode(['success' => true, 'message' => 'Password changed successfully.']);
            exit();
        } else {
            echo json_encode(['success' => false, 'message' => 'Incorrect current password.']);
            exit();
        }
    } else {
        http_response_code(405); // Method Not Allowed
        echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
        exit();
    }
} else {
    // Handle other requests or provide a default response
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    exit();
}
?>