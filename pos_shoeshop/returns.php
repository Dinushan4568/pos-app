<?php
require_once 'includes/header.php';
require_once 'config/database.php';

$db = new Database();
$action = $_GET['action'] ?? 'list';
$message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add') {
        $shoe_id = $_POST['shoe_id'];
        $quantity = $_POST['quantity'];
        $description = $_POST['description'];
        $date = $_POST['date'];
        
        $db->query("INSERT INTO Returns (Shoe_Id, quantity, description, date) VALUES (?, ?, ?, ?)", 
                   [$shoe_id, $quantity, $description, $date]);
        
        // Update stock (add back to inventory)
        $db->query("UPDATE Shoes SET stock = stock + ? WHERE Shoe_Id = ?", [$quantity, $shoe_id]);
        
        $message = "Return recorded successfully!";
        $action = 'list';
    } elseif ($action === 'edit') {
        $r_id = $_POST['r_id'];
        $shoe_id = $_POST['shoe_id'];
        $quantity = $_POST['quantity'];
        $description = $_POST['description'];
        $date = $_POST['date'];
        
        // Get old quantity to adjust stock
        $oldReturn = $db->fetchOne("SELECT quantity, Shoe_Id FROM Returns WHERE R_Id = ?", [$r_id]);
        if ($oldReturn) {
            // Remove old quantity from stock
            $db->query("UPDATE Shoes SET stock = stock - ? WHERE Shoe_Id = ?", [$oldReturn['quantity'], $oldReturn['Shoe_Id']]);
            // Add new quantity to stock
            $db->query("UPDATE Shoes SET stock = stock + ? WHERE Shoe_Id = ?", [$quantity, $shoe_id]);
        }
        
        $db->query("UPDATE Returns SET Shoe_Id = ?, quantity = ?, description = ?, date = ? WHERE R_Id = ?", 
                   [$shoe_id, $quantity, $description, $date, $r_id]);
        $message = "Return updated successfully!";
        $action = 'list';
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $r_id = $_GET['delete'];
    
    // Get return details to adjust stock
    $return = $db->fetchOne("SELECT quantity, Shoe_Id FROM Returns WHERE R_Id = ?", [$r_id]);
    if ($return) {
        // Remove quantity from stock
        $db->query("UPDATE Shoes SET stock = stock - ? WHERE Shoe_Id = ?", [$return['quantity'], $return['Shoe_Id']]);
    }
    
    $db->query("DELETE FROM Returns WHERE R_Id = ?", [$r_id]);
    $message = "Return deleted successfully!";
    $action = 'list';
}

// Get returns for listing
$returns = $db->fetchAll("
    SELECT r.*, s.size 
    FROM Returns r 
    JOIN Shoes s ON r.Shoe_Id = s.Shoe_Id 
    ORDER BY r.date DESC, r.R_Id DESC
");

// Get shoes for dropdown
$shoes = $db->fetchAll("SELECT * FROM Shoes ORDER BY size ASC");

// Get return for editing
$editReturn = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $editReturn = $db->fetchOne("SELECT * FROM Returns WHERE R_Id = ?", [$_GET['id']]);
}

// Calculate totals
$totalReturns = array_sum(array_column($returns, 'quantity'));
$todayReturns = array_sum(array_column(
    array_filter($returns, function($return) {
        return $return['date'] === date('Y-m-d');
    }), 'quantity'
));
?>

<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-900">Returns Management</h1>
        <?php if ($action === 'list'): ?>
            <a href="?action=add" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Add New Return
            </a>
        <?php else: ?>
            <a href="returns.php" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                Back to List
            </a>
        <?php endif; ?>
    </div>

    <?php if ($message): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Returns</p>
                    <p class="text-2xl font-semibold text-gray-900"><?php echo $totalReturns; ?> items</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-orange-100 text-orange-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Today's Returns</p>
                    <p class="text-2xl font-semibold text-gray-900"><?php echo $todayReturns; ?> items</p>
                </div>
            </div>
        </div>
    </div>

    <?php if ($action === 'add' || $action === 'edit'): ?>
        <!-- Add/Edit Form -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">
                <?php echo $action === 'add' ? 'Add New Return' : 'Edit Return'; ?>
            </h2>
            
            <form method="POST" class="space-y-4">
                <?php if ($action === 'edit'): ?>
                    <input type="hidden" name="r_id" value="<?php echo $editReturn['R_Id']; ?>">
                <?php endif; ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Shoe</label>
                        <select name="shoe_id" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Shoe</option>
                            <?php foreach ($shoes as $shoe): ?>
                                <option value="<?php echo $shoe['Shoe_Id']; ?>" 
                                        <?php echo ($editReturn && $editReturn['Shoe_Id'] == $shoe['Shoe_Id']) ? 'selected' : ''; ?>>
                                    Size <?php echo htmlspecialchars($shoe['size']); ?> (Stock: <?php echo $shoe['stock']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Quantity</label>
                        <input type="number" name="quantity" min="1" required 
                               value="<?php echo $editReturn ? $editReturn['quantity'] : ''; ?>"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Date</label>
                        <input type="date" name="date" required 
                               value="<?php echo $editReturn ? $editReturn['date'] : date('Y-m-d'); ?>"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" rows="3" 
                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Reason for return..."><?php echo $editReturn ? htmlspecialchars($editReturn['description']) : ''; ?></textarea>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <a href="returns.php" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                        Cancel
                    </a>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        <?php echo $action === 'add' ? 'Add Return' : 'Update Return'; ?>
                    </button>
                </div>
            </form>
        </div>
    <?php else: ?>
        <!-- Returns List -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold">Return History</h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shoe Size</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($returns)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">No returns found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($returns as $return): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        #<?php echo $return['R_Id']; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        Size <?php echo htmlspecialchars($return['size']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo $return['quantity']; ?> items
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo date('M d, Y', strtotime($return['date'])); ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <?php echo htmlspecialchars($return['description'] ?: '-'); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="?action=edit&id=<?php echo $return['R_Id']; ?>" 
                                           class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                                        <a href="?delete=<?php echo $return['R_Id']; ?>" 
                                           onclick="return confirmDelete('Are you sure you want to delete this return?')"
                                           class="text-red-600 hover:text-red-900">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?> 