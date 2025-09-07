<?php
session_start();
require_once __DIR__ . '/../db_connect/connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 2) {
    header("Location: login.php");
    exit();
}

$managerId = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT a.*, r.description, r.created_at, t.name AS type_name, u.full_name
    FROM approvals a
    JOIN service_requests r ON a.request_id = r.request_id
    JOIN request_types t ON r.request_type_id = t.request_type_id
    JOIN users u ON r.user_id = u.user_id
    WHERE a.manager_id = ?
    ORDER BY a.decision_date DESC
");
$stmt->execute([$managerId]);
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>My Approval History</h2>

<?php if (count($records) === 0): ?>
    <p>No approval records found.</p>
<?php else: ?>
<table border="1" cellpadding="5">
    <tr>
        <th>ID</th>
        <th>Employee</th>
        <th>Request Type</th>
        <th>Status</th>
        <th>Note</th>
        <th>Decision Date</th>
    </tr>
    <?php foreach ($records as $rec): ?>
    <tr>
        <td><?= $rec['request_id'] ?></td>
        <td><?= $rec['full_name'] ?></td>
        <td><?= $rec['type_name'] ?></td>
        <td><?= $rec['decision'] ?></td>
        <td><?= $rec['decision_note'] ?></td>
        <td><?= $rec['decision_date'] ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

<a href="dashboard.php">← Back to Dashboard</a>
