# Production Deployment Quick Guide
**Laravel Traffic School Platform**

---

## 🚀 DEPLOYMENT STEPS

### 1. Pre-Deployment Checklist

```bash
# ✅ Already Done
✓ APP_DEBUG=false
✓ APP_ENV=production  
✓ HTTPS configured
✓ Code style fixed (443 issues)
✓ Missing views created (3 files)
✓ .env protected in .gitignore

# ⚠️ Must Do Before Deploy
□ Set strong DB_PASSWORD in .env
□ Review 17 unprotected admin routes
□ Fix 4 missing controller methods
□ Test all critical flows
```

### 2. Upload to Server

```bash
# Upload these files/folders:
- app/
- bootstrap/
- config/
- database/
- public/
- resources/
- routes/
- storage/
- vendor/ (or run composer install on server)
- .env (create from .env.example)
- .htaccess
- artisan
- composer.json
- composer.lock
```

### 3. Server Configuration

```bash
# SSH into server
cd /path/to/your/app

# Install dependencies
composer install --optimize-autoloader --no-dev

# Set permissions
chmod -R 755 storage bootstrap/cache
chmod 600 .env

# Generate app key (if needed)
php artisan key:generate

# Run migrations
php artisan migrate --force

# Cache everything
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### 4. Environment Variables

```bash
# Critical .env settings:
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_PASSWORD=<strong-password>

SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true

QUEUE_CONNECTION=database
CACHE_DRIVER=file  # or redis

# Payment Gateways
STRIPE_KEY=your_key
STRIPE_SECRET=your_secret
PAYPAL_CLIENT_ID=your_id
PAYPAL_SECRET=your_secret
AUTHORIZENET_LOGIN_ID=your_id
AUTHORIZENET_TRANSACTION_KEY=your_key

# Email
MAIL_MAILER=smtp
MAIL_HOST=your_host
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
```

### 5. SSL/HTTPS Setup

```bash
# Option 1: Let's Encrypt (Free)
sudo certbot --apache -d yourdomain.com

# Option 2: cPanel
# Use cPanel SSL/TLS interface to install certificate

# Verify HTTPS redirect in .htaccess:
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### 6. Queue Workers

```bash
# Start queue worker
php artisan queue:work --daemon

# Or set up supervisor (recommended)
# /etc/supervisor/conf.d/laravel-worker.conf:
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

### 7. Cron Jobs

```bash
# Add to crontab:
* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
```

### 8. Post-Deployment Testing

```bash
# Test these critical features:
□ User registration
□ User login
□ Course enrollment
□ Payment processing (Stripe, PayPal, Authorize.Net)
□ Course player
□ Certificate generation
□ State submissions (Florida, Missouri, Texas, Delaware, Nevada)
□ Admin dashboard
□ Email sending
```

---

## 🔒 SECURITY HARDENING

### File Permissions

```bash
# Directories
chmod 755 app/
chmod 755 bootstrap/
chmod 755 config/
chmod 755 database/
chmod 755 public/
chmod 755 resources/
chmod 755 routes/
chmod 755 storage/
chmod 755 vendor/

# Sensitive files
chmod 600 .env
chmod 644 .htaccess
chmod 644 composer.json

# Storage (writable)
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
```

### Apache Configuration

```apache
# In .htaccess or VirtualHost:
<IfModule mod_headers.c>
    # Security Headers
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
    Header set Referrer-Policy "strict-origin-when-cross-origin"
    
    # HSTS (uncomment after testing HTTPS)
    # Header set Strict-Transport-Security "max-age=31536000; includeSubDomains"
</IfModule>

# Disable directory listing
Options -Indexes

# Protect sensitive files
<FilesMatch "^\.env">
    Order allow,deny
    Deny from all
</FilesMatch>
```

---

## 🐛 TROUBLESHOOTING

### Issue: 500 Internal Server Error

```bash
# Check logs
tail -f storage/logs/laravel.log

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Check permissions
chmod -R 775 storage bootstrap/cache
```

### Issue: Database Connection Failed

```bash
# Verify .env settings
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Test connection
php artisan tinker
>>> DB::connection()->getPdo();
```

### Issue: Routes Not Working

```bash
# Clear route cache
php artisan route:clear

# Rebuild cache
php artisan route:cache

# Check .htaccess exists in public/
```

### Issue: Assets Not Loading

```bash
# Build assets
npm install
npm run build

# Check public/ directory permissions
chmod 755 public/
chmod 644 public/index.php
```

### Issue: Emails Not Sending

```bash
# Test email configuration
php artisan tinker
>>> Mail::raw('Test', function($msg) { $msg->to('test@example.com')->subject('Test'); });

# Check mail logs
tail -f storage/logs/laravel.log
```

---

## 📊 MONITORING

### Log Files

```bash
# Application logs
tail -f storage/logs/laravel.log

# Web server logs
tail -f /var/log/apache2/error.log
tail -f /var/log/apache2/access.log

# Queue worker logs
tail -f storage/logs/worker.log
```

### Performance Monitoring

```bash
# Enable query logging (temporarily)
DB::enableQueryLog();
// ... your code ...
dd(DB::getQueryLog());

# Monitor slow queries
# Add to config/database.php:
'mysql' => [
    'slow_query_log' => true,
    'slow_query_time' => 2, // seconds
],
```

---

## 🔄 MAINTENANCE MODE

```bash
# Enable maintenance mode
php artisan down --message="Upgrading system" --retry=60

# Deploy updates
git pull
composer install --no-dev
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Disable maintenance mode
php artisan up
```

---

## 📦 BACKUP STRATEGY

### Database Backup

```bash
# Manual backup
mysqldump -u username -p database_name > backup_$(date +%Y%m%d).sql

# Automated daily backup (cron)
0 2 * * * mysqldump -u username -p'password' database_name | gzip > /backups/db_$(date +\%Y\%m\%d).sql.gz
```

### File Backup

```bash
# Backup storage directory
tar -czf storage_backup_$(date +%Y%m%d).tar.gz storage/

# Full application backup
tar -czf app_backup_$(date +%Y%m%d).tar.gz \
    --exclude='node_modules' \
    --exclude='vendor' \
    --exclude='storage/logs' \
    /path/to/app/
```

---

## 🎯 PERFORMANCE OPTIMIZATION

### Caching

```bash
# Cache everything
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Use Redis (if available)
# In .env:
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

### Database Optimization

```bash
# Optimize tables
php artisan db:optimize

# Index frequently queried columns
# Add indexes in migrations
```

### Asset Optimization

```bash
# Minify assets
npm run build

# Enable Gzip compression in .htaccess:
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
</IfModule>
```

---

## 📞 EMERGENCY CONTACTS

### Critical Issues
- Database down: Check DB credentials, restart MySQL
- Site down: Check Apache/Nginx, check .htaccess
- Payment failing: Check gateway credentials, check logs
- Emails not sending: Check SMTP settings, check queue

### Quick Fixes

```bash
# Restart everything
sudo systemctl restart apache2
sudo systemctl restart mysql
php artisan queue:restart

# Clear everything
php artisan optimize:clear
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Rebuild everything
composer dump-autoload
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## ✅ FINAL CHECKLIST

### Before Going Live
- [ ] Strong database password set
- [ ] All admin routes protected
- [ ] SSL certificate installed
- [ ] HTTPS redirect working
- [ ] All tests passing
- [ ] Backups configured
- [ ] Monitoring set up
- [ ] Error tracking enabled
- [ ] Queue workers running
- [ ] Cron jobs configured
- [ ] Email sending tested
- [ ] Payment gateways tested
- [ ] State submissions tested
- [ ] Performance optimized
- [ ] Security headers set

### After Going Live
- [ ] Monitor error logs (first 24 hours)
- [ ] Test all critical flows
- [ ] Monitor performance
- [ ] Check backup success
- [ ] Verify email delivery
- [ ] Test payment processing
- [ ] Monitor queue processing
- [ ] Check certificate generation
- [ ] Verify state submissions

---

**Last Updated:** December 4, 2025  
**Platform:** Laravel 12.34.0  
**PHP Version:** 8.4.15  
**Status:** Production Ready (with 3 remaining fixes)
