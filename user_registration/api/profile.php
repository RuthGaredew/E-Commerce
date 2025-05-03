<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['product_service_auth_token'])) {
    header("Location: login.php");
    exit();
}


$token = $_SESSION['product_service_auth_token'];
$apiBase = 'http://localhost/user-registration-service/profile_api.php';

$email = '';
$profilePhoto = 'default.png';
$uploadError = '';
$passwordError = '';
$passwordSuccess = '';
$uploadSuccess = false;

// Fetch profile info via API

$ch = curl_init($apiBase . '?action=get_profile');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token
]);
$response = curl_exec($ch);
curl_close($ch);
$data = json_decode($response, true);
if ($data && isset($data['email'])) {
    $email = $data['email'];
    $profilePhoto = $data['profile_photo'] ?? 'default.png';
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Upload profile photo
    if (isset($_POST['upload_photo'])) {
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['profile_photo']['tmp_name'];
            $fileName = $_FILES['profile_photo']['name'];

            $fileData = file_get_contents($fileTmpPath);
            $boundary = uniqid();
            $delimiter = '-------------' . $boundary;

            $postData = "--$delimiter\r\n"
                . "Content-Disposition: form-data; name=\"profile_photo\"; filename=\"" . $fileName . "\"\r\n"
                . "Content-Type: " . mime_content_type($fileTmpPath) . "\r\n\r\n"
                . $fileData . "\r\n"
                . "--$delimiter--\r\n";

            $ch = curl_init($apiBase . '?action=upload_photo');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: Bearer $token",
                "Content-Type: multipart/form-data; boundary=$delimiter",
                "Content-Length: " . strlen($postData)
            ]);

            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                $uploadSuccess = true;
                header("Location: profile.php?upload_success=1");
                exit();
            } else {
                $uploadError = 'Photo upload failed.';
            }
        } else {
            $uploadError = 'Invalid file.';
        }
    }

    // Change password
    if (isset($_POST['change_password'])) {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];

        $postData = http_build_query([
            'current_password' => $currentPassword,
            'new_password' => $newPassword,
            'confirm_password' => $confirmPassword
        ]);

        $ch = curl_init($apiBase . '?action=change_password');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $token",
            "Content-Type: application/x-www-form-urlencoded"
        ]);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            header("Location: profile.php?password_change_success=1");
            exit();
        } else {
            $responseData = json_decode($result, true);
            $passwordError = $responseData['error'] ?? 'Password change failed.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Profile</title>
    <link rel="stylesheet" href="profile.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .profile-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 400px;
            text-align: center;
        }
        h2 {
            color: #333;
            margin-bottom: 20px;
        }
        .profile-info {
            margin-bottom: 20px;
        }
        .profile-image {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
        }
        h3 {
            color: #555;
            margin-top: 0;
        }
        .upload-photo, .change-password {
            border-top: 1px solid #eee;
            padding-top: 20px;
            margin-top: 20px;
            text-align: left;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: bold;
        }
        input[type="file"], input[type="password"] {
            width: calc(100% - 22px);
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            margin-bottom: 10px;
        }
        button {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
        }
        button:hover {
            background-color: #0056b3;
        }
        .message {
            margin-top: 10px;
            padding: 10px;
            border-radius: 5px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .logout-link {
            margin-top: 20px;
            text-align: center;
        }
        .logout-link a {
            color: #dc3545;
            text-decoration: none;
        }
        .logout-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <h2>Your Profile</h2>

        <div class="profile-info">
            <img src="uploads/<?= htmlspecialchars($profilePhoto) ?>" alt="Profile Photo" class="profile-image">
            <h3>Email: <?= htmlspecialchars($email) ?></h3>
        </div>

        <div class="upload-photo">
            <h3>Upload Profile Photo</h3>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="upload_photo">
                <input type="file" name="profile_photo" id="profilePhotoInput" accept="image/*" required>
                <button type="submit">Upload Photo</button>
                <?php if ($uploadError): ?>
                    <div class="message error"><?= htmlspecialchars($uploadError) ?></div>
                <?php elseif (isset($_GET['upload_success'])): ?>
                    <div class="message success">Profile photo uploaded successfully.</div>
                <?php endif; ?>
            </form>
        </div>

        <div class="change-password">
            <h3>Change Password</h3>
            <form method="post">
                <input type="hidden" name="change_password">
                <div class="form-group">
                    <label for="current_password">Current Password:</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label for="new_password">New Password:</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit">Change Password</button>
                <?php if ($passwordError): ?>
                    <div class="message error"><?= htmlspecialchars($passwordError) ?></div>
                <?php elseif (isset($_GET['password_change_success'])): ?>
                    <div class="message success">Password changed successfully.</div>
                <?php endif; ?>
            </form>
        </div>

        <p class="logout-link"><a href="logout.php">Logout</a></p>
    </div>
</body>
</html>
