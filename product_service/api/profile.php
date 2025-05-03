<?php
session_start();
$loggedIn = isset($_SESSION['product_service_logged_in']) && $_SESSION['product_service_logged_in'] === true && isset($_SESSION['product_service_email']);
$email = $loggedIn ? htmlspecialchars($_SESSION['product_service_email']) : '';
$authToken = $_SESSION['product_service_auth_token'] ?? '';

if (!$loggedIn || !$authToken) {
    header("Location: http://localhost/user_registration/api/login.php"); // Redirect if not logged in
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Profile</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .profile-container { max-width: 600px; margin: 0 auto; }
        h2 { margin-bottom: 20px; }
        .profile-info { border: 1px solid #ccc; padding: 15px; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input[type="text"], input[type="email"], input[type="password"], input[type="file"] { width: 100%; padding: 8px; box-sizing: border-box; margin-bottom: 10px; }
        button { padding: 10px 15px; background-color: #007bff; color: white; border: none; cursor: pointer; }
        button:hover { background-color: #0056b3; }
        .error { color: red; }
        #profile-image-container { margin-bottom: 15px; }
        #profile-image { max-width: 100px; border-radius: 50%; }
    </style>
</head>
<body>
    <div class="profile-container">
        <h2>Manage Your Profile</h2>

        <div class="profile-info" id="profile-details">
            <p>Loading profile information...</p>
        </div>

        <h3>Update Profile Photo</h3>
        <form id="uploadPhotoForm" enctype="multipart/form-data">
            <div class="form-group">
                <label for="profile-photo-upload">Choose a new profile photo:</label>
                <input type="file" id="profile-photo-upload" name="profile_photo">
            </div>
            <button type="button" id="upload-photo-button">Upload Photo</button>
            <div id="upload-photo-message"></div>
        </form>

        <h3>Change Password</h3>
        <form id="changePasswordForm">
            <div class="form-group">
                <label for="current-password">Current Password:</label>
                <input type="password" id="current-password" name="current_password" required>
            </div>
            <div class="form-group">
                <label for="new-password">New Password:</label>
                <input type="password" id="new-password" name="new_password" required>
            </div>
            <div class="form-group">
                <label for="confirm-password">Confirm New Password:</label>
                <input type="password" id="confirm-password" name="confirm_password" required>
            </div>
            <button type="submit">Change Password</button>
            <div id="change-password-message"></div>
        </form>

        <p><a href="home_page.php">Back to Home</a></p>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const profileDetailsContainer = document.getElementById('profile-details');
            const uploadPhotoButton = document.getElementById('upload-photo-button');
            const uploadPhotoForm = document.getElementById('uploadPhotoForm');
            const changePasswordForm = document.getElementById('changePasswordForm');
            const uploadPhotoMessage = document.getElementById('upload-photo-message');
            const changePasswordMessage = document.getElementById('change-password-message');
            const authToken = '<?php echo $authToken; ?>';

            // Function to fetch profile information
            function fetchProfile() {
                if (authToken) {
                    fetch('http://localhost/user_registration/api/profile_api.php?action=get_profile', {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': `Bearer ${authToken}`
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            profileDetailsContainer.innerHTML = `
                                <p>Email: ${data.email}</p>
                                <div id="profile-image-container">
                                    ${data.profile_photo ? `<img id="profile-image" src="http://localhost/user_registration/uploads/${data.profile_photo}" alt="Profile Photo">` : '<p>No profile photo uploaded.</p>'}
                                </div>
                            `;
                        } else {
                            profileDetailsContainer.innerHTML = `<p class="error">${data.message}</p>`;
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching profile:', error);
                        profileDetailsContainer.innerHTML = '<p class="error">Failed to load profile information.</p>';
                    });
                } else {
                    profileDetailsContainer.innerHTML = '<p class="error">Authentication token not found. Please log in again.</p>';
                }
            }

            // Function to upload profile photo
            function uploadProfilePhoto() {
                const fileInput = document.getElementById('profile-photo-upload');
                const file = fileInput.files[0];
                const formData = new FormData();
                formData.append('profile_photo', file);

                if (authToken && file) {
                    fetch('http://localhost/user_registration/api/profile_api.php?action=upload_photo', {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${authToken}`
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        uploadPhotoMessage.textContent = data.message;
                        if (data.success && data.profile_photo_url) {
                            fetchProfile(); // Reload profile to show the new image
                        }
                    })
                    .catch(error => {
                        console.error('Error uploading profile photo:', error);
                        uploadPhotoMessage.textContent = 'Failed to upload profile photo.';
                    });
                } else {
                    uploadPhotoMessage.textContent = 'Please select a file to upload.';
                }
            }

            // Function to change password
            function changePassword(event) {
                event.preventDefault();
                const currentPasswordInput = document.getElementById('current-password');
                const newPasswordInput = document.getElementById('new-password');
                const confirmPasswordInput = document.getElementById('confirm-password');

                const currentPassword = currentPasswordInput.value;
                const newPassword = newPasswordInput.value;
                const confirmPassword = confirmPasswordInput.value;

                if (!currentPassword || !newPassword || !confirmPassword) {
                    changePasswordMessage.textContent = 'All password fields are required.';
                    return;
                }

                if (newPassword !== confirmPassword) {
                    changePasswordMessage.textContent = 'New passwords do not match.';
                    return;
                }

                if (authToken) {
                    fetch('http://localhost/user_registration/api/profile_api.php?action=change_password', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'Authorization': `Bearer ${authToken}`
                        },
                        body: new URLSearchParams({
                            current_password: currentPassword,
                            new_password: newPassword,
                            confirm_password: confirmPassword
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        changePasswordMessage.textContent = data.message;
                        if (data.success) {
                            currentPasswordInput.value = '';
                            newPasswordInput.value = '';
                            confirmPasswordInput.value = '';
                        }
                    })
                    .catch(error => {
                        console.error('Error changing password:', error);
                        changePasswordMessage.textContent = 'Failed to change password.';
                    });
                } else {
                    changePasswordMessage.textContent = 'Authentication token not found. Please log in again.';
                }
            }

            // Event listeners
            if (uploadPhotoButton) {
                uploadPhotoButton.addEventListener('click', uploadProfilePhoto);
            }

            if (changePasswordForm) {
                changePasswordForm.addEventListener('submit', changePassword);
            }

            // Fetch profile information on page load
            fetchProfile();
        });
    </script>
</body>
</html>