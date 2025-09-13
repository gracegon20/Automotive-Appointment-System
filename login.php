<?php
include 'connect.php';
session_start();

$loginError = '';
$loginSuccess = ''; // <-- Add this

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $dbh->prepare("SELECT user_id, password, role FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Password check (use password_verify() in production)
        if ($password === $user['password']) {
            $_SESSION['username'] = $username;
            $_SESSION['password'] = $password;
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'];

            // Instead of redirect, we set success URL here
            $loginSuccess = ($user['role'] === 'admin') ? 'admin.php' : 'customerdashboard.php';
        } else {
            $loginError = 'Incorrect password.';
        }
    } else {
        $loginError = 'User not found.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Auto Repair Shop</title>
    <link rel="icon" type="image/png" href="Images/carlogo.png">
    <link rel="shortcut icon" type="image/png" href="Images/carlogo.png">
    <link rel="stylesheet" href="css/loginstyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   
<style>
.modal {
  display: none;
  position: fixed;
  z-index: 1000;
  left: 0; top: 0;
  width: 100%; height: 100%;
  background-color: rgba(0,0,0,0.5);
  justify-content: center;
  align-items: center;
  animation: fadeIn 0.3s ease;
}
.modal-content {
  background-color: #fff;
  padding: 30px 40px;
  border-radius: 10px;
  text-align: center;
  box-shadow: 0 0 10px rgba(0,0,0,0.2);
  font-size: 18px;
}
@keyframes fadeIn {
  from { opacity: 0; } to { opacity: 1; }
}

/* Loading overlay */
#loadingOverlay {
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(0,0,0,0.6);
  z-index: 2000;
  display: none;
  justify-content: center;
  align-items: center;
  flex-direction: column;
  color: white;
  font-size: 24px;
  animation: fadeIn 0.5s ease;
}
#loadingOverlay i {
  font-size: 50px;
  margin-bottom: 20px;
}
</style>

</head>
<body>
    <header class="login-header">
        <div class="logo-container">
            <img src="Images/FrontEndPictures/logo.webp" alt="AutoFIX Logo">
            <h2>AutoFIX</h2>
        </div>
        <nav>
            <ul>
                <li><a href="home.php">Home</a></li>
                <li><a href="signup.php">Sign Up</a></li>
            </ul>
        </nav>
    </header>

    <!-- Loading overlay -->
    <div id="loadingOverlay">
      <div class="overlay-content">
        <i class="fas fa-spinner fa-spin"></i>
        <p>Logging in...</p>
      </div>
    </div>

    <div class="login-container">
        <h1>AutoFIX Login</h1>

        <?php if (!empty($loginError)) : ?>
            <p class="error-message" style="color:red;"><?= htmlspecialchars($loginError) ?></p>
        <?php endif; ?>

        <form id="loginForm" method="POST">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" placeholder="Enter your username" required>

            <label for="password">Password:</label>
            <div class="password-wrapper">
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
                <span class="toggle-password" onclick="togglePassword()">
                    <i class="fas fa-eye"></i>
                </span>
            </div>
            <button type="submit" id="loginButton">
                <span id="buttonText">Login</span>
                <span id="spinner" style="display:none; margin-left:10px;">
                    <i class="fas fa-spinner fa-spin"></i>
                </span>
            </button>
        </form>
    </div>

    <!-- Modal for success -->
<div id="successModal" class="modal">
  <div class="modal-content">
    <p>Login successfully! Redirecting...</p>
  </div>
</div>

<!-- Modal for error -->
<div id="errorModal" class="modal">
  <div class="modal-content">
    <p><?= htmlspecialchars($loginError) ?></p>
  </div>
</div>

<script>
function togglePassword() {
    const pwd = document.getElementById("password");
    const icon = document.querySelector(".toggle-password i");
    if (pwd.type === "password") {
        pwd.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    } else {
        pwd.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
}

const successModal = document.getElementById('successModal');
const errorModal = document.getElementById('errorModal');
const loadingOverlay = document.getElementById('loadingOverlay');

document.getElementById('loginForm').addEventListener('submit', function(event) {
    loadingOverlay.style.display = 'flex';
});

<?php if (!empty($loginSuccess)) : ?>
    loadingOverlay.style.display = 'none'; // Hide loading
    successModal.style.display = 'flex'; // Show success modal
    setTimeout(() => {
        window.location.href = '<?= $loginSuccess ?>';
    }, 3000); // Wait 3 seconds then redirect
<?php elseif (!empty($loginError)) : ?>
    loadingOverlay.style.display = 'none'; // Hide loading
    errorModal.style.display = 'flex'; // Show error modal
    setTimeout(() => {
        errorModal.style.display = 'none';
    }, 3000); // Close error after 3 seconds
<?php endif; ?>
</script>
</body>
</html>
