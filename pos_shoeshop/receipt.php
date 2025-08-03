<?php
require_once 'includes/header.php';
require_once 'config/database.php';

$db = new Database();

// Get sale ID from URL
$sale_id = $_GET['sale_id'] ?? 0;

// Get sale details
$sale = $db->fetchOne("SELECT * FROM Sales WHERE Sell_Id = ?", [$sale_id]);

if (!$sale) {
    echo '<div class="text-center py-8"><p class="text-red-600">Sale not found!</p></div>';
    exit();
}

// Get sale items with shoe details
$items = $db->fetchAll("
    SELECT si.*, s.custom_shoe_id, s.size, s.selling_price 
    FROM Sale_Items si 
    JOIN Shoes s ON si.Shoe_Id = s.Shoe_Id 
    WHERE si.Sell_Id = ?
    ORDER BY si.Sale_Item_Id ASC
", [$sale_id]);

$total = array_sum(array_column($items, 'total'));
$items_count = array_sum(array_column($items, 'quantity'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - Sale #<?php echo $sale_id; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            /* Hide everything except receipt */
            .no-print, header, nav, .bg-gray-100, body > div:not(.receipt-container) { 
                display: none !important; 
            }
            
            
        }
        
        .receipt-container {
            width: 80mm;
            max-width: 80mm;
            margin: 0 auto;
            background: white;
            font-family: 'Courier New', monospace;
        }
        
        .receipt-only {
            display: none;
        }
        
        @media screen {
            .receipt-only {
                display: block;
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Print and Back buttons - Hidden when printing -->
    <div class="no-print bg-white shadow-sm border-b p-4">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <h1 class="text-xl font-semibold text-gray-900">Receipt - Sale #<?php echo $sale_id; ?></h1>
            <div class="flex space-x-3">
                <button onclick="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    🖨️ Print Receipt
                </button>
                <a href="add_sale.php" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                    ➕ New Sale
                </a>
                <a href="sales.php" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                    📋 All Sales
                </a>
            </div>
        </div>
    </div>

    <!-- Receipt Content - This is what will be printed -->
    <div class="receipt-container">
        <!-- Header with Logo -->
        <div class="text-center py-4 border-b-2 border-gray-300">
            <h2 class="text-lg font-bold">BLUE TAG SHOE SHOP</h2>
            <p class="text-sm text-gray-600">No:E/4077,Near AG office,Sooriawewa</p>
            <p class="text-sm text-gray-600">Phone: +94 743022959</p>
            <p class="text-sm text-gray-600">Email: dinushanlakshi123@gmail.com</p>
        </div>

        <!-- Sale Information -->
        <div class="py-3 border-b border-gray-300">
            <div class="flex justify-between items-center">
                <span class="text-sm">Receipt #:</span>
                <span class="text-sm font-bold"><?php echo str_pad($sale_id, 6, '0', STR_PAD_LEFT); ?></span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm">Date:</span>
                <span class="text-sm"><?php echo date('d/m/Y', strtotime($sale['date'])); ?></span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm">Time:</span>
                <span class="text-sm"><?php echo date('H:i:s'); ?></span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm">Cashier:</span>
                <span class="text-sm"><?php echo htmlspecialchars($_SESSION['user'] ?? 'Admin'); ?></span>
            </div>
        </div>

        <!-- Items -->
        <div class="py-3 border-b border-gray-300">
            <div class="text-center mb-2">
                <span class="text-sm font-bold">ITEMS</span>
            </div>
            
            <?php foreach ($items as $item): ?>
                <div class="mb-2">
                    <div class="flex justify-between">
                        <span class="text-sm font-bold"><?php echo htmlspecialchars($item['custom_shoe_id']); ?></span>
                        <span class="text-sm"><?php echo $item['quantity']; ?> x LKR <?php echo number_format($item['selling_price'], 2); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm">Size: <?php echo htmlspecialchars($item['size']); ?></span>
                        <span class="text-sm font-bold">LKR <?php echo number_format($item['total'], 2); ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Totals -->
        <div class="py-3 border-b border-gray-300">
            <div class="flex justify-between">
                <span class="text-sm">Items:</span>
                <span class="text-sm"><?php echo $items_count; ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm">Subtotal:</span>
                <span class="text-sm">LKR <?php echo number_format($total, 2); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm">Tax (0%):</span>
                <span class="text-sm">LKR 0.00</span>
            </div>
            <div class="flex justify-between font-bold text-lg">
                <span>TOTAL:</span>
                <span>LKR <?php echo number_format($total, 2); ?></span>
            </div>
        </div>

        <!-- Payment Information -->
        <div class="py-3 border-b border-gray-300">
            <div class="flex justify-between">
                <span class="text-sm">Payment Method:</span>
                <span class="text-sm">Cash</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm">Amount Paid:</span>
                <span class="text-sm">LKR <?php echo number_format($total, 2); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm">Change:</span>
                <span class="text-sm">LKR 0.00</span>
            </div>
        </div>

        <!-- Footer -->
        <div class="py-4 text-center">
            <p class="text-sm font-bold mb-2">Thank you for your purchase!</p>
            <p class="text-xs text-gray-600 mb-2">Please keep this receipt for your records</p>
            <p class="text-xs text-gray-600 mb-2">Returns accepted within 7 days with receipt</p>
            <p class="text-xs text-gray-600">Visit us again!</p>
        </div>
    </div>

    <script>
        // Auto-print after page loads (for thermal printer)
        window.addEventListener('load', function() {
            // Small delay to ensure everything is loaded
            setTimeout(function() {
                window.print();
            }, 500);
        });
        
        // Focus on print button for better UX (only on screen)
        document.addEventListener('DOMContentLoaded', function() {
            const printBtn = document.querySelector('button[onclick="window.print()"]');
            if (printBtn) printBtn.focus();
        });
    </script>
</body>
</html> 