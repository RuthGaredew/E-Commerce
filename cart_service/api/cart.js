// cart.js

async function fetchCartItems() {
    const cartItemsUrl = 'http://localhost/cart_service/api/get_cart.php';

    try {
        const response = await fetch(cartItemsUrl, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${pageData.authToken}`
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        const cart = data.cart || []; // Extract the 'cart' array, or return an empty array
        updateCartCount(cart.length); // Update the cart count
        return cart;

    } catch (error) {
        console.error('Error fetching cart items:', error);
        // Optionally display an error message to the user
        updateCartCount(0); // Set cart count to 0 on error
        return []; // Return an empty array in case of error
    }
}

function updateCartCount(count) {
    const cartCountElement = document.getElementById('cart-count');
    if (cartCountElement) {
        cartCountElement.textContent = count;
    } else {
        console.error('Cart count element not found.');
    }
}

function displayCartItems(cartItems) {
    const cartContainer = document.getElementById('cart-items-container');

    if (!cartContainer) {
        console.error('Cart container element not found.');
        return;
    }

    cartContainer.innerHTML = '';

    if (cartItems.length === 0) {
        cartContainer.textContent = 'Your cart is empty.';
        return;
    }

    const cartList = document.createElement('ul');

    const productDetailsPromises = cartItems.map(item => fetchProductDetails(item.product_id));
    Promise.all(productDetailsPromises).then(productDetails => {
        cartItems.forEach((item, index) => {
            const product = productDetails[index];

            if (product) {
                const listItem = document.createElement('li');
                listItem.innerHTML = `
                    <img src="${product.image_path}" alt="${product.product_name}" width="50">
                    <span>${product.product_name}</span>
                    <span>
                        Quantity:
                        <input type="number" value="${item.quantity}" min="1" id="quantity-${item.product_id}">
                        <button onclick="updateCartItemQuantity(${item.product_id}, document.getElementById('quantity-${item.product_id}').value)">Update</button>
                    </span>
                    <span>Price: $${product.price}</span>
                    <span>Subtotal: $${(item.quantity * product.price).toFixed(2)}</span>
                    <button onclick="removeFromCart(${item.product_id})">Remove</button>
                `;
                cartList.appendChild(listItem);
            } else {
                console.warn(`Could not retrieve details for product ID ${item.product_id}`);
            }
        });

        cartContainer.appendChild(cartList);

        let totalPrice = 0;
        for (let i = 0; i < cartItems.length; i++) {
            if (productDetails[i]) {
                totalPrice += cartItems[i].quantity * productDetails[i].price;
            }
        }
        const totalElement = document.createElement('p');
        totalElement.textContent = `Total: $${totalPrice.toFixed(2)}`;
        cartContainer.appendChild(totalElement);

        // Add the "Clear Cart" button
        const clearCartButton = document.createElement('button');
        clearCartButton.textContent = 'Clear Cart';
        clearCartButton.onclick = clearCart;
        cartContainer.appendChild(clearCartButton);
    });
}

async function removeFromCart(productId) {
    const removeFromCartUrl = `http://localhost/cart_service/api/delete_cart_item.php?product_id=${productId}`; // DELETE URL

    try {
        const response = await fetch(removeFromCartUrl, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${pageData.authToken}`
            },
            // No body needed for DELETE with query parameters
        });

        if (response.ok) {
            // Refresh the cart items
            const cartItems = await fetchCartItems();
            displayCartItems(cartItems);
        } else {
            const errorData = await response.json();
            console.error('Error removing from cart:', errorData);
            alert('Failed to remove item from cart.');
        }
    } catch (error) {
        console.error('Error removing from cart:', error);
        alert('An error occurred while removing from cart.');
    }
}

async function updateCartItemQuantity(productId, quantity) {
    const updateCartItemUrl = `http://localhost/cart_service/api/update_cart_item.php?product_id=${productId}`;

    try {
        const response = await fetch(updateCartItemUrl, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${pageData.authToken}`
            },
            body: JSON.stringify({ quantity: quantity })
        });

        if (response.ok) {
            // Refresh the cart items
            const cartItems = await fetchCartItems();
            displayCartItems(cartItems);
        } else {
            const errorData = await response.json();
            console.error('Error updating cart item:', errorData);
            alert('Failed to update cart item.');
        }
    } catch (error) {
        console.error('Error updating cart item:', error);
        alert('An error occurred while updating cart item.');
    }
}

async function clearCart() {
    const clearCartUrl = 'http://localhost/cart_service/api/clear_cart.php';

    try {
        const response = await fetch(clearCartUrl, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${pageData.authToken}`
            }
        });

        if (response.ok) {
            // Refresh the cart items
            const cartItems = await fetchCartItems();
            displayCartItems(cartItems);
            alert('Cart cleared successfully!'); // Optional success message
        } else {
            const errorData = await response.json();
            console.error('Error clearing cart:', errorData);
            alert('Failed to clear cart.');
        }
    } catch (error) {
        console.error('Error clearing cart:', error);
        alert('An error occurred while clearing cart.');
    }
}

async function initializeCart() {
    // Fetch and display cart items
    const cartItems = await fetchCartItems();
    displayCartItems(cartItems);
}

document.addEventListener('DOMContentLoaded', async function() {
    await initializeCart(); // Call initializeCart instead
});