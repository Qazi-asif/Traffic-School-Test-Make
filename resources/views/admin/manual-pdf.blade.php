<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Traffic School Platform - Admin User Manual</title>
    <style>
        @page {
            margin: 1in;
            @top-center {
                content: "Admin User Manual";
                font-size: 10pt;
                color: #666;
            }
            @bottom-center {
                content: "Page " counter(page);
                font-size: 10pt;
                color: #666;
            }
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            font-size: 11pt;
            text-align: left;
            margin: 0;
            padding: 0;
        }
        
        .cover-page {
            text-align: center;
            padding-top: 200px;
            page-break-after: always;
        }
        
        .cover-title {
            font-size: 28pt;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 20px;
        }
        
        .cover-subtitle {
            font-size: 18pt;
            color: #7f8c8d;
            margin-bottom: 40px;
        }
        
        .cover-info {
            font-size: 12pt;
            color: #95a5a6;
            margin-top: 100px;
        }
        
        .toc {
            page-break-after: always;
        }
        
        .toc h1 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            margin-bottom: 30px;
        }
        
        .toc ul {
            list-style: none;
            padding-left: 0;
        }
        
        .toc li {
            margin-bottom: 8px;
            padding-left: 20px;
        }
        
        .toc li.level-1 {
            font-weight: bold;
            margin-top: 15px;
            padding-left: 0;
        }
        
        .toc li.level-2 {
            padding-left: 20px;
        }
        
        .toc li.level-3 {
            padding-left: 40px;
            font-size: 10pt;
        }
        
        h1 {
            color: #2c3e50;
            font-size: 20pt;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            margin-top: 40px;
            margin-bottom: 20px;
            page-break-before: always;
            text-align: left;
            font-weight: bold;
        }
        
        h1:first-of-type {
            page-break-before: auto;
        }
        
        h2 {
            color: #34495e;
            font-size: 16pt;
            margin-top: 30px;
            margin-bottom: 15px;
            border-left: 4px solid #3498db;
            padding-left: 15px;
            text-align: left;
            font-weight: bold;
        }
        
        h3 {
            color: #7f8c8d;
            font-size: 14pt;
            margin-top: 25px;
            margin-bottom: 10px;
            text-align: left;
            font-weight: bold;
        }
        
        h4 {
            color: #95a5a6;
            font-size: 12pt;
            margin-top: 20px;
            margin-bottom: 8px;
            text-align: left;
            font-weight: bold;
        }
        
        p {
            margin-bottom: 12px;
            text-align: left;
            line-height: 1.6;
        }
        
        ul, ol {
            margin-bottom: 15px;
            padding-left: 25px;
            text-align: left;
        }
        
        li {
            margin-bottom: 5px;
            text-align: left;
            line-height: 1.5;
        }
        
        code {
            background: #f8f9fa;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 10pt;
            color: #e74c3c;
        }
        
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #3498db;
            overflow-x: auto;
            font-family: 'Courier New', monospace;
            font-size: 9pt;
            line-height: 1.4;
            margin: 15px 0;
        }
        
        .alert {
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            border-left: 4px solid;
        }
        
        .alert-info {
            background: #e8f4fd;
            border-color: #3498db;
            color: #2980b9;
        }
        
        .alert-warning {
            background: #fef9e7;
            border-color: #f39c12;
            color: #d68910;
        }
        
        .alert-danger {
            background: #fdf2f2;
            border-color: #e74c3c;
            color: #c0392b;
        }
        
        .alert-success {
            background: #eafaf1;
            border-color: #27ae60;
            color: #229954;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 10pt;
        }
        
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        th {
            background: #f8f9fa;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .no-break {
            page-break-inside: avoid;
        }
        
        .url {
            color: #3498db;
            text-decoration: underline;
        }
        
        .step-number {
            background: #3498db;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: inline-block;
            text-align: center;
            font-weight: bold;
            margin-right: 10px;
        }
        
        .highlight {
            background: #fff3cd;
            padding: 2px 4px;
            border-radius: 3px;
        }
        
        .footer-info {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9pt;
            color: #666;
            padding: 10px;
        }
    </style>
</head>
<body>
    <!-- Cover Page -->
    <div class="cover-page">
        <div class="cover-title">Traffic School Platform</div>
        <div class="cover-subtitle">Administrator User Manual</div>
        <div class="cover-info">
            <p>Comprehensive Guide for System Administration</p>
            <p>Version 1.0 | December 2025</p>
        </div>
    </div>

    <!-- Table of Contents -->
    <div class="toc">
        <h1>Table of Contents</h1>
        <ul>
            <li class="level-1">1. Getting Started</li>
            <li class="level-2">Admin Dashboard Access</li>
            <li class="level-2">User Roles</li>
            
            <li class="level-1">2. Course Management</li>
            <li class="level-2">Creating a New Course</li>
            <li class="level-2">Editing Existing Courses</li>
            <li class="level-2">Copying Courses</li>
            <li class="level-2">Course Filters & Search</li>
            
            <li class="level-1">3. Chapter Management</li>
            <li class="level-2">Accessing Chapter Builder</li>
            <li class="level-2">Creating Chapters</li>
            <li class="level-2">Editing Chapters</li>
            <li class="level-2">Chapter Timer Settings</li>
            
            <li class="level-1">4. Question Management</li>
            <li class="level-2">Question Manager Interface</li>
            <li class="level-2">Creating Questions</li>
            <li class="level-2">Importing Questions</li>
            <li class="level-2">Bulk Question Management</li>
            
            <li class="level-1">5. Student Management</li>
            <li class="level-2">User Management Interface</li>
            <li class="level-2">Adding New Students</li>
            <li class="level-2">Managing Existing Users</li>
            <li class="level-2">Student Enrollments</li>
            
            <li class="level-1">6. State Integration Management</li>
            <li class="level-2">Florida DICDS Integration</li>
            <li class="level-2">California TVCC Integration</li>
            <li class="level-2">Nevada NTSA Integration</li>
            <li class="level-2">State Stamps Management</li>
            
            <li class="level-1">7. Payment & Revenue Management</li>
            <li class="level-2">Payment Gateway Configuration</li>
            <li class="level-2">Revenue Reports</li>
            <li class="level-2">Merchant Management</li>
            
            <li class="level-1">8. Certificate Management</li>
            <li class="level-2">Certificate Templates</li>
            <li class="level-2">Certificate Generation</li>
            <li class="level-2">Certificate Lookup</li>
            
            <li class="level-1">9. System Administration</li>
            <li class="level-2">User Access Management</li>
            <li class="level-2">Security Settings</li>
            <li class="level-2">System Monitoring</li>
            <li class="level-2">Backup & Recovery</li>
            
            <li class="level-1">10. Troubleshooting</li>
            <li class="level-2">Common Issues</li>
            <li class="level-2">Error Codes</li>
            <li class="level-2">Getting Help</li>
            <li class="level-2">Quick Reference</li>
        </ul>
    </div>

    <!-- Main Content -->
    <h1>Getting Started</h1>
    
    <h2>Admin Dashboard Access</h2>
    <ul>
        <li><strong>URL:</strong> <span class="url">/admin/dashboard</span></li>
        <li><strong>Login:</strong> Use your admin credentials</li>
        <li><strong>Navigation:</strong> Left sidebar contains all admin functions</li>
    </ul>

    <h2>User Roles</h2>
    <ul>
        <li><strong>Super Admin:</strong> Full system access</li>
        <li><strong>Admin:</strong> Course and student management</li>
        <li><strong>Instructor:</strong> Course content management</li>
        <li><strong>School Admin:</strong> School-specific operations</li>
    </ul>

    <h1>Course Management</h1>

    <h2>Creating a New Course</h2>
    
    <h3>1. Navigate to Course Management</h3>
    <ul>
        <li>Go to <span class="url">/admin/florida-courses</span> (or your state-specific course page)</li>
        <li>Click <strong>"Create Course"</strong> button</li>
    </ul>

    <h3>2. Fill Course Details</h3>
    <ul>
        <li><strong>Course Type:</strong> BDI, ADI, or TLSAE</li>
        <li><strong>Delivery Type:</strong> Internet, In Person, CD ROM, Video, DVD</li>
        <li><strong>Title:</strong> Descriptive course name</li>
        <li><strong>Description:</strong> Course overview (optional)</li>
        <li><strong>Duration:</strong> Minimum 240 minutes for compliance</li>
        <li><strong>Min Pass Score:</strong> Default 80%</li>
        <li><strong>Price:</strong> Course fee in dollars</li>
        <li><strong>DICDS Course ID:</strong> Required for state reporting</li>
        <li><strong>Status:</strong> Active/Inactive</li>
    </ul>

    <h3>3. Save Course</h3>
    <ul>
        <li>Click <strong>"Save Course"</strong></li>
        <li>Course appears in course list</li>
    </ul>

    <h2>Editing Existing Courses</h2>

    <h3>1. Find Course</h3>
    <ul>
        <li>Use filters: Course Type, Delivery Type</li>
        <li>Use search box for specific courses</li>
    </ul>

    <h3>2. Edit Course</h3>
    <ul>
        <li>Click <strong>Edit</strong> button (pencil icon)</li>
        <li>Modify any field except DICDS ID (contact support)</li>
        <li>Click <strong>"Save Course"</strong></li>
    </ul>

    <h2>Copying Courses</h2>

    <h3>1. Select Source Course</h3>
    <ul>
        <li>Click <strong>Copy</strong> button (copy icon) on existing course</li>
    </ul>

    <h3>2. Configure Copy Options</h3>
    <ul>
        <li><strong>Basic Info:</strong> Always copied</li>
        <li><strong>Chapters:</strong> ✓ Copy chapter content</li>
        <li><strong>Questions:</strong> ✓ Copy chapter questions</li>
        <li><strong>Final Exam:</strong> ✓ Copy final exam questions</li>
        <li><strong>Active Status:</strong> Set new course as active</li>
    </ul>

    <h3>3. Set New Course Details</h3>
    <ul>
        <li>Change title (auto-adds "Copy")</li>
        <li>Update DICDS Course ID (must be unique)</li>
        <li>Adjust price if needed</li>
        <li>Click <strong>"Copy Course"</strong></li>
    </ul>

    <h2>Course Filters & Search</h2>
    <ul>
        <li><strong>Course Type Filter:</strong> BDI, ADI, TLSAE</li>
        <li><strong>Delivery Type Filter:</strong> Internet, In Person, etc.</li>
        <li><strong>Search:</strong> Search by course title</li>
        <li><strong>Status:</strong> Active/Inactive courses</li>
    </ul>

    <h1>Chapter Management</h1>

    <h2>Accessing Chapter Builder</h2>

    <h3>1. From Course List</h3>
    <ul>
        <li>Click <strong>Chapters</strong> button (book icon) on any course</li>
        <li>Opens Chapter Builder for that course</li>
    </ul>

    <h2>Creating Chapters</h2>

    <h3>1. Add New Chapter</h3>
    <ul>
        <li>Click <strong>"Add Chapter"</strong> button</li>
        <li>Fill chapter details:
            <ul>
                <li><strong>Title:</strong> Chapter name</li>
                <li><strong>Duration:</strong> Chapter length in minutes</li>
                <li><strong>Min Time:</strong> Minimum time student must spend</li>
                <li><strong>Video URL:</strong> Optional video content</li>
                <li><strong>Content:</strong> Chapter text content</li>
                <li><strong>Order Index:</strong> Chapter sequence number</li>
                <li><strong>Active:</strong> Enable/disable chapter</li>
            </ul>
        </li>
    </ul>

    <h3>2. Save Chapter</h3>
    <ul>
        <li>Click <strong>"Save Chapter"</strong></li>
        <li>Chapter appears in chapter list</li>
    </ul>

    <h2>Editing Chapters</h2>

    <h3>1. Select Chapter</h3>
    <ul>
        <li>Click <strong>Edit</strong> button (pencil icon) on chapter card</li>
    </ul>

    <h3>2. Modify Content</h3>
    <ul>
        <li>Update any field</li>
        <li><strong>Content:</strong> Use rich text for formatting</li>
        <li><strong>Duration:</strong> Ensure compliance with state requirements</li>
    </ul>

    <h3>3. Save Changes</h3>
    <ul>
        <li>Click <strong>"Save Chapter"</strong></li>
    </ul>

    <h2>Chapter Timer Settings</h2>

    <h3>1. Timer Configuration</h3>
    <ul>
        <li><strong>Enable Chapter Timers:</strong> ✓ Enforce timing</li>
        <li><strong>Enforce Minimum Time:</strong> ✓ Prevent rushing</li>
        <li>Click <strong>"Save Settings"</strong></li>
    </ul>

    <h3>2. Per-Chapter Timing</h3>
    <ul>
        <li>Set <strong>Duration:</strong> Total chapter time</li>
        <li>Set <strong>Min Time:</strong> Minimum required time</li>
        <li>Students cannot proceed until min time elapsed</li>
    </ul>

    <h2>Managing Chapter Questions</h2>

    <h3>1. Access Questions</h3>
    <ul>
        <li>Click <strong>Questions</strong> button (question mark icon) on chapter</li>
        <li>Opens Question Manager for that chapter</li>
    </ul>

    <h1>Question Management</h1>

    <h2>Question Manager Interface</h2>
    <p><strong>Location:</strong> <span class="url">/admin/chapters/{chapter_id}/questions</span></p>

    <h2>Creating Questions</h2>

    <h3>1. Add New Question</h3>
    <ul>
        <li>Click <strong>"Add Question"</strong> button</li>
        <li>Fill question form:
            <ul>
                <li><strong>Question Text:</strong> The question content</li>
                <li><strong>Question Type:</strong> Multiple Choice or True/False</li>
                <li><strong>Options:</strong> One per line (for multiple choice)</li>
                <li><strong>Correct Answer:</strong> A, B, C, D, or True/False</li>
                <li><strong>Explanation:</strong> Optional explanation for answer</li>
                <li><strong>Points:</strong> Question value (default: 1)</li>
                <li><strong>Order:</strong> Question sequence</li>
            </ul>
        </li>
    </ul>

    <h3>2. Delaware Courses Only</h3>
    <ul>
        <li><strong>Quiz Set:</strong> Select Quiz Set 1 or Quiz Set 2</li>
        <li>Students get Set 2 if they fail Set 1</li>
    </ul>

    <h3>3. Save Question</h3>
    <ul>
        <li>Click <strong>"Save"</strong></li>
        <li>Question appears in question list</li>
    </ul>

    <h2>Importing Questions</h2>

    <h3>From DOCX Files</h3>
    <ol>
        <li><strong>Export Sample Format</strong>
            <ul>
                <li>Click <strong>"Export Sample DOCX"</strong></li>
                <li>Use this format for your questions</li>
            </ul>
        </li>
        <li><strong>Import DOCX</strong>
            <ul>
                <li>Click <strong>"Import"</strong></li>
                <li>Select .docx file</li>
                <li>Questions automatically parsed and imported</li>
            </ul>
        </li>
    </ol>

    <h3>From TXT Files</h3>
    <ol>
        <li><strong>Prepare TXT File</strong>
            <ul>
                <li>Format: Question text, then options A-D, mark correct with ***</li>
                <li>Example:</li>
            </ul>
            <pre>What is the speed limit in school zones?
A. 15 mph ***
B. 25 mph
C. 35 mph
D. 45 mph</pre>
        </li>
        <li><strong>Import TXT</strong>
            <ul>
                <li>Click <strong>"Import"</strong> or <strong>"Import Final Exam (TXT)"</strong></li>
                <li>Select .txt file</li>
                <li>System processes and imports questions</li>
            </ul>
        </li>
    </ol>

    <h2>Bulk Question Management</h2>

    <h3>1. Select Multiple Questions</h3>
    <ul>
        <li>Check boxes next to questions</li>
        <li>Use <strong>"Select All"</strong> for all questions</li>
    </ul>

    <h3>2. Bulk Actions</h3>
    <ul>
        <li><strong>Delete Selected:</strong> Remove multiple questions</li>
        <li><strong>Delete All:</strong> Clear all questions (use carefully!)</li>
    </ul>

    <h2>Question Types</h2>

    <h3>Multiple Choice</h3>
    <ul>
        <li>2-6 options (A, B, C, D, E, F)</li>
        <li>One correct answer</li>
        <li>Options displayed as radio buttons</li>
    </ul>

    <h3>True/False</h3>
    <ul>
        <li>Two options: True, False</li>
        <li>Simple binary choice</li>
        <li>Good for fact-based questions</li>
    </ul>

    <h2>Smart Paste Features</h2>

    <div class="alert alert-info">
        <strong>Question Text Cleanup:</strong>
        <ul>
            <li>Automatically removes A), B), C) prefixes</li>
            <li>Removes numbered prefixes (1), 2), 3))</li>
            <li>Cleans extra whitespace</li>
        </ul>
    </div>

    <div class="alert alert-info">
        <strong>Options Cleanup:</strong>
        <ul>
            <li>Removes option prefixes when pasting</li>
            <li>Detects *** markers for correct answers</li>
            <li>Auto-sets correct answer field</li>
        </ul>
    </div>

    <h1>Student Management</h1>

    <h2>User Management Interface</h2>
    <p><strong>Location:</strong> <span class="url">/admin/users</span></p>

    <h2>Adding New Students</h2>

    <h3>1. Create User Account</h3>
    <ul>
        <li>Click <strong>"Add User"</strong> button</li>
        <li>Fill user details:
            <ul>
                <li><strong>First Name:</strong> Student's first name</li>
                <li><strong>Last Name:</strong> Student's last name</li>
                <li><strong>Email:</strong> Login email (must be unique)</li>
                <li><strong>Password:</strong> Initial password</li>
                <li><strong>Role:</strong> Select "Student"</li>
            </ul>
        </li>
    </ul>

    <h3>2. Save User</h3>
    <ul>
        <li>Click <strong>"Save"</strong></li>
        <li>User can now log in and enroll</li>
    </ul>

    <h2>Managing Existing Users</h2>

    <h3>1. Find Users</h3>
    <ul>
        <li>Browse user list</li>
        <li>Search by name or email</li>
    </ul>

    <h3>2. Edit User</h3>
    <ul>
        <li>Click <strong>"Edit"</strong> button</li>
        <li>Modify any field except email (contact support)</li>
        <li>Leave password blank to keep current</li>
        <li>Click <strong>"Update"</strong></li>
    </ul>

    <h3>3. Delete User</h3>
    <ul>
        <li>Click <strong>"Delete"</strong> button</li>
        <li>Confirm deletion</li>
        <li><strong>Warning:</strong> This removes all user data</li>
    </ul>

    <h2>User Roles</h2>
    <ul>
        <li><strong>Student:</strong> Can enroll and take courses</li>
        <li><strong>Instructor:</strong> Can manage course content</li>
        <li><strong>Admin:</strong> Can manage users and courses</li>
        <li><strong>Super Admin:</strong> Full system access</li>
    </ul>

    <h2>Student Enrollments</h2>
    <p><strong>Location:</strong> <span class="url">/admin/enrollments</span></p>

    <h3>1. View Enrollments</h3>
    <ul>
        <li>See all student course enrollments</li>
        <li>Filter by course, status, date</li>
    </ul>

    <h3>2. Enrollment Details</h3>
    <ul>
        <li>Click enrollment to view progress</li>
        <li>See chapter completion</li>
        <li>View quiz attempts and scores</li>
    </ul>

    <h3>3. Manual Enrollment</h3>
    <ul>
        <li>Select student and course</li>
        <li>Set enrollment date</li>
        <li>Apply any discounts/coupons</li>
    </ul>

    <h1>State Integration Management</h1>

    <h2>Florida DICDS Integration</h2>
    <p><strong>Location:</strong> <span class="url">/admin/fl-transmissions</span></p>

    <h3>Viewing Transmissions</h3>
    <ol>
        <li><strong>Transmission List</strong>
            <ul>
                <li>Shows all certificate submissions to Florida DHSMV</li>
                <li>Status: Pending, Success, Error</li>
                <li>Retry count and timestamps</li>
            </ul>
        </li>
        <li><strong>Transmission Details</strong>
            <ul>
                <li>Click transmission to view full details</li>
                <li>See request payload and response</li>
                <li>Error messages if failed</li>
            </ul>
        </li>
    </ol>

    <h3>Manual Transmission</h3>
    <ol>
        <li><strong>Send Individual</strong>
            <ul>
                <li>Click <strong>"Send"</strong> on pending transmission</li>
                <li>System attempts immediate submission</li>
            </ul>
        </li>
        <li><strong>Retry Failed</strong>
            <ul>
                <li>Click <strong>"Retry"</strong> on failed transmission</li>
                <li>Increments retry count</li>
                <li>Updates with new response</li>
            </ul>
        </li>
    </ol>

    <h3>Bulk Operations</h3>
    <ol>
        <li><strong>Send All Pending</strong>
            <ul>
                <li>Process all pending transmissions</li>
                <li>Useful for batch processing</li>
            </ul>
        </li>
        <li><strong>Retry All Failed</strong>
            <ul>
                <li>Retry all failed transmissions</li>
                <li>Good for system recovery</li>
            </ul>
        </li>
    </ol>

    <h2>California TVCC Integration</h2>
    <p><strong>Location:</strong> <span class="url">/admin/ca-transmissions</span></p>

    <h3>TVCC Configuration</h3>
    <ul>
        <li><strong>Endpoint:</strong> <span class="url">https://xsg.dmv.ca.gov/tvcc/tvccservice</span></li>
        <li><strong>Authentication:</strong> Stored in database</li>
        <li><strong>Method:</strong> SOAP/REST API</li>
    </ul>

    <h3>Managing TVCC Submissions</h3>
    <ol>
        <li><strong>View Submissions</strong>
            <ul>
                <li>All California certificate submissions</li>
                <li>Court code mappings</li>
                <li>Response tracking</li>
            </ul>
        </li>
        <li><strong>Court Code Management</strong>
            <ul>
                <li>Map citation courts to TVCC codes</li>
                <li>Update court information</li>
                <li>Validate court codes</li>
            </ul>
        </li>
    </ol>

    <h2>Nevada NTSA Integration</h2>
    <p><strong>Location:</strong> <span class="url">/admin/nv-ntsa-transmissions</span></p>

    <h3>NTSA Configuration</h3>
    <ul>
        <li><strong>Endpoint:</strong> <span class="url">https://secure.ntsa.us/cgi-bin/register.cgi</span></li>
        <li><strong>Method:</strong> HTTP POST form submission</li>
        <li><strong>School Name:</strong> "DUMMIES TRAFFIC SCHOOL.COM"</li>
    </ul>

    <h3>Managing NTSA Submissions</h3>
    <ol>
        <li><strong>Registration Tracking</strong>
            <ul>
                <li>Student registrations with NTSA</li>
                <li>Completion callbacks</li>
                <li>Score reporting</li>
            </ul>
        </li>
    </ol>

    <h2>State Stamps Management</h2>
    <p><strong>Location:</strong> <span class="url">/admin/state-stamps</span></p>

    <h3>Adding State Stamps</h3>
    <ol>
        <li><strong>Create New Stamp</strong>
            <ul>
                <li>Click <strong>"Add State Stamp"</strong></li>
                <li>Select state code</li>
                <li>Upload logo image (PNG recommended, 200x200px)</li>
                <li>Set description</li>
                <li>Mark as active</li>
            </ul>
        </li>
        <li><strong>Logo Requirements</strong>
            <ul>
                <li>PNG format with transparent background</li>
                <li>200x200 pixels recommended</li>
                <li>Clear, professional appearance</li>
            </ul>
        </li>
    </ol>

    <h3>Managing Existing Stamps</h3>
    <ol>
        <li><strong>Edit Stamp</strong>
            <ul>
                <li>Click <strong>Edit</strong> button</li>
                <li>Update logo or description</li>
                <li>Cannot change state code</li>
            </ul>
        </li>
        <li><strong>Activate/Deactivate</strong>
            <ul>
                <li>Toggle active status</li>
                <li>Inactive stamps don't appear on certificates</li>
            </ul>
        </li>
    </ol>

    <h1>Payment & Revenue Management</h1>

    <h2>Payment Gateway Configuration</h2>
    <p><strong>Location:</strong> <span class="url">/admin/payment-gateways</span></p>

    <h3>Supported Gateways</h3>
    <ul>
        <li><strong>Stripe:</strong> Credit/debit cards</li>
        <li><strong>PayPal:</strong> PayPal accounts</li>
        <li><strong>Authorize.Net:</strong> Merchant processing</li>
    </ul>

    <h3>Gateway Settings</h3>
    <ol>
        <li><strong>Configure Stripe</strong>
            <ul>
                <li>Add publishable key</li>
                <li>Add secret key</li>
                <li>Set webhook endpoints</li>
                <li>Test mode toggle</li>
            </ul>
        </li>
        <li><strong>Configure PayPal</strong>
            <ul>
                <li>Client ID and secret</li>
                <li>Sandbox/live mode</li>
                <li>IPN settings</li>
            </ul>
        </li>
    </ol>

    <h2>Revenue Reports</h2>
    <p><strong>Location:</strong> <span class="url">/admin/revenue</span></p>

    <h3>Report Types</h3>
    <ol>
        <li><strong>Daily Revenue</strong>
            <ul>
                <li>Sales by day</li>
                <li>Payment method breakdown</li>
                <li>Refund tracking</li>
            </ul>
        </li>
        <li><strong>Course Revenue</strong>
            <ul>
                <li>Revenue by course</li>
                <li>Enrollment trends</li>
                <li>Popular courses</li>
            </ul>
        </li>
        <li><strong>State Revenue</strong>
            <ul>
                <li>Revenue by state</li>
                <li>Compliance fees</li>
                <li>Transmission costs</li>
            </ul>
        </li>
    </ol>

    <h3>Generating Reports</h3>
    <ol>
        <li><strong>Select Date Range</strong>
            <ul>
                <li>Start and end dates</li>
                <li>Preset ranges (week, month, quarter)</li>
            </ul>
        </li>
        <li><strong>Choose Filters</strong>
            <ul>
                <li>Payment method</li>
                <li>Course type</li>
                <li>State</li>
            </ul>
        </li>
        <li><strong>Export Options</strong>
            <ul>
                <li>PDF reports</li>
                <li>CSV data export</li>
                <li>Email delivery</li>
            </ul>
        </li>
    </ol>

    <h2>Merchant Management</h2>
    <p><strong>Location:</strong> <span class="url">/admin/merchants</span></p>

    <h3>Merchant Accounts</h3>
    <ol>
        <li><strong>Add Merchant</strong>
            <ul>
                <li>Merchant name and details</li>
                <li>Fee structure</li>
                <li>Settlement terms</li>
            </ul>
        </li>
        <li><strong>Fee Configuration</strong>
            <ul>
                <li>Transaction fees</li>
                <li>Monthly fees</li>
                <li>Chargeback fees</li>
            </ul>
        </li>
        <li><strong>Reconciliation</strong>
            <ul>
                <li>Match payments to deposits</li>
                <li>Track settlement timing</li>
                <li>Handle disputes</li>
            </ul>
        </li>
    </ol>

    <h1>Certificate Management</h1>

    <h2>Certificate Templates</h2>
    <p><strong>Location:</strong> <span class="url">/admin/certificates</span></p>

    <h3>Template Management</h3>
    <ol>
        <li><strong>Create Template</strong>
            <ul>
                <li>Upload background image</li>
                <li>Set text positions</li>
                <li>Configure fonts and colors</li>
                <li>Add state stamps</li>
            </ul>
        </li>
        <li><strong>Template Fields</strong>
            <ul>
                <li>Student name position</li>
                <li>Course name position</li>
                <li>Completion date</li>
                <li>Certificate number</li>
                <li>State seal placement</li>
            </ul>
        </li>
    </ol>

    <h3>State-Specific Templates</h3>
    <ul>
        <li><strong>Florida:</strong> DHSMV requirements</li>
        <li><strong>California:</strong> DMV compliance</li>
        <li><strong>Nevada:</strong> NTSA format</li>
        <li><strong>Texas:</strong> State-specific layout</li>
    </ul>

    <h2>Certificate Generation</h2>

    <h3>Automatic Generation</h3>
    <ul>
        <li>Triggered on course completion</li>
        <li>Uses appropriate state template</li>
        <li>Includes verification QR code</li>
        <li>Stores in certificate inventory</li>
    </ul>

    <h3>Manual Generation</h3>
    <ol>
        <li><strong>Generate Individual</strong>
            <ul>
                <li>Select student and course</li>
                <li>Choose template</li>
                <li>Generate and download</li>
            </ul>
        </li>
        <li><strong>Bulk Generation</strong>
            <ul>
                <li>Select multiple completions</li>
                <li>Batch generate certificates</li>
                <li>Email delivery options</li>
            </ul>
        </li>
    </ol>

    <h2>Certificate Lookup</h2>
    <p><strong>Location:</strong> <span class="url">/admin/certificate-lookup</span></p>

    <h3>Verification System</h3>
    <ol>
        <li><strong>Search Certificates</strong>
            <ul>
                <li>By certificate number</li>
                <li>By student name</li>
                <li>By completion date</li>
            </ul>
        </li>
        <li><strong>Verification Details</strong>
            <ul>
                <li>Certificate authenticity</li>
                <li>Course information</li>
                <li>Completion status</li>
                <li>QR code validation</li>
            </ul>
        </li>
    </ol>

    <h3>Public Lookup</h3>
    <ul>
        <li><strong>URL:</strong> <span class="url">/verify/{certificate_number}</span></li>
        <li>Allows courts/employers to verify</li>
        <li>Shows basic completion info</li>
        <li>Protects student privacy</li>
    </ul>

    <h1>System Administration</h1>

    <h2>User Access Management</h2>
    <p><strong>Location:</strong> <span class="url">/admin/user-access</span></p>

    <h3>Permission Levels</h3>
    <ol>
        <li><strong>Super Admin</strong>
            <ul>
                <li>Full system access</li>
                <li>User management</li>
                <li>System configuration</li>
            </ul>
        </li>
        <li><strong>Admin</strong>
            <ul>
                <li>Course management</li>
                <li>Student management</li>
                <li>Reports access</li>
            </ul>
        </li>
        <li><strong>Instructor</strong>
            <ul>
                <li>Course content editing</li>
                <li>Student progress viewing</li>
                <li>Limited admin functions</li>
            </ul>
        </li>
        <li><strong>School Admin</strong>
            <ul>
                <li>School-specific data</li>
                <li>Local user management</li>
                <li>School reports</li>
            </ul>
        </li>
    </ol>

    <h2>Security Settings</h2>
    <p><strong>Location:</strong> <span class="url">/admin/security-dashboard</span></p>

    <h3>Password Policies</h3>
    <ol>
        <li><strong>Requirements</strong>
            <ul>
                <li>Minimum length: 8 characters</li>
                <li>Must include: uppercase, lowercase, number</li>
                <li>Special character recommended</li>
            </ul>
        </li>
        <li><strong>Expiration</strong>
            <ul>
                <li>Admin passwords: 90 days</li>
                <li>Student passwords: No expiration</li>
                <li>Force change on first login</li>
            </ul>
        </li>
    </ol>

    <h3>Login Security</h3>
    <ol>
        <li><strong>Failed Login Protection</strong>
            <ul>
                <li>Lock account after 5 failed attempts</li>
                <li>15-minute lockout period</li>
                <li>Email notification to admin</li>
            </ul>
        </li>
        <li><strong>Session Management</strong>
            <ul>
                <li>Session timeout: 2 hours inactive</li>
                <li>Concurrent session limits</li>
                <li>Force logout on password change</li>
            </ul>
        </li>
    </ol>

    <h2>System Monitoring</h2>

    <h3>Performance Monitoring</h3>
    <ol>
        <li><strong>Server Health</strong>
            <ul>
                <li>CPU usage</li>
                <li>Memory usage</li>
                <li>Disk space</li>
                <li>Database performance</li>
            </ul>
        </li>
        <li><strong>Application Metrics</strong>
            <ul>
                <li>Page load times</li>
                <li>Error rates</li>
                <li>User activity</li>
                <li>Course completion rates</li>
            </ul>
        </li>
    </ol>

    <h3>Log Management</h3>
    <ol>
        <li><strong>Error Logs</strong>
            <ul>
                <li>Application errors</li>
                <li>Database errors</li>
                <li>Payment failures</li>
                <li>State transmission errors</li>
            </ul>
        </li>
        <li><strong>Activity Logs</strong>
            <ul>
                <li>User logins</li>
                <li>Course enrollments</li>
                <li>Certificate generations</li>
                <li>Admin actions</li>
            </ul>
        </li>
    </ol>

    <h2>Backup & Recovery</h2>

    <h3>Automated Backups</h3>
    <ol>
        <li><strong>Database Backups</strong>
            <ul>
                <li>Daily full backups</li>
                <li>Hourly incremental backups</li>
                <li>30-day retention</li>
            </ul>
        </li>
        <li><strong>File Backups</strong>
            <ul>
                <li>Certificate files</li>
                <li>Course content</li>
                <li>User uploads</li>
                <li>System configurations</li>
            </ul>
        </li>
    </ol>

    <h3>Recovery Procedures</h3>
    <ol>
        <li><strong>Database Recovery</strong>
            <ul>
                <li>Point-in-time recovery</li>
                <li>Full system restore</li>
                <li>Selective table recovery</li>
            </ul>
        </li>
        <li><strong>File Recovery</strong>
            <ul>
                <li>Individual file restore</li>
                <li>Bulk file recovery</li>
                <li>Version rollback</li>
            </ul>
        </li>
    </ol>

    <h1>Troubleshooting</h1>

    <h2>Common Issues</h2>

    <h3>Course Creation Problems</h3>

    <div class="alert alert-danger">
        <strong>Issue:</strong> DICDS Course ID already exists<br>
        <strong>Solution:</strong> Use unique DICDS ID for each course<br>
        <strong>Check:</strong> Existing course list for duplicates
    </div>

    <div class="alert alert-danger">
        <strong>Issue:</strong> Course not appearing for enrollment<br>
        <strong>Solution:</strong> Verify course is marked as "Active"<br>
        <strong>Check:</strong> Course status in course management
    </div>

    <h3>Question Import Issues</h3>

    <div class="alert alert-warning">
        <strong>Issue:</strong> DOCX import fails<br>
        <strong>Solution:</strong> Use exported sample format exactly<br>
        <strong>Check:</strong> File format and question structure
    </div>

    <div class="alert alert-warning">
        <strong>Issue:</strong> TXT import creates malformed questions<br>
        <strong>Solution:</strong> Verify TXT format with proper A), B), C) structure<br>
        <strong>Check:</strong> Correct answer marked with ***
    </div>

    <h3>State Transmission Failures</h3>

    <div class="alert alert-danger">
        <strong>Issue:</strong> Florida DICDS transmission fails<br>
        <strong>Solution:</strong> Check DICDS credentials and course ID<br>
        <strong>Check:</strong> Student data completeness (DL, DOB, etc.)
    </div>

    <div class="alert alert-danger">
        <strong>Issue:</strong> California TVCC authentication error<br>
        <strong>Solution:</strong> Update TVCC password in database<br>
        <strong>Check:</strong> Court code mapping accuracy
    </div>

    <h3>Payment Processing Issues</h3>

    <div class="alert alert-warning">
        <strong>Issue:</strong> Stripe payments failing<br>
        <strong>Solution:</strong> Verify API keys and webhook configuration<br>
        <strong>Check:</strong> Test mode vs live mode settings
    </div>

    <div class="alert alert-warning">
        <strong>Issue:</strong> PayPal IPN not working<br>
        <strong>Solution:</strong> Check IPN URL configuration<br>
        <strong>Check:</strong> PayPal account settings
    </div>

    <h2>Error Codes</h2>

    <h3>State Integration Errors</h3>

    <table>
        <thead>
            <tr>
                <th>System</th>
                <th>Code</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td rowspan="4">Florida DICDS</td>
                <td>E001</td>
                <td>Invalid course ID</td>
            </tr>
            <tr>
                <td>E002</td>
                <td>Student data incomplete</td>
            </tr>
            <tr>
                <td>E003</td>
                <td>Authentication failure</td>
            </tr>
            <tr>
                <td>E004</td>
                <td>Duplicate submission</td>
            </tr>
            <tr>
                <td rowspan="4">California TVCC</td>
                <td>T001</td>
                <td>Invalid court code</td>
            </tr>
            <tr>
                <td>T002</td>
                <td>Authentication denied</td>
            </tr>
            <tr>
                <td>T003</td>
                <td>Validation failed</td>
            </tr>
            <tr>
                <td>T004</td>
                <td>Service unavailable</td>
            </tr>
        </tbody>
    </table>

    <h3>System Errors</h3>

    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th>Code</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td rowspan="4">Database</td>
                <td>DB001</td>
                <td>Connection timeout</td>
            </tr>
            <tr>
                <td>DB002</td>
                <td>Query execution failed</td>
            </tr>
            <tr>
                <td>DB003</td>
                <td>Constraint violation</td>
            </tr>
            <tr>
                <td>DB004</td>
                <td>Deadlock detected</td>
            </tr>
            <tr>
                <td rowspan="4">Application</td>
                <td>APP001</td>
                <td>File upload failed</td>
            </tr>
            <tr>
                <td>APP002</td>
                <td>Email delivery failed</td>
            </tr>
            <tr>
                <td>APP003</td>
                <td>PDF generation error</td>
            </tr>
            <tr>
                <td>APP004</td>
                <td>Session expired</td>
            </tr>
        </tbody>
    </table>

    <h2>Getting Help</h2>

    <h3>Internal Support</h3>
    <ol>
        <li><strong>Check Logs</strong>
            <ul>
                <li>Review error logs first</li>
                <li>Note exact error messages</li>
                <li>Check timestamps</li>
            </ul>
        </li>
        <li><strong>System Status</strong>
            <ul>
                <li>Verify all services running</li>
                <li>Check database connectivity</li>
                <li>Confirm external API status</li>
            </ul>
        </li>
    </ol>

    <h3>External Support</h3>
    <ol>
        <li><strong>State Agencies</strong>
            <ul>
                <li><strong>Florida DHSMV:</strong> (850) 617-2000</li>
                <li><strong>California DMV:</strong> (916) 657-6437</li>
                <li><strong>Nevada NTSA:</strong> Contact through portal</li>
            </ul>
        </li>
        <li><strong>Payment Processors</strong>
            <ul>
                <li><strong>Stripe Support:</strong> support@stripe.com</li>
                <li><strong>PayPal Support:</strong> Through merchant portal</li>
                <li><strong>Authorize.Net:</strong> (877) 447-3938</li>
            </ul>
        </li>
    </ol>

    <h3>Documentation</h3>
    <ul>
        <li><strong>Laravel Documentation:</strong> <span class="url">https://laravel.com/docs</span></li>
        <li><strong>State Integration Guides:</strong> See individual state folders</li>
        <li><strong>API Documentation:</strong> <span class="url">/api/documentation</span></li>
    </ul>

    <h2>Quick Reference</h2>

    <h3>Essential URLs</h3>
    <table>
        <thead>
            <tr>
                <th>Function</th>
                <th>URL</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Admin Dashboard</td>
                <td>/admin/dashboard</td>
            </tr>
            <tr>
                <td>Course Management</td>
                <td>/admin/florida-courses</td>
            </tr>
            <tr>
                <td>User Management</td>
                <td>/admin/users</td>
            </tr>
            <tr>
                <td>State Transmissions</td>
                <td>/admin/fl-transmissions</td>
            </tr>
            <tr>
                <td>Revenue Reports</td>
                <td>/admin/revenue</td>
            </tr>
            <tr>
                <td>Certificate Lookup</td>
                <td>/admin/certificate-lookup</td>
            </tr>
        </tbody>
    </table>

    <h3>Keyboard Shortcuts</h3>
    <ul>
        <li><strong>Ctrl+S:</strong> Save current form</li>
        <li><strong>Ctrl+N:</strong> New item (where applicable)</li>
        <li><strong>Ctrl+F:</strong> Search/filter</li>
        <li><strong>Esc:</strong> Close modal/cancel action</li>
    </ul>

    <h3>Best Practices</h3>
    <ol>
        <li><strong>Always backup before major changes</strong></li>
        <li><strong>Test in staging environment first</strong></li>
        <li><strong>Keep DICDS course IDs organized</strong></li>
        <li><strong>Monitor state transmission success rates</strong></li>
        <li><strong>Regular certificate inventory audits</strong></li>
        <li><strong>Maintain current payment gateway credentials</strong></li>
    </ol>

    <div class="footer-info">
        <p><em>Last Updated: December 2025 | Version: 1.0</em></p>
    </div>
</body>
</html>