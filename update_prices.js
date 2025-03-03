async function updateStockPrices() {
    try {
        const response = await fetch('api/update_prices.php');
        const result = await response.json();
        
        if(result.success) {
            console.log('Prices updated:', result.updated);
            // Refresh portfolio and market data
            if(typeof loadPortfolio === 'function') loadPortfolio();
            if(typeof loadMarketData === 'function') loadMarketData();
        } else {
            console.error('Price update failed:', result.message);
        }
    } catch(error) {
        console.error('Error updating prices:', error);
    }
}

// Update prices every 5 minutes
setInterval(updateStockPrices, 300000);

// Initial update
updateStockPrices();