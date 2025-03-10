<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fantasy Market Master</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <h1>Fantasy Market Master</h1>
    <?php if (isset($_SESSION["user_id"])): ?> <!-- Show navigation only when logged in -->
    <nav>
        <a href="dashboard.php">Back to Dashboard</a>
        <a href="currency_converter.php">Currency</a>
        <a href="logout.php">Logout</a>
    </nav>
    <?php endif; ?>
</header>

<main>
