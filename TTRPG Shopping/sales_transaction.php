<?php
require 'auth.php';
require 'config.php';
require 'header.php'; // Include the header

// Ensure only admins can access this page
if ($_SESSION["role"] !== "admin") {
    die("Access denied.");
}

// Handle marking sales as removed
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["sale_id"])) {
    $sale_id = $_POST["sale_id"];
    $stmt = $pdo->prepare("UPDATE sales SET removed = TRUE WHERE id = ?");
    $stmt->execute([$sale_id]);
}

// Fetch all sales, newest first
$stmt = $pdo->query("
    SELECT sales.id, users.username, items.name AS item_name, 
       sales.quantity, sales.sale_price, sales.sale_date, sales.removed
FROM sales
JOIN users ON sales.user_id = users.id
JOIN items ON sales.item_id = items.id
ORDER BY sales.sale_date DESC
");
$sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <div class="content-box">
        <h2 class="page-title">Sales Transaction Log</h2>
        <div class="table-container">
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Player</th>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Sale Price</th>
                        <th>Date</th>
                        <th>Updated in Game</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sales as $sale): ?>
                        <tr class="<?= $sale["removed"] ? 'added' : '' ?>">
                            <td><?= $sale["id"] ?></td>
                            <td><?= htmlspecialchars($sale["username"]) ?></td>
                            <td><?= htmlspecialchars($sale["item_name"]) ?></td>
                            <td><?= $sale["quantity"] ?></td>
                            <td><?= $sale["sale_price"] ?> copper</td>
                            <td><?= $sale["sale_date"] ?></td>
                            <td>
                                <?php if ($sale["removed"]): ?>
                                    <span class="status-marked">✅</span>
                                <?php else: ?>
                                    <form method="POST" class="inline-form">
                                        <input type="hidden" name="sale_id" value="<?= $sale["id"] ?>">
                                        <button type="submit" class="btn-mark">Mark as Updated</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <br>
    <a href="dashboard.php" class="btn-back">Back to Dashboard</a>
</div>

<?php require 'footer.php'; ?>
