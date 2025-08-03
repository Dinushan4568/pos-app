<?php
require_once 'includes/header.php';
require_once 'config/database.php';

$db = new Database();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = date('Y-m-d');
    
    // Insert sale
    $db->query("INSERT INTO Sales (date) VALUES (?)", [$date]);
    $sale_id = $db->lastInsertId();
    
    // Insert sale items
    $shoe_ids = $_POST['shoe_id'] ?? [];
    $quantities = $_POST['quantity'] ?? [];
    $prices = $_POST['price'] ?? [];
    
    for ($i = 0; $i < count($shoe_ids); $i++) {
        if (!empty($shoe_ids[$i]) && !empty($quantities[$i])) {
            $shoe_id = $shoe_ids[$i];
            $quantity = $quantities[$i];
            $price = $prices[$i];
            $total = $quantity * $price;
            
            // Insert sale item
            $db->query("INSERT INTO Sale_Items (Sell_Id, Shoe_Id, quantity, total) VALUES (?, ?, ?, ?)", 
                       [$sale_id, $shoe_id, $quantity, $total]);
            
            // Update stock
            $db->query("UPDATE Shoes SET stock = stock - ? WHERE Shoe_Id = ?", [$quantity, $shoe_id]);
        }
    }
    
    // Redirect to receipt page
    header("Location: receipt.php?sale_id=" . $sale_id);
    exit();
}

// Get all shoes for dropdown
$shoes = $db->fetchAll("SELECT * FROM Shoes WHERE stock > 0 ORDER BY custom_shoe_id ASC, size ASC");
?>

<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-900">New Sale</h1>
        <a href="sales.php" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
            View All Sales
        </a>
    </div>

    <?php if ($message): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" id="saleForm">
            <div class="space-y-6">
                <!-- Sale Items -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Sale Items</h3>
                    <div id="saleItems" class="space-y-4">
                        <div class="sale-item grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Shoe</label>
                                <select name="shoe_id[]" class="shoe-select mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                                    <option value="">Select Shoe</option>
                                    <?php foreach ($shoes as $shoe): ?>
                                        <option value="<?php echo $shoe['Shoe_Id']; ?>" 
                                                data-price="<?php echo $shoe['selling_price']; ?>"
                                                data-stock="<?php echo $shoe['stock']; ?>">
                                            <?php echo htmlspecialchars($shoe['custom_shoe_id']); ?> - Size <?php echo htmlspecialchars($shoe['size']); ?> - LKR <?php echo number_format($shoe['selling_price'], 2); ?> (Stock: <?php echo $shoe['stock']; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Price (LKR)</label>
                                <input type="number" step="0.01" name="price[]" class="price-input mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" readonly>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Quantity</label>
                                <input type="number" name="quantity[]" min="1" class="quantity-input mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Total (LKR)</label>
                                <input type="number" step="0.01" class="total-input mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-50" readonly>
                            </div>
                            
                            <div>
                                <button type="button" class="remove-item bg-red-600 text-white px-3 py-2 rounded hover:bg-red-700">
                                    Remove
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <button type="button" id="addItem" class="mt-4 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                        Add Another Item
                    </button>
                </div>
                
                <!-- Sale Summary -->
                <div class="border-t pt-6">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-medium text-gray-900">Sale Summary</h3>
                        <div class="text-right">
                            <p class="text-sm text-gray-600">Total Items: <span id="totalItems">0</span></p>
                            <p class="text-lg font-semibold text-gray-900">Total Amount: LKR <span id="grandTotal">0.00</span></p>
                        </div>
                    </div>
                </div>
                
                <!-- Submit Button -->
                <div class="flex justify-end">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                        Complete Sale
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const saleItemsContainer = document.getElementById('saleItems');
    const addItemBtn = document.getElementById('addItem');
    const totalItemsSpan = document.getElementById('totalItems');
    const grandTotalSpan = document.getElementById('grandTotal');
    
    // Add new item row
    addItemBtn.addEventListener('click', function() {
        const firstItem = saleItemsContainer.querySelector('.sale-item');
        const newItem = firstItem.cloneNode(true);
        
        // Clear values
        newItem.querySelector('.shoe-select').value = '';
        newItem.querySelector('.price-input').value = '';
        newItem.querySelector('.quantity-input').value = '';
        newItem.querySelector('.total-input').value = '';
        
        saleItemsContainer.appendChild(newItem);
        attachEventListeners(newItem);
    });
    
    // Remove item row
    saleItemsContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-item')) {
            if (saleItemsContainer.querySelectorAll('.sale-item').length > 1) {
                e.target.closest('.sale-item').remove();
                updateTotals();
            }
        }
    });
    
    // Attach event listeners to a sale item
    function attachEventListeners(item) {
        const shoeSelect = item.querySelector('.shoe-select');
        const priceInput = item.querySelector('.price-input');
        const quantityInput = item.querySelector('.quantity-input');
        const totalInput = item.querySelector('.total-input');
        
        shoeSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value) {
                const price = selectedOption.dataset.price;
                priceInput.value = price;
                updateItemTotal(item);
            } else {
                priceInput.value = '';
                totalInput.value = '';
            }
        });
        
        quantityInput.addEventListener('input', function() {
            updateItemTotal(item);
        });
    }
    
    // Update individual item total
    function updateItemTotal(item) {
        const price = parseFloat(item.querySelector('.price-input').value) || 0;
        const quantity = parseInt(item.querySelector('.quantity-input').value) || 0;
        const total = price * quantity;
        item.querySelector('.total-input').value = total.toFixed(2);
        updateTotals();
    }
    
    // Update grand total
    function updateTotals() {
        let totalItems = 0;
        let grandTotal = 0;
        
        document.querySelectorAll('.sale-item').forEach(item => {
            const quantity = parseInt(item.querySelector('.quantity-input').value) || 0;
            const total = parseFloat(item.querySelector('.total-input').value) || 0;
            
            totalItems += quantity;
            grandTotal += total;
        });
        
        totalItemsSpan.textContent = totalItems;
        grandTotalSpan.textContent = grandTotal.toFixed(2);
    }
    
    // Attach event listeners to initial items
    document.querySelectorAll('.sale-item').forEach(attachEventListeners);
    
    // Form validation
    document.getElementById('saleForm').addEventListener('submit', function(e) {
        const items = document.querySelectorAll('.sale-item');
        let hasValidItem = false;
        
        items.forEach(item => {
            const shoeId = item.querySelector('.shoe-select').value;
            const quantity = item.querySelector('.quantity-input').value;
            
            if (shoeId && quantity) {
                hasValidItem = true;
            }
        });
        
        if (!hasValidItem) {
            e.preventDefault();
            alert('Please add at least one item to the sale.');
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?> 