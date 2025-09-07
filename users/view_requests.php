<?php
session_start();
require __DIR__ . '/../db_connect/connect.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user']['user_id'];
$statusFilter = $_GET['status'] ?? '';

$sql = "
    SELECT 
        sr.request_id,
        u.full_name AS requested_by,
        rt.name AS request_type,
        sr.description,
        sr.status,
        sr.created_at
    FROM service_request sr
    INNER JOIN user u ON sr.user_id = u.user_id
    INNER JOIN request_type rt ON sr.request_type_id = rt.request_type_id
";

if ($statusFilter !== '') {
    $sql .= " WHERE sr.status = :status";
}

$sql .= " ORDER BY sr.created_at DESC";

$stmt = $conn->prepare($sql);
if ($statusFilter !== '') {
    $stmt->bindParam(':status', $statusFilter);
}
$stmt->execute();
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

$statuses = ['Approved','Pending Assistance', 'Solved'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Service Requests</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 240px;
            background-color: #212529;
            color: white;
            padding-top: 1rem;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 0.75rem 1rem;
        }
        .sidebar a:hover, .sidebar a.active {
            background-color: #343a40;
        }
        .main-content {
            flex-grow: 1;
            padding: 2rem;
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
<!-- Sidebar -->
<div class="sidebar">
    <h4 class="text-center mb-4">IT Panel</h4>
    <a href="it_dashboard.php" class="active"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
    <a href="view_requests.php?status=<?= urlencode('Approved') ?>"><i class="bi bi-list-check me-2"></i>Assigned Tasks</a>
    <a href="view_requests.php?status=<?= urlencode('Pending Assistance') ?>"><i class="bi bi-hammer me-2"></i>Ongoing Support</a>
    <a href="view_requests.php?status=<?= urlencode('Solved') ?>"><i class="bi bi-check-all me-2"></i>Completed</a>
    <a href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
</div>

<!-- Main Content -->
<div class="main-content">
    <h3 class="mb-4">Service Requests</h3>

    <form method="get" class="mb-3">
        <select name="status" class="form-select w-auto" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <?php foreach ($statuses as $s): ?>
                <option value="<?= $s ?>" <?= $statusFilter === $s ? 'selected' : '' ?>><?= $s ?></option>
            <?php endforeach; ?>
        </select>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Requested By</th>
                    <th>Request Type</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($requests) > 0): ?>
                    <?php foreach ($requests as $index => $req): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($req['requested_by']) ?></td>
                            <td><?= htmlspecialchars($req['request_type']) ?></td>
                            <td><?= nl2br(htmlspecialchars($req['description'])) ?></td>
                            <td>
                                <select class="form-select form-select-sm" onchange="updateStatus(<?= $req['request_id'] ?>, this)">
                                    <?php foreach ($statuses as $status): ?>
                                        <option value="<?= $status ?>" <?= $req['status'] === $status ? 'selected' : '' ?>><?= $status ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><?= htmlspecialchars($req['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center">No service requests found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function updateStatus(requestId, select) {
    const newStatus = select.value;
    fetch('update_status.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `request_id=${requestId}&status=${encodeURIComponent(newStatus)}`
    })
    .then(response => response.text())
    .then(text => {
        console.log(text); // You can show a message or update UI here
    })
    .catch(() => alert('Failed to update status'));
}
</script>
</body>
</html>
