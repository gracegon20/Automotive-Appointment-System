<?php
include 'connect.php'; // Ensure this sets up both PDO ($dbh) and mysqli ($conn) connections
session_start();

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
  .profile-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
      background-color: #fff;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      border-radius: 10px;
      overflow: hidden;
  }

  .profile-table th, .profile-table td {
      padding: 15px;
      text-align: left;
  }

  .profile-table thead {
      background-color: #004d99;
      color: #ffffff;
  }

  .profile-table tr:nth-child(even) {
      background-color: #f2f2f2;
  }

  .profile-table tr:hover {
      background-color: #e6f2ff;
      transition: background-color 0.3s ease;
  }

  .section-header h2 {
      margin-bottom: 5px;
      color: #004d99;
  }

  .btn-primary {
      background-color: #004d99;
      color: white;
      padding: 10px 20px;
      border-radius: 5px;
      text-decoration: none;
      transition: background-color 0.3s;
  }

  .btn-primary:hover {
      background-color: #003366;
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
            <li><a href="customerdashboard.php" class="nav-link" aria-label="Dashboard" data-section="dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
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
    <section id="appointments" class="appointment-section" aria-label="Appointments">
      <div class="section-header">
        <h2>Profile</h2>
        <p>Manage your profile information here.</p>
        <a href="updateprofile.php" id="updateprofilebtn" class="btn btn-primary">Update Profile</a>
      </div>
      <br>
      <hr>
      <div id="existingAppointments" class="section-content">
        <h3>Your Profile Information</h3>
        <table class="profile-table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Address</th>
              <th>Phone</th>
              <th>Vehicle</th>
              <th>Username</th>
              <th>Password</th>
            </tr>
          </thead>
          <tbody>
          <?php
try {
    // Check if user is logged in and user_id is set
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        try {
            if (isset($_SESSION['user_id'])) {
                $user_id = $_SESSION['user_id'];
        
                $stmt = $dbh->prepare("SELECT u.name, u.address, u.phone, a.vehicle_details, u.username, u.password FROM appointments a
                JOIN users u ON a.user_id = u.user_id WHERE a.user_id = :user_id");
                $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $stmt->execute();
        
                if ($stmt->rowCount() > 0) {
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($user['name']) . "</td>";
                    echo "<td>" . htmlspecialchars($user['address']) . "</td>";
                    echo "<td>" . htmlspecialchars($user['phone']) . "</td>";
                    echo "<td>" . htmlspecialchars($user['vehicle_details']) . "</td>";
                    echo "<td>". htmlspecialchars($user["username"]) . "</td>";
                    echo "<td>********</td>"; // Mask the password for security
                    echo "</tr>";
                } else {
                    echo "<tr><td colspan='5'>Profile data not found.</td></tr>";
                }
            } else {
                echo "<tr><td colspan='5'>User not logged in.</td></tr>";
            }
        } catch (PDOException $e) {
            echo "<tr><td colspan='5'>Error loading profile: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
        }
        
    }
} catch (PDOException $e) {
    error_log("Appointments query error: " . $e->getMessage());
    echo "<tr><td colspan='5'>Error loading appointments. Please try again later.</td></tr>";
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
