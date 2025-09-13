<?php
include 'connect.php';
session_start();

$from_month = isset($_GET['from_month']) ? $_GET['from_month'] . '-01' : null;
$to_month = isset($_GET['to_month']) ? date("Y-m-t", strtotime($_GET['to_month'] . '-01')) : null;

$query = "
    SELECT 
        s.service_id,
        s.service_name,
        s.price,
        COUNT(i.id) AS times_sold,
        COUNT(i.id) * s.price AS total_sales
    FROM services s
    LEFT JOIN appointments a ON a.service_id = s.service_id
    LEFT JOIN trackings t ON t.id = a.id
    LEFT JOIN invoices i ON i.id = a.id AND i.status = 'paid'
";

$conditions = [];
$params = [];

if ($from_month && $to_month) {
    $conditions[] = "i.date_issued BETWEEN :from AND :to";
    $params[':from'] = $from_month;
    $params[':to'] = $to_month;
}

if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$query .= " GROUP BY s.service_id ORDER BY s.service_id";

$stmt = $dbh->prepare($query);
$stmt->execute($params);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Dashboard</title>
  <link rel="icon" type="image/png" href="Images/carlogo.png">
  <link rel="shortcut icon" type="image/png" href="Images/carlogo.png">
  <link rel="stylesheet" href="css/adminstyle.css"/>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"/>
<style>
.inventory-container {
  padding: 20px;
  margin-left: 165px; /* account for sidebar */
}

.inventory-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 16px;
}

.inventory-table th, .inventory-table td {
  border: 1px solid #ddd;
  padding: 12px;
  text-align: left;
}

.inventory-table th {
  background-color: #f4f4f4;
  font-weight: bold;
}

.inventory-table tr:hover {
  background-color: #f1f1f1;
}

.filter-btn {
    background-color:rgb(203, 210, 196);
    border-style: none;
    border-radius: 5px;
    padding: 10px 20px;
    margin-left: 20px;
}

.filter-btn:hover {
    background-color: rgb(145, 201, 229);
}

.back-btn {
    background-color:rgb(203, 210, 196);
    border-style: none;
    border-radius: 5px;
    padding: 10px 20px;
    margin-left: 10px;
    margin-top: 20px;
}

.back-btn:hover {
    background-color: rgb(145, 201, 229);
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
  <div class="inventory-container">
    <h1>Services Inventory Sales</h1>
    <form method="GET" style="margin-bottom: 20px;">
      <label for="from_month">From:</label>
      <input type="month" id="from_month" name="from_month" value="<?= isset($_GET['from_month']) ? $_GET['from_month'] : '' ?>">

      <label for="to_month" style="margin-left: 10px;">To:</label>
      <input type="month" id="to_month" name="to_month" value="<?= isset($_GET['to_month']) ? $_GET['to_month'] : '' ?>">

      <button class="filter-btn" type="submit">Filter</button>
    </form>

    <table class="inventory-table">
      <thead>
        <tr>
          <th>Service Id</th>
          <th>Service Name</th>
          <th>Service Price</th>
          <th>Services Rendered</th>
          <th>Total Sales</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($results as $row): ?>
          <tr>
            <td><?= htmlspecialchars($row['service_id'])?></td>
            <td><?= htmlspecialchars($row['service_name']) ?></td>
            <td>₱<?= number_format($row['price'], 2) ?></td>
            <td><?= $row['times_sold'] ?></td>
            <td>₱<?= number_format($row['total_sales'], 2) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <?php if ($from_month && $to_month): ?>
    <form method="GET" style="margin-bottom: 20px;">
    <button class="back-btn" type="submit">Back to Full View</button>
    </form>
    <?php endif; ?>
</div> 
</main>

</body>
</html>
