<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if (empty($token) || empty($newPassword) || empty($confirmPassword)) {
        echo json_encode(['success' => false, 'error' => 'All fields are required.']);
        exit();
    }

    if (strlen($newPassword) < 6) {
        echo json_encode(['success' => false, 'error' => 'New password must be at least 6 characters long.']);
        exit();
    }

    if ($newPassword !== $confirmPassword) {
        echo json_encode(['success' => false, 'error' => 'New passwords do not match.']);
        exit();
    }

    $pdo = getDatabaseConnection();

    try {
        // Log the received token for debugging
        error_log("RESET PASSWORD API - Received Token: " . $token);

        $stmt = $pdo->prepare("SELECT user_id, reset_token_expiry FROM register WHERE reset_token = :token");
        $stmt->execute([':token' => $token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Log the expiry time from the database
            error_log("RESET PASSWORD API - Token Expiry from DB: " . $user['reset_token_expiry']);

            // Check if the token has expired
            $expiryTimestamp = strtotime($user['reset_token_expiry']);
            $currentTimestamp = time(); // Current server time in UTC

            // Log the timestamps for comparison
            error_log("RESET PASSWORD API - Current Timestamp (UTC): " . $currentTimestamp);
            error_log("RESET PASSWORD API - Expiry Timestamp (UTC): " . $expiryTimestamp);

            if ($expiryTimestamp > $currentTimestamp) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $updateStmt = $pdo->prepare("UPDATE register SET password = :password, reset_token = NULL, reset_token_expiry = NULL WHERE user_id = :user_id");
                $updateStmt->execute([':password' => $hashedPassword, ':user_id' => $user['user_id']]);

                echo json_encode(['success' => true, 'message' => 'Your password has been reset successfully. You will be redirected to the login page.']);
                exit();
            } else {
                error_log("RESET PASSWORD API - Token has expired.");
                echo json_encode(['success' => false, 'error' => 'Invalid or expired reset token.']);
                exit();
            }
        } else {
            error_log("RESET PASSWORD API - Token not found in database.");
            echo json_encode(['success' => false, 'error' => 'Invalid or expired reset token.']);
            exit();
        }

    } catch (PDOException $e) {
        error_log("RESET PASSWORD API - Database error: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
        exit();
    }

} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
    exit();
}
?>