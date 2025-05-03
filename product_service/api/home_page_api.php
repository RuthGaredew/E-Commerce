<?php
// home_page_api.php

require __DIR__ . '/../../../vendor/autoload.php'; // Composer autoloader
require __DIR__ . '/../../../product_service/utils/Database.php';
require __DIR__ . '/../../../product_service/utils/ApiHelper.php';

use ProductService\Utils\Database;
use ProductService\Utils\ApiHelper;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

// --- Configuration for User Service ---
$userServiceBaseUri = 'http://localhost/user_registration/api/'; // Base URL of User Registration service

// --- Function to fetch user information from User Registration Service ---
function fetchUserDataFromUserService() {
    global $userServiceBaseUri;
    $client = new Client();

    session_start();
    if (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
        $userProfileEndpoint = 'profile.php'; // Specific endpoint for user profile

        try {
            $response = $client->request('GET', $userServiceBaseUri . $userProfileEndpoint, [
                'query' => ['user_id' => $userId],
                'timeout' => 5,
                // Add Authorization header if needed (if your profile endpoint requires authentication)
            ]);

            $statusCode = $response->getStatusCode();
            $body = $response->getBody();
            $userData = json_decode($body, true);

            if ($statusCode === 200 && isset($userData['user_id']) && isset($userData['email'])) {
                return ['logged_in' => true, 'email' => $userData['email']]; // Return only essential info
            } else {
                error_log("Failed to fetch user data. Status: " . $statusCode . ", Body: " . $body);
                return ['logged_in' => false, 'email' => null, 'error' => 'Failed to fetch user data from User Service'];
            }

        } catch (GuzzleException $e) {
            error_log("Error communicating with User Service: " . $e->getMessage());
            return ['logged_in' => false, 'email' => null, 'error' => 'Error communicating with User Service'];
        }
    } else {
        return ['logged_in' => false, 'email' => null];
    }
}

// --- API Logic ---
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'user_info':
        // --- Fetch User Information ---
        $userData = fetchUserDataFromUserService();
        ApiHelper::respondWithJson($userData);
        break;

    default:
        ApiHelper::respondWithJson(['error' => 'Invalid action'], 400);
        break;
}
?>