<?php
include 'connect.php'; // Ensure this sets up both PDO ($dbh) and mysqli ($conn) connections
session_start();

// Debugging: Check if the user is logged in and user_id is set
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    echo "User ID: " . $user_id . "<br>";  // Debug: print the user ID
} else {
    echo "User is not logged in. Session variable 'user_id' is missing.<br>";
    exit();  // Stop further execution if user is not logged in
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
        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .btn-edit,
        .btn-cancel {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-edit {
            background-color:rgb(168, 202, 230);
            color: black;
        }

        .btn-edit:hover {
            background-color: #218838;
        }

        .btn-cancel {
            background-color:rgb(241, 207, 181);
            color: black;
        }

        .btn-cancel:hover {
            background-color: #c82333;
        }

        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.6);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 400px;
            text-align: center;
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
        <h2>Appointments</h2>
        <p>Manage your appointments here. You can book new appointments and view your existing ones.</p>
        <a href="appointmentform.php" id="addAppointmentBtn" class="btn btn-primary">Add Appointment</a>
      </div>
      <br>
      <hr>
      <div id="existingAppointments" class="section-content">
        <h3>Your Existing Appointments</h3>
        <table class="appointments-table">
          <thead>
            <tr>
              <th>Service</th>
              <th>Vehicle Details</th>
              <th>Date</th>
              <th>Time</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php
try {
    // Check if user is logged in and user_id is set
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];

        $appointmentsQuery = $dbh->prepare("
            SELECT a.*, s.service_name 
            FROM appointments a 
            LEFT JOIN services s ON a.service_id = s.service_id 
            WHERE a.user_id = :user_id 
            ORDER BY a.date, a.time
        ");
        $appointmentsQuery->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $appointmentsQuery->execute();

        if ($appointmentsQuery->rowCount() > 0) {
            while ($appointment = $appointmentsQuery->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($appointment['service_name']) . "</td>";
                echo "<td>" . htmlspecialchars($appointment['vehicle_details']) . "</td>";
                echo "<td>" . htmlspecialchars($appointment['date']) . "</td>";
                echo "<td>" . htmlspecialchars($appointment['time']) . "</td>";
                echo "<td>" . htmlspecialchars($appointment['status']) . "</td>";
                echo "<td>
                  <div class='action-buttons'>";
                     if ($appointment['status'] === 'pending') {
                  echo "
                  <button type='button' class='btn-cancel' onclick='openCancelModal(" . $appointment['id'] . ")'>❌ Cancel</button>
                   ";
               } else {
               echo "<span style='color: gray;'>No actions available</span>";
            }
         echo "</div>
        </td>";

            }
        } else {
            echo "<tr><td colspan='5'>No appointments found.</td></tr>";
        }
    } else {
        echo "<tr><td colspan='5'>User not found or not logged in.</td></tr>";
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

<div id="cancelModal" class="modal-overlay">
    <div class="modal-content">
        <h3>Cancel Appointment</h3>
        <p>Are you sure you want to cancel this appointment?</p>
        <form id="cancelForm" method="POST" action="cancelappointment.php">
            <input type="hidden" name="id" id="cancelAppointmentId">
            <button type="submit" class="btn-cancel">Yes, Cancel</button>
            <button type="button" onclick="closeCancelModal()" class="btn-edit">No, Go Back</button>
        </form>
    </div>
</div>

<script>
function openCancelModal(id) {
    document.getElementById('cancelAppointmentId').value = id;
    document.getElementById('cancelModal').style.display = 'flex';
}

function closeCancelModal() {
    document.getElementById('cancelModal').style.display = 'none';
}
</script>

<script src="javascripts/customer.js"></script>
</body>
</html>
