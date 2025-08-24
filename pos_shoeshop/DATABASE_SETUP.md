# Database Setup and Permissions Guide

## SQLite Database Configuration

This POS application uses SQLite database which requires proper file and directory permissions to function correctly.

### Common Issues and Solutions

#### "readonly database" Error
**Problem:** SQLite returns error "SQLSTATE[HY000]: General error: 8 attempt to write a readonly database"

**Causes:**
1. Database file lacks write permissions for the web server user
2. Database directory lacks write permissions
3. SQLite can't create journal/WAL files due to permission restrictions

**Solutions:**

#### For Linux/Unix Systems:
```bash
# Set proper permissions for the database directory and files
chmod 775 database/
chmod 664 database/*.db

# If you have sudo access and need to set group ownership:
sudo chgrp www-data database/ database/*.db
sudo chmod g+w database/ database/*.db
```

#### For Development Environment:
```bash
# More permissive permissions for development
chmod 777 database/
chmod 666 database/*.db
```

#### For Production Environment:
```bash
# Secure permissions for production
chmod 770 database/
chmod 660 database/*.db
chown www-data:www-data database/ database/*.db
```

### File Permissions Explained

| Permission | Octal | Description |
|------------|-------|-------------|
| 777 | rwxrwxrwx | Full access for everyone (development only) |
| 775 | rwxrwxr-x | Owner/group read/write/execute, others read/execute |
| 770 | rwxrwx--- | Owner/group read/write/execute, others no access |
| 666 | rw-rw-rw- | Everyone can read/write (development only) |
| 664 | rw-rw-r-- | Owner/group read/write, others read only |
| 660 | rw-rw---- | Owner/group read/write, others no access |

### Directory Structure
```
database/
├── pos_shoeshop.db          (Main database file)
├── schema.sql               (Database schema)
├── backups/                 (Backup directory)
│   └── pos_shoeshop_backup_*.db
└── *.db                     (Other backup files)
```

### Automated Setup Script

Create this script to set proper permissions automatically:

```bash
#!/bin/bash
# setup_permissions.sh

# Navigate to the application directory
cd /path/to/your/pos_shoeshop

# Create backup directory if it doesn't exist
mkdir -p database/backups

# Set directory permissions
chmod 775 database/
chmod 777 database/backups/

# Set file permissions
chmod 664 database/*.db
chmod 644 database/schema.sql

# If running on a server, set proper ownership
# sudo chown -R www-data:www-data database/

echo "Database permissions have been set successfully!"
```

### Troubleshooting

1. **Check current permissions:**
   ```bash
   ls -la database/
   ```

2. **Test database connectivity:**
   ```bash
   php -r "
   require_once 'config/database.php';
   try {
       \$db = new Database();
       echo 'Database connection: SUCCESS\n';
   } catch (Exception \$e) {
       echo 'Error: ' . \$e->getMessage() . '\n';
   }"
   ```

3. **Test write operations:**
   ```bash
   php -r "
   require_once 'config/database.php';
   try {
       \$db = new Database();
       \$db->query('INSERT INTO Expenses (Category, amount, Description, Date) VALUES (?, ?, ?, ?)', 
                   ['Test', 1.0, 'Permission test', date('Y-m-d H:i:s')]);
       echo 'Database write: SUCCESS\n';
   } catch (Exception \$e) {
       echo 'Write Error: ' . \$e->getMessage() . '\n';
   }"
   ```

### Best Practices

1. **Regular Backups:** Use the built-in export feature regularly
2. **File Monitoring:** Monitor database file permissions after server updates
3. **Security:** Use minimal required permissions in production
4. **Testing:** Test database operations after any permission changes

### Support

If you continue to experience database issues:
1. Check web server error logs
2. Verify PHP SQLite extension is installed
3. Ensure sufficient disk space
4. Check for SQLite file locks (*.sqlite-journal, *.sqlite-wal files)