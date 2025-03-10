<?php
require 'auth.php';
require 'config.php';
require 'header.php'; // Include the header

// Ensure only admins can access this page
if ($_SESSION["role"] !== "admin") {
    die("Access denied.");
}

// Handle marking transactions as added
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["transaction_id"])) {
    $transaction_id = $_POST["transaction_id"];
    $stmt = $pdo->prepare("UPDATE transactions SET added = TRUE WHERE id = ?");
    $stmt->execute([$transaction_id]);
}

// Fetch all transactions, newest first
$stmt = $pdo->query("
    SELECT transactions.id, users.username, shops.name AS shop_name, items.name AS item_name, 
           transactions.quantity, transactions.total_price, transactions.purchase_date, transactions.added
    FROM transactions
    JOIN users ON transactions.user_id = users.id
    JOIN shops ON transactions.shop_id = shops.id
    JOIN items ON transactions.item_id = items.id
    ORDER BY transactions.purchase_date DESC
");
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <div class="content-box">
        <h2 class="page-title">Transaction Log</h2>
        <div class="table-container">
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Player</th>
                        <th>Shop</th>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Total Price</th>
                        <th>Date</th>
                        <th>Added</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $transaction): ?>
                        <tr class="<?= $transaction["added"] ? 'added' : '' ?>">
                            <td><?= $transaction["id"] ?></td>
                            <td><?= htmlspecialchars($transaction["username"]) ?></td>
                            <td><?= htmlspecialchars($transaction["shop_name"]) ?></td>
                            <td><?= htmlspecialchars($transaction["item_name"]) ?></td>
                            <td><?= $transaction["quantity"] ?></td>
                            <td><?= $transaction["total_price"] ?> copper</td>
                            <td><?= $transaction["purchase_date"] ?></td>
                            <td>
                                <?php if ($transaction["added"]): ?>
                                    <span class="status-marked">✅</span>
                                <?php else: ?>
                                    <form method="POST" class="inline-form">
                                        <input type="hidden" name="transaction_id" value="<?= $transaction["id"] ?>">
                                        <button type="submit" class="btn-mark">Mark as Added</button>
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



<?php require 'footer.php'; // Include the footer ?>
