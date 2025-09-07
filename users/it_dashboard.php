<?php
session_start();
require __DIR__ . '/../db_connect/connect.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user']['user_id'] ?? null;

// ✅ Statuses relevant to IT
$statuses = ['Approved', 'Pending Assistance', 'Solved'];
$statusCounts = [];
$totalRequests = 0;

// ✅ Count requests per status (no department filter)
foreach ($statuses as $status) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM service_request WHERE status = ?");
    $stmt->execute([$status]);
    $count = (int)$stmt->fetchColumn();
    $statusCounts[$status] = $count;
    $totalRequests += $count;
}

$colorMap = [
    'Approved' => 'success',
    'Pending Assistance' => 'info',
    'Solved' => 'primary'
];

$iconMap = [
    'Approved' => 'bi-check2-circle',
    'Pending Assistance' => 'bi-tools',
    'Solved' => 'bi-check-all'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>IT Dashboard</title>
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
    <h4 class="text-center mb-4">IT Panel</h4>
    <a href="it_dashboard.php" class="active"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
    <a href="view_requests.php?status=<?= urlencode('Approved') ?>"><i class="bi bi-list-check me-2"></i>Assigned Tasks</a>
    <a href="view_requests.php?status=<?= urlencode('Pending Assistance') ?>"><i class="bi bi-hammer me-2"></i>Ongoing Support</a>
    <a href="view_requests.php?status=<?= urlencode('Solved') ?>"><i class="bi bi-check-all me-2"></i>Completed</a>
    <a href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
</div>

<!-- Main Content -->
<div class="content">
    <h3 class="mb-4">Welcome, <?= htmlspecialchars($_SESSION['user']['name'] ?? 'IT Staff') ?></h3>

    <div class="row mb-4">
        <?php foreach ($statuses as $status): ?>
            <?php
                $count = $statusCounts[$status] ?? 0;
                $color = $colorMap[$status] ?? 'secondary';
                $icon = $iconMap[$status] ?? 'bi-question-circle';
                $encodedStatus = urlencode($status);
            ?>
            <div class="col-md-4">
                <a href="view_requests.php?status=<?= $encodedStatus ?>" class="text-decoration-none">
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
            All Department Requests (IT View)
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-center">
    <div style="max-width: 300px; width: 100%;">
        <canvas id="statusChart"></canvas>
    </div>
</div>
            <?php if ($totalRequests === 0): ?>
                <p class="mt-3 text-center text-muted">No service requests available.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
const chartData = {
    labels: <?= json_encode(array_keys($statusCounts)) ?>,
    datasets: [{
        data: <?= json_encode(array_values($statusCounts)) ?>,
        backgroundColor: ['#198754', '#0dcaf0', '#0d6efd'],
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
