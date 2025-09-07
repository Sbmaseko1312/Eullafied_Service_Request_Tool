<?php
session_start();
require __DIR__ . '/../db_connect/connect.php';

// OPTIONAL: Get role name (if you use role names in DB)
function getRoleName($roleId, $conn) {
    $stmt = $conn->prepare("SELECT role_name FROM role WHERE role_id = ?");
    $stmt->execute([$roleId]);
    return $stmt->fetchColumn();
}

// Handle delete request
if (isset($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM role WHERE role_id = ?");
    $stmt->execute([intval($_GET['delete'])]);
    header("Location: role.php");
    exit;
}

// Handle update request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $roleId = intval($_POST['role_id']);
    $newName = trim($_POST['role_name']);

    if ($newName !== '') {
        $stmt = $conn->prepare("UPDATE role SET role_name = ? WHERE role_id = ?");
        $stmt->execute([$newName, $roleId]);
    }
    header("Location: role.php");
    exit;
}

// Handle search
$searchName = $_GET['search'] ?? '';
$query = "SELECT * FROM role WHERE 1=1";
$params = [];

if (!empty($searchName)) {
    $query .= " AND role_name LIKE ?";
    $params[] = '%' . $searchName . '%';
}

$query .= " ORDER BY role_id DESC";
$stmt = $conn->prepare($query);
$stmt->execute($params);
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Roles</title>
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
    <a href="requests.php"><i class="bi bi-list-task me-2"></i>Requests</a>
    <a href="departments.php"><i class="bi bi-diagram-3 me-2"></i>Departments</a>
    <a href="role.php" class="active"><i class="bi bi-shield-lock-fill me-2"></i>Roles</a>
    <a href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
</div>

<!-- Content -->
<div class="content">
    <h3 class="mb-4">Roles</h3>

    <!-- Search Form -->
    <form method="GET" class="row g-3 mb-4 align-items-center">
        <div class="col-md-6">
            <input type="text" name="search" class="form-control" placeholder="Search by role name" value="<?= htmlspecialchars($searchName) ?>">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-dark w-100"><i class="bi bi-search"></i> Search</button>
        </div>
        <div class="col-md-2">
            <a href="role.php" class="btn btn-secondary w-100"><i class="bi bi-x-circle"></i> Clear</a>
        </div>
    </form>

    <!-- Roles Table -->
    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (count($roles) > 0): ?>
                <?php foreach ($roles as $role): ?>
                <tr>
                    <td><?= htmlspecialchars($role['role_name']) ?></td>
                    <td>
                        <!-- Delete -->
                        <a href="?delete=<?= $role['role_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this role?')">
                            <i class="bi bi-trash"></i>
                        </a>

                        <!-- Update Button triggers Modal -->
                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#updateModal<?= $role['role_id'] ?>">
                            <i class="bi bi-pencil-square"></i>
                        </button>

                        <!-- Update Modal -->
                        <div class="modal fade" id="updateModal<?= $role['role_id'] ?>" tabindex="-1" aria-labelledby="updateModalLabel<?= $role['role_id'] ?>" aria-hidden="true">
                          <div class="modal-dialog">
                            <div class="modal-content">
                              <form method="POST">
                                  <div class="modal-header">
                                    <h5 class="modal-title" id="updateModalLabel<?= $role['role_id'] ?>">Update Role</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                  </div>
                                  <div class="modal-body">
                                    <input type="hidden" name="role_id" value="<?= $role['role_id'] ?>">
                                    <div class="mb-3">
                                        <label for="role_name" class="form-label">Role Name</label>
                                        <input type="text" name="role_name" class="form-control" value="<?= htmlspecialchars($role['role_name']) ?>" required>
                                    </div>
                                  </div>
                                  <div class="modal-footer">
                                    <button type="submit" name="update_role" class="btn btn-primary">Save Changes</button>
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
                <tr><td colspan="2" class="text-center text-muted">No roles found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
