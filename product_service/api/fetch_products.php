<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "product_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$category = isset($_GET['category']) ? $_GET['category'] : null;
$whereClause = "";
$paramType = "";
$paramValue = "";

if ($category) {
    $whereClause = "WHERE c.category_name = ?";
    $paramType = "s";
    $paramValue = $category;
}

$sql = "SELECT p.product_name, p.price, p.discount_price, p.stock_quantity, p.image_path
        FROM products p
        JOIN categories c ON p.category_id = c.category_id
        " . $whereClause;

$stmt = $conn->prepare($sql);

if ($paramType && $paramValue !== "") {
    $stmt->bind_param($paramType, $paramValue);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo '<div class="product-list-container">';
    while ($row = $result->fetch_assoc()) {
        echo '<div class="product-card">';
        if (!empty($row["image_path"]) && file_exists($row["image_path"])) {
            echo '<img src="' . $row["image_path"] . '" alt="' . htmlspecialchars($row["product_name"]) . '" class="product-image">';
        } else {
            echo '<img src="placeholder.png" alt="No Image" class="product-image">';
        }
        echo '<h3 class="product-name">' . htmlspecialchars($row["product_name"]) . '</h3>';
        if ($row["discount_price"] !== null && $row["discount_price"] < $row["price"]) {
            echo '<p class="product-price"><span class="discounted-price">$' . htmlspecialchars($row["price"]) . '</span> $' . htmlspecialchars($row["discount_price"]) . '</p>';
        } else {
            echo '<p class="product-price">$' . htmlspecialchars($row["price"]) . '</p>';
        }
        echo '<p>Quantity: ' . htmlspecialchars($row["stock_quantity"]) . '</p>';
        echo '</div>';
    }
    echo '</div>';
} else {
    echo "<p>No products found" . ($category ? " in the " . htmlspecialchars(ucfirst($category)) . " category." : ".") . "</p>";
}

$stmt->close();
$conn->close();
?>