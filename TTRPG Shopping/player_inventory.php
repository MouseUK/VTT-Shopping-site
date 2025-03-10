<?php
require 'auth.php';
require 'config.php';
require 'header.php';

// Ensure only players can access this page
if ($_SESSION["role"] !== "pc") {
    die("Access denied.");
}

$user_id = $_SESSION["user_id"];

// Handle item selling
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["sell_item_id"])) {
    $sell_item_id = intval($_POST["sell_item_id"]);

    // Fetch item details
    $stmt = $pdo->prepare("SELECT items.name, items.base_price, player_inventory.quantity 
                           FROM player_inventory 
                           JOIN items ON player_inventory.item_id = items.id 
                           WHERE player_inventory.user_id = ? AND player_inventory.item_id = ?");
    $stmt->execute([$user_id, $sell_item_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($item && $item['quantity'] > 0) {
        // Calculate sale price (65%-85% of base price)
        $sale_percentage = rand(65, 85) / 100;
        $sale_price = round($item['base_price'] * $sale_percentage);

        // Remove item or decrease quantity
        if ($item['quantity'] == 1) {
            $stmt = $pdo->prepare("DELETE FROM player_inventory WHERE user_id = ? AND item_id = ?");
            $stmt->execute([$user_id, $sell_item_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE player_inventory SET quantity = quantity - 1 WHERE user_id = ? AND item_id = ?");
            $stmt->execute([$user_id, $sell_item_id]);
        }

        // Update player currency
        $stmt = $pdo->prepare("UPDATE users SET currency = currency + ? WHERE id = ?");
        $stmt->execute([$sale_price, $user_id]);

        // Insert into sales table
        $stmt = $pdo->prepare("INSERT INTO sales (user_id, item_id, quantity, sale_price, sale_date) 
                               VALUES (?, ?, 1, ?, NOW())");
        $stmt->execute([$user_id, $sell_item_id, $sale_price]);

        echo "<script>alert('You sold {$item['name']} for ' + formatCurrency($sale_price));</script>";
    }
}

// Fetch player inventory
$stmt = $pdo->prepare("SELECT items.id, items.name, items.description, items.base_price, items.type, items.rarity, player_inventory.quantity 
                       FROM player_inventory 
                       JOIN items ON player_inventory.item_id = items.id 
                       WHERE player_inventory.user_id = ?");
$stmt->execute([$user_id]);
$inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch user currency
$stmt = $pdo->prepare("SELECT currency FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$currency = intval($user['currency']);

// Convert currency function
function convertCurrency($amount) {
    $platinum = intdiv($amount, 1000);
    $amount %= 1000;
    $gold = intdiv($amount, 100);
    $amount %= 100;
    $electrum = intdiv($amount, 50);
    $amount %= 50;
    $silver = intdiv($amount, 10);
    $copper = $amount % 10;

    if ($platinum > 0) return "{$platinum}pp";
    if ($gold > 0) return "{$gold}gp";
    if ($electrum > 0) return "{$electrum}ep";
    if ($silver > 0) return "{$silver}sp";
    return "{$copper}cp";
}
?>

<div class="container">
    <h2>Your Currency:</h2>
    <ul class="currency-list">
        <li>Platinum: <?= intdiv($currency, 1000) ?></li>
        <li>Gold: <?= intdiv($currency % 1000, 100) ?></li>
        <li>Electrum: <?= intdiv($currency % 100, 50) ?></li>
        <li>Silver: <?= intdiv($currency % 50, 10) ?></li>
        <li>Copper: <?= $currency % 10 ?></li>
    </ul>

    <h2>Your Inventory</h2>

    <?php if (empty($inventory)): ?>
        <p>You have no items.</p>
    <?php else: ?>
        <table class="styled-table">
            <tr>
                <th>Item</th>
                <th>Description</th>
                <th>Type</th>
                <th>Rarity</th>
                <th>Quantity</th>
                <th>Base Price</th>
                <th>Sell</th>
            </tr>
            <?php foreach ($inventory as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['name']) ?></td>
                    <td><?= htmlspecialchars($item['description']) ?></td>
                    <td><?= htmlspecialchars($item['type']) ?></td>
                    <td><?= htmlspecialchars($item['rarity']) ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td><?= convertCurrency($item['base_price']) ?></td>
                    <td>
                     <form method="POST" onsubmit="return confirmSale(this, <?= $item['base_price'] ?>);">
                         <input type="hidden" name="sell_item_id" value="<?= $item['id'] ?>">
                        <button type="submit" class="btn">Sell</button>
                     </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <div class="action-buttons">
        <a href="dashboard.php">Back to Dashboard</a>
    </div>
</div>

<script>
function confirmSale(form, basePrice) {
    // Calculate randomized sale price (65%-75%)
    let salePercentage = Math.floor(Math.random() * (75 - 65 + 1)) + 65;
    let salePrice = Math.round(basePrice * (salePercentage / 100));

    // Convert to currency breakdown
    let platinum = Math.floor(salePrice / 1000);
    let gold = Math.floor((salePrice % 1000) / 100);
    let electrum = Math.floor((salePrice % 100) / 50);
    let silver = Math.floor((salePrice % 50) / 10);
    let copper = salePrice % 10;

    // Construct the message
    let message = `Are you sure you want to sell this item for:\n`;
    if (platinum > 0) message += `${platinum}pp `;
    if (gold > 0) message += `${gold}gp `;
    if (electrum > 0) message += `${electrum}ep `;
    if (silver > 0) message += `${silver}sp `;
    if (copper > 0) message += `${copper}cp`;

    // Show confirmation dialog
    return confirm(message.trim()); // Returns true if "OK", false if "Cancel"
}
</script>

<?php require 'footer.php'; ?>
