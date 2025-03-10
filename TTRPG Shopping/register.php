<?php
require 'config.php';
require 'header.php'; // Include the header


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $role = ($_POST["role"] === "admin") ? "admin" : "pc";

    $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role, currency) VALUES (?, ?, ?, 1000)");
    if ($stmt->execute([$username, $password, $role])) {
        echo "User registered successfully!";
    } else {
        echo "Registration failed.";
    }
}
?>
<br>
<center>
<form method="POST">
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <select name="role">
        <option value="pc">Player</option>
        <option value="admin">Admin</option>
    </select>
    <button type="submit">Register</button>
</form>
</center>
<?php require 'footer.php'; // Include the footer ?>