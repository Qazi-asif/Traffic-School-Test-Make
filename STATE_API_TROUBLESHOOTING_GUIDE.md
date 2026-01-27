# State API Troubleshooting Guide

## 🔍 **Quick Diagnosis**

Run this command to get a comprehensive overview:
```bash
php artisan states:test-all
```

## 🛠️ **Step-by-Step Environment Checks**

### **1. Test DNS Resolution (PowerShell)**
```powershell
# Test each hostname
nslookup services.flhsmv.gov
nslookup xsg.dmv.ca.gov  
nslookup secure.ntsa.us
nslookup testingprovider.com

# Alternative method
Resolve-DnsName services.flhsmv.gov
Resolve-DnsName xsg.dmv.ca.gov
Resolve-DnsName secure.ntsa.us
Resolve-DnsName testingprovider.com
```

**Expected Results:**
- ✅ Should return IP addresses
- ❌ If "can't find" or timeout → DNS issue or domain doesn't exist

### **2. Test HTTP/HTTPS Connectivity**
```powershell
# Test HTTPS endpoints
Invoke-WebRequest -Uri "https://services.flhsmv.gov" -Method Head -TimeoutSec 10
Invoke-WebRequest -Uri "https://xsg.dmv.ca.gov" -Method Head -TimeoutSec 10

# Test HTTP endpoints  
Invoke-WebRequest -Uri "https://secure.ntsa.us" -Method Head -TimeoutSec 10
Invoke-WebRequest -Uri "http://testingprovider.com" -Method Head -TimeoutSec 10
```

**Expected Results:**
- ✅ Status 200, 301, 302, or 405 (Method Not Allowed)
- ❌ Connection timeout, DNS errors, or 404/500 errors

### **3. Test with cURL (if available)**
```bash
curl -I -m 10 "https://services.flhsmv.gov"
curl -I -m 10 "https://xsg.dmv.ca.gov/tvcc/tvccservice"
curl -I -m 10 "https://secure.ntsa.us/cgi-bin/register.cgi"
curl -I -m 10 "http://testingprovider.com/ccs/register.jsp"
```

### **4. Check Network Configuration**
```powershell
# Check DNS servers
Get-DnsClientServerAddress

# Check proxy settings
netsh winhttp show proxy

# Test basic connectivity
Test-NetConnection -ComputerName google.com -Port 443
Test-NetConnection -ComputerName 8.8.8.8 -Port 53
```

## 🔧 **Laravel/PHP Configuration Fixes**

### **1. Update Your .env File**
Add these configuration options to your `.env`:

```env
# Florida FLHSMV
FLORIDA_ENABLED=true
FLORIDA_MODE=live
FLORIDA_FALLBACK_ENABLED=true
FLORIDA_SIMULATE_SUCCESS=true

# California TVCC
CALIFORNIA_TVCC_ENABLED=true
CALIFORNIA_TVCC_MODE=live
CALIFORNIA_TVCC_FALLBACK_ENABLED=true
CALIFORNIA_TVCC_SIMULATE_SUCCESS=true

# Nevada NTSA
NEVADA_NTSA_ENABLED=true
NEVADA_NTSA_MODE=live
NEVADA_NTSA_FALLBACK_ENABLED=true
NEVADA_NTSA_SIMULATE_SUCCESS=true

# CCS
CCS_ENABLED=true
CCS_MODE=live
CCS_FALLBACK_ENABLED=true
CCS_SIMULATE_SUCCESS=true

# Development Settings
STATE_API_LOG_REQUESTS=true
STATE_API_LOG_RESPONSES=true
STATE_API_SIMULATE_DELAYS=false
STATE_API_FORCE_FALLBACK=false
```

### **2. Clear Laravel Caches**
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### **3. Test Individual States**
```bash
# Test each state individually
php artisan tinker
>>> $service = new App\Services\ImprovedFlhsmvSoapService();
>>> $result = $service->testConnection();
>>> dd($result);
```

## 🚨 **Common Error Solutions**

### **Error: "Could not resolve host"**
**Cause:** DNS resolution failure
**Solutions:**
1. Check if domain exists: `nslookup domain.com`
2. Try different DNS servers: `8.8.8.8` or `1.1.1.1`
3. Check if you're behind a corporate firewall
4. Verify the URL is correct (check for typos)

### **Error: "WSDL URL is not accessible"**
**Cause:** SOAP WSDL file cannot be downloaded
**Solutions:**
1. Test WSDL URL in browser: `https://services.flhsmv.gov/path/to/service.wsdl`
2. Check SSL certificate issues
3. Verify firewall/proxy settings
4. Enable fallback mode in `.env`

### **Error: "Connection timed out"**
**Cause:** Network timeout or server down
**Solutions:**
1. Increase timeout in config: `FLORIDA_TIMEOUT=60`
2. Check if server is down (try from different network)
3. Enable fallback mode
4. Contact API provider

### **Error: "SSL certificate problem"**
**Cause:** SSL/TLS certificate issues
**Solutions:**
1. Disable SSL verification for testing (not production):
   ```php
   'verify' => false
   ```
2. Update CA certificates on your system
3. Contact API provider about certificate issues

## 🔄 **Fallback Strategies**

### **1. Enable Mock Mode for Development**
```env
FLORIDA_MODE=mock
CALIFORNIA_TVCC_MODE=mock
NEVADA_NTSA_MODE=mock
CCS_MODE=mock
```

### **2. Enable Fallback Mode**
```env
FLORIDA_FALLBACK_ENABLED=true
CALIFORNIA_TVCC_FALLBACK_ENABLED=true
NEVADA_NTSA_FALLBACK_ENABLED=true
CCS_FALLBACK_ENABLED=true
```

### **3. Force Fallback for Testing**
```env
STATE_API_FORCE_FALLBACK=true
```

## 📞 **Production Readiness Checklist**

### **What to Ask Vendors/Clients:**

#### **Florida FLHSMV:**
- [ ] Current production WSDL URL
- [ ] Valid credentials (username, password, school ID, instructor ID)
- [ ] IP whitelisting requirements
- [ ] Alternative HTTP endpoint (if available)
- [ ] Support contact information

#### **California TVCC:**
- [ ] Current WSDL URL for TVCC service
- [ ] Valid user credentials
- [ ] Password rotation schedule
- [ ] Court code mappings
- [ ] Support contact information

#### **Nevada NTSA:**
- [ ] Verify if `secure.ntsa.us` is correct domain
- [ ] Current registration endpoint URL
- [ ] School name and test name registration
- [ ] Result callback URL requirements
- [ ] Support contact information

#### **CCS:**
- [ ] Verify if `testingprovider.com` is still active
- [ ] Current registration endpoint URL
- [ ] School name registration
- [ ] Result callback URL requirements
- [ ] Support contact information

### **Environment Separation:**
```env
# Development
FLORIDA_MODE=mock
STATE_API_FORCE_FALLBACK=true

# Staging
FLORIDA_MODE=live
FLORIDA_FALLBACK_ENABLED=true

# Production
FLORIDA_MODE=live
FLORIDA_FALLBACK_ENABLED=false
STATE_API_LOG_REQUESTS=false
STATE_API_LOG_RESPONSES=false
```

## 🔍 **Debugging Commands**

### **1. Test All APIs**
```bash
php artisan states:test-all
```

### **2. Check Laravel Logs**
```bash
tail -f storage/logs/laravel.log
```

### **3. Test Individual Service**
```bash
php artisan tinker
>>> $service = new App\Services\FlhsmvSoapService();
>>> $result = $service->testConnection();
>>> print_r($result);
```

### **4. Test Mock Responses**
```bash
php artisan tinker
>>> use App\Services\StateApiMockService;
>>> $mock = StateApiMockService::getMockResponse('florida', 'success');
>>> print_r($mock);
```

## 🚀 **Quick Fixes for Immediate Stability**

### **1. Enable All Fallbacks (Emergency Mode)**
```env
STATE_API_FORCE_FALLBACK=true
FLORIDA_SIMULATE_SUCCESS=true
CALIFORNIA_TVCC_SIMULATE_SUCCESS=true
NEVADA_NTSA_SIMULATE_SUCCESS=true
CCS_SIMULATE_SUCCESS=true
```

### **2. Switch to Mock Mode**
```env
FLORIDA_MODE=mock
CALIFORNIA_TVCC_MODE=mock
NEVADA_NTSA_MODE=mock
CCS_MODE=mock
```

### **3. Disable Problematic States**
```env
CALIFORNIA_TVCC_ENABLED=false
NEVADA_NTSA_ENABLED=false
CCS_ENABLED=false
```

## 📋 **OneDrive Considerations**

Since your project is in OneDrive (`C:\Users\lenovo\OneDrive\Desktop\elearning.wolkeconsultancy.website`):

1. **Sync Issues:** OneDrive sync might interfere with file operations
2. **Path Length:** Windows has path length limitations
3. **File Locking:** OneDrive might lock files during sync

**Solutions:**
- Pause OneDrive sync during development
- Consider moving project to `C:\dev\` or similar
- Exclude `vendor/`, `node_modules/`, and `storage/` from OneDrive sync

## 🆘 **Emergency Contacts**

When all else fails, contact:
- **Florida FLHSMV:** [Contact information needed]
- **California DMV:** [Contact information needed]  
- **Nevada NTSA:** [Contact information needed]
- **CCS Provider:** [Contact information needed]

## 📝 **Next Steps After Fixing**

1. Document working configurations
2. Set up monitoring/alerts for API failures
3. Create runbooks for common issues
4. Schedule regular API health checks
5. Plan for credential rotation