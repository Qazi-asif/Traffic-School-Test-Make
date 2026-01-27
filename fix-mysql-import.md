# MySQL Import Fix Guide

## Problem
SQL import failing with:
- "Missing expression (near 'ON' at position 25)"
- "MySQL server has gone away"
- Foreign key constraint errors

## Solution

### Step 1: Update MySQL Configuration
Add these settings to your MySQL configuration (my.cnf or my.ini):

```ini
[mysql]
max_allowed_packet = 1024M
wait_timeout = 28800
interactive_timeout = 28800

[mysqld]
max_allowed_packet = 1024M
wait_timeout = 28800
interactive_timeout = 28800
innodb_buffer_pool_size = 256M
innodb_log_file_size = 64M
```

### Step 2: Fix the SQL File
Before importing, edit the SQL file:

1. **Find and replace problematic lines:**
   - Replace: `SET FOREIGN_KEY_CHECKS = ON;`
   - With: `SET FOREIGN_KEY_CHECKS = 1;`

2. **Add these lines at the beginning:**
```sql
SET FOREIGN_KEY_CHECKS = 0;
SET AUTOCOMMIT = 0;
START TRANSACTION;
```

3. **Add these lines at the end:**
```sql
COMMIT;
SET FOREIGN_KEY_CHECKS = 1;
```

### Step 3: Import via Command Line (Recommended)
Instead of phpMyAdmin, use command line:

```bash
mysql -u root -p --max_allowed_packet=1024M your_database_name < wolkeweb56541_elearning.sql
```

### Step 4: Alternative - Split Import
If still failing, split the import:

```bash
# Import structure only
mysql -u root -p your_database_name < structure_only.sql

# Import data only
mysql -u root -p your_database_name < data_only.sql
```

### Step 5: phpMyAdmin Settings (if using phpMyAdmin)
In phpMyAdmin config (config.inc.php):

```php
$cfg['ExecTimeLimit'] = 0;
$cfg['MemoryLimit'] = '512M';
$cfg['UploadDir'] = '';
$cfg['SaveDir'] = '';
```

## Quick Fix Commands

### Restart MySQL Service:
```bash
# Windows
net stop mysql
net start mysql

# Linux/Mac
sudo service mysql restart
```

### Test Connection:
```bash
mysql -u root -p -e "SELECT 1;"
```

## Prevention for Future Exports

When exporting, use these settings:
- ✅ Add DROP TABLE statements
- ✅ Disable foreign key checks
- ✅ Use extended inserts
- ✅ Export in chunks (if very large)

## If All Else Fails

1. **Create fresh database**
2. **Run Laravel migrations**: `php artisan migrate:fresh`
3. **Import only data** (not structure)
4. **Use seeders** to populate test data