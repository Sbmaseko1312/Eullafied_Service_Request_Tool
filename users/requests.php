<?php
session_start();
require __DIR__ . '/../db_connect/connect.php';

// Uncomment to enforce admin access
// if (!isset($_SESSION['user']) || $_SESSION['user']['role_id'] != 1) {
//     echo "<script>alert('Access denied.'); window.location.href='login.php';</script>";
//     exit;
// }

// Delete request
if (isset($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM service_request WHERE request_id = ?");
    $stmt->execute([intval($_GET['delete'])]);
    header("Location: requests.php");
    exit;
}

// Filter status
$filterStatus = $_GET['status'] ?? '';

// Get all distinct statuses for filter dropdown
$statuses = $conn->query("SELECT DISTINCT status FROM service_request")->fetchAll(PDO::FETCH_COLUMN);

// Fetch requests with optional status filter
$query = "SELECT * FROM service_request WHERE 1=1";
if ($filterStatus !== '') {
    $query .= " AND status = " . $conn->quote($filterStatus);
}
$query .= " ORDER BY created_at DESC";

$requests = $conn->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Service Requests</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { display: flex; min-height: 100vh; }
        .sidebar {
            width: 240px; background-color: #343a40; color: white; padding-top: 1rem;
        }
        .sidebar a {
            color: white; text-decoration: none; display: block; padding: 0.75rem 1rem;
        }
        .sidebar a:hover, .sidebar a.active {
            background-color: #495057;
        }
        .content {
            flex-grow: 1; padding: 2rem; background-color: #f8f9fa;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h4 class="text-center mb-4">Admin Panel</h4>
    <a href="admin_dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
    <a href="staff.php"><i class="bi bi-people-fill me-2"></i>Staff</a>
    <a href="#" class="active"><i class="bi bi-list-task me-2"></i>Requests</a>
    <a href="departments.php"><i class="bi bi-diagram-3 me-2"></i>Departments</a>
    <a href="role.php"><i class="bi bi-shield-lock-fill me-2"></i>Roles</a>
    <a href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
</div>

<!-- Content -->
<div class="content">
    <h3 class="mb-4">Service Requests</h3>

    <!-- Filter -->
    <form method="GET" class="row g-3 mb-4 align-items-center">
        <div class="col-md-4">
            <select name="status" class="form-select">
                <option value="">All Statuses</option>
                <option value="Pending Approval">Pending Approval</option>
                <option value="Approved">Approved</option>
                <option value="Pending Assistance">Pending Assistance</option>
                <option value="Solved">Solved</option>
                <?php foreach ($statuses as $status): ?>
                    <option value="<?= htmlspecialchars($status) ?>" <?= ($filterStatus === $status) ? 'selected' : '' ?>>
                        <?= htmlspecialchars(ucfirst($status)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-dark w-100"><i class="bi bi-filter"></i> Filter</button>
        </div>
        <div class="col-md-2">
            <a href="requests.php" class="btn btn-secondary w-100"><i class="bi bi-x-circle"></i> Clear</a>
        </div>
    </form>

    <!-- Requests Table -->
    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Request ID</th>
                    <th>User ID</th>
                    <th>Request Type ID</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Updated At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (count($requests) > 0): ?>
                <?php foreach ($requests as $req): ?>
                <tr>
                    <td><?= htmlspecialchars($req['request_id']) ?></td>
                    <td><?= htmlspecialchars($req['user_id']) ?></td>
                    <td><?= htmlspecialchars($req['request_type_id']) ?></td>
                    <td><?= htmlspecialchars($req['description']) ?></td>
                    <td><?= htmlspecialchars(ucfirst($req['status'])) ?></td>
                    <td><?= htmlspecialchars($req['created_at']) ?></td>
                    <td><?= htmlspecialchars($req['updated_at']) ?></td>
                    <td>
                        <a href="?delete=<?= $req['request_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this request?')">
                            <i class="bi bi-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="8" class="text-center text-muted">No requests found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
