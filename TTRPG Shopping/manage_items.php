<?php
require 'auth.php';
require 'config.php';
require 'header.php';

if ($_SESSION["role"] !== "admin") {
    die("Access denied.");
}

// D&D Rarity Options
$rarities = ["Common", "Uncommon", "Rare", "Very Rare", "Legendary", "Artifact"];

// Handle item creation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["name"], $_POST["type"], $_POST["rarity"], $_POST["base_price"]) && empty($_POST["id"])) {
    $stmt = $pdo->prepare("INSERT INTO items (name, description, type, rarity, base_price) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([trim($_POST["name"]), trim($_POST["description"]), trim($_POST["type"]), trim($_POST["rarity"]), intval($_POST["base_price"])]);
}

// Handle item updates
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id"], $_POST["name"], $_POST["type"], $_POST["rarity"], $_POST["base_price"])) {
    $stmt = $pdo->prepare("UPDATE items SET name = ?, description = ?, type = ?, rarity = ?, base_price = ? WHERE id = ?");
    $stmt->execute([$_POST["name"], $_POST["description"], $_POST["type"], $_POST["rarity"], $_POST["base_price"], $_POST["id"]]);
}

// Handle item deletion
if (isset($_GET["delete"])) {
    $stmt = $pdo->prepare("DELETE FROM items WHERE id = ?");
    $stmt->execute([$_GET["delete"]]);
}

// Fetch items
$items = $pdo->query("SELECT * FROM items")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h2>Manage Items</h2>

    <!-- Add New Item -->
    <div class="card">
        <h3>Add New Item</h3>
        <form method="POST" class="item-form">
            <div class="form-group">
                <label>Item Name: </label>
                <input type="text" name="name" required>
            </div>

            <div class="form-group">
                <label>Description:</label>
                <textarea name="description" required></textarea>
            </div>

            <div class="form-group">
                <label>Item Type:  </label>
                <input type="text" name="type">
            </div>

            <div class="form-group">
                <label>Rarity: </label>
                <select name="rarity" required>
                    <?php foreach ($rarities as $rarity): ?>
                        <option value="<?= $rarity ?>"><?= $rarity ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Base Price (in copper):</label>
                <input type="number" name="base_price" required>
            </div>

            <button type="submit">Add Item</button>
        </form>
    </div>

    <!-- Edit/Delete Items -->
    <div class="card">
    <h3>Edit Items</h3>
    <h3>Filter Items</h3>
<div>
    <input type="text" id="searchBox" onkeyup="filterItems()" placeholder="Search by name...">
    
    <select id="typeFilter" onchange="filterItems()">
        <option value="">All Types</option>
        <?php 
        // Collect unique types from the items
        $uniqueTypes = array_unique(array_column($items, 'type'));
        foreach ($uniqueTypes as $type): ?>
            <option value="<?= htmlspecialchars($type) ?>"><?= htmlspecialchars($type) ?></option>
        <?php endforeach; ?>
    </select>

    <select id="rarityFilter" onchange="filterItems()">
        <option value="">All Rarities</option>
        <?php foreach ($rarities as $rarity): ?>
            <option value="<?= $rarity ?>"><?= $rarity ?></option>
        <?php endforeach; ?>
    </select>
</div>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th>Type</th>
                <th>Rarity</th>
                <th>Base Price</th>
                <th>Actions</th>
            </tr>
        </thead>
  <tbody>
    <?php foreach ($items as $item): ?>
    <tr 
        class="item-row" 
        data-name="<?= strtolower(htmlspecialchars($item['name'])) ?>" 
        data-type="<?= strtolower(htmlspecialchars($item['type'])) ?>" 
        data-rarity="<?= strtolower(htmlspecialchars($item['rarity'])) ?>"
    >
        <form method="POST">
            <input type="hidden" name="id" value="<?= $item['id'] ?>">
            <td><input type="text" name="name" value="<?= htmlspecialchars($item['name']) ?>" required></td>
            <td><textarea name="description" required><?= htmlspecialchars($item['description']) ?></textarea></td>
            <td><input type="text" name="type" value="<?= htmlspecialchars($item['type']) ?>"></td>
            <td>
                <select name="rarity" required>
                    <?php foreach ($rarities as $rarity): ?>
                        <option value="<?= $rarity ?>" <?= ($rarity == $item['rarity']) ? "selected" : "" ?>>
                            <?= $rarity ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td><input type="number" name="base_price" value="<?= $item['base_price'] ?>" required></td>
            <td class="action-buttons">
                <button type="submit" class="btn">Update</button>
            </td>
        </form>
        <td class="action-buttons">
            <form method="GET" onsubmit="return confirm('Delete this item?');">
                <input type="hidden" name="delete" value="<?= $item['id'] ?>">
                <button type="submit" class="btn-delete">Delete</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</tbody>

    </table>
</div>


    <a href="dashboard.php" class="btn-back">Back to Dashboard</a>
</div>

<script>
function filterItems() {
    let searchQuery = document.getElementById("searchBox").value.toLowerCase();
    let selectedType = document.getElementById("typeFilter").value.toLowerCase();
    let selectedRarity = document.getElementById("rarityFilter").value.toLowerCase();
    let rows = document.getElementsByClassName("item-row");

    for (let row of rows) {
        let itemName = row.getAttribute("data-name");
        let itemType = row.getAttribute("data-type");
        let itemRarity = row.getAttribute("data-rarity");

        // Check if the row matches the filter criteria
        let matchesSearch = searchQuery === "" || itemName.includes(searchQuery);
        let matchesType = selectedType === "" || itemType === selectedType;
        let matchesRarity = selectedRarity === "" || itemRarity === selectedRarity;

        // Show or hide rows based on filters
        if (matchesSearch && matchesType && matchesRarity) {
            row.style.display = "table-row";
        } else {
            row.style.display = "none";
        }
    }
}
</script>

<?php require 'footer.php'; ?>
