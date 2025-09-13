<?php
session_start();
session_unset();
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Logging Out - AutoFIX</title>
    <meta http-equiv="refresh" content="5;url=home.php"> <!-- Auto-redirect after 5 seconds -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    body {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100vh;
        margin: 0;
        background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
        font-family: 'Poppins', sans-serif;
        animation: fadeIn 1s ease forwards;
    }
    .logout-container {
        text-align: center;
        background: white;
        padding: 40px 60px;
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        animation: bounceIn 1s ease;
    }
    .logout-container i {
        font-size: 60px;
        color: #28a745;
        margin-bottom: 20px;
    }
    .logout-container h1 {
        font-size: 30px;
        color: #333;
        margin-bottom: 15px;
    }
    .logout-container p {
        font-size: 18px;
        color: #555;
    }
    .spinner {
        margin-top: 20px;
        font-size: 30px;
        color:blue;
        animation: spin 2s linear infinite;
    }
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    @keyframes bounceIn {
        0% { transform: scale(0.5); opacity: 0; }
        60% { transform: scale(1.2); opacity: 1; }
        100% { transform: scale(1); }
    }
    </style>
</head>
<body>

<div class="logout-container">
    <i class="fas fa-check-circle"></i>
    <h1>Successfully Logged Out!</h1>
    <p>Redirecting you to Home Page...</p>
    <div class="spinner">
        <i class="fas fa-spinner fa-spin"></i>
    </div>
</div>

</body>
</html>
