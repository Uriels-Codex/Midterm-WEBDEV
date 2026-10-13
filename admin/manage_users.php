<?php
session_start();
require '../db.php';

$error_log_file = __DIR__ . '/../error_log.txt';
set_error_handler(function($severity, $message, $file, $line) use ($error_log_file) {
    $entry = "[".date('Y-m-d H:i:s')."] PHP Error: $message in $file on line $line\n";
    error_log($entry, 3, $error_log_file);
    throw new ErrorException($message, 0, $severity, $file, $line);
});

if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// message left by add, edit or delete (shown once, then cleared)
$flash_msg = $_SESSION['flash_msg'] ?? '';
unset($_SESSION['flash_msg']);

$search = trim($_GET['search'] ?? '');

if ($search !== '') {
    $stmnt = $pdo->prepare(
        "SELECT user_id, fullname, username, email, created_date
         FROM user_table
         WHERE fullname LIKE :s1 OR username LIKE :s2 OR email LIKE :s3
         ORDER BY user_id DESC"
    );
    $like = '%' . $search . '%';
    $stmnt->execute([':s1' => $like, ':s2' => $like, ':s3' => $like]);
} else {
    $stmnt = $pdo->prepare(
        "SELECT user_id, fullname, username, email, created_date
         FROM user_table
         ORDER BY user_id DESC"
    );
    $stmnt->execute();
}
$users = $stmnt->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Manage Users</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="page">
        <nav class="topBar">
            <span>WELCOME, <?= htmlspecialchars($_SESSION['fullname']) ?></span>
            <div>
                <a href="dashboard.php">DASHBOARD</a>
                <a href="../logout.php">LOG OUT</a>
            </div>
        </nav>

        <div class="card">
            <h2>Manage Users</h2>

            <?php if ($flash_msg): ?>
                <p style="color:green;"><?= htmlspecialchars($flash_msg) ?></p>
            <?php endif; ?>

            <p><a href="add_user.php">+ Add User</a></p>

            <form method="get">
                <input type="text" name="search" placeholder="Search name, username or email"
                       value="<?= htmlspecialchars($search) ?>">
                <button type="submit">Search</button>
            </form>

            <table border="1" cellpadding="6">
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Registered</th>
                    <th>Actions</th>
                </tr>
                <?php if (empty($users)): ?>
                    <tr><td colspan="6">No users found.</td></tr>
                <?php endif; ?>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= (int)$u['user_id'] ?></td>
                        <td><?= htmlspecialchars($u['fullname']) ?></td>
                        <td><?= htmlspecialchars($u['username']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= htmlspecialchars($u['created_date']) ?></td>
                        <td>
                            <a href="update_user.php?id=<?= (int)$u['user_id'] ?>">Edit</a> |
                            <a href="delete_user.php?id=<?= (int)$u['user_id'] ?>"
                               onclick="return confirm('Delete this user and all their files?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</body>
</html>