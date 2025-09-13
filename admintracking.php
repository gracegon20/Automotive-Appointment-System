<?php
include 'connect.php';
session_start();

// Pagination setup
$limit = 2; // Items per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// 1. Handle update form submission (already in your code)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['track_id'], $_POST['status'], $_POST['estimated_completion'])) {
  $track_id = $_POST['track_id'];
  $status = $_POST['status'];
  $estimated_completion = $_POST['estimated_completion'];
  $technician_notes = $_POST['technician_notes'] ?? '';

  $update = $dbh->prepare("UPDATE trackings 
      SET status = :status, 
          estimated_completion = :estimated_completion,
          technician_notes = :technician_notes
      WHERE track_id = :track_id
  ");
  $update->execute([
      ':status' => $status,
      ':estimated_completion' => $estimated_completion,
      ':technician_notes' => $technician_notes,
      ':track_id' => $track_id
  ]);

     // Fetch the appointment ID tied to this tracking
     $getAppointmentId = $dbh->prepare("SELECT id FROM trackings WHERE track_id = :track_id");
     $getAppointmentId->execute([':track_id' => $track_id]);
     $appointmentId = $getAppointmentId->fetchColumn();
 
     // If status is completed, generate invoice if not exists
     if ($status === 'completed' && $appointmentId) {
         $checkInvoice = $dbh->prepare("SELECT COUNT(*) FROM invoices WHERE id = :id");
         $checkInvoice->execute([':id' => $appointmentId]);
 
         if ($checkInvoice->fetchColumn() == 0) {
             $insertInvoice = $dbh->prepare("
                 INSERT INTO invoices (id, payment_method, date_issued, status)
                 VALUES (:id, NULL, NOW(), 'pending')
             ");
             $insertInvoice->execute([':id' => $appointmentId]);
         }
     }

    header("Location: admintracking.php");
    exit();
}

// 2. Automatically move approved appointments to trackings if not already there
$approved = $dbh->query("SELECT id FROM appointments WHERE status = 'approved'")->fetchAll(PDO::FETCH_COLUMN);
if ($approved) {
    $existing = $dbh->query("SELECT id FROM trackings")->fetchAll(PDO::FETCH_COLUMN);
    $missing = array_diff($approved, $existing);

    if (!empty($missing)) {
        $insert = $dbh->prepare("INSERT INTO trackings (id, status, estimated_completion) VALUES (:id, 'none', NULL)");
        foreach ($missing as $id) {
            $insert->execute([':id' => $id]);
        }
    }
}

// 3. Fetch joined tracking data
$sql = "
    SELECT 
        t.track_id,
        t.status,
        t.estimated_completion,
        t.technician_notes,
        a.vehicle_details,
        s.service_name,
        a.date AS appointment_date,
        a.time AS appointment_time,
        u.name
    FROM trackings t
    JOIN appointments a ON t.id = a.id
    JOIN services s ON a.service_id = s.service_id
    JOIN users u ON a.user_id = u.user_id
    WHERE u.role = 'customer'
    ORDER BY a.date DESC, a.time DESC
";
$stmt = $dbh->prepare($sql);
$stmt->execute();
$trackings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Fetch joined tracking data with optional filtering
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';

$sql = "
    SELECT 
        t.track_id,
        t.status,
        t.estimated_completion,
        t.technician_notes,
        a.vehicle_details,
        s.service_name,
        a.date AS appointment_date,
        a.time AS appointment_time,
        u.name
    FROM trackings t
    JOIN appointments a ON t.id = a.id
    JOIN services s ON a.service_id = s.service_id
    JOIN users u ON a.user_id = u.user_id
    WHERE u.role = 'customer'
";

if ($statusFilter !== 'all') {
    $sql .= " AND t.status = :statusFilter";
}

$sql .= " ORDER BY a.date DESC, a.time DESC";

$stmt = $dbh->prepare($sql);

if ($statusFilter !== 'all') {
    $stmt->execute([':statusFilter' => $statusFilter]);
} else {
    $stmt->execute();
}

$trackings = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql = "
    SELECT 
        t.track_id,
        t.status,
        t.estimated_completion,
        t.technician_notes,
        a.vehicle_details,
        s.service_name,
        a.date AS appointment_date,
        a.time AS appointment_time,
        u.name
    FROM trackings t
    JOIN appointments a ON t.id = a.id
    JOIN services s ON a.service_id = s.service_id
    JOIN users u ON a.user_id = u.user_id
    WHERE u.role = 'customer'
";

// If the filter is applied, add the condition for status
//For the next and previous clicking
if ($statusFilter !== 'all') {
    $sql .= " AND t.status = :statusFilter";
}

// Add pagination to the query
$sql .= " ORDER BY a.date DESC, a.time DESC LIMIT $limit OFFSET $offset";
$stmt = $dbh->prepare($sql);

if ($statusFilter !== 'all') {
    $stmt->execute([':statusFilter' => $statusFilter]);
} else {
    $stmt->execute();
}

$trackings = $stmt->fetchAll(PDO::FETCH_ASSOC);
$totalRecordsQuery = "SELECT COUNT(*) FROM trackings t
    JOIN appointments a ON t.id = a.id
    JOIN users u ON a.user_id = u.user_id
    WHERE u.role = 'customer'";

if ($statusFilter !== 'all') {
    $totalRecordsQuery .= " AND t.status = :statusFilter";
}

$totalStmt = $dbh->prepare($totalRecordsQuery);

if ($statusFilter !== 'all') {
    $totalStmt->execute([':statusFilter' => $statusFilter]);
} else {
    $totalStmt->execute();
}

$totalRecords = $totalStmt->fetchColumn();
$totalPages = ceil($totalRecords / $limit);
// Determine the previous and next page numbers
$prevPage = $page > 1 ? $page - 1 : 1;
$nextPage = $page < $totalPages ? $page + 1 : $totalPages;
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Dashboard - Appointments</title>
  <link rel="icon" type="image/png" href="Images/carlogo.png">
  <link rel="shortcut icon" type="image/png" href="Images/carlogo.png">
  <link rel="stylesheet" href="css/adminstyle.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
<style>

    /* Overall body layout */
main {
  margin-left: 165px;
  padding: 5px;
}

.track-contents h2 {
  margin-bottom: 20px;
  color: black;
}

table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  background-color: white;
  border-radius: 8px;
  overflow: hidden;
  font-size: 12px;
}

th, td {
  padding: 12px 15px;
  border: 1px solid #deb887; /* burlywood */
  text-align: center;
}

th {
  background-color: #f5deb3; /* wheat */
  color: #5a3e1b;
  font-weight: bold;
}

tr:nth-child(even) {
  background-color: white; 
}

/* Adjust update column */
.update-column {
  display: flex;
  flex-direction: column;
  align-items: flex-start; 
  gap: 8px; 
  padding: 8px;
}

.update-column textarea {
  width: 90%;
  min-height: 50px;
  padding: 8px;
}

.update-column select,
.update-column input[type="date"],
.update-column button {
  width: 90%;
  max-width: 180px;
  padding: 5px;
  border-radius: 4px;
  margin-top: 5px;
  border: 1px solid #deb887;
}

.update-column button {
  margin-top: 10px;
  padding: 8px;
  cursor: pointer;
  border-radius: 4px;
  background-color: #8b4513; /* saddle brown */
  color: white;
  font-weight: bold;
  transition: background-color 0.3s ease;
}

.update-column button:hover {
  background-color: #a0522d; /* sienna */
}

.status-select {
  padding: 5px;
  border-radius: 4px;
  border: 1px solid #deb887;
}

textarea {
  resize: vertical;
  padding: 5px;
  border-radius: 4px;
  border: 1px solid #deb887;
  width: 100%;
  font-family: inherit;
}

/* Filter container */
.filter-container {
  margin: 20px 0;
  text-align: left;
}

.filter-btn {
  padding: 8px 16px;
  margin-right: 10px;
  border: 1px solid #deb887;
  background-color: #fffacd;
  color: #5a3e1b;
  border-radius: 6px;
  text-decoration: none;
  font-weight: bold;
  transition: 0.3s;
}

.filter-btn.active, .filter-btn:hover {
  background-color: #8b4513;
  color: white;
}

.filter-btn:hover:not(.active) {
  background-color: #deb887;
  color: #5a3e1b;
}

.pagination {
  margin-top: 20px;
  text-align: center;
}

.pagination-btn {
  padding: 8px 16px;
  margin: 0 5px;
  border: 1px solid #deb887;
  background-color: #fffacd;
  color: #5a3e1b;
  border-radius: 6px;
  text-decoration: none;
  font-weight: bold;
  transition: 0.3s;
}

.pagination-btn:hover {
  background-color: #8b4513;
  color: white;
}

.pagination-btn.disabled {
  background-color: #f0f0f0;
  color: #ccc;
  pointer-events: none;
}

.pagination-info {
  font-weight: bold;
  padding: 0 10px;
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
    <div class="track-contents">
    <h2>Repair Tracking Management</h2>
    <div class="filter-container">
  <a href="admintracking.php?status=all" class="filter-btn <?= ($statusFilter == 'all') ? 'active' : '' ?>">All</a>
  <a href="admintracking.php?status=ongoing" class="filter-btn <?= ($statusFilter == 'ongoing') ? 'active' : '' ?>">Ongoing</a>
  <a href="admintracking.php?status=completed" class="filter-btn <?= ($statusFilter == 'completed') ? 'active' : '' ?>">Completed</a>
  <a href="admintracking.php?status=ready for pick-up" class="filter-btn <?= ($statusFilter == 'ready for pick-up') ? 'active' : '' ?>">Ready for Pick-up</a>
 </div>
</div>

    <table>
      <thead>
        <tr>
        <th>Customer</th>
        <th>Service</th>
        <th>Vehicle</th>
        <th>Technician Notes</th>
        <th>Appointment Date</th>
        <th>Appointment Time</th>
        <th>Status</th>
        <th>Completion Date</th>
        <th>Update</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($trackings as $track): ?>
        <tr>
          <td><?= htmlspecialchars($track['name']) ?></td>
          <td><?= htmlspecialchars($track['service_name']) ?></td>
          <td><?= htmlspecialchars($track['vehicle_details']) ?></td>
          <td><?= htmlspecialchars($track['technician_notes'] ?? '') ?></td>
          <td><?= htmlspecialchars($track['appointment_date']) ?></td>
          <td><?= htmlspecialchars($track['appointment_time']) ?></td>
          <td><?= htmlspecialchars($track['status']) ?></td>
          <td><?= htmlspecialchars($track['estimated_completion'] ?? '') ?></td>

          <td class="update-column">
            <form method="POST" class="tracking-form" data-id="<?= $track['track_id'] ?>">
              <textarea name="technician_notes" rows="3" cols="25" placeholder="Enter notes..."><?= htmlspecialchars($track['technician_notes'] ?? '') ?></textarea>
              <input type="hidden" name="track_id" value="<?= $track['track_id'] ?>" />
              <select name="status" class="status-select">
                <option <?= $track['status'] === 'none' ? 'selected' : '' ?>>none</option>
                <option <?= $track['status'] === 'ongoing' ? 'selected' : '' ?>>ongoing</option>
                <option <?= $track['status'] === 'completed' ? 'selected' : '' ?>>completed</option>
                <option <?= $track['status'] === 'ready for pick-up' ? 'selected': '' ?>>ready for pick-up</option>
              </select>
              <br>
              <input type="date" name="estimated_completion" value="<?= htmlspecialchars($track['estimated_completion']) ?>" />
              <br>
              <button type="submit" class="btn btn-update">Update</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <!-- Pagination Buttons -->
<div class="pagination">
    <a href="admintracking.php?page=<?= $prevPage ?>&status=<?= htmlspecialchars($statusFilter) ?>" class="pagination-btn <?= ($page == 1) ? 'disabled' : '' ?>">Previous</a>
    <span class="pagination-info">Page <?= $page ?> of <?= $totalPages ?></span>
    <a href="admintracking.php?page=<?= $nextPage ?>&status=<?= htmlspecialchars($statusFilter) ?>" class="pagination-btn <?= ($page == $totalPages) ? 'disabled' : '' ?>">Next</a>
</div>
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
  ">Repair status updated successfully!</p>
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
  <script>
    document.querySelectorAll('.tracking-form').forEach(form => {
  form.addEventListener('submit', function (e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch('admintracking.php', {
      method: 'POST',
      body: new URLSearchParams(formData)
    })
    .then(res => {
      if (!res.ok) throw new Error('Network error');
      return res.text();
    })
    .then(() => {
      // Show the success modal and overlay
      document.getElementById('successModal').style.display = 'block';
      document.getElementById('modalOverlay').style.display = 'block';

      // Hide the modal and overlay after 3 seconds
      setTimeout(() => {
        document.getElementById('successModal').style.display = 'none';
        document.getElementById('modalOverlay').style.display = 'none';
        window.location.reload(); // Reload the page to reflect updates
      }, 3000); // 3000ms = 3 seconds
    })
    .catch(err => {
      alert('Update failed.');
      console.error(err);
    });
  });
 });

</script>

<script>
  // Set minimum date to today for all date inputs
  window.addEventListener('DOMContentLoaded', () => {
    const dateInputs = document.querySelectorAll('input[type="date"]');
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    const minDate = `${yyyy}-${mm}-${dd}`;

    dateInputs.forEach(input => {
      input.min = minDate;
    });
  });
</script>


</main>
  <!-- Font Awesome for Icons -->
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="javascripts/admin.js"></script>
</body>
</html>
