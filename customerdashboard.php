<?php
include 'connect.php'; // Ensure this sets up both PDO ($dbh) and mysqli ($conn) connections
session_start();

$user_id = $_SESSION['user_id'] ?? null;
$name = 'Customer'; // Default name if not found

if ($user_id) {
    $stmt = $dbh->prepare("SELECT name FROM users WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && isset($user['name'])) {
        $name = $user['name'];
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard</title>
    <link rel="icon" type="image/png" href="Images/carlogo.png">
    <link rel="shortcut icon" type="image/png" href="Images/carlogo.png">
    <link rel="stylesheet" href="css/customerstyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
.main-content {
    margin-left: 170px; /* Adjust if your sidebar width is different */
    margin-right: 20px;
    padding: 2rem;
    background-color:rgb(243, 240, 230);
    min-height: 100vh;
    border-radius: 10px;
}

/* Dashboard header */
.dashboard-header {
    text-align: center;
    margin-bottom: 2rem;
}

.dashboard-header h1 {
    font-size: 2rem;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.dashboard-header p {
    font-size: 1rem;
    color: #7f8c8d;
}

/* Grid layout for cards */
.card-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 2rem;
    max-width: 1000px;
    margin: 0 auto;
    padding: 1rem;
}

@media (max-width: 768px) {
    .card-grid {
        grid-template-columns: 1fr;
    }
}

.dashboard-card {
    background-color: #fff;
    border-radius: 16px;
    padding: 1.8rem 1.5rem;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
    text-align: center;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.dashboard-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);
}

.dashboard-card h2 {
    font-size: 1.4rem;
    color: #34495e;
    margin-bottom: 0.75rem;
}

.dashboard-card p {
    font-size: 0.95rem;
    color: #555;
    margin-bottom: 1.25rem;
}

.card-button {
    display: inline-block;
    padding: 0.6rem 1.4rem;
    background-color: #007BFF;
    color: #fff;
    border: none;
    border-radius: 10px;
    font-size: 0.95rem;
    font-weight: 600;
    text-decoration: none;
    transition: background-color 0.3s ease;
}

.card-button:hover {
    background-color: #0056b3;
}


</style>
</head>
<body>
    <header class="main-header" style="top: 0;">
        <div class="header-content">
            <div class="logo-container">
                <img src="Images/FrontEndPictures/logo.webp" alt="Auto Repair Shop Logo" class="logo">
                <h2>AutoFIX</h2>
            </div>
        </div>
    </header>
    <div class="sidebar" aria-label="Customer Dashboard Sidebar">
        <ul class="sidebar-menu">
            <li><a href="customerdashboard.php" class="nav-link" aria-label="Dashboard" data-section="dashboard"> <i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
            <hr>
            <p>Menu</p>
            <li><a href="customer.php" class="nav-link" aria-label="Appointments" data-section="appointments"> <i class="fas fa-calendar-check"></i> Appointments</a></li>
            <li><a href="repairtracking.php" class="nav-link" aria-label="Repair Tracking"><i class="fas fa-tools"></i> Repair Tracking</a></li>
            <li><a href="invoices.php" class="nav-link" aria-label="Invoicing" data-section="invoicing"><i class="fas fa-file-invoice"></i> Invoices</a></li>
            <li><a href="history.php" class="nav-link" aria-label="History" data-section="history"><i class="fas fa-history"></i> History</a></li>
            <li><a href="profile.php" class="nav-link" aria-label="Profile Management" data-section="profileManagement"><i class="fas fa-user-cog"></i> Profile</a></li>
            <hr>
            <li><a href="AutoFIX.php" class="nav-link" aria-label="Autofix" data-section="autofix"><i class="fa-solid fa-shop"></i>About AutoFIX</a></li>
            <li><a href="logout.php" class="nav-link" aria-label="Logout" data-section="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

<main class="main-content">
 <section class="dashboard-header">
    <h1>Welcome, <?php echo htmlspecialchars($name); ?>!</h1>
    <p>Select an option below to manage your services.</p>
</section>

    <section class="card-grid">
        <!-- Appointments Card -->
        <div class="dashboard-card">
            <h2>Appointments</h2>
            <p>View and manage your upcoming service appointments.</p>
            <a href="customer.php" class="card-button">Go to Appointments</a>
        </div>

        <!-- Repair Tracking Card -->
        <div class="dashboard-card">
            <h2>Repair Tracking</h2>
            <p>Check the status of your ongoing vehicle repairs.</p>
            <a href="repairtracking.php" class="card-button">Track Repairs</a>
        </div>

        <!-- Invoices Card -->
        <div class="dashboard-card">
            <h2>Invoices</h2>
            <p>View your service invoices and payment status.</p>
            <a href="invoices.php" class="card-button">View Invoices</a>
        </div>

        <!-- Profile Card -->
        <div class="dashboard-card">
            <h2>Profile</h2>
            <p>Update your contact details and account settings.</p>
            <a href="profile.php" class="card-button">Manage Profile</a>
        </div>
    </section>
</main>

<script src="javascripts/customer.js"></script>
</body>
</html>
