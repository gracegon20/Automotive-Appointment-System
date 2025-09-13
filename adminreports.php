<?php
include 'connect.php';
session_start();

$startDate = $_GET['start'] ?? null;
$endDate = $_GET['end'] ?? null;


$appointmentsData = [];
$appointmentsMonthlyData = [];
$invoicesWeeklyData = [];
$invoicesMonthlyData = [];
$repairWeeklyData = [];
$repairMonthlyData = [];

if ($startDate && $endDate) {
    // Daily appointments
    $stmt = $dbh->prepare("
        SELECT DATE(date) as date, COUNT(*) as total 
        FROM appointments 
        WHERE date BETWEEN :start AND :end 
        GROUP BY date
        ORDER BY date
    ");
    $stmt->execute([
        ':start' => $startDate,
        ':end' => $endDate
    ]);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $appointmentsData[] = [
            'date' => $row['date'],
            'total' => (int)$row['total']
        ];
    }

    // Monthly appointments
    $stmtMonthly = $dbh->prepare("
        SELECT DATE_FORMAT(date, '%Y-%m') as month, COUNT(*) as total
        FROM appointments
        WHERE date BETWEEN :start AND :end
        GROUP BY month
        ORDER BY month
    ");
    $stmtMonthly->execute([
        ':start' => $startDate,
        ':end' => $endDate
    ]);
    while ($row = $stmtMonthly->fetch(PDO::FETCH_ASSOC)) {
        $appointmentsMonthlyData[] = [
            'month' => $row['month'],
            'total' => (int)$row['total']
        ];
    }

    // Weekly invoices
    $stmtInvoicesWeekly = $dbh->prepare("
        SELECT YEARWEEK(date_issued, 1) as yearweek, COUNT(*) as total
        FROM invoices
        WHERE date_issued BETWEEN :start AND :end
        GROUP BY yearweek
        ORDER BY yearweek
    ");
    $stmtInvoicesWeekly->execute([
        ':start' => $startDate,
        ':end' => $endDate
    ]);
    while ($row = $stmtInvoicesWeekly->fetch(PDO::FETCH_ASSOC)) {
        $invoicesWeeklyData[] = [
            'yearweek' => $row['yearweek'],
            'total' => (int)$row['total']
        ];
    }

    // Monthly invoices
    $stmtInvoicesMonthly = $dbh->prepare("
        SELECT DATE_FORMAT(date_issued, '%Y-%m') as month, COUNT(*) as total
        FROM invoices
        WHERE date_issued BETWEEN :start AND :end
        GROUP BY month
        ORDER BY month
    ");
    $stmtInvoicesMonthly->execute([
        ':start' => $startDate,
        ':end' => $endDate
    ]);
    while ($row = $stmtInvoicesMonthly->fetch(PDO::FETCH_ASSOC)) {
        $invoicesMonthlyData[] = [
            'month' => $row['month'],
            'total' => (int)$row['total']
        ];
    }

    // Weekly repair data
    $stmtRepairWeekly = $dbh->prepare("
        SELECT YEARWEEK(estimated_completion, 1) as yearweek, COUNT(*) as total
        FROM trackings
        WHERE estimated_completion BETWEEN :start AND :end AND status = 'completed'
        GROUP BY yearweek
        ORDER BY yearweek
    ");
    $stmtRepairWeekly->execute([
        ':start' => $startDate,
        ':end' => $endDate
    ]);
    while ($row = $stmtRepairWeekly->fetch(PDO::FETCH_ASSOC)) {
        $repairWeeklyData[] = [
            'yearweek' => $row['yearweek'],
            'total' => (int)$row['total']
        ];
    }

    // Monthly repair data
    $stmtRepairMonthly = $dbh->prepare("
        SELECT DATE_FORMAT(estimated_completion, '%Y-%m') as month, COUNT(*) as total
        FROM trackings
        WHERE estimated_completion BETWEEN :start AND :end AND status = 'completed'
        GROUP BY month
        ORDER BY month
    ");
    $stmtRepairMonthly->execute([
        ':start' => $startDate,
        ':end' => $endDate
    ]);
    while ($row = $stmtRepairMonthly->fetch(PDO::FETCH_ASSOC)) {
        $repairMonthlyData[] = [
            'month' => $row['month'],
            'total' => (int)$row['total']
        ];
    }
    $stmtRepairMonthly = $dbh->query("
        SELECT DATE_FORMAT(estimated_completion, '%Y-%m') as month, COUNT(*) as total
        FROM trackings
        WHERE status = 'completed'
        GROUP BY month
        ORDER BY month
    ");
    while ($row = $stmtRepairMonthly->fetch(PDO::FETCH_ASSOC)) {
        $repairMonthlyData[] = [
            'month' => $row['month'],
            'total' => (int)$row['total']
        ];
    }
 
    // Fetch invoice count
    $stmtInvoice = $dbh->prepare("SELECT COUNT(*) as total FROM invoices WHERE date_issued BETWEEN :start AND :end");
    $stmtInvoice->execute([
        ':start' => $startDate,
        ':end' => $endDate
    ]);
    $invoiceCount = (int)$stmtInvoice->fetch(PDO::FETCH_ASSOC)['total'];

    // Fetch repair count
    $stmtRepair = $dbh->prepare("SELECT COUNT(*) as total FROM trackings WHERE estimated_completion BETWEEN :start AND :end AND status = 'completed'");
    $stmtRepair->execute([
        ':start' => $startDate,
        ':end' => $endDate
    ]);
    $repairCount = (int)$stmtRepair->fetch(PDO::FETCH_ASSOC)['total'];

    // Fetch customer count
    $stmtCustomer = $dbh->prepare("SELECT COUNT(*) as total FROM users WHERE role = 'customer' BETWEEN :start AND :end");
    $stmtCustomer->execute([
        ':start' => $startDate,
        ':end' => $endDate
    ]);
    $customerCount = (int)$stmtCustomer->fetch(PDO::FETCH_ASSOC)['total'];

} else {
    // Handle default case where no dates are provided
    $stmt = $dbh->query("SELECT DATE(date) as date, COUNT(*) as total FROM appointments GROUP BY date ORDER BY date");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $appointmentsData[] = [
            'date' => $row['date'],
            'total' => (int)$row['total']
        ];
    }

    // Monthly appointments default
    $stmtMonthly = $dbh->query("
        SELECT DATE_FORMAT(date, '%Y-%m') as month, COUNT(*) as total
        FROM appointments
        GROUP BY month
        ORDER BY month
    ");
    while ($row = $stmtMonthly->fetch(PDO::FETCH_ASSOC)) {
        $appointmentsMonthlyData[] = [
            'month' => $row['month'],
            'total' => (int)$row['total']
        ];
    }

    // Weekly invoices default
    $stmtInvoicesWeekly = $dbh->query("
        SELECT YEARWEEK(date_issued, 1) as yearweek, COUNT(*) as total
        FROM invoices
        GROUP BY yearweek
        ORDER BY yearweek
    ");
    while ($row = $stmtInvoicesWeekly->fetch(PDO::FETCH_ASSOC)) {
        $invoicesWeeklyData[] = [
            'yearweek' => $row['yearweek'],
            'total' => (int)$row['total']
        ];
    }

    // Monthly invoices default
    $stmtInvoicesMonthly = $dbh->query("
        SELECT DATE_FORMAT(date_issued, '%Y-%m') as month, COUNT(*) as total
        FROM invoices
        GROUP BY month
        ORDER BY month
    ");
    while ($row = $stmtInvoicesMonthly->fetch(PDO::FETCH_ASSOC)) {
        $invoicesMonthlyData[] = [
            'month' => $row['month'],
            'total' => (int)$row['total']
        ];
    }

    // Default repair view (all data)
    $stmtRepairWeekly = $dbh->query("
    SELECT YEARWEEK(estimated_completion, 1) as yearweek, COUNT(*) as total
    FROM trackings
    GROUP BY yearweek
    ORDER BY yearweek
   ");
   while ($row = $stmtRepairWeekly->fetch(PDO::FETCH_ASSOC)) {
    $repairWeeklyData[] = [
        'yearweek' => $row['yearweek'],
        'total' => (int)$row['total']
    ];
  }
  //Monthly Repair Default
  $stmtRepairMonthly = $dbh->query("
        SELECT DATE_FORMAT(estimated_completion, '%Y-%m') as month, COUNT(*) as total
        FROM trackings
        GROUP BY month
        ORDER BY month
    ");
    while ($row = $stmtRepairMonthly->fetch(PDO::FETCH_ASSOC)) {
        $repairMonthlyData[] = [
            'month' => $row['month'],
            'total' => (int)$row['total']
        ];
    }

    // Fetch invoice count
    $invoiceCount = (int)$dbh->query("SELECT COUNT(*) as total FROM invoices")->fetch(PDO::FETCH_ASSOC)['total'];

    // Fetch repair count
    $repairCount = (int)$dbh->query("SELECT COUNT(*) as total FROM trackings WHERE status = 'completed'")->fetch(PDO::FETCH_ASSOC)['total'];

    // Fetch customer count
    $customerCount = (int)$dbh->query("SELECT COUNT(*) as total FROM users WHERE role = 'customer'")->fetch(PDO::FETCH_ASSOC)['total'];
}

$appointmentCount = array_sum(array_column($appointmentsData, 'total'));

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <link rel="icon" type="image/png" href="Images/carlogo.png">
  <link rel="shortcut icon" type="image/png" href="Images/carlogo.png">
    <link rel="stylesheet" href="css/adminstyle.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
 main {
  margin-left: 165px; /* Same as sidebar width */
  margin-right: 20px;
}

.report-contents h2 {
  margin-top: 20px;
  color: black;
}
.filter-btn {
  margin-left: 10px;
  padding: 8px 12px;
  background-color: beige;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.filter-btn:hover {
  background-color: rgb(110, 131, 225);
  color: white;
}

.down-btn {
  margin-left: 150px;
  padding: 8px 12px;
  background-color: beige;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.right-btn {
  margin-left: 20px;
  padding: 8px 12px;
  background-color: beige;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.down-btn:hover, .right-btn:hover {
  background-color: rgb(110, 131, 225);
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
<div class="report-contents">
  <h2>Reports</h2>
  <div style="margin-top: 20px;">
  <label for="startDate">Start Date:</label>
  <input type="date" id="startDate" value="<?= htmlspecialchars($startDate) ?>">

  <label for="endDate" style="margin-left: 10px;">End Date:</label>
  <input type="date" id="endDate" value="<?= htmlspecialchars($endDate) ?>">
  
  <button class="filter-btn" onclick="applyDateFilter()">Apply</button>
  <button class="down-btn" onclick="downloadPDF()">DownloadPDF</button>
  <button class="right-btn" onclick="downloadCSV()">DownloadCSV</button>
 </div>
 <div>
   <?php if ($startDate && $endDate): ?>
    <form method="GET" style="margin-bottom: 20px;">
    <button class="back-btn" type="submit">Back to Full View</button>
    </form>
    <?php endif; ?>
 </div>
</div>

<h3>Appointments Section</h3>
<section class="report-charts" style="display: flex; gap: 40px; flex-wrap: wrap; justify-content: left;">
  <div style="flex: 1 1 300px; max-width: 350px; height: 400px; margin-left: 10px;">
    <canvas id="appointmentsPieChart" style="width: 100%; max-width: 300px; height: 50px; margin: 30px auto;"></canvas>
  </div>
  <div style="flex: 1 1 300px; max-width: 350px; height: 400px;">
    <canvas id="appointmentsMonthlyChart" style="width: 100%; max-width: 400px; height: auto; margin: 30px auto;"></canvas>
  </div>
  <div style="flex: 1 1 300px; max-width: 350px; height: 400px;">
    <h3 style="text-align: center;">Appointment Report Summary</h3>
    <?php
    // Total appointment
    $appointmentQuery = $startDate && $endDate
        ? "SELECT COUNT(a.id) AS total FROM appointments a
        WHERE a.date BETWEEN :start AND :end"
        :"SELECT COUNT(a.id) AS total FROM appointments a";

    $stmtAppointment = $dbh->prepare($appointmentQuery);

    if ($startDate && $endDate) {
        $stmtAppointment->execute([':start' => $startDate, ':end' => $endDate]);
    } else {
        $stmtAppointment->execute();
    }

    $appointmentResult = $stmtAppointment->fetch(PDO::FETCH_ASSOC);
    $totalAppointment = $appointmentResult['total'] ?? 0;
    ?>
    <strong>Total Appointment: <?= number_format($totalAppointment) ?></strong>

    <!-- Appointment List -->
    <div style="flex: 1 1 100%; margin-left: 10px; margin-top: 20px;">
    <h4>List of Appointments</h4>

    <?php
    $appointQuery = $startDate && $endDate
      ? "SELECT u.name, s.service_name, a.status FROM appointments a
      JOIN services s ON a.service_id = s.service_id
      JOIN users u ON a.user_id = u.user_id
      WHERE a.status = 'approved' AND a.date BETWEEN :start AND :end"
      :"SELECT u.name, s.service_name, a.status FROM appointments a
      JOIN services s ON a.service_id = s.service_id
      JOIN users u ON a.user_id = u.user_id
      WHERE a.status = 'approved'";

    $stmtApp = $dbh->prepare($appointQuery);

    if ($startDate && $endDate) {
      $stmtApp->execute([':start' => $startDate, ':end' => $endDate]);
    } else {
      $stmtApp->execute();
    }

    $appointList = $stmtApp->fetchAll(PDO::FETCH_ASSOC);

  if (count($appointList) > 0): ?>
    <ul style="list-style: none; padding-left: 0;">
      <?php foreach ($appointList as $appointment): ?>
        <li style="margin-bottom: 10px; padding: 5px; border-bottom: 1px solid #ccc;">
          <strong><?= htmlspecialchars($appointment['name']) ?></strong> - 
          <?= htmlspecialchars($appointment['service_name'])?> -
          <?= htmlspecialchars($appointment['status']) ?>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <p>No Appointment found.</p>
  <?php endif; ?>

  </div>
</section>


<h3>Invoices Section</h3>
<section class="report-charts" style="display: flex; gap: 40px; flex-wrap: wrap; justify-content: left;">
  <div style="flex: 1 1 300px; max-width: 350px; height: 400px; margin-left: 10px;">
    <canvas id="invoicesWeeklyChart" width="150" height="150" style="margin-top: 30px;"></canvas>
  </div>
  <div style="flex: 1 1 300px; max-width: 350px; height: 400px; margin-left: 10px;">
    <canvas id="invoicesMonthlyChart" width="150" height="150" style="margin-top: 30px;"></canvas>
  </div>
  <div style="flex: 1 1 300px; max-width: 350px; height: 400px; margin-left: 10px;">
     <h3  style="text-align: center;"> Invoices Summary</h3>
     <?php
    // Total revenue
    $revenueQuery = $startDate && $endDate
        ? "SELECT SUM(s.price) AS total FROM invoices i 
        JOIN appointments a ON i.id = a.id
        JOIN services s ON a.service_id = s.service_id
        WHERE date_issued BETWEEN :start AND :end"
        :"SELECT SUM(s.price) AS total FROM invoices i
        JOIN appointments a ON i.id = a.id
        JOIN services s ON a.service_id = s.service_id";

    $stmtRevenue = $dbh->prepare($revenueQuery);

    if ($startDate && $endDate) {
        $stmtRevenue->execute([':start' => $startDate, ':end' => $endDate]);
    } else {
        $stmtRevenue->execute();
    }

    $revenueResult = $stmtRevenue->fetch(PDO::FETCH_ASSOC);
    $totalRevenue = $revenueResult['total'] ?? 0;
    ?>
    <strong>Total Revenue: ₱<?= number_format($totalRevenue, 2) ?></strong>

    <!-- Paid Customers List -->
    <div style="flex: 1 1 100%; margin-left: 10px; margin-top: 20px;">
    <h4>List of Paid Customers</h4>

    <?php
    $paidQuery = $startDate && $endDate
      ? "SELECT u.name, s.price, i.date_issued FROM invoices i
         JOIN appointments a ON i.id = a.id
         JOIN services s ON a.service_id = s.service_id
         JOIN users u ON a.user_id = u.user_id
         WHERE i.status = 'paid' AND i.date_issued BETWEEN :start AND :end"
      : "SELECT u.name, s.price, i.date_issued FROM invoices i
      JOIN appointments a ON i.id = a.id
      JOIN services s ON a.service_id = s.service_id
      JOIN users u ON a.user_id = u.user_id
      WHERE i.status = 'paid'";

    $stmtPaid = $dbh->prepare($paidQuery);

    if ($startDate && $endDate) {
      $stmtPaid->execute([':start' => $startDate, ':end' => $endDate]);
    } else {
      $stmtPaid->execute();
    }

    $paidCustomers = $stmtPaid->fetchAll(PDO::FETCH_ASSOC);

  if (count($paidCustomers) > 0): ?>
    <ul style="list-style: none; padding-left: 0;">
      <?php foreach ($paidCustomers as $customer): ?>
        <li style="margin-bottom: 10px; padding: 5px; border-bottom: 1px solid #ccc;">
          <strong><?= htmlspecialchars($customer['name']) ?></strong> - 
          ₱<?= htmlspecialchars($customer['price'])?> -
          <?= htmlspecialchars($customer['date_issued']) ?>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <p>No paid customers found for this range.</p>
  <?php endif; ?>
</div>

  </div>
   </div>
</section>

<h3>Repair Tracking Section</h3>
<section class="report-charts" style="display: flex; gap: 40px; flex-wrap: wrap; justify-content: left;">
  <div style="flex: 1 1 300px; max-width: 350px; height: 400px; margin-left: 10px;">
    <canvas id="repairWeeklyChart" width="150" height="150" style="margin-top: 30px;"></canvas>
  </div>
  <div style="flex: 1 1 300px; max-width: 350px; height: 400px; margin-left: 10px;">
    <canvas id="repairMonthlyChart" width="150" height="150" style="margin-top: 30px;"></canvas>
  </div>
  <div style="flex: 1 1 300px; max-width: 350px; height: 400px; margin-left: 10px;">
     <h3  style="text-align: center;"> Repair Tracking Summary</h3>

     <?php
    // Total Repair Tracking
    $repairQuery = $startDate && $endDate
        ?"SELECT COUNT(t.track_id) AS total FROM trackings t
        WHERE t.estimated_completion BETWEEN :start AND :end"
        :"SELECT COUNT(t.track_id) AS total FROM trackings t";

    $stmtRepair = $dbh->prepare($repairQuery);

    if ($startDate && $endDate) {
        $stmtRepair->execute([':start' => $startDate, ':end' => $endDate]);
    } else {
        $stmtRepair->execute();
    }

    $trackResult = $stmtRepair->fetch(PDO::FETCH_ASSOC);
    $totalRepair = $trackResult['total'] ?? 0;
    ?>
    <strong>Total Repair: <?= number_format($totalRepair) ?></strong>

    <!-- Tracking List -->
    <div style="flex: 1 1 100%; margin-left: 10px; margin-top: 20px;">
    <h4>List of Repair Tracking</h4>

    <?php
    $trackQuery = $startDate && $endDate
      ? "SELECT u.name, s.service_name, t.status FROM trackings t
      JOIN appointments a ON t.id = a.id
      JOIN services s ON a.service_id = s.service_id
      JOIN users u ON a.user_id = u.user_id
      WHERE t.status = 'completed' AND t.estimated_completion BETWEEN :start AND :end"
      :"SELECT u.name, s.service_name, t.status FROM trackings t
      JOIN appointments a ON t.id = a.id
      JOIN services s ON a.service_id = s.service_id
      JOIN users u ON a.user_id = u.user_id
      WHERE t.status = 'completed'";

    $stmtStatus = $dbh->prepare($trackQuery);

    if ($startDate && $endDate) {
      $stmtStatus->execute([':start' => $startDate, ':end' => $endDate]);
    } else {
      $stmtStatus->execute();
    }

    $trackList = $stmtStatus->fetchAll(PDO::FETCH_ASSOC);

  if (count($trackList) > 0): ?>
    <ul style="list-style: none; padding-left: 0;">
      <?php foreach ($trackList as $track): ?>
        <li style="margin-bottom: 10px; padding: 5px; border-bottom: 1px solid #ccc;">
          <strong><?= htmlspecialchars($track['name']) ?></strong> - 
          <?= htmlspecialchars($track['service_name'])?> -
          <?= htmlspecialchars($track['status']) ?>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <p>No repair tracking status found.</p>
  <?php endif; ?>
   </div>
</section>

</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
window.reportData = {
  appointments: <?= $appointmentCount ?>,
  invoices: <?= $invoiceCount ?? 0 ?>,
  repairs: <?= $repairCount ?? 0 ?>,
  customers: <?= $customerCount ?? 0 ?>
};

window.appointmentChartData = <?= json_encode($appointmentsData) ?>;
window.appointmentMonthlyChartData = <?= json_encode($appointmentsMonthlyData) ?>;
window.invoicesWeeklyChartData = <?= json_encode($invoicesWeeklyData) ?>;
window.invoicesMonthlyChartData = <?= json_encode($invoicesMonthlyData) ?>;
window.repairWeeklyChartData = <?= json_encode($repairWeeklyData) ?>;
window.repairMonthlyChartData = <?= json_encode($repairMonthlyData) ?>;

function applyDateFilter() {
  const start = document.getElementById('startDate').value;
  const end = document.getElementById('endDate').value;

  if (!start || !end) {
    alert('Please select both start and end dates.');
    return;
  }

  window.location.href = `adminreports.php?start=${start}&end=${end}`;
}

function downloadCSV() {
  const data = window.reportData;
  const month = document.getElementById('filterMonth') ? document.getElementById('filterMonth').value : '';
  const csv = `Metric,Value\nAppointments,${data.appointments}\nInvoices Paid,${data.invoices}\nRepairs Completed,${data.repairs}\nCustomers,${data.customers}`;
  const blob = new Blob([csv], { type: 'text/csv' });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.download = `report_${month}.csv`;
  link.click();
}

function downloadPDF() {
  const month = document.getElementById('filterMonth') ? document.getElementById('filterMonth').value : '';
  const data = window.reportData;
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF();

  doc.setFontSize(18).text("AutoFIX - Monthly Report", 20, 20);
  doc.setFontSize(12).text(`Report for: ${month}`, 20, 30);

  doc.autoTable({
    head: [['Metric', 'Value']],
    body: [
      ['Appointments', data.appointments],
      ['Invoices Paid', data.invoices],
      ['Repairs Completed', data.repairs],
      ['Customers', data.customers]
    ],
    startY: 40
  });

  doc.save(`AutoFIX_Report_${month}.pdf`);
}

function loadAppointmentsTrend() {
  // Appointments by Date Pie Chart
  const ctx = document.getElementById('appointmentsPieChart').getContext('2d');
  const labels = window.appointmentChartData.map(item => item.date);
  const data = window.appointmentChartData.map(item => item.total);

  new Chart(ctx, {
    type: 'pie',
    data: {
      labels: labels,
      datasets: [{
        label: "Appointments per Day",
        data: data,
        backgroundColor: generateColors(data.length),
        borderColor: 'black',
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: 'bottom',
          labels: {
            font: {
              size: 10
            }
          }
        },
        title: {
          display: true,
          text: 'Appointments Distribution by Date',
          font: {
            size: 15
          }
        }
      }
    }
  });

  // Appointments by Month Bar Chart
  const ctxMonthly = document.getElementById('appointmentsMonthlyChart').getContext('2d');
  const labelsMonthly = window.appointmentMonthlyChartData.map(item => item.month);
  const dataMonthly = window.appointmentMonthlyChartData.map(item => item.total);

  new Chart(ctxMonthly, {
    type: 'bar',
    data: {
      labels: labelsMonthly,
      datasets: [{
        label: "Appointments per Month",
        data: dataMonthly,
        backgroundColor: generateColors(dataMonthly.length),
        borderColor: '#fff',
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: 'bottom',
          labels: {
            font: {
              size: 10
            }
          }
        },
        title: {
          display: true,
          text: 'Appointments Distribution by Month',
          font: {
            size: 14
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          precision: 0,
          ticks: {
            font: {
              size: 10
            }
          }
        },
        x: {
          ticks: {
            font: {
              size: 10
            }
          }
        }
      }
    }
  });

  // Invoices per Week Line Chart
  const ctxInvoicesWeekly = document.getElementById('invoicesWeeklyChart').getContext('2d');
  const labelsInvoicesWeekly = window.invoicesWeeklyChartData.map(item => item.yearweek);
  const dataInvoicesWeekly = window.invoicesWeeklyChartData.map(item => item.total);

  new Chart(ctxInvoicesWeekly, {
    type: 'line',
    data: {
      labels: labelsInvoicesWeekly,
      datasets: [{
        label: "Invoices per Week",
        data: dataInvoicesWeekly,
        fill: false,
        borderColor: 'rgba(75, 192, 192, 1)',
        tension: 0.1
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: 'bottom',
          labels: {
            font: {
              size: 10
            }
          }
        },
        title: {
          display: true,
          text: 'Invoices per Week',
          font: {
            size: 14
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          precision: 0,
          ticks: {
            font: {
              size: 10
            }
          }
        },
        x: {
          ticks: {
            font: {
              size: 10
            }
          }
        }
      }
    }
  });

  // Invoices by Month Bar Chart
  const ctxInvoicesMonthly = document.getElementById('invoicesMonthlyChart').getContext('2d');
  const labelsInvoicesMonthly = window.invoicesMonthlyChartData.map(item => item.month);
  const dataInvoicesMonthly = window.invoicesMonthlyChartData.map(item => item.total);

  new Chart(ctxInvoicesMonthly, {
    type: 'bar',
    data: {
      labels: labelsInvoicesMonthly,
      datasets: [{
        label: "Invoices per Month",
        data: dataInvoicesMonthly,
        backgroundColor: generateColors(dataInvoicesMonthly.length),
        borderColor: '#fff',
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: 'bottom',
          labels: {
            font: {
              size: 10
            }
          }
        },
        title: {
          display: true,
          text: 'Invoices Distribution by Month',
          font: {
            size: 14
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          precision: 0,
          ticks: {
            font: {
              size: 10
            }
          }
        },
        x: {
          ticks: {
            font: {
              size: 10
            }
          }
        }
      }
    }
  });
}

function renderRepairCharts() {
  const weeklyData = window.repairWeeklyChartData;
  const monthlyData = window.repairMonthlyChartData;

  // Weekly Repair Chart
  const repairWeeklyCtx = document.getElementById('repairWeeklyChart').getContext('2d');
  new Chart(repairWeeklyCtx, {
    type: 'bar',
    data: {
      labels: weeklyData.map(d => `Week ${d.yearweek}`),
      datasets: [{
        label: 'Repairs per Week',
        data: weeklyData.map(d => d.total),
        backgroundColor: 'rgba(75, 192, 192, 0.6)',
        borderColor: 'rgba(75, 192, 192, 1)',
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false },
        title: { display: true, text: 'Weekly Repairs' }
      },
      scales: {
        y: { beginAtZero: true }
      }
    }
  });

  // Monthly Repair Chart
  const repairMonthlyCtx = document.getElementById('repairMonthlyChart').getContext('2d');
  new Chart(repairMonthlyCtx, {
    type: 'line',
    data: {
      labels: monthlyData.map(d => d.month),
      datasets: [{
        label: 'Repairs per Month',
        data: monthlyData.map(d => d.total),
        borderColor: 'rgba(153, 102, 255, 1)',
        backgroundColor: 'rgba(153, 102, 255, 0.2)',
        fill: true,
        tension: 0.3
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false },
        title: { display: true, text: 'Monthly Repairs' }
      },
      scales: {
        y: { beginAtZero: true }
      }
    }
  });
}

// Call on page load
renderRepairCharts();
function generateColors(count) {
  const colors = [];
  for (let i = 0; i < count; i++) {
    const r = Math.floor(Math.random() * 156) + 100;
    const g = Math.floor(Math.random() * 156) + 100;
    const b = Math.floor(Math.random() * 156) + 100;
    colors.push(`rgba(${r}, ${g}, ${b}, 0.7)`);
  }
  return colors;
}

document.addEventListener('DOMContentLoaded', loadAppointmentsTrend);

</script>
<!-- Font Awesome for Icons -->
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<script src="javascripts/admin.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
</body>
</html>