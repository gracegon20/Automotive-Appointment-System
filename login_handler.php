<?php
include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true); // Accept JSON input
    $username = $input['username'];
    $password = $input['password'];

    // Validate user credentials
    $stmt = $dbh->prepare("SELECT role FROM users WHERE username = :username AND password = :password");
    $stmt->bindValue(':username', $username, PDO::PARAM_STR);
    $stmt->bindValue(':password', $password, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $user = $result;
        if ($user['role'] === 'admin') {
            echo json_encode(['success' => true, 'message' => 'Welcome Admin!', 'redirect' => 'admin.php']);
        } elseif ($user['role'] === 'customer') {
            echo json_encode(['success' => true, 'message' => 'Welcome Customer!', 'redirect' => 'customer.php']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid role.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Incorrect password or username']);
    }

    unset($stmt);
    $dbh = null;
}
?>
