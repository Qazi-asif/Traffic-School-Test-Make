# Deployment Checklist

## Pre-Deployment

- [ ] Review all changes in modified files
- [ ] Run verification script: `php test-final-exam-fixes.php`
- [ ] Backup database before deployment
- [ ] Test in local/development environment first

## Deployment Steps

### 1. Deploy Code Changes
```bash
# Pull latest changes
git pull origin main

# Or copy modified files:
# - app/Http/Controllers/FinalExamResultController.php
# - app/Models/UserCourseEnrollment.php
# - resources/views/course-player.blade.php
```

### 2. Clear Caches
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### 3. Verify Database Schema
```bash
# Check if columns exist
php artisan tinker
>>> Schema::hasColumn('user_course_enrollments', 'final_exam_completed')
>>> Schema::hasColumn('user_course_enrollments', 'final_exam_result_id')
```

If columns don't exist, run migration:
```bash
php artisan migrate
```

### 4. Restart Services
```bash
# Restart queue workers
php artisan queue:restart

# Restart web server (if needed)
sudo systemctl restart php-fpm
sudo systemctl restart nginx
```

## Post-Deployment Testing

### Manual Test Scenario

1. **Setup Test User**
   - [ ] Create/use test student account
   - [ ] Enroll in a course
   - [ ] Complete all chapters

2. **Test Final Exam - First Attempt**
   - [ ] Access final exam
   - [ ] Complete and submit (ensure score ≥ 80%)
   - [ ] Verify progress shows 100%
   - [ ] Verify automatic redirect to `/generate-certificates`
   - [ ] Verify certificate is generated

3. **Test Re-access Prevention**
   - [ ] Try to access final exam again
   - [ ] Should see "Course Completed" screen
   - [ ] Should NOT be able to retake
   - [ ] Verify button to generate certificate exists

4. **Test Failed Scenario**
   - [ ] Create another test enrollment
   - [ ] Complete chapters
   - [ ] Take final exam with score < 80%
   - [ ] Verify progress stays at 95%
   - [ ] Verify redirect to result page (not certificates)
   - [ ] Verify retry option available

### Database Verification

```sql
-- Check a completed enrollment
SELECT 
    id, 
    user_id, 
    status, 
    progress_percentage, 
    final_exam_completed,
    completed_at
FROM user_course_enrollments 
WHERE status = 'completed'
LIMIT 5;

-- Check final exam results
SELECT 
    fer.id,
    fer.enrollment_id,
    fer.final_exam_score,
    fer.is_passing,
    fer.status,
    uce.progress_percentage,
    uce.status as enrollment_status
FROM final_exam_results fer
JOIN user_course_enrollments uce ON fer.enrollment_id = uce.id
WHERE fer.is_passing = 1
LIMIT 5;

-- Check for duplicate passed results (should be 0)
SELECT 
    enrollment_id, 
    COUNT(*) as count
FROM final_exam_results
WHERE is_passing = 1
GROUP BY enrollment_id
HAVING count > 1;
```

### Log Monitoring

```bash
# Watch logs in real-time
tail -f storage/logs/laravel.log | grep -i "final exam"

# Look for these messages:
# - "Final exam completed - Before progress update"
# - "Final exam completed - After progress update"
# - "Redirecting to certificate generation"
# - "Progress update - ... Overall Progress: 100%"
```

## Rollback Plan

If issues are detected:

### Quick Rollback
```bash
git checkout HEAD~1 -- app/Http/Controllers/FinalExamResultController.php
git checkout HEAD~1 -- app/Models/UserCourseEnrollment.php
git checkout HEAD~1 -- resources/views/course-player.blade.php

php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Full Rollback
```bash
# Revert to previous commit
git revert HEAD

# Or restore from backup
# (restore database and code)
```

## Success Criteria

- [ ] Students can complete final exam
- [ ] Progress updates to 100% on pass
- [ ] Automatic redirect to certificate generation
- [ ] Students cannot retake passed exam
- [ ] Failed students can retry (if attempts remaining)
- [ ] No errors in logs
- [ ] No duplicate final exam results in database
- [ ] Certificates are generated automatically

## Monitoring (First 24 Hours)

- [ ] Monitor error logs for exceptions
- [ ] Check for any support tickets related to final exams
- [ ] Verify certificate generation is working
- [ ] Check database for any anomalies
- [ ] Monitor application performance

## Contact

If issues arise:
1. Check `storage/logs/laravel.log`
2. Review FINAL_EXAM_FIXES.md for troubleshooting
3. Use QUICK_REFERENCE.md for common issues
4. Rollback if critical issues found

## Notes

- All changes are backward compatible
- No database migrations required (columns already exist from previous migration)
- Changes include comprehensive logging for debugging
- Frontend changes are minimal and focused
