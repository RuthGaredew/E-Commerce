<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="register_style.css">
    <style>
        .error-message {
            color: red;
            font-size: 0.9em;
            margin-top: 5px;
        }
        .password-strength {
            font-size: 0.8em;
            margin-top: 5px;
        }
        .weak { color: red; }
        .medium { color: orange; }
        .strong { color: green; }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Create Account</h2>

        <div id="error-message" class="error" style="display:none;"></div>
        <div id="success-message" class="success" style="display:none;"></div>

        <form id="registrationForm">
            <div class="form-group">
                <input type="email" name="email" id="email" placeholder="Email" required>
                <div id="email-error" class="error-message" style="display:none;"></div>
            </div>
            <div class="form-group">
                <input type="password" name="password" id="password" placeholder="Password" required>
                <div id="password-error" class="error-message" style="display:none;"></div>
                <div id="password-strength" class="password-strength"></div>
            </div>
            <div class="form-group">
                <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required>
                <div id="confirm-password-error" class="error-message" style="display:none;"></div>
            </div>
            <div class="form-group">
                <input type="checkbox" name="terms" id="terms" required>
                <label for="terms">I agree to the <a href="#">Terms and Conditions</a></label>
                <div id="terms-error" class="error-message" style="display:none;"></div>
            </div>
            <button type="submit">Register</button>
        </form>
        <p class="login-link">Already have an account? <a href="login.php">Login</a></p>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const registrationForm = document.getElementById('registrationForm');
            const errorMessageDiv = document.getElementById('error-message');
            const successMessageDiv = document.getElementById('success-message');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            const emailErrorDiv = document.getElementById('email-error');
            const passwordErrorDiv = document.getElementById('password-error');
            const confirmPasswordErrorDiv = document.getElementById('confirm-password-error');
            const passwordStrengthDiv = document.getElementById('password-strength');
            const termsCheckbox = document.getElementById('terms');
            const termsErrorDiv = document.getElementById('terms-error');

            passwordInput.addEventListener('input', function() {
                const password = passwordInput.value;
                let strength = '';
                if (password.length < 8) {
                    strength = 'Weak';
                    passwordStrengthDiv.className = 'password-strength weak';
                } else if (password.length < 12) {
                    strength = 'Medium';
                    passwordStrengthDiv.className = 'password-strength medium';
                } else {
                    strength = 'Strong';
                    passwordStrengthDiv.className = 'password-strength strong';
                }
                passwordStrengthDiv.textContent = strength ? `Password Strength: ${strength}` : '';
            });

            registrationForm.addEventListener('submit', function(event) {
                event.preventDefault();
                errorMessageDiv.style.display = 'none';
                successMessageDiv.style.display = 'none';
                emailErrorDiv.style.display = 'none';
                passwordErrorDiv.style.display = 'none';
                confirmPasswordErrorDiv.style.display = 'none';
                termsErrorDiv.style.display = 'none';

                const email = emailInput.value.trim();
                const password = passwordInput.value;
                const confirmPassword = confirmPasswordInput.value;
                const termsChecked = termsCheckbox.checked;

                let isValid = true;

                if (!email) {
                    emailErrorDiv.textContent = 'Email is required.';
                    emailErrorDiv.style.display = 'block';
                    isValid = false;
                } else if (!isValidEmail(email)) {
                    emailErrorDiv.textContent = 'Invalid email format.';
                    emailErrorDiv.style.display = 'block';
                    isValid = false;
                }

                if (!password) {
                    passwordErrorDiv.textContent = 'Password is required.';
                    passwordErrorDiv.style.display = 'block';
                    isValid = false;
                } else if (password.length < 8) {
                    passwordErrorDiv.textContent = 'Password must be at least 8 characters long.';
                    passwordErrorDiv.style.display = 'block';
                    isValid = false;
                }

                if (!confirmPassword) {
                    confirmPasswordErrorDiv.textContent = 'Confirm password is required.';
                    confirmPasswordErrorDiv.style.display = 'block';
                    isValid = false;
                } else if (password !== confirmPassword) {
                    confirmPasswordErrorDiv.textContent = 'Passwords do not match.';
                    confirmPasswordErrorDiv.style.display = 'block';
                    isValid = false;
                }

                if (!termsChecked) {
                    termsErrorDiv.textContent = 'You must agree to the Terms and Conditions.';
                    termsErrorDiv.style.display = 'block';
                    isValid = false;
                }

                if (!isValid) {
                    return;
                }

                const formData = new FormData(registrationForm);

                fetch('register_api.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        successMessageDiv.textContent = data.message;
                        successMessageDiv.style.display = 'block';
                        errorMessageDiv.style.display = 'none';
                        registrationForm.reset();
                        setTimeout(() => {
                            window.location.href = 'login.php';
                        }, 1500);
                    } else {
                        errorMessageDiv.textContent = data.error;
                        errorMessageDiv.style.display = 'block';
                        successMessageDiv.style.display = 'none';
                    }
                })
                .catch(error => {
                    errorMessageDiv.textContent = 'An error occurred during registration.';
                    errorMessageDiv.style.display = 'block';
                    successMessageDiv.style.display = 'none';
                    console.error('Error:', error);
                });
            });

            function isValidEmail(email) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return emailRegex.test(email);
            }
        });
    </script>
</body>
</html>