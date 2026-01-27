# Traffic School Platform - Admin User Manual

## Table of Contents

1. [Getting Started](#getting-started)
2. [Course Management](#course-management)
3. [Chapter Management](#chapter-management)
4. [Question Management](#question-management)
5. [Student Management](#student-management)
6. [State Integration Management](#state-integration-management)
7. [Payment & Revenue Management](#payment--revenue-management)
8. [Certificate Management](#certificate-management)
9. [System Administration](#system-administration)
10. [Troubleshooting](#troubleshooting)

---

## Getting Started

### Admin Dashboard Access
- **URL**: `/admin/dashboard`
- **Login**: Use your admin credentials
- **Navigation**: Left sidebar contains all admin functions

### User Roles
- **Super Admin**: Full system access
- **Admin**: Course and student management
- **Instructor**: Course content management
- **School Admin**: School-specific operations

---

## Course Management

### Creating a New Course

1. **Navigate to Course Management**
   - Go to `/admin/florida-courses` (or your state-specific course page)
   - Click **"Create Course"** button

2. **Fill Course Details**
   - **Course Type**: BDI, ADI, or TLSAE
   - **Delivery Type**: Internet, In Person, CD ROM, Video, DVD
   - **Title**: Descriptive course name
   - **Description**: Course overview (optional)
   - **Duration**: Minimum 240 minutes for compliance
   - **Min Pass Score**: Default 80%
   - **Price**: Course fee in dollars
   - **DICDS Course ID**: Required for state reporting
   - **Status**: Active/Inactive

3. **Save Course**
   - Click **"Save Course"**
   - Course appears in course list

### Editing Existing Courses

1. **Find Course**
   - Use filters: Course Type, Delivery Type
   - Use search box for specific courses

2. **Edit Course**
   - Click **Edit** button (pencil icon)
   - Modify any field except DICDS ID (contact support)
   - Click **"Save Course"**

### Copying Courses

1. **Select Source Course**
   - Click **Copy** button (copy icon) on existing course

2. **Configure Copy Options**
   - **Basic Info**: Always copied
   - **Chapters**: ✓ Copy chapter content
   - **Questions**: ✓ Copy chapter questions
   - **Final Exam**: ✓ Copy final exam questions
   - **Active Status**: Set new course as active

3. **Set New Course Details**
   - Change title (auto-adds "Copy")
   - Update DICDS Course ID (must be unique)
   - Adjust price if needed
   - Click **"Copy Course"**

### Course Filters & Search

- **Course Type Filter**: BDI, ADI, TLSAE
- **Delivery Type Filter**: Internet, In Person, etc.
- **Search**: Search by course title
- **Status**: Active/Inactive courses

---

## Chapter Management

### Accessing Chapter Builder

1. **From Course List**
   - Click **Chapters** button (book icon) on any course
   - Opens Chapter Builder for that course

### Creating Chapters

1. **Add New Chapter**
   - Click **"Add Chapter"** button
   - Fill chapter details:
     - **Title**: Chapter name
     - **Duration**: Chapter length in minutes
     - **Min Time**: Minimum time student must spend
     - **Video URL**: Optional video content
     - **Content**: Chapter text content
     - **Order Index**: Chapter sequence number
     - **Active**: Enable/disable chapter

2. **Save Chapter**
   - Click **"Save Chapter"**
   - Chapter appears in chapter list

### Editing Chapters

1. **Select Chapter**
   - Click **Edit** button (pencil icon) on chapter card

2. **Modify Content**
   - Update any field
   - **Content**: Use rich text for formatting
   - **Duration**: Ensure compliance with state requirements

3. **Save Changes**
   - Click **"Save Chapter"**

### Chapter Timer Settings

1. **Timer Configuration**
   - **Enable Chapter Timers**: ✓ Enforce timing
   - **Enforce Minimum Time**: ✓ Prevent rushing
   - Click **"Save Settings"**

2. **Per-Chapter Timing**
   - Set **Duration**: Total chapter time
   - Set **Min Time**: Minimum required time
   - Students cannot proceed until min time elapsed

### Managing Chapter Questions

1. **Access Questions**
   - Click **Questions** button (question mark icon) on chapter
   - Opens Question Manager for that chapter

---

## Question Management

### Question Manager Interface

**Location**: `/admin/chapters/{chapter_id}/questions`

### Creating Questions

1. **Add New Question**
   - Click **"Add Question"** button
   - Fill question form:
     - **Question Text**: The question content
     - **Question Type**: Multiple Choice or True/False
     - **Options**: One per line (for multiple choice)
     - **Correct Answer**: A, B, C, D, or True/False
     - **Explanation**: Optional explanation for answer
     - **Points**: Question value (default: 1)
     - **Order**: Question sequence

2. **Delaware Courses Only**
   - **Quiz Set**: Select Quiz Set 1 or Quiz Set 2
   - Students get Set 2 if they fail Set 1

3. **Save Question**
   - Click **"Save"**
   - Question appears in question list

### Importing Questions

#### From DOCX Files
1. **Export Sample Format**
   - Click **"Export Sample DOCX"**
   - Use this format for your questions

2. **Import DOCX**
   - Click **"Import"**
   - Select .docx file
   - Questions automatically parsed and imported

#### From TXT Files
1. **Prepare TXT File**
   - Format: Question text, then options A-D, mark correct with ***
   - Example:
     ```
     What is the speed limit in school zones?
     A. 15 mph ***
     B. 25 mph
     C. 35 mph
     D. 45 mph
     ```

2. **Import TXT**
   - Click **"Import"** or **"Import Final Exam (TXT)"**
   - Select .txt file
   - System processes and imports questions

### Bulk Question Management

1. **Select Multiple Questions**
   - Check boxes next to questions
   - Use **"Select All"** for all questions

2. **Bulk Actions**
   - **Delete Selected**: Remove multiple questions
   - **Delete All**: Clear all questions (use carefully!)

### Question Types

#### Multiple Choice
- 2-6 options (A, B, C, D, E, F)
- One correct answer
- Options displayed as radio buttons

#### True/False
- Two options: True, False
- Simple binary choice
- Good for fact-based questions

### Smart Paste Features

**Question Text Cleanup**:
- Automatically removes A), B), C) prefixes
- Removes numbered prefixes (1), 2), 3))
- Cleans extra whitespace

**Options Cleanup**:
- Removes option prefixes when pasting
- Detects *** markers for correct answers
- Auto-sets correct answer field

---

## Student Management

### User Management Interface

**Location**: `/admin/users`

### Adding New Students

1. **Create User Account**
   - Click **"Add User"** button
   - Fill user details:
     - **First Name**: Student's first name
     - **Last Name**: Student's last name
     - **Email**: Login email (must be unique)
     - **Password**: Initial password
     - **Role**: Select "Student"

2. **Save User**
   - Click **"Save"**
   - User can now log in and enroll

### Managing Existing Users

1. **Find Users**
   - Browse user list
   - Search by name or email

2. **Edit User**
   - Click **"Edit"** button
   - Modify any field except email (contact support)
   - Leave password blank to keep current
   - Click **"Update"**

3. **Delete User**
   - Click **"Delete"** button
   - Confirm deletion
   - **Warning**: This removes all user data

### User Roles

- **Student**: Can enroll and take courses
- **Instructor**: Can manage course content
- **Admin**: Can manage users and courses
- **Super Admin**: Full system access

### Student Enrollments

**Location**: `/admin/enrollments`

1. **View Enrollments**
   - See all student course enrollments
   - Filter by course, status, date

2. **Enrollment Details**
   - Click enrollment to view progress
   - See chapter completion
   - View quiz attempts and scores

3. **Manual Enrollment**
   - Select student and course
   - Set enrollment date
   - Apply any discounts/coupons

---

## State Integration Management

### Florida DICDS Integration

**Location**: `/admin/fl-transmissions`

#### Viewing Transmissions
1. **Transmission List**
   - Shows all certificate submissions to Florida DHSMV
   - Status: Pending, Success, Error
   - Retry count and timestamps

2. **Transmission Details**
   - Click transmission to view full details
   - See request payload and response
   - Error messages if failed

#### Manual Transmission
1. **Send Individual**
   - Click **"Send"** on pending transmission
   - System attempts immediate submission

2. **Retry Failed**
   - Click **"Retry"** on failed transmission
   - Increments retry count
   - Updates with new response

#### Bulk Operations
1. **Send All Pending**
   - Process all pending transmissions
   - Useful for batch processing

2. **Retry All Failed**
   - Retry all failed transmissions
   - Good for system recovery

### California TVCC Integration

**Location**: `/admin/ca-transmissions`

#### TVCC Configuration
- **Endpoint**: `https://xsg.dmv.ca.gov/tvcc/tvccservice`
- **Authentication**: Stored in database
- **Method**: SOAP/REST API

#### Managing TVCC Submissions
1. **View Submissions**
   - All California certificate submissions
   - Court code mappings
   - Response tracking

2. **Court Code Management**
   - Map citation courts to TVCC codes
   - Update court information
   - Validate court codes

### Nevada NTSA Integration

**Location**: `/admin/nv-ntsa-transmissions`

#### NTSA Configuration
- **Endpoint**: `https://secure.ntsa.us/cgi-bin/register.cgi`
- **Method**: HTTP POST form submission
- **School Name**: "DUMMIES TRAFFIC SCHOOL.COM"

#### Managing NTSA Submissions
1. **Registration Tracking**
   - Student registrations with NTSA
   - Completion callbacks
   - Score reporting

### State Stamps Management

**Location**: `/admin/state-stamps`

#### Adding State Stamps
1. **Create New Stamp**
   - Click **"Add State Stamp"**
   - Select state code
   - Upload logo image (PNG recommended, 200x200px)
   - Set description
   - Mark as active

2. **Logo Requirements**
   - PNG format with transparent background
   - 200x200 pixels recommended
   - Clear, professional appearance

#### Managing Existing Stamps
1. **Edit Stamp**
   - Click **Edit** button
   - Update logo or description
   - Cannot change state code

2. **Activate/Deactivate**
   - Toggle active status
   - Inactive stamps don't appear on certificates

---

## Payment & Revenue Management

### Payment Gateway Configuration

**Location**: `/admin/payment-gateways`

#### Supported Gateways
- **Stripe**: Credit/debit cards
- **PayPal**: PayPal accounts
- **Authorize.Net**: Merchant processing

#### Gateway Settings
1. **Configure Stripe**
   - Add publishable key
   - Add secret key
   - Set webhook endpoints
   - Test mode toggle

2. **Configure PayPal**
   - Client ID and secret
   - Sandbox/live mode
   - IPN settings

### Revenue Reports

**Location**: `/admin/revenue`

#### Report Types
1. **Daily Revenue**
   - Sales by day
   - Payment method breakdown
   - Refund tracking

2. **Course Revenue**
   - Revenue by course
   - Enrollment trends
   - Popular courses

3. **State Revenue**
   - Revenue by state
   - Compliance fees
   - Transmission costs

#### Generating Reports
1. **Select Date Range**
   - Start and end dates
   - Preset ranges (week, month, quarter)

2. **Choose Filters**
   - Payment method
   - Course type
   - State

3. **Export Options**
   - PDF reports
   - CSV data export
   - Email delivery

### Merchant Management

**Location**: `/admin/merchants`

#### Merchant Accounts
1. **Add Merchant**
   - Merchant name and details
   - Fee structure
   - Settlement terms

2. **Fee Configuration**
   - Transaction fees
   - Monthly fees
   - Chargeback fees

3. **Reconciliation**
   - Match payments to deposits
   - Track settlement timing
   - Handle disputes

---

## Certificate Management

### Certificate Templates

**Location**: `/admin/certificates`

#### Template Management
1. **Create Template**
   - Upload background image
   - Set text positions
   - Configure fonts and colors
   - Add state stamps

2. **Template Fields**
   - Student name position
   - Course name position
   - Completion date
   - Certificate number
   - State seal placement

#### State-Specific Templates
- **Florida**: DHSMV requirements
- **California**: DMV compliance
- **Nevada**: NTSA format
- **Texas**: State-specific layout

### Certificate Generation

#### Automatic Generation
- Triggered on course completion
- Uses appropriate state template
- Includes verification QR code
- Stores in certificate inventory

#### Manual Generation
1. **Generate Individual**
   - Select student and course
   - Choose template
   - Generate and download

2. **Bulk Generation**
   - Select multiple completions
   - Batch generate certificates
   - Email delivery options

### Certificate Lookup

**Location**: `/admin/certificate-lookup`

#### Verification System
1. **Search Certificates**
   - By certificate number
   - By student name
   - By completion date

2. **Verification Details**
   - Certificate authenticity
   - Course information
   - Completion status
   - QR code validation

#### Public Lookup
- **URL**: `/verify/{certificate_number}`
- Allows courts/employers to verify
- Shows basic completion info
- Protects student privacy

---

## System Administration

### User Access Management

**Location**: `/admin/user-access`

#### Permission Levels
1. **Super Admin**
   - Full system access
   - User management
   - System configuration

2. **Admin**
   - Course management
   - Student management
   - Reports access

3. **Instructor**
   - Course content editing
   - Student progress viewing
   - Limited admin functions

4. **School Admin**
   - School-specific data
   - Local user management
   - School reports

### Security Settings

**Location**: `/admin/security-dashboard`

#### Password Policies
1. **Requirements**
   - Minimum length: 8 characters
   - Must include: uppercase, lowercase, number
   - Special character recommended

2. **Expiration**
   - Admin passwords: 90 days
   - Student passwords: No expiration
   - Force change on first login

#### Login Security
1. **Failed Login Protection**
   - Lock account after 5 failed attempts
   - 15-minute lockout period
   - Email notification to admin

2. **Session Management**
   - Session timeout: 2 hours inactive
   - Concurrent session limits
   - Force logout on password change

### System Monitoring

#### Performance Monitoring
1. **Server Health**
   - CPU usage
   - Memory usage
   - Disk space
   - Database performance

2. **Application Metrics**
   - Page load times
   - Error rates
   - User activity
   - Course completion rates

#### Log Management
1. **Error Logs**
   - Application errors
   - Database errors
   - Payment failures
   - State transmission errors

2. **Activity Logs**
   - User logins
   - Course enrollments
   - Certificate generations
   - Admin actions

### Backup & Recovery

#### Automated Backups
1. **Database Backups**
   - Daily full backups
   - Hourly incremental backups
   - 30-day retention

2. **File Backups**
   - Certificate files
   - Course content
   - User uploads
   - System configurations

#### Recovery Procedures
1. **Database Recovery**
   - Point-in-time recovery
   - Full system restore
   - Selective table recovery

2. **File Recovery**
   - Individual file restore
   - Bulk file recovery
   - Version rollback

---

## Troubleshooting

### Common Issues

#### Course Creation Problems

**Issue**: DICDS Course ID already exists
- **Solution**: Use unique DICDS ID for each course
- **Check**: Existing course list for duplicates

**Issue**: Course not appearing for enrollment
- **Solution**: Verify course is marked as "Active"
- **Check**: Course status in course management

#### Question Import Issues

**Issue**: DOCX import fails
- **Solution**: Use exported sample format exactly
- **Check**: File format and question structure

**Issue**: TXT import creates malformed questions
- **Solution**: Verify TXT format with proper A), B), C) structure
- **Check**: Correct answer marked with ***

#### State Transmission Failures

**Issue**: Florida DICDS transmission fails
- **Solution**: Check DICDS credentials and course ID
- **Check**: Student data completeness (DL, DOB, etc.)

**Issue**: California TVCC authentication error
- **Solution**: Update TVCC password in database
- **Check**: Court code mapping accuracy

#### Payment Processing Issues

**Issue**: Stripe payments failing
- **Solution**: Verify API keys and webhook configuration
- **Check**: Test mode vs live mode settings

**Issue**: PayPal IPN not working
- **Solution**: Check IPN URL configuration
- **Check**: PayPal account settings

### Error Codes

#### State Integration Errors

**Florida DICDS**:
- `E001`: Invalid course ID
- `E002`: Student data incomplete
- `E003`: Authentication failure
- `E004`: Duplicate submission

**California TVCC**:
- `T001`: Invalid court code
- `T002`: Authentication denied
- `T003`: Validation failed
- `T004`: Service unavailable

#### System Errors

**Database**:
- `DB001`: Connection timeout
- `DB002`: Query execution failed
- `DB003`: Constraint violation
- `DB004`: Deadlock detected

**Application**:
- `APP001`: File upload failed
- `APP002`: Email delivery failed
- `APP003`: PDF generation error
- `APP004`: Session expired

### Getting Help

#### Internal Support
1. **Check Logs**
   - Review error logs first
   - Note exact error messages
   - Check timestamps

2. **System Status**
   - Verify all services running
   - Check database connectivity
   - Confirm external API status

#### External Support
1. **State Agencies**
   - **Florida DHSMV**: (850) 617-2000
   - **California DMV**: (916) 657-6437
   - **Nevada NTSA**: Contact through portal

2. **Payment Processors**
   - **Stripe Support**: support@stripe.com
   - **PayPal Support**: Through merchant portal
   - **Authorize.Net**: (877) 447-3938

#### Documentation
- **Laravel Documentation**: https://laravel.com/docs
- **State Integration Guides**: See individual state folders
- **API Documentation**: `/api/documentation`

---

## Quick Reference

### Essential URLs
- **Admin Dashboard**: `/admin/dashboard`
- **Course Management**: `/admin/florida-courses`
- **User Management**: `/admin/users`
- **State Transmissions**: `/admin/fl-transmissions`
- **Revenue Reports**: `/admin/revenue`
- **Certificate Lookup**: `/admin/certificate-lookup`

### Keyboard Shortcuts
- **Ctrl+S**: Save current form
- **Ctrl+N**: New item (where applicable)
- **Ctrl+F**: Search/filter
- **Esc**: Close modal/cancel action

### Best Practices
1. **Always backup before major changes**
2. **Test in staging environment first**
3. **Keep DICDS course IDs organized**
4. **Monitor state transmission success rates**
5. **Regular certificate inventory audits**
6. **Maintain current payment gateway credentials**

---

*Last Updated: December 2025*
*Version: 1.0*