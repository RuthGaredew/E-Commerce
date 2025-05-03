<?php
session_start();
error_log("HOME PAGE SESSION START: " . print_r($_SESSION, true));
$loggedIn = isset($_SESSION['product_service_logged_in']) && $_SESSION['product_service_logged_in'] === true && isset($_SESSION['product_service_email']);
$email = $loggedIn ? htmlspecialchars($_SESSION['product_service_email']) : '';
$userId = $loggedIn ? ($_SESSION['product_service_user_id'] ?? null) : null;
$authToken = $_SESSION['product_service_auth_token'] ?? '';

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

// Function to call the logout microservice API (KEEP IN BACK-END)
function callLogoutMicroservice() {
    $api_url = 'http://localhost/product_service/api/logout.php'; // Replace with your actual logout API endpoint
    $redirect_url = 'home_page.php';

    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['auth_key' => $_SESSION['product_service_auth_token'] ?? ''])); // Send auth token in body
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Bearer ' . ($_SESSION['product_service_auth_token'] ?? '')]); // Include Authorization header

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code >= 200 && $http_code < 300) {
        $_SESSION = [];
        session_destroy();
        header("Location: " . $redirect_url);
        exit();
    } else {
        error_log("Logout API Error: HTTP Code: " . $http_code . ", Response: " . $response);
        header("Location: " . $redirect_url . "?logout_api_error=1");
        exit();
    }
}

// Handle logout request
if (isset($_GET['logout'])) {
    callLogoutMicroservice();
}

// Fetch all categories
$categories = [];
$sql_categories = "SELECT category_id, category_name FROM categories";
$result_categories = $conn->query($sql_categories);
if ($result_categories->num_rows > 0) {
    while ($row = $result_categories->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Pagination settings
$productsPerPage = 5; // You can adjust this number
$currentPage = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($currentPage - 1) * $productsPerPage;

// Check if a category is selected
$selectedCategory = isset($_GET['category']) ? htmlspecialchars($_GET['category']) : null;

// Check if a search term is provided
$searchTerm = isset($_GET['search_term']) ? htmlspecialchars($_GET['search_term']) : '';

// Fetch products based on category and/or search term with pagination
$products = [];
$sql_products = "SELECT product_id, product_name, price, discount_price, image_path, category_id FROM products WHERE 1=1";
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

$sql_products .= " LIMIT ?, ?";
$params[] = $offset;
$params[] = $productsPerPage;
$types .= "ii";

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

// Get total number of products (for pagination calculation)
$totalProducts = 0;
$sql_total = "SELECT COUNT(*) AS total FROM products WHERE 1=1";
$params_total = [];
$types_total = "";

if ($selectedCategory) {
    $sql_total .= " AND category_id = ?";
    $params_total[] = $selectedCategory;
    $types_total .= "s";
}

if ($searchTerm) {
    $sql_total .= " AND product_name LIKE ?";
    $params_total[] = "%" . $searchTerm . "%";
    $types_total .= "s";
}

$stmt_total = $conn->prepare($sql_total);
if (!empty($params_total)) {
    $stmt_total->bind_param($types_total, ...$params_total);
}
$stmt_total->execute();
$result_total = $stmt_total->get_result();
if ($row_total = $result_total->fetch_assoc()) {
    $totalProducts = $row_total['total'];
}
$stmt_total->close();
$conn->close();

$totalPages = ceil($totalProducts / $productsPerPage);

// Define slider images array
$sliderImages = [
    ['image' => 'logo.jpg', 'alt' => 'Slider Image 1'],
    ['image' => 'logo.jpg', 'alt' => 'Slider Image 2'],
    ['image' => 'logo.jpg', 'alt' => 'Slider Image 3'],
    ['image' => 'logo.jpg', 'alt' => 'Slider Image 4'],
];

// Prepare data for the HTML
$data = [
    'loggedIn' => $loggedIn,
    'email' => $email,
    'userId' => $userId,
    'authToken' => $authToken,
    'categories' => $categories,
    'selectedCategory' => $selectedCategory,
    'searchTerm' => $searchTerm,
    'products' => $products,
    'sliderImages' => $sliderImages,
    'logoutApiUrl' => 'http://localhost/product_service/api/logout.php',
    'loginPageUrl' => 'http://localhost/user_registration/api/login.php',
    'homePageUrl' => 'http://localhost/product_service/api/home_page.php',
    'currentPage' => $currentPage,
    'totalPages' => $totalPages
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Service Home</title>
    <link rel="stylesheet" href="home_style__.css">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* Your inline styles can remain here or be moved to the CSS file */

    </style>
</head>
<body>
    <header>
        <h1>Home_page</h1>
        <div class="header-right">
            <div class="header-search-container">
                <div class="header-search">
                    <form action="home_page.php" method="GET">
                        <input type="text" id="search-term" name="search_term" placeholder="Search products..." value="<?php echo htmlspecialchars($data['searchTerm']); ?>">
                        <?php if ($data['selectedCategory']): ?>
                            <input type="hidden" name="category" value="<?php echo htmlspecialchars($data['selectedCategory']); ?>">
                        <?php endif; ?>
                        <button class="search-button" type="submit">Search</button>
                    </form>
                </div>
            </div>
            <div class="cart-icon-container">
                <a href="http://localhost/cart_service/api/cart.php">
                    <i class="fas fa-shopping-cart"></i>
                    <span id="cart-count" class="cart-count-badge">0</span>
                </a>
            </div>
            <nav>
                <ul>
                    <li><a href="home_page.php">Home</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <li id="login-link-li"><a href="#" id="show-login-form">Login</a></li>
                    <li><a href="http://localhost/user_registration/api/register.php">Sign Up</a></li>
                    <li id="logout-link-li" style="display: none;"><a href="#" id="logout-link">Logout</a></li>
                </ul>
            </nav>
            <div id="user-info">
                <?php echo $data['loggedIn'] ? '<span class="welcome">Welcome, ' . htmlspecialchars($data['email']) . '!</span>' : '<span></span>'; ?>
            </div>
        </div>

    </header>

    <nav class="home-category-menu">
        <ul>
            <li><a href="home_page.php<?php echo (isset($_GET['search_term']) ? '?search_term=' . htmlspecialchars($_GET['search_term']) : ''); ?>" class="<?php if (!$data['selectedCategory'] && !$data['searchTerm']) echo 'active'; ?>">All Products</a></li>
            <?php foreach ($data['categories'] as $category): ?>
                <li>
                    <a href="home_page.php?category=<?php echo htmlspecialchars($category['category_id']); ?><?php echo (isset($_GET['search_term']) ? '&search_term=' . htmlspecialchars($_GET['search_term']) : ''); ?>"
                       class="<?php if ($data['selectedCategory'] === $category['category_id']) echo 'active'; ?>">
                        <?php echo htmlspecialchars(ucfirst($category['category_name'])); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <div class="container">
        <main class="product-navigation">
            <div id="signup-section" style="display: none;">
                <h2>Sign Up</h2>
                <div id="signup-error-message" class="error" style="display:none;"></div>
                <form id="signupForm">
                    <div class="form-group">
                        <label for="signup-email">Email:</label>
                        <input type="email" id="signup-email" name="email" required>
                        <div id="signup-email-error" class="error-message" style="display:none;"></div>
                    </div>
                    <div class="form-group">
                        <label for="signup-password">Password:</label>
                        <input type="password" id="signup-password" name="password" required>
                        <div id="signup-password-error" class="error-message" style="display:none;"></div>
                    </div>
                    <button type="submit">Register</button>
                </form>
                <p class="login-link">Already have an account? <a href="#" id="show-login-form-from-signup">Login</a></p>
            </div>

            <div class="slider-container" style="max-width: 100%; margin: 20px auto; text-align: center;">
                <?php foreach ($data['sliderImages'] as $slide): ?>
                    <div class="slide" style="display: block;">
                        <a href="home_page.php" style="display: inline-block;">
                            <img src="<?php echo htmlspecialchars($slide['image']); ?>"
                                 alt="<?php echo htmlspecialchars($slide['alt']); ?>"
                                 style="width: 100px; height: 100px; border-radius: 70%; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);">
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="product-grid-container">
                <h2>Products</h2>
                <?php if (empty($data['products'])): ?>
                    <p>No products found <?php if ($data['selectedCategory']) echo 'in this category'; ?>
                        <?php if ($data['searchTerm']) echo 'matching your search term.'; ?></p>
                <?php else: ?>
                    <div class="product-grid">
                        <?php foreach ($data['products'] as $product): ?>
                            <div class="product-item" data-category-id="<?php echo htmlspecialchars($product['category_id']); ?>" data-product-name="<?php echo htmlspecialchars(strtolower($product['product_name'])); ?>">
                                <img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                                <h4><?php echo htmlspecialchars($product['product_name']); ?></h4>
                                <p class="price">$<?php echo number_format($product['price'], 2); ?>
                                    <?php if ($product['discount_price']): ?>
                                        <span class="discount-price">$<?php echo number_format($product['discount_price'], 2); ?></span>
                                    <?php endif; ?>
                                </p>
                                <div class="button-container">
                                    <button class="add-to-cart-btn" onclick="handleAddToCart(<?php echo htmlspecialchars($product['product_id']); ?>, 1)">
                                        <i class="fas fa-shopping-cart"></i> Add to Cart
                                    </button>
                                    <button class="buy-now-btn" onclick="handleBuyNow(<?php echo htmlspecialchars($product['product_id']); ?>, 1)">Buy Now</button>
                                </div>
                                <a href="product_details.php?id=<?php echo htmlspecialchars($product['product_id']); ?>">View Details</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($data['totalPages'] > 1): ?>
                <div class="pagination">
                    <?php if ($data['currentPage'] > 1): ?>
                        <a href="home_page.php?page=<?php echo $data['currentPage'] - 1; ?><?php echo (isset($_GET['category']) ? '&category=' . htmlspecialchars($_GET['category']) : ''); ?><?php echo (isset($_GET['search_term']) ? '&search_term=' . htmlspecialchars($_GET['search_term']) : ''); ?>">Previous</a>
                    <?php else: ?>
                        <span class="disabled">Previous</span>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $data['totalPages']; $i++): ?>
                        <a href="home_page.php?page=<?php echo $i; ?><?php echo (isset($_GET['category']) ? '&category=' . htmlspecialchars($_GET['category']) : ''); ?><?php echo (isset($_GET['search_term']) ? '&search_term=' . htmlspecialchars($_GET['search_term']) : ''); ?>" class="<?php if ($i === $data['currentPage']) echo 'current'; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>

                    <?php if ($data['currentPage'] < $data['totalPages']): ?>
                        <a href="home_page.php?page=<?php echo $data['currentPage'] + 1; ?><?php echo (isset($_GET['category']) ? '&category=' . htmlspecialchars($_GET['category']) : ''); ?><?php echo (isset($_GET['search_term']) ? '&search_term=' . htmlspecialchars($_GET['search_term']) : ''); ?>">Next</a>
                    <?php else: ?>
                        <span class="disabled">Next</span>
                    <?php endif; ?>
                </div>
             <?php endif; ?>

            </main>
        </div>

        <script>
            const pageData = <?php echo json_encode($data); ?>;

            // **IMPLEMENTATION OF ALL JAVASCRIPT FUNCTIONS**
            async function handleAddToCart(productId, quantity = 1) {
                // ... (Your existing handleAddToCart function)
                console.log('Add to cart clicked for product ID:', productId);
            }
        </script>
        <script src="buy_now.js"></script>
        <script type="text/javascript" src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script>
        <script src="home_scrpt_withoutbuy.js"></script>

        <?php
        include 'footer.php';
        ?>
    </body>
    </html>