<?php
include 'connect.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please login first.'); window.location.href='login.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];
$id = $_GET['id'] ?? null;

if (!$id) {
    echo "<script>alert('No appointment ID provided.'); window.location.href='customer.php';</script>";
    exit();
}

// Fetch appointment details
try {
    $stmt = $dbh->prepare("SELECT * FROM appointments WHERE id = :id AND user_id = :user_id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        echo "<script>alert('Appointment not found.'); window.location.href='customer.php';</script>";
        exit();
    }

    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Fetch appointment failed: " . $e->getMessage());
    echo "<script>alert('Error loading appointment.'); window.location.href='customer.php';</script>";
    exit();
}

// Update logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_id = $_POST['serviceType'];
    $vehicle_details = trim($_POST['vehicle_details']);
    $date = $_POST['date'];
    $time = $_POST['time'];
    $optional_message = trim($_POST['optional_message']);
    $vehicle_photo_path = $appointment['vehicle_photo']; // Default to current photo

    // Handle new photo upload if provided
    if (isset($_FILES['vehicle_photo']) && $_FILES['vehicle_photo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $fileTmp = $_FILES['vehicle_photo']['tmp_name'];
        $ext = pathinfo($_FILES['vehicle_photo']['name'], PATHINFO_EXTENSION);
        $newFileName = uniqid('vehicle_', true) . '.' . strtolower($ext);
        $destPath = $uploadDir . $newFileName;

        if (move_uploaded_file($fileTmp, $destPath)) {
            $vehicle_photo_path = $destPath;
        }
    }

    // Update the appointment
    try {
        $updateStmt = $dbh->prepare("
            UPDATE appointments 
            SET service_id = :service_id, vehicle_details = :vehicle_details, date = :date, time = :time,
                message = :message, vehicle_photo = :vehicle_photo 
            WHERE id = :id AND user_id = :user_id
        ");
        $updateStmt->execute([
            ':service_id' => $service_id,
            ':vehicle_details' => $vehicle_details,
            ':date' => $date,
            ':time' => $time,
            ':message' => $optional_message,
            ':vehicle_photo' => $vehicle_photo_path,
            ':id' => $id,
            ':user_id' => $user_id
        ]);

        echo "<script>alert('Appointment updated successfully!'); window.location.href='customer.php';</script>";
        exit();
    } catch (PDOException $e) {
        error_log("Update error: " . $e->getMessage());
        echo "<script>alert('Error updating appointment.');</script>";
    }
}

// Handle AJAX request for fetching booked times
if (isset($_GET['fetch_times']) && $_GET['fetch_times'] == 1 && isset($_GET['date'], $_GET['service_id'])) {
    header('Content-Type: application/json');

    $date = $_GET['date'];
    $service_id = $_GET['service_id'];

    try {
        $stmt = $dbh->prepare("SELECT time FROM appointments WHERE date = :date AND service_id = :service_id AND id != :id");
        $stmt->execute([
            ':date' => $date,
            ':service_id' => $service_id,
            ':id' => $id // exclude the current appointment being edited
        ]);

        $booked_times = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo json_encode($booked_times);
        exit();
    } catch (PDOException $e) {
        echo json_encode([]);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoFIX - Add Appointment</title>
    <link rel="icon" type="image/png" href="Images/carlogo.png">
    <link rel="shortcut icon" type="image/png" href="Images/carlogo.png">
    <style>
  /* Base Styles */
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: beige;
    margin: 0;
    padding: 0;
    height: 100vh;
}

/* Main Content (center form on the page) */
.main-content {
    display: flex;
    justify-content: center;
    align-items: center;
    padding-top: 60px; /* Adjust for header */
    height: 100vh; /* Full viewport height */
}

/* Form Container */
.form-container {
    background-color: #ffffff;
    padding: 30px 40px;
    border-radius: 15px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 700px; /* Increased width of the form */
    box-sizing: border-box;
}

/* Title */
.form-title {
    text-align: center;
    font-size: 24px;
    margin-bottom: 20px;
    color: #004d99;
}

/* Form Elements */
.form-label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
    color: #333;
}

/* Submit Button */
.btn-primary {
    background-color: #004d99;
    color: white;
    padding: 12px;
    border: none;
    border-radius: 8px;
    width: 100%;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.btn-primary:hover {
    background-color: #003366;
}

/* Back Button */
.back-btn {
    color: #004d99;
    font-weight: bold;
    text-decoration: none;
    font-size: 16px;
    padding: 10px;
    display: inline-block;
    margin-top: 20px; /* Added margin to place it below the submit button */
}

.back-btn:hover {
    text-decoration: underline;
}

/* Modal */
.modal-overlay {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0; top: 0;
    width: 100%; height: 100%;
    background: rgba(0, 0, 0, 0.5);
}

.modal-content {
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    padding: 30px;
    border-radius: 10px;
    text-align: center;
    max-width: 400px;
    width: 90%;
}

.modal-content h3 {
    margin-bottom: 15px;
    color: #004d99;
}

.modal-content button {
    padding: 10px 20px;
    margin-top: 10px;
    background: #004d99;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}

.modal-content button:hover {
    background: #003366;
}

textarea.form-input {
    height: 120px;
    resize: vertical; /* allows resizing up/down */
}

.form-columns {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    gap: 20px;
    box-sizing: border-box;
}

.form-column {
    flex: 0 0 48%; /* roughly 2 equal columns with space between */
    box-sizing: border-box;
}

.form-input, select, textarea {
    width: 100%;
    padding: 10px 12px;
    margin-bottom: 20px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 15px;
    box-sizing: border-box;
}

</style>

</head>
<body>
    <main class="main-content">
        <section id="editAppointment" class="content-section">
        <form method="POST" enctype="multipart/form-data" id="appointmentForm" class="form-container" onsubmit="return showModal(event)">
    <div class="form-title">Edit Appointment</div>

    <div class="form-columns">
        <div class="form-column">
            <label for="serviceType" class="form-label">Service Type:</label>
            <select name="serviceType" id="serviceType" class="form-input" required>
                <?php
                $services = $dbh->query("SELECT service_id, service_name FROM services");
                foreach ($services as $service) {
                    $selected = $appointment['service_id'] == $service['service_id'] ? 'selected' : '';
                    echo "<option value='{$service['service_id']}' $selected>{$service['service_name']}</option>";
                }
                ?>
            </select>

            <label for="vehicle_details" class="form-label">Vehicle Details:</label>
            <input type="text" name="vehicle_details" class="form-input" required value="<?= htmlspecialchars($appointment['vehicle_details']) ?>">

            <label for="date" class="form-label">Date:</label>
            <input type="date" name="date" class="form-input" required value="<?= $appointment['date'] ?>">

            <label for="time" class="form-label">Time:</label>
            <select name="time" id="time" class="form-input" required>
                <option value="">Select a time</option>
            </select>
        </div>

        <div class="form-column">
            <label for="optional_message" class="form-label">Message (Optional):</label>
            <textarea name="optional_message" class="form-input"><?= htmlspecialchars($appointment['message'] ?? '') ?></textarea>

            <label for="vehicle_photo" class="form-label">Replace Vehicle Photo:</label>
            <input type="file" name="vehicle_photo" class="form-input" accept="image/*">
            <?php if ($appointment['vehicle_photo']): ?>
                <p>Current Photo: <a href="<?= $appointment['vehicle_photo'] ?>" target="_blank">View</a></p>
            <?php endif; ?>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Update Appointment</button>
    <a href="customer.php" class="back-btn">← Back</a>
</form>

        </section>
    </main>
    <script>
  function showModal(event) {
    event.preventDefault();
    document.getElementById('confirmModal').style.display = 'block';
    return false;
  }

  function closeModal() {
    document.getElementById('confirmModal').style.display = 'none';
  }

  function submitAppointment() {
    document.getElementById('appointmentForm').submit();
  }
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const dateInput = document.getElementById('date');
    const serviceSelect = document.getElementById('serviceType');
    const timeInput = document.getElementById('time');

    const holidays = [
        '2025-01-01', '2025-12-25', '2025-04-09', '2025-05-01',
        '2025-06-12', '2025-08-25', '2025-11-30', '2025-12-30'
    ];

    const startHour = 8, endHour = 17, interval = 30;
    const currentTime = "<?= $appointment['time'] ?>";

    function generateSlots() {
        const slots = [];
        for (let hour = startHour; hour < endHour; hour++) {
            for (let min = 0; min < 60; min += interval) {
                const h = String(hour).padStart(2, '0');
                const m = String(min).padStart(2, '0');
                slots.push(`${h}:${m}`);
            }
        }
        return slots;
    }

    function formatDisplay(time24) {
        const [h, m] = time24.split(':');
        const hour = parseInt(h);
        const ampm = hour < 12 ? 'AM' : 'PM';
        const displayHour = hour % 12 || 12;
        return `${displayHour}:${m} ${ampm}`;
    }

    async function updateTimeOptions() {
        const date = dateInput.value;
        const serviceId = serviceSelect.value;
        timeInput.innerHTML = '<option value="">Select a time</option>';

        if (!date || !serviceId) return;

        const selectedDate = new Date(date);
        if (selectedDate.getDay() === 0 || holidays.includes(date)) {
            alert('Appointments are not available on Sundays or holidays.');
            return;
        }

        try {
            const response = await fetch(`editappointment.php?fetch_times=1&date=${date}&service_id=${serviceId}&id=<?= $id ?>`);
            const booked = await response.json();
            const slots = generateSlots();

            slots.forEach(slot => {
                const option = document.createElement('option');
                option.value = slot;
                option.textContent = booked.includes(slot) ? `${formatDisplay(slot)} (Booked)` : formatDisplay(slot);
                if (booked.includes(slot) && slot !== currentTime) option.disabled = true;
                if (slot === currentTime) option.selected = true;
                timeInput.appendChild(option);
            });
        } catch (err) {
            console.error('Error fetching booked times:', err);
        }
    }

    updateTimeOptions();
    dateInput.addEventListener('change', updateTimeOptions);
    serviceSelect.addEventListener('change', updateTimeOptions);
});
</script>



<script src="customer.js"></script>
    <!-- Confirmation Modal -->
<div class="modal-overlay" id="confirmModal">
  <div class="modal-content">
    <h3>Confirm Rescheduling</h3>
    <p>Are you sure you want to reschedule this appointment?</p>
    <button onclick="submitAppointment()">Yes, Reschedule It</button>
    <button onclick="closeModal()">Cancel</button>
  </div>
</div>

</body>
</html>