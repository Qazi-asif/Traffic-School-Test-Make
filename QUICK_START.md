# 🚀 Email System - Quick Start Guide

## ⚡ 3-Minute Setup

### 1. Register Provider
Add to `config/app.php`:
```php
App\Providers\EventServiceProvider::class,
```

### 2. Update .env
```env
QUEUE_CONNECTION=database
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
```

### 3. Clear Cache & Start Queue
```bash
php artisan config:clear
php artisan queue:work
```

## ✅ That's It!

Emails will now automatically send when:
- ✉️ User enrolls in course
- ✉️ Payment is approved
- ✉️ Course is completed
- ✉️ Certificate is generated

## 🧪 Test Now

```bash
php artisan tinker
```

```php
$enrollment = App\Models\UserCourseEnrollment::first();
event(new App\Events\UserEnrolled($enrollment));
```

Check your Mailtrap inbox!

## 📚 Full Documentation

- **Setup Guide:** `EMAIL_SYSTEM_SETUP.md`
- **Implementation:** `EMAIL_SYSTEM_IMPLEMENTATION.md`
- **Test Script:** `test-emails.php`

## 🆘 Troubleshooting

**Emails not sending?**
```bash
# Check queue is running
ps aux | grep queue:work

# Check failed jobs
php artisan queue:failed

# Check logs
tail -f storage/logs/laravel.log
```

**Restart queue:**
```bash
php artisan queue:restart
php artisan queue:work
```

## 🎯 Email Templates Location

```
resources/views/emails/
├── courses/enrolled.blade.php
├── courses/completed.blade.php
├── payments/approved.blade.php
└── certificates/generated.blade.php
```

Edit these files to customize your emails!

---

**Ready to go!** 🎉
