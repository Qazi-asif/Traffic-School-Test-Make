# Course Booklet System - Quick Start Guide

## Installation

### 1. Run Migrations
```bash
php artisan migrate
```

### 2. Seed Default Templates
```bash
php artisan db:seed --class=BookletTemplateSeeder
```

### 3. Ensure Storage is Linked
```bash
php artisan storage:link
```

### 4. Start Queue Worker (Required for PDF Generation)
```bash
php artisan queue:work
```

## Quick Test

### Create Your First Booklet

1. **Login as Admin**
   - Navigate to `/admin/booklets`

2. **Create a Booklet**
   - Click "Create New Booklet"
   - Select a course
   - Enter version (e.g., "2025.1")
   - Enter title (e.g., "Florida BDI Course Booklet")
   - Click "Create Booklet"
   - System will generate PDF automatically

3. **View the Booklet**
   - Click "Preview PDF" to view in browser
   - Click "Download PDF" to download

### Test Student Ordering

1. **Login as Student**
   - Navigate to "My Enrollments"
   - Click "Booklet" button on any enrollment

2. **Place Order**
   - Select "PDF Download"
   - Click "Place Order"

3. **Download**
   - Order will be processed automatically
   - Refresh page to see "Ready" status
   - Click "Download PDF"

## Admin Access

### Booklet Management
- **URL**: `/admin/booklets`
- **Features**:
  - Create/edit/delete booklets
  - Preview and download
  - Regenerate PDFs
  - Version control

### Order Management
- **URL**: `/admin/booklets/orders/all`
- **Features**:
  - View all orders
  - Filter by status
  - Bulk generate
  - Mark as printed/shipped
  - Track shipments

### Template Customization
- **URL**: `/admin/booklets/templates/all`
- **Features**:
  - Edit Blade templates
  - Customize styling
  - Preview changes

## Student Access

### My Booklets
- **URL**: `/booklets`
- **Features**:
  - View order history
  - Check order status
  - Download ready booklets

### Order Booklet
- **URL**: `/booklets/order/{enrollment_id}`
- **Formats**:
  - PDF Download (instant)
  - Print & Mail (3-5 days)
  - Print & Pickup (1-2 days)

## Common Tasks

### Process Pending Orders Manually
```bash
php artisan booklets:process-pending
```

### Check Failed Jobs
```bash
php artisan queue:failed
```

### Retry Failed Job
```bash
php artisan queue:retry {job_id}
```

### Clear All Failed Jobs
```bash
php artisan queue:flush
```

## Troubleshooting

### "Booklet not ready for download"
- Ensure queue worker is running
- Check order status in admin panel
- Run: `php artisan booklets:process-pending`

### "File not found"
- Check storage permissions
- Verify storage is linked
- Check `storage/app/booklets/` directory exists

### PDF Generation Errors
- Check logs: `storage/logs/laravel.log`
- Verify DomPDF is installed
- Test with simple content first

## Production Setup

### 1. Configure Queue Worker
Use Supervisor or systemd to keep queue worker running:

**Supervisor Config** (`/etc/supervisor/conf.d/laravel-worker.conf`):
```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/storage/logs/worker.log
```

### 2. Schedule Automatic Processing
Add to `app/Console/Kernel.php`:
```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('booklets:process-pending')->hourly();
}
```

Then add to crontab:
```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

### 3. Optimize Storage
- Use cloud storage (S3) for production
- Configure in `config/filesystems.php`
- Update `FILESYSTEM_DISK` in `.env`

## Navigation

### Admin Menu Items
Add to admin navigation:
```html
<a href="/admin/booklets">Course Booklets</a>
<a href="/admin/booklets/orders/all">Booklet Orders</a>
<a href="/admin/booklets/templates/all">Booklet Templates</a>
```

### Student Menu Items
Add to student navigation:
```html
<a href="/booklets">My Course Booklets</a>
```

## Next Steps

1. ✅ Run migrations
2. ✅ Seed templates
3. ✅ Start queue worker
4. ✅ Create first booklet
5. ✅ Test student ordering
6. ⬜ Customize templates
7. ⬜ Configure production queue
8. ⬜ Set up scheduled tasks

## Support

- Check documentation: `BOOKLET_SYSTEM_IMPLEMENTATION.md`
- Review logs: `storage/logs/laravel.log`
- Test queue: `php artisan queue:work --once`

---

**Ready to use!** 🚀
