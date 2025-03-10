<?php
require 'auth.php';
require 'config.php';
require 'header.php'; // Include the header

// Ensure only players can access this page
if ($_SESSION["role"] !== "pc") {
    die("Access denied.");
}

$user_id = $_SESSION["user_id"];

// Fetch the player's inventory
$stmt = $pdo->prepare("SELECT items.name, player_inventory.quantity FROM player_inventory JOIN items ON player_inventory.item_id = items.id WHERE player_inventory.user_id = ?");
$stmt->execute([$user_id]);
$inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch user currency
$stmt = $pdo->prepare("SELECT currency FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$currency = intval($user['currency']);

// Convert currency
$platinum = intdiv($currency, 1000);
$currency %= 1000;
$gold = intdiv($currency, 100);
$currency %= 100;
$electrum = intdiv($currency, 50);
$currency %= 50;
$silver = intdiv($currency, 10);
$copper = $currency % 10;
?>

<div class="container">
    <h2>Your Currency:</h2>
    <ul class="currency-list">
        <li>Platinum: <?= $platinum ?></li>
        <li>Gold: <?= $gold ?></li>
        <li>Electrum: <?= $electrum ?></li>
        <li>Silver: <?= $silver ?></li>
        <li>Copper: <?= $copper ?></li>
    </ul>
    <h2>Your Inventory</h2>
    <?php if (empty($inventory)): ?>
        <p>You have no items.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($inventory as $item): ?>
                <li><?= htmlspecialchars($item['name']) ?> - Quantity: <?= $item['quantity'] ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>



    <div class="action-buttons">
        <a href="dashboard.php">Back to Dashboard</a>
    </div>
</div>

<?php require 'footer.php'; // Include the footer ?>