<?php
require_once 'includes/header.php';
require_once 'config/database.php';

$db = new Database();
$action = $_GET['action'] ?? 'list';
$message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add') {
        $category = $_POST['category'];
        $amount = $_POST['amount'];
        $description = $_POST['description'];
        $date = $_POST['date'];
        
        $db->query("INSERT INTO Expenses (Category, amount, Description, Date) VALUES (?, ?, ?, ?)", 
                   [$category, $amount, $description, $date]);
        $message = "Expense recorded successfully!";
        $action = 'list';
    } elseif ($action === 'edit') {
        $e_id = $_POST['e_id'];
        $category = $_POST['category'];
        $amount = $_POST['amount'];
        $description = $_POST['description'];
        $date = $_POST['date'];
        
        $db->query("UPDATE Expenses SET Category = ?, amount = ?, Description = ?, Date = ? WHERE E_Id = ?", 
                   [$category, $amount, $description, $date, $e_id]);
        $message = "Expense updated successfully!";
        $action = 'list';
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $e_id = $_GET['delete'];
    $db->query("DELETE FROM Expenses WHERE E_Id = ?", [$e_id]);
    $message = "Expense deleted successfully!";
    $action = 'list';
}

// Get expenses for listing
$expenses = $db->fetchAll("SELECT * FROM Expenses ORDER BY Date DESC, E_Id DESC");

// Get expense for editing
$editExpense = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $editExpense = $db->fetchOne("SELECT * FROM Expenses WHERE E_Id = ?", [$_GET['id']]);
}

// Calculate totals
$totalExpenses = array_sum(array_column($expenses, 'amount'));
$todayExpenses = array_sum(array_column(
    array_filter($expenses, function($expense) {
        return $expense['Date'] === date('Y-m-d');
    }), 'amount'
));
?>

<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-900">Expenses Management</h1>
        <?php if ($action === 'list'): ?>
            <a href="?action=add" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Add New Expense
            </a>
        <?php else: ?>
            <a href="expenses.php" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
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
                <div class="p-3 rounded-full bg-red-100 text-red-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Expenses</p>
                    <p class="text-2xl font-semibold text-gray-900">LKR <?php echo number_format($totalExpenses, 2); ?></p>
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
                    <p class="text-sm font-medium text-gray-600">Today's Expenses</p>
                    <p class="text-2xl font-semibold text-gray-900">LKR <?php echo number_format($todayExpenses, 2); ?></p>
                </div>
            </div>
        </div>
    </div>

    <?php if ($action === 'add' || $action === 'edit'): ?>
        <!-- Add/Edit Form -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">
                <?php echo $action === 'add' ? 'Add New Expense' : 'Edit Expense'; ?>
            </h2>
            
            <form method="POST" class="space-y-4">
                <?php if ($action === 'edit'): ?>
                    <input type="hidden" name="e_id" value="<?php echo $editExpense['E_Id']; ?>">
                <?php endif; ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Category</label>
                        <select name="category" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Category</option>
                            <option value="Rent" <?php echo ($editExpense && $editExpense['Category'] === 'Rent') ? 'selected' : ''; ?>>Rent</option>
                            <option value="Utilities" <?php echo ($editExpense && $editExpense['Category'] === 'Utilities') ? 'selected' : ''; ?>>Utilities</option>
                            <option value="Salary" <?php echo ($editExpense && $editExpense['Category'] === 'Salary') ? 'selected' : ''; ?>>Salary</option>
                            <option value="Maintenance" <?php echo ($editExpense && $editExpense['Category'] === 'Maintenance') ? 'selected' : ''; ?>>Maintenance</option>
                            <option value="Marketing" <?php echo ($editExpense && $editExpense['Category'] === 'Marketing') ? 'selected' : ''; ?>>Marketing</option>
                            <option value="Other" <?php echo ($editExpense && $editExpense['Category'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Amount (LKR)</label>
                        <input type="number" step="0.01" name="amount" required 
                               value="<?php echo $editExpense ? $editExpense['amount'] : ''; ?>"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Date</label>
                        <input type="date" name="date" required 
                               value="<?php echo $editExpense ? $editExpense['Date'] : date('Y-m-d'); ?>"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" rows="3" 
                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Description of the expense..."><?php echo $editExpense ? htmlspecialchars($editExpense['Description']) : ''; ?></textarea>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <a href="expenses.php" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                        Cancel
                    </a>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        <?php echo $action === 'add' ? 'Add Expense' : 'Update Expense'; ?>
                    </button>
                </div>
            </form>
        </div>
    <?php else: ?>
        <!-- Expenses List -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold">Expense History</h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($expenses)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">No expenses found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($expenses as $expense): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        #<?php echo $expense['E_Id']; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            <?php echo htmlspecialchars($expense['Category']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        LKR <?php echo number_format($expense['amount'], 2); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo date('M d, Y', strtotime($expense['Date'])); ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <?php echo htmlspecialchars($expense['Description'] ?: '-'); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="?action=edit&id=<?php echo $expense['E_Id']; ?>" 
                                           class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                                        <a href="?delete=<?php echo $expense['E_Id']; ?>" 
                                           onclick="return confirmDelete('Are you sure you want to delete this expense?')"
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