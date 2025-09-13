<?php
include 'connect.php';
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - AutoFIX</title>
    <link rel="icon" type="image/png" href="Images/carlogo.png">
    <link rel="shortcut icon" type="image/png" href="Images/carlogo.png">
    <link rel="stylesheet" href="css/aboutus.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="header-container">
            <div class="logo-container">
                <img src="Images/FrontEndPictures/logo.webp" alt="AutoFIX Logo" class="logo">
                <a href="home.php">Back</a>
            </div>
        </div>
    </header>

     <!-- Main Content -->
    <main class="about-section">
        <section class="intro">
            <h1>About AutoFIX</h1>
            <p>At <strong>AutoFIX</strong>, we specialize in simplifying automotive repair shop appointment and repair tracking management. Our platform is designed to enhance productivity, streamline operations, and boost customer satisfaction—all in one easy-to-use system.</p>
        </section>

        <section class="mission">
            <h2>Our Mission</h2>
            <p>We aim to revolutionize the auto repair industry by providing cutting-edge, customizable software that helps businesses operate more efficiently, grow sustainably, and deliver top-tier service to their clients.</p>
        </section>

        <section class="experience">
            <h2>Why Choose Us</h2>
            <ul>
                <li>✔ Years of industry experience</li>
                <li>✔ Innovative and user-friendly technology</li>
                <li>✔ Dedicated customer support</li>
                <li>✔ Tailored solutions for small to large-scale repair shops</li>
            </ul>
        </section>

    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <p>&copy; 2025 <strong>AutoFIX</strong>. All rights reserved.</p>
            <p>Connect with us: 
                <a href="#">Facebook</a> | 
                <a href="#">Twitter</a> | 
                <a href="#">Instagram</a>
            </p>
        </div>
    </footer>


    <!-- JavaScript -->
    <script src="javascripts/about.js"></script>
</body>
</html>
