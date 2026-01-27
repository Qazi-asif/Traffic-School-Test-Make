# 📧 Automated Email System - Complete Setup Guide

## 📁 File Structure Created

```
app/
├── Events/
│   ├── UserEnrolled.php
│   ├── PaymentApproved.php
│   ├── CourseCompleted.php
│   └── CertificateGenerated.php
├── Listeners/
│   ├── SendEnrollmentConfirmation.php
│   ├── SendPaymentApprovedEmail.php
│   ├── SendCourseCompletedEmail.php
│   └── SendCertificateEmail.php
├── Notifications/
│   ├── EnrollmentConfirmation.php
│   ├── PaymentApprovedNotification.php
│   ├── CourseCompletedNotification.php
│   └── CertificateGeneratedNotification.php
└── Providers/
    └── EventServiceProvider.php

resources/views/emails/
├── courses/
│   ├── enrolled.blade.php
│   └── completed.blade.php
├── payments/
│   └── approved.blade.php
└── certificates/
    └── generated.blade.php
```

## 🚀 Installation Steps

### 1. Install Required Packages

```bash
composer require barryvdh/laravel-dompdf
```

### 2. Configure Queue Driver

Update `.env`:

```env
QUEUE_CONNECTION=database

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@elearning.com
MAIL_FROM_NAME="E-Learning Platform"
```

### 3. Run Migrations

```bash
php artisan migrate
```

### 4. Register EventServiceProvider

Add to `config/app.php` in the `providers` array:

```php
App\Providers\EventServiceProvider::class,
```

### 5. Clear Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan event:clear
```

## 🎯 How It Works

### Event Flow

1. **Enrollment** → `UserEnrolled` event → `SendEnrollmentConfirmation` listener → Email sent
2. **Payment** → `PaymentApproved` event → `SendPaymentApprovedEmail` listener → Email sent
3. **Course Completion** → `CourseCompleted` event → `SendCourseCompletedEmail` listener → Email sent
4. **Certificate** → `CertificateGenerated` event → `SendCertificateEmail` listener → Email with PDF attachment

### Integration Points

#### ✅ Already Integrated:

1. **PaymentPageController** - Dispatches `UserEnrolled` and `PaymentApproved` events
2. **CourseCompletionController** - Dispatches `CourseCompleted` event
3. **CertificateController** - Dispatches `CertificateGenerated` event

## 🔧 Running the Queue Worker

### Development (Single Worker)

```bash
php artisan queue:work --tries=3
```

### Production (Supervisor)

Create `/etc/supervisor/conf.d/laravel-worker.conf`:

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/project/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/path/to/your/project/storage/logs/worker.log
stopwaitsecs=3600
```

Start supervisor:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

## 🧪 Testing

### Manual Event Dispatch (Tinker)

```bash
php artisan tinker
```

```php
// Test Enrollment Email
$enrollment = App\Models\UserCourseEnrollment::first();
event(new App\Events\UserEnrolled($enrollment));

// Test Payment Email
$payment = App\Models\Payment::first();
event(new App\Events\PaymentApproved($payment));

// Test Course Completion Email
$enrollment = App\Models\UserCourseEnrollment::where('status', 'completed')->first();
event(new App\Events\CourseCompleted($enrollment));

// Test Certificate Email
$certificate = App\Models\Certificate::first();
event(new App\Events\CertificateGenerated($certificate));
```

### Check Queue Jobs

```bash
# View pending jobs
php artisan queue:work --once

# View failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

### Email Preview (Mailtrap)

1. Sign up at https://mailtrap.io
2. Get SMTP credentials
3. Update `.env` with Mailtrap settings
4. All emails will be caught in Mailtrap inbox

## 📋 Email Templates

All email templates are located in `resources/views/emails/`:

- **enrolled.blade.php** - Enrollment confirmation with course details
- **approved.blade.php** - Payment receipt with invoice
- **completed.blade.php** - Course completion congratulations
- **generated.blade.php** - Certificate with PDF attachment

### Customizing Templates

Edit the Blade files to match your branding. Variables available:

- `$user` - User model
- `$enrollment` - Enrollment model
- `$course` - Course model
- `$payment` - Payment model
- `$certificate` - Certificate model

## 🔄 Queue Management Commands

```bash
# Start queue worker
php artisan queue:work

# Process only one job
php artisan queue:work --once

# Stop after processing current job
php artisan queue:restart

# Clear all jobs
php artisan queue:clear

# View failed jobs
php artisan queue:failed

# Retry specific failed job
php artisan queue:retry {id}

# Retry all failed jobs
php artisan queue:retry all

# Forget failed job
php artisan queue:forget {id}

# Flush all failed jobs
php artisan queue:flush
```

## 📊 Monitoring

### Check Queue Status

```bash
# Count pending jobs
php artisan queue:monitor database

# View queue statistics
php artisan horizon:stats  # If using Horizon
```

### Log Files

- Queue logs: `storage/logs/laravel.log`
- Worker logs: `storage/logs/worker.log` (if using Supervisor)

## 🎨 Customization

### Add New Email Notification

1. Create Event:
```bash
php artisan make:event YourEvent
```

2. Create Listener:
```bash
php artisan make:listener SendYourEmail --event=YourEvent
```

3. Create Notification:
```bash
php artisan make:notification YourNotification
```

4. Register in `EventServiceProvider.php`

5. Create email template in `resources/views/emails/`

## 🐛 Troubleshooting

### Emails Not Sending

1. Check queue is running: `ps aux | grep queue:work`
2. Check failed jobs: `php artisan queue:failed`
3. Check logs: `tail -f storage/logs/laravel.log`
4. Verify mail config: `php artisan config:cache`

### Queue Worker Stops

- Use Supervisor for auto-restart
- Check memory limits in `php.ini`
- Monitor with `supervisorctl status`

### PDF Attachment Issues

1. Ensure `barryvdh/laravel-dompdf` is installed
2. Check certificate `pdf_path` exists in storage
3. Verify storage permissions: `chmod -R 775 storage`

## 📝 Production Checklist

- [ ] Queue driver set to `database`
- [ ] Supervisor configured and running
- [ ] Mail credentials configured
- [ ] Failed job monitoring setup
- [ ] Log rotation configured
- [ ] Storage permissions correct
- [ ] EventServiceProvider registered
- [ ] All migrations run
- [ ] Cache cleared

## 🎯 Next Steps

1. Configure real SMTP (Gmail, SendGrid, AWS SES)
2. Add email tracking/analytics
3. Implement email preferences
4. Add unsubscribe functionality
5. Create admin dashboard for email logs
6. Set up email rate limiting
7. Add scheduled reminder emails

## 📧 Support

For issues or questions, contact your development team.
