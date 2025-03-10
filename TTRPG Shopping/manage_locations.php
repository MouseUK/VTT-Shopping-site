<?php
require 'auth.php';
require 'config.php';
require 'header.php'; // Include the header

if ($_SESSION["role"] !== "admin") {
    die("Access denied.");
}

// Handle location creation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["name"], $_POST["image_url"], $_POST["description"]) && empty($_POST["id"])) {
    $stmt = $pdo->prepare("INSERT INTO locations (name, image_url, description) VALUES (?, ?, ?)");
    $stmt->execute([trim($_POST["name"]), trim($_POST["image_url"]), trim($_POST["description"])]);
}

// Handle location updates
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id"], $_POST["name"], $_POST["image_url"], $_POST["description"])) {
    $stmt = $pdo->prepare("UPDATE locations SET name = ?, image_url = ?, description = ? WHERE id = ?");
    $stmt->execute([$_POST["name"], $_POST["image_url"], $_POST["description"], $_POST["id"]]);
}

// Handle location deletion
if (isset($_GET["delete"])) {
    $stmt = $pdo->prepare("DELETE FROM locations WHERE id = ?");
    $stmt->execute([$_GET["delete"]]);
}

// Fetch locations
$locations = $pdo->query("SELECT * FROM locations")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h2>Manage Locations</h2>

    <h3>Add New Location</h3>
    <form method="POST" class="form-group">
        <input type="text" name="name" placeholder="Location Name" required>
        <input type="text" name="image_url" placeholder="Image URL">
        <textarea name="description" placeholder="Location Description"></textarea>
        <button type="submit" class="btn">Add Location</button>
    </form>

<h3>Edit Locations</h3>
<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Image</th>
            <th>Description</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($locations as $location): ?>
            <tr>
                <form method="POST" class="inline-form"> <!-- Form starts here -->
                    <input type="hidden" name="id" value="<?= $location['id'] ?>">
                    <td><input type="text" name="name" value="<?= htmlspecialchars($location['name']) ?>" required></td>
                    <td><input type="text" name="image_url" value="<?= htmlspecialchars($location['image_url']) ?>" placeholder="Image URL"></td>
                    <td><textarea name="description" placeholder="Location Description"><?= htmlspecialchars($location['description']) ?></textarea></td>
                    <td>
                        <button type="submit" class="btn">Update</button>
                        <a href="?delete=<?= $location['id'] ?>" class="btn-delete" onclick="return confirm('Delete this location?');">Delete</a>
                    </td>
                </form> <!-- Form ends here -->
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>


    <a href="dashboard.php" class="btn">Back to Dashboard</a>
</div>

<?php require 'footer.php'; // Include the footer ?>