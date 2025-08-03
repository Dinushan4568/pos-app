<?php
require_once 'includes/header.php';
require_once 'config/database.php';

$db = new Database();

// Get selected date or default to today
$selectedDate = $_GET['date'] ?? date('Y-m-d');
$view = $_GET['view'] ?? 'details'; // 'details' or 'dates'

// Get all unique dates with activities, ordered by newest first
$dates = $db->fetchAll("
    SELECT DISTINCT date FROM (
        SELECT date FROM Sales
        UNION
        SELECT date FROM Purchases  
        UNION
        SELECT Date as date FROM Expenses
        UNION
        SELECT date FROM Returns
    ) ORDER BY date DESC
");

// Get date summary with activity counts
$dateSummary = $db->fetchAll("
    SELECT 
        date,
        (SELECT COUNT(*) FROM Sales WHERE date = d.date) as sales_count,
        (SELECT COUNT(*) FROM Purchases WHERE date = d.date) as purchases_count,
        (SELECT COUNT(*) FROM Expenses WHERE Date = d.date) as expenses_count,
        (SELECT COUNT(*) FROM Returns WHERE date = d.date) as returns_count,
        (SELECT COALESCE(SUM(si.total), 0) FROM Sales s JOIN Sale_Items si ON s.Sell_Id = si.Sell_Id WHERE s.date = d.date) as sales_total,
        (SELECT COALESCE(SUM(cost), 0) FROM Purchases WHERE date = d.date) as purchases_total,
        (SELECT COALESCE(SUM(amount), 0) FROM Expenses WHERE Date = d.date) as expenses_total,
        (SELECT COALESCE(SUM(s.selling_price * r.quantity), 0) FROM Returns r JOIN Shoes s ON r.Shoe_Id = s.Shoe_Id WHERE r.date = d.date) as returns_total
    FROM (
        SELECT DISTINCT date FROM (
            SELECT date FROM Sales
            UNION
            SELECT date FROM Purchases  
            UNION
            SELECT Date as date FROM Expenses
            UNION
            SELECT date FROM Returns
        )
    ) d
    ORDER BY date DESC
");

// Get activities for selected date (only if viewing details)
$sales = [];
$purchases = [];
$expenses = [];
$returns = [];
$allActivities = [];
$totalSales = 0;
$totalPurchases = 0;
$totalExpenses = 0;
$totalReturns = 0;
$netAmount = 0;

if ($view === 'details') {
    $sales = $db->fetchAll("
        SELECT 'sale' as type, s.Sell_Id as id, s.date, 
               SUM(si.total) as total_amount, COUNT(si.Sale_Item_Id) as items_count,
               'Sale' as activity_type
        FROM Sales s 
        JOIN Sale_Items si ON s.Sell_Id = si.Sell_Id 
        WHERE s.date = ?
        GROUP BY s.Sell_Id
        ORDER BY s.Sell_Id DESC
    ", [$selectedDate]);

    $purchases = $db->fetchAll("
        SELECT 'purchase' as type, P_Id as id, date, cost as total_amount, 
               1 as items_count, 'Purchase' as activity_type
        FROM Purchases 
        WHERE date = ?
        ORDER BY P_Id DESC
    ", [$selectedDate]);

    $expenses = $db->fetchAll("
        SELECT 'expense' as type, E_Id as id, Date as date, amount as total_amount,
               1 as items_count, Category as activity_type
        FROM Expenses 
        WHERE Date = ?
        ORDER BY E_Id DESC
    ", [$selectedDate]);

    $returns = $db->fetchAll("
        SELECT 'return' as type, R_Id as id, date, 
               (SELECT selling_price * r.quantity FROM Shoes WHERE Shoe_Id = r.Shoe_Id) as total_amount,
               1 as items_count, 'Return' as activity_type
        FROM Returns r
        WHERE date = ?
        ORDER BY R_Id DESC
    ", [$selectedDate]);

    // Combine all activities and sort by time (assuming ID represents order)
    $allActivities = array_merge($sales, $purchases, $expenses, $returns);
    usort($allActivities, function($a, $b) {
        return $b['id'] - $a['id']; // Sort by ID descending (newest first)
    });

    // Calculate totals for the selected date
    $totalSales = array_sum(array_column($sales, 'total_amount'));
    $totalPurchases = array_sum(array_column($purchases, 'total_amount'));
    $totalExpenses = array_sum(array_column($expenses, 'total_amount'));
    $totalReturns = array_sum(array_column($returns, 'total_amount'));
    $netAmount = $totalSales - $totalPurchases - $totalExpenses + $totalReturns;
}
?>

<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-900">Activity History</h1>
        <div class="flex items-center space-x-4">
            <div class="flex space-x-2">
                <a href="?view=dates" class="px-3 py-2 text-sm font-medium rounded-md <?php echo $view === 'dates' ? 'bg-blue-100 text-blue-700' : 'text-gray-500 hover:text-gray-700'; ?>">
                    Date List
                </a>
                <a href="?view=details<?php echo $selectedDate ? '&date=' . $selectedDate : ''; ?>" class="px-3 py-2 text-sm font-medium rounded-md <?php echo $view === 'details' ? 'bg-blue-100 text-blue-700' : 'text-gray-500 hover:text-gray-700'; ?>">
                    Details
                </a>
            </div>
            <?php if ($view === 'details'): ?>
                <label class="text-sm font-medium text-gray-700">Select Date:</label>
                <select id="dateSelect" class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <?php foreach ($dates as $date): ?>
                        <option value="<?php echo $date['date']; ?>" <?php echo $date['date'] === $selectedDate ? 'selected' : ''; ?>>
                            <?php echo date('M d, Y', strtotime($date['date'])); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($view === 'dates'): ?>
        <!-- Date List View -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold">All Activity Dates</h2>
            </div>
            
            <?php if (empty($dateSummary)): ?>
                <div class="p-6 text-center">
                    <p class="text-gray-500">No activity dates found.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sales</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Purchases</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expenses</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Returns</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Net Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($dateSummary as $date): ?>
                                <?php 
                                $netAmount = $date['sales_total'] - $date['purchases_total'] - $date['expenses_total'] + $date['returns_total'];
                                $totalActivities = $date['sales_count'] + $date['purchases_count'] + $date['expenses_count'] + $date['returns_count'];
                                ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?php echo date('M d, Y', strtotime($date['date'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo $date['sales_count']; ?> (LKR <?php echo number_format($date['sales_total'], 2); ?>)
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo $date['purchases_count']; ?> (LKR <?php echo number_format($date['purchases_total'], 2); ?>)
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo $date['expenses_count']; ?> (LKR <?php echo number_format($date['expenses_total'], 2); ?>)
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo $date['returns_count']; ?> (LKR <?php echo number_format($date['returns_total'], 2); ?>)
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium <?php echo $netAmount >= 0 ? 'text-green-600' : 'text-red-600'; ?>">
                                        LKR <?php echo number_format($netAmount, 2); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="?view=details&date=<?php echo $date['date']; ?>" 
                                           class="text-blue-600 hover:text-blue-900">View Details</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <!-- Details View -->
        <!-- Date Summary -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Summary for <?php echo date('M d, Y', strtotime($selectedDate)); ?></h2>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div class="bg-green-50 p-4 rounded-lg">
                    <p class="text-sm text-green-600">Total Sales</p>
                    <p class="text-lg font-bold text-green-900">LKR <?php echo number_format($totalSales, 2); ?></p>
                    <p class="text-xs text-green-600"><?php echo count($sales); ?> transactions</p>
                </div>
                <div class="bg-yellow-50 p-4 rounded-lg">
                    <p class="text-sm text-yellow-600">Total Purchases</p>
                    <p class="text-lg font-bold text-yellow-900">LKR <?php echo number_format($totalPurchases, 2); ?></p>
                    <p class="text-xs text-yellow-600"><?php echo count($purchases); ?> transactions</p>
                </div>
                <div class="bg-red-50 p-4 rounded-lg">
                    <p class="text-sm text-red-600">Total Expenses</p>
                    <p class="text-lg font-bold text-red-900">LKR <?php echo number_format($totalExpenses, 2); ?></p>
                    <p class="text-xs text-red-600"><?php echo count($expenses); ?> transactions</p>
                </div>
                <div class="bg-blue-50 p-4 rounded-lg">
                    <p class="text-sm text-blue-600">Total Returns</p>
                    <p class="text-lg font-bold text-blue-900">LKR <?php echo number_format($totalReturns, 2); ?></p>
                    <p class="text-xs text-blue-600"><?php echo count($returns); ?> transactions</p>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg">
                    <p class="text-sm text-purple-600">Net Amount</p>
                    <p class="text-lg font-bold <?php echo $netAmount >= 0 ? 'text-purple-900' : 'text-red-900'; ?>">
                        LKR <?php echo number_format($netAmount, 2); ?>
                    </p>
                    <p class="text-xs text-purple-600">Profit/Loss</p>
                </div>
            </div>
        </div>

        <!-- Activity List -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">All Activities</h3>
            </div>
            
            <?php if (empty($allActivities)): ?>
                <div class="p-6 text-center">
                    <p class="text-gray-500">No activities found for this date.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Activity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($allActivities as $activity): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                   <?php 
                                                   switch($activity['type']) {
                                                       case 'sale': echo 'bg-green-100 text-green-800'; break;
                                                       case 'purchase': echo 'bg-yellow-100 text-yellow-800'; break;
                                                       case 'expense': echo 'bg-red-100 text-red-800'; break;
                                                       case 'return': echo 'bg-blue-100 text-blue-800'; break;
                                                   }
                                                   ?>">
                                            <?php echo ucfirst($activity['type']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        #<?php echo $activity['id']; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($activity['activity_type']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo $activity['items_count']; ?> items
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium 
                                               <?php echo $activity['type'] === 'sale' ? 'text-green-600' : 
                                                       ($activity['type'] === 'purchase' || $activity['type'] === 'expense' ? 'text-red-600' : 'text-blue-600'); ?>">
                                        LKR <?php echo number_format($activity['total_amount'], 2); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <?php if ($activity['type'] === 'sale'): ?>
                                            <button onclick="viewSaleDetails(<?php echo $activity['id']; ?>)" 
                                                    class="text-blue-600 hover:text-blue-900">View Details</button>
                                        <?php elseif ($activity['type'] === 'purchase'): ?>
                                            <a href="purchases.php?action=view&id=<?php echo $activity['id']; ?>" 
                                               class="text-blue-600 hover:text-blue-900">View Details</a>
                                        <?php elseif ($activity['type'] === 'expense'): ?>
                                            <a href="expenses.php?action=view&id=<?php echo $activity['id']; ?>" 
                                               class="text-blue-600 hover:text-blue-900">View Details</a>
                                        <?php elseif ($activity['type'] === 'return'): ?>
                                            <a href="returns.php?action=view&id=<?php echo $activity['id']; ?>" 
                                               class="text-blue-600 hover:text-blue-900">View Details</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
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
document.getElementById('dateSelect')?.addEventListener('change', function() {
    window.location.href = 'history.php?view=details&date=' + this.value;
});

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