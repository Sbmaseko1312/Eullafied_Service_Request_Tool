<?php
session_start();
require __DIR__ . '/../db_connect/connect.php';

// ✅ Check login
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user']['user_id'] ?? null;

// ✅ Get manager's department
$stmt = $conn->prepare("SELECT department_id FROM user WHERE user_id = ?");
$stmt->execute([$userId]);
$deptId = $stmt->fetchColumn();

// ✅ Status categories
$statuses = ['Pending Approval', 'Approved', 'Pending Assistance', 'Solved'];
$statusCounts = [];
$totalRequests = 0;

// ✅ Count requests per status in this manager's department
foreach ($statuses as $status) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) FROM service_request sr
        JOIN user u ON sr.user_id = u.user_id
        WHERE sr.status = ? AND u.department_id = ?
    ");
    $stmt->execute([$status, $deptId]);
    $count = (int)$stmt->fetchColumn();
    $statusCounts[$status] = $count;
    $totalRequests += $count;
}

// ✅ Color/icon maps must be defined BEFORE use
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manager Dashboard</title>
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
    <h4 class="text-center mb-4">Manager Panel</h4>
    <a href="manager_dashboard.php" class="active"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
    <a href="create_request.php"><i class="bi bi-plus-circle me-2"></i>Create Request</a>
    <a href="team_members.php"><i class="bi bi-people-fill me-2"></i>Team Members</a>
    <a href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
</div>

<!-- Main Content -->
<div class="content">
    <h3 class="mb-4">Welcome, <?= htmlspecialchars($_SESSION['user']['name'] ?? 'Manager') ?></h3>

    <div class="row mb-4">
        <?php foreach ($statuses as $status): ?>
            <?php
                $count = $statusCounts[$status] ?? 0;
                $color = $colorMap[$status] ?? 'secondary';
                $icon = $iconMap[$status] ?? 'bi-question-circle';
                $encodedStatus = urlencode($status);
            ?>
            <div class="col-md-3">
                <a href="request.php?status=<?= $encodedStatus ?>" class="text-decoration-none">
                    <div class="card border-start border-<?= $color ?> border-4 shadow-sm text-dark">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="text-muted"><?= $status ?></h6>
                                    <h4><?= $count ?></h4>
                                </div>
                                <div class="card-icon text-<?= $color ?>">
                                    <i class="bi <?= $icon ?>"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Pie Chart -->
    <div class="card">
        <div class="card-header bg-dark text-white">
            Service Requests in Your Department
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-center">
    <div style="max-width: 300px; width: 100%;">
        <canvas id="statusChart"></canvas>
    </div>
</div>
            <?php if ($totalRequests == 0): ?>
                <p class="mt-3 text-center text-muted">No requests yet in your department.</p>
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
