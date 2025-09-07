<?php
session_start();
require __DIR__ . '/../db_connect/connect.php';

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo "Unauthorized";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestId = $_POST['request_id'] ?? null;
    $newStatus = $_POST['status'] ?? null;
    $staffId = $_SESSION['user']['user_id'];

    if ($requestId && $newStatus && in_array($newStatus, ['Pending Assistance', 'Solved'])) {
        $stmt = $conn->prepare("UPDATE service_request SET status = ?, updated_at = NOW(), staff_id = ? WHERE request_id = ?");
        $success = $stmt->execute([$newStatus, $staffId, $requestId]);

        if ($success) {
            echo "Status updated successfully";
        } else {
            echo "Failed to update status";
        }
    } else {
        echo "Invalid request";
    }
} else {
    echo "Invalid method";
}
