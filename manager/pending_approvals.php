<?php
session_start();
require_once __DIR__ . '/../db_connect/connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 2) { // 2 = MANAGER
    header("Location: login.php");
    exit();
}

$managerId = $_SESSION['user_id'];

// Fetch all requests needing approval by manager's department
$stmt = $conn->prepare("
    SELECT r.request_id, u.full_name AS employee_name, t.name AS request_type, r.description, r.created_at
    FROM service_requests r
    JOIN users u ON r.user_id = u.user_id
    JOIN request_types t ON r.request_type_id = t.request_type_id
    JOIN departments d ON u.department_id = d.department_id
    JOIN users m ON m.department_id = d.department_id
    WHERE t.requires_approval = 1 
      AND r.status = 'Submitted'
      AND m.user_id = ?
");
$stmt->execute([$managerId]);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Pending Approvals</h2>

<?php if (count($requests) === 0): ?>
    <p>No requests to approve at this time.</p>
<?php else: ?>
<table border="1" cellpadding="5">
    <tr>
        <th>ID</th>
        <th>Employee</th>
        <th>Type</th>
        <th>Description</th>
        <th>Date</th>
        <th>Action</th>
    </tr>
    <?php foreach ($requests as $req): ?>
    <tr>
        <td><?= $req['request_id'] ?></td>
        <td><?= $req['employee_name'] ?></td>
        <td><?= $req['request_type'] ?></td>
        <td><?= nl2br($req['description']) ?></td>
        <td><?= $req['created_at'] ?></td>
        <td><a href="approve_request.php?id=<?= $req['request_id'] ?>">Review</a></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

<a href="dashboard.php">← Back to Dashboard</a>
