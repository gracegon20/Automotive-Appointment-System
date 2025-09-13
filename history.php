<?php
include 'connect.php';
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');  // Redirect to login if not logged in
    exit();
}

$user_id = $_SESSION['user_id'];  // Get user ID from session

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>History</title>
    <link rel="icon" type="image/png" href="Images/carlogo.png">
    <link rel="shortcut icon" type="image/png" href="Images/carlogo.png">
    <link rel="stylesheet" href="css/customerstyle.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>
<body>
    <header class="main-header" style="top: 0;">
        <div class="header-content">
            <div class="logo-container">
                <a href="customer.php"><img src="Images/FrontEndPictures/logo.webp" alt="Auto Repair Shop Logo" class="logo" /></a>
                <h2>AutoFIX</h2>
            </div>
        </div>
    </header>

    <div class="sidebar" aria-label="Customer Dashboard Sidebar">
        <ul class="sidebar-menu">
            <li><a href="customerdashboard.php" aria-label="Dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <hr>
            <p>Menu</p>
            <li><a href="customer.php" aria-label="Appointments"><i class="fas fa-calendar-check"></i> Appointments</a></li>
            <li><a href="repairtracking.php" aria-label="Repair Tracking"><i class="fas fa-tools"></i> Repair Tracking</a></li>
            <li><a href="invoices.php" aria-label="Invoicing"><i class="fas fa-file-invoice"></i> Invoices</a></li>
            <li><a href="history.php" class="active" aria-label="History"><i class="fas fa-history"></i> History</a></li>
            <li><a href="profile.php" aria-label="Profile Management"><i class="fas fa-user-cog"></i> Profile</a></li>
            <hr>
            <li><a href="AutoFIX.php" class="nav-link" aria-label="Autofix" data-section="autofix"><i class="fa-solid fa-shop"></i>About AutoFIX</a></li>
            <li><a href="logout.php" class="nav-link" aria-label="Logout" data-section="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <main class="main-content">
        <section id="history" class="history-section" aria-label="History">
            <div class="section-header">
                <h2>History</h2>
                <p>View your past repair records and service history here.</p>
            </div>
            <hr />
            <div id="historyDetails" class="history-section-content section-content">
                <h3>Your Repair History</h3>
                <table class="appointments-table">
                    <thead>
                        <tr>
                            <th>Service Date</th>
                            <th>Vehicle</th>
                            <th>Service Type</th>
                            <th>Status</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        try {
                            // Fetch user's repair history
                            $stmt = $dbh->prepare("
                                SELECT 
                                    t.estimated_completion AS service_date, a.vehicle_details, s.service_name, t.status, s.price AS amount
                                    FROM appointments a
                                    JOIN services s ON a.service_id = s.service_id
                                    JOIN trackings t ON a.id = t.id
                                    JOIN invoices i ON a.id = i.id
                                    WHERE a.user_id = :user_id AND i.status = 'paid'
                            ");
                            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                            $stmt->execute();

                            if ($stmt->rowCount() > 0) {
                                while ($record = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($record['service_date']) . "</td>";
                                    echo "<td>" . htmlspecialchars($record['vehicle_details']) . "</td>";
                                    echo "<td>" . htmlspecialchars($record['service_name']) . "</td>";
                                    echo "<td>" . htmlspecialchars($record['status']) . "</td>";
                                    echo "<td>₱" . number_format($record['amount'], 2) . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4'>No service history found.</td></tr>";
                            }

                        } catch (PDOException $e) {
                            echo "<tr><td colspan='4'>Error: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <script src="javascripts/customer.js"></script>
</body>
</html>
