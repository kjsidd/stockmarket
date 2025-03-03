async function loadPortfolio() {
    const response = await fetch('api/portfolio.php');
    const portfolio = await response.json();
    
    const portfolioHTML = portfolio.map(item => `
        <tr>
            <td>${item.symbol}</td>
            <td>${item.quantity}</td>
            <td>$${item.current_price}</td>
            <td>$${(item.quantity * item.current_price).toFixed(2)}</td>
            <td class="${item.profit >= 0 ? 'text-success' : 'text-danger'}">
                $${item.profit.toFixed(2)}
            </td>
        </tr>
    `).join('');
    
    document.getElementById('portfolioItems').innerHTML = portfolioHTML;
}

async function executeTrade(type) {
    const symbol = document.getElementById('tradeSymbol').value;
    const quantity = document.getElementById('tradeQuantity').value;
    
    const response = await fetch('api/trade.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            symbol,
            quantity,
            type
        })
    });
    
    const result = await response.json();
    if (result.success) {
        loadPortfolio();
        alert('Trade executed successfully!');
    } else {
        alert(result.message);
    }
}

// Initial load
loadPortfolio();