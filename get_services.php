<?php
include 'connect.php';

header('Content-Type: application/json');

try {
    $query = "SELECT service_name FROM services";
    $result = $conn->query($query);

    $services = array();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $services[] = $row['service_name'];
        }
        echo json_encode(['success' => true, 'services' => $services]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No services available.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
