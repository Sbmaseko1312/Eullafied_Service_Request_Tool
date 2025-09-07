<?php
session_start();
require_once __DIR__ . '/../db_connect/connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$stmt = $conn->prepare("SELECT u.full_name, u.email, d.name AS department, r.role_name
                        FROM users u
                        JOIN departments d ON u.department_id = d.department_id
                        JOIN roles r ON u.role_id = r.role_id
                        WHERE u.user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<h2>My Profile</h2>
<p><strong>Name:</strong> <?= $user['full_name'] ?></p>
<p><strong>Email:</strong> <?= $user['email'] ?></p>
<p><strong>Department:</strong> <?= $user['department'] ?></p>
<p><strong>Role:</strong> <?= $user['role_name'] ?></p>
<a href="dashboard.php">← Back to Dashboard</a>
