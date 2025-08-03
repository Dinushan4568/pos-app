<?php
require_once 'includes/header.php';
require_once 'config/database.php';
require_once 'includes/permissions.php';

$db = new Database();
$action = $_GET['action'] ?? 'list';
$message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add') {
        // Check if user can edit
        if (!canEdit()) {
            $message = "Error: You don't have permission to add shoes!";
        } else {
            $custom_shoe_id = $_POST['custom_shoe_id'];
            $size = $_POST['size'];
            $price = $_POST['price'];
            $stock = $_POST['stock'];
            $cost_price = $_POST['cost_price'];
            $selling_price = $_POST['selling_price'];
            
            // Check if custom shoe ID already exists
            $existing = $db->fetchOne("SELECT Shoe_Id FROM Shoes WHERE custom_shoe_id = ?", [$custom_shoe_id]);
            if ($existing) {
                $message = "Error: Shoe ID '$custom_shoe_id' already exists!";
            } else {
                $db->query("INSERT INTO Shoes (custom_shoe_id, size, price, stock, cost_price, selling_price) VALUES (?, ?, ?, ?, ?, ?)", 
                           [$custom_shoe_id, $size, $price, $stock, $cost_price, $selling_price]);
                $message = "Shoe added successfully!";
                $action = 'list';
            }
        }
    } elseif ($action === 'edit') {
        // Check if user can edit
        if (!canEdit()) {
            $message = "Error: You don't have permission to edit shoes!";
        } else {
            $shoe_id = $_POST['shoe_id'];
            $custom_shoe_id = $_POST['custom_shoe_id'];
            $size = $_POST['size'];
            $price = $_POST['price'];
            $stock = $_POST['stock'];
            $cost_price = $_POST['cost_price'];
            $selling_price = $_POST['selling_price'];
            
            // Check if custom shoe ID already exists for other shoes
            $existing = $db->fetchOne("SELECT Shoe_Id FROM Shoes WHERE custom_shoe_id = ? AND Shoe_Id != ?", [$custom_shoe_id, $shoe_id]);
            if ($existing) {
                $message = "Error: Shoe ID '$custom_shoe_id' already exists!";
            } else {
                $db->query("UPDATE Shoes SET custom_shoe_id = ?, size = ?, price = ?, stock = ?, cost_price = ?, selling_price = ? WHERE Shoe_Id = ?", 
                           [$custom_shoe_id, $size, $price, $stock, $cost_price, $selling_price, $shoe_id]);
                $message = "Shoe updated successfully!";
                $action = 'list';
            }
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    if (!canDelete()) {
        $message = "Error: You don't have permission to delete shoes!";
    } else {
        $shoe_id = $_GET['delete'];
        $db->query("DELETE FROM Shoes WHERE Shoe_Id = ?", [$shoe_id]);
        $message = "Shoe deleted successfully!";
    }
    $action = 'list';
}

// Get shoes for listing
$shoes = $db->fetchAll("SELECT * FROM Shoes ORDER BY custom_shoe_id ASC, size ASC");

// Get shoe for editing
$editShoe = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $editShoe = $db->fetchOne("SELECT * FROM Shoes WHERE Shoe_Id = ?", [$_GET['id']]);
}
?>

<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-900">Shoes Management</h1>
        <?php if ($action === 'list'): ?>
            <?php if (canEdit()): ?>
                <a href="?action=add" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Add New Shoe
                </a>
            <?php endif; ?>
        <?php else: ?>
            <a href="shoes.php" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                Back to List
            </a>
        <?php endif; ?>
    </div>

    <?php if ($message): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <?php if ($action === 'add' || $action === 'edit'): ?>
        <?php if (!canEdit()): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                You don't have permission to edit shoes. Only administrators can add or edit shoes.
            </div>
        <?php else: ?>
            <!-- Add/Edit Form -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">
                    <?php echo $action === 'add' ? 'Add New Shoe' : 'Edit Shoe'; ?>
                </h2>
                
                <form method="POST" class="space-y-4">
                    <?php if ($action === 'edit'): ?>
                        <input type="hidden" name="shoe_id" value="<?php echo $editShoe['Shoe_Id']; ?>">
                    <?php endif; ?>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Shoe ID *</label>
                            <input type="text" name="custom_shoe_id" required 
                                   value="<?php echo $editShoe ? htmlspecialchars($editShoe['custom_shoe_id']) : ''; ?>"
                                   placeholder="e.g., SH001, NIKE-7, etc."
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <p class="mt-1 text-sm text-gray-500">Enter a unique identifier for this shoe</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Size</label>
                            <input type="text" name="size" required 
                                   value="<?php echo $editShoe ? htmlspecialchars($editShoe['size']) : ''; ?>"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Price (LKR)</label>
                            <input type="number" step="0.01" name="price" required 
                                   value="<?php echo $editShoe ? $editShoe['price'] : ''; ?>"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Stock</label>
                            <input type="number" name="stock" required 
                                   value="<?php echo $editShoe ? $editShoe['stock'] : ''; ?>"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Cost Price (LKR)</label>
                            <input type="number" step="0.01" name="cost_price" required 
                                   value="<?php echo $editShoe ? $editShoe['cost_price'] : ''; ?>"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Selling Price (LKR)</label>
                            <input type="number" step="0.01" name="selling_price" required 
                                   value="<?php echo $editShoe ? $editShoe['selling_price'] : ''; ?>"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <a href="shoes.php" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                            Cancel
                        </a>
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                            <?php echo $action === 'add' ? 'Add Shoe' : 'Update Shoe'; ?>
                        </button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <!-- Shoes List -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold">Shoes Inventory</h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shoe ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cost Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Selling Price</th>
                            <?php if (canEdit()): ?>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($shoes)): ?>
                            <tr>
                                <td colspan="<?php echo canEdit() ? '8' : '7'; ?>" class="px-6 py-4 text-center text-gray-500">No shoes found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($shoes as $shoe): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?php echo $shoe['Shoe_Id']; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">
                                        <?php echo htmlspecialchars($shoe['custom_shoe_id']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($shoe['size']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        LKR <?php echo number_format($shoe['price'], 2); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                   <?php echo $shoe['stock'] < 5 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'; ?>">
                                            <?php echo $shoe['stock']; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        LKR <?php echo number_format($shoe['cost_price'], 2); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        LKR <?php echo number_format($shoe['selling_price'], 2); ?>
                                    </td>
                                    <?php if (canEdit()): ?>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="?action=edit&id=<?php echo $shoe['Shoe_Id']; ?>" 
                                               class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                                            <?php if (canDelete()): ?>
                                                <a href="?delete=<?php echo $shoe['Shoe_Id']; ?>" 
                                                   onclick="return confirmDelete('Are you sure you want to delete this shoe?')"
                                                   class="text-red-600 hover:text-red-900">Delete</a>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function confirmDelete(message) {
    return confirm(message);
}
</script>

<?php require_once 'includes/footer.php'; ?> 