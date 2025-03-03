<?php
require_once 'config/db.php';
require_once 'config/api_key.php';

$symbol = $_GET['symbol'] ?? 'IBM';

// Get time series data
$url = "https://www.alphavantage.co/query?function=TIME_SERIES_DAILY&symbol=$symbol&apikey=".ALPHA_VANTAGE_API_KEY;
$data = json_decode(file_get_contents($url), true);

$dates = [];
$prices = [];
foreach ($data['Time Series (Daily)'] as $date => $values) {
    $dates[] = $date;
    $prices[] = $values['4. close'];
}

// Get fundamental data
$url = "https://www.alphavantage.co/query?function=OVERVIEW&symbol=$symbol&apikey=".ALPHA_VANTAGE_API_KEY;
$fundamentals = json_decode(file_get_contents($url), true);

header('Content-Type: application/json');
echo json_encode([
    'dates' => array_reverse($dates),
    'prices' => array_reverse($prices),
    'pe_ratio' => $fundamentals['PERatio'] ?? 'N/A',
    'market_cap' => $fundamentals['MarketCapitalization'] ?? 'N/A',
    'dividend_yield' => $fundamentals['DividendYield'] ?? 'N/A'
]);
?>