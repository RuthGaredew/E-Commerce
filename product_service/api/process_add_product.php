<?php
session_start(); // Make sure session_start() is at the very top if you use sessions

// Database connection details
$servername = "localhost";
$username = "root"; // Replace with your database username
$password = ""; // Replace with your database password
$dbname = "product_db"; // Replace with your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category_id = $_POST["category_id"];
    $product_name = $_POST["product_name"];
    $price = $_POST["price"];
    $discount_price = $_POST["discount_price"] ?? null;
    $stock_quantity = $_POST["stock_quantity"];
    $image = $_FILES["image"];

    // Basic validation (no change here)
    if (empty($category_id) || empty($product_name) || empty($price) || !is_numeric($price) || !is_numeric($stock_quantity)) {
        echo "<p style='color: red;'>Error: Please fill in all required fields (Category, Product Name, Price, Stock Quantity) correctly.</p>";
        echo '<p><a href="add_product.php">Go back to add product form</a></p>';
        exit();
    }

    $image_path = "";
    // Handle image upload (no change here)
    if ($image["error"] == 0) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($image["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ["jpg", "jpeg", "png", "gif"];
        if (in_array($imageFileType, $allowed_types)) {
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
            if (move_uploaded_file($image["tmp_name"], $target_file)) {
                $image_path = $target_file;
            } else {
                echo "<p style='color: red;'>Error uploading image.</p>";
                echo '<p><a href="add_product.php">Go back to add product form</a></p>';
                exit();
            }
        } else {
            echo "<p style='color: red;'>Invalid image file type.</p>";
            echo '<p><a href="add_product.php">Go back to add product form</a></p>';
            exit();
        }
    }

    // Prepare and bind the SQL statement (no change here)
    $sql = "INSERT INTO products (category_id, product_name, price, discount_price, stock_quantity, image_path)
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isddis", $category_id, $product_name, $price, $discount_price, $stock_quantity, $image_path);

    if ($stmt->execute()) {
        // Redirect to the admin product listing page after successful addition
        header("Location: add_product.php?add_success=1"); // Or whatever your admin product page is
        exit();
    } else {
        echo "<p style='color: red;'>Error adding product: " . $stmt->error . "</p>";
        echo '<p><a href="add_product.php">Go back to add product form</a></p>';
    }

    $stmt->close();
    $conn->close();

} else {
    // If the page is accessed directly without submitting the form (no change here)
    header("Location: add_product.php");
    exit();
}
?>