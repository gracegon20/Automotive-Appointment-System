<?php
session_start();
include 'connect.php'; // Ensure this sets up PDO as $dbh

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

$id = $_POST['id'] ?? $_GET['id'] ?? null;

if (!$id) {
    echo "Missing appointment ID.";
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Check ownership first
    $check = $dbh->prepare("SELECT * FROM appointments WHERE id = :id AND user_id = :user_id");
    $check->execute([':id' => $id, ':user_id' => $user_id]);

    if ($check->rowCount() === 0) {
        die("Appointment not found or you do not have permission to cancel it.");
    }

    // Delete or mark as cancelled
    $cancel = $dbh->prepare("DELETE FROM appointments WHERE id = :id");
    $cancel->execute([':id' => $id]);

    header("Location: customer.php?cancel=success");
    exit;
} catch (PDOException $e) {
    error_log("Cancellation error: " . $e->getMessage());
    die("Error cancelling appointment.");
}
