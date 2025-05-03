
    async function handleBuyNow(productId, quantity = 1) {
        alert("Buy Now clicked! Product ID: " + productId);
        try {
            const response = await fetch("../../order_now/api/buy_now.php", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ product_id: productId, quantity: quantity })
            });
    
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
    
            const data = await response.json();
            if (data.success) {
                if (data.payment_success) {
                    alert(`Your order has been placed and Telebirr payment was successful! Order ID: ${data.orderId}, Transaction ID: ${data.telebirr_transaction_id}`);
                    window.location.href = `../../order_now/order_confirmation.php?orderId=${data.orderId}`; // Redirect to order confirmation page
                } else {
                    alert(data.error || 'Telebirr payment failed.');
                }
            } else {
                alert(data.error || 'Failed to create order.');
            }
    
        } catch (error) {
            console.error('Error creating order:', error);
            alert('An error occurred while creating your order.');
        }
    }

