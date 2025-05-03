<?php
// product_service/api/products.php

header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "product_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

$whereClauses = [];
$sql = "SELECT product_id, product_name, price, discount_price, image_path, category_id FROM products";

// Handle featured products request
if (isset($_GET['featured']) && $_GET['featured'] === 'true') {
    $whereClauses[] = "is_featured = 1"; // Assuming you have an 'is_featured' column
}

// Handle category filter
if (isset($_GET['category_id']) && !empty($_GET['category_id'])) {
    $categoryId = $conn->real_escape_string($_GET['category_id']);
    $whereClauses[] = "category_id = '" . $categoryId . "'";
}

// Handle search term
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchTerm = $conn->real_escape_string($_GET['search']);
    $whereClauses[] = "product_name LIKE '%" . $searchTerm . "%'";
}

if (!empty($whereClauses)) {
    $sql .= " WHERE " . implode(" AND ", $whereClauses);
}

$result = $conn->query($sql);
$products = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

$conn->close();

echo json_encode($products);
?>
