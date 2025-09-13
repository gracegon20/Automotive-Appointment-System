<?php
include 'connect.php';
session_start();

// Today's Appointments
$today = date('Y-m-d');
$stmt = $dbh->prepare("SELECT COUNT(*) FROM appointments a WHERE a.date = :today");
$stmt->execute([':today' => $today]);
$todaysAppointments = $stmt->fetchColumn();

// Pending Services
$stmt = $dbh->query("SELECT COUNT(*) FROM trackings WHERE status != 'completed'");
$pendingServices = $stmt->fetchColumn();

// Monthly Revenue
$monthStart = date('Y-m-01');
$monthEnd = date('Y-m-t');
$stmt = $dbh->prepare("
    SELECT SUM(s.price) FROM invoices i
    JOIN appointments a ON i.id = a.id
    JOIN services s ON a.service_id = s.service_id
    WHERE i.status = 'paid' AND i.date_issued BETWEEN :start AND :end
");
$stmt->execute([':start' => $monthStart, ':end' => $monthEnd]);
$monthlyRevenue = $stmt->fetchColumn() ?? 0;

// Recent Activity
$recentActivity = $dbh->query("
  SELECT CONCAT('New appointment from ', u.name) AS activity 
  FROM appointments a 
  JOIN users u ON a.user_id = u.user_id
  ORDER BY a.id DESC LIMIT 1
")->fetchColumn();

$recentRepair = $dbh->query("
  SELECT CONCAT('Service completed for vehicle ', a.vehicle_details) AS activity
  FROM trackings t
  JOIN appointments a ON t.id = a.id
  JOIN services s ON a.service_id = s.service_id
  JOIN users u ON a.user_id = u.user_id
  WHERE u.role = 'customer' AND t.status = 'completed'
  ORDER BY t.track_id DESC LIMIT 1
")->fetchColumn();

$recentInvoice = $dbh->query("
  SELECT CONCAT('Invoice paid for ₱', s.price) AS activity 
  FROM invoices i 
  JOIN appointments a ON i.id = a.id
  JOIN services s ON a.service_id = s.service_id
  JOIN users u ON a.user_id = u.user_id
  WHERE u.role = 'customer' AND i.status = 'paid'
  ORDER BY i.invoice_id DESC LIMIT 1
")->fetchColumn();

if (isset($_GET['dashboardData'])) {
    echo json_encode([
        'appointments' => $todaysAppointments,
        'pendingRepairs' => $pendingServices,
        'revenue' => number_format($monthlyRevenue, 2)
    ]);
    exit;
}

if (isset($_GET['appointmentsTrend'])) {
    $year = date('Y');
    $stmt = $dbh->prepare("
        SELECT MONTH(date) AS month, COUNT(*) AS count
        FROM appointments
        WHERE YEAR(date) = ?
        GROUP BY MONTH(date)
        ORDER BY month
    ");
    $stmt->execute([$year]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $monthlyData = array_fill(1, 12, 0);
    foreach ($data as $row) {
        $monthlyData[(int)$row['month']] = (int)$row['count'];
    }

    echo json_encode($monthlyData);
    exit;
}

if (isset($_GET['revenueFilter'])) {
  $filter = $_GET['revenueFilter'];
  $label = '';
  $query = "
      SELECT SUM(s.price) as total
      FROM invoices i
      JOIN appointments a ON i.id = a.id
      JOIN services s ON a.service_id = s.service_id
      WHERE i.status = 'paid'
  ";

  switch ($filter) {
      case 'daily':
          $today = date('Y-m-d');
          $query .= " AND i.date_issued = :value";
          $stmt = $dbh->prepare($query);
          $stmt->execute([':value' => $today]);
          $label = "Today (" . date('F j, Y') . ")";
          break;

      case 'weekly':
          $startOfWeek = date('Y-m-d', strtotime('monday this week'));
          $endOfWeek = date('Y-m-d', strtotime('sunday this week'));
          $query .= " AND i.date_issued BETWEEN :start AND :end";
          $stmt = $dbh->prepare($query);
          $stmt->execute([':start' => $startOfWeek, ':end' => $endOfWeek]);
          $label = "This Week";
          break;

      case 'yearly':
          $year = date('Y');
          $query .= " AND YEAR(i.date_issued) = :value";
          $stmt = $dbh->prepare($query);
          $stmt->execute([':value' => $year]);
          $label = "Year " . $year;
          break;

      case 'monthly':
      default:
          $start = date('Y-m-01');
          $end = date('Y-m-t');
          $query .= " AND i.date_issued BETWEEN :start AND :end";
          $stmt = $dbh->prepare($query);
          $stmt->execute([':start' => $start, ':end' => $end]);
          $label = date('F Y');
          break;
  }

  $total = $stmt->fetchColumn() ?? 0;
  echo json_encode([
      'revenue' => number_format($total, 2),
      'label' => $label
  ]);
  exit;
}

if (isset($_GET['appointmentsFilter'])) {
  $filter = $_GET['appointmentsFilter'];
  $query = "SELECT COUNT(*) FROM appointments WHERE ";

  switch ($filter) {
      case 'weekly':
          $start = date('Y-m-d', strtotime('monday this week'));
          $end = date('Y-m-d', strtotime('sunday this week'));
          $query .= "date BETWEEN :start AND :end";
          $stmt = $dbh->prepare($query);
          $stmt->execute([':start' => $start, ':end' => $end]);
          break;

      case 'monthly':
          $start = date('Y-m-01');
          $end = date('Y-m-t');
          $query .= "date BETWEEN :start AND :end";
          $stmt = $dbh->prepare($query);
          $stmt->execute([':start' => $start, ':end' => $end]);
          break;

      case 'daily':
      default:
          $today = date('Y-m-d');
          $query .= "date = :today";
          $stmt = $dbh->prepare($query);
          $stmt->execute([':today' => $today]);
          break;
  }

  $count = $stmt->fetchColumn();
  echo json_encode([
      'count' => $count,
      'label' => ucfirst($filter)
  ]);
  exit;
}
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
  <section id="dashboard" class="content-section active">
    <div class="dashboard-widgets">
      <div class="widget stats-widget">
        <h3>Appointments</h3>
        <div class="stat-value" id="appointmentsCount"><?= $todaysAppointments ?></div>
        <div class="stat-change" id="appointmentsChange">As of <?= date('F j, Y') ?></div>
        <br>
        <label for="appointmentFilter">View Appointments:</label>
        <select id="appointmentFilter" onchange="loadAppointmentsCount(this.value)">
        <option value="daily" selected>Daily</option>
        <option value="weekly">Weekly</option>
        <option value="monthly">Monthly</option>
        </select>

      </div>

      <div class="widget stats-widget">
        <h3>Pending Services</h3>
        <div class="stat-value" id="pendingServices"><?= $pendingServices ?></div>
        <div class="stat-change" id="servicesChange">Awaiting completion</div>
      </div>

      <div class="widget stats-widget">
        <h3>Revenue</h3>
        <div class="stat-value" id="monthlyRevenue">₱<?= number_format($monthlyRevenue, 2) ?></div>
        <div class="stat-change" id="revenueChange">For <?= date('F Y') ?></div>
        <br>
        <label for="revenueFilter">View Revenue:</label>
        <select id="revenueFilter" onchange="loadRevenueData(this.value)">
        <option value="monthly" selected>Monthly</option>
        <option value="daily">Daily</option>
        <option value="weekly">Weekly</option>
        <option value="yearly">Yearly</option>
       </select>
      </div>

      <div class="widget chart-widget">
        <h3>Appointments Trend (Monthly)</h3>
        <canvas id="appointmentsChart" height="150"></canvas>
      </div>

      <div class="widget recent-activity">
        <h3>Recent Activity</h3>
        <ul>
          <li><?= $recentActivity ?: 'No recent appointments' ?></li>
          <li><?= $recentRepair ?: 'No completed services yet' ?></li>
          <li><?= $recentInvoice ?: 'No paid invoices yet' ?></li>
        </ul>
      </div>
    </div>
  </section>
</main>

<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function loadDashboardData() {
    fetch('admin.php?dashboardData=1')
        .then(res => res.json())
        .then(data => {
            document.getElementById('appointmentsCount').textContent = data.appointments;
            document.getElementById('appointmentsChange').textContent = "Today’s total appointments";

            document.getElementById('pendingServices').textContent = data.pendingRepairs;
            document.getElementById('servicesChange').textContent = "Services pending";

            document.getElementById('monthlyRevenue').textContent = `₱${data.revenue}`;
            document.getElementById('revenueChange').textContent = "So far this month";
        });
}

function loadAppointmentsTrend() {
    fetch('admin.php?appointmentsTrend=1')
        .then(res => res.json())
        .then(data => {
            const ctx = document.getElementById('appointmentsChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [
                        "Jan", "Feb", "Mar", "Apr", "May", "Jun",
                        "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
                    ],
                    datasets: [{
                        label: "Monthly Appointments",
                        data: Object.values(data),
                        borderColor: "#1e90ff",
                        backgroundColor: "rgba(30,144,255,0.2)",
                        fill: true,
                        tension: 0.3,
                        pointRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });
}

function loadRevenueData(filter = 'monthly') {
    fetch(`admin.php?revenueFilter=${filter}`)
        .then(res => res.json())
        .then(data => {
            document.getElementById('monthlyRevenue').textContent = `₱${data.revenue}`;
            document.getElementById('revenueChange').textContent = `For ${data.label}`;
        });
}

function loadAppointmentsCount(filter = 'daily') {
    fetch(`admin.php?appointmentsFilter=${filter}`)
        .then(res => res.json())
        .then(data => {
            document.getElementById('appointmentsCount').textContent = data.count;
            document.getElementById('appointmentsChange').textContent = `Total for ${data.label}`;
        });
}

loadDashboardData();
loadRevenueData();
loadAppointmentsTrend();
loadAppointmentsCount();
</script>
</body>
</html>
