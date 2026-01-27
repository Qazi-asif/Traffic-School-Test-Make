# Certificate Generation Functionality

## Files Copied/Updated

### 1. Certificate View
- **File**: `resources/views/certificate.blade.php`
- **Purpose**: Displays printable traffic school certificate
- **Features**: 
  - Professional certificate layout
  - Student information display
  - Course completion details
  - Signature sections

### 2. CertificateController Updates
- **File**: `app/Http/Controllers/CertificateController.php`
- **Added Method**: `generate(Request $request)`
- **Updated Method**: `generateCertificateNumber($stateCode = null)`

## Route Available
- `GET /certificate` - Generates certificate for authenticated user's completed course

## How It Works

### 1. User Authentication
- Checks if user is logged in
- Redirects to login if not authenticated

### 2. Course Completion Check
- Finds user's completed enrollment
- Checks for `status = 'completed'` OR `progress_percentage = 100`
- Redirects to dashboard if no completed courses

### 3. Data Collection
- Gathers user information from registration data
- Builds formatted address, phone, birth date, due date
- Gets course information from enrollment

### 4. Certificate Generation
- Creates unique certificate number: `CERT-YYYY-XXXXX`
- Populates certificate template with user data
- Returns printable certificate view

## Certificate Data Fields
- **Student Name**: From user's first_name + last_name
- **Student Address**: From mailing_address, city, state, zip
- **Completion Date**: From enrollment completion date
- **Course Type**: From course title
- **Score**: From final exam score
- **License Number**: From driver_license
- **Birth Date**: From birth_month/day/year
- **Citation Number**: From user registration
- **Due Date**: From due_month/day/year
- **Court**: From court_selected
- **County**: From user's state

## Usage
1. User completes a course
2. Navigate to `/certificate`
3. System generates and displays printable certificate
4. User can print or save the certificate

## Requirements
- User must be authenticated
- User must have at least one completed course enrollment
- All user registration fields should be populated for complete certificate

## Certificate Features
- Professional layout with borders and sections
- Highlighted fields with green background
- Photo placeholder section
- Signature areas for both student and school
- Court-acceptable format
- Print-friendly styling
