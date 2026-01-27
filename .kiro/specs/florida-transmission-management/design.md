# Design Document

## Overview

The Florida Transmission Management System enhances the existing FLHSMV/DICDS integration by providing a comprehensive admin interface for monitoring, managing, and troubleshooting course completion submissions. The system leverages Laravel's queue system for asynchronous processing, implements automatic retry logic with exponential backoff, and provides bulk operations for efficient transmission management.

The design builds upon the existing `FlhsmvSubmission`, `FlhsmvSubmissionError`, and `FlhsmvSubmissionQueue` models, adding a new job-based processing layer, admin controller, and scheduled tasks for automation.

## Architecture

### High-Level Architecture

```
┌─────────────────┐
│  Course Player  │
│   (Frontend)    │
└────────┬────────┘
         │ Course Completion
         ▼
┌─────────────────────────┐
│  EnrollmentObserver     │
│  (Event Handler)        │
└────────┬────────────────┘
         │ Creates Certificate
         ▼
┌─────────────────────────┐
│  TransmissionCreator    │
│  (Service)              │
└────────┬────────────────┘
         │ Creates FlhsmvSubmission
         ▼
┌─────────────────────────┐       ┌──────────────────┐
│  Admin Dashboard        │◄──────┤  Admin User      │
│  (FlTransmissionCtrl)   │       └──────────────────┘
└────────┬────────────────┘
         │ Dispatches Jobs
         ▼
┌─────────────────────────┐
│  Queue System           │
│  (Laravel Queue)        │
└────────┬────────────────┘
         │ Processes Jobs
         ▼
┌─────────────────────────┐
│  SendFlhsmvJob          │
│  (Background Job)       │
└────────┬────────────────┘
         │ Calls Service
         ▼
┌─────────────────────────┐       ┌──────────────────┐
│  FlhsmvSoapService      │──────►│  Florida DICDS   │
│  (Existing Service)     │       │  API (SOAP)      │
└─────────────────────────┘       └──────────────────┘
```

### Component Interaction Flow

1. **Course Completion**: Student completes course → EnrollmentObserver triggers
2. **Certificate Generation**: Observer creates FloridaCertificate
3. **Transmission Creation**: TransmissionCreatorService creates FlhsmvSubmission with status 'pending'
4. **Admin Management**: Admin views dashboard, triggers send operations
5. **Job Dispatch**: Controller dispatches SendFlhsmvTransmissionJob to queue
6. **Background Processing**: Queue worker executes job
7. **API Submission**: Job calls FlhsmvSoapService to submit to Florida
8. **Status Update**: Job updates FlhsmvSubmission based on response
9. **Retry Logic**: Failed jobs automatically retry with backoff
10. **Notification**: Repeated failures trigger admin notifications

## Components and Interfaces

### 1. TransmissionCreatorService

**Purpose**: Creates transmission records when Florida certificates are generated

**Interface**:
```php
class TransmissionCreatorService
{
    public function createFromCertificate(FloridaCertificate $certificate): FlhsmvSubmission
    public function validateStudentData(User $user): array
}
```

**Responsibilities**:
- Validate required student data exists
- Create FlhsmvSubmission record with appropriate status
- Handle validation errors gracefully

### 2. SendFlhsmvTransmissionJob

**Purpose**: Background job that processes transmission submissions

**Interface**:
```php
class SendFlhsmvTransmissionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public function __construct(int $submissionId)
    public function handle(FlhsmvSoapService $service): void
    public function failed(Throwable $exception): void
}
```

**Responsibilities**:
- Load transmission and related data
- Validate required fields
- Build and store payload
- Call FlhsmvSoapService
- Update transmission status
- Handle errors and retries

### 3. FlTransmissionController

**Purpose**: Admin interface for managing transmissions

**Interface**:
```php
class FlTransmissionController extends Controller
{
    public function index(Request $request): View
    public function pending(Request $request): View
    public function failed(Request $request): View
    public function completed(Request $request): View
    public function sendSingle(int $id): RedirectResponse
    public function sendAll(): RedirectResponse
    public function retry(int $id): RedirectResponse
}
```

**Responsibilities**:
- Display transmission lists with filtering
- Dispatch jobs for manual sends
- Handle bulk operations
- Provide user feedback

### 4. ProcessPendingTransmissionsCommand

**Purpose**: Scheduled command for nightly automatic processing

**Interface**:
```php
class ProcessPendingTransmissionsCommand extends Command
{
    protected $signature = 'flhsmv:process-pending';
    protected $description = 'Process all pending Florida transmissions';
    
    public function handle(): int
}
```

**Responsibilities**:
- Find all pending transmissions
- Dispatch jobs for each
- Log processing summary
- Handle errors gracefully

### 5. TransmissionNotificationService

**Purpose**: Send notifications for repeated failures

**Interface**:
```php
class TransmissionNotificationService
{
    public function notifyRepeatedFailure(FlhsmvSubmission $submission): void
    public function notifyBulkFailures(Collection $submissions): void
}
```

**Responsibilities**:
- Detect repeated failures
- Consolidate similar errors
- Send email notifications
- Log notification activity

## Data Models

### FlhsmvSubmission (Existing - Enhanced)

```php
class FlhsmvSubmission extends Model
{
    protected $fillable = [
        'user_id',
        'florida_certificate_id',
        'submission_data',      // Original data
        'payload_json',         // NEW: Built payload before sending
        'response_data',
        'status',               // pending, processing, completed, failed, error
        'error_code',
        'error_message',
        'retry_count',
        'submitted_at',
        'completed_at',
        'sent_at'              // NEW: Timestamp when actually sent
    ];
    
    protected $casts = [
        'submission_data' => 'array',
        'payload_json' => 'array',
        'response_data' => 'array',
        'submitted_at' => 'datetime',
        'completed_at' => 'datetime',
        'sent_at' => 'datetime'
    ];
    
    // Relationships
    public function user(): BelongsTo
    public function certificate(): BelongsTo
    public function errors(): HasMany
    public function queueEntry(): HasOne
    
    // Scopes
    public function scopePending($query)
    public function scopeFailed($query)
    public function scopeCompleted($query)
    public function scopeNeedsRetry($query)
}
```

### FlhsmvSubmissionError (Existing - No Changes)

Already well-designed for error tracking.

### FlhsmvSubmissionQueue (Existing - No Changes)

Already tracks queue processing state.

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system-essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Transmission creation on course completion

*For any* Florida course completion, when a certificate is generated, a corresponding FlhsmvSubmission record should be created with status 'pending' or 'error'

**Validates: Requirements 6.1, 6.2, 6.3**

### Property 2: Status transitions are valid

*For any* FlhsmvSubmission, status transitions should follow the valid state machine: pending → processing → (completed | failed), and failed → pending (on retry)

**Validates: Requirements 3.3, 3.4, 5.1**

### Property 3: Retry count increments on failure

*For any* transmission that fails, the retry_count field should be incremented by exactly 1

**Validates: Requirements 5.3, 8.4, 9.4**

### Property 4: Payload stored before sending

*For any* transmission that is sent to Florida API, the payload_json field should be populated before the API call is made

**Validates: Requirements 7.5**

### Property 5: Successful transmission sets timestamps

*For any* transmission that completes successfully, both sent_at and completed_at timestamps should be set

**Validates: Requirements 8.2**

### Property 6: Failed transmission records error details

*For any* transmission that fails, error_code and error_message fields should be populated

**Validates: Requirements 8.3**

### Property 7: Bulk operations process independently

*For any* set of transmissions in a bulk send operation, the failure of one transmission should not prevent processing of other transmissions

**Validates: Requirements 4.4**

### Property 8: Retry respects maximum attempts

*For any* transmission, automatic retries should not exceed 3 attempts

**Validates: Requirements 9.1, 9.3**

### Property 9: Scheduled task doesn't duplicate processing

*For any* transmission being processed, the scheduled task should not dispatch a duplicate job for the same transmission

**Validates: Requirements 10.5**

### Property 10: Notification sent on third failure

*For any* transmission that fails for the third time, an admin notification should be sent

**Validates: Requirements 12.1**

### Property 11: Sensitive data redacted in logs

*For any* logged transmission data, sensitive fields (SSN, passwords) should be redacted or masked

**Validates: Requirements 13.3**

### Property 12: Filter combinations use AND logic

*For any* combination of filters applied to the transmission list, results should match all filter criteria simultaneously

**Validates: Requirements 14.5**

## Error Handling

### Validation Errors

**Scenario**: Required student data missing (driver_license_number, citation_number)

**Handling**:
- Create transmission with status 'error'
- Set descriptive error_message
- Do not attempt API submission
- Display in failed transmissions list with "Edit Student" link

### API Errors

**Scenario**: Florida API returns error response

**Handling**:
- Parse error_code and error_message from response
- Update transmission status to 'failed'
- Increment retry_count
- Schedule automatic retry with backoff
- Log full response for debugging

### Network Errors

**Scenario**: Connection timeout or network failure

**Handling**:
- Catch exception in job
- Update transmission status to 'failed'
- Set error_code to 'NETWORK_ERROR'
- Increment retry_count
- Schedule automatic retry
- Log exception details

### Queue Failures

**Scenario**: Job fails permanently after max retries

**Handling**:
- Move to failed_jobs table
- Keep transmission status as 'failed'
- Send admin notification
- Provide artisan command to retry: `php artisan queue:retry {job-id}`

### Concurrent Processing

**Scenario**: Multiple workers or admins trigger same transmission

**Handling**:
- Use database locking when updating transmission status
- Check status before processing (skip if not 'pending')
- Log concurrent access attempts

## Testing Strategy

### Unit Tests

**TransmissionCreatorService**:
- Test successful transmission creation with valid data
- Test error status when required fields missing
- Test validation logic for each required field

**SendFlhsmvTransmissionJob**:
- Test successful API submission updates status correctly
- Test failed API submission records error details
- Test retry count increments on failure
- Test payload building and storage

**FlTransmissionController**:
- Test index displays correct transmissions
- Test filtering by status, date, course
- Test search by student name/email
- Test authorization (admin only)

**TransmissionNotificationService**:
- Test notification sent on third failure
- Test bulk failure consolidation
- Test notification content includes required details

### Property-Based Tests

Property-based testing will use **Pest with Pest Faker** for Laravel. Each property test should run a minimum of 100 iterations.

**Property 1: Transmission creation**
- Generate random FloridaCertificate with valid/invalid user data
- Verify transmission created with correct status

**Property 2: Status transitions**
- Generate random status transition sequences
- Verify only valid transitions are allowed

**Property 3: Retry count**
- Generate random failure scenarios
- Verify retry_count increments correctly

**Property 4: Payload storage**
- Generate random transmission data
- Verify payload_json populated before sending

**Property 5: Timestamp setting**
- Generate random successful transmissions
- Verify timestamps set correctly

**Property 6: Error recording**
- Generate random error responses
- Verify error details recorded

**Property 7: Bulk independence**
- Generate random sets of transmissions with some failures
- Verify all are processed independently

**Property 8: Retry limits**
- Generate transmissions with various retry counts
- Verify max retry limit enforced

**Property 9: No duplicate processing**
- Generate concurrent processing scenarios
- Verify no duplicate jobs dispatched

**Property 10: Notification triggering**
- Generate transmissions with various failure counts
- Verify notification sent only on third failure

**Property 11: Data redaction**
- Generate log entries with sensitive data
- Verify sensitive fields redacted

**Property 12: Filter logic**
- Generate random filter combinations
- Verify AND logic applied correctly

### Integration Tests

**End-to-End Flow**:
- Complete course → certificate generated → transmission created → job processed → status updated
- Test with mock Florida API
- Verify database state at each step

**Queue Processing**:
- Dispatch multiple jobs
- Verify queue worker processes them
- Test failed job handling

**Admin Interface**:
- Test full admin workflow: view pending → send → view completed
- Test retry workflow: view failed → retry → verify reprocessed
- Test bulk send with mixed results

### Testing Framework Configuration

```php
// tests/Pest.php
uses(Tests\TestCase::class)->in('Feature', 'Unit');

// Property-based testing configuration
function propertyTest(string $description, callable $test): void
{
    test($description, function () use ($test) {
        for ($i = 0; $i < 100; $i++) {
            $test();
        }
    });
}
```

Each property-based test will be tagged with:
```php
// Feature: florida-transmission-management, Property 1: Transmission creation on course completion
```

## Security Considerations

### Authorization

- All admin routes protected by `auth` and `role:admin` middleware
- Transmission data access restricted to authorized admins
- API credentials stored in environment variables, never in code

### Data Protection

- Sensitive data (SSN, passwords) redacted in logs
- API credentials encrypted in database if stored
- Transmission payloads contain only necessary data

### Rate Limiting

- Admin actions rate-limited to prevent abuse
- Queue processing throttled to respect Florida API limits
- Bulk operations limited to reasonable batch sizes

### Audit Trail

- All admin actions logged in audit_logs table
- Transmission status changes tracked with timestamps
- Failed access attempts logged in security_logs

## Performance Considerations

### Database Optimization

- Index on `flhsmv_submissions.status` for filtering
- Index on `flhsmv_submissions.created_at` for date range queries
- Index on `flhsmv_submissions.user_id` for joins
- Composite index on `(status, created_at)` for common queries

### Queue Configuration

- Dedicated `flhsmv` queue for transmission jobs
- Separate queue worker process for isolation
- Configurable concurrency (default: 1 to respect API limits)
- Job timeout: 60 seconds

### Pagination

- Admin lists paginated at 50 records per page
- Eager load relationships to avoid N+1 queries
- Use cursor pagination for large datasets

### Caching

- Cache Florida error code mappings (config/flhsmv.php)
- Cache admin dashboard statistics (5 minute TTL)
- No caching of transmission data (must be real-time)

## Deployment Considerations

### Environment Configuration

Required `.env` variables:
```
FLHSMV_USERNAME=
FLHSMV_PASSWORD=
FLHSMV_WSDL_URL=
FLHSMV_DEFAULT_SCHOOL_ID=
FLHSMV_DEFAULT_INSTRUCTOR_ID=
FLHSMV_RETRY_ATTEMPTS=3
FLHSMV_RETRY_DELAY=300
FLHSMV_ADMIN_EMAIL=admin@example.com

QUEUE_CONNECTION=database  # or redis
```

### Queue Worker Setup

**Supervisor Configuration** (production):
```ini
[program:flhsmv-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --queue=flhsmv --tries=3 --backoff=60,300,900
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/storage/logs/flhsmv-worker.log
```

### Scheduled Tasks

Add to `app/Console/Kernel.php`:
```php
$schedule->command('flhsmv:process-pending')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->onOneServer();
```

### Database Migrations

New migration needed:
- Add `payload_json` column to `flhsmv_submissions`
- Add `sent_at` column to `flhsmv_submissions`
- Add indexes for performance

### Monitoring

- Monitor queue length: `php artisan queue:monitor flhsmv:50`
- Monitor failed jobs: `php artisan queue:failed`
- Monitor transmission success rate via dashboard
- Alert on high failure rates (>10% in 1 hour)

## Future Enhancements

### Phase 2 Considerations

- **Batch API Submission**: Submit multiple completions in single API call
- **Real-time Status Updates**: WebSocket updates for admin dashboard
- **Advanced Analytics**: Success rate trends, error pattern analysis
- **Multi-State Support**: Extend pattern to Texas, Missouri, Delaware
- **API Versioning**: Support multiple Florida API versions
- **Webhook Integration**: Receive status updates from Florida
- **Export Functionality**: Export transmission reports to CSV/Excel
- **Advanced Filtering**: Saved filter presets, complex query builder
