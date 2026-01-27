# Hosting Platform Deployment Guide - Quiz Grading Fix

## Overview

This guide shows you how to deploy and run the quiz grading fix on your hosting platform. There are **3 methods** available - choose the one that works best for your hosting setup.

---

## Method 1: Web-Based Admin Tool (EASIEST - No SSH Required)

### Step 1: Upload Files

Upload these new files to your hosting via FTP/SFTP or your hosting control panel:

```
app/Http/Controllers/Admin/QuizMaintenanceController.php
resources/views/admin/quiz-maintenance/index.blade.php
```

### Step 2: Update Routes

The `routes/web.php` file has been updated with new routes. Make sure this change is deployed.

### Step 3: Access the Tool

1. Log in to your admin panel
2. Navigate to: `https://yourdomain.com/admin/quiz-maintenance`
3. Click "Run Diagnosis" to see broken questions
4. Enable "Dry Run" and click "Fix Issues" to preview changes
5. Disable "Dry Run" and click "Fix Issues" to apply fixes

### Advantages:
- ✅ No SSH access required
- ✅ Visual interface
- ✅ Dry run mode to preview changes
- ✅ Works on any hosting platform

---

## Method 2: Artisan Commands via SSH (RECOMMENDED)

### Step 1: Upload Files

Upload these new files:

```
app/Console/Commands/DiagnoseBrokenQuizzes.php
app/Console/Commands/FixQuizAnswerFormats.php
```

### Step 2: SSH into Your Server

```bash
ssh username@yourdomain.com
cd /path/to/your/laravel/project
```

### Step 3: Run Commands

**Diagnose issues:**
```bash
php artisan quiz:diagnose
```

**Preview fixes (dry run):**
```bash
php artisan quiz:fix --dry-run
```

**Apply fixes:**
```bash
php artisan quiz:fix
```

**Verify fixes:**
```bash
php artisan quiz:diagnose
```

### Advantages:
- ✅ Fast execution
- ✅ Detailed console output
- ✅ Can be automated
- ✅ Dry run option

---

## Method 3: Standalone PHP Scripts (Alternative)

### Step 1: Upload Files

Upload these files to your project root:

```
diagnose_broken_quizzes.php
fix_quiz_answer_formats.php
```

### Step 2: Run via SSH

```bash
ssh username@yourdomain.com
cd /path/to/your/laravel/project
php diagnose_broken_quizzes.php
php fix_quiz_answer_formats.php
```

### Step 3: Run via Web Browser (if no SSH)

1. Temporarily create a route in `routes/web.php`:

```php
Route::get('/run-quiz-diagnosis', function() {
    require base_path('diagnose_broken_quizzes.php');
});

Route::get('/run-quiz-fix', function() {
    require base_path('fix_quiz_answer_formats.php');
});
```

2. Visit:
   - `https://yourdomain.com/run-quiz-diagnosis`
   - `https://yourdomain.com/run-quiz-fix`

3. **IMPORTANT:** Remove these routes after running!

---

## Hosting Platform Specific Instructions

### cPanel Hosting

1. **Upload Files:**
   - Use File Manager or FTP
   - Navigate to `public_html` or your Laravel root

2. **Run Commands:**
   - Use Terminal in cPanel (if available)
   - Or use the Web-Based Admin Tool (Method 1)

3. **Alternative:**
   - Use cPanel's Cron Jobs to run: `php /home/username/public_html/artisan quiz:fix`

### Plesk Hosting

1. **Upload Files:**
   - Use File Manager or FTP

2. **Run Commands:**
   - Use SSH Terminal in Plesk
   - Or use Scheduled Tasks to run artisan commands

### AWS / DigitalOcean / VPS

1. **SSH Access:**
   ```bash
   ssh user@your-server-ip
   cd /var/www/html/your-project
   php artisan quiz:fix
   ```

2. **Or use the Web-Based Tool** (Method 1)

### Shared Hosting (No SSH)

**Use Method 1 (Web-Based Admin Tool)** - it's designed specifically for this scenario.

---

## Verification Steps

After running the fix, verify it worked:

### Via Web Tool:
1. Go to `/admin/quiz-maintenance`
2. Click "Run Diagnosis"
3. Should show: "✅ All questions are correctly formatted!"

### Via SSH:
```bash
php artisan quiz:diagnose
```

Should output: "✅ All questions appear to be correctly formatted!"

### Via Testing:
1. Log in as a student
2. Take a quiz that was previously broken
3. Answer questions correctly
4. Verify they're marked as correct

---

## Troubleshooting

### "Class not found" Error

**Solution:** Clear Laravel's cache:
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

Or via web:
```
https://yourdomain.com/clear-cache
```

### "Permission denied" Error

**Solution:** Fix file permissions:
```bash
chmod -R 755 app/Console/Commands
chmod -R 755 app/Http/Controllers/Admin
```

### Web Tool Not Loading

**Solution:** 
1. Check if routes are properly deployed
2. Clear route cache: `php artisan route:clear`
3. Check Laravel logs: `storage/logs/laravel.log`

### Database Connection Error

**Solution:**
1. Verify `.env` database credentials
2. Test connection: `php artisan tinker` then `DB::connection()->getPdo();`

---

## Rollback Plan

If something goes wrong:

### Option 1: Restore Database Backup
```bash
mysql -u username -p database_name < backup.sql
```

### Option 2: Manual Revert
The fix only modifies `correct_answer` and `options` fields. You can manually edit questions via the admin UI.

---

## Security Notes

### For Web-Based Tool:

Add authentication middleware to routes (already included):
```php
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/quiz-maintenance', ...);
});
```

### For Standalone Scripts:

**Delete or move outside web root after use:**
```bash
rm diagnose_broken_quizzes.php
rm fix_quiz_answer_formats.php
```

---

## Automation (Optional)

### Run Fix Automatically on Deploy

Add to your deployment script:
```bash
php artisan quiz:fix --dry-run
# Review output, then:
# php artisan quiz:fix
```

### Schedule Regular Checks

Add to `app/Console/Kernel.php`:
```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('quiz:diagnose')
        ->weekly()
        ->emailOutputTo('admin@yourdomain.com');
}
```

---

## Support Checklist

Before asking for help, verify:

- [ ] Files uploaded to correct locations
- [ ] Routes file updated
- [ ] Laravel cache cleared
- [ ] Database credentials correct
- [ ] Checked `storage/logs/laravel.log` for errors
- [ ] Tried all 3 methods

---

## Quick Reference

| Method | SSH Required | Best For |
|--------|-------------|----------|
| Web-Based Tool | ❌ No | Shared hosting, non-technical users |
| Artisan Commands | ✅ Yes | VPS, dedicated servers, developers |
| Standalone Scripts | ⚠️ Optional | Flexible, works both ways |

**Recommendation:** Start with Method 1 (Web-Based Tool) - it's the safest and easiest.
