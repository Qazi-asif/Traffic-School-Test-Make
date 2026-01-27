# Course Player 404 Error Fix

## Issue
POST request to `/web/enrollments/3/complete-chapter/1` was returning 404 error.

## Root Cause
The route exists but there were model compatibility issues between `Chapter` and `CourseChapter` models.

## Files Fixed

### 1. Added Missing Chapter Model
- **File**: `app/Models/Chapter.php`
- **Issue**: Referenced in ProgressController but didn't exist
- **Fix**: Copied from source project

### 2. Fixed ProgressController
- **File**: `app/Http/Controllers/ProgressController.php`
- **Method**: `completeChapterWeb()`
- **Issue**: Only looked for CourseChapter model
- **Fix**: Now checks both Chapter and CourseChapter models

### 3. Fixed Progress Calculation
- **Method**: `updateEnrollmentProgress()`
- **Issue**: Only counted CourseChapter records
- **Fix**: Checks both chapter models for total count

## Changes Made

### ProgressController::completeChapterWeb()
```php
// Before: Only CourseChapter
$chapterModel = \App\Models\CourseChapter::findOrFail($chapter);

// After: Both models
$chapterModel = \App\Models\Chapter::find($chapter) ?? \App\Models\CourseChapter::find($chapter);
```

### ProgressController::updateEnrollmentProgress()
```php
// Before: Only CourseChapter count
$totalChapters = \App\Models\CourseChapter::where('course_id', $enrollment->course_id)->count();

// After: Both models
$totalChapters = \App\Models\CourseChapter::where('course_id', $enrollment->course_id)->count();
if ($totalChapters == 0) {
    $totalChapters = \App\Models\Chapter::where('course_id', $enrollment->course_id)->count();
}
```

## Testing
1. Navigate to course player: `/course-player?enrollmentId=3`
2. Complete a chapter
3. Should now return success response instead of 404

## Route Structure
The route is properly defined in `routes/web.php`:
```php
Route::post('/web/enrollments/{enrollment}/complete-chapter/{chapter}', 
    [App\Http\Controllers\ProgressController::class, 'completeChapterWeb']);
```

## Expected Response
```json
{
    "success": true,
    "progress": {...},
    "message": "Chapter completed successfully"
}
```
