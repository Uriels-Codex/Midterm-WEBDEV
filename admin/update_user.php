<?php
// admin edits an existing user
session_start();
require '../db.php';

$error_log_file = __DIR__ . '/../error_log.txt';
set_error_handler(function($severity, $message, $file, $line) use ($error_log_file) {
    $entry = "[".date('Y-m-d H:i:s')."] PHP Error: $message in $file on line $line\n";
    error_log($entry, 3, $error_log_file);
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// only admin may use this page
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// which user? comes from the link (?id=7) first, then from the hidden field on submit...
$user_id = (int)($_GET['id'] ?? $_POST['user_id'] ?? 0);

if ($user_id <= 0) {
    header('Location: manage_users.php');
    exit;
}

// load the current data of this user
$stmnt = $pdo->prepare(
    "SELECT fullname, username, email
     FROM user_table
     WHERE user_id = :user_id
     LIMIT 1"
);
$stmnt->execute([':user_id' => $user_id]);
$user = $stmnt->fetch();

// no such user, go back to the list
if (!$user) {
    header('Location: manage_users.php');
    exit;
}

$fullname = $user['fullname'];
$username = $user['username'];
$email    = $user['email'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';   // optional: blank means keep the old password

    if ($fullname === '') {
        $errors[] = 'Full name is required.';
    } elseif (mb_strlen($fullname) > 255) {
        $errors[] = 'Full name must be 255 characters or less.';
    }

    if ($username === '') {
        $errors[] = 'Username is required.';
    } elseif (mb_strlen($username) > 255) {
        $errors[] = 'Username must be 255 characters or less.';
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email is required.';
    } elseif (mb_strlen($email) > 255) {
        $errors[] = 'Email must be 255 characters or less.';
    }

    if ($password !== '' && mb_strlen($password) < 8) {
        $errors[] = 'New password must be at least 8 characters.';
    }

    if (empty($errors)) {
        try {
            if ($password !== '') {
                // admin typed a new password, so hash and save it too
                $stmnt = $pdo->prepare(
                    "UPDATE user_table
                     SET fullname = :fullname, username = :username,
                         email = :email, password_hash = :password_hash
                     WHERE user_id = :user_id"
                );
                $stmnt->execute([
                    ':fullname'      => $fullname,
                    ':username'      => $username,
                    ':email'         => $email,
                    ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
                    ':user_id'       => $user_id
                ]);
            } else {
                // password left blank, so keep the old one
                $stmnt = $pdo->prepare(
                    "UPDATE user_table
                     SET fullname = :fullname, username = :username, email = :email
                     WHERE user_id = :user_id"
                );
                $stmnt->execute([
                    ':fullname' => $fullname,
                    ':username' => $username,
                    ':email'    => $email,
                    ':user_id'  => $user_id
                ]);
            }

            $_SESSION['flash_msg'] = 'User updated successfully.';
            header('Location: manage_users.php');
            exit;

        } catch (PDOException $ex) {
            if ($ex->getCode() === '23000') {
                $errors[] = 'Username or email is already used by another account.';
            } else {
                error_log("[".date('Y-m-d H:i:s')."] DB Error: ".$ex->getMessage()."\n", 3, $error_log_file);
                $errors[] = 'An error occurred while updating the user. Please try again later.';
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Edit User</title>
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
            <h2>Edit User #<?= (int)$user_id ?></h2>

            <?php foreach ($errors as $e): ?>
                <p style="color:red;"><?= htmlspecialchars($e) ?></p>
            <?php endforeach; ?>

            <form method="post" novalidate>
                <!-- remembers which user is being edited when the form is submitted -->
                <input type="hidden" name="user_id" value="<?= (int)$user_id ?>">

                <label>
                    Full Name<br>
                    <input type="text" name="fullname" value="<?= htmlspecialchars($fullname) ?>" required maxlength="255">
                </label>
                <br><br>
                <label>
                    Username<br>
                    <input type="text" name="username" value="<?= htmlspecialchars($username) ?>" required maxlength="255">
                </label>
                <br><br>
                <label>
                    Email<br>
                    <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required maxlength="255">
                </label>
                <br><br>
                <label>
                    New Password (leave blank to keep the current one)<br>
                    <input type="password" name="password" minlength="8">
                </label>
                <br><br>
                <button type="submit">Save Changes</button>
            </form>

            <p><a href="manage_users.php">Back to Manage Users</a></p>
        </div>
    </div>
</body>
</html>