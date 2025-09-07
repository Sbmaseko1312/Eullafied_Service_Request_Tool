<?php
session_start();
require_once __DIR__ . '/../db_connect/connect.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$requestId = $_GET['id'];

$stmt = $conn->prepare("SELECT r.*, t.name AS type_name
                        FROM service_requests r
                        JOIN request_types t ON r.request_type_id = t.request_type_id
                        WHERE r.request_id = ? AND r.user_id = ?");
$stmt->execute([$requestId, $userId]);
$request = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$request) {
    echo "<p style='color:red;'>Request not found or access denied.</p>";
    exit();
}
?>

<h2>Request Details</h2>

<p><strong>Request ID:</strong> <?= $request['request_id'] ?></p>
<p><strong>Type:</strong> <?= $request['type_name'] ?></p>
<p><strong>Status:</strong> <?= $request['status'] ?></p>
<p><strong>Description:</strong> <?= nl2br($request['description']) ?></p>
<p><strong>Created At:</strong> <?= $request['created_at'] ?></p>
<p><strong>Last Updated:</strong> <?= $request['updated_at'] ?></p>

<a href="my_requests.php">← Back to My Requests</a>
