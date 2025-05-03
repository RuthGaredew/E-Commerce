<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="forgot_password_style.css">
</head>
<body>
    <div class="forgot-password-container">
        <h2>Forgot Your Password?</h2>
        <p>Please enter your email address.</p>

        <div id="error-message" class="error" style="display:none;"></div>
        <div id="success-message" class="success" style="display:none;"></div>

        <form id="forgotPasswordForm">
            <div class="form-group">
                <label for="email"></label>
                <input type="email" id="email" name="email" required>
            </div>
            <button type="submit">Send Reset Link</button>
        </form>

        <p class="login-link"><a href="login.php">Back to Login</a></p>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const forgotPasswordForm = document.getElementById('forgotPasswordForm');
            const errorMessageDiv = document.getElementById('error-message');
            const successMessageDiv = document.getElementById('success-message');

            forgotPasswordForm.addEventListener('submit', function(event) {
                event.preventDefault();
                errorMessageDiv.style.display = 'none';
                successMessageDiv.style.display = 'none';

                const formData = new FormData(forgotPasswordForm);

                fetch('forgot_password_api.php', {
    method: 'POST',
    body: formData
})
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        successMessageDiv.textContent = data.message;
                        successMessageDiv.style.display = 'block';
                        forgotPasswordForm.reset();
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
</body>
</html>