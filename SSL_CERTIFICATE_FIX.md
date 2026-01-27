# SSL Certificate Fix for Windows

## 🚨 **Immediate SSL Certificate Fix**

The error `cURL error 60: SSL certificate problem: unable to get local issuer certificate` is common on Windows. Here are the solutions:

### **Option 1: Download CA Certificate Bundle (Recommended)**

1. **Download the CA certificate bundle:**
   ```powershell
   # Create directory for certificates
   mkdir C:\php\extras\ssl -Force
   
   # Download the latest CA bundle from curl.se
   Invoke-WebRequest -Uri "https://curl.se/ca/cacert.pem" -OutFile "C:\php\extras\ssl\cacert.pem"
   ```

2. **Update your `php.ini` file:**
   - Find your `php.ini` file: `php --ini`
   - Add this line:
   ```ini
   curl.cainfo = "C:\php\extras\ssl\cacert.pem"
   openssl.cafile = "C:\php\extras\ssl\cacert.pem"
   ```

3. **Restart your web server/PHP**

### **Option 2: Temporary Fix for Development**

Add this to your `.env` file temporarily:
```env
# TEMPORARY SSL FIX - DO NOT USE IN PRODUCTION
CURL_VERIFY_SSL=false
HTTP_VERIFY_SSL=false
```

### **Option 3: Laravel HTTP Client Configuration**

Create a config file to disable SSL verification for development:

**File: `config/http.php`**
```php
<?php

return [
    'verify' => env('HTTP_VERIFY_SSL', true),
    'timeout' => 30,
    'connect_timeout' => 10,
];
```

Then in your services, use:
```php
Http::withOptions(['verify' => config('http.verify')])
    ->get('https://example.com');
```

### **Option 4: Windows Certificate Store Update**

```powershell
# Update Windows certificate store
certlm.msc
# Or run Windows Update to get latest certificates
```

## 🔧 **Quick Test After Fix**

Run this to test if SSL is working:
```bash
php artisan states:test-all
```

The internet connectivity test should now pass.