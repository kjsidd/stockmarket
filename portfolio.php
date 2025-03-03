<?php
require_once 'includes/header.php';
require_once 'config/db.php';
require_once 'includes/auth.php';
redirect_if_not_logged_in();

// Get user portfolio
$stmt = $pdo->prepare("
    SELECT p.*, s.symbol, s.current_price 
    FROM portfolio p 
    JOIN stocks s ON p.stock_id = s.id 
    WHERE p.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$portfolio = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate totals
$total_value = 0;
$total_profit = 0;
foreach ($portfolio as $item) {
    $current_value = $item['quantity'] * $item['current_price'];
    $total_value += $current_value;
    $total_profit += ($current_value - ($item['quantity'] * $item['purchase_price']));
}
?>
<div class="container mt-4">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Your Portfolio</h4>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="stat-card bg-light p-3">
                                <h5>Total Value</h5>
                                <div class="h3 text-primary">₹<?= number_format($total_value, 2) ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="stat-card bg-light p-3">
                                <h5>Total Profit/Loss</h5>
                                <div class="h3 <?= $total_profit >= 0 ? 'text-success' : 'text-danger' ?>">
                                    ₹<?= number_format($total_profit, 2) ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Stock</th>
                                <th>Quantity</th>
                                <th>Avg. Cost</th>
                                <th>LTP</th>
                                <th>Current Value</th>
                                <th>P&L</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($portfolio as $item): 
                                $current_value = $item['quantity'] * $item['current_price'];
                                $profit = $current_value - ($item['quantity'] * $item['purchase_price']);
                            ?>
                            <tr>
                                <td><?= $item['symbol'] ?></td>
                                <td><?= $item['quantity'] ?></td>
                                <td>₹<?= number_format($item['purchase_price'], 2) ?></td>
                                <td>₹<?= number_format($item['current_price'], 2) ?></td>
                                <td>₹<?= number_format($current_value, 2) ?></td>
                                <td class="<?= $profit >= 0 ? 'text-success' : 'text-danger' ?>">
                                    ₹<?= number_format($profit, 2) ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-danger" 
                                            onclick="sellStock('<?= $item['symbol'] ?>', <?= $item['stock_id'] ?>)">
                                        Sell
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function sellStock(symbol, stockId) {
    const quantity = prompt(`Enter quantity to sell of ${symbol}:`);
    if (quantity) {
        fetch('api/trade.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                stock_id: stockId,
                quantity: quantity,
                type: 'SELL'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) location.reload();
            else alert(data.message);
        });
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
