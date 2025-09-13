<?php
include 'connect.php';
session_start();

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_id'])) {
        $deleteId = $_POST['delete_id'];
        $stmt = $dbh->prepare("DELETE FROM users WHERE user_id = :id");
        $stmt->execute([':id' => $deleteId]);
        exit("deleted");
    } 
}

// Fetch customers
$stmt = $dbh->prepare("SELECT user_id, name, address, phone FROM users WHERE role = 'customer'");
$stmt->execute();
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pagination Setup
$limit = 5; // items per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Get total customer count for pagination
$countStmt = $dbh->prepare("SELECT COUNT(*) FROM users WHERE role = 'customer'");
$countStmt->execute();
$totalCustomers = $countStmt->fetchColumn();
$totalPages = ceil($totalCustomers / $limit);

// Fetch paginated customers
$sql = "
  SELECT user_id, name, address, phone FROM users
  WHERE role = 'customer'
  ORDER BY user_id ASC
  LIMIT :limit OFFSET :offset
";
$stmt = $dbh->prepare($sql);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Admin - Customers</title>
  <link rel="icon" type="image/png" href="Images/carlogo.png">
  <link rel="shortcut icon" type="image/png" href="Images/carlogo.png">
  <link rel="stylesheet" href="css/adminstyle.css"/>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
 
  <style>

    main {
      margin-left: 165px;
      padding: 5px;
      margin-right: 10px;
    }
    .customer-contents h2 {
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

    .btn { padding: 5px 10px; cursor: pointer; }
    .btn-delete { 
      background-color:rgb(150, 188, 223); 
      color: black; 
      border: none; 
    }

    .btn-delete:hover {
      background-color: red;
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

  <main>
  <div class="customer-contents">
  <h2>Customer Management</h2>
  </div>
<table>
  <thead>
    <tr>
      <th>Name</th>
      <th>Address</th>
      <th>Phone</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($customers as $customer): ?>
      <tr data-id="<?= $customer['user_id'] ?>">
        <td><?= htmlspecialchars($customer['name']) ?></td>
        <td><?= htmlspecialchars($customer['address']) ?></td>
        <td><?= htmlspecialchars($customer['phone']) ?></td>
<td>
          <button class="btn btn-delete">Delete</button>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<!--Pagination Section-->
<div class="pagination">
<a href="admininvoices.php?page=<?= $prevPage ?>" class="pagination-btn <?= ($page == 1) ? 'disabled' : '' ?>">Previous</a>
<span class="pagination-info">Page <?= $page ?> of <?= $totalPages ?></span>
<a href="admininvoices.php?page=<?= $nextPage ?>" class="pagination-btn <?= ($page == $totalPages) ? 'disabled' : '' ?>">Next</a></div>
  
</div>

<script>
document.querySelectorAll('.btn-delete').forEach(btn => {
  btn.addEventListener('click', function () {
    const row = this.closest('tr');
    const id = row.dataset.id;

    if (confirm('Are you sure you want to delete this customer?')) {
      fetch('admincustomer.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `delete_id=${id}`
      })
      .then(res => res.text())
      .then(response => {
        if (response === 'deleted') {
          row.remove();
        } else {
          alert('Failed to delete user.');
        }
      });
    }
  });
});
</script>
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="javascripts/admin.js"></script>
</main>
</body>
</html>
