-- POS Shoe Shop Database Schema
-- SQLite Database

-- Create Shoes table
CREATE TABLE IF NOT EXISTS Shoes (
    Shoe_Id INTEGER PRIMARY KEY AUTOINCREMENT,
    custom_shoe_id TEXT UNIQUE,
    size TEXT NOT NULL,
    price REAL NOT NULL,
    stock INTEGER DEFAULT 0,
    cost_price REAL NOT NULL,
    selling_price REAL NOT NULL
);

-- Create Users table with roles
CREATE TABLE IF NOT EXISTS Users (
    username TEXT PRIMARY KEY,
    password TEXT NOT NULL,
    role TEXT DEFAULT 'user' CHECK (role IN ('admin', 'owner', 'user'))
);

-- Create Sales table
CREATE TABLE IF NOT EXISTS Sales (
    Sell_Id INTEGER PRIMARY KEY AUTOINCREMENT,
    date TEXT NOT NULL
);

-- Create Sale_Items table
CREATE TABLE IF NOT EXISTS Sale_Items (
    Sale_Item_Id INTEGER PRIMARY KEY AUTOINCREMENT,
    Sell_Id INTEGER NOT NULL,
    Shoe_Id INTEGER NOT NULL,
    quantity INTEGER NOT NULL,
    total REAL NOT NULL,
    FOREIGN KEY (Sell_Id) REFERENCES Sales(Sell_Id),
    FOREIGN KEY (Shoe_Id) REFERENCES Shoes(Shoe_Id)
);

-- Create Suppliers table
CREATE TABLE IF NOT EXISTS Suppliers (
    Sup_Id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    mobile TEXT,
    Seller_details TEXT
);

-- Create Purchases table
CREATE TABLE IF NOT EXISTS Purchases (
    P_Id INTEGER PRIMARY KEY AUTOINCREMENT,
    Sup_Id INTEGER NOT NULL,
    cost REAL NOT NULL,
    date TEXT NOT NULL,
    other_details TEXT,
    FOREIGN KEY (Sup_Id) REFERENCES Suppliers(Sup_Id)
);

-- Create Expenses table
CREATE TABLE IF NOT EXISTS Expenses (
    E_Id INTEGER PRIMARY KEY AUTOINCREMENT,
    Category TEXT NOT NULL,
    amount REAL NOT NULL,
    Description TEXT,
    Date TEXT NOT NULL
);

-- Create Returns table
CREATE TABLE IF NOT EXISTS Returns (
    R_Id INTEGER PRIMARY KEY AUTOINCREMENT,
    Shoe_Id INTEGER NOT NULL,
    quantity INTEGER NOT NULL,
    description TEXT,
    date TEXT NOT NULL,
    FOREIGN KEY (Shoe_Id) REFERENCES Shoes(Shoe_Id)
);

-- Insert default admin user
INSERT OR IGNORE INTO Users (username, password, role) VALUES ('admin', 'admin123', 'admin'); 