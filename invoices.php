<?php
include 'connect.php';
session_start();

// Handle payment method submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['invoice_id'], $_POST['payment_method'])) {
    $invoice_id = $_POST['invoice_id'];
    $payment_method = $_POST['payment_method'];

    $update = $dbh->prepare("UPDATE invoices SET payment_method = :payment_method WHERE invoice_id = :invoice_id");
    $update->execute([
        ':payment_method' => $payment_method,
        ':invoice_id' => $invoice_id
    ]);
    header("Location: invoices.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Invoices</title>
    <link rel="icon" type="image/png" href="Images/carlogo.png">
    <link rel="shortcut icon" type="image/png" href="Images/carlogo.png">
    <link rel="stylesheet" href="css/customerstyle.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <style>
  .appointments-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
      background-color: #fff;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      border-radius: 10px;
      overflow: hidden;
  }

  .appointments-table th,
  .appointments-table td {
      padding: 15px;
      text-align: left;
  }

  .appointments-table thead {
      background-color: #004d99;
      color: #fff;
  }

  .appointments-table tr:nth-child(even) {
      background-color: #f9f9f9;
  }

  .appointments-table tr:hover {
      background-color: #e6f2ff;
      transition: background-color 0.3s ease;
  }

  select,
  button[type="submit"] {
      padding: 5px 10px;
      border: 1px solid #ccc;
      border-radius: 5px;
      font-size: 14px;
  }

  button[type="submit"] {
      background-color: #004d99;
      color: white;
      cursor: pointer;
      margin-left: 5px;
      transition: background-color 0.3s ease;
  }

  button[type="submit"]:hover {
      background-color: #003366;
  }

  .status-badge {
      padding: 6px 10px;
      border-radius: 5px;
      font-weight: bold;
      text-transform: capitalize;
  }

  .status-paid {
      background-color: #d4edda;
      color: #155724;
  }

  .status-not-paid {
      background-color: #f8d7da;
      color: #721c24;
  }

  .receipt-btn {
      padding: 6px 12px;
      border: none;
      border-radius: 5px;
      background-color: #28a745;
      color: white;
      cursor: pointer;
      font-size: 14px;
      transition: background-color 0.3s ease;
  }

  .receipt-btn:hover {
      background-color: #218838;
  }
</style>

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
            <li><a href="repairtracking.php" aria-label="Repair Tracking"><i class="fas fa-tools"></i> Repair Tracking</a></li>
            <li><a href="invoices.php" class="active" aria-label="Invoicing"><i class="fas fa-file-invoice"></i> Invoices</a></li>
            <li><a href="history.php" class="nav-link" aria-label="History" data-section="history"><i class="fas fa-history"></i> History</a></li>
            <li><a href="profile.php" aria-label="Profile Management"><i class="fas fa-user-cog"></i> Profile</a></li>
            <hr>
            <li><a href="AutoFIX.php" class="nav-link" aria-label="Autofix" data-section="autofix"><i class="fa-solid fa-shop"></i>About AutoFIX</a></li>
            <li><a href="logout.php" class="nav-link" aria-label="Logout" data-section="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <main class="main-content">
        <section id="invoices" class="invoices-section" aria-label="Customer Invoices">
            <div class="section-header">
                <h2>Invoices</h2>
                <p>You can view your invoice records here.</p>
            </div>
            <hr />
            <div id="invoiceDetails" class="section-content">
                <h3>Your Invoices</h3>
                <table class="appointments-table">
                    <thead>
                        <tr>
                            <th>Vehicle</th>
                            <th>Service</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Date Issued</th>
                            <th>Status</th>
                            <th>Receipt</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    try {
                        if (isset($_SESSION['user_id'])) {
                            $user_id = $_SESSION['user_id'];
                    
                            // Step 1: Auto-generate invoices if tracking is completed but no invoice exists
                            $generateInvoices = $dbh->prepare("
                                INSERT INTO invoices (track_id, id, payment_method, date_issued, status)
                                SELECT t.track_id, t.id, NULL, NOW(), 'not paid'
                                FROM appointments a
                                LEFT JOIN trackings t ON t.id = a.id
                                JOIN invoices i ON i.id = a.id
                                WHERE a.user_id = :user_id AND t.status = 'ongoing' AND i.invoice_id IS NULL
                            ");
                            $generateInvoices->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                            $generateInvoices->execute();
                    
                            // Step 2: Fetch invoices for display
                            $invoiceQuery = $dbh->prepare("
                                SELECT i.invoice_id, a.vehicle_details, s.service_name, s.price AS amount,
                                       i.payment_method, i.date_issued, i.status
                                FROM invoices i
                                JOIN appointments a ON i.id = a.id
                                JOIN services s ON a.service_id = s.service_id
                                JOIN trackings t ON t.id = a.id
                                WHERE a.user_id = :user_id AND t.status = 'completed'
                                ORDER BY i.date_issued DESC
                            ");
                            $invoiceQuery->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                            $invoiceQuery->execute();
                    
                            if ($invoiceQuery->rowCount() > 0) {
                                while ($invoice = $invoiceQuery->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($invoice['vehicle_details']) . "</td>";
                                    echo "<td>" . htmlspecialchars($invoice['service_name']) . "</td>";
                                    echo "<td>₱" . htmlspecialchars(number_format($invoice['amount'], 2)) . "</td>";
                    
                                    // Payment Method Selection
                                    if ($invoice['payment_method'] === null) {
                                        echo "<td>
                                            <form method='POST'>
                                                <input type='hidden' name='invoice_id' value='" . $invoice['invoice_id'] . "' />
                                                <select name='payment_method' required>
                                                    <option value='' disabled selected>Select</option>
                                                    <option value='Cash'>Cash</option>
                                                    <option value='GCash'>GCash</option>
                                                </select>
                                                <button type='submit'>Submit</button>
                                            </form>
                                        </td>";
                                    } else {
                                        echo "<td>" . htmlspecialchars($invoice['payment_method']) . "</td>";
                                    }
                    
                                    echo "<td>" . htmlspecialchars($invoice['date_issued']) . "</td>";
                                    echo "<td>" . htmlspecialchars($invoice['status']) . "</td>";
                    
                                    if ($invoice['status'] === 'paid') {
                                        echo "<td><button onclick='downloadReceipt(" . $invoice['invoice_id'] . ", \"" . htmlspecialchars($invoice['vehicle_details']) . "\", \"" . htmlspecialchars($invoice['service_name']) . "\", " . $invoice['amount'] . ", \"" . $invoice['date_issued'] . "\")'>Download Receipt</button></td>";
                                    } else {
                                        echo "<td>-</td>";
                                    }
                    
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='8'>No invoices found.</td></tr>";
                            }
                        } else {
                            echo "<tr><td colspan='8'>User not found or not logged in.</td></tr>";
                        }
                    } catch (PDOException $e) {
                        echo "Error: " . $e->getMessage();
                    }
             ?>                    
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <script>
    function downloadReceipt(invoiceId, vehicle, service, amount, dateIssued) {
        // Create a new jsPDF instance
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();

        // Add title and logo (if necessary)
        doc.setFontSize(16);
        doc.text("Auto Repair Service Receipt", 20, 20);

        // Example table data (you can dynamically add more data if needed)
        const invoiceData = [
            ['Invoice #', invoiceId],
            ['Date Issued', dateIssued],
            ['Vehicle', vehicle],
            ['Service', service],
            ['Amount', '₱' + amount.toFixed(2)],
            ['Status', 'Paid']
        ];

        // Table column headers
        const columns = ["Field", "Details"];

        // Generate the table using jsPDF autoTable plugin
        doc.autoTable({
            head: [columns],
            body: invoiceData,
            startY: 30, // Start drawing the table below the title
            theme: 'grid', // Optional theme
            margin: { top: 10 }, // Optional margin
            styles: {
                fontSize: 12,
                cellPadding: 4,
                halign: 'left', // Horizontal alignment for text
            }
        });

        // Save the PDF file
        doc.save("receipt_" + invoiceId + ".pdf");
    }
    </script>

    <script src="javascripts/customer.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>

</body>
</html>
