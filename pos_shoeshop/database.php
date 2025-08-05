<?php
require_once 'includes/header.php';
require_once 'config/database.php';
require_once 'includes/permissions.php';
require_once 'whatsapp_notify.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$db = new Database();
$message = '';
$error = '';
$whatsappUrl = '';

// Handle database export
if (isset($_GET['action']) && $_GET['action'] === 'export') {
    if (canExportDatabase()) {
        $result = exportDatabase();
        if ($result['success']) {
            $message = "Database exported successfully to database/backups/" . $result['filename'];
            // Generate WhatsApp notification URL
            $whatsappUrl = sendWhatsAppNotification($result['filename']);
        } else {
            $error = "Export failed: " . $result['error'];
        }
    } else {
        $error = "You don't have permission to export the database.";
    }
}

// Handle database import
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import'])) {
    if (canImportDatabase()) {
        $result = importDatabase($_FILES['database_file']);
        if ($result['success']) {
            $message = "Database imported successfully!";
        } else {
            $error = "Import failed: " . $result['error'];
        }
    } else {
        $error = "You don't have permission to import the database.";
    }
}

function exportDatabase() {
    $sourceFile = 'database/pos_shoeshop.db';
    $exportDir = 'database/backups/';
    $timestamp = date('Y-m-d_H-i-s');
    $filename = "pos_shoeshop_backup_{$timestamp}.db";
    $destinationFile = $exportDir . $filename;
    
    // Create directory if it doesn't exist
    if (!is_dir($exportDir)) {
        if (!mkdir($exportDir, 0777, true)) {
            return ['success' => false, 'error' => 'Could not create export directory'];
        }
    }
    
    // Copy database file
    if (copy($sourceFile, $destinationFile)) {
        return ['success' => true, 'filename' => $filename];
    } else {
        return ['success' => false, 'error' => 'Could not copy database file'];
    }
}

function importDatabase($file) {
    // Validate file
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'File upload failed'];
    }
    
    if ($file['type'] !== 'application/octet-stream' && $file['type'] !== 'application/x-sqlite3') {
        return ['success' => false, 'error' => 'Invalid file type. Please upload a SQLite database file.'];
    }
    
    $sourceFile = $file['tmp_name'];
    $destinationFile = 'database/pos_shoeshop.db';
    
    // Create backup before import
    $backupFile = 'database/pos_shoeshop_backup_' . date('Y-m-d_H-i-s') . '.db';
    if (file_exists($destinationFile)) {
        copy($destinationFile, $backupFile);
    }
    
    // Copy new database
    if (copy($sourceFile, $destinationFile)) {
        return ['success' => true];
    } else {
        return ['success' => false, 'error' => 'Could not copy imported database'];
    }
}

// Get database info
$dbSize = file_exists('database/pos_shoeshop.db') ? filesize('database/pos_shoeshop.db') : 0;
$dbSizeFormatted = $dbSize > 0 ? number_format($dbSize / 1024, 2) . ' KB' : '0 KB';
$lastModified = file_exists('database/pos_shoeshop.db') ? date('Y-m-d H:i:s', filemtime('database/pos_shoeshop.db')) : 'Never';
?>

<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-900">Database Management</h1>
        <div class="flex space-x-3">
            <?php if (canExportDatabase()): ?>
                <a href="?action=export" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                    📤 Export Database
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <!-- Database Information -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-4">Database Information</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-blue-50 p-4 rounded-lg">
                <h3 class="text-sm font-medium text-blue-800">Database Size</h3>
                <p class="text-2xl font-bold text-blue-900"><?php echo $dbSizeFormatted; ?></p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <h3 class="text-sm font-medium text-green-800">Last Modified</h3>
                <p class="text-lg font-semibold text-green-900"><?php echo $lastModified; ?></p>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg">
                <h3 class="text-sm font-medium text-purple-800">Export Location</h3>
                <p class="text-sm font-semibold text-purple-900">database/backups/</p>
            </div>
        </div>
    </div>

    <!-- Export Section -->
    <?php if (canExportDatabase()): ?>
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Export Database</h2>
            <div class="space-y-4">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-blue-800 mb-2">Export Details</h3>
                    <ul class="text-sm text-blue-700 space-y-1">
                        <li>• Database will be copied to: <strong>database/backups/</strong></li>
                        <li>• Filename format: <strong>pos_shoeshop_backup_YYYY-MM-DD_HH-MM-SS.db</strong></li>
                        <li>• WhatsApp notification will be sent to: <strong>0716662848</strong></li>
                        <li>• Available to: <strong><?php echo getRoleDisplayName(getUserRole()); ?></strong></li>
                    </ul>
                </div>
                
                <div class="flex items-center space-x-4">
                    <a href="?action=export" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span>Export Database</span>
                    </a>
                    
                    <?php if ($whatsappUrl): ?>
                        <a href="<?php echo $whatsappUrl; ?>" 
                           target="_blank" 
                           class="bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-600 flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>
                            </svg>
                            <span>Open WhatsApp</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Import Section -->
    <?php if (canImportDatabase()): ?>
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Import Database</h2>
            <div class="space-y-4">
                <div class="bg-red-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-red-800 mb-2">⚠️ Important Warning</h3>
                    <ul class="text-sm text-red-700 space-y-1">
                        <li>• This will <strong>replace</strong> the current database</li>
                        <li>• A backup will be created automatically</li>
                        <li>• Only upload valid SQLite database files</li>
                        <li>• Available to: <strong><?php echo getRoleDisplayName(getUserRole()); ?></strong></li>
                    </ul>
                </div>
                
                <form method="POST" enctype="multipart/form-data" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Database File</label>
                        <input type="file" name="database_file" accept=".db,.sqlite,.sqlite3" required
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <p class="mt-1 text-sm text-gray-500">Only .db, .sqlite, or .sqlite3 files are allowed</p>
                    </div>
                    
                    <button type="submit" name="import" 
                            class="bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 flex items-center space-x-2"
                            onclick="return confirm('Are you sure you want to import this database? This will replace the current database.')">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        <span>Import Database</span>
                    </button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <!-- Permission Info -->
    <div class="bg-gray-50 rounded-lg p-6">
        <h2 class="text-lg font-semibold mb-4">Permission Information</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div>
                <h3 class="font-medium text-gray-900 mb-2">👑 Administrator</h3>
                <ul class="text-gray-600 space-y-1">
                    <li>• Can export database</li>
                    <li>• Can import database</li>
                    <li>• Full access to all features</li>
                </ul>
            </div>
            <div>
                <h3 class="font-medium text-gray-900 mb-2">👨‍💼 Owner</h3>
                <ul class="text-gray-600 space-y-1">
                    <li>• Can export database</li>
                    <li>• Cannot import database</li>
                    <li>• Can view history & add expenses</li>
                </ul>
            </div>
            <div>
                <h3 class="font-medium text-gray-900 mb-2">👤 User</h3>
                <ul class="text-gray-600 space-y-1">
                    <li>• Can export database</li>
                    <li>• Cannot import database</li>
                    <li>• Basic view-only access</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 