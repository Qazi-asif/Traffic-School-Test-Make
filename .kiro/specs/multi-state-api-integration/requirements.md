# Multi-State API Integration Requirements

## Introduction

This document outlines requirements for integrating multiple state government APIs for traffic school certificate submission and verification. The system will support California (TVCC & CTSI), Nevada (NTSA), and Court Compliance System (CCS) in addition to the existing Florida FLHSMV integration.

## Glossary

- **TVCC**: Traffic Violator Certificate Completion (California DMV)
- **CTSI**: California Traffic School Interface
- **NTSA**: Nevada Traffic Safety Association
- **CCS**: Court Compliance System (multi-state)
- **Certificate Transmission**: Automated submission of course completion data to state authorities
- **State Transmission**: Record of certificate submission attempt to a state API
- **Transmission Response**: API response containing success/error codes and messages

## Requirements

### Requirement 1: California TVCC Integration

**User Story:** As a system administrator, I want to automatically submit California course completions to the DMV TVCC system, so that students' certificates are electronically verified.

#### Acceptance Criteria

1. WHEN a California student completes a course THEN the system SHALL automatically submit certificate data to the TVCC API endpoint
2. WHEN submitting to TVCC THEN the system SHALL include all required fields (ccDate, courtCd, dateOfBirth, dlNbr, firstName, lastName, modality, refNbr, userDto)
3. WHEN TVCC returns a success response THEN the system SHALL store the ccSeqNbr, ccStatCd, and ccSubTstamp
4. WHEN TVCC returns an error THEN the system SHALL log the error and allow manual resubmission
5. WHEN authentication fails THEN the system SHALL retrieve credentials from the database tvcc_password table

### Requirement 2: California CTSI Integration

**User Story:** As a system administrator, I want to receive and process CTSI result callbacks, so that certificate submissions through CTSI are properly tracked.

#### Acceptance Criteria

1. WHEN CTSI posts XML results THEN the system SHALL parse and store the keyresponse, saveData, and processDate
2. WHEN CTSI data is received THEN the system SHALL update the student record with the transmission status
3. WHEN CTSI processing completes THEN the system SHALL store results in the vsCTSI_ResultURL table
4. WHEN CTSI errors occur THEN the system SHALL log the error details for administrator review

### Requirement 3: Nevada NTSA Integration

**User Story:** As a system administrator, I want to redirect Nevada students to NTSA for registration and receive completion callbacks, so that Nevada certificates are properly submitted.

#### Acceptance Criteria

1. WHEN a Nevada student completes registration THEN the system SHALL redirect to NTSA with all required parameters
2. WHEN redirecting to NTSA THEN the system SHALL include Name, Email, License, DOB, Court, CaseNum, DueDate, School, TestName, Telephone, UniqueID, Encrypt, Demo, and Language
3. WHEN NTSA posts completion results THEN the system SHALL store percentage, testDate, and certificateSentDate in ntsaRecord table
4. WHEN NTSA callback is received THEN the system SHALL update the student's completion status
5. WHEN NTSA transmission fails THEN the system SHALL allow manual resubmission from admin interface

### Requirement 4: CCS (Court Compliance System) Integration

**User Story:** As a system administrator, I want to register students with CCS and receive completion callbacks, so that multi-state court compliance is maintained.

#### Acceptance Criteria

1. WHEN a CCS-enabled student registers THEN the system SHALL redirect to CCS registration endpoint
2. WHEN redirecting to CCS THEN the system SHALL include StudentName, StudentEmail, StudentDriLicNum, StudentBirthday, StudentCourtName, StudentCaseNum, StudentCourtDueDate, StudentSchoolName, StudentSignUpDate, StudentAddress, StudentCity, StudentState, StudentPostalCode, StudentTelephoneNum, StudentUserID, and StudentLanguage
3. WHEN CCS posts completion results THEN the system SHALL store StudentUserID, StudentDriLicNum, Status, Percentage, TestDate, and CertificateSentDate
4. WHEN CCS status is "pass" THEN the system SHALL update vscustomer and progresstable records
5. WHEN CCS data is received THEN the system SHALL store results in ccsRecord table

### Requirement 5: Admin Management Interface

**User Story:** As an administrator, I want to view, manage, and manually resubmit state transmissions, so that I can handle failed submissions and track certificate delivery.

#### Acceptance Criteria

1. WHEN viewing transmissions THEN the system SHALL display all state submissions with status, date, and response
2. WHEN a transmission fails THEN the system SHALL provide a "Resend" button for manual resubmission
3. WHEN viewing California transmissions THEN the system SHALL show TVCC and CTSI submissions separately
4. WHEN viewing Nevada transmissions THEN the system SHALL display NTSA registration and completion data
5. WHEN viewing CCS transmissions THEN the system SHALL show registration and completion status
6. WHEN filtering transmissions THEN the system SHALL allow filtering by state, status, and date range

### Requirement 6: State-Specific Configuration

**User Story:** As an administrator, I want to configure which submission system each county/course uses, so that certificates are sent to the correct state authority.

#### Acceptance Criteria

1. WHEN configuring a county THEN the system SHALL allow enabling/disabling ntsa, ccs, and ctsi flags
2. WHEN a student enrolls THEN the system SHALL determine the correct submission system based on county/course flags
3. WHEN multiple systems are enabled THEN the system SHALL submit to all enabled systems
4. WHEN no system is enabled THEN the system SHALL use manual certificate delivery
5. WHEN configuration changes THEN the system SHALL apply to new enrollments only

### Requirement 7: Error Handling and Retry Logic

**User Story:** As a system administrator, I want automatic retry logic for failed transmissions, so that temporary API failures don't require manual intervention.

#### Acceptance Criteria

1. WHEN a transmission fails with a network error THEN the system SHALL retry up to 3 times with exponential backoff
2. WHEN a transmission fails with an authentication error THEN the system SHALL not retry automatically
3. WHEN a transmission fails with a validation error THEN the system SHALL log the error and notify administrators
4. WHEN all retries are exhausted THEN the system SHALL mark the transmission as "failed" and send notification
5. WHEN a transmission succeeds after retry THEN the system SHALL update the status to "success"

### Requirement 8: Response Logging and Audit Trail

**User Story:** As an administrator, I want complete audit logs of all state API interactions, so that I can troubleshoot issues and maintain compliance records.

#### Acceptance Criteria

1. WHEN any state API is called THEN the system SHALL log the request payload, timestamp, and user
2. WHEN any state API responds THEN the system SHALL log the response payload, status code, and timestamp
3. WHEN viewing audit logs THEN the system SHALL display request/response pairs with search and filter capabilities
4. WHEN exporting audit logs THEN the system SHALL provide CSV and PDF export options
5. WHEN audit logs are 90 days old THEN the system SHALL archive them to long-term storage

### Requirement 9: Multi-School Support (Florida)

**User Story:** As a system administrator, I want to support multiple Florida school credentials, so that different courses can use different FLHSMV school accounts.

#### Acceptance Criteria

1. WHEN configuring Florida courses THEN the system SHALL allow selecting from multiple school credential sets
2. WHEN submitting to Florida THEN the system SHALL use the credentials associated with the course
3. WHEN school credentials are invalid THEN the system SHALL log the error and notify administrators
4. WHEN adding new school credentials THEN the system SHALL validate them against the FLHSMV API
5. WHEN credentials expire THEN the system SHALL send notification 30 days before expiration

### Requirement 10: Async Processing with Queue System

**User Story:** As a system, I want to process state transmissions asynchronously, so that course completion is not delayed by API calls.

#### Acceptance Criteria

1. WHEN a student completes a course THEN the system SHALL queue the transmission job immediately
2. WHEN processing transmission jobs THEN the system SHALL process them in FIFO order
3. WHEN a job fails THEN the system SHALL requeue it with exponential backoff
4. WHEN queue depth exceeds 100 THEN the system SHALL send alert to administrators
5. WHEN a job is older than 24 hours THEN the system SHALL escalate to high-priority queue
