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
    $stmt = $conn->prepare("DELETE FROM department WHERE department_id = ?");
    $stmt->execute([intval($_GET['delete'])]);
    header("Location: departments.php");
    exit;
}

// Update request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_department'])) {
    $deptId = intval($_POST['department_id']);
    $newName = trim($_POST['department_name']);

    if ($newName !== '') {
        $stmt = $conn->prepare("UPDATE department SET name = ? WHERE department_id = ?");
        $stmt->execute([$newName, $deptId]);
    }
    header("Location: departments.php");
    exit;
}

// Search by department name
$searchName = $_GET['search'] ?? '';

// Fetch departments based on search
$query = "SELECT * FROM department WHERE 1=1";
$params = [];

if (!empty($searchName)) {
    $query .= " AND name LIKE ?";
    $params[] = '%' . $searchName . '%';
}

$query .= " ORDER BY department_id DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Departments</title>
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
    <a href="departments.php" class="active"><i class="bi bi-diagram-3 me-2"></i>Departments</a>
    <a href="role.php"><i class="bi bi-shield-lock-fill me-2"></i>Roles</a>
    <a href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
</div>

<!-- Content -->
<div class="content">
    <h3 class="mb-4">Departments</h3>

    <!-- Search Form -->
    <form method="GET" class="row g-3 mb-4 align-items-center">
        <div class="col-md-6">
            <input type="text" name="search" class="form-control" placeholder="Search by department name" value="<?= htmlspecialchars($searchName) ?>">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-dark w-100"><i class="bi bi-search"></i> Search</button>
        </div>
        <div class="col-md-2">
            <a href="departments.php" class="btn btn-secondary w-100"><i class="bi bi-x-circle"></i> Clear</a>
        </div>
    </form>

    <!-- Departments Table -->
    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (count($departments) > 0): ?>
                <?php foreach ($departments as $dept): ?>
                <tr>
                    <td><?= htmlspecialchars($dept['name']) ?></td>
                    <td>
                        <!-- Delete -->
                        <a href="?delete=<?= $dept['department_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this department?')">
                            <i class="bi bi-trash"></i>
                        </a>

                        <!-- Update Button triggers Modal -->
                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#updateModal<?= $dept['department_id'] ?>">
                            <i class="bi bi-pencil-square"></i>
                        </button>

                        <!-- Update Modal -->
                        <div class="modal fade" id="updateModal<?= $dept['department_id'] ?>" tabindex="-1" aria-labelledby="updateModalLabel<?= $dept['department_id'] ?>" aria-hidden="true">
                          <div class="modal-dialog">
                            <div class="modal-content">
                              <form method="POST">
                                  <div class="modal-header">
                                    <h5 class="modal-title" id="updateModalLabel<?= $dept['department_id'] ?>">Update Department</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                  </div>
                                  <div class="modal-body">
                                    <input type="hidden" name="department_id" value="<?= $dept['department_id'] ?>">
                                    <div class="mb-3">
                                        <label for="department_name" class="form-label">Department Name</label>
                                        <input type="text" name="department_name" class="form-control" value="<?= htmlspecialchars($dept['name']) ?>" required>
                                    </div>
                                  </div>
                                  <div class="modal-footer">
                                    <button type="submit" name="update_department" class="btn btn-primary">Save Changes</button>
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
                <tr><td colspan="3" class="text-center text-muted">No departments found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
