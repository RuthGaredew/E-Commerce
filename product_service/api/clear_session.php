<?php
// clear_session.php (Product Service)

session_start();
header('Content-Type: application/json');

// Destroy all session data
$_SESSION = [];
session_unset();
session_destroy();

echo json_encode(['success' => true, 'message' => 'Session cleared successfully.']);
?>
