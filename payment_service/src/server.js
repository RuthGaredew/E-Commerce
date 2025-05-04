const express = require('express');
const axios = require('axios');
const bodyParser = require('body-parser');
const cors = require('cors');
require('dotenv').config();

const app = express(); // Initialize the Express app
const port = process.env.PORT || 3000;

// Middleware
app.use(cors());
app.use(bodyParser.json());

// PayPal API credentials
const paypalClientID = process.env.PAYPAL_CLIENT_ID;
const paypalSecret = process.env.PAYPAL_CLIENT_SECRET;

// Root route
app.get('/', (req, res) => {
    res.send('Welcome to the Payment Service API!');
});

// Payment route
app.post('/create-payment', async (req, res) => {
    const { amount } = req.body;

    try {
        const response = await axios.post('https://api.sandbox.paypal.com/v1/payments/payment', {
            intent: 'sale',
            payer: {
                payment_method: 'paypal'
            },
            transactions: [{
                amount: {
                    total: amount,
                    currency: 'USD'
                },
                description: 'Payment description'
            }],
            redirect_urls: {
                return_url: 'http://localhost:3000/success',
                cancel_url: 'http://localhost:3000/cancel'
            }
        }, {
            auth: {
                username: paypalClientID,
                password: paypalSecret
            }
        });

        res.json(response.data);
    } catch (error) {
        console.error(error);
        res.status(500).send('Payment creation failed');
    }
});

// Start the server
app.listen(port, () => {
    console.log(`Server is running at http://localhost:${port}`);
});