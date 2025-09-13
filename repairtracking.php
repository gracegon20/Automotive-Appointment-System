<?php
include 'connect.php';
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Repair Tracking</title>
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
            <li><a href="repairtracking.php" aria-label="Repair Tracking" class="active"><i class="fas fa-tools"></i> Repair Tracking</a></li>
            <li><a href="invoices.php" aria-label="Invoicing"><i class="fas fa-file-invoice"></i> Invoices</a></li>
            <li><a href="history.php" aria-label="History"><i class="fas fa-history"></i> History</a></li>
            <li><a href="profile.php" aria-label="Profile Management"><i class="fas fa-user-cog"></i> Profile</a></li>
            <hr>
           <li><a href="AutoFIX.php" class="nav-link" aria-label="Autofix" data-section="autofix"><i class="fa-solid fa-shop"></i>About AutoFIX</a></li>
           <li><a href="logout.php" class="nav-link" aria-label="Logout" data-section="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <main class="main-content">
        <section id="repairTracking" class="tracking-section" aria-label="Repair Tracking">
            <div class="section-header">
                <h2>Repair Tracking</h2>
                <p>Track the status of your vehicle repairs here. You can view repair progress and estimated completion dates.</p>
            </div>
            <hr />
            <div id="repairTrackingDetails" class="repair-tracking-section section-content">
                <h3>Your Repair Status</h3>
                <table class="appointments-table">
                    <thead>
                        <tr>
                            <th>Vehicle Details</th>
                            <th>Service Type</th>
                            <th>Status</th>
                            <th>Technician Notes</th>
                            <th>Estimated Time Completion</th>
                        </tr>
                    </thead>
                 <tbody>
                    <?php
                    try {
                        // Check if user is logged in and user_id is set
                        if (isset($_SESSION['user_id'])) {
                            $user_id = $_SESSION['user_id'];
                            
                            // Fetch repair tracking information for the logged-in user
                            $trackingQuery = $dbh->prepare("SELECT a.vehicle_details, s.service_name, t.status, t.technician_notes, t.estimated_completion
                                                    FROM appointments a
                                                    JOIN services s ON a.service_id = s.service_id
                                                    JOIN trackings t ON a.id = t.id
                                                    WHERE a.user_id = :user_id");
                            $trackingQuery->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                            $trackingQuery->execute();

                            if ($trackingQuery->rowCount() > 0) {
                                while ($tracking = $trackingQuery->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<tr>";
                                
                                    echo "<td>" . htmlspecialchars($tracking['vehicle_details']) . "</td>";
                                    echo "<td>" . htmlspecialchars($tracking['service_name']) . "</td>";
                                    echo "<td>" . htmlspecialchars($tracking['status']) . "</td>";
                                    echo "<td>". htmlspecialchars($tracking['technician_notes'] ?? '') . "</td>";
                                    echo "<td>" . htmlspecialchars($tracking['estimated_completion'] ?? '') . "</td>";

               
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4'>No appointments found.</td></tr>";
                            }

                        } else {
                            echo "<tr><td colspan='4'>User not logged in.</td></tr>";
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
