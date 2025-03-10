<?php
require 'auth.php';
require 'config.php';
require 'header.php';

if ($_SESSION["role"] !== "admin") {
    die("Access denied.");
}

// Fetch players
$players = $pdo->query("SELECT id, username FROM users WHERE role = 'pc'")->fetchAll(PDO::FETCH_ASSOC);

// Fetch items
$items = $pdo->query("SELECT * FROM items")->fetchAll(PDO::FETCH_ASSOC);

// Handle inventory quantity update
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["inventory_id"]) && isset($_POST["quantity"])) {
    $inventory_id = $_POST["inventory_id"];
    $quantity = max(0, intval($_POST["quantity"]));

    if ($quantity == 0) {
        $stmt = $pdo->prepare("DELETE FROM player_inventory WHERE id = ?");
        $stmt->execute([$inventory_id]);
    } else {
        $stmt = $pdo->prepare("UPDATE player_inventory SET quantity = ? WHERE id = ?");
        $stmt->execute([$quantity, $inventory_id]);
    }
}

// Handle deleting an item
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_inventory_id"])) {
    $delete_id = $_POST["delete_inventory_id"];
    $stmt = $pdo->prepare("DELETE FROM player_inventory WHERE id = ?");
    $stmt->execute([$delete_id]);
}

// Handle adding a new item to a player's inventory
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["new_item_id"], $_POST["new_player_id"], $_POST["new_quantity"])) {
    $new_item_id = $_POST["new_item_id"];
    $new_player_id = $_POST["new_player_id"];
    $new_quantity = max(1, intval($_POST["new_quantity"]));

    // Check if the item already exists in inventory
    $stmt = $pdo->prepare("SELECT id, quantity FROM player_inventory WHERE user_id = ? AND item_id = ?");
    $stmt->execute([$new_player_id, $new_item_id]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        $stmt = $pdo->prepare("UPDATE player_inventory SET quantity = ? WHERE id = ?");
        $stmt->execute([$existing['quantity'] + $new_quantity, $existing['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO player_inventory (user_id, item_id, quantity) VALUES (?, ?, ?)");
        $stmt->execute([$new_player_id, $new_item_id, $new_quantity]);
    }
}

// Fetch selected player's inventory
$player_inventory = [];
if (isset($_GET["player_id"])) {
    $stmt = $pdo->prepare("
        SELECT player_inventory.id, player_inventory.quantity, items.name 
        FROM player_inventory 
        JOIN items ON player_inventory.item_id = items.id 
        WHERE player_inventory.user_id = ?
    ");
    $stmt->execute([$_GET["player_id"]]);
    $player_inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="container">
    <h2>Manage Player Inventory</h2>

    <h3>Select Player</h3>
    <form method="GET" class="styled-form">
        <select name="player_id" onchange="this.form.submit()">
            <option value="">Choose a player...</option>
            <?php foreach ($players as $player): ?>
                <option value="<?= $player['id'] ?>" <?= isset($_GET["player_id"]) && $_GET["player_id"] == $player['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($player['username']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
    <br>

    <?php if (isset($_GET["player_id"])): ?>
        <h3>Player's Inventory</h3>
        <table class="styled-table">
            <tr>
                <th>Item</th>
                <th>Quantity</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($player_inventory as $inventory): ?>
                <tr>
                    <td><?= htmlspecialchars($inventory['name']) ?></td>
                    <td>
                        <form method="POST" class="inventory-form" style="display:inline-block;">
                            <input type="hidden" name="inventory_id" value="<?= $inventory['id'] ?>">
                            <input type="number" name="quantity" min="0" value="<?= $inventory['quantity'] ?>">
                            <button type="submit" class="btn">Update</button>
                        </form>
                    </td>
                    <td>
                        <form method="POST" class="inventory-form" style="display:inline-block;">
                            <input type="hidden" name="delete_inventory_id" value="<?= $inventory['id'] ?>">
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <h3>Add Item to Player</h3>

        <input type="text" id="searchBox" placeholder="Search item by name..." onkeyup="filterItems()">
        <select id="typeFilter" onchange="filterItems()">
            <option value="">Filter by Type</option>
            <?php
            $types = array_unique(array_column($items, 'type'));
            foreach ($types as $type) {
                echo "<option value='" . htmlspecialchars($type) . "'>" . htmlspecialchars($type) . "</option>";
            }
            ?>
        </select>
        <select id="rarityFilter" onchange="filterItems()">
            <option value="">Filter by Rarity</option>
            <?php
            $rarities = array_unique(array_column($items, 'rarity'));
            foreach ($rarities as $rarity) {
                echo "<option value='" . htmlspecialchars($rarity) . "'>" . htmlspecialchars($rarity) . "</option>";
            }
            ?>
        </select>

        <form method="POST" class="styled-form">
            <input type="hidden" name="new_player_id" value="<?= $_GET["player_id"] ?>">

            <select name="new_item_id" id="itemDropdown">
                <?php foreach ($items as $item): ?>
                    <option value="<?= $item['id'] ?>" data-type="<?= htmlspecialchars($item['type']) ?>" data-rarity="<?= htmlspecialchars($item['rarity']) ?>">
                        <?= htmlspecialchars($item['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Quantity:</label>
            <input type="number" name="new_quantity" min="1" required>
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
