<?php
session_start();
require __DIR__ . '/../db_connect/connect.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$managerId = $_SESSION['user']['user_id'];
$selectedStatus = $_GET['status'] ?? null;

if (!$selectedStatus) {
    echo "<script>alert('Status is required.'); window.location.href='manager_dashboard.php';</script>";
    exit;
}

// Handle status update from dropdown
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id'], $_POST['new_status'])) {
    $requestId = $_POST['request_id'];
    $newStatus = $_POST['new_status'];

    if (in_array($newStatus, ['Approved', 'Declined'])) {
        $stmt = $conn->prepare("UPDATE service_request SET status = ?, updated_at = NOW(), staff_id = ? WHERE request_id = ?");
        $stmt->execute([$newStatus, $managerId, $requestId]);
        header("Location: request.php?status=" . urlencode($selectedStatus));
        exit;
    }
}

// Get manager's department
$stmt = $conn->prepare("SELECT department_id FROM user WHERE user_id = ?");
$stmt->execute([$managerId]);
$deptId = $stmt->fetchColumn();

// Fetch requests for department and selected status
$stmt = $conn->prepare("
    SELECT sr.*, rt.name AS request_type_name, u.full_name AS requester_name
    FROM service_request sr
    JOIN user u ON sr.user_id = u.user_id
    JOIN request_type rt ON sr.request_type_id = rt.request_type_id
    WHERE u.department_id = ? AND sr.status = ?
    ORDER BY sr.created_at DESC
");
$stmt->execute([$deptId, $selectedStatus]);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Requests - <?= htmlspecialchars($selectedStatus) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 240px;
            background-color: #343a40;
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
            background-color: #495057;
        }
        .content {
            flex-grow: 1;
            padding: 2rem;
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h4 class="text-center mb-4">Manager Panel</h4>
    <a href="manager_dashboard.php" class="active"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
    <a href="create_request.php"><i class="bi bi-plus-circle me-2"></i>Create Request</a>
    <a href="request_team.php"><i class="bi bi-list-task me-2"></i>View Requests</a>
    <a href="team_members.php"><i class="bi bi-people-fill me-2"></i>Team Members</a>
    <a href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
</div>

<!-- Content -->
<div class="content">
    <h3 class="mb-4">Requests - <?= htmlspecialchars($selectedStatus) ?></h3>

    <?php if (empty($requests)): ?>
        <p class="text-muted">No requests with status "<?= htmlspecialchars($selectedStatus) ?>" in your department.</p>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Type</th>
                    <th>Requested By</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Updated</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($requests as $req): ?>
                <tr>
                    <td><?= htmlspecialchars($req['request_type_name']) ?></td>
                    <td><?= htmlspecialchars($req['requester_name']) ?></td>
                    <td><?= nl2br(htmlspecialchars($req['description'])) ?></td>
                    <td>
                        <?php if ($req['status'] === 'Pending Approval'): ?>
                            <form method="POST" class="m-0">
                                <input type="hidden" name="request_id" value="<?= $req['request_id'] ?>">
                                <select name="new_status" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option selected disabled><?= $req['status'] ?></option>
                                    <option value="Approved">Approve</option>
                                    <option value="Declined">Decline</option>
                                </select>
                            </form>
                        <?php else: ?>
                            <?= htmlspecialchars($req['status']) ?>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($req['created_at']) ?></td>
                    <td><?= htmlspecialchars($req['updated_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <a href="manager_dashboard.php" class="btn btn-secondary mt-3"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
</div>

</body>
</html>
