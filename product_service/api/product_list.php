<?php
session_start();

// Function to call the logout microservice API
function callLogoutMicroservice() {
    $api_url = 'YOUR_LOGOUT_MICROSERVICE_API_ENDPOINT';
    $redirect_url = 'home_page.php'; // Redirect to home page after API logout

    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true); // Adjust the HTTP method as needed
    // Example: Setting a Content-Type header if your API expects JSON
    // curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    // Example: Sending data in the request body as JSON
    // $post_data = json_encode(['session_id' => session_id()]); // Example data
    // curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code >= 200 && $http_code < 300) {
        // API logout successful, now clear local session
        $_SESSION = [];
        session_destroy();
        header("Location: " . $redirect_url);
        exit();
    } else {
        // Handle API error (log, display message, or redirect with error)
        error_log("Logout API Error: HTTP Code: " . $http_code . ", Response: " . $response);
        header("Location: " . $redirect_url . "?logout_api_error=1");
        exit();
    }
}

// Handle logout request
if (isset($_GET['logout'])) {
    callLogoutMicroservice();
}

$loggedIn = isset($_SESSION['product_service_logged_in']) && $_SESSION['product_service_logged_in'] === true && isset($_SESSION['product_service_email']);
$email = $loggedIn ? htmlspecialchars($_SESSION['product_service_email']) : '';

// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "product_db";

// Establish database connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all categories (for the top menu)
$categories = [];
$sql_categories = "SELECT category_id, category_name FROM categories";
$result_categories = $conn->query($sql_categories);
if ($result_categories->num_rows > 0) {
    while ($row = $result_categories->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Fetch all categories (for filtering in the header)
$allCategories = [];
$sql_all_categories = "SELECT category_id, category_name FROM categories";
$result_all_categories = $conn->query($sql_all_categories);
if ($result_all_categories->num_rows > 0) {
    while ($row = $result_all_categories->fetch_assoc()) {
        $allCategories[] = $row;
    }
}

// Check if a category is selected
$selectedCategory = isset($_GET['category']) ? htmlspecialchars($_GET['category']) : null;

// Check if a search term is provided
$searchTerm = isset($_GET['search_term']) ? htmlspecialchars($_GET['search_term']) : '';

// Fetch products based on selected category and/or search term
$products = [];
$sql_products = "SELECT product_name, price, discount_price, image_path, category_id FROM products WHERE 1=1";
$params = [];
$types = "";

if ($selectedCategory) {
    $sql_products .= " AND category_id = ?";
    $params[] = $selectedCategory;
    $types .= "s";
}

if ($searchTerm) {
    $sql_products .= " AND product_name LIKE ?";
    $params[] = "%" . $searchTerm . "%";
    $types .= "s";
}

$stmt = $conn->prepare($sql_products);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result_products = $stmt->get_result();
while ($row = $result_products->fetch_assoc()) {
    $products[] = $row;
}
$stmt->close();

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List - <?php echo ucfirst($selectedCategory ?? 'All Products'); ?></title>
    <link rel="stylesheet" href="product_style.css">

</head>
<body>
    <div class="wrapper">
    <header>
    <div class="header-left">
        <h1>Product_list</h1>
    </div>
    <div class="header-right">
        <div class="header-search">
            <form action="product_list.php" method="GET">
                <input type="text" name="search_term" placeholder="Search products..." value="<?php echo htmlspecialchars($searchTerm); ?>">
                <?php if ($selectedCategory): ?>
                    <input type="hidden" name="category" value="<?php echo htmlspecialchars($selectedCategory); ?>">
                <?php endif; ?>
                <button type="submit">Search</button>
            </form>
        </div>
        <nav class="header-right-nav">
            <ul class="main-menu">
                <li><a href="home_page.php"> Back_to home</a></li>
            </ul>
            <div class="menu-icon" onclick="toggleMenu()">☰</div> 
        </nav>
    </div>
</header>
        <ul class="category-menu">
            <li><a href="product_list.php" class="<?php if (!$selectedCategory && !$searchTerm) echo 'active'; ?>">All Products</a></li>
            <?php foreach ($categories as $category): ?>
                <li>
                    <a href="product_list.php?category=<?php echo htmlspecialchars($category['category_id']); ?>"
                        class="<?php if ($selectedCategory === $category['category_id']) echo 'active'; ?>">
                        <?php echo htmlspecialchars(ucfirst($category['category_name'])); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="container">
            <div class="product-grid-container">
                <h2>Products</h2>

                <div class="filter-search-container" style="display: none;">
                    <div class="search-container">
                        <form action="product_list.php" method="GET">
                            <input type="text" name="search_term" placeholder="Search products..." value="<?php echo htmlspecialchars($searchTerm); ?>">
                            <?php if ($selectedCategory): ?>
                                <input type="hidden" name="category" value="<?php echo htmlspecialchars($selectedCategory); ?>">
                            <?php endif; ?>
                            <button type="submit">Search</button>
                        </form>
                    </div>
                </div>

                <?php if (empty($products)): ?>
                    <p>No products found <?php if ($selectedCategory) echo 'in this category'; ?>
                        <?php if ($searchTerm) echo 'matching your search term.'; ?></p>
                <?php else: ?>
                    <div class="product-grid">
                        <?php foreach ($products as $product): ?>
                            <div class="product-item" data-category-id="<?php echo htmlspecialchars($product['category_id']); ?>" data-product-name="<?php echo htmlspecialchars(strtolower($product['product_name'])); ?>">
                                <img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                                <h4><?php echo htmlspecialchars($product['product_name']); ?></h4>
                                <p class="price">$<?php echo number_format($product['price'], 2); ?>
                                    <?php if ($product['discount_price']): ?>
                                        <span class="discount-price">$<?php echo number_format($product['discount_price'], 2); ?></span>
                                    <?php endif; ?>
                                </p>
                                <a href="#">View Details</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php include 'footer.php'; ?>
    </div>

    <script>
        function toggleMenu() {
            const categoryMenu = document.querySelector('.category-menu');
            categoryMenu.classList.toggle('open');
        }
    </script>
</body>
</html>