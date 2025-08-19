<?php
// db.php - single place for DB connection (used in all files)
// Database credentials (from user)
$db_host = 'localhost';
$db_name = 'dbxutryicovexp';
$db_user = 'up0ghncfmfakv';
$db_pass = 'vznwqmh2glra';

try {
    $pdo = new PDO("mysql:host={$db_host};dbname={$db_name};charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    // show friendly message (for production you might hide details)
    echo "<h2>Database connection failed</h2><p>{$e->getMessage()}</p>";
    exit;
}
?>
