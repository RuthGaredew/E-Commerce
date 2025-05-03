<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['product_service_logged_in']) || $_SESSION['product_service_logged_in'] !== true) {
    header("Location: http://localhost/user_registration/api/login.php");
    exit;
}

$email = $_SESSION['product_service_email'];
$userId = $_SESSION['product_service_user_id'];
$authToken = $_SESSION['product_service_auth_token'];

// API endpoint URL for getting cart items
$api_url = 'http://localhost/cart_service/api/get_cart.php';

// Items per page
$items_per_page = 5; // Adjust as needed

// Get current page number
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page); // Ensure page is at least 1

// Calculate offset
$offset = ($page - 1) * $items_per_page;

// Modify API call to include pagination parameters (if your API supports it)
// *** IMPORTANT:  This assumes your API can handle 'limit' and 'offset' parameters ***
$api_url_with_pagination = $api_url . '?limit=' . $items_per_page . '&offset=' . $offset;

// Initialize cURL session for getting cart items
$ch = curl_init($api_url_with_pagination);

// Set cURL options for getting cart items
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $authToken,
    'Content-Type: application/json'
]);

// Execute the cURL request for getting cart items
$response = curl_exec($ch);

// Check for cURL errors for getting cart items
if (curl_errno($ch)) {
    echo 'cURL error: ' . curl_error($ch);
    exit;
}

// Get HTTP status code for getting cart items
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Close cURL session for getting cart items
curl_close($ch);

// Check HTTP status code for getting cart items
if ($http_code !== 200) {
    echo "Error: API request failed with status code " . $http_code . "<br>";
    echo "Response: " . $response;
    exit;
}

// Decode the JSON response for getting cart items
$cart_items = json_decode($response, true);

// Check if decoding was successful for getting cart items
if ($cart_items === null && json_last_error() !== JSON_ERROR_NONE) {
    echo 'Error decoding JSON: ' . json_last_error_msg();
    exit;
}

// *** NEW:  Get total number of items (you'll need to modify your API to return this) ***
// *** This is just a placeholder - you'll need to implement this in your API ***
$total_items = 10; // Replace with the actual total number of items from your API

// Calculate total number of pages
$total_pages = ceil($total_items / $items_per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="cart_style_updated.css">
    <style>
        .delete-button {
            background-color: #f44336; /* Red */
            color: white;
            padding: 5px 10px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            border: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <h1>Shopping Cart</h1>

    <?php if (empty($cart_items)): ?>
        <p>Your cart is empty.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Image</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                    <th>Actions</th> <!-- New column for delete button -->
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart_items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                        <td>
                            <?php
                            $image_path = htmlspecialchars($item['image_path']);
                            echo "<img src=\"$image_path\" alt=\"" . htmlspecialchars($item['product_name']) . "\" width=\"50\">";
                            echo "<br>";
                            echo "Image Path: " . $image_path; // *** DEBUG: Print the image path ***
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                        <td>$<?php echo number_format($item['price'], 2); ?></td>
                        <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                        <td>
                            <!-- Delete Button -->
                            <button class="delete-button" onclick="deleteCartItem(<?php echo htmlspecialchars($item['product_id']); ?>)">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- Pagination Links -->
    <div class="pagination">
        <?php if ($total_pages > 1): ?>
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>">Previous</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>" <?php if ($i == $page) echo 'class="active"'; ?>><?php echo $i; ?></a>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page + 1; ?>">Next</a>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <a href="http://localhost/product_service/api/home_page.php">Continue Shopping</a>

    <script>
        function deleteCartItem(productId) {
            if (confirm("Are you sure you want to delete this item?")) {
                // *** IMPORTANT: Replace with the correct URL for your delete API ***
                const url = `http://localhost/cart_service/api/delete_cart_item.php?product_id=${productId}`;

                fetch(url, {
                    method: 'DELETE', // Or 'POST' depending on your API
                    headers: {
                        'Authorization': 'Bearer <?php echo htmlspecialchars($authToken); ?>',
                        'Content-Type': 'application/json'
                    },
                })
                .then(response => {
                    if (response.ok) {
                        alert("Item deleted successfully!");
                        location.reload(); // Refresh the page
                    } else {
                        console.error('Error deleting item:', response.status);
                        alert("Error deleting item.");
                    }
                })
                .catch(error => {
                    console.error('Error deleting item:', error);
                    alert("An error occurred while deleting the item.");
                });
            }
        }
    </script>
</body>
</html>