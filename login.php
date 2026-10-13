<?php
// log-in interface; Shared Logic for admin and user/student
session_start();
require 'db.php';

//------ERROR Handler-------
// basically create a folder and text file to store the said errors
// __DIR__ is the current directory on where this file is sitting in
// example: C:\Users\UrieL\Desktop\Midterm WEBDEV

$error_log_file = __DIR__ . '/error_log.txt';
set_error_handler(function($severity, $message, $file, $line) use ($error_log_file) {
    $entry = "[".date('Y-m-d H:i:s')."] PHP Error: $message in $file on line $line\n";
    error_log($entry, 3, $error_log_file);
    throw new ErrorException($message, 0, $severity, $file, $line);
});


// check if either user or admin is log in... IF ADMIN > admin/dashboard.php
// IF user/student > dashboard.php
// its in ternary; short hand if else " ? " is basically IF.. " : " basically ELSE
if (!empty($_SESSION['role'])) {
    header('Location: ' . ($_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'dashboard.php'));
    exit;
}

$errors = [];
$identifier = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    // trim username or email white space, also set to null if variable is empty >> "??" is IF
    $identifier = trim($_POST['identifier'] ?? ''); 
    $password = $_POST['password'] ?? '';

    if($identifier === '' || $password === '') {
        $errors[] = 'Please enter a valid username or password...';
    } else{
        try {
            // attempt to read/ access info on admin table first
            $stmnt = $pdo->prepare(
                "SELECT admin_id, fullname, username, email, password_hash
                 FROM admin_table
                 WHERE username =  :identifier OR email = :identifier
                 LIMIT 1"
            );

            // execute the script above and also fetch the data
            $stmnt->execute(['identifier' => $identifier]);
            $admin = $stmnt->fetch();

            if ($admin && password_verify($password, $admin['password_hash'])) {
                session_regenerate_id(TRUE);
                $_SESSION['role'] = 'admin';
                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['fullname'] = $admin['fullname'];
                header('Location: admin/dashboard.php');
                exit;
            }

            $stmnt = $pdo->prepare(
                'select user_id, fullname, username, email, password_hash
                from user_table
                where username= :identifier OR email= :identifier
                limit 1'
            );
            $stmnt->execute([':identifier' => $identifier]);
            $user = $stmnt->fetch();

            if($user && password_verify($password, $user['password_hash'])) {
                session_regenerate_id(TRUE);
                $_SESSION['role'] = 'user';
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['fullname'] = $user['fullname'];
                header('Location: dashboard.php');
                exit;
            }

            $errors[] = 'Invalid username/email or password..';

            
        }catch (PDOException $ex){
            error_log("[".date('Y-m-d H:i:s')."] DB Error: ".$ex->getMessage()."\n", 3, $error_log_file);
            $errors[] = 'An error occurred while logging in. Please try again later.';
        }
    }
}


?>
<!doctype html>
<html lang ="en">
<head>
    <meta charset="utf-8">
    <title>Login</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <h1>Login</h1>
    <?php foreach ($errors as $e): ?>
        <p style="color:red;"><?= htmlspecialchars($e)?></p>
    <?php endforeach;?>

    <form method="post" novalidate>
        <label>Username or Email <br>
            <input type="text" name="identifier" value="<?=htmlspecialchars($identifier)?>" required>          
 </label><br><br> 
 <label>Password
    <input type="password" name="password">
 </label>
        <button type="submit">Login</button>
    </form>
<p>Don't have an account? <a href="register.php">Register here</a></p>
<p>Want to register as an admin? <a href="admin/register.php">Register here as Admin</a></p>
</body>
</html>

