<?php 
require 'auth.php';
require 'config.php';
require 'header.php'; // Include the header

if ($_SESSION["role"] !== "admin") {
    die("Access denied.");
}

// Update user details
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["user_id"])) {
    $user_id = $_POST["user_id"];

    // Update location
    if (isset($_POST["location_id"])) {
        $stmt = $pdo->prepare("UPDATE users SET location_id = ? WHERE id = ?");
        $stmt->execute([$_POST["location_id"], $user_id]);
    }

    // Update currency
    if (isset($_POST["currency"])) {
        $currency = (int)$_POST["currency"];
        $stmt = $pdo->prepare("UPDATE users SET currency = ? WHERE id = ?");
        $stmt->execute([$currency, $user_id]);
    }

    // Update role
    if (isset($_POST["role"])) {
        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->execute([$_POST["role"], $user_id]);
    }

    // Update password if set
    if (!empty($_POST["new_password"])) {
        $new_password_hash = password_hash($_POST["new_password"], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $stmt->execute([$new_password_hash, $user_id]);
    }
}

// Delete user
if (isset($_GET["delete"])) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$_GET["delete"]]);
}

// Fetch users and locations
$users = $pdo->query("SELECT users.*, locations.name AS location_name FROM users LEFT JOIN locations ON users.location_id = locations.id")->fetchAll(PDO::FETCH_ASSOC);
$locations = $pdo->query("SELECT * FROM locations")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h2>Manage Users</h2>
    <table>
        <thead>
            <tr>
                <th>Username</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($user['username']) ?></strong> 
                    </td>
                    <td>
                        <form method="POST" class="user-form">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">

                            <div class="form-group">
                                <label>Location:</label>
                                <select name="location_id">
                                    <option value="">None</option>
                                    <?php foreach ($locations as $location): ?>
                                        <option value="<?= $location['id'] ?>" <?= ($user['location_id'] == $location['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($location['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Currency:</label>
                                <input type="number" name="currency" value="<?= $user['currency'] ?>" min="0">
                            </div>

                            <div class="form-group">
                                <label>Role:</label>
                                <select name="role">
                                    <option value="pc" <?= ($user['role'] == 'pc') ? 'selected' : '' ?>>PC</option>
                                    <option value="admin" <?= ($user['role'] == 'admin') ? 'selected' : '' ?>>Admin</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>New Password:</label>
                                <input type="password" name="new_password" placeholder="Leave blank to keep current">
                            </div>

                            <div class="action-buttons">
                                <button type="submit" class="btn">Update</button>
                                <a href="?delete=<?= $user['id'] ?>" class="btn-delete" onclick="return confirm('Delete this user?');">Delete</a>
                            </div>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a href="dashboard.php" class="btn">Back to Dashboard</a>
</div>

<?php require 'footer.php'; // Include the footer ?>
