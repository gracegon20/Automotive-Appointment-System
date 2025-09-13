<?php
include 'connect.php'; // Ensure this provides $dbh (PDO instance)

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input
    $name = trim($_POST['name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    // Basic validation (server-side)
    if (empty($name) || empty($address) || empty($username) || empty($phone) || empty($password)) {
        $response['message'] = 'All fields are required.';
    } else {
        try {
            // Check if username already exists
            $checkStmt = $dbh->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
            $checkStmt->bindParam(':username', $username);
            $checkStmt->execute();

            if ($checkStmt->fetchColumn() > 0) {
                $response['message'] = 'Username already exists. Please choose another one.';
            } else {
                // Insert new user
                $sql = "INSERT INTO users (name, address, username, phone, password) 
                        VALUES (:name, :address, :username, :phone, :password)";
                $stmt = $dbh->prepare($sql);
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':address', $address);
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':phone', $phone);
                $stmt->bindParam(':password', $password); // You should hash this

                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Registration successful!';
                } else {
                    $response['message'] = 'Error: Could not register. Please try again.';
                }
            }
        } catch (PDOException $e) {
            $response['message'] = 'Database error: ' . $e->getMessage();
        }
    }
}

header('Content-Type: application/json');
echo json_encode($response);

?>