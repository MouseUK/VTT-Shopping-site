<?php
require 'auth.php';
require 'config.php';
require 'header.php'; // Include the header

if ($_SESSION["role"] !== "admin") {
    die("Access denied.");
}

// Handle shop creation (only when id is not set)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["name"], $_POST["location_id"], $_POST["image_url"], $_POST["description"]) && empty($_POST["id"])) {
    $stmt = $pdo->prepare("INSERT INTO shops (name, location_id, image_url, description) VALUES (?, ?, ?, ?)");
    $stmt->execute([trim($_POST["name"]), $_POST["location_id"], trim($_POST["image_url"]), trim($_POST["description"])]);
}

// Handle shop updates
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id"], $_POST["name"], $_POST["location_id"], $_POST["image_url"], $_POST["description"])) {
    $stmt = $pdo->prepare("UPDATE shops SET name = ?, location_id = ?, image_url = ?, description = ? WHERE id = ?");
    $stmt->execute([$_POST["name"], $_POST["location_id"], $_POST["image_url"], $_POST["description"], $_POST["id"]]);
}

// Handle shop deletion
if (isset($_GET["delete"])) {
    $stmt = $pdo->prepare("DELETE FROM shops WHERE id = ?");
    $stmt->execute([$_GET["delete"]]);
}

// Fetch locations and shops
$locations = $pdo->query("SELECT * FROM locations")->fetchAll(PDO::FETCH_ASSOC);
$shops = $pdo->query("SELECT shops.*, locations.name AS location_name FROM shops JOIN locations ON shops.location_id = locations.id")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h2>Manage Shops</h2>
    
    <!-- Add New Shop -->
    <h3>Add New Shop</h3>
    <form method="POST" class="styled-form">
        <input type="text" name="name" placeholder="Shop Name" required>
        <select name="location_id" required>
            <?php foreach ($locations as $location): ?>
                <option value="<?= $location['id'] ?>"><?= htmlspecialchars($location['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="text" name="image_url" placeholder="Image URL">
        <textarea name="description" placeholder="Shop Description"></textarea>
        <button type="submit" class="btn">Add Shop</button>
    </form>
    
    <!-- Edit/Delete Shops -->
    <h3>Edit Shops</h3>
    <table>
        <thead>
            <tr>
                <th>Shop Name</th>
                <th>Location</th>
                <th>Image URL</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($shops as $shop): ?>
                <tr>
                    <form method="POST" class="shop-form">
                        <input type="hidden" name="id" value="<?= $shop['id'] ?>">
                        <td><input type="text" name="name" value="<?= htmlspecialchars($shop['name']) ?>" required></td>
                        <td>
                            <select name="location_id">
                                <?php foreach ($locations as $location): ?>
                                    <option value="<?= $location['id'] ?>" <?= $shop['location_id'] == $location['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($location['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><input type="text" name="image_url" value="<?= htmlspecialchars($shop['image_url']) ?>" placeholder="Image URL"></td>
                        <td><textarea name="description" placeholder="Shop Description"><?= htmlspecialchars($shop['description']) ?></textarea></td>
                        <td>
                            <button type="submit" class="btn">Update</button>
                            <a href="?delete=<?= $shop['id'] ?>" class="btn-delete" onclick="return confirm('Delete this shop?');">Delete</a>
                        </td>
                    </form>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <a href="dashboard.php" class="btn">Back to Dashboard</a>
</div>

<?php require 'footer.php'; // Include the footer ?>
