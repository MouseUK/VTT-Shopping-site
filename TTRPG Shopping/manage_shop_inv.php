<?php
require 'auth.php';
require 'config.php';
require 'header.php'; // Include the header

if ($_SESSION["role"] !== "admin") {
    die("Access denied.");
}

// Handle shop-wide price modifier update
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["shop_id"], $_POST["shop_price_modifier"])) {
    $shop_id = $_POST["shop_id"];
    $shop_price_modifier = floatval($_POST["shop_price_modifier"]);

    $stmt = $pdo->prepare("UPDATE shops SET price_modifier = ? WHERE id = ?");
    $stmt->execute([$shop_price_modifier, $shop_id]);
}

// Handle shop inventory updates
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["inventory_id"])) {
    $inventory_id = $_POST["inventory_id"];
    $stock = intval($_POST["stock"]);
    $price_modifier = floatval($_POST["price_modifier"]);

    $stmt = $pdo->prepare("UPDATE shop_inventory SET stock = ?, price_modifier = ? WHERE id = ?");
    $stmt->execute([$stock, $price_modifier, $inventory_id]);
}

// Handle adding a new item to a shop
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["new_item_id"], $_POST["new_shop_id"], $_POST["new_stock"], $_POST["new_price_modifier"])) {
    $new_item_id = $_POST["new_item_id"];
    $new_shop_id = $_POST["new_shop_id"];
    $new_stock = intval($_POST["new_stock"]);
    $new_price_modifier = floatval($_POST["new_price_modifier"]);

    $stmt = $pdo->prepare("INSERT INTO shop_inventory (shop_id, item_id, stock, price_modifier) VALUES (?, ?, ?, ?)");
    $stmt->execute([$new_shop_id, $new_item_id, $new_stock, $new_price_modifier]);
}

// Fetch locations
$locations = $pdo->query("SELECT * FROM locations")->fetchAll(PDO::FETCH_ASSOC);

// Fetch shops (filtered by location if selected)
$shops = [];
if (isset($_GET["location_id"])) {
    $stmt = $pdo->prepare("SELECT * FROM shops WHERE location_id = ?");
    $stmt->execute([$_GET["location_id"]]);
    $shops = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch items
$items = $pdo->query("SELECT * FROM items")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h2>Manage Shop Inventories</h2>

    <h3>Select Location</h3>
    <form method="GET" class="styled-form">
        <select name="location_id" onchange="this.form.submit()">
            <option value="">Choose a location...</option>
            <?php foreach ($locations as $location): ?>
                <option value="<?= $location['id'] ?>" <?= isset($_GET["location_id"]) && $_GET["location_id"] == $location['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($location['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
    <br>

    <?php if (isset($_GET["location_id"])): ?>
        <h3>Select Shop</h3>
        <form method="GET" class="styled-form">
            <input type="hidden" name="location_id" value="<?= $_GET["location_id"] ?>">
            <select name="shop_id" onchange="this.form.submit()">
                <option value="">Choose a shop...</option>
                <?php foreach ($shops as $shop): ?>
                    <option value="<?= $shop['id'] ?>" <?= isset($_GET["shop_id"]) && $_GET["shop_id"] == $shop['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($shop['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
        <br>
    <?php endif; ?>

    <?php if (isset($_GET["shop_id"])): 
        $shop_id = $_GET["shop_id"];
        $stmt = $pdo->prepare("SELECT * FROM shops WHERE id = ?");
        $stmt->execute([$shop_id]);
        $shop = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT shop_inventory.id, shop_inventory.stock, shop_inventory.price_modifier, items.name, items.base_price FROM shop_inventory JOIN items ON shop_inventory.item_id = items.id WHERE shop_inventory.shop_id = ?");
        $stmt->execute([$shop_id]);
        $shop_inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <h3>Update Shop Price Modifier</h3>
    <form method="POST" class="styled-form">
        <input type="hidden" name="shop_id" value="<?= $shop_id ?>">
        <label>Shop-Wide Price Modifier:</label>
        <input type="number" step="0.1" name="shop_price_modifier" value="<?= $shop['price_modifier'] ?? 1.0 ?>">
        <button type="submit" class="btn">Update</button>
    </form>

    <h3>Manage Shop Inventory</h3>
    <table class="styled-table">
        <tr>
            <th>Item</th>
            <th>Base Price</th>
            <th>Stock</th>
            <th>Price Modifier</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($shop_inventory as $inventory): ?>
            <tr>
                <td><?= htmlspecialchars($inventory['name']) ?></td>
                <td><?= $inventory['base_price'] ?> copper</td>
                <td>
                    <form method="POST" class="shop-form">
                        <input type="hidden" name="inventory_id" value="<?= $inventory['id'] ?>">
                        <input type="number" name="stock" min="0" value="<?= $inventory['stock'] ?>">
                        <input type="number" step="0.1" name="price_modifier" value="<?= $inventory['price_modifier'] ?>">
                        <button type="submit" class="btn">Update</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

<h3>Add Item to Shop</h3>

<!-- Search & Filter Form -->
<input type="text" id="searchBox" placeholder="Search item by name..." onkeyup="filterItems()">
<select id="typeFilter" onchange="filterItems()">
    <option value="">Filter by Type</option>
    <?php
    $types = array_unique(array_column($items, 'type')); // Get unique types
    foreach ($types as $type) {
        echo "<option value='" . htmlspecialchars($type) . "'>" . htmlspecialchars($type) . "</option>";
    }
    ?>
</select>
<select id="rarityFilter" onchange="filterItems()">
    <option value="">Filter by Rarity</option>
    <?php
    $rarities = array_unique(array_column($items, 'rarity')); // Get unique rarities
    foreach ($rarities as $rarity) {
        echo "<option value='" . htmlspecialchars($rarity) . "'>" . htmlspecialchars($rarity) . "</option>";
    }
    ?>
</select>

<!-- Item Selection Form -->
<form method="POST" class="styled-form">
    <input type="hidden" name="new_shop_id" value="<?= $shop_id ?>">

    <select name="new_item_id" id="itemDropdown">
        <?php foreach ($items as $item): ?>
            <option value="<?= $item['id'] ?>" data-type="<?= htmlspecialchars($item['type']) ?>" data-rarity="<?= htmlspecialchars($item['rarity']) ?>">
                <?= htmlspecialchars($item['name']) ?> (Base Price: <?= $item['base_price'] ?>)
            </option>
        <?php endforeach; ?>
    </select>

    <label>Stock:</label>
    <input type="number" name="new_stock" min="1" required>
    <label>Price Modifier:</label>
    <input type="number" step="0.1" name="new_price_modifier" value="1.0">
    <button type="submit" class="btn">Add</button>
</form>

    <?php endif; ?>
    <br>
    <a href="dashboard.php" class="btn">Back to Dashboard</a>
</div>

<script>
function filterItems() {
    let searchQuery = document.getElementById("searchBox").value.toLowerCase();
    let selectedType = document.getElementById("typeFilter").value.toLowerCase();
    let selectedRarity = document.getElementById("rarityFilter").value.toLowerCase();
    let dropdown = document.getElementById("itemDropdown");
    
    for (let option of dropdown.options) {
        let itemName = option.text.toLowerCase();
        let itemType = option.getAttribute("data-type").toLowerCase();
        let itemRarity = option.getAttribute("data-rarity").toLowerCase();
        
        // Show only items that match the search/filter criteria
        if (
            (searchQuery === "" || itemName.includes(searchQuery)) &&
            (selectedType === "" || itemType === selectedType) &&
            (selectedRarity === "" || itemRarity === selectedRarity)
        ) {
            option.style.display = "block";
        } else {
            option.style.display = "none";
        }
    }
}
</script>

<?php require 'footer.php'; ?>

