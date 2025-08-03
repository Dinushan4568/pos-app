<?php
require_once 'includes/header.php';
require_once 'config/database.php';

$db = new Database();
$action = $_GET['action'] ?? 'list';
$message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add') {
        $name = $_POST['name'];
        $mobile = $_POST['mobile'];
        $seller_details = $_POST['seller_details'];
        
        $db->query("INSERT INTO Suppliers (name, mobile, Seller_details) VALUES (?, ?, ?)", 
                   [$name, $mobile, $seller_details]);
        $message = "Supplier added successfully!";
        $action = 'list';
    } elseif ($action === 'edit') {
        $sup_id = $_POST['sup_id'];
        $name = $_POST['name'];
        $mobile = $_POST['mobile'];
        $seller_details = $_POST['seller_details'];
        
        $db->query("UPDATE Suppliers SET name = ?, mobile = ?, Seller_details = ? WHERE Sup_Id = ?", 
                   [$name, $mobile, $seller_details, $sup_id]);
        $message = "Supplier updated successfully!";
        $action = 'list';
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $sup_id = $_GET['delete'];
    
    // Check if supplier has any purchases
    $purchases = $db->fetchOne("SELECT COUNT(*) as count FROM Purchases WHERE Sup_Id = ?", [$sup_id]);
    if ($purchases['count'] > 0) {
        $message = "Cannot delete supplier. They have associated purchases.";
    } else {
        $db->query("DELETE FROM Suppliers WHERE Sup_Id = ?", [$sup_id]);
        $message = "Supplier deleted successfully!";
    }
    $action = 'list';
}

// Get suppliers for listing
$suppliers = $db->fetchAll("SELECT * FROM Suppliers ORDER BY name ASC");

// Get supplier for editing
$editSupplier = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $editSupplier = $db->fetchOne("SELECT * FROM Suppliers WHERE Sup_Id = ?", [$_GET['id']]);
}
?>

<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-900">Suppliers Management</h1>
        <?php if ($action === 'list'): ?>
            <a href="?action=add" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Add New Supplier
            </a>
        <?php else: ?>
            <a href="suppliers.php" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
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
                <?php echo $action === 'add' ? 'Add New Supplier' : 'Edit Supplier'; ?>
            </h2>
            
            <form method="POST" class="space-y-4">
                <?php if ($action === 'edit'): ?>
                    <input type="hidden" name="sup_id" value="<?php echo $editSupplier['Sup_Id']; ?>">
                <?php endif; ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Name</label>
                        <input type="text" name="name" required 
                               value="<?php echo $editSupplier ? htmlspecialchars($editSupplier['name']) : ''; ?>"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Mobile</label>
                        <input type="text" name="mobile" 
                               value="<?php echo $editSupplier ? htmlspecialchars($editSupplier['mobile']) : ''; ?>"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Seller Details</label>
                    <textarea name="seller_details" rows="3" 
                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Additional details about the supplier..."><?php echo $editSupplier ? htmlspecialchars($editSupplier['Seller_details']) : ''; ?></textarea>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <a href="suppliers.php" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                        Cancel
                    </a>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        <?php echo $action === 'add' ? 'Add Supplier' : 'Update Supplier'; ?>
                    </button>
                </div>
            </form>
        </div>
    <?php else: ?>
        <!-- Suppliers List -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold">Suppliers List</h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mobile</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($suppliers)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">No suppliers found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($suppliers as $supplier): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        #<?php echo $supplier['Sup_Id']; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($supplier['name']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo htmlspecialchars($supplier['mobile'] ?: '-'); ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <?php echo htmlspecialchars($supplier['Seller_details'] ?: '-'); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="?action=edit&id=<?php echo $supplier['Sup_Id']; ?>" 
                                           class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                                        <a href="?delete=<?php echo $supplier['Sup_Id']; ?>" 
                                           onclick="return confirmDelete('Are you sure you want to delete this supplier?')"
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