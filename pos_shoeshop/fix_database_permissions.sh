#!/bin/bash
# fix_database_permissions.sh
# Script to fix SQLite database permissions for POS Shoe Shop

echo "POS Shoe Shop - Database Permission Fix Script"
echo "=============================================="

# Check if we're in the right directory
if [ ! -f "config/database.php" ]; then
    echo "Error: This script must be run from the pos_shoeshop directory!"
    echo "Please navigate to the application directory and try again."
    exit 1
fi

echo "Setting up database permissions..."

# Create backup directory if it doesn't exist
mkdir -p database/backups
echo "✓ Created backup directory"

# Set directory permissions
chmod 775 database/
chmod 777 database/backups/
echo "✓ Set directory permissions"

# Set file permissions for existing database files
if [ -f "database/pos_shoeshop.db" ]; then
    chmod 664 database/pos_shoeshop.db
    echo "✓ Set main database file permissions"
fi

# Set permissions for backup files
chmod 664 database/*.db 2>/dev/null || true
echo "✓ Set backup file permissions"

# Set schema file permissions
if [ -f "database/schema.sql" ]; then
    chmod 644 database/schema.sql
    echo "✓ Set schema file permissions"
fi

echo ""
echo "Current database directory permissions:"
ls -la database/

echo ""
echo "Permission fix completed successfully!"
echo ""
echo "If you're running this on a web server (Apache/Nginx), you may also need to run:"
echo "sudo chown -R www-data:www-data database/"
echo ""
echo "Test your application now to ensure database operations work correctly."