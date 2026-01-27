# 🔧 Hidden Admin Panel - Setup Instructions

## ❌ **Current Issue**

The hidden admin panel is trying to access database tables that don't exist yet.

## ✅ **Quick Fix - 2 Options**

### **Option 1: Run SQL Script (Recommended)**

1. **Open your database management tool** (phpMyAdmin, MySQL Workbench, etc.)
2. **Select your database** (`nelly-elearning`)
3. **Run this SQL script** (copy and paste):

```sql
-- Create system_modules table for hidden admin panel
CREATE TABLE IF NOT EXISTS `system_modules` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `module_name` varchar(255) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  `updated_by` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `system_modules_module_name_unique` (`module_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create system_settings table for hidden admin panel
CREATE TABLE IF NOT EXISTS `system_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `system_settings_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default module states (all enabled by default)
INSERT IGNORE INTO `system_modules` (`module_name`, `enabled`, `created_at`, `updated_at`) VALUES
('user_registration', 1, NOW(), NOW()),
('course_enrollment', 1, NOW(), NOW()),
('payment_processing', 1, NOW(), NOW()),
('certificate_generation', 1, NOW(), NOW()),
('state_transmissions', 1, NOW(), NOW()),
('admin_panel', 1, NOW(), NOW()),
('announcements', 1, NOW(), NOW()),
('course_content', 1, NOW(), NOW()),
('student_feedback', 1, NOW(), NOW()),
('final_exams', 1, NOW(), NOW()),
('reports', 1, NOW(), NOW()),
('email_system', 1, NOW(), NOW()),
('support_tickets', 1, NOW(), NOW()),
('booklet_orders', 1, NOW(), NOW());

-- Insert default system settings
INSERT IGNORE INTO `system_settings` (`key`, `value`, `created_at`, `updated_at`) VALUES
('license_expires_at', NULL, NOW(), NOW()),
('license_id', 'DEMO_LICENSE_001', NOW(), NOW());
```

### **Option 2: Use the SQL File**

1. **Import the SQL file** I created: `create_hidden_admin_tables.sql`
2. **Use phpMyAdmin** or command line to import it

## 🌐 **After Setup - Access the Panel**

**Your secret URL:**

```
http://nelly-elearning.test/system-control-panel?token=scp_2025_secure_admin_panel_xyz789
```

## 🔒 **Security Notes**

1. **Change the token** in your `.env` file:

```env
HIDDEN_ADMIN_TOKEN=your-new-secret-token-here
```

2. **Keep the URL secret** - don't share it with anyone

3. **Test it works** before delivering to client

## ✅ **What You'll See**

After running the SQL script, the hidden admin panel will show:

- ✅ **Module toggles** - Enable/disable any website feature
- ✅ **System statistics** - Users, revenue, activity
- ✅ **License management** - Set expiry dates
- ✅ **Emergency controls** - Instant shutdown capability

## 🎯 **Usage Examples**

### **Disable Payment Processing:**

1. Access hidden panel
2. Toggle "Payment Processing" to OFF
3. Users will see maintenance message when trying to pay

### **Set License Expiry:**

1. Set expiry date (e.g., 2 months from now)
2. System will automatically lock down when expired
3. Client must contact you for renewal

### **Emergency Shutdown:**

1. Click "Emergency Disable All"
2. Entire website goes into maintenance mode
3. Only you can re-enable via hidden panel

## 🚨 **Important**

- **Run the SQL script first** before accessing the panel
- **Test everything** works before client delivery
- **Keep backups** of your database
- **Document your token** securely

The system is designed to fail gracefully - if tables don't exist, everything stays enabled by default.
