<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Product Service</title>
</head>
<body>
    <h2>Login to your Account</h2>
    
    <form id="loginForm">
        <input type="email" id="email" placeholder="Email" required><br><br>
        <input type="password" id="password" placeholder="Password" required><br><br>
        <button type="submit">Login</button>
    </form>

    <div id="message"></div>

    <script>
    document.getElementById('loginForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;

        try {
            // 1. Call User Service's login_api.php
            const loginResponse = await fetch('http://localhost/user_registration/api/login_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email, password })
            });

            const loginData = await loginResponse.json();

            if (loginResponse.ok && loginData.success) {
                // 2. Set session in Product Service
                await fetch('set_session.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        logged_in: true,
                        email: email,
                        user_id: loginData.user_id,
                        token: loginData.token
                    })
                });

                // 3. Redirect to home page
                window.location.href = 'home_page.php';
            } else {
                document.getElementById('message').innerText = loginData.message || "Login failed!";
            }
        } catch (error) {
            console.error('Error:', error);
            document.getElementById('message').innerText = "Error connecting to login service!";
        }
    });
    </script>
</body>
</html>
