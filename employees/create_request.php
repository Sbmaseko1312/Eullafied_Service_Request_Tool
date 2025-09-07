<?php
session_start();
require_once __DIR__ . '/../db_connect/connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_type_id = $_POST['request_type_id'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("INSERT INTO service_requests (user_id, request_type_id, description) VALUES (?, ?, ?)");
    $stmt->execute([$userId, $request_type_id, $description]);

    echo "<p style='color:green;'>Request submitted successfully.</p>";
}

// Load request types
$requestTypes = $conn->query("SELECT request_type_id, name FROM request_types")->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Create IT Service Request</h2>

<form method="post">
    <label for="request_type_id">Request Type:</label><br>
    <select name="request_type_id" required>
        <option value="">Select type</option>
        <?php foreach ($requestTypes as $type): ?>
            <option value="<?= $type['request_type_id'] ?>"><?= $type['name'] ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <textarea name="description" rows="4" placeholder="Describe the issue..." required></textarea><br><br>

    <button type="submit">Submit Request</button>
</form>

<a href="dashboard.php">← Back to Dashboard</a>
