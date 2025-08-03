<?php
require_once 'includes/header.php';
require_once 'config/database.php';

$db = new Database();
$action = $_GET['action'] ?? 'list';
$message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add') {
        $sup_id = $_POST['sup_id'];
        $cost = $_POST['cost'];
        $date = $_POST['date'];
        $other_details = $_POST['other_details'];
        
        $db->query("INSERT INTO Purchases (Sup_Id, cost, date, other_details) VALUES (?, ?, ?, ?)", 
                   [$sup_id, $cost, $date, $other_details]);
        $message = "Purchase recorded successfully!";
        $action = 'list';
    } elseif ($action === 'edit') {
        $p_id = $_POST['p_id'];
        $sup_id = $_POST['sup_id'];
        $cost = $_POST['cost'];
        $date = $_POST['date'];
        $other_details = $_POST['other_details'];
        
        $db->query("UPDATE Purchases SET Sup_Id = ?, cost = ?, date = ?, other_details = ? WHERE P_Id = ?", 
                   [$sup_id, $cost, $date, $other_details, $p_id]);
        $message = "Purchase updated successfully!";
        $action = 'list';
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $p_id = $_GET['delete'];
    $db->query("DELETE FROM Purchases WHERE P_Id = ?", [$p_id]);
    $message = "Purchase deleted successfully!";
    $action = 'list';
}

// Get purchases for listing
$purchases = $db->fetchAll("
    SELECT p.*, s.name as supplier_name 
    FROM Purchases p 
    JOIN Suppliers s ON p.Sup_Id = s.Sup_Id 
    ORDER BY p.date DESC, p.P_Id DESC
");

// Get suppliers for dropdown
$suppliers = $db->fetchAll("SELECT * FROM Suppliers ORDER BY name ASC");

// Get purchase for editing
$editPurchase = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $editPurchase = $db->fetchOne("SELECT * FROM Purchases WHERE P_Id = ?", [$_GET['id']]);
}
?>

<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-900">Purchases Management</h1>
        <?php if ($action === 'list'): ?>
            <a href="?action=add" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Add New Purchase
            </a>
        <?php else: ?>
            <a href="purchases.php" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
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
        <!-- Add/Edit Form -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">
                <?php echo $action === 'add' ? 'Add New Purchase' : 'Edit Purchase'; ?>
            </h2>
            
            <form method="POST" class="space-y-4">
                <?php if ($action === 'edit'): ?>
                    <input type="hidden" name="p_id" value="<?php echo $editPurchase['P_Id']; ?>">
                <?php endif; ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Supplier</label>
                        <select name="sup_id" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Supplier</option>
                            <?php foreach ($suppliers as $supplier): ?>
                                <option value="<?php echo $supplier['Sup_Id']; ?>" 
                                        <?php echo ($editPurchase && $editPurchase['Sup_Id'] == $supplier['Sup_Id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($supplier['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Cost (LKR)</label>
                        <input type="number" step="0.01" name="cost" required 
                               value="<?php echo $editPurchase ? $editPurchase['cost'] : ''; ?>"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Date</label>
                        <input type="date" name="date" required 
                               value="<?php echo $editPurchase ? $editPurchase['date'] : date('Y-m-d'); ?>"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Other Details</label>
                    <textarea name="other_details" rows="3" 
                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Additional details about the purchase..."><?php echo $editPurchase ? htmlspecialchars($editPurchase['other_details']) : ''; ?></textarea>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <a href="purchases.php" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                        Cancel
                    </a>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        <?php echo $action === 'add' ? 'Add Purchase' : 'Update Purchase'; ?>
                    </button>
                </div>
            </form>
        </div>
    <?php else: ?>
        <!-- Purchases List -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold">Purchase History</h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cost</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($purchases)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">No purchases found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($purchases as $purchase): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        #<?php echo $purchase['P_Id']; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($purchase['supplier_name']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        LKR <?php echo number_format($purchase['cost'], 2); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo date('M d, Y', strtotime($purchase['date'])); ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <?php echo htmlspecialchars($purchase['other_details'] ?: '-'); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="?action=edit&id=<?php echo $purchase['P_Id']; ?>" 
                                           class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                                        <a href="?delete=<?php echo $purchase['P_Id']; ?>" 
                                           onclick="return confirmDelete('Are you sure you want to delete this purchase?')"
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