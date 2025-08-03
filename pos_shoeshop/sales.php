<?php
require_once 'includes/header.php';
require_once 'config/database.php';

$db = new Database();

// Get sales with totals
$sales = $db->fetchAll("
    SELECT s.Sell_Id, s.date, 
           SUM(si.total) as total_amount, 
           COUNT(si.Sale_Item_Id) as items_count
    FROM Sales s 
    LEFT JOIN Sale_Items si ON s.Sell_Id = si.Sell_Id 
    GROUP BY s.Sell_Id 
    ORDER BY s.date DESC, s.Sell_Id DESC
");

// Get today's sales
$todaySales = $db->fetchAll("
    SELECT s.Sell_Id, s.date, 
           SUM(si.total) as total_amount, 
           COUNT(si.Sale_Item_Id) as items_count
    FROM Sales s 
    LEFT JOIN Sale_Items si ON s.Sell_Id = si.Sell_Id 
    WHERE s.date = ?
    GROUP BY s.Sell_Id 
    ORDER BY s.Sell_Id DESC
", [date('Y-m-d')]);

$todayTotal = array_sum(array_column($todaySales, 'total_amount'));
?>

<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-900">Sales History</h1>
        <a href="add_sale.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            New Sale
        </a>
    </div>

    <!-- Today's Summary -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-4">Today's Sales Summary</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-blue-50 p-4 rounded-lg">
                <p class="text-sm text-blue-600">Total Sales</p>
                <p class="text-2xl font-bold text-blue-900"><?php echo count($todaySales); ?></p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <p class="text-sm text-green-600">Total Revenue</p>
                <p class="text-2xl font-bold text-green-900">LKR <?php echo number_format($todayTotal, 2); ?></p>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg">
                <p class="text-sm text-purple-600">Date</p>
                <p class="text-2xl font-bold text-purple-900"><?php echo date('M d, Y'); ?></p>
            </div>
        </div>
    </div>

    <!-- Sales List -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold">All Sales</h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sale ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($sales)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">No sales found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($sales as $sale): ?>
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
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="viewSaleDetails(<?php echo $sale['Sell_Id']; ?>)" 
                                            class="text-blue-600 hover:text-blue-900 mr-3">
                                        View Details
                                    </button>
                                    <a href="receipt.php?sale_id=<?php echo $sale['Sell_Id']; ?>" 
                                       class="text-green-600 hover:text-green-900">
                                        Print Receipt
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Sale Details Modal -->
<div id="saleModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Sale Details</h3>
                <button onclick="closeSaleModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="saleDetails" class="space-y-4">
                <!-- Sale details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function viewSaleDetails(saleId) {
    // Show loading
    document.getElementById('saleDetails').innerHTML = '<p class="text-center">Loading...</p>';
    document.getElementById('saleModal').classList.remove('hidden');
    
    // Fetch sale details via AJAX
    fetch(`get_sale_details.php?sale_id=${saleId}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('saleDetails').innerHTML = html;
        })
        .catch(error => {
            document.getElementById('saleDetails').innerHTML = '<p class="text-center text-red-600">Error loading sale details</p>';
        });
}

function closeSaleModal() {
    document.getElementById('saleModal').classList.add('hidden');
}
</script>

<?php require_once 'includes/footer.php'; ?> 