<?php
session_start();
require __DIR__ . '/../db_connect/connect.php';

// ✅ Check login
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$loggedInUser = $_SESSION['user'];
$roleId = $loggedInUser['role_id'];
$userId = $loggedInUser['user_id'];
$deptId = $loggedInUser['department_id'];

// ✅ Fetch departments and roles (for admin filter and edit form)
$departments = $conn->query("SELECT * FROM department")->fetchAll(PDO::FETCH_ASSOC);
$roles = $conn->query("SELECT * FROM role")->fetchAll(PDO::FETCH_ASSOC);

// ✅ Prepare query based on role
if ($roleId == 1) {
    // Admin: View all staff with filters
    $filterDept = $_GET['department_id'] ?? '';
    $search = $_GET['search'] ?? '';

    $query = "SELECT u.*, d.name AS department, r.role_name
              FROM user u
              LEFT JOIN department d ON u.department_id = d.department_id
              LEFT JOIN role r ON u.role_id = r.role_id
              WHERE r.role_name IN ('Employee','Manager','IT')";

    if ($filterDept !== '') {
        $query .= " AND u.department_id = " . intval($filterDept);
    }
    if ($search !== '') {
        $query .= " AND (u.full_name LIKE " . $conn->quote("%$search%") . " OR u.email LIKE " . $conn->quote("%$search%") . ")";
    }
    $users = $conn->query($query)->fetchAll(PDO::FETCH_ASSOC);

} elseif ($roleId == 2) {
    // Manager: View only team members from the same department
    $stmt = $conn->prepare("SELECT u.*, d.name AS department, r.role_name
                             FROM user u
                             LEFT JOIN department d ON u.department_id = d.department_id
                             LEFT JOIN role r ON u.role_id = r.role_id
                             WHERE u.department_id = :dept AND u.user_id != :uid
                             ORDER BY u.full_name ASC");
    $stmt->execute(['dept' => $deptId, 'uid' => $userId]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Other roles: No access
    echo "<script>alert('Access denied.'); window.location.href='login.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $roleId == 1 ? 'Staff Management' : 'My Team Members' ?></title>
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
        .content { flex-grow: 1; padding: 2rem; background-color: #f8f9fa; }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h4 class="text-center mb-4"><?= $roleId == 1 ? 'Admin Panel' : 'Manager Panel' ?></h4>
    <?php if ($roleId == 1): ?>
        <a href="admin_dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
        <a href="#" class="active"><i class="bi bi-people-fill me-2"></i>Staff</a>
        <a href="requests.php"><i class="bi bi-list-task me-2"></i>Requests</a>
        <a href="departments.php"><i class="bi bi-diagram-3 me-2"></i>Departments</a>
        <a href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
    <?php else: ?>
        <a href="manager_dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
        <a href="create_request.php"><i class="bi bi-plus-circle me-2"></i>Create Request</a>
        <a href="#" class="active"><i class="bi bi-people-fill me-2"></i>Team Members</a>
        <a href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
    <?php endif; ?>
</div>

<!-- Main Content -->
<div class="content">
    <h3 class="mb-4"><?= $roleId == 1 ? 'Staff Management' : 'My Team Members' ?></h3>

    <?php if ($roleId == 1): ?>
        <!-- Admin Filters -->
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-4">
                <select name="department_id" class="form-select">
                    <option value="">All Departments</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?= $dept['department_id'] ?>" <?= ($filterDept == $dept['department_id']) ? 'selected' : '' ?>>
                            <?= $dept['name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search name/email" value="<?= htmlspecialchars($search ?? '') ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-dark w-100"><i class="bi bi-filter"></i> Filter</button>
            </div>
            <div class="col-md-2">
                <a href="staff_or_team.php" class="btn btn-secondary w-100"><i class="bi bi-x-circle"></i> Clear</a>
            </div>
        </form>
    <?php endif; ?>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Name</th><th>Email</th><th>Department</th><th>Role</th>
                    <?php if ($roleId == 1): ?><th>Actions</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
            <?php if (count($users)): ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['full_name']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['department']) ?></td>
                        <td><?= htmlspecialchars($user['role_name']) ?></td>
                        <?php if ($roleId == 1): ?>
                            <td>
                                <a href="edit_user.php?id=<?= $user['user_id'] ?>" class="btn btn-warning btn-sm"><i class="bi bi-pencil-square"></i></a>
                                <a href="?delete=<?= $user['user_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?')"><i class="bi bi-trash"></i></a>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="<?= $roleId == 1 ? 5 : 4 ?>" class="text-center text-muted">No records found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
