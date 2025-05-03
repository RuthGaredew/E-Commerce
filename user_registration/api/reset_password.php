<?php
$token = $_GET['token'] ?? '';

if (empty($token)) {
    // Handle invalid or missing token
    echo "<div class='error-container'><h2>Invalid Reset Link</h2><p>The password reset link is invalid or has expired.</p><p><a href='login.php'>Back to Login</a></p></div>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Your Password</title>
    <link rel="stylesheet" href="reset_password_style.css">
</head>
<body>
    <div class="reset-password-container">
        <h2>Reset Your Password</h2>
        <p>Enter your new password below.</p>

        <div id="error-message" class="error" style="display:none;"></div>
        <div id="success-message" class="success" style="display:none;"></div>

        <form id="resetPasswordForm">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            <div class="form-group">
                <label for="new_password">New Password:</label>
                <input type="password" id="new_password" name="new_password" required>
                <div id="new-password-error" class="error-message" style="display:none;"></div>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm New Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
                <div id="confirm-password-error" class="error-message" style="display:none;"></div>
            </div>
            <button type="submit">Reset Password</button>
        </form>

        <p class="login-link"><a href="login.php">Back to Login</a></p>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const resetPasswordForm = document.getElementById('resetPasswordForm');
            const errorMessageDiv = document.getElementById('error-message');
            const successMessageDiv = document.getElementById('success-message');
            const newPasswordInput = document.getElementById('new_password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            const newPasswordErrorDiv = document.getElementById('new-password-error');
            const confirmPasswordErrorDiv = document.getElementById('confirm-password-error');

            resetPasswordForm.addEventListener('submit', function(event) {
                event.preventDefault();
                errorMessageDiv.style.display = 'none';
                successMessageDiv.style.display = 'none';
                newPasswordErrorDiv.style.display = 'none';
                confirmPasswordErrorDiv.style.display = 'none';

                const token = document.querySelector('input[name="token"]').value;
                const newPassword = newPasswordInput.value;
                const confirmPassword = confirmPasswordInput.value;

                let isValid = true;

                if (!newPassword) {
                    newPasswordErrorDiv.textContent = 'New password is required.';
                    newPasswordErrorDiv.style.display = 'block';
                    isValid = false;
                } else if (newPassword.length < 6) {
                    newPasswordErrorDiv.textContent = 'New password must be at least 6 characters long.';
                    newPasswordErrorDiv.style.display = 'block';
                    isValid = false;
                }

                if (!confirmPassword) {
                    confirmPasswordErrorDiv.textContent = 'Confirm new password is required.';
                    confirmPasswordErrorDiv.style.display = 'block';
                    isValid = false;
                } else if (newPassword !== confirmPassword) {
                    confirmPasswordErrorDiv.textContent = 'New passwords do not match.';
                    confirmPasswordErrorDiv.style.display = 'block';
                    isValid = false;
                }

                if (!isValid) {
                    return; // Stop form submission if client-side validation fails
                }

                const formData = new FormData(resetPasswordForm);

                fetch('reset_password_api.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        successMessageDiv.textContent = data.message;
                        successMessageDiv.style.display = 'block';
                        resetPasswordForm.reset();
                        setTimeout(function() {
                            window.location.href = 'login.php';
                        }, 3000);
                    } else {
                        errorMessageDiv.textContent = data.error;
                        errorMessageDiv.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    errorMessageDiv.textContent = 'An error occurred while processing your request.';
                    errorMessageDiv.style.display = 'block';
                });
            });
        });
    </script>
    <style>
        .error-message {
            color: red;
            font-size: 0.9em;
            margin-top: 5px;
        }
    </style>
</body>
</html>