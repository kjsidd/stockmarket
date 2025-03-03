<?php
require_once '../config/db.php';

$stock_id = $_GET['stock_id'] ?? '';

if (empty($stock_id)) {
    die(json_encode(['error' => 'Stock ID is required']));
}

// Fetch historical data
$stmt = $pdo->prepare("
    SELECT date, price FROM stock_prices 
    WHERE stock_id = ? 
    ORDER BY date ASC
");
$stmt->execute([$stock_id]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($data);
?>