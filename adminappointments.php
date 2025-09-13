<?php
include 'connect.php';
session_start();

// Pagination setup
$limit = 4; // Items per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'All';

// Handle appointment status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['status'], $_POST['id'])) {
    $id = $_POST['id'];
    $status = $_POST['status'];

    $update = $dbh->prepare("UPDATE appointments SET status = :status WHERE id = :id");
    $update->execute([':status' => $status, ':id' => $id]);
    header("Location: adminappointments.php?status=" . urlencode($statusFilter)); // refresh to show changes with filter
    exit();
}

// Handle appointment deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];

    // First, check if the repair tracking status is "completed and ready for pick-up" and the invoice is "paid"
    $checkAppointment = $dbh->prepare("SELECT t.status AS tracking_status, i.status AS invoice_status
                                       FROM appointments a
                                       LEFT JOIN trackings t ON a.id = t.id
                                       LEFT JOIN invoices i ON a.id = i.id
                                       WHERE a.id = :id");
    $checkAppointment->execute([':id' => $delete_id]);
    $appointment = $checkAppointment->fetch(PDO::FETCH_ASSOC);

    if ($appointment && $appointment['tracking_status'] === 'completed' && $appointment['invoice_status'] === 'paid') {
        // Proceed with deletion if conditions are met
        $delete = $dbh->prepare("DELETE FROM appointments WHERE id = :id");
        $delete->execute([':id' => $delete_id]);

        // Optionally, delete associated invoice or other related data if necessary
        $deleteInvoice = $dbh->prepare("DELETE FROM invoices WHERE id = :id");
        $deleteInvoice->execute([':id' => $delete_id]);

        header("Location: adminappointments.php?status=" . urlencode($statusFilter)); // Refresh to show changes
        exit();
    } else {
        // If the conditions are not met, show an error message
        echo "<script>alert('Cannot delete. The tracking is not completed and ready for pick-up or the invoice is not paid.');</script>";
    }
}

// Fetch appointments with optional status filter
$sql = "
    SELECT 
        a.id, 
        u.name AS customer_name, 
        s.service_name, 
        a.vehicle_details, 
        a.date, 
        a.time, 
        a.status, 
        a.message,
        a.vehicle_photo,
        t.status AS tracking_status,
        i.status AS invoice_status 
    FROM appointments a
    JOIN users u ON a.user_id = u.user_id
    JOIN services s ON a.service_id = s.service_id
    LEFT JOIN trackings t ON a.id = t.id
    LEFT JOIN invoices i ON a.id = i.id
";

if ($statusFilter !== 'All') {
  $sql .= " WHERE a.status = :status ";
}

$sql .= " ORDER BY a.date DESC, a.time DESC LIMIT :limit OFFSET :offset";

$stmt = $dbh->prepare($sql);

if ($statusFilter !== 'All') {
  $stmt->bindValue(':status', $statusFilter);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Total count for pagination
$countSql = "SELECT COUNT(*) FROM appointments " . ($statusFilter !== 'All' ? "WHERE status = :status" : "");
$countStmt = $dbh->prepare($countSql);
if ($statusFilter !== 'All') {
  $countStmt->bindValue(':status', $statusFilter);
}
$countStmt->execute();
$totalAppointments = $countStmt->fetchColumn();
$totalPages = ceil($totalAppointments / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Dashboard - Appointments</title>
  <link rel="icon" type="image/png" href="Images/carlogo.png">
  <link rel="shortcut icon" type="image/png" href="Images/carlogo.png">
  <link rel="stylesheet" href="css/adminstyle.css"/>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
  <style>

/* Content Wrapper */
main {
  margin-left: 165px; /* Account for the sidebar */
  padding: 5px;
  margin-right: 10px;
}

/* Appointments Section */
.app-contents h2 {
  margin-bottom: 20px;
  color: black;
}

/* Filter Buttons */
.filter-container {
  margin-bottom: 20px;
}

.filter-container a {
  text-decoration: none;
}

.filter-btn {
  padding: 8px 16px;
  margin-right: 10px;
  border: none;
  cursor: pointer;
  background-color: #ddd;
  border-radius: 4px;
  font-weight: bold;
  color: #5a3e1b;
  transition: background-color 0.3s ease;
  text-decoration: none;
}

.filter-btn.active {
  background-color: #8b4513;
  color: white;
}

.filter-btn:hover:not(.active) {
  background-color: #deb887;
  color: #5a3e1b;
}

/* Table Styling */
table {
  width: 100%;
  border-collapse: collapse;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  background-color: white;
  border-radius: 8px;
  overflow: hidden;
}

th, td {
  padding: 12px 15px;
  border: 1px solid #deb887;
  text-align: center;
}

th {
  background-color: #f5deb3;
  color: #5a3e1b;
  font-weight: bold;
}

tr:nth-child(even) {
  background-color: #fff;
}

/* Buttons */
.btn {
  padding: 5px 10px;
  cursor: pointer;
  border-radius: 4px;
  border: none;
  font-weight: bold;
  margin-top: 5px;
}

.btn-update {
  background-color: #8b4513;
  color: white;
  transition: background-color 0.3s ease;
}

.btn-update:hover {
  background-color: #a0522d;
}

.btn-delete {
  background-color: lightgrey;
}

.btn-delete:hover {
  background-color: red;
  color: white;
}

/* Dropdown for status */
.status-select {
  padding: 5px;
  border-radius: 4px;
  border: 1px solid #deb887;
}

  </style>
</head>
<body>
<div class="sidebar">
  <div class="logo-container">
      <img src="Images/FrontEndPictures/logo.webp" alt="AutoFIX Logo" class="logo"/>
      <h2>AutoFIX</h2>
    </div>
    <div class="sidebar-admin">
      <h3>Admin</h3>
      </div>
  <hr>
  <ul class="sidebar-menu">
    <li><a href="admin.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
    <hr>
    <p>Menu</p>
    <li><a href="adminappointments.php"><i class="fas fa-calendar-check"></i>Appointments</a></li>
    <li><a href="admintracking.php"><i class="fas fa-tools"></i>Repair Tracking</a></li>
    <li><a href="admininvoices.php"><i class="fas fa-file-invoice"></i>Invoices</a></li>
    <li><a href="admininventory.php"><i class="fa-solid fa-boxes-stacked"></i>Services Inventory</a></li>
    <li><a href="adminreports.php"><i class="fas fa-line-chart"></i>Reports</a></li>
    <li><a href="admincustomer.php"><i class="fas fa-users"></i>Customers</a></li>
    <hr>
    <li><a href="logout.php" id="logout-btn"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
  </ul>
</div>

  <!-- Main Content -->
  <main>
    <div class="app-contents">
    <h2>Appointments Management</h2>
    <div class="filter-container" role="group" aria-label="Filter appointments by status">
      <a href="adminappointments.php?status=All" class="filter-btn <?= $statusFilter === 'All' ? 'active' : '' ?>">All</a>
      <a href="adminappointments.php?status=Pending" class="filter-btn <?= $statusFilter === 'Pending' ? 'active' : '' ?>">Pending</a>
      <a href="adminappointments.php?status=Approved" class="filter-btn <?= $statusFilter === 'Approved' ? 'active' : '' ?>">Approved</a>
    </div>
    </div>
    <table>
      <thead>
        <tr>
          <th>Customer</th>
          <th>Service</th>
          <th>Vehicle</th>
          <th>Message</th>
          <th>Photo</th>
          <th>Date</th>
          <th>Time</th>
          <th>Status</th>
          <th>Update</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($appointments as $appt): ?>
          <tr>
            <td><?= htmlspecialchars($appt['customer_name']) ?></td>
            <td><?= htmlspecialchars($appt['service_name']) ?></td>
            <td><?= htmlspecialchars($appt['vehicle_details']) ?></td>
            <td><?= htmlspecialchars($appt['message'] ?? '-') ?></td>
            <td>
            <?php if (!empty($appt['vehicle_photo'])): ?>
            <a href="<?= htmlspecialchars($appt['vehicle_photo']) ?>" target="_blank">
            <img src="<?= htmlspecialchars($appt['vehicle_photo']) ?>" alt="Vehicle Photo" style="width:80px;height:50px;object-fit:cover;border-radius:4px;">
            </a>
            <?php else: ?>
            -
           <?php endif; ?>
           </td>
            <td><?= htmlspecialchars($appt['date']) ?></td>
            <td><?= htmlspecialchars($appt['time']) ?></td>
            <td><?= htmlspecialchars($appt['status']) ?></td>
            <td>
              <form method="POST" class="status-form" data-id="<?= $appt['id'] ?>">
                <input type="hidden" name="id" value="<?= $appt['id'] ?>" />
                <select name="status" class="status-select">
                  <option <?= $appt['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                  <option <?= $appt['status'] === 'approved' ? 'selected' : '' ?>>Approved</option>
                </select>
                <button type="submit" name="status" class="btn btn-update">Update</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <!-- Pagination controls -->
    <div style="margin-top: 20px; text-align: center;">
      <?php if ($page > 1): ?>
        <a class="filter-btn" href="?page=<?= $page - 1 ?>&status=<?= urlencode($statusFilter) ?>">Previous</a>
      <?php endif; ?>
      <span>Page <?= $page ?> of <?= $totalPages ?></span>
      <?php if ($page < $totalPages): ?>
        <a class="filter-btn" href="?page=<?= $page + 1 ?>&status=<?= urlencode($statusFilter) ?>">Next</a>
      <?php endif; ?>
    </div>
    <script>
      document.querySelectorAll('.status-form').forEach(form => {
        form.addEventListener('submit', function (e) {
          e.preventDefault(); // Prevent full form reload

          const id = this.dataset.id;
          const status = this.querySelector('select[name="status"]').value;

          fetch('adminappointments.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${encodeURIComponent(id)}&status=${encodeURIComponent(status)}`
          })
          .then(res => {
            if (!res.ok) throw new Error('Network error');
            return res.text();
          })
          .then(response => {
         const modal = document.getElementById('successModal');
         const overlay = document.getElementById('modalOverlay');
  
         overlay.style.display = 'block';
         modal.style.display = 'block';

         setTimeout(() => {
         modal.style.display = 'none';
         overlay.style.display = 'none';
         window.location.reload();
         }, 3000); // Hide after 3 seconds
        })

        });
      });
    </script>
  </main>
  <!-- Font Awesome for Icons -->
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="javascripts/admin.js"></script>
  <!-- Success Modal -->
  <div id="successModal" style="
  display: none;
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background-color: white;
  padding: 30px 50px;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
  border-radius: 8px;
  z-index: 1000;
  text-align: center;
  max-width: 90%;
">
  <p style="
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: black;
  ">Appointment status updated successfully!</p>
</div>
<!-- Success modal overlay -->
<div id="modalOverlay" style="
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent black */
  z-index: 999;
"></div>

</body>
</html>
