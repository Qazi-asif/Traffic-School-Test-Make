# Customer Segmentation - Quick Start Guide

## 🚀 Get Started in 5 Minutes

### Step 1: Verify Installation ✅

All files are already created! Just verify:

```bash
# Check if migration ran
php artisan migrate:status | grep enrollment_segments

# Should show: Ran - 2025_12_03_191505_create_enrollment_segments_table
```

### Step 2: Access the Dashboard

Navigate to:
```
http://yoursite.com/admin/customers/segments
```

You'll see 8 segment cards with real-time counts.

### Step 3: Explore Segments

Click any card to view that segment:

1. **Completed This Month** - See all monthly completions
2. **Paid, Not Completed** - Follow up with paying customers
3. **Abandoned** - Re-engage inactive students
4. **Expiring Soon** - Urgent warnings needed

### Step 4: Use Filters

On any segment view:
- Filter by State (FL, MO, TX, DE)
- Filter by Course
- Filter by Progress %
- Filter by Date Range

### Step 5: Take Action

**Send Reminders:**
1. Select students (checkboxes)
2. Click "Send Reminder to Selected"
3. Emails sent automatically!

**Export Data:**
1. Select students
2. Click "Export Selected"
3. Download CSV file

**Extend Expiration:**
1. Select students
2. Enter days to extend
3. Click "Extend Expiration"

---

## 📧 Email Automation

### Manual Commands

Test the email system:

```bash
# Send reminders to paid incomplete (7+ days)
php artisan customers:remind-incomplete --days=7

# Warn customers expiring in 7 days
php artisan customers:warn-expiring --days=7
```

### Automatic Scheduling

Add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Weekly reminders
    $schedule->command('customers:remind-incomplete --days=7')
        ->weekly()->mondays()->at('09:00');
    
    // Daily expiration warnings
    $schedule->command('customers:warn-expiring --days=7')
        ->daily()->at('08:00');
}
```

Then start the scheduler:
```bash
php artisan schedule:work
```

---

## 🎯 Common Use Cases

### Use Case 1: Monthly Reporting
```
1. Go to: /admin/customers/completed-monthly
2. Select: Current month/year
3. Click: Export Selected
4. Use CSV for reporting
```

### Use Case 2: Follow Up Paid Customers
```
1. Go to: /admin/customers/paid-incomplete
2. Filter: Progress < 50%
3. Select: All students
4. Click: Send Reminder
```

### Use Case 3: Urgent Expiration Warnings
```
1. Go to: /admin/customers/expiring-soon
2. Filter: Days = 3
3. Select: All students
4. Click: Send Expiration Warning
```

### Use Case 4: Re-engage Abandoned Students
```
1. Go to: /admin/customers/abandoned
2. Filter: Days inactive = 30
3. Select: Paid students only
4. Click: Send Re-engagement Email
```

---

## 📊 Understanding the Dashboard

### Segment Cards

Each card shows:
- **Count** - Number of students in segment
- **Color** - Urgency indicator
  - 🟢 Green = Completed (good)
  - 🔵 Blue = Paid/In Progress (active)
  - 🟠 Orange = Abandoned (needs attention)
  - 🟡 Yellow = Expiring (warning)
  - 🔴 Red = Expired (urgent)
  - 🟣 Purple = In Progress (monitoring)
  - ⚫ Gray = Never Started (onboarding)
  - 🩷 Pink = Struggling (support needed)

### Monthly Trend Chart

Shows completion trends over last 6 months:
- Visual bar chart
- Hover for exact counts
- Identify seasonal patterns

---

## 🔍 Segment Definitions

| Segment | Who's Included | Action Needed |
|---------|---------------|---------------|
| **Completed This Month** | Finished this month | Generate certificates |
| **Paid, Not Completed** | Paid but incomplete | Send reminders |
| **In Progress** | Started & paid | Monitor progress |
| **Abandoned** | Inactive 30+ days | Re-engagement |
| **Expiring Soon** | Court date < 7 days | Urgent warnings |
| **Expired** | Recently expired | Recovery attempts |
| **Never Started** | Paid, not started | Onboarding help |
| **Struggling** | 3+ failed quizzes | Support outreach |

---

## ⚙️ Configuration

### Email Settings

Verify `.env` has:
```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_FROM_ADDRESS=noreply@yoursite.com
```

### Test Email Sending

```bash
# Test with tinker
php artisan tinker

# Send test email
$enrollment = App\Models\UserCourseEnrollment::first();
Mail::to('test@example.com')->send(new App\Mail\CourseCompletionReminder($enrollment));
```

---

## 🐛 Troubleshooting

### No Data Showing?

**Check enrollments exist:**
```bash
php artisan tinker
App\Models\UserCourseEnrollment::count()
```

**Check filters:**
- Remove all filters
- Try different states/courses

### Emails Not Sending?

**Check mail configuration:**
```bash
php artisan config:clear
php artisan queue:work
```

**Check logs:**
```bash
tail -f storage/logs/laravel.log
```

### 404 Error?

**Clear caches:**
```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

### Permission Denied?

**Check user role:**
```bash
php artisan tinker
auth()->user()->role
# Should be 'admin' or 'super-admin'
```

---

## 📈 Best Practices

### Daily Tasks
1. Check "Expiring Soon" segment
2. Review "Struggling" students
3. Monitor "Abandoned" count

### Weekly Tasks
1. Send reminders to paid incomplete
2. Export completed monthly report
3. Review never-started students

### Monthly Tasks
1. Export monthly completions
2. Analyze completion trends
3. Review segment statistics

---

## 🎓 Tips & Tricks

### Bulk Operations
- Use checkboxes to select multiple students
- "Select All" checkbox selects entire page
- Bulk actions work on selected students only

### Filtering
- Combine multiple filters for precise segments
- Save common filter combinations as custom segments
- Use progress range for targeted follow-ups

### Exports
- CSV includes all enrollment details
- Use for external reporting
- Import into Excel/Google Sheets

### Email Templates
- Customize in `resources/views/emails/reminders/`
- Add your branding
- Test before bulk sending

---

## 🚀 Next Steps

1. **Explore Each Segment** - Click through all 8 segments
2. **Test Filters** - Try different filter combinations
3. **Send Test Email** - Use bulk remind on 1-2 students
4. **Export Data** - Download a CSV to review format
5. **Schedule Automation** - Set up daily/weekly commands

---

## 📞 Need Help?

- Check `CUSTOMER_SEGMENTATION_COMPLETE.md` for full documentation
- Review `CUSTOMER_SEGMENTATION_NAVIGATION.md` for menu setup
- Check Laravel logs: `storage/logs/laravel.log`

---

## ✅ You're Ready!

Your Customer Segmentation system is fully operational. Start by visiting:

**`/admin/customers/segments`**

Happy segmenting! 🎉
