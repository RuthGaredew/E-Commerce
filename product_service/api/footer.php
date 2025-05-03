<?php
/* footer.php */
?>
<html>
    <head>
    <link rel="stylesheet" href="footer_style.css">
</head>
<body>

    <footer>
        <div class="footer-container">
            <div class="footer-section">
                <h3>About Us</h3>
                <p>A brief description of your company or website.</p>
                <p>Located in Addis Ababa, Ethiopia.</p>
            </div>
            <div class="footer-section">
                <h3>Contact Us</h3>
                <p>Email: info@example.com</p>
                <p>Phone: +251 123 456 789</p>
                <p>Address: Your Address, Addis Ababa</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="home_page.php">Home</a></li>
                    <li><a href="product_list.php">Products</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">Terms of Service</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Follow Us</h3>
                <div class="social-links">
                    <a href="#"><img src="images/facebook.png" alt="Facebook"></a>
                    <a href="#"><img src="images/twitter.png" alt="Twitter"></a>
                    <a href="#"><img src="images/instagram.png" alt="Instagram"></a>
                    </div>
            </div>
        </div>
        <div class="copyright">
            &copy; <?php echo date('Y'); ?> Your Website Name. All rights reserved.
        </div>
    </footer>

    </body>
</html>