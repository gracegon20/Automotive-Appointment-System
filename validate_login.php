<?php
include 'connect.php';
header('Content-Type: application/json');
session_start();

$input = json_decode(file_get_contents('php://input'), true);
$username = $input['username'] ?? '';
$password = $input['password'] ?? '';

// Debugging logs
error_log("Received username: $username");
error_log("Received password: $password");

// Validate user credentials
$stmt = $dbh->prepare('SELECT password FROM users WHERE username = ?');
$stmt->bindValue(1, $username, PDO::PARAM_STR);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if ($result) {
    error_log("Query result: " . json_encode($result));
    $row = $result;
    // Check if username is "admin" and password matches
    if ($username === 'admin' && $password === $row['password']) {
        $_SESSION['user_id'] = $username; // Set session for admin
        echo json_encode(['success' => true, 'message' => 'Successfully logged in!', 'redirect' => 'admin.php']);
    } elseif ($password === $row['password']) {
        $_SESSION['user_id'] = $username; // Set session for customer
        echo json_encode(['success' => true, 'message' => 'Successfully logged in!', 'redirect' => 'customerdashboard.php']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Username/Password is incorrect. Try again.']);
    }
} else {
    error_log("No matching user found.");
    echo json_encode(['success' => false, 'message' => 'Username/Password is incorrect. Try again.']);
}

// Release the statement resources
$stmt = null;
// Close the database connection
$dbh = null;
?>

