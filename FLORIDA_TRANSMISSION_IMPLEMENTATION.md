# Florida State Transmission Management System - Implementation Guide

## Overview

This system automatically manages course completion transmissions to Florida DICDS, including error handling, retry logic, and admin management interface.

## Architecture

### Components Created

1. **Database Layer**
   - `state_transmissions` table - Stores transmission records
   - `transmission_error_codes` table - Error code lookup and user-friendly messages
   - `StateTransmission` model with relationships
   - `TransmissionErrorCode` model

2. **Event-Driven System**
   - `CourseCompleted` event (existing)
   - `CreateStateTransmission` listener - Creates transmission records on course completion
   - Extensible for multiple states

3. **Job Queue System**
   - `SendFloridaTransmissionJob` - Handles API transmission with retry logic
   - Automatic retry with exponential backoff (1min, 5min, 15min)
   - Admin notifications on repeated failures

4. **Admin Interface**
   - `FlTransmissionController` - Manage transmissions
   - Admin views for pending, error, and successful transmissions
   - Manual send, retry, and bulk operations

5. **Scheduled Tasks**
   - Nightly automatic transmission of pending records
   - Optional cleanup of old successful transmissions

6. **Notifications**
   - `RepeatedTransmissionFailure` - Alerts admins after 3+ failures

## Installation Steps

### 1. Run Migrations

```bash
php artisan migrate
```

This creates:
- `state_transmissions` table
- `transmission_error_codes` table

### 2. Seed Error Codes

```bash
php artisan db:seed --class=TransmissionErrorCodeSeeder
```

### 3. Configure Environment

Add to `.env`:

```env
# Queue Configuration
QUEUE_CONNECTION=database

# Florida API Configuration
FLORIDA_API_URL=https://api.flhsmv.gov/dicds/transmissions
FLORIDA_API_KEY=your_api_key_here
FLORIDA_USERNAME=your_username_here
FLORIDA_PASSWORD=your_password_here
FLORIDA_SCHOOL_ID=your_school_id_here
FLORIDA_API_TIMEOUT=30
```

### 4. Start Queue Worker

```bash
# Development
php artisan queue:work

# Production (use Supervisor)
php artisan queue:work --tries=3 --timeout=90
```

### 5. Set Up Scheduled Tasks

Add to crontab:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

The system will automatically send pending transmissions daily at 2:00 AM.

## Usage

### Automatic Transmission

When a student completes a course:

1. `CourseCompleted` event is fired
2. `CreateStateTransmission` listener checks if state requires reporting
3. Creates a `StateTransmission` record with status 'pending'
4. Dispatches `SendFloridaTransmissionJob` to queue
5. Job validates data, sends to Florida API, updates status

### Manual Transmission Management

Access admin interface at: `/admin/fl-transmissions`

**Features:**
- View pending, error, and successful transmissions
- Send individual transmissions
- Send all pending transmissions in bulk
- Retry failed transmissions
- View detailed transmission information
- Edit student data if validation fails

### Command Line Operations

```bash
# Send all pending transmissions
php artisan transmissions:send-pending --state=FL

# Dry run (preview without sending)
php artisan transmissions:send-pending --state=FL --dry-run

# Limit number of transmissions
php artisan transmissions:send-pending --state=FL --limit=50

# View failed queue jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Monitor queue
php artisan queue:monitor database --max=100
```

## Error Handling

### Error Code System

The system includes a comprehensive error code lookup table with:
- Technical error messages
- User-friendly explanations
- Resolution steps
- Retryable flag

### Common Errors and Solutions

**VALIDATION_ERROR**
- Missing required fields (driver license, citation, court case)
- Solution: Edit enrollment record, add missing data, retry

**401 - Authentication Failed**
- Invalid API credentials
- Solution: Verify `.env` credentials, contact Florida DHSMV

**422 - Validation Failed**
- Invalid driver license or citation number
- Solution: Verify with student, update enrollment, retry

**500/502/503 - Server Errors**
- Florida API temporarily unavailable
- Solution: System will retry automatically

**DUPLICATE_SUBMISSION**
- Already reported to Florida
- Solution: No action needed, mark as resolved

### Retry Logic

- Automatic retry: 3 attempts with exponential backoff
- Backoff intervals: 1 minute, 5 minutes, 15 minutes
- Admin notification after 3 failures
- Manual retry available in admin interface

## Monitoring

### Admin Dashboard

Statistics cards show:
- Pending transmissions count
- Error transmissions count
- Successful transmissions count
- Total transmissions

### Logs

All transmission attempts are logged:

```bash
# View transmission logs
tail -f storage/logs/laravel.log | grep "Florida"

# View specific transmission
php artisan tinker
>>> StateTransmission::with('enrollment.user')->find(123)
```

### Notifications

Admins receive email notifications when:
- A transmission fails 3+ times
- Includes transmission details, error info, and resolution link

## Extending to Other States

To add support for Missouri, Texas, or Delaware:

### 1. Create State-Specific Job

```php
// app/Jobs/SendMissouriTransmissionJob.php
class SendMissouriTransmissionJob implements ShouldQueue
{
    // Similar structure to SendFloridaTransmissionJob
    // Customize payload and API endpoint for Missouri
}
```

### 2. Update Listener

```php
// app/Listeners/CreateStateTransmission.php
protected const REPORTABLE_STATES = [
    'FL' => SendFloridaTransmissionJob::class,
    'MO' => SendMissouriTransmissionJob::class,
    'TX' => SendTexasTransmissionJob::class,
];
```

### 3. Add State Configuration

```php
// config/services.php
'missouri' => [
    'api_url' => env('MISSOURI_API_URL'),
    'api_key' => env('MISSOURI_API_KEY'),
    // ...
],
```

### 4. Seed Error Codes

Add Missouri-specific error codes to `TransmissionErrorCodeSeeder`.

## Testing

### Unit Tests

```php
// tests/Unit/SendFloridaTransmissionJobTest.php
public function test_successful_transmission()
{
    Http::fake([
        'api.flhsmv.gov/*' => Http::response(['success' => true], 200)
    ]);

    $transmission = StateTransmission::factory()->pending()->create();
    
    $job = new SendFloridaTransmissionJob($transmission->id);
    $job->handle();

    $this->assertEquals('success', $transmission->fresh()->status);
}
```

### Manual Testing

```bash
# Create test transmission
php artisan tinker
>>> $enrollment = UserCourseEnrollment::where('completed_at', '!=', null)->first()
>>> $transmission = StateTransmission::create([
...     'enrollment_id' => $enrollment->id,
...     'state' => 'FL',
...     'status' => 'pending'
... ])
>>> SendFloridaTransmissionJob::dispatch($transmission->id)

# Check status
>>> $transmission->fresh()->status
```

## Production Deployment

### Pre-Deployment Checklist

- [ ] Update `.env` with production API credentials
- [ ] Test with sandbox API first
- [ ] Run migrations: `php artisan migrate --force`
- [ ] Seed error codes: `php artisan db:seed --class=TransmissionErrorCodeSeeder`
- [ ] Cache config: `php artisan config:cache`
- [ ] Set up Supervisor for queue workers
- [ ] Configure cron for scheduled tasks
- [ ] Test with a single transmission
- [ ] Monitor logs for first 24 hours

### Supervisor Configuration

```ini
[program:laravel-queue-transmissions]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --queue=default --tries=3 --timeout=90
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/storage/logs/queue-worker.log
stopwaitsecs=3600
```

### Monitoring Setup

1. Set up log monitoring (e.g., Papertrail, Loggly)
2. Configure error alerting
3. Monitor queue depth: `php artisan queue:monitor database --max=100`
4. Set up uptime monitoring for admin interface

## Troubleshooting

### Queue Not Processing

```bash
# Check queue worker is running
ps aux | grep "queue:work"

# Restart queue workers
php artisan queue:restart

# Check failed jobs
php artisan queue:failed
```

### Transmissions Stuck in Pending

```bash
# Manually process pending
php artisan transmissions:send-pending --state=FL

# Check for errors in logs
tail -f storage/logs/laravel.log
```

### API Connection Issues

```bash
# Test API connectivity
php artisan tinker
>>> Http::get(config('services.florida.api_url'))

# Verify credentials
>>> config('services.florida.api_key')
```

## Support

For issues or questions:
1. Check logs: `storage/logs/laravel.log`
2. Review error codes: `/admin/fl-transmissions`
3. Contact Florida DHSMV technical support for API issues
4. Review this documentation

## Maintenance

### Regular Tasks

- Monitor failed transmissions weekly
- Review error patterns monthly
- Update error codes as needed
- Clean up old successful transmissions (optional)
- Review and optimize queue performance

### Database Maintenance

```bash
# Clean up old successful transmissions (older than 90 days)
php artisan tinker
>>> StateTransmission::where('status', 'success')
...     ->where('sent_at', '<', now()->subDays(90))
...     ->delete()
```

## Security Considerations

- API credentials stored in `.env` (never commit)
- HTTPS required for all API calls
- Admin interface restricted to super_admin and school_admin roles
- Sensitive data sanitized in logs
- Rate limiting on admin actions
- CSRF protection on all forms

## Performance Optimization

- Queue workers run in background
- Batch processing for bulk operations
- Database indexes on frequently queried columns
- Pagination on admin views
- Caching of configuration in production
- Exponential backoff prevents API overload
