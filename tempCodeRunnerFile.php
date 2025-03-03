<?php
require_once 'includes/header.php';
require_once 'config/db.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8">
            <!-- Search Form -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="mb-0">Stock Search</h4>
                </div>
                <div class="card-body">
                    <form id="searchForm">
                        <div class="input-group mb-3">
                            <input type="text" id="searchQuery" class="form-control" placeholder="Search for a stock (e.g., AAPL, RELIANCE)" required>
                            <button type="submit" class="btn btn-primary">Search</button>
                        </div>
                    </form>
                    <div id="searchResults" class="mt-3">
                        <!-- Search results will be displayed here -->
                    </div>
                </div>
            </div>

            <!-- Stock Chart -->
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Stock Chart</h4>
                </div>
                <div class="card-body">
                    <div id="chartContainer">
                        <!-- Chart will be rendered here -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Watchlist -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Your Watchlist</h4>
                </div>
                <div class="card-body" id="watchlist">
                    <!-- Watchlist items will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- JavaScript for Search and Chart -->
<script>
document.getElementById('searchForm').addEventListener('submit', function (e) {
    e.preventDefault(); // Prevent form submission

    const query = document.getElementById('searchQuery').value;
    const resultsDiv = document.getElementById('searchResults');

    // Clear previous results
    resultsDiv.innerHTML = '<div class="text-center">Loading...</div>';

    // Fetch search results
    fetch(`api/search_stocks.php?query=${encodeURIComponent(query)}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log("Search Results:", data); // Debug: Log results to console
            if (data.error) {
                resultsDiv.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
                return;
            }

            if (data.length === 0) {
                resultsDiv.innerHTML = '<div class="alert alert-warning">No stocks found.</div>';
                return;
            }

            // Display results
            const html = data.map(stock => `
                <div class="card mb-2">
                    <div class="card-body">
                        <h5 class="card-title">${stock.symbol} - ${stock.name}</h5>
                        <p class="card-text">
                            Price: ₹${stock.current_price.toFixed(2)}
                        </p>
                        <button class="btn btn-sm btn-primary" onclick="viewStock('${stock.symbol}')">
                            View Details
                        </button>
                        <button class="btn btn-sm btn-success" onclick="showStockChart(${stock.id}, '${stock.symbol}')">
                            Show Chart
                        </button>
                    </div>
                </div>
            `).join('');

            resultsDiv.innerHTML = html;
        })
        .catch(error => {
            console.error("Error:", error); // Debug: Log errors to console
            resultsDiv.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
        });
});

function showStockChart(stockId, symbol) {
    // Fetch historical data for the chart
    fetch(`api/stock_chart.php?stock_id=${stockId}`)
        .then(response => response.json())
        .then(data => {
            renderStockChart(symbol, data);
        })
        .catch(error => {
            console.error("Error fetching chart data:", error);
            alert("Failed to load chart data.");
        });
}

function renderStockChart(symbol, data) {
    const chartContainer = document.getElementById('chartContainer');
    chartContainer.innerHTML = '<canvas id="stockChart"></canvas>';

    const ctx = document.getElementById('stockChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(entry => entry.date),
            datasets: [{
                label: `${symbol} Price`,
                data: data.map(entry => entry.price),
                borderColor: '#007bff',
                tension: 0.1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: false
                }
            }
        }
    });
}

// Load watchlist on page load
loadWatchlist();

async function loadWatchlist() {
    const resultsDiv = document.getElementById('watchlist');

    try {
        const response = await fetch('api/watchlist.php');
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        const watchlist = await response.json();

        const watchlistHTML = watchlist.map(stock => `
            <div class="d-flex justify-content-between mb-2">
                <div>${stock.symbol}</div>
                <div>
                    <button class="btn btn-sm btn-info" onclick="loadStockData('${stock.symbol}')">View</button>
                    <button class="btn btn-sm btn-danger" onclick="removeFromWatchlist('${stock.symbol}')">×</button>
                </div>
            </div>
        `).join('');

        resultsDiv.innerHTML = watchlistHTML;
    } catch (error) {
        console.error('Error loading watchlist:', error);
        resultsDiv.innerHTML = '<div class="alert alert-danger">Failed to load watchlist.</div>';
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>