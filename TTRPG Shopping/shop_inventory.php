<?php
require 'auth.php';
require 'config.php';
require 'header.php'; // Include the header

// Ensure only players can access this page
if ($_SESSION["role"] !== "pc") {
    die("Access denied.");
}

// Get shop ID from URL
if (!isset($_GET["shop_id"])) {
    die("Shop not specified.");
}

$shop_id = $_GET["shop_id"];

// Fetch shop details
$stmt = $pdo->prepare("SELECT * FROM shops WHERE id = ?");
$stmt->execute([$shop_id]);
$shop = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$shop) {
    die("Shop not found.");
}

// Fetch the player's current currency
$user_id = $_SESSION["user_id"];
$stmt = $pdo->prepare("SELECT currency FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$player_currency = $user["currency"];

// Handle item purchase
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["inventory_id"])) {
    $inventory_id = $_POST["inventory_id"];
    $quantity = max(1, intval($_POST['quantity'] ?? 1));

    // Fetch item details
    $stmt = $pdo->prepare(
        "SELECT shop_inventory.id, items.id AS item_id, items.name, shop_inventory.stock, 
                (items.base_price * shop_inventory.price_modifier) AS price 
         FROM shop_inventory 
         JOIN items ON shop_inventory.item_id = items.id 
         WHERE shop_inventory.id = ? AND shop_inventory.shop_id = ?"
    );
    $stmt->execute([$inventory_id, $shop_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    $total_price = $item['price'] * $quantity;

    if (!$item || $item['stock'] < $quantity) {
        echo "<p style='color: red;'>Not enough stock available.</p>";
    } elseif ($player_currency < $total_price) {
        echo "<p style='color: red;'>Not enough currency.</p>";
    } else {
        // Deduct price from player currency
        $new_currency = $player_currency - $total_price;
        $stmt = $pdo->prepare("UPDATE users SET currency = ? WHERE id = ?");
        $stmt->execute([$new_currency, $user_id]);

        // Reduce stock in shop inventory
        $stmt = $pdo->prepare("UPDATE shop_inventory SET stock = stock - ? WHERE id = ?");
        $stmt->execute([$quantity, $inventory_id]);

        // Add item to player inventory
        // Check if player already owns the item
$stmt = $pdo->prepare("SELECT id, quantity FROM player_inventory WHERE user_id = ? AND item_id = ?");
$stmt->execute([$user_id, $item['item_id']]);
$inventory_entry = $stmt->fetch(PDO::FETCH_ASSOC);

if ($inventory_entry) {
    // Player already has the item, update quantity
    $stmt = $pdo->prepare("UPDATE player_inventory SET quantity = quantity + ? WHERE id = ?");
    $stmt->execute([$quantity, $inventory_entry['id']]);
} else {
    // Player does not own the item, insert new record
    $stmt = $pdo->prepare("INSERT INTO player_inventory (user_id, item_id, quantity) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $item['item_id'], $quantity]);
}

        // Record transaction
        $stmt = $pdo->prepare("INSERT INTO transactions (user_id, shop_id, item_id, quantity, total_price, purchase_date, added) 
                               VALUES (?, ?, ?, ?, ?, NOW(), FALSE)");
        $stmt->execute([$user_id, $shop_id, $item['item_id'], $quantity, $total_price]);

        echo "<p style='color: green;'>Purchased " . htmlspecialchars($quantity) . " × " . htmlspecialchars($item['name']) . "!</p>";
    }
}

// Fetch filter parameters
$type_filter = $_GET['type'] ?? '';
$rarity_filter = $_GET['rarity'] ?? '';
$availability_filter = isset($_GET['in_stock']);

// Fetch items in the shop's inventory
$query = "
    SELECT shop_inventory.id, 
           items.name, 
           items.type,
           items.rarity,
           shop_inventory.stock, 
           (items.base_price * shop_inventory.price_modifier) AS price
    FROM shop_inventory 
    JOIN items ON shop_inventory.item_id = items.id 
    WHERE shop_inventory.shop_id = ?";

$params = [$shop_id];

// Apply filters
if ($type_filter) {
    $query .= " AND items.type = ?";
    $params[] = $type_filter;
}
if ($rarity_filter) {
    $query .= " AND items.rarity = ?";
    $params[] = $rarity_filter;
}
if ($availability_filter) {
    $query .= " AND shop_inventory.stock > 0";
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch distinct types and rarities for filtering
$type_stmt = $pdo->prepare("
    SELECT DISTINCT items.type 
    FROM shop_inventory 
    JOIN items ON shop_inventory.item_id = items.id 
    WHERE shop_inventory.shop_id = ? 
    ORDER BY items.type
");
$type_stmt->execute([$shop_id]);
$types = $type_stmt->fetchAll(PDO::FETCH_COLUMN);

$rarity_stmt = $pdo->query("SELECT DISTINCT rarity FROM items ORDER BY rarity");
$rarities = $rarity_stmt->fetchAll(PDO::FETCH_COLUMN);

// Function to convert copper to platinum, gold, electrum, silver, and copper
function formatCurrency($copper) {
    $platinum = intdiv($copper, 1000);
    $copper %= 1000;
    $gold = intdiv($copper, 100);
    $copper %= 100;
    $electrum = intdiv($copper, 50);
    $copper %= 50;
    $silver = intdiv($copper, 10);
    $copper = $copper % 10;
    return "{$platinum} pp, {$gold} gp, {$electrum} ep, {$silver} sp, {$copper} cp";
}

// Function to return a color based on item rarity
function getRarityColor($rarity) {
    switch ($rarity) {
        case 'Common': return 'gray';
        case 'Uncommon': return 'green';
        case 'Rare': return 'blue';
        case 'Very Rare': return 'purple';
        case 'Legendary': return 'gold';
        default: return 'black';
    }
}
?>

<style>
    .inventory-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 10px;
    }

    .card {
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        background: white;
    }

    .card strong {
        display: block;
        font-size: 1.1em;
    }

    .action-buttons {
        margin-top: 5px;
    }
</style>

<div class="container">
    <h2><?= htmlspecialchars($shop['name']) ?> Inventory</h2>

    <!-- Shop Image & Description -->
    <?php if (!empty($shop['image_url'])): ?>
        <img src="<?= htmlspecialchars($shop['image_url']) ?>" alt="<?= htmlspecialchars($shop['name']) ?>" width="200">
    <?php endif; ?>
    <p><?= nl2br(htmlspecialchars($shop['description'])) ?></p>

    <p><strong>Your Currency:</strong> <?= formatCurrency($player_currency) ?></p>

    <!-- Filtering Form -->
    <form method="GET">
        <input type="hidden" name="shop_id" value="<?= $shop_id ?>">
        <label for="type">Type:</label>
        <select name="type" id="type">
            <option value="">All</option>
            <?php foreach ($types as $type): ?>
                <option value="<?= htmlspecialchars($type) ?>" <?= ($type_filter === $type) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($type) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="rarity">Rarity:</label>
        <select name="rarity" id="rarity">
            <option value="">All</option>
            <?php foreach ($rarities as $rarity): ?>
                <option value="<?= htmlspecialchars($rarity) ?>" <?= ($rarity_filter === $rarity) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($rarity) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>
            <input type="checkbox" name="in_stock" value="1" <?= $availability_filter ? 'checked' : '' ?>>
            In-stock only
        </label>

        <button type="submit">Filter</button>
    </form>

    <?php if (empty($items)): ?>
        <p>No items available in this shop.</p>
<?php else: ?>
    <div class="inventory-list">
        <?php foreach ($items as $item): ?>
            <div class="card">
                <strong><?= htmlspecialchars($item['name']) ?></strong><br>
                <span style="color:<?= getRarityColor($item['rarity']) ?>; display: inline-block;">
                    <strong><?= htmlspecialchars($item['rarity']) ?></strong>
                </span>, <?= htmlspecialchars($item['type']) ?><br><br>

                <strong>Price:</strong> <?= formatCurrency($item['price']) ?><br><br>
                <strong>Stock:</strong> <?= $item['stock'] ?>

                <?php if ($item['stock'] > 0): ?>
                    <form method="POST" class="action-buttons">
                        <input type="hidden" name="inventory_id" value="<?= $item['id'] ?>">
                        <label for="quantity-<?= $item['id'] ?>"><strong>Purchase Quantity:</strong></label>
                        <input type="number" id="quantity-<?= $item['id'] ?>" name="quantity" min="1" max="<?= $item['stock'] ?>" value="1" required>
                        <button type="submit" class="btn">Buy</button>
                    </form>
                <?php else: ?>
                    <span style="color: red;">(Out of stock)</span>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>


    <a href="shop_list.php" class="btn">Back to Shops</a>
</div>

<?php require 'footer.php'; // Include the footer ?>
