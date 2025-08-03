<?php
require_once 'config/database.php';

$sale_id = $_GET['sale_id'] ?? 0;
$db = new Database();

// Get sale details
$sale = $db->fetchOne("SELECT * FROM Sales WHERE Sell_Id = ?", [$sale_id]);

if (!$sale) {
    echo '<p class="text-red-600">Sale not found</p>';
    exit;
}

// Get sale items with custom shoe ID
$items = $db->fetchAll("
    SELECT si.*, s.custom_shoe_id, s.size, s.selling_price 
    FROM Sale_Items si 
    JOIN Shoes s ON si.Shoe_Id = s.Shoe_Id 
    WHERE si.Sell_Id = ?
", [$sale_id]);

$total = array_sum(array_column($items, 'total'));
?>

<div class="space-y-4">
    <div class="border-b pb-4">
        <h4 class="text-lg font-semibold">Sale #<?php echo $sale['Sell_Id']; ?></h4>
        <p class="text-gray-600">Date: <?php echo date('M d, Y', strtotime($sale['date'])); ?></p>
    </div>
    
    <div class="space-y-3">
        <h5 class="font-medium">Items:</h5>
        <?php if (empty($items)): ?>
            <p class="text-gray-500">No items found</p>
        <?php else: ?>
            <div class="space-y-2">
                <?php foreach ($items as $item): ?>
                    <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                        <div>
                            <span class="font-medium text-blue-600"><?php echo htmlspecialchars($item['custom_shoe_id']); ?></span>
                            <span class="text-gray-600"> - Size <?php echo htmlspecialchars($item['size']); ?></span>
                            <span class="text-gray-600">x <?php echo $item['quantity']; ?></span>
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-gray-600">LKR <?php echo number_format($item['selling_price'], 2); ?> each</div>
                            <div class="font-medium">LKR <?php echo number_format($item['total'], 2); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="border-t pt-4">
        <div class="flex justify-between items-center">
            <span class="text-lg font-semibold">Total:</span>
            <span class="text-lg font-semibold">LKR <?php echo number_format($total, 2); ?></span>
        </div>
        <div class="mt-4 text-center">
            <a href="receipt.php?sale_id=<?php echo $sale['Sell_Id']; ?>" 
               class="inline-block bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                🖨️ Print Receipt
            </a>
        </div>
    </div>
</div> 