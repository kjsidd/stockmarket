-- Create Database
CREATE DATABASE IF NOT EXISTS virtual_trading;
USE virtual_trading;

-- Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    balance DECIMAL(15, 2) DEFAULT 100000.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Stocks Table
CREATE TABLE stocks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    symbol VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    current_price DECIMAL(15, 2) NOT NULL,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Portfolio Table
CREATE TABLE portfolio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    stock_id INT,
    quantity INT NOT NULL,
    purchase_price DECIMAL(15, 2),
    purchase_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (stock_id) REFERENCES stocks(id)
);

-- Transactions Table
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    stock_id INT,
    type ENUM('BUY', 'SELL') NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(15, 2) NOT NULL,
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (stock_id) REFERENCES stocks(id)
);

CREATE TABLE stock_prices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stock_id INT,
    date DATE NOT NULL,
    price DECIMAL(15, 2) NOT NULL,
    FOREIGN KEY (stock_id) REFERENCES stocks(id)
);

-- Watchlist Table
CREATE TABLE watchlist (
    user_id INT,
    stock_id INT,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, stock_id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (stock_id) REFERENCES stocks(id)
);

-- Insert Sample Users
INSERT INTO users (username, password) VALUES
('john_doe', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'), -- password: password
('jane_smith', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'); -- password: password

-- Insert Sample Stocks
INSERT INTO stocks (symbol, name, current_price) VALUES
('AAPL', 'Apple Inc.', 185.32),
('GOOGL', 'Alphabet Inc.', 135.45),
('MSFT', 'Microsoft Corporation', 330.21),
('AMZN', 'Amazon.com Inc.', 128.76),
('TSLA', 'Tesla Inc.', 250.50),
('RELIANCE', 'Reliance Industries', 2750.50),
('TCS', 'Tata Consultancy Services', 3450.75),
('INFY', 'Infosys Ltd', 1500.00),
('HDFCBANK', 'HDFC Bank Ltd', 1650.25);

-- Insert Sample Portfolio Data
INSERT INTO portfolio (user_id, stock_id, quantity, purchase_price) VALUES
(1, 1, 10, 180.00), -- John owns 10 AAPL shares
(1, 3, 5, 320.00),  -- John owns 5 MSFT shares
(2, 2, 20, 130.00); -- Jane owns 20 GOOGL shares

-- Insert Sample Transactions
INSERT INTO transactions (user_id, stock_id, type, quantity, price) VALUES
(1, 1, 'BUY', 10, 180.00),
(1, 3, 'BUY', 5, 320.00),
(2, 2, 'BUY', 20, 130.00);

-- Insert Sample Watchlist Data
INSERT INTO watchlist (user_id, stock_id) VALUES
(1, 2), -- John added GOOGL to watchlist
(1, 4), -- John added AMZN to watchlist
(2, 1); -- Jane added AAPL to watchlist