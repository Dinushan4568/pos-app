<?php
session_start();
require_once 'includes/permissions.php';

if (!isset($_SESSION['user']) && basename($_SERVER['PHP_SELF']) !== 'login.php') {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Shoe Shop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1e40af',
                        secondary: '#64748b'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50">
    <?php if (isset($_SESSION['user'])): ?>
    <nav class="bg-primary text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-4">
                    <h1 class="text-xl font-bold">POS Shoe Shop</h1>
                    <span class="text-sm bg-white bg-opacity-20 px-2 py-1 rounded">
                        <?php echo getRoleDisplayName(getUserRole()); ?>
                    </span>
                </div>
                <div class="flex items-center space-x-6">
                    <a href="index.php" class="hover:text-gray-300">Dashboard</a>
                    <a href="shoes.php" class="hover:text-gray-300">Shoes</a>
                    <a href="sales.php" class="hover:text-gray-300">Sales</a>
                    <?php if (canAddPurchase()): ?>
                        <a href="purchases.php" class="hover:text-gray-300">Purchases</a>
                    <?php endif; ?>
                    <?php if (canAddExpense()): ?>
                        <a href="expenses.php" class="hover:text-gray-300">Expenses</a>
                    <?php endif; ?>
                    <?php if (canAddReturn()): ?>
                        <a href="returns.php" class="hover:text-gray-300">Returns</a>
                    <?php endif; ?>
                    <?php if (canManageUsers()): ?>
                        <a href="suppliers.php" class="hover:text-gray-300">Suppliers</a>
                    <?php endif; ?>
                    <?php if (canViewHistory()): ?>
                        <a href="history.php" class="hover:text-gray-300">History</a>
                    <?php endif; ?>
                    <?php if (canManageUsers()): ?>
                        <a href="users.php" class="hover:text-gray-300">Users</a>
                    <?php endif; ?>
                    <a href="database.php" class="hover:text-gray-300">Database</a>
                    <a href="logout.php" class="hover:text-gray-300">Logout</a>
                </div>
            </div>
        </div>
    </nav>
    <?php endif; ?>
    
    <main class="max-w-7xl mx-auto px-4 py-6"> 