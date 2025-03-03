<?php
require_once '../config/db.php';

$query = $_GET['query'] ?? '';

if (empty($query)) {
    echo json_encode(['error' => 'Search query is empty']);
    exit;
}

// Check if data is cached
$stmt = $pdo->prepare("SELECT data, last_updated FROM stock_cache WHERE symbol = ?");
$stmt->execute([$query]);
$cachedData = $stmt->fetch(PDO::FETCH_ASSOC);

if ($cachedData && (time() - strtotime($cachedData['last_updated'])) < 3600) {
    // Use cached data if it's less than 1 hour old
    echo $cachedData['data'];
    exit;
}

// Fetch new data from RapidAPI
$apiKey = '20dd0c7da3msh31594a77559a2dbp10cc59jsna326ee8bf7fd';
$host = 'indian-stock-exchange-api2.p.rapidapi.com';
$url = "https://indian-stock-exchange-api2.p.rapidapi.com/corporate_actions?stock_name=$query";

$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_HTTPHEADER => [
        "X-RapidAPI-Host: $host",
        "X-RapidAPI-Key: $apiKey"
    ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
    echo json_encode(['error' => "cURL Error: $err"]);
} else {
    $data = json_decode($response, true);

    if (isset($data['error'])) {
        echo json_encode(['error' => $data['error']]);
    } else {
        // Format the response
        $formattedData = [
            'symbol' => $query,
            'name' => '', // The API doesn't provide the company name
            'current_price' => 0, // This API doesn't provide live prices
            'last_updated' => date('Y-m-d H:i:s'),
            'corporate_actions' => $data // Include corporate actions
        ];

        // Cache the new data
        $stmt = $pdo->prepare("
            INSERT INTO stock_cache (symbol, data) 
            VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE data = ?, last_updated = NOW()
        ");
        $stmt->execute([$query, json_encode([$formattedData]), json_encode([$formattedData])]);

        echo json_encode([$formattedData]);
    }
}
?>