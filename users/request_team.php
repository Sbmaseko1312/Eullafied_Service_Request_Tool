<?php
session_start();
require __DIR__ . '/../db_connect/connect.php';

// ✅ Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user']['user_id'];
$statusFilter = $_GET['status'] ?? '';

// ✅ Get manager's department
$stmt = $conn->prepare("SELECT department_id FROM user WHERE user_id = ?");
$stmt->execute([$userId]);
$deptId = $stmt->fetchColumn();

// ✅ Fetch service requests for team members in the same department
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
    WHERE u.department_id = :dept
";

if ($statusFilter !== '') {
    $sql .= " AND sr.status = :status";
}

$sql .= " ORDER BY sr.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':dept', $deptId);
if ($statusFilter !== '') {
    $stmt->bindParam(':status', $statusFilter);
}
$stmt->execute();
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ✅ Status options for filtering
$statuses = ['Pending Approval', 'Approved', 'Pending Assistance', 'Solved'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Team Service Requests</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
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
    <h4 class="text-center mb-4">Manager Panel</h4>
    <a href="manager_dashboard.php" class="active"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
    <a href="create_request.php"><i class="bi bi-plus-circle me-2"></i>Create Request</a>
    <a href="request_team.php"><i class="bi bi-list-task me-2"></i>View Requests</a>
    <a href="team_members.php"><i class="bi bi-people-fill me-2"></i>Team Members</a>
    <a href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
</div>

<!-- Main Content -->
<div class="main-content">
    <h3 class="mb-4">Service Requests from Your Team</h3>

    <!-- Status Filter -->
    <form method="get" class="mb-3">
        <select name="status" class="form-select w-auto d-inline" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <?php foreach ($statuses as $s): ?>
                <option value="<?= htmlspecialchars($s) ?>" <?= $statusFilter === $s ? 'selected' : '' ?>><?= htmlspecialchars($s) ?></option>
            <?php endforeach; ?>
        </select>
    </form>

    <!-- Requests Table -->
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
                                <span class="badge 
                                    <?= $req['status'] === 'Solved' ? 'bg-success' : 
                                       ($req['status'] === 'Pending Assistance' ? 'bg-warning text-dark' : 
                                       ($req['status'] === 'Approved' ? 'bg-primary' : 'bg-secondary')) ?>">
                                    <?= htmlspecialchars($req['status']) ?>
                                </span>
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
</body>
</html>
