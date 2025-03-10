<?php
require 'auth.php';
require 'config.php';
require 'header.php'; // Include the header

// Ensure only players can access this page
if ($_SESSION["role"] !== "pc") {
    die("Access denied.");
}

// Get the player's location
$user_id = $_SESSION["user_id"];
$stmt = $pdo->prepare("SELECT location_id FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !$user["location_id"]) {
    die("You do not have a location set. Contact an admin.");
}

// Get the shops in the player's location
$stmt = $pdo->prepare("SELECT * FROM shops WHERE location_id = ?");
$stmt->execute([$user["location_id"]]);
$shops = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h2>Shops in your location</h2>
    
    <?php if (empty($shops)): ?>
        <p>No shops available in your location.</p>
    <?php else: ?>
        <ul class="shop-list">
            <?php foreach ($shops as $shop): ?>
                <li class="shop-item">
                    <a href="shop_inventory.php?shop_id=<?= $shop['id'] ?>" class="shop-link">
                        <?= htmlspecialchars($shop['name']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    
    <div class="action-buttons">
        <a href="dashboard.php" class="btn">Back to Dashboard</a>
    </div>
</div>

<?php require 'footer.php'; // Include the footer ?>