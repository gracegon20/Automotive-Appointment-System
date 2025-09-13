<?php
session_start(); // Start the session
include 'connect.php'; // Include the database connection file
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AutoFIX - Automotive Management System</title>
  <link rel="icon" type="image/png" href="Images/carlogo.png">
  <link rel="shortcut icon" type="image/png" href="Images/carlogo.png">
  <link rel="stylesheet" href="css/StylePage.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
  <header class="header">
    <div class="logo-container">
      <img src="Images/FrontEndPictures/logo.webp" alt="AutoFIX Logo" class="logo">
      <h3>AutoFIX</h3>
    </div>
    <nav class="main-nav">
      <ul>
        <li><a href="home.php" class="nav-link">Home</a></li>
        <li><a href="services.php" class="nav-link">Services</a></li>
        <li><a href="aboutus.php" class="nav-link">About</a></li>
      </ul>
    </nav>
    <div class="auth-buttons">
      <a href="signup.php" class="btn signup">Sign Up</a>
      <a href="login.php" class="btn login">Log In</a>
    </div>
  </header>

  <section class="hero">
    <div class="hero-container">
      <div class="welcome-text">
    <h1>Welcome to <span class="highlights">AutoFIX</span></h1>
    <p><h2>Your Partner in <span class="highlight">Automotive Excellence</span></h2></p>
    <p>Are you tired of juggling paperwork, missed appointments, and inefficient workflows? Look no further! This is made for you!</p>
    <p>Our system is designed to streamline shop's operations, enhance customer experience, and boost your bottom line.</p>
    <p>Join us in revolutionizing the way to manage an automotive business.</p>
  </div>
    </div>
  </section>

  <section class="illustration">
    <div class="image-card">
      <img src="Images/FrontEndPictures/service1.jpg" alt="Illustration" class="illustration-image">
      <div class="image-card-content">
        <h2>Streamline Your Workflow</h2>
        <p>Our system simplifies our shop's operations, making it easier to manage appointments, inventory, and customer interactions.</p>
    </div>
    </div>
    <div class="image-card">
      <img src="Images/FrontEndPictures/service3.jpg" alt="Illustration" class="illustration-image">
      <div class="image-card-content">
        <h2>Automate Your Processes</h2>
        <p>With AutoFIX, you can automate repetitive tasks, allowing team to focus on what really matters - providing top-notch service.</p>
      </div>
    </div>

    <div class="image-card">
      <img src="Images/FrontEndPictures/customerexperince.jpg" alt="Illustration" class="illustration-image">
      <div class="image-card-content">
        <h2>Enhance Customer Experience</h2>
        <p>Our system helps you deliver personalized service, ensuring customers feel valued and appreciated.</p>
      </div>
    </div>
   </section>

  <section class="features">
    <div class="feature">
      <h2>SIMPLIFY</h2>
      <p>Streamline shop's operation with ease and efficiently.</p>
    </div>
    <div class="feature">
      <h2>AUTOMATE</h2>
      <p>Let automation do the manual works and process.</p>
    </div>
    <div class="feature">
      <h2>ACCELERATE</h2>
      <p>Boost efficiency and speed up every process.</p>
    </div>
  </section>
  <script src="javascripts/jscript.js"></script>
</body>
</html>
