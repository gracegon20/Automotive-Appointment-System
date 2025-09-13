<?php
include 'connect.php'; // Assumes $dbh (PDO) is set
session_start();

if (isset($_GET['fetch_times']) && $_GET['fetch_times'] == '1') {
    include 'connect.php';
    $date = $_GET['date'] ?? null;
    $service_id = $_GET['service_id'] ?? null;

    if ($date && $service_id) {
        $stmt = $dbh->prepare("SELECT time FROM appointments WHERE date = :date AND service_id = :service_id");
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':service_id', $service_id);
        $stmt->execute();
        $bookedTimes = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo json_encode($bookedTimes);
    } else {
        echo json_encode([]);
    }
    exit(); // Prevent rest of the page from rendering
}

// Get user_id directly from session (set during login)
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    echo "<script>alert('User not logged in. Please login first.'); window.location.href='login.php';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $service_id = $_POST['serviceType'] ?? null;
    $vehicle_details = trim($_POST['vehicle_details'] ?? '');
    $date = $_POST['date'] ?? null;
    $time = $_POST['time'] ?? null;
    $optional_message = trim($_POST['optional_message'] ?? '');
    $vehicle_photo_path = null;

// Handle vehicle photo upload
if (isset($_FILES['vehicle_photo']) && $_FILES['vehicle_photo']['error'] == UPLOAD_ERR_OK) {
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true); // Create uploads folder if it doesn't exist
    }

    $fileTmpPath = $_FILES['vehicle_photo']['tmp_name'];
    $fileName = basename($_FILES['vehicle_photo']['name']);
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    // Generate a unique filename to avoid overwriting
    $newFileName = uniqid('vehicle_', true) . '.' . $fileExtension;

    $dest_path = $uploadDir . $newFileName;

    if (move_uploaded_file($fileTmpPath, $dest_path)) {
        $vehicle_photo_path = $dest_path;
    } else {
        error_log("Failed to move uploaded vehicle photo.");
    }
  }


    // Basic validation
    if (!$service_id || !$vehicle_details || !$date || !$time) {
        echo "<script>alert('Please fill in all required fields.'); window.history.back();</script>";
        exit();
    }

     // Ensure the date is today or in the future
     if (strtotime($date) < strtotime('today')) {
        echo "<script>alert('Please select a date today or in the future.'); window.history.back();</script>";
        exit();
    }

    $dayOfWeek = date('w', strtotime($date)); // 0 = Sunday
    $holidays = ['2025-01-01', '2025-12-25', '2025-04-09', '2025-05-01', '2025-06-12', '2025-08-25', '2025-11-30','2025-12-30']; // Add more as needed

    if ($dayOfWeek == 0 || in_array($date, $holidays)) {
    echo "<script>alert('The shop is closed on Sundays and holidays. Please select another date.'); window.history.back();</script>";
    exit();
   }


    try {
        // Check if the service exists
        $serviceCheck = $dbh->prepare("SELECT service_id FROM services WHERE service_id = :service_id");
        $serviceCheck->bindParam(':service_id', $service_id, PDO::PARAM_INT);
        $serviceCheck->execute();

        if ($serviceCheck->rowCount() > 0) {
            // Insert the appointment
            $stmt = $dbh->prepare("
            INSERT INTO appointments (user_id, service_id, vehicle_details, date, time, status, message, vehicle_photo) 
            VALUES (:user_id, :service_id, :vehicle_details, :date, :time, 'Pending', :optional_message, :vehicle_photo)
        ");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':service_id', $service_id, PDO::PARAM_INT);
        $stmt->bindParam(':vehicle_details', $vehicle_details, PDO::PARAM_STR);
        $stmt->bindParam(':date', $date, PDO::PARAM_STR);
        $stmt->bindParam(':time', $time, PDO::PARAM_STR);
        $stmt->bindParam(':optional_message', $optional_message, PDO::PARAM_STR);
        $stmt->bindParam(':vehicle_photo', $vehicle_photo_path, PDO::PARAM_STR);
        $stmt->execute();
            echo "<script>alert('Appointment successfully booked!'); window.location.href='customer.php';</script>";
            exit();
        } else {
            echo "<script>alert('Invalid service selected. Please try again.'); window.history.back();</script>";
            exit();
        }
    } catch (PDOException $e) {
        error_log("Error inserting appointment: " . $e->getMessage());
        echo "<script>alert('An error occurred while booking the appointment. Please try again later.'); window.history.back();</script>";
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
    padding-top: 60px; 
    height: 100vh; 
}

/* Form Container */
.form-container {
    background-color: #ffffff;
    padding: 30px 40px;
    border-radius: 15px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 700px;
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
    margin-top: 20px; 
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
        <section id="addAppointment" class="content-section">
        <form method="POST" enctype="multipart/form-data" id="appointmentForm" class="form-container" onsubmit="return showModal(event)">
        <div class="form-title">Add New Appointment</div>
           
        <div class="form-columns">
        
        <div class="form-column">
                <label for="serviceType" class="form-label">Service Type:</label>
                <select name="serviceType" id="serviceType" class="form-input" required>
                    <?php
                    $stmt = $dbh->prepare("SELECT service_id, service_name FROM services");
                    $stmt->execute();
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value='{$row['service_id']}'>{$row['service_name']}</option>";
                    }
                    ?>
                </select>

                <label for="vehicle_details" class="form-label">Vehicle Details:</label>
                <input type="text" name="vehicle_details" id="vehicle_details" class="form-input" required placeholder="Enter vehicle details">

                <label for="date" class="form-label">Date:</label>
                <input type="date" name="date" id="date" class="form-input" required>

                <label>Time:</label>
                <select name="time" id="time" class="form-input" required>
                <option value="">Select a time</option>
                </select>
            </div>
                <div class="form-column">
                <label for="optional_message" class="form-label">Message (Optional):</label>
                <textarea name="optional_message" id="optional_message" class="form-input" placeholder="Describe any issue or additional request (optional)"></textarea>

                <label for="vehicle_photo" class="form-label">Attach Vehicle Photo (Optional):</label>
                <input type="file" name="vehicle_photo" id="vehicle_photo" class="form-input" accept="image/*">

            </div>
        </div>
                <button type="submit" class="btn btn-primary">Book Appointment</button>
                <a href="customer.php" class="back-btn">← Back to Appointments</a>
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
    const timeSelect = document.getElementById('time');

    // Define holiday dates (YYYY-MM-DD)
    const holidays = [
      '2025-01-01', // New Year's Day
      '2025-12-25', // Christmas
      '2025-04-09', // Day of Valor
      '2025-05-01', // Labor Day
      '2025-06-12', // Independence Day
      '2025-08-25', // National Hero Day
      '2025-11-30', // Bonifacio Day
      '2025-12-30' // Rizal Day
    ];

    const startHour = 8, endHour = 17, interval = 30;

    function generateSlots() {
      const slots = [];
      for (let hour = startHour; hour < endHour; hour++) {
        for (let min = 0; min < 60; min += interval) {
          slots.push(`${String(hour).padStart(2, '0')}:${String(min).padStart(2, '0')}`);
        }
      }
      return slots;
    }

    function formatDisplay(time24) {
      const [h, m] = time24.split(':');
      const hour = parseInt(h);
      const ampm = hour <12 ? 'AM' : 'PM';
      const displayHour = hour % 12 || 12;
      return `${displayHour}:${m} ${ampm}`;
    }

    async function updateTimeOptions() {
      const date = dateInput.value;
      const serviceId = serviceSelect.value;
      timeSelect.innerHTML = '<option value="">Select a time</option>';
      if (!date || !serviceId) return;

      // Block holidays and Sundays
      const selectedDate = new Date(date);
      const isSunday = selectedDate.getDay() === 0; // Sunday = 0
      const isHoliday = holidays.includes(date);

      if (isSunday || isHoliday) {
        alert('Sorry, appointments are not available on Sundays or holidays.');
        return;
      }

      // Fetch booked slots for the selected date and service
      const res = await fetch(`?fetch_times=1&date=${date}&service_id=${serviceId}`);
      const booked = await res.json();
      const slots = generateSlots();

      // Populate time options
      slots.forEach(slot => {
        const option = document.createElement('option');
        option.value = slot;
        option.textContent = booked.includes(slot) ? `${formatDisplay(slot)} (Booked)` : formatDisplay(slot);
        if (booked.includes(slot)) option.disabled = true;
        timeSelect.appendChild(option);
      });
    }

    // Set minimum date to today
    const today = new Date();
    const minDate = today.toISOString().split('T')[0];
    dateInput.min = minDate;

    dateInput.addEventListener('change', updateTimeOptions);
    serviceSelect.addEventListener('change', updateTimeOptions);
});
</script>

<script src="customer.js"></script>
    <!-- Confirmation Modal -->
<div class="modal-overlay" id="confirmModal">
  <div class="modal-content">
    <h3>Confirm Booking</h3>
    <p>Are you sure you want to book this appointment?</p>
    <button onclick="submitAppointment()">Yes, Book It</button>
    <button onclick="closeModal()">Cancel</button>
  </div>
</div>

</body>
</html>
