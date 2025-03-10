<?php
require 'auth.php'; // Ensures the user is logged in
require 'config.php';
require 'header.php'; // Include the header

// Fetch user data (currency and location)
$stmt = $pdo->prepare("SELECT currency, location_id FROM users WHERE id = ?");
$stmt->execute([$_SESSION["user_id"]]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("<div class='container'><p class='error'>Error: User data not found.</p></div>");
}

$currency = intval($user['currency']); // Ensure it's an integer
$location_id = intval($user['location_id']); // Ensure it's an integer

// Fetch location details
$location_name = "Unknown";
$location_description = "No description available.";
$location_image = "default.jpg"; // Fallback image

if ($location_id) {
    $stmt = $pdo->prepare("SELECT name, description, image_url FROM locations WHERE id = ?");
    $stmt->execute([$location_id]);
    $location = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($location) {
        $location_name = htmlspecialchars($location['name']);
        $location_description = htmlspecialchars($location['description']);
        $location_image = htmlspecialchars($location['image_url']);
    }
}

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
    <div class="card">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h1>

        <?php if ($_SESSION["role"] === "admin"): ?>
            <p class="role">You are logged in as an <strong>admin</strong>.</p>
            <div class="action-buttons">
                <a href="manage_users.php" class="btn">Manage Users</a>
                <a href="manage_player_inventory.php" class="btn">Manage Users Inventory</a>
                <a href="manage_locations.php" class="btn">Manage Locations</a>
                <a href="manage_shops.php" class="btn">Manage Shops</a>
                <a href="manage_shop_inv.php" class="btn">Manage Shop Inventory</a>
                <a href="manage_items.php" class="btn">Manage Items</a>
                <a href="manage_transactions.php" class="btn">Manage Transactions</a>
                <a href="sales_transaction.php" class="btn">Manage Sales</a>
            </div>
        <?php else: ?>
            <p class="role">You are logged in as a <strong>player</strong>.</p>
            <h4>__________________________________________________________</h4>
            <h3>Current Location:</h3>
            <p class="location"><?php echo $location_name; ?></p>
            <!-- Display Location Image -->
            <div class="location-box">
                <img src="<?php echo $location_image; ?>" style="max-width:50%;height:auto;" alt="Location Image" class="location-image">
                
                <p class="location-description"><?php echo $location_description; ?></p>
            </div>
            <h4>__________________________________________________________</h4>
            <h3>Your Currency:</h3>
            <ul class="currency-list">
                <li>Platinum: <?php echo $platinum; ?></li>
                <li>Gold: <?php echo $gold; ?></li>
                <li>Electrum: <?php echo $electrum; ?></li>
                <li>Silver: <?php echo $silver; ?></li>
                <li>Copper: <?php echo $copper; ?></li>
            </ul>

            <div class="action-buttons">
                <a href="player_inventory.php" class="btn">View Inventory</a>
                <a href="shop_list.php" class="btn">View Available Shops</a>
            </div>
        <?php endif; ?>
        <br>
        <a href="logout.php" class="btn btn-delete">Logout</a>
    </div>
</div>

<?php require 'footer.php'; ?>
