<?php
include 'connect.php';
session_start();

$statusFilter = isset($_GET['status']) ? trim($_GET['status']) : '';


//Pagination Setup
$limit = 5; // Items per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Handle invoice update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['invoice_id'], $_POST['status'])) {
    $invoice_id = $_POST['invoice_id'];
    $status = $_POST['status'];
    $payment_method = $_POST['payment_method'] ?? null;

    $update = $dbh->prepare("UPDATE invoices SET status = :status, payment_method = :payment_method WHERE id = :id");
    $update->execute([
        ':status' => $status,
        ':payment_method' => $payment_method,
        ':id' => $invoice_id
    ]);
    header("Location: admininvoices.php");
    exit();
}
// Fetch invoice records joined with appointments, users, and services
$sql = "
    SELECT 
        i.id AS invoice_id,
        i.status AS invoice_status,
        i.payment_method,
        i.date_issued,
        u.name,
        a.vehicle_details,
        s.service_name,
        a.date AS appointment_date,
        a.time AS appointment_time
    FROM invoices i
    JOIN appointments a ON i.id = a.id
    JOIN users u ON a.user_id = u.user_id
    JOIN services s ON a.service_id = s.service_id
    WHERE u.role = 'customer'
";

// Apply filter if set
if ($statusFilter) {
    $sql .= " AND i.status = :status";
}

// Pagination and ordering
$sql .= " ORDER BY i.date_issued DESC LIMIT :limit OFFSET :offset";

$stmt = $dbh->prepare($sql);
if ($statusFilter) {
    $stmt->bindValue(':status', $statusFilter);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count total invoices based on the filter
$countSql = "
    SELECT COUNT(*) 
    FROM invoices i
    JOIN appointments a ON i.id = a.id
    JOIN users u ON a.user_id = u.user_id
    WHERE u.role = 'customer'
";

if ($statusFilter) {
    $countSql .= " AND i.status = :status";
}

$countStmt = $dbh->prepare($countSql);
if ($statusFilter) {
    $countStmt->bindValue(':status', $statusFilter);
}
$countStmt->execute();
$totalInvoices = $countStmt->fetchColumn();

// Check if there are any results
if ($totalInvoices === false) {
    $totalInvoices = 0;
}

$totalPages = ceil($totalInvoices / $limit);
$prevPage = max(1, $page - 1);
$nextPage = min($totalPages, $page + 1);

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Dashboard - Invoices</title>
  <link rel="icon" type="image/png" href="Images/carlogo.png">
  <link rel="shortcut icon" type="image/png" href="Images/carlogo.png">
  <link rel="stylesheet" href="css/adminstyle.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
  <style>
    main {
      margin-left: 165px;
      padding: 5px;
    }

    .invoices-contents h2 {
      margin-bottom: 20px;
      color: black;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
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
      background-color: white;
    }
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
    .status-select {
      padding: 5px;
      border-radius: 4px;
      border: 1px solid #deb887;
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
<>
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
    <div class="invoices-contents">
    <h2>Invoices Management</h2>
  <form method="GET" style="margin-bottom: 20px;">
  <label for="status-filter">Filter by Status: </label>
  <select id="status-filter" name="status" onchange="this.form.submit()">
    <option value="" <?= !isset($_GET['status']) ? 'selected' : '' ?>>All</option>
    <option value="paid" <?= (isset($_GET['status']) && $_GET['status'] === 'paid') ? 'selected' : '' ?>>Paid</option>
    <option value="not paid" <?= (isset($_GET['status']) && $_GET['status'] === 'not paid') ? 'selected' : '' ?>>Not Paid</option>
  </select>
</form>
    </div>
    <table>
      <thead>
        <tr>
          <th>Customer</th>
          <th>Service</th>
          <th>Vehicle</th>
          <th>Appointment Date</th>
          <th>Appointment Time</th>
          <th>Payment Method</th>
          <th>Status</th>
          <th>Date Issued</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($invoices as $invoice): ?>
        <?php
          $status = $invoice['invoice_status'] ?? 'N/A';
          $badgeClass = match (strtolower($status)) {
          'paid' => 'badge bg-success',
          'unpaid' => 'badge bg-danger',
          'pending' => 'badge bg-warning text-dark',
          default => 'badge bg-secondary',
          };
        ?>
        <tr>
          <td><?= htmlspecialchars($invoice['name']) ?></td>
          <td><?= htmlspecialchars($invoice['service_name']) ?></td>
          <td><?= htmlspecialchars($invoice['vehicle_details']) ?></td>
          <td><?= htmlspecialchars($invoice['appointment_date']) ?></td>
          <td><?= htmlspecialchars($invoice['appointment_time']) ?></td>
          <td><?= htmlspecialchars($invoice['payment_method'] ?? 'Not set') ?></td>
          <td><span class="<?= $badgeClass ?>"><?= htmlspecialchars($status) ?></span></td><td><?= htmlspecialchars($invoice['date_issued']) ?></td>
          
          <td>
            <form method="POST" class="invoice-form" data-id="<?= $invoice['invoice_id'] ?>">
              <input type="hidden" name="invoice_id" value="<?= $invoice['invoice_id'] ?>" />
              <input type="hidden" name="payment_method" value="<?= htmlspecialchars($invoice['payment_method']) ?>" />
              <select name="status" class="status-select">
                <option value="not paid" <?= $invoice['invoice_status'] === 'not paid' ? 'selected' : '' ?>>Not Paid</option>
                <option value="paid" <?= $invoice['invoice_status'] === 'paid' ? 'selected' : '' ?>>Paid</option>
              </select>

              <button type="submit" class="btn btn-update">Update</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
     <!-- Pagination Buttons -->
<div class="pagination">
<a href="admininvoices.php?page=<?= $prevPage ?>" class="pagination-btn <?= ($page == 1) ? 'disabled' : '' ?>">Previous</a>
<span class="pagination-info">Page <?= $page ?> of <?= $totalPages ?></span>
<a href="admininvoices.php?page=<?= $nextPage ?>" class="pagination-btn <?= ($page == $totalPages) ? 'disabled' : '' ?>">Next</a></div>
  </main>
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
  ">Invoices updated successfully!</p>
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
    document.querySelectorAll('.invoice-form').forEach(form => {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('admininvoices.php', {
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

  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="javascripts/admin.js"></script>
</body>
</html>
