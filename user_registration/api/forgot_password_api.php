<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db.php';
require 'vendor/autoload.php'; // Use Composer autoloader for PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Configure email settings
$mailHost = 'smtp.gmail.com';
$mailUsername = 'merhi2011@gmail.com';
$mailPassword = 'bczm heyb evuz eefn'; // <-- IMPORTANT! Use App Password if 2FA is enabled
$mailPort = 587;
$mailEncryption = 'tls';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'error' => 'Invalid email format.']);
        exit();
    }

    $pdo = getDatabaseConnection();

    try {
        $stmt = $pdo->prepare("SELECT user_id FROM register WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user) {
            $token = bin2hex(random_bytes(32)); // Generate a unique token
            $stmt = $pdo->prepare("UPDATE register SET reset_token = :token, reset_token_expiry = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE email = :email");
            $stmt->execute([':token' => $token, ':email' => $email]);

            // Log the expiry value after insertion
            $stmt = $pdo->prepare("SELECT reset_token_expiry FROM register WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            error_log("FORGOT PASSWORD API - Stored Expiry: " . $result['reset_token_expiry']);


            $resetLink = 'http://localhost/user_registration/api/reset_password.php?token=' . $token; // Adjust the URL

            $mail = new PHPMailer(true);

            try {
                //Server settings
                $mail->isSMTP();
                $mail->Host       = $mailHost;
                $mail->SMTPAuth   = true;
                $mail->Username   = $mailUsername;
                $mail->Password   = $mailPassword;
                $mail->SMTPSecure = $mailEncryption;
                $mail->Port       = $mailPort;

                //Recipients
                $mail->setFrom($mailUsername, 'Your Website Name');
                $mail->addAddress($email);

                //Content
                $mail->isHTML(true);
                $mail->Subject = 'Password Reset Request';
                $mail->Body     = 'Click the following link to reset your password: <a href="' . $resetLink . '">' . $resetLink . '</a>. This link will expire in 1 hour.';
                $mail->AltBody = 'Click the following link to reset your password: ' . $resetLink . '. This link will expire in 1 hour.';

                $mail->send();
                echo json_encode(['success' => true, 'message' => 'A password reset link has been sent to your email address. Please check your inbox (and spam folder).']);
                exit();

            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => 'Failed to send reset email: ' . $mail->ErrorInfo]);
                exit();
            }

        } else {
            echo json_encode(['success' => false, 'error' => 'Email address not found.']);
            exit();
        }

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
        exit();
    }

} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
    exit();
}
?>