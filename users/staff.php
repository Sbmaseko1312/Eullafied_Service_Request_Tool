<?php
session_start();
require __DIR__ . '/../db_connect/connect.php';


// Delete
if (isset($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM user WHERE user_id = ?");
    $stmt->execute([intval($_GET['delete'])]);
    header("Location: staff.php");
    exit;
}

// Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $stmt = $conn->prepare("UPDATE user SET full_name = ?, email = ?, department_id = ?, role_id = ? WHERE user_id = ?");
    $stmt->execute([
        $_POST['full_name'], $_POST['email'],
        intval($_POST['department_id']),
        intval($_POST['role_id']),
        intval($_POST['user_id'])
    ]);
    header("Location: staff.php");
    exit;
}

// Create new user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $department_id = intval($_POST['department_id']);
    $role_id = intval($_POST['role_id']);

    $stmt = $conn->prepare("INSERT INTO user (full_name, email, password_hash, department_id, role_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$full_name, $email, $password, $department_id, $role_id]);
    header("Location: staff.php");
    exit;
}

$departments = $conn->query("SELECT * FROM department")->fetchAll(PDO::FETCH_ASSOC);
$roles = $conn->query("SELECT * FROM role")->fetchAll(PDO::FETCH_ASSOC);

$filterDept = $_GET['department_id'] ?? '';
$search = $_GET['search'] ?? '';

$query = "SELECT u.*, d.name AS department, r.role_name
          FROM user u
          LEFT JOIN department d ON u.department_id = d.department_id
          LEFT JOIN role r ON u.role_id = r.role_id
          WHERE r.role_name IN ('Employee', 'Manager', 'IT')";

if ($filterDept !== '') {
    $query .= " AND u.department_id = " . intval($filterDept);
}
if ($search !== '') {
    $query .= " AND (u.full_name LIKE " . $conn->quote("%$search%") . " OR u.email LIKE " . $conn->quote("%$search%") . ")";
}
$users = $conn->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Staff Management</title>
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
    <a href="#" class="active"><i class="bi bi-people-fill me-2"></i>Staff</a>
    <a href="requests.php"><i class="bi bi-list-task me-2"></i>Requests</a>
    <a href="departments.php"><i class="bi bi-diagram-3 me-2"></i>Departments</a>
    <a href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
</div>

<!-- Content -->
<div class="content">
    <h3 class="mb-4">Staff Management</h3>

    <!-- Filters -->
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
            <input type="text" name="search" class="form-control" placeholder="Search name/email" value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-dark w-100"><i class="bi bi-filter"></i> Filter</button>
        </div>
        <div class="col-md-2">
            <a href="staff.php" class="btn btn-secondary w-100"><i class="bi bi-x-circle"></i> Clear</a>
        </div>
    </form>

    <!-- Create User Button -->
<div class="mb-3">
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createUserModal">
        <i class="bi bi-plus-circle"></i> Add New User
    </button>
</div>

<!-- Create User Modal -->
<div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="createUserModalLabel">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body row g-3 px-3 py-2">
                    <div class="col-md-6">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Department</label>
                        <select name="department_id" class="form-select">
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?= $dept['department_id'] ?>"><?= $dept['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Role</label>
                        <select name="role_id" class="form-select">
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role['role_id'] ?>"><?= $role['role_name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer px-3 pb-3">
                    <button type="submit" name="create_user" class="btn btn-success">Add User</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

    <!-- Staff Table -->
    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle">
            <thead class="table-dark">
                <tr><th>Name</th><th>Email</th><th>Department</th><th>Role</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php if (count($users)): ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['full_name']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= $user['department'] ?></td>
                        <td><?= $user['role_name'] ?></td>
                        <td>
                            <!-- Edit Button -->
                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editUserModal<?= $user['user_id'] ?>">
                                <i class="bi bi-pencil-square"></i>
                            </button>

                            <!-- Delete Button -->
                            <a href="?delete=<?= $user['user_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?')">
                                <i class="bi bi-trash"></i>
                            </a>

                            <!-- Edit Modal -->
                            <div class="modal fade" id="editUserModal<?= $user['user_id'] ?>" tabindex="-1" aria-labelledby="editUserModalLabel<?= $user['user_id'] ?>" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <form method="POST">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="editUserModalLabel<?= $user['user_id'] ?>">Edit User</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body row g-3 px-3 py-2">
                                                <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">

                                                <div class="col-md-6">
                                                    <label class="form-label">Full Name</label>
                                                    <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label">Email</label>
                                                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label">Department</label>
                                                    <select name="department_id" class="form-select">
                                                        <?php foreach ($departments as $dept): ?>
                                                            <option value="<?= $dept['department_id'] ?>" <?= $user['department_id'] == $dept['department_id'] ? 'selected' : '' ?>>
                                                                <?= $dept['name'] ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label">Role</label>
                                                    <select name="role_id" class="form-select">
                                                        <?php foreach ($roles as $role): ?>
                                                            <option value="<?= $role['role_id'] ?>" <?= $user['role_id'] == $role['role_id'] ? 'selected' : '' ?>>
                                                                <?= $role['role_name'] ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer px-3 pb-3">
                                                <button type="submit" name="update_user" class="btn btn-primary">Save Changes</button>
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" class="text-center text-muted">No users found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Bootstrap JS for modals -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
