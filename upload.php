<?php

session_start();
require 'db.php';

// Access control (same pattern as dashboard.php)
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit;
}
$user_id = $_SESSION['user_id'];

// Directory config
$upload_dir = __DIR__ . '/uploads';   // where files are saved on disk
$max_size = 2 * 1024 * 1024;          // 2 MB 

//Checking file extension, only allowed are accepted.
$allowed_mime = [
    'image/jpeg'      => '.jpg',
    'image/png'       => '.png',
    'application/pdf' => '.pdf'
];

$error_log_file = __DIR__ . '/error_log.txt';

// Error handler
set_error_handler(function($severity, $message, $file, $line) use ($error_log_file) {
    $entry = "[".date('Y-m-d H:i:s')."] PHP Error: $message in $file on line $line\n";
    error_log($entry, 3, $error_log_file);
    throw new ErrorException($message, 0, $severity, $file, $line);
});

function show_message($msg, $is_error = true) {
    $color = $is_error ? 'darkred' : 'green';
    return '<div style="color:'.$color.';margin:10px 0;">'.htmlspecialchars($msg).'</div>';
}

// Check if upload folder exist
try {
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true) && !is_dir($upload_dir)) {
            throw new RuntimeException('Failed to create upload directory.');
        }
    }
} catch (Exception $ex) {
    error_log("[".date('Y-m-d H:i:s')."] Upload dir error: ".$ex->getMessage()."\n", 3, $error_log_file);
    die('Server configuration error. Please contact administrator.');
}

$messages = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // $_FILES holds info about files sent by the form. 'file'
    // matches <input name="file"> in the form below.
    if (empty($_FILES['file'])) {
        $messages[] = ['msg' => 'No file uploaded.', 'error' => true];
    } else {
        $file = $_FILES['file'];
        try {
            if (!isset($file['error']) || is_array($file['error'])) {
                throw new RuntimeException('Invalid file upload parameters.');
            }

            // PHP sets an error code for every upload, even
            // successful uploads get = (UPLOAD_ERR_OK = no problem).
            switch ($file['error']) {
                case UPLOAD_ERR_OK:
                    break;
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    throw new RuntimeException('Exceeded filesize limit.');
                case UPLOAD_ERR_PARTIAL:
                case UPLOAD_ERR_NO_FILE:
                    throw new RuntimeException('File not completely uploaded or missing.');
                default:
                    throw new RuntimeException('Unknown upload error.');
            }

            // Check the size ourselves too.
            if ($file['size'] > $max_size) {
                throw new RuntimeException('File exceeds 2 MB size limit.');
            }

            // SECURITY: never trust the file name or the type the
            // finfo reads the file's real contents.
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file['tmp_name']);
            if ($mime === false) {
                throw new RuntimeException('Unable to determine file type.');
            }
            if (!array_key_exists($mime, $allowed_mime)) {
                throw new RuntimeException('Only JPG, PNG and PDF files are allowed.');
            }

            // prevents overwrites and bad filenames.
            $ext = $allowed_mime[$mime];
            $unique = bin2hex(random_bytes(16)) . $ext;
            $destination = $upload_dir . '/' . $unique;

            // Confirms the file really came from this form.
            if (!is_uploaded_file($file['tmp_name'])) {
                throw new RuntimeException('Possible file upload attack.');
            }

            // Move from PHP's temp location into "uploads".
            if (!move_uploaded_file($file['tmp_name'], $destination)) {
                throw new RuntimeException('Failed to move uploaded file.');
            }
            chmod($destination, 0644);

            // Save the record in the database. If this fails,
            // delete the file so no loose data is left on disk.
            try {
                $stmnt = $pdo->prepare(
                    "INSERT INTO uploaded_files (user_id, filename, file_path)
                     VALUES (:user_id, :filename, :file_path)"
                );
                $stmnt->execute([
                    ':user_id'   => $user_id,
                    ':filename'  => $file['name'],       // original name, for display
                    ':file_path' => 'uploads/' . $unique // saved name, for the link
                ]);
            } catch (PDOException $ex) {
                unlink($destination);
                throw new RuntimeException('Could not save the file record.');
            }

            $messages[] = ['msg' => 'File uploaded successfully.', 'error' => false];

        } catch (RuntimeException $ex) {
            error_log("[".date('Y-m-d H:i:s')."] Upload Error: ".$ex->getMessage()."\n", 3, $error_log_file);
            $messages[] = ['msg' => $ex->getMessage(), 'error' => true];
        } catch (Exception $ex) {
            error_log("[".date('Y-m-d H:i:s')."] General Upload Error: ".$ex->getMessage()."\n", 3, $error_log_file);
            $messages[] = ['msg' => 'File upload failed.', 'error' => true];
        }
    }
}

// List the user's ONLY upload... Newest first
$files = [];
try {
    $stmnt = $pdo->prepare(
        "SELECT filename, file_path, upload_date FROM uploaded_files
         WHERE user_id = :user_id
         ORDER BY upload_date DESC"
    );
    $stmnt->execute([':user_id' => $user_id]);
    $files = $stmnt->fetchAll();
} catch (PDOException $ex) {
    error_log("[".date('Y-m-d H:i:s')."] Fetch files error: ".$ex->getMessage()."\n", 3, $error_log_file);
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Upload Files</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .thumb { display:inline-block; margin:8px; text-align:center; }
        .thumb img { display:block; max-width:150px; max-height:150px; }
    </style>
</head>
<body>
<div class="page">
    <nav class="topBar">
        <span>WELCOME, <?= htmlspecialchars($_SESSION['fullname']) ?></span>
        <div>
            <a href="dashboard.php">DASHBOARD</a>
            <a href="logout.php">LOG OUT</a>
        </div>
    </nav>

    <div class="card">
        <h2>Upload a File</h2>

        <?php foreach ($messages as $m): ?>
            <?= show_message($m['msg'], $m['error']) ?>
        <?php endforeach; ?>

        <!-- enctype="multipart/form-data" is REQUIRED for file uploads -->
        <form method="post" enctype="multipart/form-data" novalidate>
            <label>
                Choose file (JPG, PNG or PDF, max 2 MB)<br>
                <input type="file" name="file" accept=".jpg,.jpeg,.png,.pdf" required>
            </label>
            <br><br>
            <button type="submit">Upload</button>
        </form>
    </div>

    <div class="card">
        <h2>Your Uploaded Files</h2>

        <?php if (empty($files)): ?>
            <p>No files uploaded yet.</p>
        <?php else: ?>
            <?php foreach ($files as $f): ?>
                <?php
                    $url = $f['file_path'];
                    $is_pdf = strtolower(pathinfo($url, PATHINFO_EXTENSION)) === 'pdf';
                ?>
                <div class="thumb">
                    <a href="<?= htmlspecialchars($url) ?>" target="_blank" rel="noopener">
                        <?php if ($is_pdf): ?>
                            [PDF] Open
                        <?php else: ?>
                            <img src="<?= htmlspecialchars($url) ?>" alt="uploaded image">
                        <?php endif; ?>
                    </a>
                    <div style="font-size:0.9em;">
                        <?= htmlspecialchars($f['filename']) ?><br>
                        <?= htmlspecialchars($f['upload_date']) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
</body>
</html>