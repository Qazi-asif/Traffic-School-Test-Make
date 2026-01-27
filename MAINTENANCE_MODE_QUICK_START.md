# Maintenance Mode - Quick Start Guide

## What Was Fixed

The maintenance mode now properly blocks regular users while allowing admins to continue working.

## How to Use

### Enable Maintenance Mode
1. Go to `http://127.0.0.1:8000/admin/settings`
2. Find the "Maintenance Mode" section
3. Toggle "Enable Maintenance Mode" ON
4. Enter a custom message (optional)
5. Click "Save"

### Disable Maintenance Mode
1. Go to `http://127.0.0.1:8000/admin/settings`
2. Find the "Maintenance Mode" section
3. Toggle "Enable Maintenance Mode" OFF
4. Click "Save"

## What Happens When Maintenance Mode is ON

### Regular Users See:
- Maintenance page with HTTP 503 status
- Custom message you provided
- Cannot access any public pages

### Admin Users Can:
- Access `/admin/*` routes normally
- Continue managing the site
- Enable/disable maintenance mode

### API Routes:
- Continue working normally
- No maintenance page shown

## Testing the Fix

### Test 1: Enable Maintenance Mode
```bash
# As admin user, enable maintenance mode
# Then open a new browser/incognito window
# Try to access: http://127.0.0.1:8000/courses
# Expected: See maintenance page
```

### Test 2: Admin Access
```bash
# While maintenance mode is ON
# Try to access: http://127.0.0.1:8000/admin/settings
# Expected: Page loads normally (no maintenance page)
```

### Test 3: Disable Maintenance Mode
```bash
# Disable maintenance mode from admin settings
# Try to access: http://127.0.0.1:8000/courses
# Expected: Page loads normally
```

## Troubleshooting

### Maintenance page not showing?
1. Clear caches: `php artisan config:clear && php artisan cache:clear`
2. Check that `settings` table exists
3. Verify maintenance_mode setting is set to true

### Admin can't access admin pages?
1. Check user's role_id (should be 1 or 2)
2. Verify user is authenticated
3. Check middleware is registered in Kernel.php

### Getting 500 error?
1. Check Laravel logs: `tail -f storage/logs/laravel.log`
2. Verify database connection
3. Run migrations if needed: `php artisan migrate`

## Key Changes Made

1. **Middleware Fix** - `CheckMaintenanceMode.php`
   - Now checks `role_id` directly for better reliability
   - Properly handles both authenticated and unauthenticated users

2. **Composer Update** - `composer.json`
   - Updated PHP version requirement to support 8.2, 8.3, and 8.4

## Files Modified

- `app/Http/Middleware/CheckMaintenanceMode.php`
- `composer.json`

## Related Files (No Changes Needed)

- `app/Models/Setting.php` - Already has all required methods
- `app/Http/Kernel.php` - Middleware already registered
- `app/Http/Controllers/SettingsController.php` - Already has all required methods
- `routes/web.php` - Routes already properly configured

## Next Steps

1. Test the maintenance mode in your local environment
2. Deploy the changes to production
3. Monitor the logs for any issues
4. Use maintenance mode when needed for updates/maintenance

## Support

For issues or questions, check:
1. `MAINTENANCE_MODE_FIX.md` - Detailed technical documentation
2. Laravel logs - `storage/logs/laravel.log`
3. Database - Verify `settings` table has correct data
