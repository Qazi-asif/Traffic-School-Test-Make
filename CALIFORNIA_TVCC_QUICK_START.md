# California TVCC Integration - Quick Start Guide

## Overview

The California TVCC (Traffic Violator Certificate Completion) integration allows automatic submission of course completion certificates to the California DMV. This system follows the same architecture as the Florida FLHSMV integration.

## Features

- ✅ Automatic certificate submission on course completion
- ✅ SOAP-based API integration with California DMV
- ✅ Retry logic with exponential backoff
- ✅ Admin interface for manual transmission/retry
- ✅ Response logging and error tracking
- ✅ Queue-based processing

## Configuration

### 1. Environment Variables

Add these to your `.env` file:

```env
# California TVCC Configuration
CA_TVCC_ENABLED=true
CA_TVCC_ENDPOINT=https://xsg.dmv.ca.gov/tvcc/tvccservice
CA_TVCC_USER_ID=Support@dummiestrafficschool.com
CA_TVCC_PASSWORD=your_password_here
CA_TVCC_VERIFY_SSL=true
```

### 2. Run Migrations

```bash
php artisan migrate
```

This creates the `california_certificates` table.

### 3. Configure Queue Worker

Ensure your queue worker is running:

```bash
php artisan queue:work
```

Or use Supervisor for production (see QUEUE_CONFIGURATION.md).

## How It Works

### Automatic Transmission Flow

1. **Course Completion**: When a student completes a California course, the `CourseCompleted` event is fired
2. **Listener**: `CreateStateTransmission` listener detects it's a CA course
3. **Transmission Record**: Creates a `StateTransmission` record with status 'pending'
4. **Job Dispatch**: Dispatches `SendCaliforniaTransmissionJob` to the queue
5. **API Call**: Job makes SOAP call to California DMV TVCC service
6. **Certificate Update**: On success, updates `CaliforniaCertificate` with response data
7. **Retry Logic**: On failure, retries up to 5 times with exponential backoff

### Data Flow

```
CourseCompleted Event
    ↓
CreateStateTransmission Listener
    ↓
StateTransmission (pending)
    ↓
SendCaliforniaTransmissionJob (queued)
    ↓
CaliforniaTvccService (SOAP API)
    ↓
California DMV TVCC
    ↓
Response Processing
    ↓
CaliforniaCertificate (updated)
    ↓
StateTransmission (success/error)
```

## Admin Interface

### Access Transmissions

Navigate to: `/admin/ca-transmissions`

### Features

- **View All Transmissions**: See pending, successful, and failed transmissions
- **Filter by Status**: Filter by pending, success, or error
- **View Details**: Click on any transmission to see full details
- **Retry Failed**: Manually retry failed transmissions
- **Bulk Actions**: Send all pending or retry all failed

### Transmission Statuses

- **Pending**: Waiting to be sent or in queue
- **Success**: Successfully submitted to California DMV
- **Error**: Failed after retries (requires manual intervention)

## API Request Format

The service sends the following data to California DMV:

```php
[
    'userDto' => [
        'userId' => 'Support@dummiestrafficschool.com',
        'password' => 'your_password'
    ],
    'ccDate' => '2025-12-09',              // Completion date (Y-m-d)
    'courtCd' => 'LAX001',                 // Court code
    'dateOfBirth' => '1990-01-15',         // Student DOB (Y-m-d)
    'dlNbr' => 'D1234567',                 // Driver license (CA only)
    'firstName' => 'John',
    'lastName' => 'Doe',
    'modality' => '4T',                    // Fixed: Online traffic school
    'refNbr' => 'ABC123456'                // Citation number
]
```

## Response Handling

### Success Response

```php
[
    'ccSeqNbr' => '123456789',      // Certificate sequence number
    'ccStatCd' => 'ACCEPTED',       // Status code
    'ccSubTstamp' => '2025-12-09T10:30:00'  // Submission timestamp
]
```

### Error Responses

Common error codes:
- `ACCESS_DENIED`: Invalid credentials
- `VALIDATION_FAILED`: Missing or invalid data
- `SERVICE_EXCEPTION`: DMV service error

## Testing

### Test Transmission Manually

1. Complete a California course
2. Go to `/admin/ca-transmissions`
3. Find the pending transmission
4. Click "View" to see details
5. Monitor the status

### Test with Artisan Command

```bash
# Send all pending California transmissions
php artisan transmissions:send CA
```

## Troubleshooting

### Transmission Stuck in Pending

**Check:**
- Queue worker is running: `php artisan queue:work`
- No errors in `storage/logs/laravel.log`
- Database connection is active

**Solution:**
```bash
# Restart queue worker
php artisan queue:restart
```

### Authentication Errors

**Check:**
- `CA_TVCC_USER_ID` is correct
- `CA_TVCC_PASSWORD` is correct
- Credentials are active with California DMV

### SSL Certificate Errors

**Temporary Fix (Development Only):**
```env
CA_TVCC_VERIFY_SSL=false
```

**Production Fix:**
- Update CA certificates on server
- Contact California DMV for certificate issues

### Missing Required Data

**Check:**
- Student has driver license number
- Student has birth date
- Enrollment has citation number
- Court code is mapped correctly

## Database Tables

### `california_certificates`

Stores California-specific certificate data:
- `certificate_number`: DMV-assigned certificate number
- `cc_seq_nbr`: TVCC sequence number
- `cc_stat_cd`: TVCC status code
- `court_code`: Court code for CTSI
- `status`: pending, sent, failed

### `state_transmissions`

Tracks all state transmissions (shared with Florida):
- `state`: 'CA' for California
- `status`: pending, success, error
- `retry_count`: Number of retry attempts
- `response_code`: API response code
- `response_message`: API response message

## Related Files

### Core Files
- `app/Services/CaliforniaTvccService.php` - SOAP API service
- `app/Jobs/SendCaliforniaTransmissionJob.php` - Queue job
- `app/Models/CaliforniaCertificate.php` - Certificate model
- `app/Http/Controllers/Admin/CaTransmissionController.php` - Admin controller

### Configuration
- `config/california.php` - California-specific config
- `.env` - Environment variables

### Views
- `resources/views/admin/ca-transmissions/index.blade.php` - List view
- `resources/views/admin/ca-transmissions/show.blade.php` - Detail view

### Routes
- `routes/web.php` - Admin routes (search for 'ca-transmissions')

## Production Checklist

- [ ] Configure correct California DMV credentials
- [ ] Set `CA_TVCC_ENABLED=true`
- [ ] Set `CA_TVCC_VERIFY_SSL=true`
- [ ] Run migrations
- [ ] Configure Supervisor for queue worker
- [ ] Test with a real course completion
- [ ] Monitor logs for first 24 hours
- [ ] Set up admin notifications for failures

## Support

For issues with:
- **Integration**: Check logs in `storage/logs/laravel.log`
- **California DMV API**: Contact California DMV support
- **System Configuration**: Review this guide and QUEUE_CONFIGURATION.md

## Next Steps

After California TVCC is working:
1. Implement California CTSI (Traffic School Interface)
2. Implement Nevada NTSA integration
3. Implement CCS (Court Compliance System)

See your old system's documentation for details on these integrations.
