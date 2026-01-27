# Implementation Plan

- [ ] 1. Enhance database schema for transmission management
  - Add `payload_json` column (JSON, nullable) to `flhsmv_submissions` table
  - Add `sent_at` column (timestamp, nullable) to `flhsmv_submissions` table
  - Add database indexes for performance: `status`, `created_at`, `user_id`, composite `(status, created_at)`
  - _Requirements: 7.5, 8.2_

- [ ] 2. Create TransmissionCreatorService for automatic transmission creation
  - Implement `createFromCertificate()` method to create FlhsmvSubmission records
  - Implement `validateStudentData()` method to check required fields (driver_license_number, citation_number)
  - Handle validation failures by creating transmission with status 'error' and descriptive message
  - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5_

- [ ] 2.1 Write property test for transmission creation
  - **Property 1: Transmission creation on course completion**
  - **Validates: Requirements 6.1, 6.2, 6.3**

- [ ] 3. Integrate TransmissionCreatorService with EnrollmentObserver
  - Modify EnrollmentObserver to call TransmissionCreatorService after certificate generation
  - Only create transmissions for Florida courses (state = 'FL')
  - Handle service exceptions gracefully with logging
  - _Requirements: 6.1_

- [ ] 4. Create SendFlhsmvTransmissionJob for background processing
  - Implement job constructor accepting `$submissionId`
  - Implement `handle()` method to process transmission
  - Load transmission and related student/certificate data
  - Validate required fields before API submission
  - Build payload according to Florida API specification
  - Store payload in `payload_json` field before sending
  - Call FlhsmvSoapService to submit data
  - Update transmission status based on API response (completed/failed)
  - Set `sent_at` timestamp on success
  - Increment `retry_count` on failure
  - Record error details (error_code, error_message) on failure
  - Implement `failed()` method for permanent failure handling
  - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5, 8.1, 8.2, 8.3, 8.4, 8.5_

- [ ] 4.1 Write property test for status transitions
  - **Property 2: Status transitions are valid**
  - **Validates: Requirements 3.3, 3.4, 5.1**

- [ ] 4.2 Write property test for retry count increment
  - **Property 3: Retry count increments on failure**
  - **Validates: Requirements 5.3, 8.4, 9.4**

- [ ] 4.3 Write property test for payload storage
  - **Property 4: Payload stored before sending**
  - **Validates: Requirements 7.5**

- [ ] 4.4 Write property test for successful transmission timestamps
  - **Property 5: Successful transmission sets timestamps**
  - **Validates: Requirements 8.2**

- [ ] 4.5 Write property test for error recording
  - **Property 6: Failed transmission records error details**
  - **Validates: Requirements 8.3**

- [ ] 5. Configure job retry logic with exponential backoff
  - Configure job to retry up to 3 times on failure
  - Set backoff delays: 60 seconds, 300 seconds, 900 seconds
  - Configure job to use 'flhsmv' queue
  - Set job timeout to 60 seconds
  - _Requirements: 9.1, 9.2, 9.3, 15.1_

- [ ] 5.1 Write property test for retry limits
  - **Property 8: Retry respects maximum attempts**
  - **Validates: Requirements 9.1, 9.3**

- [ ] 6. Create FlTransmissionController for admin interface
  - Implement `index()` method to display dashboard with tabs (pending, failed, completed)
  - Implement `pending()` method to list pending transmissions with pagination (50 per page)
  - Implement `failed()` method to list failed/error transmissions with error details
  - Implement `completed()` method to list successful transmissions
  - Add authorization middleware (auth, role:admin)
  - Eager load relationships to avoid N+1 queries
  - _Requirements: 1.1, 1.2, 1.5, 2.1, 2.2, 11.1, 11.2, 11.3, 11.5_

- [ ] 7. Implement manual send operations in FlTransmissionController
  - Implement `sendSingle($id)` method to dispatch job for one transmission
  - Validate transmission is in 'pending' status before sending
  - Display success message with count of jobs dispatched
  - Display error message if transmission not in pending status
  - _Requirements: 3.1, 3.2, 3.5_

- [ ] 8. Implement bulk send operations in FlTransmissionController
  - Implement `sendAll()` method to dispatch jobs for all pending transmissions
  - Process each transmission independently (don't stop on individual failures)
  - Display success message with count of transmissions queued
  - _Requirements: 4.1, 4.2, 4.4, 4.5_

- [ ] 8.1 Write property test for bulk operation independence
  - **Property 7: Bulk operations process independently**
  - **Validates: Requirements 4.4**

- [ ] 9. Implement retry operations in FlTransmissionController
  - Implement `retry($id)` method to reset failed transmission to pending
  - Dispatch job for retried transmission
  - Increment retry_count field
  - Display warning if retry_count > 5
  - Display success message after dispatching retry job
  - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5_

- [ ] 10. Implement search and filtering in FlTransmissionController
  - Add search by student email or name
  - Add filter by status (pending, failed, completed, error)
  - Add filter by date range (created_at)
  - Add filter by course
  - Combine multiple filters using AND logic
  - Maintain filter state in query parameters
  - _Requirements: 14.1, 14.2, 14.3, 14.4, 14.5_

- [ ] 10.1 Write property test for filter combination logic
  - **Property 12: Filter combinations use AND logic**
  - **Validates: Requirements 14.5**

- [ ] 11. Create admin Blade views for transmission management
  - Create `admin/fl_transmissions/index.blade.php` with tabs for pending/failed/completed
  - Create `admin/fl_transmissions/pending.blade.php` with table and "Send All" button
  - Create `admin/fl_transmissions/failed.blade.php` with error details and retry buttons
  - Create `admin/fl_transmissions/completed.blade.php` with success details
  - Include CSRF tokens and method spoofing for POST forms
  - Display flash messages for user feedback
  - Add pagination controls
  - Add search and filter forms
  - Map error codes to human-readable descriptions from config
  - _Requirements: 1.2, 1.3, 1.4, 2.2, 2.3, 2.4, 2.5, 11.2, 11.3_

- [ ] 12. Add routes for FlTransmissionController
  - Add route group with auth and admin middleware
  - GET `/admin/fl-transmissions` → index
  - GET `/admin/fl-transmissions/pending` → pending
  - GET `/admin/fl-transmissions/failed` → failed
  - GET `/admin/fl-transmissions/completed` → completed
  - POST `/admin/fl-transmissions/{id}/send` → sendSingle
  - POST `/admin/fl-transmissions/send-all` → sendAll
  - POST `/admin/fl-transmissions/{id}/retry` → retry
  - _Requirements: 1.1, 2.1, 3.1, 4.1, 5.1, 11.1_

- [ ] 13. Create ProcessPendingTransmissionsCommand for scheduled processing
  - Create artisan command `flhsmv:process-pending`
  - Find all transmissions with status 'pending'
  - Check transmission is not already being processed (concurrency control)
  - Dispatch SendFlhsmvTransmissionJob for each pending transmission
  - Log count of transmissions queued
  - Handle errors gracefully and continue processing
  - _Requirements: 10.1, 10.2, 10.3, 10.4, 10.5_

- [ ] 13.1 Write property test for no duplicate processing
  - **Property 9: Scheduled task doesn't duplicate processing**
  - **Validates: Requirements 10.5**

- [ ] 14. Schedule ProcessPendingTransmissionsCommand
  - Add command to `app/Console/Kernel.php` schedule
  - Schedule to run daily at 2:00 AM
  - Use `withoutOverlapping()` to prevent concurrent runs
  - Use `onOneServer()` for multi-server environments
  - _Requirements: 10.1_

- [ ] 15. Create TransmissionNotificationService for failure alerts
  - Implement `notifyRepeatedFailure()` method to send email on 3rd failure
  - Implement `notifyBulkFailures()` method to consolidate similar errors within 1 hour
  - Include student name, course name, error code, error message in notification
  - Include direct link to transmission in admin dashboard
  - Log notification in email_logs table
  - _Requirements: 12.1, 12.2, 12.3, 12.4, 12.5_

- [ ] 15.1 Write property test for notification triggering
  - **Property 10: Notification sent on third failure**
  - **Validates: Requirements 12.1**

- [ ] 16. Integrate TransmissionNotificationService with SendFlhsmvTransmissionJob
  - Call notification service when transmission fails for 3rd time
  - Check retry_count in job's failed() method
  - _Requirements: 12.1_

- [ ] 17. Implement comprehensive logging for transmissions
  - Log complete request payload before sending to Florida API
  - Log complete response data after receiving from Florida API
  - Redact sensitive information (SSN, passwords) in logs
  - Log full exception stack trace on errors
  - Use Laravel logging system with 'flhsmv' channel
  - _Requirements: 13.1, 13.2, 13.3, 13.4, 13.5_

- [ ] 17.1 Write property test for sensitive data redaction
  - **Property 11: Sensitive data redacted in logs**
  - **Validates: Requirements 13.3**

- [ ] 18. Configure queue system for transmission processing
  - Add 'flhsmv' queue connection to `config/queue.php`
  - Support both database and Redis queue drivers
  - Configure queue worker command for production
  - Document supervisor configuration for queue worker
  - _Requirements: 15.1, 15.2, 15.5_

- [ ] 19. Add environment configuration
  - Document required .env variables in README
  - Add FLHSMV_ADMIN_EMAIL for notifications
  - Ensure existing FLHSMV variables are documented
  - _Requirements: 12.1_

- [ ] 20. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.
