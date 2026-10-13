<?php
// public sign up -- meaning for admin only
require '../db.php';

$error_log_file = __DIR__ . '/../error_log.txt';
set_error_handler(function($severity, $message, $file, $line) use ($error_log_file) {
    $entry = "[".date('Y-m-d H:i:s')."] PHP Error: $message in $file on line $line\n";
    error_log($entry, 3, $error_log_file);
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// if already logged in, skip the form and go to the dashboard
if (!empty($_SESSION['role'])) {
    header('Location: ' . ($_SESSION['role'] === 'admin' ? 'dashboard.php' : '../dashboard.php'));
    exit;
}

$fullname = $username = $email = '';
$errors = [];

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    
    if($fullname === ''){
        $errors[] = 'fullname is required..';
    }elseif (mb_strlen($fullname) > 255) {
        $errors[] = 'fullname must be 100 characters or less..';
    }
    if($username === ''){
        $errors[] = 'username is required..';
    }elseif (mb_strlen($username) > 255) {
        $errors[] = 'Username must be 100 characters or less..';
    }
    if($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email is required..';
    }elseif(mb_strlen($email)>255){
        $errors[] = 'Email must be 100 characteres or less..';
    }

    if($password === '' || mb_strlen($password) < 8) {
        $errors[] = 'Password myst be atleast 8 characters..';
    }

    if(empty($errors)){
        try{
            $stmnt = $pdo->prepare(
                "INSERT INTO admin_table (fullname, username, email, password_hash)
                 VALUES(:fullname, :username, :email, :password_hash)"
            );

            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $stmnt->execute([
                ':fullname' => $fullname,
                ':username' => $username,
                ':email' => $email,
                ':password_hash' => $hashed
            ]);

            $_SESSION['register_success'] = "Registered Successfully. Proceed to login.";
            header('Location: ../login.php');
            exit;

        } catch (PDOException $ex) {
             if ($ex->getCode() === '23000') {
                $errors[] = 'Username or email is already registered.';
            } else {
                error_log("[".date('Y-m-d H:i:s')."] DB Error: ".$ex->getMessage()."\n", 3, $error_log_file);
                $errors[] = 'An error occurred while creating account. Please try again later.';
            }
        } catch (Exception $ex) {
            error_log("[".date('Y-m-d H:i:s')."] General Error: ".$ex->getMessage()."\n", 3, $error_log_file);
            $errors[] = 'An unexpected error occurred.';
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Register</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<h1>Admin Registration</h1>
    <?php foreach ($errors as $e): ?>
        <p style="color:red;"><?= htmlspecialchars($e)?></p>
    <?php endforeach;?>

<form method="post" novalidate>
    <label>
    Full Name <br>
    <input type="text" name="fullname" value="<?=htmlspecialchars($fullname)?>" required maxlength="255">
    </label>
    <br><br>
    <label>
        Username<br>
        <input type="text" name="username" value="<?=htmlspecialchars($username)?>" required maxlength="255">
    </label>
    <br><br>
    <label>
        Email<br>
        <input type="email" name="email" value="<?=htmlspecialchars($email)?>" required maxlength="255">
    </label>
    <br><br>
    <label>
        Password<br>
        <input type="password" name="password" required minlength="8">
    </label>
    <br><br>
    <button type="submit">Register</button>
</form>

<p>Already have an account? <a href="../login.php">Login</a></p>
</body>
</html>
