# HTTP SOAP Commands Reference

## Available Commands

### 1. General HTTP SOAP Testing
```bash
# Test all HTTP SOAP services (Florida, California)
php artisan soap:test-http

# Test specific service
php artisan soap:test-http florida
php artisan soap:test-http california

# Show detailed output
php artisan soap:test-http --detailed
```

### 2. Florida Production Testing
```bash
# Test Florida HTTP SOAP with real production data (dry run)
php artisan florida:test-http-real --dry-run

# Test Florida HTTP SOAP with real production data (actual call)
php artisan florida:test-http-real
```

### 3. Service Comparison
```bash
# Compare SOAP vs HTTP SOAP services (dry run)
php artisan florida:compare-services --dry-run

# Compare SOAP vs HTTP SOAP services (actual calls)
php artisan florida:compare-services
```

### 4. System Check
```bash
# Check SOAP extension availability and recommendations
php check-soap-support.php
```

## Command Details

### `soap:test-http`
- **Purpose**: Test HTTP SOAP connectivity to state APIs
- **Benefits**: Works without SOAP extension, cPanel compatible
- **Output**: Connection status, endpoint accessibility, error details

### `florida:test-http-real`
- **Purpose**: Test Florida FLHSMV API with real production data using HTTP SOAP
- **Benefits**: Same as original `florida:test-real` but works without SOAP extension
- **Safety**: Includes confirmation prompts before making real API calls
- **Output**: Detailed response analysis, error code mapping

### `florida:compare-services`
- **Purpose**: Side-by-side comparison of Native SOAP vs HTTP SOAP
- **Benefits**: Performance analysis, compatibility testing
- **Output**: Response time comparison, success rates, recommendations

### `check-soap-support.php`
- **Purpose**: Quick system check for SOAP extension availability
- **Benefits**: Provides specific recommendations based on your environment
- **Output**: Extension status, configuration suggestions

## Response Codes You Might See

### Success Responses
- **HTTP 200**: Service accessible and responding
- **SUCCESS**: Certificate submitted successfully
- **MOCK_SUCCESS**: Test mode response

### Common Error Responses
- **HTTP 405**: Method not allowed (normal for GET requests to SOAP endpoints)
- **HTTP 500**: Server error (check credentials and data format)
- **Site unavailable**: Maintenance page (normal during testing)
- **Access Denied**: IP restrictions or invalid credentials

### Florida-Specific Error Codes
- **VL000**: Login failed - invalid credentials
- **DV100**: Citation number incorrect length (must be 7 characters)
- **CF032**: Invalid Florida driver license format
- **ST000-ST005**: Missing required student data

## Usage Examples

### Basic Testing (Safe)
```bash
# Check if HTTP SOAP works on your system
php artisan soap:test-http

# See what would be sent to Florida API
php artisan florida:test-http-real --dry-run
```

### Production Testing (Use with caution)
```bash
# Test with real Florida credentials
php artisan florida:test-http-real

# Compare both services with real data
php artisan florida:compare-services
```

### Troubleshooting
```bash
# Check system compatibility
php check-soap-support.php

# Test specific service with details
php artisan soap:test-http florida --detailed

# View logs
tail -f storage/logs/laravel.log | grep "HTTP SOAP"
```

## Benefits of HTTP SOAP Commands

### ✅ **cPanel Compatible**
- No SOAP extension required
- Works on any shared hosting
- No server configuration needed

### ✅ **Same Functionality**
- Identical API calls to native SOAP
- All error codes preserved
- Same response format

### ✅ **Enhanced Features**
- Better error handling
- Built-in retry logic
- SSL certificate issue handling
- Detailed logging

### ✅ **Easy Testing**
- Dry-run modes for safety
- Detailed output formatting
- Performance comparison
- System compatibility checks

## Deployment Workflow

1. **Development**: Test with `--dry-run` flags
2. **Staging**: Use comparison commands to verify both services
3. **Production**: Deploy with HTTP SOAP enabled
4. **Monitoring**: Use test commands to verify connectivity

This ensures your traffic school platform works reliably on any hosting environment!