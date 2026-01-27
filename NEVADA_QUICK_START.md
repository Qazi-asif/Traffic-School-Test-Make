# Nevada Integration - Quick Start Guide

## Setup (5 minutes)

### 1. Run the Seeder
```bash
php artisan db:seed --class=NevadaMasterSeeder
```

This creates:
- Nevada Traffic Safety Course (8 hours)
- Nevada Defensive Driving Course (6 hours)

### 2. Access Nevada Dashboard
Navigate to: **http://your-domain/admin/nevada**

You'll see:
- Active courses count
- Total students
- Certificates issued
- Pending submissions
- Recent completions

## Key Features

### 📊 Compliance Logs
**URL:** `/admin/nevada/compliance-logs`

View all student activities:
- Login events
- Chapter progress
- Quiz attempts
- Course completions
- Certificate generation

**Export:** Click "Export CSV" to download logs

### 👥 Student Management
**URL:** `/admin/nevada/students`

- Search by DMV number, court case, or name
- View detailed activity logs
- Check validation status
- Monitor due dates

### 📜 Certificates
**URL:** `/admin/nevada/certificates`

- View all Nevada certificates
- Filter by submission status
- Submit to state
- Track confirmation numbers

### 📈 Reports
**URL:** `/admin/nevada/reports/compliance`

Generate compliance reports:
- Activity breakdown by type
- Unique user counts
- Date range filtering
- Visual statistics

## Common Tasks

### View Student Activity
1. Go to `/admin/nevada/students`
2. Click on a student
3. View complete activity timeline
4. Check validation errors (if any)

### Submit Certificate to State
1. Go to `/admin/nevada/certificates`
2. Find certificate with "Pending" status
3. Click "Submit"
4. Track submission status

### Export Compliance Data
1. Go to `/admin/nevada/compliance-logs`
2. Set filters (optional):
   - Log type
   - Date range
3. Click "Export CSV"
4. Download file

### Generate Compliance Report
1. Go to `/admin/nevada/reports/compliance`
2. Select date range
3. Click "Generate Report"
4. View statistics and breakdown

## Automatic Logging

The system automatically logs:
- ✅ User logins (for Nevada enrollments)
- ✅ Chapter starts and completions
- ✅ Quiz attempts (pass/fail)
- ✅ Course completions
- ✅ Certificate generation

No manual intervention required!

## Validation Rules

Students must meet these requirements:
- ✅ Complete all chapters
- ✅ Meet minimum time requirement (hours)
- ✅ Complete within max days (default: 90)
- ✅ Pass all required quizzes

## Navigation

Find Nevada features in the admin sidebar:
- **NEVADA STATE** section
- Nevada Dashboard
- Nevada Students
- Compliance Logs
- Nevada Certificates

## API Integration

To integrate with Nevada state systems, update:
```php
app/Services/NevadaComplianceService.php
```

Method: `submitToState()`

Add your state API endpoint and authentication.

## Support

For issues or questions:
1. Check compliance logs for activity
2. Review student validation errors
3. Check submission error messages
4. Export logs for analysis

## Quick Reference

| Feature | URL | Purpose |
|---------|-----|---------|
| Dashboard | `/admin/nevada` | Overview & stats |
| Students | `/admin/nevada/students` | Student management |
| Compliance Logs | `/admin/nevada/compliance-logs` | Activity tracking |
| Certificates | `/admin/nevada/certificates` | Certificate management |
| Reports | `/admin/nevada/reports/compliance` | Compliance reporting |

## Next Steps

1. ✅ Enroll test student in Nevada course
2. ✅ Complete some activities
3. ✅ Check compliance logs
4. ✅ Generate certificate
5. ✅ Test submission flow
6. ✅ Export compliance data

Nevada integration is ready to use!
