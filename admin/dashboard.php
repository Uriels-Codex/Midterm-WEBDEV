<?php

session_start();
require '../db.php';

$error_log_file = __DIR__ . '/../error_log.txt';
set_error_handler(function($severity, $message, $file, $line) use ($error_log_file) {
    $entry = "[".date('Y-m-d H:i:s')."] PHP Error: $message in $file on line $line\n";
    error_log($entry, 3, $error_log_file);
    throw new ErrorException($message, 0, $severity, $file, $line);
});

if(empty($_SESSION['role'] )|| $_SESSION['role'] !== 'admin'){
    header('Location: ../login.php');
    exit;
}

$admin_id = $_SESSION['admin_id'];

$stmnt = $pdo->prepare(
    "SELECT fullname, username, email
    FROM admin_table
    WHERE admin_id = :admin_id
    LIMIT 1"
);

$stmnt->execute([':admin_id' => $admin_id]);
$admin_dashb = $stmnt -> fetch();

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Admin Dashboard</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="page">
        <nav class="topBar">
            <span>WELCOME, <?= htmlspecialchars($_SESSION['fullname']) ?></span>
            <div>
                <a href="manage_users.php">MANAGE USERS</a>
                <a href="../logout.php">LOG OUT</a>
            </div>
        </nav>

        <div class="card">
            <h2>Administrator Information</h2>
            <p><b>Admin ID:</b> <?= htmlspecialchars((string)$admin_id) ?></p>
            <p><b>Full Name:</b> <?= htmlspecialchars($admin_dashb['fullname']) ?></p>
            <p><b>Username:</b> <?= htmlspecialchars($admin_dashb['username']) ?></p>
            <p><b>Email:</b> <?= htmlspecialchars($admin_dashb['email']) ?></p>
        </div>

        <div class="card">
            <h2>User Management</h2>
            <p><a href="manage_users.php">View, add, edit or delete users</a></p>
        </div>
    </div>
</body>
</html>
