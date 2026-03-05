<?php
// config/db.php
$host = 'localhost';
$dbname = 'ared_facility';
$username = 'root';
$password = '';

// PDO connection (keep for secure queries)
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// mysqli connection (VULNERABLE - for SQL injection practice)
$conn = mysqli_connect($host, $username, $password, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Flag in config file
// FLAG{config_file_exposed}
?>