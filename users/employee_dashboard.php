<?php
session_start();
require __DIR__ . '/../db_connect/connect.php';

// // Make sure user is logged in
// if (!isset($_SESSION['user'])) {
//     header("Location: login.php");
//     exit;
// }

$userId = $_SESSION['user']['user_id'];

// Fetch request types for dropdown
$stmt = $conn->prepare("SELECT request_type_id, name FROM request_type ORDER BY name ASC");
$stmt->execute();
$requestTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle new request submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_request'])) {
    $requestTypeId = intval($_POST['request_type_id']);
    $description = trim($_POST['description']);

    if ($requestTypeId > 0 && $description !== '') {
        $stmt = $conn->prepare("INSERT INTO service_request (user_id, request_type_id, description) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $requestTypeId, $description]);
        header("Location: employee_dashboard.php?success=1");
        exit;
    } else {
        $error = "Please select a request type and provide a description.";
    }
}

// Fetch requests by this user with assigned staff name (full_name)
$stmt = $conn->prepare("
    SELECT 
        sr.*, 
        rt.name AS request_type_name,
        u_staff.full_name AS staff_name
    FROM service_request sr
    JOIN request_type rt ON sr.request_type_id = rt.request_type_id
    LEFT JOIN user u_staff ON sr.staff_id = u_staff.user_id
    WHERE sr.user_id = ?
    ORDER BY sr.created_at DESC
");
$stmt->execute([$userId]);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Employee Dashboard</title>
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
    <h4 class="text-center mb-4">Employee Panel</h4>
    <a href="employee_dashboard.php" class="active"><i class="bi bi-house-door-fill me-2"></i>Dashboard</a>
    <a href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
</div>

<!-- Content -->
<div class="content">
    <h3 class="mb-4">Create New Request</h3>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php elseif (isset($_GET['success'])): ?>
        <div class="alert alert-success">Request created successfully!</div>
    <?php endif; ?>

    <form method="POST" class="mb-5">
        <div class="mb-3">
            <label for="request_type_id" class="form-label">Request Type</label>
            <select name="request_type_id" id="request_type_id" class="form-select" required>
                <option value="">-- Select Request Type --</option>
                <?php foreach ($requestTypes as $type): ?>
                    <option value="<?= $type['request_type_id'] ?>"><?= htmlspecialchars($type['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea name="description" id="description" class="form-control" rows="4" placeholder="Provide details about your request" required></textarea>
        </div>
        <button type="submit" name="create_request" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Submit Request</button>
    </form>

    <h3 class="mb-4">My Requests</h3>
    <?php if (count($requests) === 0): ?>
        <p class="text-muted">You have not made any requests yet.</p>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Request Type</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Assigned Staff</th> <!-- New column -->
                    <th>Created At</th>
                    <th>Updated At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $req): ?>
                <tr>
                    <td><?= htmlspecialchars($req['request_type_name']) ?></td>
                    <td><?= nl2br(htmlspecialchars($req['description'])) ?></td>
                    <td><?= htmlspecialchars($req['status']) ?></td>
                    <td>
                        <?= $req['staff_name'] !== null 
                            ? htmlspecialchars($req['staff_name']) 
                            : '<span class="text-muted fst-italic">Not Assigned</span>' ?>
                    </td>
                    <td><?= htmlspecialchars($req['created_at']) ?></td>
                    <td><?= htmlspecialchars($req['updated_at']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
