<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

$host = 'localhost';  // Change this if your database is hosted elsewhere
$dbname = 'loginsystem'; // Your database name
$username = 'dm';   // Your database username
$password = 'password!';       // Your database password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>