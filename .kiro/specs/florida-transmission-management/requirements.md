# Requirements Document

## Introduction

This feature enhances the existing Florida FLHSMV/DICDS state transmission system by adding comprehensive admin management capabilities, queue-based job processing, automated retry mechanisms, and bulk operations. The system will allow administrators to monitor, manage, and troubleshoot course completion submissions to Florida's state reporting system.

## Glossary

- **FLHSMV**: Florida Department of Highway Safety and Motor Vehicles
- **DICDS**: Driver Improvement Course Data System (Florida's web service for course completion reporting)
- **Transmission**: A submission of course completion data to the Florida state system
- **System**: The Florida Transmission Management System
- **Admin**: A user with administrative privileges to manage transmissions
- **Student**: A user who has completed a course requiring state reporting
- **Payload**: The JSON data structure sent to Florida's API
- **Queue Worker**: A background process that executes queued transmission jobs

## Requirements

### Requirement 1

**User Story:** As an admin, I want to view all pending Florida transmissions, so that I can monitor which course completions need to be submitted to the state.

#### Acceptance Criteria

1. WHEN an admin accesses the Florida transmissions dashboard THEN the System SHALL display all transmissions with status 'pending'
2. WHEN displaying pending transmissions THEN the System SHALL show student email, student name, completion date, course name, and creation timestamp
3. WHEN displaying pending transmissions THEN the System SHALL provide a "Send" action button for each individual transmission
4. WHEN displaying pending transmissions THEN the System SHALL provide a "Send All Pending" bulk action button
5. WHEN the pending transmissions list exceeds 50 records THEN the System SHALL paginate the results

### Requirement 2

**User Story:** As an admin, I want to view all failed Florida transmissions with error details, so that I can identify and resolve submission issues.

#### Acceptance Criteria

1. WHEN an admin accesses the Florida transmissions dashboard THEN the System SHALL display all transmissions with status 'failed' or 'error'
2. WHEN displaying failed transmissions THEN the System SHALL show student email, student name, completion date, course name, error code, error message, and retry count
3. WHEN displaying failed transmissions THEN the System SHALL provide a "Retry" action button for each transmission
4. WHEN displaying failed transmissions THEN the System SHALL provide an "Edit Student" link to modify student data
5. WHEN an error code matches a known FLHSMV error code THEN the System SHALL display the human-readable error description from the configuration

### Requirement 3

**User Story:** As an admin, I want to manually trigger a single transmission, so that I can immediately submit a specific course completion to Florida.

#### Acceptance Criteria

1. WHEN an admin clicks "Send" on a pending transmission THEN the System SHALL dispatch a background job to process that transmission
2. WHEN the transmission job is dispatched THEN the System SHALL display a success message to the admin
3. WHEN the transmission job completes successfully THEN the System SHALL update the transmission status to 'completed'
4. WHEN the transmission job fails THEN the System SHALL update the transmission status to 'failed' and record the error details
5. IF the transmission is not in 'pending' status WHEN the admin attempts to send THEN the System SHALL prevent the action and display an error message

### Requirement 4

**User Story:** As an admin, I want to send all pending transmissions at once, so that I can efficiently process multiple course completions in bulk.

#### Acceptance Criteria

1. WHEN an admin clicks "Send All Pending" THEN the System SHALL dispatch background jobs for all transmissions with status 'pending'
2. WHEN dispatching bulk jobs THEN the System SHALL display the count of transmissions queued for processing
3. WHEN bulk jobs are dispatched THEN the System SHALL process each transmission independently without blocking the admin interface
4. WHEN any individual transmission in the bulk operation fails THEN the System SHALL continue processing remaining transmissions
5. WHEN bulk jobs complete THEN the System SHALL update each transmission status independently based on its result

### Requirement 5

**User Story:** As an admin, I want to retry failed transmissions, so that I can resubmit course completions after resolving data or connectivity issues.

#### Acceptance Criteria

1. WHEN an admin clicks "Retry" on a failed transmission THEN the System SHALL reset the transmission status to 'pending'
2. WHEN retrying a transmission THEN the System SHALL dispatch a background job to process the transmission
3. WHEN retrying a transmission THEN the System SHALL increment the retry_count field
4. WHEN a transmission has been retried more than 5 times THEN the System SHALL display a warning to the admin
5. WHEN the retry job completes THEN the System SHALL update the transmission status based on the result

### Requirement 6

**User Story:** As a system, I want to automatically create transmission records when students complete Florida courses, so that course completions are tracked for state reporting.

#### Acceptance Criteria

1. WHEN a student completes a course with state code 'FL' THEN the System SHALL create a new transmission record with status 'pending'
2. WHEN creating a transmission record THEN the System SHALL associate it with the student's user_id and florida_certificate_id
3. WHEN creating a transmission record THEN the System SHALL initialize retry_count to 0
4. WHEN creating a transmission record THEN the System SHALL validate that required student data exists (driver_license_number, citation_number)
5. IF required student data is missing WHEN creating a transmission THEN the System SHALL create the record with status 'error' and descriptive error message

### Requirement 7

**User Story:** As a background job, I want to process transmission submissions asynchronously, so that the admin interface remains responsive and transmissions can be retried automatically.

#### Acceptance Criteria

1. WHEN a transmission job executes THEN the System SHALL load the transmission record and related student data
2. WHEN processing a transmission THEN the System SHALL validate all required fields before sending to Florida API
3. WHEN validation fails THEN the System SHALL update the transmission status to 'error' with a descriptive validation error message
4. WHEN validation succeeds THEN the System SHALL build the payload according to Florida's API specification
5. WHEN the payload is built THEN the System SHALL store it in the payload_json field before sending

### Requirement 8

**User Story:** As a background job, I want to send transmission data to Florida's API and handle responses, so that course completions are officially reported to the state.

#### Acceptance Criteria

1. WHEN sending a transmission THEN the System SHALL use the existing FlhsmvSoapService to submit the data
2. WHEN the Florida API returns a success response THEN the System SHALL update the transmission status to 'completed' and set sent_at timestamp
3. WHEN the Florida API returns an error response THEN the System SHALL update the transmission status to 'failed' and record the error_code and error_message
4. WHEN the Florida API returns an error THEN the System SHALL increment the retry_count field
5. WHEN a network or connection error occurs THEN the System SHALL update the transmission status to 'failed' and record the exception message

### Requirement 9

**User Story:** As a background job, I want to implement automatic retry logic with exponential backoff, so that temporary failures can be resolved without manual intervention.

#### Acceptance Criteria

1. WHEN a transmission job fails THEN the System SHALL automatically retry the job up to 3 times
2. WHEN retrying a failed job THEN the System SHALL use exponential backoff delays (1 minute, 5 minutes, 15 minutes)
3. WHEN the maximum retry attempts are exhausted THEN the System SHALL mark the transmission as 'failed' permanently
4. WHEN a job is retried THEN the System SHALL log the retry attempt with timestamp
5. WHEN a retried job succeeds THEN the System SHALL update the transmission status to 'completed'

### Requirement 10

**User Story:** As an admin, I want to schedule automatic nightly transmission of all pending records, so that course completions are submitted to Florida without manual intervention.

#### Acceptance Criteria

1. WHEN the scheduled task runs at 2:00 AM daily THEN the System SHALL identify all transmissions with status 'pending'
2. WHEN the scheduled task identifies pending transmissions THEN the System SHALL dispatch background jobs for each transmission
3. WHEN the scheduled task completes THEN the System SHALL log the count of transmissions queued
4. WHEN the scheduled task encounters an error THEN the System SHALL log the error and continue operation
5. WHEN the scheduled task runs THEN the System SHALL not process transmissions that are already being processed

### Requirement 11

**User Story:** As an admin, I want to view successful transmissions with submission details, so that I can verify course completions were properly reported to Florida.

#### Acceptance Criteria

1. WHEN an admin accesses the successful transmissions view THEN the System SHALL display all transmissions with status 'completed'
2. WHEN displaying successful transmissions THEN the System SHALL show student email, student name, completion date, course name, and sent_at timestamp
3. WHEN displaying successful transmissions THEN the System SHALL show the Florida certificate number received from the state
4. WHEN displaying successful transmissions THEN the System SHALL provide filtering by date range
5. WHEN displaying successful transmissions THEN the System SHALL paginate results with 50 records per page

### Requirement 12

**User Story:** As an admin, I want to receive notifications when transmissions repeatedly fail, so that I can proactively address systemic issues.

#### Acceptance Criteria

1. WHEN a transmission fails for the 3rd time THEN the System SHALL send an email notification to configured admin email addresses
2. WHEN sending failure notifications THEN the System SHALL include student name, course name, error code, and error message
3. WHEN sending failure notifications THEN the System SHALL include a direct link to the transmission in the admin dashboard
4. WHEN multiple transmissions fail with the same error code within 1 hour THEN the System SHALL send a single consolidated notification
5. WHEN the notification email is sent THEN the System SHALL log the notification in the email_logs table

### Requirement 13

**User Story:** As a developer, I want comprehensive logging of all transmission requests and responses, so that I can troubleshoot integration issues with Florida's API.

#### Acceptance Criteria

1. WHEN a transmission is sent to Florida API THEN the System SHALL log the complete request payload
2. WHEN a response is received from Florida API THEN the System SHALL log the complete response data
3. WHEN logging transmission data THEN the System SHALL redact sensitive information (SSN, passwords)
4. WHEN a transmission error occurs THEN the System SHALL log the full exception stack trace
5. WHEN logging transmission activity THEN the System SHALL use the Laravel logging system with 'flhsmv' channel

### Requirement 14

**User Story:** As an admin, I want to filter and search transmissions by various criteria, so that I can quickly find specific submissions.

#### Acceptance Criteria

1. WHEN an admin uses the search function THEN the System SHALL filter transmissions by student email or name
2. WHEN an admin selects a date range filter THEN the System SHALL display only transmissions created within that range
3. WHEN an admin selects a status filter THEN the System SHALL display only transmissions matching that status
4. WHEN an admin selects a course filter THEN the System SHALL display only transmissions for that specific course
5. WHEN multiple filters are applied THEN the System SHALL combine filters using AND logic

### Requirement 15

**User Story:** As a system administrator, I want to configure queue workers to process transmission jobs, so that background processing is reliable and scalable.

#### Acceptance Criteria

1. WHEN transmission jobs are dispatched THEN the System SHALL use the 'flhsmv' queue connection
2. WHEN queue workers are started THEN the System SHALL process jobs from the 'flhsmv' queue
3. WHEN a job fails permanently THEN the System SHALL move it to the failed_jobs table
4. WHEN viewing failed jobs THEN the System SHALL provide commands to retry or delete them
5. WHEN the queue worker is configured THEN the System SHALL support both database and Redis queue drivers
