const paypal = require('./paypalConfig');

exports.createPayment = (req, res) => {
  const { amount } = req.body;

  const paymentJson = {
    intent: 'sale',
    payer: {
      payment_method: 'paypal'
    },
    redirect_urls: {
      return_url: 'http://localhost:3000/execute', // Update this for your frontend
      cancel_url: 'http://localhost:3000/cancel'
    },
    transactions: [{
      amount: {
        total: amount,
        currency: 'USD'
      },
      description: 'E-commerce Order'
    }]
  };

  paypal.payment.create(paymentJson, (error, payment) => {
    if (error) {
      console.error(error);
      res.status(500).send(error);
    } else {
      res.json(payment);
    }
  });
};

exports.executePayment = (req, res) => {
  const paymentId = req.query.paymentId;
  const payerId = { payer_id: req.query.PayerID };

  paypal.payment.execute(paymentId, payerId, (error, payment) => {
    if (error) {
      console.error(error);
      res.status(500).send(error);
    } else {
      res.json(payment);
    }
  });
};