document.addEventListener("DOMContentLoaded", function () {
    const showLoginFormLink = document.getElementById("show-login-form");
    const signupLinkLi = document.querySelector('nav ul li:nth-child(4)');
    const loginLinkLi = document.getElementById("login-link-li");
    const logoutLinkLi = document.getElementById("logout-link-li");
    const logoutLink = document.getElementById("logout-link");
    const signupSection = document.getElementById("signup-section");
    const showLoginFormFromSignupLink = document.getElementById("show-login-form-from-signup");
    const categoryFilter = document.getElementById('category-filter');
    const searchTermInput = document.getElementById('search-term');
    const cartItemCountElement = document.getElementById('cart-item-count'); // Assuming you have an element to display the cart count

    const {
        loggedIn,
        email,
        userId,
        authToken,
        categories,
        selectedCategory,
        searchTerm,
        products,
        sliderImages,
        logoutApiUrl,
        loginPageUrl,
        homePageUrl
    } = pageData;

    function callLogoutMicroservice() {
        if (!authToken) {
            console.error("Authentication token not found for logout.");
            window.location.href = loginPageUrl;
            return;
        }

        fetch(logoutApiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${authToken}`
            },
            body: JSON.stringify({ auth_key: authToken })
        })
        .then(response => {
            if (!response.ok) {
                console.error('Logout API failed:', response.status, response.statusText);
                return response.json().then(errorData => {
                    console.error('Logout API error details:', errorData);
                    window.location.href = loginPageUrl + '?logout_failed=1';
                    throw new Error('Logout API request failed');
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Logout API successful:', data);
            fetch("logout.php", { method: "POST" })
            .then(response => {
                if (response.ok) {
                    window.location.href = homePageUrl;
                } else {
                    console.error('Local logout.php call failed:', response.status);
                    window.location.href = homePageUrl + '?local_logout_failed=1';
                }
            })
            .catch(error => {
                console.error('Error during local logout:', error);
                window.location.href = homePageUrl + '?local_logout_error=1';
            });
        })
        .catch(error => {
            console.error('Error during API logout:', error);
        });
    }

    if (logoutLink) {
        logoutLink.addEventListener("click", function (e) {
            e.preventDefault();
            callLogoutMicroservice();
        });
    }

    if (loggedIn) {
        if (loginLinkLi) loginLinkLi.style.display = "none";
        if (logoutLinkLi) logoutLinkLi.style.display = "block";
        if (signupLinkLi) signupLinkLi.style.display = "none";
        if (signupSection) signupSection.style.display = "none";
        fetchCartItemCount(); // Fetch cart count on login
    } else {
        if (loginLinkLi) loginLinkLi.style.display = "block";
        if (logoutLinkLi) logoutLinkLi.style.display = "none";
        if (signupLinkLi) signupLinkLi.style.display = "block";
        if (signupSection) signupSection.style.display = "none";

        if (showLoginFormLink) {
            showLoginFormLink.addEventListener("click", function (e) {
                e.preventDefault();
                window.location.href = "http://localhost/user_registration/api/login.php";
            });
        }

        const urlParams = new URLSearchParams(window.location.search);
        const loginSuccess = urlParams.get("login_success");
        const userIdFromURL = urlParams.get("user_id");
        const authKeyFromURL = urlParams.get("auth_key");
        const emailFromURL = urlParams.get("email");
        if (loginSuccess === "true" && userIdFromURL && authKeyFromURL && emailFromURL) {
            fetch("set_session.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ logged_in: true, email: emailFromURL, user_id: userIdFromURL, token: authKeyFromURL })
            })
            .then(response => response.json())
            .then(sessionData => {
                if (sessionData.success) {
                    window.location.replace(window.location.pathname);
                } else {
                    alert("Session could not be set. Please log in again.");
                }
            })
            .catch(error => {
                console.error("Login session error:", error);
                alert("Login failed. Please try again.");
            });
        }
    }

    $('.slider-container').slick({
        autoplay: true,
        autoplaySpeed: 3000,
        dots: true,
        arrows: true,
        infinite: true,
        speed: 500,
        slidesToShow: 1,
        slidesToScroll: 1,
        centerMode: true,
        centerPadding: '60px'
    });

    // Helper Functions
    function displayMessage(message) {
        alert(message);
    }

    function getLoggedInUserId() {
        return userId;
    }

    function redirectToLoginPage() {
        window.location.href = "http://localhost/user_registration/api/login.php";
    }

    function updateCartDisplay(cartData) {
        const cartItemCount = cartData.items ? cartData.items.reduce((sum, item) => sum + item.quantity, 0) : 0;
        if (cartItemCountElement) {
            cartItemCountElement.textContent = cartItemCount;
        }
        console.log("Cart Data:", cartData);
    }

    function updateCartIcon(cartData) {
        const cartItemCount = cartData.items ? cartData.items.reduce((sum, item) => sum + item.quantity, 0) : 0;
        const cartIcon = document.getElementById('cart-icon'); // Assuming you have an element with this ID
        if (cartIcon) {
            cartIcon.textContent = `Cart (${cartItemCount})`;
        }
    }

    async function fetchCartItemCount() {
        if (loggedIn && userId && authToken) {
            try {
                const cartServiceUrl = 'http://localhost/cart-service/cart/items/count'; // Assuming an endpoint to get the count
                const response = await fetch(cartServiceUrl, {
                    method: 'GET',
                    headers: {
                        'Authorization': `Bearer ${authToken}`
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    const itemCount = data.count || 0; // Adjust based on the actual response
                    if (cartItemCountElement) {
                        cartItemCountElement.textContent = itemCount;
                    }
                } else {
                    console.error('Failed to fetch cart item count:', response.status);
                }
            } catch (error) {
                console.error('Error fetching cart item count:', error);
            }
        }
    }

    // home_script.js

async function handleAddToCart(productId, quantity = 1) {
    const cartServiceUrl = 'http://localhost/cart-service/cart/items'; // Replace with the actual URL of your cart-service API

    try {
        const response = await fetch(cartServiceUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
                // Removed: 'Authorization': `Bearer ${authToken}`
            },
            body: JSON.stringify({ productId: productId, quantity: quantity })
        });

        if (response.ok) {
            const data = await response.json();
            alert(data.message || 'Product added to cart!');
            // Optionally update the displayed cart item count
            updateCartItemCount();
        } else {
            const error = await response.json();
            alert(error.error || 'Failed to add product to cart.');
        }
    } catch (error) {
        console.error('Error adding to cart:', error);
        alert('An error occurred while adding to cart.');
    }
}

async function handleAddToCart(productId, quantity = 1) {
    const cartServiceUrl = 'http://localhost/api/add_to_cart.php'; // Adjust URL

    try {
        const response = await fetch(cartServiceUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${authToken}`
            },
            body: JSON.stringify({ product_id: productId, quantity: quantity })
        });

        if (response.ok) {
            const data = await response.json();
            alert(data.message || 'Product added to cart!');

            // Store JWT in local storage (VERY IMPORTANT)
            localStorage.setItem('product_service_auth_token', authToken);

            // Redirect to the cart service frontend
            window.location.href = 'http://localhost/cart_service/api/cart.html'; // Adjust URL
        } else {
            const error = await response.json();
            alert(error.error || 'Failed to add product to cart.');
        }
    } catch (error) {
        console.error('Error adding to cart:', error);
        alert('An error occurred while adding to cart.');
    }
}
    async function handleBuyNow(productId, quantity = 1) {
        try {
            const currentUserId = getLoggedInUserId();
            if (!currentUserId) {
                redirectToLoginPage();
                return;
            }

            const orderServiceUrl = '/orders.php'; // Adjust if your order service is also a microservice
            const response = await fetch(orderServiceUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    // You might need authorization for the order service as well
                },
                body: JSON.stringify({ productId, quantity, userId: currentUserId })
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(`HTTP error! status: ${response.status}, message: ${errorData.message || 'Failed to create order'}`);
            }

            const orderData = await response.json();
            console.log('Order created:', orderData);
            displayMessage(`Your order has been placed! Order ID: ${orderData.orderId}`);
            window.location.href = `/order-confirmation.html?orderId=${orderData.orderId}`;
            const button = document.querySelector(`.buy-now-btn[onclick*="handleBuyNow(${productId}"]`);
            if (button) {
                button.classList.add('pulsing');
                setTimeout(() => {
                    button.classList.remove('pulsing');
                }, 2000);
            }
        } catch (error) {
            console.error('Error creating order:', error);
            displayMessage('Failed to create your order.');
        }
    }
});