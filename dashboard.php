<?php
session_start();
require 'db.php';

$error_log_file = __DIR__ . '/error_log.txt';
set_error_handler(function($severity, $message, $file, $line) use ($error_log_file) {
    $entry = "[".date('Y-m-d H:i:s')."] PHP Error: $message in $file on line $line\n";
    error_log($entry, 3, $error_log_file);
    throw new ErrorException($message, 0, $severity, $file, $line);
});

if(empty($_SESSION['role'] )|| $_SESSION['role'] !== 'user'){
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$stmnt = $pdo->prepare(
    "SELECT fullname, username, email, created_date 
    FROM user_table
    WHERE user_id = :user_id
    LIMIT 1"
);

$stmnt->execute([':user_id' => $user_id]);
$user_dashb = $stmnt ->fetch();

?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Dashboard</title>
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <link rel="stylesheet" href="css/style.css">
    </head>
<body>
    <div class="page">
        <nav class="topBar">
            <span>WELCOME, <?= htmlspecialchars($_SESSION['fullname']) ?></span>
            <div>
                <a href="upload.php">UPLOAD FILES</a>
                <a href="logout.php">LOG OUT</a>
            </div>
        </nav>

        <div class="card">
            <h2>Your Information</h2>
            <p><b>User ID:</b> <?= htmlspecialchars((string)$user_id) ?></p>
            <p><b>Full Name:</b> <?= htmlspecialchars($user_dashb['fullname']) ?></p>
            <p><b>Username:</b> <?= htmlspecialchars($user_dashb['username']) ?></p>
            <p><b>Email:</b> <?= htmlspecialchars($user_dashb['email']) ?></p>
            <p><b>Date Registered:</b> <?= htmlspecialchars($user_dashb['created_date']) ?></p>
        </div>
    </div>
</body>
</html>
