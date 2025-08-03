# POS Shoe Shop System

A complete offline Point of Sale (POS) system for shoe shops built with PHP, SQLite, and Tailwind CSS.

## 🚀 Features

### Core Functionality
- **User Authentication** - Secure login system
- **Inventory Management** - Add, edit, delete shoes with stock tracking
- **Sales Management** - Multi-item sales with automatic stock updates
- **Purchase Tracking** - Record purchases from suppliers
- **Expense Management** - Track business expenses by category
- **Returns Management** - Handle customer returns with stock adjustments
- **Supplier Management** - Manage supplier information
- **Dashboard** - Real-time statistics and overview

### Technical Features
- **100% Offline** - Works without internet connection
- **SQLite Database** - Lightweight, file-based database
- **Responsive Design** - Works on desktop, tablet, and mobile
- **Modern UI** - Clean interface with Tailwind CSS
- **Real-time Calculations** - Dynamic totals and stock updates

## 📋 Requirements

- **XAMPP** (Apache + PHP)
- **PHP 7.4+** (with SQLite extension)
- **Web Browser** (Chrome, Firefox, Safari, Edge)

## 🛠️ Installation

### Step 1: Download and Extract
1. Download the project files
2. Extract to your XAMPP `htdocs` folder
3. Rename the folder to `pos_shoeshop`

### Step 2: Start XAMPP
1. Open XAMPP Control Panel
2. Start Apache service
3. Ensure Apache is running on port 80

### Step 3: Access the System
1. Open your web browser
2. Navigate to: `http://localhost/pos_shoeshop`
3. You'll be redirected to the login page

### Step 4: Login
- **Username:** `admin`
- **Password:** `admin123`

## 📊 Database Schema

### Tables Overview

1. **Shoes** - Product inventory
   - Shoe_Id (Primary Key)
   - size, price, stock, cost_price, selling_price

2. **Users** - System users
   - username (Primary Key), password

3. **Sales** - Sales transactions
   - Sell_Id (Primary Key), date

4. **Sale_Items** - Individual items in sales
   - Sale_Item_Id (Primary Key)
   - Sell_Id (Foreign Key), Shoe_Id (Foreign Key)
   - quantity, total

5. **Suppliers** - Supplier information
   - Sup_Id (Primary Key)
   - name, mobile, Seller_details

6. **Purchases** - Purchase transactions
   - P_Id (Primary Key)
   - Sup_Id (Foreign Key), cost, date, other_details

7. **Expenses** - Business expenses
   - E_Id (Primary Key)
   - Category, amount, Description, Date

8. **Returns** - Customer returns
   - R_Id (Primary Key)
   - Shoe_Id (Foreign Key), quantity, description, date

## 🎯 How to Use

### 1. Dashboard
- View daily sales statistics
- Check low stock alerts
- Quick access to all functions

### 2. Shoes Management
- Add new shoes with size, price, and stock
- Edit existing shoe information
- View current stock levels
- Delete shoes (if no sales exist)

### 3. Sales
- Create new sales with multiple items
- Automatic stock deduction
- Real-time total calculations
- View sales history with details

### 4. Purchases
- Record purchases from suppliers
- Track purchase costs and dates
- Link purchases to suppliers

### 5. Expenses
- Categorize expenses (Rent, Utilities, Salary, etc.)
- Track daily and total expenses
- Add descriptions for each expense

### 6. Returns
- Process customer returns
- Automatic stock adjustment
- Track return reasons

### 7. Suppliers
- Manage supplier information
- Link suppliers to purchases
- Cannot delete suppliers with existing purchases

## 🔧 Configuration

### Database Location
The SQLite database is automatically created at:
```
pos_shoeshop/database/pos_shoeshop.db
```

### File Structure
```
pos_shoeshop/
├── config/
│   └── database.php
├── database/
│   ├── schema.sql
│   └── pos_shoeshop.db (auto-created)
├── includes/
│   ├── header.php
│   └── footer.php
├── index.php (Dashboard)
├── login.php
├── logout.php
├── shoes.php
├── add_sale.php
├── sales.php
├── purchases.php
├── expenses.php
├── returns.php
├── suppliers.php
├── get_sale_details.php
└── README.md
```

## 🔒 Security Features

- **Session Management** - Secure user sessions
- **SQL Injection Protection** - Prepared statements
- **XSS Protection** - HTML escaping
- **Authentication Required** - All pages require login

## 📱 Responsive Design

The system is fully responsive and works on:
- Desktop computers
- Tablets
- Mobile phones
- All modern browsers

## 🚨 Important Notes

1. **Backup Your Database** - Regularly backup the `pos_shoeshop.db` file
2. **Stock Management** - Stock is automatically updated on sales and returns
3. **Data Integrity** - Foreign key constraints prevent orphaned records
4. **Offline Operation** - System works completely offline once installed

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Ensure PHP SQLite extension is enabled
   - Check file permissions on database folder

2. **Page Not Found**
   - Verify XAMPP Apache is running
   - Check folder name is exactly `pos_shoeshop`

3. **Login Issues**
   - Default credentials: admin/admin123
   - Clear browser cache if needed

4. **Stock Not Updating**
   - Check for JavaScript errors in browser console
   - Ensure all form fields are filled correctly

## 📞 Support

For technical support or questions:
- Check the troubleshooting section above
- Verify all requirements are met
- Ensure proper file permissions

## 🔄 Updates

To update the system:
1. Backup your database file
2. Replace PHP files with new versions
3. Restore your database file
4. Test all functionality

---

**Version:** 1.0  
**Last Updated:** December 2024  
**Compatibility:** PHP 7.4+, XAMPP, Modern Browsers 