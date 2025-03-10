<?php
// config.php - Database connection setup
require 'auth.php'; // Ensures the user is logged in
require 'config.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>

<?php
// locations.php - Manage locations
require 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["name"])) {
    $name = trim($_POST["name"]);
    $stmt = $pdo->prepare("INSERT INTO locations (name) VALUES (?)");
    $stmt->execute([$name]);
}

$locations = $pdo->query("SELECT * FROM locations")->fetchAll(PDO::FETCH_ASSOC);
?>

<form method="POST">
    <input type="text" name="name" placeholder="New Location" required>
    <button type="submit">Add Location</button>
</form>

<ul>
    <?php foreach ($locations as $location): ?>
        <li><?= htmlspecialchars($location['name']) ?></li>
    <?php endforeach; ?>
</ul>

<?php
// shops.php - Manage shops
require 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $location_id = $_POST["location_id"];
    $stmt = $pdo->prepare("INSERT INTO shops (name, location_id) VALUES (?, ?)");
    $stmt->execute([$name, $location_id]);
}

$shops = $pdo->query("SELECT shops.*, locations.name AS location_name FROM shops JOIN locations ON shops.location_id = locations.id")->fetchAll(PDO::FETCH_ASSOC);
$locations = $pdo->query("SELECT * FROM locations")->fetchAll(PDO::FETCH_ASSOC);
?>

<form method="POST">
    <input type="text" name="name" placeholder="Shop Name" required>
    <select name="location_id" required>
        <?php foreach ($locations as $location): ?>
            <option value="<?= $location['id'] ?>"><?= htmlspecialchars($location['name']) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit">Add Shop</button>
</form>

<ul>
    <?php foreach ($shops as $shop): ?>
        <li><?= htmlspecialchars($shop['name']) ?> (<?= htmlspecialchars($shop['location_name']) ?>)</li>
    <?php endforeach; ?>
</ul>

<?php
// inventory.php - Manage shop inventory
require 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $shop_id = $_POST["shop_id"];
    $item_id = $_POST["item_id"];
    $stock = $_POST["stock"];
    $price = $_POST["price"];
    $stmt = $pdo->prepare("INSERT INTO shop_inventory (shop_id, item_id, stock, price) VALUES (?, ?, ?, ?)");
    $stmt->execute([$shop_id, $item_id, $stock, $price]);
}

$inventory = $pdo->query("SELECT shop_inventory.*, shops.name AS shop_name, items.name AS item_name FROM shop_inventory JOIN shops ON shop_inventory.shop_id = shops.id JOIN items ON shop_inventory.item_id = items.id")->fetchAll(PDO::FETCH_ASSOC);
$shops = $pdo->query("SELECT * FROM shops")->fetchAll(PDO::FETCH_ASSOC);
$items = $pdo->query("SELECT * FROM items")->fetchAll(PDO::FETCH_ASSOC);
?>

<form method="POST">
    <select name="shop_id" required>
        <?php foreach ($shops as $shop): ?>
            <option value="<?= $shop['id'] ?>"><?= htmlspecialchars($shop['name']) ?></option>
        <?php endforeach; ?>
    </select>
    <select name="item_id" required>
        <?php foreach ($items as $item): ?>
            <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['name']) ?></option>
        <?php endforeach; ?>
    </select>
    <input type="number" name="stock" placeholder="Stock" required>
    <input type="number" name="price" placeholder="Price (Copper)" required>
    <button type="submit">Add to Inventory</button>
</form>

<ul>
    <?php foreach ($inventory as $entry): ?>
        <li><?= htmlspecialchars($entry['shop_name']) ?> - <?= htmlspecialchars($entry['item_name']) ?> (Stock: <?= $entry['stock'] ?>, Price: <?= $entry['price'] ?> copper)</li>
    <?php endforeach; ?>
</ul>
