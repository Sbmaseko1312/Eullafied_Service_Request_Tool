<?php
require_once __DIR__ . '/../db_connect/connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $department_id = $_POST['department_id'];
    $role_id = $_POST['role_id'];

    $stmt = $conn->prepare("INSERT INTO users (full_name, email, password_hash, department_id, role_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$full_name, $email, $password, $department_id, $role_id]);

    echo "User registered successfully.";
}
?>
<!-- Basic HTML Form -->
<form method="post">
    <h2>Register</h2>
    <input type="text" name="full_name" placeholder="Full Name" required><br>
    <input type="email" name="email" placeholder="Email" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <input type="number" name="department_id" placeholder="Department ID" required><br>
    <input type="number" name="role_id" placeholder="Role ID (1=EMPLOYEE, 2=MANAGER, etc.)" required><br>
    <button type="submit">Register</button>
</form>
