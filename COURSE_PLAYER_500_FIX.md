# Course Player 500 Error Fix

## Issue
POST request to `/web/enrollments/3/complete-chapter/1` was returning 500 Internal Server Error.

## Root Cause
**Foreign Key Constraint Violation:**
```
SQLSTATE[23000]: Integrity constraint violation: 1452 Cannot add or update a child row: 
a foreign key constraint fails (`user_course_progress`, CONSTRAINT `user_course_progress_chapter_id_foreign` 
FOREIGN KEY (`chapter_id`) REFERENCES `course_chapters` (`id`) ON DELETE CASCADE)
```

The `user_course_progress` table expects `chapter_id` to reference `course_chapters.id`, but the system was trying to insert a chapter ID from the `chapters` table.

## Solution

### 1. Fixed ProgressController::completeChapterWeb()
- **Before**: Tried to use chapter ID from either table directly
- **After**: Ensures chapter exists in `course_chapters` table first
- **Logic**: If chapter only exists in `chapters` table, creates corresponding `course_chapters` record

### 2. Updated UserCourseProgress Model
- Added `courseChapter()` relationship
- Enhanced `chapter()` method to handle both chapter types

## Key Changes

### ProgressController Fix:
```php
// Check if chapter exists in course_chapters (required by foreign key)
$courseChapter = \App\Models\CourseChapter::find($chapter);

if (!$courseChapter) {
    // Create course_chapter record if only exists in chapters table
    $regularChapter = \App\Models\Chapter::find($chapter);
    $courseChapter = \App\Models\CourseChapter::create([
        'course_id' => $enrollment->course_id,
        'title' => $regularChapter->title,
        // ... other fields
    ]);
}
```

### Error Handling:
- Added try-catch block
- Proper error logging
- User-friendly error responses

## Database Constraint
The foreign key constraint requires:
- `user_course_progress.chapter_id` → `course_chapters.id`
- Cannot reference `chapters.id` directly

## Testing
1. Navigate to: `/course-player?enrollmentId=3`
2. Complete a chapter
3. Should now return success response instead of 500 error

## Expected Response
```json
{
    "success": true,
    "progress": {...},
    "message": "Chapter completed successfully"
}
```
