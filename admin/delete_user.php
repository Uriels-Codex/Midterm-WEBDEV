<?php
// Delete part of CRUD
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

$user_id = (int)($_GET['id'] ?? 0);

if ($user_id > 0) {
    try {
        // find this user's uploaded files before deleting anything,
        // since once the user is gone their file records are gone too
        $stmnt = $pdo->prepare("SELECT file_path FROM uploaded_files WHERE user_id = :user_id");
        $stmnt->execute([':user_id' => $user_id]);
        $files = $stmnt->fetchAll();

        // delete the actual files from the uploads folder
        foreach ($files as $f) {
            // basename() keeps only the file name, so the path can't escape uploads/
            $path = __DIR__ . '/../uploads/' . basename($f['file_path']);
            if (is_file($path)) {
                unlink($path);
            }
        }

        // delete the user; ON DELETE CASCADE (from the localhost iteslf) removes their uploaded_files rows
        $stmnt = $pdo->prepare("DELETE FROM user_table WHERE user_id = :user_id");
        $stmnt->execute([':user_id' => $user_id]);

        if ($stmnt->rowCount() > 0) {
            $_SESSION['flash_msg'] = 'User deleted successfully.';
        } else {
            $_SESSION['flash_msg'] = 'User not found.';
        }

    } catch (PDOException $ex) {
        error_log("[".date('Y-m-d H:i:s')."] DB Error: ".$ex->getMessage()."\n", 3, $error_log_file);
        $_SESSION['flash_msg'] = 'Could not delete the user. Please try again.';
    }
}

header('Location: manage_users.php');
exit;