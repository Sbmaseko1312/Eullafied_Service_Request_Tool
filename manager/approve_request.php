<?php
session_start();
require_once __DIR__ . '/../db_connect/connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 2 || !isset($_GET['id'])) {
    header("Location: login.php");
    exit();
}

$managerId = $_SESSION['user_id'];
$requestId = $_GET['id'];

// Get request details
$stmt = $conn->prepare("
    SELECT r.*, t.name AS type_name, u.full_name
    FROM service_requests r
    JOIN request_types t ON r.request_type_id = t.request_type_id
    JOIN users u ON r.user_id = u.user_id
    WHERE r.request_id = ? AND r.status = 'Submitted'
");
$stmt->execute([$requestId]);
$request = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$request) {
    echo "<p>Invalid or already processed request.</p>";
    exit();
}

// Handle approval submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $decision = $_POST['decision'];
    $note = $_POST['note'];

    $conn->beginTransaction();

    $conn->prepare("INSERT INTO approvals (request_id, manager_id, decision, decision_note) VALUES (?, ?, ?, ?)")
         ->execute([$requestId, $managerId, $decision, $note]);

    $newStatus = ($decision === 'Approved') ? 'Approved' : 'Rejected';

    $conn->prepare("UPDATE service_requests SET status = ? WHERE request_id = ?")
         ->execute([$newStatus, $requestId]);

    $conn->commit();

    echo "<p style='color:green;'>Request has been $decision.</p>";
    echo "<a href='pending_approvals.php'>← Back to Approvals</a>";
    exit();
}
?>

<h2>Review Request</h2>

<p><strong>Employee:</strong> <?= $request['full_name'] ?></p>
<p><strong>Request Type:</strong> <?= $request['type_name'] ?></p>
<p><strong>Description:</strong><br><?= nl2br($request['description']) ?></p>
<p><strong>Date Submitted:</strong> <?= $request['created_at'] ?></p>

<form method="post">
    <label for="decision">Decision:</label><br>
    <select name="decision" required>
        <option value="">--Select--</option>
        <option value="Approved">Approve</option>
        <option value="Rejected">Reject</option>
    </select><br><br>

    <label for="note">Manager Note (optional):</label><br>
    <textarea name="note" rows="3"></textarea><br><br>

    <button type="submit">Submit Decision</button>
</form>

<a href="pending_approvals.php">← Back to Pending Approvals</a>
