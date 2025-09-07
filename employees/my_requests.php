<?php
session_start();
require_once __DIR__ . '/../db_connect/connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT r.request_id, t.name AS request_type, r.status, r.created_at
                        FROM service_requests r
                        JOIN request_types t ON r.request_type_id = t.request_type_id
                        WHERE r.user_id = ?
                        ORDER BY r.created_at DESC");
$stmt->execute([$userId]);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>My IT Service Requests</h2>

<table border="1" cellpadding="5">
    <tr>
        <th>ID</th>
        <th>Type</th>
        <th>Status</th>
        <th>Created</th>
        <th>View</th>
    </tr>
    <?php foreach ($requests as $req): ?>
    <tr>
        <td><?= $req['request_id'] ?></td>
        <td><?= $req['request_type'] ?></td>
        <td><?= $req['status'] ?></td>
        <td><?= $req['created_at'] ?></td>
        <td><a href="request_detail.php?id=<?= $req['request_id'] ?>">View</a></td>
    </tr>
    <?php endforeach; ?>
</table>

<a href="dashboard.php">← Back to Dashboard</a>
