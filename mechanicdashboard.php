<?php
include 'connect.php';
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mechanic Dashboard</title>
    <link rel="icon" type="image/png" href="Images/carlogo.png">
    <link rel="shortcut icon" type="image/png" href="Images/carlogo.png">
    <link rel="stylesheet" href="css/adminstyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<header class="dashboard-header">
  <div class="header-left">
    <div class="logo-container">
      <img src="Images/FrontEndPictures/logo.webp" alt="AutoFIX Logo" class="logo"/>
      <h3>AutoFIX</h3>
    </div>
  </div>
</header>

<div class="sidebar">
  <ul class="sidebar-menu">
    <li><a href="mechanicdashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
    <hr>
    <p>Menu</p>
    <li><a href="mechanicappointment.php"><i class="fas fa-calendar-check"></i> Appointments</a></li>
    <li><a href="mechanicstatus.php"><i class="fas fa-tools"></i> Repair Tracking</a></li>
    <li><a href="mechanicprofile.php"><i class="fas fa-user-cog"></i> Profile</a></li>
    <hr>
           <li><a href="logout.php" class="nav-link" aria-label="Logout" data-section="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
           <li><a href="AutoFIX.php" class="nav-link" aria-label="Autofix" data-section="autofix"><i class="fa-solid fa-shop"></i>About AutoFIX</a></li>
  </ul>
</div>

<main class="main-content">
    <section class="mechanic-dashboard">
        
    </section>
</main>
    <script src="javascripts/mechanic.js"></script>
</body>
</html>