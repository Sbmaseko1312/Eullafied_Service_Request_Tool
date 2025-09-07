<?php
session_start();
require_once __DIR__ . '/../db_connect/connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password_hash'];

    $stmt = $conn->prepare("SELECT * FROM user WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Direct comparison since password is not hashed
    if ($user && $password === $user['password_hash']) {
        $_SESSION['user'] = $user;

        switch ($user['role_id']) {
            case 1: header("Location: admin_dashboard.php"); break;
            case 2: header("Location: manager_dashboard.php"); break;
            case 3: header("Location: it_dashboard.php"); break;
            case 4: header("Location: employee_dashboard.php"); break;
            default: echo "Invalid role."; exit;
        }
        exit;
    } else {
        $error = "Invalid login credentials.";
    }
}
?>



<!-- HTML Login Form -->
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card col-md-6 offset-md-3">
        <div class="card-header bg-dark text-white">Login</div>
        <div class="card-body">
            <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
            <form method="POST">
                <div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control" required></div>
                <div class="mb-3"><label>Password</label><input type="password" name="password_hash" class="form-control" required></div>
                <button type="submit" class="btn btn-dark">Login</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
