<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="login_styles.css">
    <style>
        .error-message {
            color: red;
            font-size: 0.9em;
            margin-top: 5px;
        }

        .hidden {
            display: none;
        }

        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>

        <div id="error-message" class="error-message hidden"></div>

        <form id="loginForm">
            <div class="form-group">
                <label for="identifier">Email:</label>
                <input type="text" id="identifier" name="identifier" required>
                <div id="identifier-error" class="error-message hidden"></div>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
                <div id="password-error" class="error-message hidden"></div>
            </div>
            <button type="submit" id="loginButton">Login</button>
        </form>
        <p class="register-link">Don't have an account? <a href="register.php">Register</a></p>
        <p class="forgot-password"><a href="forgot_password.php">Forgot Password?</a></p>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const loginForm = document.getElementById('loginForm');
            const loginButton = document.getElementById('loginButton');
            const errorMessageDiv = document.getElementById('error-message');
            const identifierInput = document.getElementById('identifier');
            const passwordInput = document.getElementById('password');
            const identifierErrorDiv = document.getElementById('identifier-error');
            const passwordErrorDiv = document.getElementById('password-error');

            loginForm.addEventListener('submit', function (event) {
                event.preventDefault();

                // Reset error messages
                errorMessageDiv.classList.add('hidden');
                identifierErrorDiv.classList.add('hidden');
                passwordErrorDiv.classList.add('hidden');

                const identifier = identifierInput.value.trim();
                const password = passwordInput.value;

                let isValid = true;

                if (!identifier) {
                    identifierErrorDiv.textContent = 'Username or email is required.';
                    identifierErrorDiv.classList.remove('hidden');
                    isValid = false;
                }

                if (!password) {
                    passwordErrorDiv.textContent = 'Password is required.';
                    passwordErrorDiv.classList.remove('hidden');
                    isValid = false;
                }

                if (!isValid) return;

                // Disable the button while processing
                loginButton.disabled = true;
                loginForm.classList.add('loading');

                fetch('login_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ identifier, password })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Use this base URL dynamically if needed
                        const baseURL = 'http://localhost/product_service/api/';
                        const redirectURL = `${baseURL}home_page.php?login_success=true&user_id=${encodeURIComponent(data.user_id)}&auth_key=${encodeURIComponent(data.auth_key)}&email=${encodeURIComponent(data.email)}`;
                        window.location.href = redirectURL;
                    } else {
                        errorMessageDiv.textContent = data.error || 'Login failed. Please try again.';
                        errorMessageDiv.classList.remove('hidden');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    errorMessageDiv.textContent = 'An error occurred during login.';
                    errorMessageDiv.classList.remove('hidden');
                })
                .finally(() => {
                    loginButton.disabled = false;
                    loginForm.classList.remove('loading');
                });
            });
        });
    </script>
</body>
</html>