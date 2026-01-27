# State Integrations Quick Start

## Overview

Multi-state certificate submission system now supports:
- ✅ **Florida (FLHSMV)** - Fully implemented
- ✅ **California (TVCC)** - Traffic Violator Certificate Completion
- ✅ **Nevada (NTSA)** - Nevada Traffic Safety Association  
- ✅ **CCS** - Court Compliance System
- ✅ **California (CTSI)** - Callback receiver

## Key Features

- **Synchronous Execution** - No queue workers needed (perfect for cPanel hosting)
- **Automatic Submission** - Triggers on course completion
- **Manual Retry** - Admin can retry failed transmissions
- **Unified Dashboard** - View all state transmissions in one place
- **Callback Handlers** - Receives results from NTSA, CCS, and CTSI

## Installation

### 1. Run Migrations

```bash
php artisan migrate
```

This creates:
- `system` column in `state_transmissions` table
- `tvcc_passwords` table for California TVCC password
- State integration flags in `courses` table
- Court mapping fields in `courts` table

### 2. Configure Environment

Add to your `.env`:

```env
# State Transmission Mode (true = synchronous, no queue needed)
STATE_TRANSMISSION_SYNC=true

# California TVCC
CALIFORNIA_TVCC_ENABLED=false
CALIFORNIA_TVCC_URL=https://xsg.dmv.ca.gov/tvcc/tvccservice
CALIFORNIA_TVCC_USER=Support@dummiestrafficschool.com

# Nevada NTSA
NEVADA_NTSA_ENABLED=false
NEVADA_NTSA_URL=https://secure.ntsa.us/cgi-bin/register.cgi
NEVADA_NTSA_SCHOOL_NAME="DUMMIES TRAFFIC SCHOOL.COM"
NEVADA_NTSA_TEST_NAME="DUMMIES TRAFFIC SCHOOL.COM - CA"

# CCS
CCS_ENABLED=false
CCS_URL=http://testingprovider.com/ccs/register.jsp
CCS_SCHOOL_NAME=dummiests
CCS_RESULT_URL=https://yourdomain.com/api/ccs/result
```

### 3. Set TVCC Password

Update the California TVCC password (stored as plain text):

**Option 1: Using Artisan Command (Recommended)**
```bash
php artisan tvcc:password
# Enter password when prompted (hidden input)

# Or pass directly
php artisan tvcc:password "your_actual_password"
```

**Option 2: Using Tinker**
```bash
php artisan tinker
App\Models\TvccPassword::updatePassword('your_actual_password');
```

**Option 3: Direct SQL**
```sql
UPDATE tvcc_passwords SET password = 'your_actual_password' WHERE id = 1;
```

### 4. Enable Systems for Courses

Update courses to enable specific systems:

```php
$course = Course::find(1);
$course->update([
    'tvcc_enabled' => true,  // California TVCC
    'ntsa_enabled' => false, // Nevada NTSA
    'ccs_enabled' => false,  // CCS
]);
```

### 5. Configure Court Mappings

Add court codes for each system:

```php
$court = Court::find(1);
$court->update([
    'tvcc_court_code' => 'LA001',        // California TVCC
    'ctsi_court_id' => 'CTSI_LA_001',    // California CTSI
    'ntsa_court_name' => 'Las Vegas Municipal Court', // Nevada NTSA
]);
```

## Usage

### Automatic Transmission

When a student completes a course, transmissions are created and sent automatically based on:
- Course state (FL = Florida)
- Course flags (`tvcc_enabled`, `ntsa_enabled`, `ccs_enabled`)
- System configuration (enabled in `.env`)

### Admin Management

Access the unified dashboard:
```
/admin/state-transmissions
```

Features:
- View all transmissions across all states
- Filter by state, system, status
- Send pending transmissions
- Retry failed transmissions
- View detailed transmission logs

### Manual Transmission

Send a specific transmission:

```php
use App\Services\CaliforniaTvccService;
use App\Models\StateTransmission;

$transmission = StateTransmission::find(1);
$service = new CaliforniaTvccService();
$success = $service->sendTransmission($transmission);
```

## API Callbacks

External systems send results to these endpoints:

### California CTSI
```
POST /api/ctsi/result
Content-Type: application/xml

<response>
    <vscid>123</vscid>
    <keyresponse>SUCCESS</keyresponse>
    <saveData>Certificate processed</saveData>
    <processDate>2025-12-09</processDate>
</response>
```

### Nevada NTSA
```
POST /api/ntsa/result
Content-Type: application/x-www-form-urlencoded

UniqueID=123&percentage=95&testDate=2025-12-09&certificateSentDate=2025-12-09
```

### CCS
```
POST /api/ccs/result
Content-Type: application/x-www-form-urlencoded

StudentUserID=123&Status=Pass&Percentage=95&TestDate=2025-12-09&CertificateSentDate=2025-12-09
```

## Troubleshooting

### Transmissions Not Sending

1. Check `.env` configuration
2. Verify course flags are enabled
3. Check logs: `storage/logs/laravel.log`
4. Verify court mappings exist

### TVCC Authentication Errors

1. Verify password in database is correct
2. Check TVCC user ID in `.env`
3. Test API endpoint accessibility
4. Ensure password doesn't have extra spaces

### Callback Not Received

1. Verify callback URLs are publicly accessible
2. Check firewall/security settings
3. Review API logs for incoming requests
4. Ensure no CSRF protection on callback routes

## Testing

### Test California TVCC

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

### Test Nevada NTSA

```bash
php artisan tinker

$service = new App\Services\NevadaNtsaService();
$transmission = StateTransmission::where('system', 'NTSA')->first();
$service->sendTransmission($transmission);
```

## Database Schema

### state_transmissions
- `id` - Primary key
- `enrollment_id` - Foreign key to enrollments
- `state` - State code (FL, CA, NV)
- `system` - System identifier (FLHSMV, TVCC, NTSA, CCS, CTSI)
- `status` - pending/success/error
- `payload_json` - Request payload
- `response_code` - Response code
- `response_message` - Response message
- `sent_at` - Transmission timestamp
- `retry_count` - Number of retries

### tvcc_passwords
- `id` - Primary key
- `password` - TVCC password (plain text)
- `updated_at` - Last update

## Support

For issues or questions:
1. Check logs in `storage/logs/laravel.log`
2. Review transmission details in admin dashboard
3. Verify API credentials and endpoints
4. Test connectivity to external APIs

## Next Steps

1. Enable systems in production `.env`
2. Configure court mappings for all courts
3. Test with real student completions
4. Monitor transmission success rates
5. Set up alerts for repeated failures
