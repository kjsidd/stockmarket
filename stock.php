<?php
require_once 'includes/header.php';
require_once 'config/db.php';

$symbol = $_GET['symbol'] ?? '';

if (empty($symbol)) {
    header("Location: market.php");
    exit;
}

// Fetch stock details
$stmt = $pdo->prepare("SELECT * FROM stocks WHERE symbol = ?");
$stmt->execute([$symbol]);
$stock = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$stock) {
    echo "<div class='alert alert-danger'>Stock not found.</div>";
    require_once 'includes/footer.php';
    exit;
}
?>
<div class="container mt-4">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0"><?= $stock['symbol'] ?> - <?= $stock['name'] ?></h4>
                </div>
                <div class="card-body">
                    <p>Current Price: ₹<?= number_format($stock['current_price'], 2) ?></p>
                    <p>Last Updated: <?= $stock['last_updated'] ?></p>
                    <button class="btn btn-primary" onclick="buyStock('<?= $stock['symbol'] ?>')">
                        Buy Stock
                    </button>
                    <button class="btn btn-success" onclick="addToWatchlist('<?= $stock['symbol'] ?>')">
                        Add to Watchlist
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function buyStock(symbol) {
    const quantity = prompt(`Enter quantity to buy for ${symbol}:`);
    if (quantity) {
        // Implement buy functionality
        alert(`Buying ${quantity} shares of ${symbol}`);
    }
}

function addToWatchlist(symbol) {
    // Implement add to watchlist functionality
    alert(`Added ${symbol} to watchlist!`);
}
</script>

<?php require_once 'includes/footer.php'; ?>