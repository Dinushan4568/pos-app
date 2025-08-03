<?php
require_once 'includes/header.php';
require_once 'config/database.php';
require_once 'includes/permissions.php';

// Check if user can manage users
requirePermission('manage_users');

$db = new Database();
$action = $_GET['action'] ?? 'list';
$message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add') {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $role = $_POST['role'];
        
        // Check if username already exists
        $existing = $db->fetchOne("SELECT username FROM Users WHERE username = ?", [$username]);
        if ($existing) {
            $message = "Error: Username '$username' already exists!";
        } elseif ($password !== $confirm_password) {
            $message = "Error: Passwords do not match!";
        } else {
            $db->query("INSERT INTO Users (username, password, role) VALUES (?, ?, ?)", [$username, $password, $role]);
            $message = "User added successfully!";
            $action = 'list';
        }
    } elseif ($action === 'edit') {
        $old_username = $_POST['old_username'];
        $username = $_POST['username'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $role = $_POST['role'];
        
        // Check if new username already exists (excluding current user)
        $existing = $db->fetchOne("SELECT username FROM Users WHERE username = ? AND username != ?", [$username, $old_username]);
        if ($existing) {
            $message = "Error: Username '$username' already exists!";
        } elseif ($password !== $confirm_password) {
            $message = "Error: Passwords do not match!";
        } else {
            $db->query("UPDATE Users SET username = ?, password = ?, role = ? WHERE username = ?", [$username, $password, $role, $old_username]);
            $message = "User updated successfully!";
            $action = 'list';
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $username = $_GET['delete'];
    if ($username !== 'admin') {
        $db->query("DELETE FROM Users WHERE username = ?", [$username]);
        $message = "User deleted successfully!";
    } else {
        $message = "Error: Cannot delete admin user!";
    }
    $action = 'list';
}

// Get users for listing
$users = $db->fetchAll("SELECT * FROM Users ORDER BY username ASC");

// Get user for editing
$editUser = null;
if ($action === 'edit' && isset($_GET['username'])) {
    $editUser = $db->fetchOne("SELECT * FROM Users WHERE username = ?", [$_GET['username']]);
}
?>

<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-900">Users Management</h1>
        <?php if ($action === 'list'): ?>
            <a href="?action=add" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Add New User
            </a>
        <?php else: ?>
            <a href="users.php" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
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
                <?php echo $action === 'add' ? 'Add New User' : 'Edit User'; ?>
            </h2>
            
            <form method="POST" class="space-y-4">
                <?php if ($action === 'edit'): ?>
                    <input type="hidden" name="old_username" value="<?php echo $editUser['username']; ?>">
                <?php endif; ?>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Username *</label>
                        <input type="text" name="username" required 
                               value="<?php echo $editUser ? htmlspecialchars($editUser['username']) : ''; ?>"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Password *</label>
                        <input type="password" name="password" required 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <p class="mt-1 text-sm text-gray-500">
                            <?php echo $action === 'edit' ? 'Leave blank to keep current password' : 'Enter a secure password'; ?>
                        </p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Role *</label>
                        <select name="role" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="user" <?php echo ($editUser && $editUser['role'] === 'user') ? 'selected' : ''; ?>>User</option>
                            <option value="owner" <?php echo ($editUser && $editUser['role'] === 'owner') ? 'selected' : ''; ?>>Owner</option>
                            <option value="admin" <?php echo ($editUser && $editUser['role'] === 'admin') ? 'selected' : ''; ?>>Administrator</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Confirm Password *</label>
                        <input type="password" name="confirm_password" required 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <a href="users.php" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                        Cancel
                    </a>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        <?php echo $action === 'add' ? 'Add User' : 'Update User'; ?>
                    </button>
                </div>
            </form>
        </div>
    <?php else: ?>
        <!-- Users List -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold">System Users</h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-center text-gray-500">No users found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($user['username']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo getRoleColor($user['role']); ?>">
                                            <?php echo getRoleDisplayName($user['role']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="?action=edit&username=<?php echo urlencode($user['username']); ?>" 
                                           class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                                        <?php if ($user['username'] !== 'admin'): ?>
                                            <a href="?delete=<?php echo urlencode($user['username']); ?>" 
                                               onclick="return confirmDelete('Are you sure you want to delete this user?')"
                                               class="text-red-600 hover:text-red-900">Delete</a>
                                        <?php endif; ?>
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

<script>
function confirmDelete(message) {
    return confirm(message);
}
</script>

<?php require_once 'includes/footer.php'; ?> 