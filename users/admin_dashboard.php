<?php
session_start();
require __DIR__ . '/../db_connect/connect.php';

// Simulated session check
// if (!isset($_SESSION['user']) || $_SESSION['user']['role_id'] != 1) {
//     echo "<script>alert('Access denied.'); window.location.href='login.php';</script>";
//     exit;
// }

// Define statuses
$statuses = ['Pending Approval', 'Approved', 'Pending Assistance', 'Solved'];
$statusCounts = [];
$totalRequests = 0;

foreach ($statuses as $status) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM service_request WHERE status = ?");
    $stmt->execute([$status]);
    $count = (int)$stmt->fetchColumn();
    $statusCounts[$status] = $count;
    $totalRequests += $count;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        .card-icon {
            font-size: 1.5rem;
            opacity: 0.7;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h4 class="text-center mb-4">Admin Panel</h4>
    <a href="#" class="active"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
    <a href="staff.php"><i class="bi bi-people-fill me-2"></i>Staff</a>
    <a href="requests.php"><i class="bi bi-list-task me-2"></i>Requests</a>
    <a href="departments.php"><i class="bi bi-diagram-3 me-2"></i>Departments</a>
    <a href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
</div>

<!-- Main Content -->
<div class="content">
    <h3 class="mb-4">Welcome, Admin</h3>

    <div class="row mb-4">
        <?php foreach ($statuses as $status): ?>
            <?php
                $colorMap = [
                    'Pending Approval' => 'warning',
                    'Approved' => 'success',
                    'Pending Assistance' => 'info',
                    'Solved' => 'primary'
                ];
                $iconMap = [
                    'Pending Approval' => 'bi-hourglass-split',
                    'Approved' => 'bi-check2-circle',
                    'Pending Assistance' => 'bi-tools',
                    'Solved' => 'bi-check-all'
                ];
                $count = $statusCounts[$status] ?? 0;
            ?>
            <div class="col-md-3">
                <div class="card border-start border-<?= $colorMap[$status] ?> border-4 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-muted"><?= $status ?></h6>
                                <h4><?= $count ?></h4>
                            </div>
                            <div class="card-icon text-<?= $colorMap[$status] ?>">
                                <i class="bi <?= $iconMap[$status] ?>"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Pie Chart -->
    <div class="card">
        <div class="card-header bg-dark text-white">
            Service Requests Overview
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-center">
    <div style="max-width: 300px; width: 100%;">
        <canvas id="statusChart"></canvas>
    </div>
</div>
            <?php if ($totalRequests == 0): ?>
                <p class="mt-3 text-center text-muted">No requests yet. All statuses are currently at 0%.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
const chartData = {
    labels: <?= json_encode(array_keys($statusCounts)) ?>,
    datasets: [{
        data: <?= json_encode(array_values($statusCounts)) ?>,
        backgroundColor: ['#ffc107', '#198754', '#0dcaf0', '#0d6efd'],
        borderColor: '#fff',
        borderWidth: 1
    }]
};

const config = {
    type: 'pie',
    data: chartData,
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const total = chartData.datasets[0].data.reduce((a, b) => a + b, 0);
                        const count = context.raw;
                        const percent = total ? ((count / total) * 100).toFixed(1) : 0;
                        return `${context.label}: ${count} (${percent}%)`;
                    }
                }
            }
        }
    }
};

new Chart(document.getElementById('statusChart'), config);
</script>

</body>
</html>
