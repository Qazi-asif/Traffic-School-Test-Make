# Missouri Form 4444 - Deployment Checklist

## ✅ **Files to Upload to Hosting**

### **New Files (Upload these)**
1. `app/Services/MissouriForm4444PdfService.php`
2. `app/Listeners/GenerateMissouriForm4444.php`
3. `resources/views/certificates/missouri-form-4444.blade.php`
4. `resources/views/emails/missouri-form-4444.blade.php`
5. `resources/views/admin/missouri-forms.blade.php`
6. `public/test-missouri-system.php` (for testing)

### **Updated Files (Replace these)**
1. `routes/web.php` (with Missouri routes added)
2. `app/Providers/EventServiceProvider.php` (with listener registered)
3. `app/Http/Controllers/MissouriController.php` (updated with PDF service)

## 🔧 **Post-Upload Steps**

### **1. Clear Cache**
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### **2. Create Storage Directory**
Ensure this directory exists and is writable:
```
storage/app/certificates/missouri/
```

### **3. Test the System**
Visit: `http://your-domain.com/test-missouri-system.php`

This will check:
- ✅ All files exist
- ✅ Database tables are present
- ✅ Classes load correctly
- ✅ Routes are registered

### **4. Configure Email (Optional)**
Update `.env` for automatic email sending:
```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@dummiestrafficschool.com
MAIL_FROM_NAME="Dummies Traffic School"
```

## 🎯 **How to Use**

### **For Students (Automatic)**
1. Student completes Missouri course
2. Form 4444 is automatically generated
3. PDF is emailed to student
4. Student downloads and submits according to instructions

### **For Admins**
1. Visit `/admin/missouri-forms`
2. View all Form 4444s
3. Track expiration dates
4. Resend forms via email
5. Mark forms as submitted

## 🔗 **Key URLs**

- **Admin Interface**: `/admin/missouri-forms`
- **Download Form**: `/missouri/form4444/{formId}/download`
- **System Test**: `/test-missouri-system.php`

## ⚠️ **Troubleshooting**

### **Common Issues:**

1. **"Class not found" errors**
   - Run `composer dump-autoload`
   - Clear cache with artisan commands

2. **"Storage directory not writable"**
   - Set permissions: `chmod 755 storage/app/certificates/missouri/`

3. **"Route not found"**
   - Verify routes are added to `web.php`
   - Run `php artisan route:clear`

4. **"Email not sending"**
   - Check `.env` email configuration
   - Test with a simple email first

## 🎉 **Success Indicators**

✅ Test page shows all green checkmarks
✅ Admin interface loads at `/admin/missouri-forms`
✅ Form 4444 generates when Missouri course completed
✅ PDF downloads correctly
✅ Email sends with PDF attachment

## 📞 **Support**

If you encounter issues:
1. Check the test page first
2. Review Laravel logs: `storage/logs/laravel.log`
3. Verify all files were uploaded correctly
4. Ensure database tables exist