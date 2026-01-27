# State Certificate Submission System

## Overview

Multi-state certificate submission system that automatically reports course completions to government agencies and third-party compliance systems. Designed for **synchronous execution** on cPanel hosting without queue workers.

## Supported Systems

| State | System | Type | Status |
|-------|--------|------|--------|
| Florida | FLHSMV/DICDS | SOAP/REST API | ✅ Implemented |
| California | TVCC | REST API | ✅ Implemented |
| California | CTSI | Callback Receiver | ✅ Implemented |
| Nevada | NTSA | Form POST | ✅ Implemented |
| Multi-State | CCS | Form POST | ✅ Implemented |

## Installation

### 1. Run Migrations
```bash
php artisan migrate
```

### 2. Configure Environment
```bash
# Copy example and edit
cp .env.example .env

# Add these settings
STATE_TRANSMISSION_SYNC=true
CALIFORNIA_TVCC_ENABLED=true
NEVADA_NTSA_ENABLED=true
CCS_ENABLED=true
```

### 3. Set Passwords
```bash
# California TVCC password
php artisan tvcc:password
```

### 4. Configure Courses
```bash
php artisan tinker
$course = Course::find(1);
$course->update(['tvcc_enabled' => true]);
```

### 5. Configure Courts
```bash
php artisan tinker
$court = Court::find(1);
$court->update(['tvcc_court_code' => 'LA001']);
```

## Usage

### Automatic Submission

Transmissions are created automatically when a student completes a course. The system checks:
- Course state (FL = Florida FLHSMV)
- Course flags (`tvcc_enabled`, `ntsa_enabled`, `ccs_enabled`)
- System configuration (enabled in `.env`)

### Admin Dashboard

Access the unified dashboard at:
```
/admin/state-transmissions
```

Features:
- View all transmissions
- Filter by state/system/status
- Manual send/retry
- Statistics dashboard

### Manual Transmission

```php
use App\Services\CaliforniaTvccService;
use App\Models\StateTransmission;

$transmission = StateTransmission::find(1);
$service = new CaliforniaTvccService();
$success = $service->sendTransmission($transmission);
```

## API Callbacks

External systems send results to these public endpoints:

### California CTSI
```
POST /api/ctsi/result
Content-Type: application/xml
```

### Nevada NTSA
```
POST /api/ntsa/result
Content-Type: application/x-www-form-urlencoded
```

### CCS
```
POST /api/ccs/result
Content-Type: application/x-www-form-urlencoded
```

## Configuration

### Environment Variables

```env
# Execution Mode
STATE_TRANSMISSION_SYNC=true  # true = synchronous, false = queued

# California TVCC
CALIFORNIA_TVCC_ENABLED=true
CALIFORNIA_TVCC_URL=https://xsg.dmv.ca.gov/tvcc/tvccservice
CALIFORNIA_TVCC_USER=Support@dummiestrafficschool.com

# California CTSI
CALIFORNIA_CTSI_ENABLED=true

# Nevada NTSA
NEVADA_NTSA_ENABLED=true
NEVADA_NTSA_URL=https://secure.ntsa.us/cgi-bin/register.cgi
NEVADA_NTSA_SCHOOL_NAME="DUMMIES TRAFFIC SCHOOL.COM"
NEVADA_NTSA_TEST_NAME="DUMMIES TRAFFIC SCHOOL.COM - CA"

# CCS
CCS_ENABLED=true
CCS_URL=http://testingprovider.com/ccs/register.jsp
CCS_SCHOOL_NAME=dummiests
CCS_RESULT_URL=https://yourdomain.com/api/ccs/result
```

### Course Flags

Enable systems per course:
```php
$course->tvcc_enabled  // California TVCC
$course->ctsi_enabled  // California CTSI
$course->ntsa_enabled  // Nevada NTSA
$course->ccs_enabled   // CCS
```

### Court Mappings

Configure court codes:
```php
$court->tvcc_court_code    // California TVCC
$court->ctsi_court_id      // California CTSI
$court->ntsa_court_name    // Nevada NTSA
```

## Architecture

### Synchronous Execution

When `STATE_TRANSMISSION_SYNC=true`:
1. Course completion triggers event
2. Listener creates transmission records
3. Services execute immediately (same request)
4. Status updated before response

No queue workers needed!

### Service Pattern

Each integration has a dedicated service:
- `CaliforniaTvccService` - TVCC API integration
- `NevadaNtsaService` - NTSA form submission
- `CcsService` - CCS form submission

All follow the same pattern:
1. Validate required fields
2. Build payload
3. Call external API
4. Handle response
5. Update transmission record

## Database Schema

### state_transmissions
- `system` - System identifier (TVCC, NTSA, CCS, FLHSMV, CTSI)
- `state` - State code (CA, NV, FL)
- `status` - pending/success/error
- `payload_json` - Request data
- `response_code` - API response code
- `response_message` - API response message

### tvcc_passwords
- `password` - Current TVCC password (plain text)
- `updated_at` - Last update timestamp

### courses
- `tvcc_enabled` - Enable California TVCC
- `ctsi_enabled` - Enable California CTSI
- `ntsa_enabled` - Enable Nevada NTSA
- `ccs_enabled` - Enable CCS

### courts
- `tvcc_court_code` - TVCC court code
- `ctsi_court_id` - CTSI court identifier
- `ntsa_court_name` - NTSA court name

## Testing

### Test TVCC
```bash
php artisan tinker

$enrollment = UserCourseEnrollment::first();
$transmission = StateTransmission::create([
    'enrollment_id' => $enrollment->id,
    'state' => 'CA',
    'system' => 'TVCC',
    'status' => 'pending',
]);

$service = new App\Services\CaliforniaTvccService();
$service->sendTransmission($transmission);
```

### Test NTSA
```bash
php artisan tinker

$service = new App\Services\NevadaNtsaService();
$transmission = StateTransmission::where('system', 'NTSA')->first();
$service->sendTransmission($transmission);
```

### Test Callback
```bash
curl -X POST http://localhost/api/ntsa/result \
  -d "UniqueID=123&percentage=95&testDate=2025-12-09&certificateSentDate=2025-12-09"
```

## Troubleshooting

### Transmissions Not Sending
1. Check `.env` configuration
2. Verify course flags are enabled
3. Check logs: `storage/logs/laravel.log`
4. Verify court mappings exist

### TVCC Authentication Errors
1. Check password: `php artisan tinker` → `TvccPassword::current()`
2. Verify user ID in `.env`
3. Test API endpoint accessibility

### Callbacks Not Received
1. Verify URLs are publicly accessible
2. Check firewall/security settings
3. Review logs for incoming requests

## Commands

### Check Configuration Status
```bash
php artisan state:check
```
Verifies migrations, configuration, passwords, and current status.

### Update TVCC Password
```bash
php artisan tvcc:password
```

### View Pending Transmissions
```bash
php artisan tinker
StateTransmission::where('status', 'pending')->count()
```

## Documentation

- **Quick Start**: `STATE_INTEGRATIONS_QUICK_START.md`
- **Implementation Details**: `STATE_INTEGRATIONS_IMPLEMENTATION_COMPLETE.md`
- **Summary**: `STATE_INTEGRATIONS_SUMMARY.md`
- **Technical Specs**: `.kiro/steering/state-integrations.md`

## Support

For issues:
1. Check logs in `storage/logs/laravel.log`
2. Review transmission details in admin dashboard
3. Verify API credentials and endpoints
4. Test connectivity to external APIs

## Security

- TVCC password stored as plain text (consider encryption for production)
- Callback endpoints are public (no auth by design)
- All transmissions logged for audit
- Admin dashboard requires authentication

## License

Proprietary - Part of Traffic School Platform
