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
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json(); // Parse response as JSON
        })
        .then(data => {
            if (data.error) {
                resultsDiv.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
                return;
            }

            if (data.length === 0) {
                resultsDiv.innerHTML = '<div class="alert alert-warning">No stocks found.</div>';
                return;
            }

            // Display results
            const html = data.map(stock => {
                return `
                    <div class="card mb-2">
                        <div class="card-body">
                            <h5 class="card-title">${stock.symbol}</h5>
                            <p class="card-text">
                                Corporate Actions: ${stock.corporate_actions.length}
                            </p>
                            <button class="btn btn-sm btn-primary" onclick="viewStock('${stock.symbol}')">
                                View Details
                            </button>
                        </div>
                    </div>
                `;
            }).join('');

            resultsDiv.innerHTML = html;
        })
        .catch(error => {
            console.error("Error:", error); // Debug: Log errors to console
            resultsDiv.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
        });
});