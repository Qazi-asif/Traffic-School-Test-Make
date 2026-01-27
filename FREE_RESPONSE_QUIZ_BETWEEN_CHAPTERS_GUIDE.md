# Free Response Quiz Between Chapters - Implementation Guide

## Overview

This implementation allows you to add free response quizzes at any point between chapters in a course. Students will encounter these quizzes as they progress through the course, and the quizzes can be mandatory or optional.

## Features

- ✅ **Flexible Placement**: Add quizzes after any chapter or at the end of the course
- ✅ **Multiple Quizzes**: Support for multiple quiz sections in one course
- ✅ **Mandatory/Optional**: Configure whether quizzes are required to proceed
- ✅ **Admin Management**: Full admin interface for managing quiz placements
- ✅ **Progress Tracking**: Quizzes integrate with the course progress system
- ✅ **Seamless Integration**: Works with existing chapter breaks and final exams

## Database Setup

### 1. Run the Migrations

```bash
php artisan migrate
```

This will create:
- `free_response_quiz_placements` table
- Add `placement_id` column to `free_response_questions` table

### 2. Or Run SQL Manually

If you prefer to run SQL directly:

```sql
-- Create placements table
CREATE TABLE free_response_quiz_placements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id BIGINT UNSIGNED NOT NULL,
    after_chapter_id BIGINT UNSIGNED NULL,
    quiz_title VARCHAR(255) NOT NULL DEFAULT 'Free Response Questions',
    quiz_description TEXT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    is_mandatory BOOLEAN DEFAULT TRUE,
    order_index INT DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_course_active (course_id, is_active),
    INDEX idx_after_chapter (after_chapter_id)
);

-- Add placement_id to questions table
ALTER TABLE free_response_questions 
ADD COLUMN placement_id BIGINT UNSIGNED NULL AFTER course_id,
ADD INDEX idx_placement (placement_id);
```

## Admin Interface

### 1. Access Quiz Placements

Navigate to: `/admin/free-response-quiz-placements`

### 2. Create a New Placement

1. Click "Add Placement"
2. Select the course
3. Choose after which chapter to place the quiz (or leave empty for end of course)
4. Set quiz title and description
5. Configure if it's mandatory
6. Set order index for multiple placements

### 3. Manage Questions for Placements

1. Go to `/admin/free-response-quiz`
2. When creating/editing questions, you can now assign them to specific placements
3. Questions without a placement will appear in the default end-of-course quiz

## How It Works

### 1. Chapter Loading Process

When a student loads a course:
1. Regular chapters are loaded first
2. Quiz placements are retrieved for the course
3. Quiz "chapters" are inserted at the specified positions
4. Chapter breaks and final exam are added
5. All items are renumbered sequentially

### 2. Student Experience

Students will see:
- Regular chapters (1, 2, 3...)
- Quiz sections inserted between chapters (e.g., "4. Mid-Course Assessment")
- Chapter breaks
- Final quiz section (if configured)
- Final exam

### 3. Progress Tracking

- Each quiz placement creates a virtual "chapter" with ID `quiz-{placement_id}`
- Progress is tracked in the `user_course_progress` table
- Mandatory quizzes must be completed to unlock subsequent content

## Configuration Examples

### Example 1: Mid-Course Quiz

Add a quiz after chapter 3:

```sql
INSERT INTO free_response_quiz_placements (
    course_id, 
    after_chapter_id, 
    quiz_title, 
    quiz_description, 
    is_mandatory, 
    order_index
) VALUES (
    1, -- Your course ID
    3, -- After chapter 3
    'Mid-Course Assessment',
    'Please answer these questions based on chapters 1-3.',
    TRUE,
    1
);
```

### Example 2: End-of-Course Quiz

Add a quiz at the end (before final exam):

```sql
INSERT INTO free_response_quiz_placements (
    course_id, 
    after_chapter_id, 
    quiz_title, 
    quiz_description, 
    is_mandatory, 
    order_index
) VALUES (
    1, -- Your course ID
    NULL, -- NULL = end of course
    'Pre-Final Review',
    'Final review questions before the exam.',
    TRUE,
    2
);
```

### Example 3: Multiple Quizzes

You can add multiple quizzes throughout the course:

```sql
-- Quiz after chapter 2
INSERT INTO free_response_quiz_placements (course_id, after_chapter_id, quiz_title, order_index) 
VALUES (1, 2, 'Early Assessment', 1);

-- Quiz after chapter 5
INSERT INTO free_response_quiz_placements (course_id, after_chapter_id, quiz_title, order_index) 
VALUES (1, 5, 'Mid-Course Review', 2);

-- Quiz at the end
INSERT INTO free_response_quiz_placements (course_id, after_chapter_id, quiz_title, order_index) 
VALUES (1, NULL, 'Final Review', 3);
```

## API Endpoints

### For Students
- `GET /web/courses/{id}/chapters` - Returns chapters with quiz placements inserted
- `GET /api/free-response-questions?placement_id={id}` - Get questions for specific placement

### For Admins
- `GET /admin/free-response-quiz-placements` - Manage placements
- `POST /admin/free-response-quiz-placements` - Create placement
- `PUT /admin/free-response-quiz-placements/{id}` - Update placement
- `DELETE /admin/free-response-quiz-placements/{id}` - Delete placement
- `POST /admin/free-response-quiz-placements/{id}/toggle` - Toggle active status

## Course Player Integration

The course player automatically handles different chapter types:

- **Regular chapters**: `selectChapter(id)`
- **Quiz chapters**: `loadFreeResponseQuizChapter(id, placementId)`
- **Chapter breaks**: `selectChapter(id)`
- **Final exam**: `selectChapter('final-exam')`

## Customization Options

### 1. Quiz Titles and Descriptions

Each placement can have:
- Custom title (default: "Free Response Questions")
- Optional description shown to students
- Custom duration (defaults to 30 minutes)

### 2. Mandatory vs Optional

- **Mandatory**: Students must complete to proceed
- **Optional**: Students can skip and continue

### 3. Multiple Placements

- Use `order_index` to control the sequence
- Each placement can have different questions
- Questions are linked to placements via `placement_id`

## Troubleshooting

### Quiz Not Appearing

1. Check if placement is active: `is_active = TRUE`
2. Verify course ID matches
3. Check if `after_chapter_id` exists (or is NULL for end placement)

### Questions Not Loading

1. Ensure questions have correct `placement_id`
2. Check if questions are active: `is_active = TRUE`
3. Verify API endpoint is accessible

### Progress Not Tracking

1. Check `user_course_progress` table for entries with `chapter_id = 'quiz-{placement_id}'`
2. Ensure enrollment ID is being passed correctly
3. Verify quiz submission endpoint is working

## Benefits

1. **Flexible Assessment**: Add quizzes exactly where needed in the course flow
2. **Better Learning**: Break up long courses with assessment points
3. **Compliance**: Meet requirements for periodic assessments
4. **Engagement**: Keep students engaged throughout the course
5. **Analytics**: Track student progress at multiple points

## Files Created/Modified

### New Files
- `app/Models/FreeResponseQuizPlacement.php`
- `app/Http/Controllers/Admin/FreeResponseQuizPlacementController.php`
- `database/migrations/2026_01_13_000001_create_free_response_quiz_placements_table.php`
- `resources/views/admin/free-response-quiz-placements/index.blade.php`

### Modified Files
- `app/Models/FreeResponseQuestion.php` - Added placement relationship
- `app/Http/Controllers/ChapterController.php` - Added quiz placement insertion logic
- `resources/views/course-player.blade.php` - Added quiz chapter handling
- `routes/web.php` - Added placement management routes

This implementation provides a complete solution for adding free response quizzes between chapters while maintaining compatibility with existing functionality.