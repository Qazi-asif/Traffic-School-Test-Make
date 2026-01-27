# State Certificate Submission Integrations

## Overview

Multi-state certificate submission system for traffic school completion reporting to government agencies and third-party compliance systems.

## Implemented States

### Florida (FLHSMV/DICDS)
- **Status**: ✅ Implemented
- **Model**: `StateTransmission`
- **Job**: `SendFloridaTransmissionJob`
- **Controller**: `FlTransmissionController`
- **API**: SOAP/REST to Florida DHSMV
- **Credentials**: Multiple school accounts (see `.env`)

## Pending State Integrations

### California - TVCC (Traffic Violator Certificate Completion)
- **API Endpoint**: `https://xsg.dmv.ca.gov/tvcc/tvccservice`
- **Method**: SOAP/REST API
- **Authentication**: 
  - User ID: `Support@dummiestrafficschool.com`
  - Password: Stored in database (`tvcc_password` table)
- **Request Parameters**:
  - `ccDate` - Course completion date (yyyy-MM-dd)
  - `courtCd` - Court code (from court mapping)
  - `dateOfBirth` - Student DOB (yyyy-MM-dd)
  - `dlNbr` - Driver's license number (CA only)
  - `firstName` - Student first name
  - `lastName` - Student last name
  - `modality` - Fixed: "4T"
  - `refNbr` - Citation number
  - `userDto` - Authentication credentials
- **Response Fields**:
  - `ccSeqNbr` - Certificate sequence number
  - `ccStatCd` - Status code
  - `ccSubTstamp` - Submission timestamp
- **Error Handling**: 
  - `TvccAccessDeniedException`
  - `TvccServiceException`
  - `TvccValidationFailedException`
- **Trigger**: Automatic on course completion for CA state tickets
- **Storage**: `tvcc_response` table

### California - CTSI (California Traffic School Interface)
- **Result URL Endpoint**: `/ctsiresulturl.jsp` (callback)
- **Method**: XML POST from CTSI system
- **Response Fields**:
  - `keyresponse` - Status key
  - `saveData` - Message/description
  - `processDate` - Processing timestamp
  - `vscid` - Student ID
- **Storage**: `vsCTSI_ResultURL` table
- **Note**: This is a callback receiver, not an outbound API

### Nevada - NTSA (Nevada Traffic Safety Association)
- **API Endpoint**: `https://secure.ntsa.us/cgi-bin/register.cgi`
- **Method**: HTTP POST form auto-submit
- **Request Parameters**:
  - `Name` - Student full name (First Last)
  - `Email` - Student email
  - `License` - Driver's license number
  - `DOB` - Date of birth (yyyy-MM-dd)
  - `Court` - Court name (from `ntsacourtname` field)
  - `CaseNum` - Citation number
  - `DueDate` - Due date (yyyy-MM-dd)
  - `School` - Fixed: "DUMMIES TRAFFIC SCHOOL.COM"
  - `TestName` - Fixed: "DUMMIES TRAFFIC SCHOOL.COM - CA"
  - `Telephone` - Phone number
  - `UniqueID` - Student ID (vscid)
  - `Encrypt` - Fixed: "0"
  - `Demo` - Fixed: "0"
  - `Language` - "English" or "Spanish"
- **Result URL**: `/resultsurl.jsp` (callback)
- **Response Fields**:
  - `percentage` - Test score
  - `testDate` - Test completion date
  - `certificateSentDate` - Certificate sent date
- **Storage**: `ntsaRecord` table

### CCS (Court Compliance System)
- **Registration Endpoint**: `http://testingprovider.com/ccs/register.jsp`
- **Method**: HTTP POST form auto-submit
- **Request Parameters**:
  - `StudentName` - Full name (First Last)
  - `StudentEmail` - Email address
  - `StudentDriLicNum` - Driver's license number
  - `StudentBirthday` - DOB (MM/dd/yyyy)
  - `StudentCourtName` - Court name
  - `StudentCaseNum` - Citation number
  - `StudentCourtDueDate` - Due date (MM/dd/yyyy)
  - `StudentSchoolName` - Fixed: "dummiests"
  - `StudentSignUpDate` - Sign-up date (MM/dd/yyyy)
  - `StudentAddress` - Full address
  - `StudentCity` - City
  - `StudentState` - State
  - `StudentPostalCode` - ZIP code
  - `StudentTelephoneNum` - Phone number
  - `StudentUserID` - Student ID
  - `StudentLanguage` - "English" or "Spanish"
- **Result URL**: `/ccsresultsurl.jsp` (callback)
- **Response Fields**:
  - `StudentUserID` - Student ID
  - `StudentDriLicNum` - Driver's license
  - `Status` - Pass/fail status
  - `Percentage` - Test score
  - `TestDate` - Test completion date
  - `CertificateSentDate` - Certificate sent date
- **Storage**: `ccsRecord` table
- **Action on Pass**: Updates `vscustomer` and `progresstable`

## Database Schema Requirements

### Core Tables

**`state_transmissions`** (existing):
- `id` - Primary key
- `enrollment_id` - Foreign key to enrollments
- `state` - State code (FL, CA, NV, etc.)
- `system` - System identifier (TVCC, CTSI, NTSA, CCS)
- `status` - pending/success/error
- `payload_json` - Request payload
- `response_code` - Response code
- `response_message` - Response message
- `sent_at` - Transmission timestamp
- `retry_count` - Number of retries
- `created_at`, `updated_at`

**`transmission_error_codes`** (existing):
- `id` - Primary key
- `state` - State code
- `error_code` - Error code from API
- `description` - Error description
- `is_retryable` - Boolean flag
- `created_at`, `updated_at`

**`tvcc_passwords`** (new):
- `id` - Primary key
- `password` - Current TVCC password
- `updated_at` - Last update timestamp

### State-Specific Flags

**`courses` table** should have:
- `ntsa_enabled` - Boolean (Nevada NTSA)
- `ccs_enabled` - Boolean (CCS)
- `ctsi_enabled` - Boolean (CA CTSI)
- `tvcc_enabled` - Boolean (CA TVCC)

**`courts` table** should have:
- `ntsa_court_name` - Nevada NTSA court name
- `ctsi_court_id` - CTSI court identifier
- `tvcc_court_code` - TVCC court code

## Implementation Pattern

### Service Layer
Create state-specific services following Florida pattern:
- `CaliforniaTvccService` - TVCC API integration
- `CaliforniaCtsiService` - CTSI callback handler
- `NevadaNtsaService` - NTSA API integration
- `CcsService` - CCS API integration

### Job Layer
Create queued jobs for each integration:
- `SendCaliforniaTvccJob`
- `SendNevadaNtsaJob`
- `SendCcsJob`

### Controller Layer
Admin controllers for management:
- `CaTvccTransmissionController`
- `NvNtsaTransmissionController`
- `CcsTransmissionController`

### Event/Listener Pattern
Extend existing `CreateStateTransmission` listener to handle multiple states:
```php
// Listen to CourseCompleted event
// Check enrollment state and enabled systems
// Create appropriate transmission records
```

## Configuration

### Environment Variables
```env
# California TVCC
CALIFORNIA_TVCC_URL=https://xsg.dmv.ca.gov/tvcc/tvccservice
CALIFORNIA_TVCC_USER=Support@dummiestrafficschool.com
CALIFORNIA_TVCC_PASSWORD=stored_in_db

# Nevada NTSA
NEVADA_NTSA_URL=https://secure.ntsa.us/cgi-bin/register.cgi
NEVADA_NTSA_SCHOOL_NAME="DUMMIES TRAFFIC SCHOOL.COM"
NEVADA_NTSA_TEST_NAME="DUMMIES TRAFFIC SCHOOL.COM - CA"

# CCS
CCS_URL=http://testingprovider.com/ccs/register.jsp
CCS_SCHOOL_NAME=dummiests
CCS_RESULT_URL=https://yourdomain.com/api/ccs/result
```

### Config Files
Create `config/state-integrations.php`:
```php
return [
    'california' => [
        'tvcc' => [
            'enabled' => env('CALIFORNIA_TVCC_ENABLED', false),
            'url' => env('CALIFORNIA_TVCC_URL'),
            'user' => env('CALIFORNIA_TVCC_USER'),
            'modality' => '4T',
        ],
        'ctsi' => [
            'enabled' => env('CALIFORNIA_CTSI_ENABLED', false),
        ],
    ],
    'nevada' => [
        'ntsa' => [
            'enabled' => env('NEVADA_NTSA_ENABLED', false),
            'url' => env('NEVADA_NTSA_URL'),
            'school_name' => env('NEVADA_NTSA_SCHOOL_NAME'),
            'test_name' => env('NEVADA_NTSA_TEST_NAME'),
        ],
    ],
    'ccs' => [
        'enabled' => env('CCS_ENABLED', false),
        'url' => env('CCS_URL'),
        'school_name' => env('CCS_SCHOOL_NAME'),
    ],
];
```

## Routes

### API Routes (for callbacks)
```php
// California CTSI callback
Route::post('/api/ctsi/result', [CtsiCallbackController::class, 'handle']);

// Nevada NTSA callback
Route::post('/api/ntsa/result', [NtsaCallbackController::class, 'handle']);

// CCS callback
Route::post('/api/ccs/result', [CcsCallbackController::class, 'handle']);
```

### Admin Routes
```php
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    // California TVCC
    Route::get('/ca-tvcc-transmissions', [CaTvccTransmissionController::class, 'index']);
    Route::post('/ca-tvcc-transmissions/{id}/send', [CaTvccTransmissionController::class, 'send']);
    Route::post('/ca-tvcc-transmissions/{id}/retry', [CaTvccTransmissionController::class, 'retry']);
    
    // Nevada NTSA
    Route::get('/nv-ntsa-transmissions', [NvNtsaTransmissionController::class, 'index']);
    Route::post('/nv-ntsa-transmissions/{id}/send', [NvNtsaTransmissionController::class, 'send']);
    
    // CCS
    Route::get('/ccs-transmissions', [CcsTransmissionController::class, 'index']);
    Route::post('/ccs-transmissions/{id}/send', [CcsTransmissionController::class, 'send']);
});
```

## Testing Strategy

### Unit Tests
- Test payload building for each integration
- Test validation logic
- Test error handling

### Integration Tests
- Mock API responses
- Test callback handlers
- Test retry logic

### Manual Testing
- Use test credentials/sandbox environments
- Verify callback URLs are accessible
- Test with real student data (in staging)

## Migration Path

1. **Phase 1**: Database schema updates
   - Add system column to state_transmissions
   - Create tvcc_passwords table
   - Add flags to courses/courts tables

2. **Phase 2**: California TVCC implementation
   - Service, Job, Controller
   - Admin views
   - Testing

3. **Phase 3**: Nevada NTSA implementation
   - Service, Job, Controller
   - Callback handler
   - Admin views

4. **Phase 4**: CCS implementation
   - Service, Job, Controller
   - Callback handler
   - Admin views

5. **Phase 5**: California CTSI callback handler
   - Callback controller
   - Admin views for monitoring

## Security Considerations

- Store TVCC password in encrypted database field
- Validate callback signatures/tokens
- Rate limit callback endpoints
- Log all transmission attempts
- Sanitize all user input before API submission
- Use HTTPS for all API calls
- Implement IP whitelisting for callbacks if possible

## Monitoring & Alerts

- Dashboard showing transmission success rates by state/system
- Alert on repeated failures (3+ retries)
- Daily summary reports
- Queue monitoring for pending transmissions
- Callback failure tracking
