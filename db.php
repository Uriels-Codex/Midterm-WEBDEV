<?php
// database connection

$db_host = 'localhost';
$db_name = 'student_management_db';
$db_user = 'root';
$db_pass = '';

//data source name
$dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";

// attemp connection 
try{
    //php data object
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
}catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}