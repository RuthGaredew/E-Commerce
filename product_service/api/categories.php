<?php
// product_service/api/categories.php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "product_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

$sql_categories = "SELECT category_id, category_name FROM categories";
$result_categories = $conn->query($sql_categories);
$categories = [];

if ($result_categories->num_rows > 0) {
    while ($row = $result_categories->fetch_assoc()) {
        $categories[] = $row;
    }
}

$conn->close();

echo json_encode($categories);
?>
