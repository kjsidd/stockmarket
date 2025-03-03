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
                        <button class="btn btn-sm btn-primary" onclick="addToWatchlist('${stock.symbol}')">
                            Add to Watchlist
                        </button>
                        <button class="btn btn-sm btn-success" onclick="viewStock('${stock.symbol}')">
                            View Details
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