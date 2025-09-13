<?php
include 'connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm_update'])) {
    $name = $_POST['name'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $username = $_POST['username'];
    $password = $_POST['password'];

     // Basic validation
    if (empty($name) || empty($address) || empty($phone) || empty($username) || empty($password)) {
        $error = "Please fill in all required fields.";
    } else {
        $update = $dbh->prepare("UPDATE users SET name = :name, address = :address, phone = :phone, username = :username, password = :password WHERE user_id = :user_id");
        $update->bindParam(':name', $name);
        $update->bindParam(':address', $address);
        $update->bindParam(':phone', $phone);
        $update->bindParam(':username', $username);
        $update->bindParam(':password', $password);
        $update->bindParam(':user_id', $user_id, PDO::PARAM_INT);

        if ($update->execute()) {
            $success = "Profile updated successfully!";
        } else {
            $error = "Failed to update profile.";
        }
    }
}

// Fetch user info
$stmt = $dbh->prepare("SELECT name, address, phone, username, password FROM users WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Profile</title>
    <link rel="icon" type="image/png" href="Images/carlogo.png">
    <link rel="shortcut icon" type="image/png" href="Images/carlogo.png">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: beige;
            padding: 40px;
        }
        .form-container {
            background-color: white;
            max-width: 500px;
            margin: auto;
            padding: 30px 40px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        h2 {
            text-align: center;
            color: #004080;
        }
        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="password"] {
            width: calc(100% - 30px);
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        .password-container {
            display: flex;
            align-items: center;
        }
        .password-container i {
            margin-left: 10px;
            cursor: pointer;
            color: #666;
        }
        button {
            margin-top: 20px;
            width: 100%;
            padding: 12px;
            background-color: #004080;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background-color: #002f5f;
        }
        .back-btn {
            display: block;
            text-align: center;
            margin-top: 20px;
            text-decoration: none;
            color: #004080;
            font-weight: bold;
        }
        .back-btn:hover {
            text-decoration: underline;
        }
        .message {
            text-align: center;
            color: green;
            margin-top: 10px;
        }
        .error {
            text-align: center;
            color: red;
            margin-top: 10px;
        }
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 999;
            padding-top: 150px;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: white;
            margin: auto;
            padding: 20px;
            border: 1px solid #888;
            width: 400px;
            border-radius: 10px;
            text-align: center;
        }
        .modal-buttons {
            margin-top: 20px;
            display: flex;
            justify-content: space-around;
        }
        .modal-buttons button {
            width: 100px;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Update Your Profile</h2>

    <?php if (!empty($success)) echo "<div class='message'>$success</div>"; ?>
    <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>

    <?php if ($user): ?>
    <form id="updateForm" method="POST">
    <input type="hidden" name="confirm_update" value="1">
    <label for="name">Full Name</label>
    <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>

    <label for="address">Address</label>
    <input type="text" name="address" value="<?= htmlspecialchars($user['address']) ?>" required>

    <label for="phone">Phone</label>
    <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required>

    <label for="username">Username</label>
    <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>

    <label for="password">Password</label>
    <div class="password-container">
        <input type="password" id="password" name="password" value="<?= htmlspecialchars($user['password']) ?>" required>
    </div>

    <button type="button" onclick="openModal()">Update Profile</button>
    </form>
<?php else: ?>
    <div class="error">User not found. Cannot display the form.</div>
<?php endif; ?>

    <a href="profile.php" class="back-btn">← Back to Profile</a>
</div>

<!-- Confirmation Modal -->
<div id="confirmModal" class="modal">
  <div class="modal-content">
    <h3>Confirm Update</h3>
    <p>Are you sure you want to update your profile?</p>
    <div class="modal-buttons">
      <button onclick="submitForm()">Yes</button>
      <button onclick="closeModal()">Cancel</button>
    </div>
  </div>
</div>

<!-- Font Awesome CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>

<script>
    const passwordField = document.querySelector('#password');

    function openModal() {
        document.getElementById("confirmModal").style.display = "block";
    }

    function closeModal() {
        document.getElementById("confirmModal").style.display = "none";
    }

    function submitForm() {
        document.getElementById("updateForm").submit();
    }

    // Close modal on outside click
    window.onclick = function(event) {
        const modal = document.getElementById("confirmModal");
        if (event.target === modal) {
            modal.style.display = "none";
        }
    }
</script>

</body>
</html>
