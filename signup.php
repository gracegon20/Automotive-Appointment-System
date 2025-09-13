<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'connect.php'; // Include the database connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    echo "<pre>";
    print_r($_POST); // Print submitted form data
    echo "</pre>";

    $name = $_POST['name'];
    $address = $_POST['address'];
    $username = $_POST['username'];
    $phone = $_POST['phone'];
    $password = $_POST['password']; // Store the password as plain text

    try {
        $sql = "INSERT INTO users (name, address, username, phone, password) VALUES (:name, :address, :username, :phone, :password)";
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':password', $password);

        if ($stmt->execute()) {
            // Success logic handled in JavaScript
        } else {
            echo "<script>alert('Error: Could not register.');</script>";
        }
    } catch (PDOException $e) {
        echo "<script>alert('Database error: " . $e->getMessage() . "');</script>";
        error_log("Database error: " . $e->getMessage()); // Log the error for debugging
    }
}

// Fetch and display all users from the database
try {
    $fetchSql = "SELECT * FROM users";
    $fetchStmt = $dbh->query($fetchSql);
    $users = $fetchStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<script>alert('Error fetching users: " . $e->getMessage() . "');</script>";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Auto Repair Shop</title>
    <link rel="icon" type="image/png" href="Images/carlogo.png">
    <link rel="shortcut icon" type="image/png" href="Images/carlogo.png">
    <link rel="stylesheet" href="css/signupstyle.css">
</head>
<body>


    <header class="signup-header">
        <div class="logo-container">
            <img src="Images/FrontEndPictures/logo.webp" alt="AutoFIX Logo">
            <h2>AutoFIX</h2>
        </div>
        <nav>
            <ul>
                <li><a href="home.php">Home</a></li>
                <li><a href="login.php">Login</a></li>
            </ul>
        </nav>
    </header>
  <div class="signup-container">
    <h1>Register for AutoFIX</h1>
    <form id="signupForm" method="POST">
      <div class="form-columns">
        <div class="form-column">
          <label for="name">Full Name:</label>
          <input type="text" id="name" name="name" placeholder="Enter your full name" required>

          <label for="address">Address:</label>
          <input type="address" id="address" name="address" placeholder="Enter your address" required>
        
          <label for="phone">Phone Number:</label>
          <input type="tel" id="phone" name="phone" placeholder="Enter your phone number" pattern="[0-9]{10}" required>

        
        </div>

        <div class="form-column">
          <label for="username">Username:</label>
          <input type="text" id="username" name="username" placeholder="Enter your username" required>
          
          <label for="password">Password:</label>
          <div class="password-wrapper">
              <input type="password" id="password" name="password" placeholder="Enter your password" required>
              <span class="toggle-password" onclick="togglePassword()">
                  <i class="fas fa-eye"></i>
              </span>
          </div>
        </div>
      </div>

      <button type="submit" onclick="handleSignup(event)">Sign Up</button> <!-- Add onclick to trigger JavaScript -->
      <p>Already have an account? <a href="login.php">Login here</a></p>
    </form>
  </div>

    <!-- Modal for success message -->
    <div id="successModal" class="modal" style="display: none;"> <!-- Ensure modal is hidden initially -->
        <div class="modal-content">
            <h2>Congratulations!</h2>
            <p>You have registered successfully. Press the button below to continue.</p>
            <button onclick="redirectToDashboard()">Continue</button>
        </div>
    </div>

    <script src="javascripts/signup.js"></script>
    <script>
        function redirectToDashboard() {
            window.location.href = 'login.php';
        }

        function handleSignup(event) {
            event.preventDefault(); // Prevent form submission

            const form = document.getElementById('signupForm');
            const formData = new FormData(form);

            fetch('signup_handler.php', {
                method: 'POST',
                body: formData,
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const successModal = document.getElementById('successModal');
                        successModal.style.display = 'flex'; // Show modal
                    } else {
                        alert(data.message); // Show error message
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again later.');
                });
        }
    </script>
</body>
</html>
