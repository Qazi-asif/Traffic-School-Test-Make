# Florida API Configuration Guide

## Environment Variables

Add these variables to your `.env` file:

```env
# Florida State Integration - REST API for Transmissions
FLORIDA_API_URL=https://api.flhsmv.gov/dicds/transmissions
FLORIDA_API_KEY=your_api_key_here
FLORIDA_USERNAME=your_username_here
FLORIDA_PASSWORD=your_password_here
FLORIDA_SCHOOL_ID=your_school_id_here
FLORIDA_API_TIMEOUT=30
```

## Configuration Access in Code

The configuration is stored in `config/services.php` and can be accessed using:

```php
// Get API URL
$apiUrl = config('services.florida.api_url');

// Get API credentials
$apiKey = config('services.florida.api_key');
$username = config('services.florida.username');
$password = config('services.florida.password');
$schoolId = config('services.florida.school_id');
```

## Security Best Practices

### 1. Never Commit Credentials

Ensure `.env` is in `.gitignore`:
```
.env
.env.backup
.env.production
```

### 2. Use Environment-Specific Files

- `.env` - Local development
- `.env.testing` - Testing environment
- `.env.production` - Production (never commit this)

### 3. Encrypt Sensitive Variables (Production)

For production, consider using Laravel's encrypted environment:

```bash
# Encrypt environment file
php artisan env:encrypt --env=production

# Decrypt when deploying
php artisan env:decrypt --env=production --key=your-encryption-key
```

### 4. Config Caching

In production, cache configuration for better performance:

```bash
# Cache configuration
php artisan config:cache

# Clear cache after changes
php artisan config:clear
```

## Logging Best Practices

### Request/Response Logging

The `SendFloridaTransmissionJob` logs all API interactions:

```php
// Before sending
Log::info("Sending Florida transmission", [
    'transmission_id' => $this->transmissionId,
    'url' => $url,
    'payload' => $payload, // Be careful with sensitive data
]);

// After receiving response
Log::info("Florida API response", [
    'transmission_id' => $this->transmissionId,
    'status_code' => $statusCode,
    'response' => $body,
]);
```

### Log Channels

Configure separate log channels in `config/logging.php`:

```php
'channels' => [
    'florida_api' => [
        'driver' => 'daily',
        'path' => storage_path('logs/florida-api.log'),
        'level' => 'debug',
        'days' => 30,
    ],
],
```

Use in code:
```php
Log::channel('florida_api')->info('Transmission sent', $data);
```

### Sanitize Sensitive Data

Never log full credentials or sensitive personal information:

```php
// BAD - Don't do this
Log::info('API call', ['password' => $password]);

// GOOD - Sanitize sensitive data
Log::info('API call', [
    'url' => $url,
    'user' => Str::mask($username, '*', 3),
    'payload' => Arr::except($payload, ['ssn', 'full_license_number'])
]);
```

## Testing Configuration

### Local Testing

Use sandbox/test credentials in `.env`:

```env
FLORIDA_API_URL=https://sandbox-api.flhsmv.gov/dicds/transmissions
FLORIDA_API_KEY=test_key_123
FLORIDA_USERNAME=test_user
FLORIDA_PASSWORD=test_pass
FLORIDA_SCHOOL_ID=TEST001
```

### Unit Testing

Mock the HTTP client in tests:

```php
use Illuminate\Support\Facades\Http;

Http::fake([
    'api.flhsmv.gov/*' => Http::response([
        'success' => true,
        'message' => 'Transmission received'
    ], 200)
]);
```

## Production Deployment Checklist

- [ ] Update `.env` with production credentials
- [ ] Verify API endpoint URL (remove sandbox)
- [ ] Test with a single transmission first
- [ ] Set up monitoring and alerts
- [ ] Configure log rotation
- [ ] Cache configuration: `php artisan config:cache`
- [ ] Set up queue workers with Supervisor
- [ ] Enable HTTPS for all API calls
- [ ] Set appropriate timeout values
- [ ] Configure retry logic and backoff

## Monitoring

### Check API Health

Create a health check command:

```bash
php artisan florida:test-connection
```

### Monitor Failed Jobs

```bash
# View failed jobs
php artisan queue:failed

# Check specific transmission
php artisan tinker
>>> StateTransmission::find(123)
```

### Log Analysis

```bash
# View recent Florida API logs
tail -f storage/logs/laravel.log | grep "Florida"

# Count errors
grep "Florida transmission failed" storage/logs/laravel.log | wc -l
```

## Troubleshooting

### Connection Timeout

Increase timeout in `.env`:
```env
FLORIDA_API_TIMEOUT=60
```

### Authentication Errors

Verify credentials:
```bash
php artisan tinker
>>> config('services.florida.username')
>>> config('services.florida.api_key')
```

### SSL Certificate Issues

For development only, you can disable SSL verification (NOT for production):
```php
Http::withoutVerifying()->post($url, $payload);
```
