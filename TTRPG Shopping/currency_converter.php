<?php
require 'header.php';

function convertCurrency($pp, $gp, $ep, $sp, $cp) {
    // Conversion rates (all values in copper)
    $conversion = [
        "pp" => 1000, // 1 Platinum = 1000 Copper
        "gp" => 100,  // 1 Gold = 100 Copper
        "ep" => 50,   // 1 Electrum = 50 Copper
        "sp" => 10,   // 1 Silver = 10 Copper
        "cp" => 1     // 1 Copper = 1 Copper
    ];

    // Convert everything to total copper
    $totalCopper = ($pp * $conversion["pp"]) + ($gp * $conversion["gp"]) +
                   ($ep * $conversion["ep"]) + ($sp * $conversion["sp"]) + $cp;

    // Save the total copper before modifying
    $originalTotalCopper = $totalCopper;

    // Convert back to optimal coinage
    $optimal = [];
    $optimal["pp"] = intdiv($totalCopper, 1000);
    $totalCopper %= 1000;
    $optimal["gp"] = intdiv($totalCopper, 100);
    $totalCopper %= 100;
    $optimal["ep"] = intdiv($totalCopper, 50);
    $totalCopper %= 50;
    $optimal["sp"] = intdiv($totalCopper, 10);
    $totalCopper %= 10;
    $optimal["cp"] = $totalCopper;

    // Alternative breakdowns (single denomination) using original copper total
    $singleCoin = [
        "cp" => $originalTotalCopper,
        "sp" => $originalTotalCopper / 10,
        "ep" => $originalTotalCopper / 50,
        "gp" => $originalTotalCopper / 100,
        "pp" => $originalTotalCopper / 1000
    ];

    return ["optimal" => $optimal, "singleCoin" => $singleCoin, "totalCopper" => $originalTotalCopper];
}

// Handle form submission
$result = [];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pp = intval($_POST["pp"] ?? 0);
    $gp = intval($_POST["gp"] ?? 0);
    $ep = intval($_POST["ep"] ?? 0);
    $sp = intval($_POST["sp"] ?? 0);
    $cp = intval($_POST["cp"] ?? 0);

    $result = convertCurrency($pp, $gp, $ep, $sp, $cp);
}
?>

<div class="container">
    <h2>D&D Currency Converter</h2>
    <p>Enter your coin amounts to convert them into the optimal distribution.</p>

    <form method="POST">
        <div class="form-group">
            <label>Platinum (PP):</label>
            <input type="number" name="pp" min="0" value="0">
        </div>
        <div class="form-group">
            <label>Gold (GP):</label>
            <input type="number" name="gp" min="0" value="0">
        </div>
        <div class="form-group">
            <label>Electrum (EP):</label>
            <input type="number" name="ep" min="0" value="0">
        </div>
        <div class="form-group">
            <label>Silver (SP):</label>
            <input type="number" name="sp" min="0" value="0">
        </div>
        <div class="form-group">
            <label>Copper (CP):</label>
            <input type="number" name="cp" min="0" value="0">
        </div>

        <button type="submit" class="btn">Convert</button>
    </form>

    <?php if (!empty($result)): ?>
        <h3>Optimal Coin Breakdown:</h3>
        <ul class="currency-list">
            <li>Platinum: <?= $result["optimal"]["pp"] ?></li>
            <li>Gold: <?= $result["optimal"]["gp"] ?></li>
            <li>Electrum: <?= $result["optimal"]["ep"] ?></li>
            <li>Silver: <?= $result["optimal"]["sp"] ?></li>
            <li>Copper: <?= $result["optimal"]["cp"] ?></li>
        </ul>

        <h3>Alternative Single Coin Conversions:</h3>
        <ul class="currency-list">
            <li>All Copper: <?= number_format($result["singleCoin"]["cp"]) ?> CP</li>
            <li>All Silver: <?= number_format($result["singleCoin"]["sp"]) ?> SP</li>
            <li>All Electrum: <?= number_format($result["singleCoin"]["ep"]) ?> EP</li>
            <li>All Gold: <?= number_format($result["singleCoin"]["gp"]) ?> GP</li>
            <li>All Platinum: <?= number_format($result["singleCoin"]["pp"], 3) ?> PP</li>
        </ul>
    <?php endif; ?>

    <a href="dashboard.php" class="btn-back">Back to Dashboard</a>
</div>

<?php require 'footer.php'; ?>
