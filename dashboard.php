<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

redirect_if_not_logged_in();

// Fix: Ensure session balance exists
if (!isset($_SESSION['balance'])) {
    $_SESSION['balance'] = 100000.00; // Default balance
}

// Get portfolio summary (Fixed SQL Query)
$stmt = $pdo->prepare("
    SELECT SUM(p.quantity * s.current_price) AS total_value,
           SUM((s.current_price - p.purchase_price) * p.quantity) AS total_pl
    FROM portfolio p
    JOIN stocks s ON p.stock_id = s.id  -- Fixed ON clause
    WHERE p.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$summary = $stmt->fetch(PDO::FETCH_ASSOC);

// Ensure data exists
$total_value = $summary['total_value'] ?? 0;
$total_pl = $summary['total_pl'] ?? 0;

// Get recent transactions (Fixed SQL Query)
$stmt = $pdo->prepare("
    SELECT t.*, s.symbol 
    FROM transactions t
    JOIN stocks s ON t.stock_id = s.id  -- Fixed ON clause
    WHERE t.user_id = ?
    ORDER BY t.transaction_date DESC 
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="mb-0">Portfolio Overview</h4>
                </div>
                <div class="card-body">
                    <canvas id="portfolioChart"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="mb-0">Quick Stats</h4>
                </div>
                <div class="card-body">
                    <div class="stat-card mb-3">
                        <h6>Account Balance</h6>
                        <div class="h4 text-success">₹<?= number_format($_SESSION['balance'], 2) ?></div>
                    </div>
                    <div class="stat-card mb-3">
                        <h6>Portfolio Value</h6>
                        <div class="h4 text-primary">₹<?= number_format($total_value, 2) ?></div>
                    </div>
                    <div class="stat-card">
                        <h6>Total P&L</h6>
                        <div class="h4 <?= $total_pl >= 0 ? 'text-success' : 'text-danger' ?>">
                            ₹<?= number_format($total_pl, 2) ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Recent Transactions</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($transactions)): ?>
                        <?php foreach($transactions as $trans): ?>
                            <div class="transaction-item mb-2">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <strong><?= htmlspecialchars($trans['symbol']) ?></strong>
                                        <div class="text-muted small"><?= htmlspecialchars($trans['type']) ?></div>
                                    </div>
                                    <div class="text-end">
                                        <div class="<?= $trans['type'] === 'BUY' ? 'text-success' : 'text-danger' ?>">
                                            ₹<?= number_format($trans['price'], 2) ?>
                                        </div>
                                        <div class="text-muted small">
                                            <?= date('d M H:i', strtotime($trans['transaction_date'])) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No recent transactions.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Portfolio Chart
const ctx = document.getElementById('portfolioChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
            label: 'Portfolio Value',
            data: [100000, 105000, 110000, 115000, 120000, 125000],
            borderColor: '#007bff',
            tension: 0.1
        }]
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
