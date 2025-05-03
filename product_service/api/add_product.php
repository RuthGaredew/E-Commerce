<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Product</title>
    <link rel="stylesheet" href="add_product_style.css">
</head>
<body>
    <div class="container">
        <h2>Add New Product</h2>

        <?php
        if (isset($_GET['success']) && $_GET['success'] == 1) {
            echo "<p style='color: green; font-weight: bold;'>Product added successfully!</p>";
        }
        ?>

        <form action="process_add_product.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="category_id">Category:</label>
                <select id="category_id" name="category_id" required>
                    <option value="">-- Select Category --</option>
                    <option value="1">Electronics</option>
                    <option value="2">Clothing</option>
                    <option value="3">Accessories</option>
                    <option value="4">Beauty & Personal Care</option>
                    <option value="5">Home & Garden</option>
                    <option value="6">Books & Media</option>
                    <option value="7">Health & Wellness</option>
                    <option value="8">Toys & Games</option>
                </select>
            </div>
            <div class="form-group">
                <label for="product_name">Product Name:</label>
                <input type="text" id="product_name" name="product_name" required>
            </div>
            <div class="form-group">
                <label for="price">Price:</label>
                <input type="number" id="price" name="price" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="discount_price">Discount Price (Optional):</label>
                <input type="number" id="discount_price" name="discount_price" step="0.01">
            </div>
            <div class="form-group">
                <label for="stock_quantity">Stock Quantity:</label>
                <input type="number" id="stock_quantity" name="stock_quantity" value="0" required>
            </div>
            <div class="form-group">
                <label for="image">Product Image:</label>
                <input type="file" id="image" name="image">
                <small>Optional</small>
            </div>
            <button type="submit">Add Product</button>
        </form>
    </div>
</body>
</html>