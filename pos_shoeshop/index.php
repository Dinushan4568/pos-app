<?php
require_once 'includes/header.php';
require_once 'config/database.php';
require_once 'includes/permissions.php';

$db = new Database();

// Get statistics
$totalShoes = $db->fetchOne("SELECT COUNT(*) as count FROM Shoes")['count'];
$totalStock = $db->fetchOne("SELECT SUM(stock) as total FROM Shoes")['total'] ?? 0;
$todaySales = $db->fetchOne("SELECT COUNT(*) as count FROM Sales WHERE date = ?", [date('Y-m-d')])['count'];
$todayRevenue = $db->fetchOne("SELECT SUM(si.total) as total FROM Sales s JOIN Sale_Items si ON s.Sell_Id = si.Sell_Id WHERE s.date = ?", [date('Y-m-d')])['total'] ?? 0;

// Get low stock items
$lowStockItems = $db->fetchAll("SELECT * FROM Shoes WHERE stock < 5 ORDER BY stock ASC LIMIT 5");

// Get today's sales details
$todaySalesDetails = $db->fetchAll("
    SELECT s.Sell_Id, s.date, SUM(si.total) as total_amount, COUNT(si.Sale_Item_Id) as items_count
    FROM Sales s 
    JOIN Sale_Items si ON s.Sell_Id = si.Sell_Id 
    WHERE s.date = ?
    GROUP BY s.Sell_Id 
    ORDER BY s.Sell_Id DESC
    LIMIT 5
", [date('Y-m-d')]);

// Get recent sales
$recentSales = $db->fetchAll("
    SELECT s.Sell_Id, s.date, SUM(si.total) as total_amount, COUNT(si.Sale_Item_Id) as items_count
    FROM Sales s 
    JOIN Sale_Items si ON s.Sell_Id = si.Sell_Id 
    GROUP BY s.Sell_Id 
    ORDER BY s.date DESC 
    LIMIT 5
");
?>

<div class="space-y-6">
    <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
    
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Shoes</p>
                    <p class="text-2xl font-semibold text-gray-900"><?php echo $totalShoes; ?></p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-14 0h14"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Stock</p>
                    <p class="text-2xl font-semibold text-gray-900"><?php echo $totalStock; ?></p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Today's Sales</p>
                    <p class="text-2xl font-semibold text-gray-900"><?php echo $todaySales; ?></p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Today's Revenue</p>
                    <p class="text-2xl font-semibold text-gray-900">LKR <?php echo number_format($todayRevenue, 2); ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions Grid -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-6">Quick Actions</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="add_sale.php" class="flex flex-col items-center justify-center p-6 bg-blue-50 rounded-lg border-2 border-blue-200 hover:bg-blue-100 hover:border-blue-300 transition-all duration-200 group">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600 group-hover:bg-blue-200 mb-3">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                </div>
                <span class="text-sm font-medium text-blue-900">New Sale</span>
            </a>
            
            <?php if (canEdit()): ?>
                <a href="shoes.php?action=add" class="flex flex-col items-center justify-center p-6 bg-green-50 rounded-lg border-2 border-green-200 hover:bg-green-100 hover:border-green-300 transition-all duration-200 group">
                    <div class="p-3 rounded-full bg-green-100 text-green-600 group-hover:bg-green-200 mb-3">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-green-900">Add Shoe</span>
                </a>
            <?php endif; ?>
            
            <?php if (canAddPurchase()): ?>
                <a href="purchases.php?action=add" class="flex flex-col items-center justify-center p-6 bg-yellow-50 rounded-lg border-2 border-yellow-200 hover:bg-yellow-100 hover:border-yellow-300 transition-all duration-200 group">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 group-hover:bg-yellow-200 mb-3">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-yellow-900">Purchase</span>
                </a>
            <?php endif; ?>
            
            <?php if (canAddExpense()): ?>
                <a href="expenses.php?action=add" class="flex flex-col items-center justify-center p-6 bg-red-50 rounded-lg border-2 border-red-200 hover:bg-red-100 hover:border-red-300 transition-all duration-200 group">
                    <div class="p-3 rounded-full bg-red-100 text-red-600 group-hover:bg-red-200 mb-3">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-red-900">Expense</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Today's Sales and Low Stock -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Today's Sales</h3>
            <?php if (empty($todaySalesDetails)): ?>
                <div class="text-center py-4">
                    <p class="text-gray-500 mb-2">No sales today</p>
                    <a href="add_sale.php" class="text-blue-600 hover:text-blue-800 font-medium">Make your first sale</a>
                </div>
            <?php else: ?>
                <div class="space-y-2">
                    <?php foreach ($todaySalesDetails as $sale): ?>
                        <div class="flex justify-between items-center p-2 bg-green-50 rounded">
                            <span class="text-sm">Sale #<?php echo $sale['Sell_Id']; ?> - <?php echo $sale['items_count']; ?> items</span>
                            <span class="text-sm font-medium text-green-600">LKR <?php echo number_format($sale['total_amount'], 2); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-4 pt-4 border-t">
                    <a href="sales.php" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View all sales →</a>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Low Stock Alert</h3>
            <?php if ($totalShoes == 0): ?>
                <div class="text-center py-4">
                    <p class="text-gray-500 mb-2">No shoes in inventory</p>
                    <a href="shoes.php?action=add" class="text-blue-600 hover:text-blue-800 font-medium">Add your first shoe</a>
                </div>
            <?php elseif (empty($lowStockItems)): ?>
                <p class="text-gray-500">All items have sufficient stock.</p>
            <?php else: ?>
                <div class="space-y-2">
                    <?php foreach ($lowStockItems as $item): ?>
                        <div class="flex justify-between items-center p-2 bg-red-50 rounded">
                            <span class="text-sm"><?php echo htmlspecialchars($item['custom_shoe_id']); ?> - Size <?php echo htmlspecialchars($item['size']); ?></span>
                            <span class="text-sm font-medium text-red-600"><?php echo $item['stock']; ?> left</span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-4 pt-4 border-t">
                    <a href="shoes.php" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View all shoes →</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Recent Sales -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Recent Sales</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sale ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($recentSales)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">No recent sales</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentSales as $sale): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    #<?php echo $sale['Sell_Id']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('M d, Y', strtotime($sale['date'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo $sale['items_count']; ?> items
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    LKR <?php echo number_format($sale['total_amount'], 2); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 