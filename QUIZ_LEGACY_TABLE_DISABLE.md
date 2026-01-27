# Quiz Legacy Table Disable Feature

## Overview

This feature allows you to disable the legacy `questions` table and force the system to only use the modern `chapter_questions` table. This prevents any potential duplicate questions and ensures consistent behavior across the quiz system.

## Configuration

### Environment Variable

Add this to your `.env` file:

```env
# Disable legacy questions table (set to true to only use chapter_questions table)
DISABLE_LEGACY_QUESTIONS_TABLE=false
```

### Configuration File

The feature is controlled by `config/quiz.php`:

```php
'disable_legacy_questions_table' => env('DISABLE_LEGACY_QUESTIONS_TABLE', false),
```

## How It Works

### When `DISABLE_LEGACY_QUESTIONS_TABLE=false` (Default)
- System checks `chapter_questions` table first
- If no questions found, falls back to `questions` table (legacy)
- Maintains backward compatibility

### When `DISABLE_LEGACY_QUESTIONS_TABLE=true`
- System ONLY uses `chapter_questions` table
- Completely ignores `questions` table (legacy)
- Chapters with questions only in legacy table will show 0 questions
- Prevents any potential duplicates

## Affected Components

### QuestionController
- `index()` method respects the configuration
- Logs which table is being used
- Never merges both tables when legacy is disabled

### Chapter Model
- `hasQuestions()` method respects configuration
- `allQuestions()` method respects configuration
- New methods for explicit table access

### ProgressController
- Uses `hasQuestions()` method for quiz checking
- Respects configuration for quiz requirements

## Migration Tool

Use the Artisan command to migrate questions from legacy table:

```bash
# Dry run to see what would be migrated
php artisan quiz:migrate-legacy-questions --dry-run

# Migrate all legacy questions
php artisan quiz:migrate-legacy-questions

# Migrate specific chapter
php artisan quiz:migrate-legacy-questions --chapter=34

# Force migration even if chapter_questions exist (creates duplicates)
php artisan quiz:migrate-legacy-questions --force
```

## Recommended Migration Process

1. **Audit Current State**
   ```bash
   php artisan quiz:migrate-legacy-questions --dry-run
   ```

2. **Migrate Legacy Questions**
   ```bash
   php artisan quiz:migrate-legacy-questions
   ```

3. **Test Quiz Functionality**
   - Verify all chapters show correct questions
   - Test quiz taking functionality
   - Check for any missing questions

4. **Enable Legacy Table Disable**
   ```env
   DISABLE_LEGACY_QUESTIONS_TABLE=true
   ```

5. **Final Testing**
   - Confirm no duplicate questions appear
   - Verify all quizzes work correctly
   - Monitor logs for any issues

## Benefits

### ✅ Prevents Duplicates
- No more duplicate questions in quizzes
- Consistent question display

### ✅ Performance
- Only queries one table instead of two
- Faster question loading

### ✅ Data Integrity
- Single source of truth for questions
- Cleaner database structure

### ✅ Future-Proof
- Modern table structure
- Better support for quiz features (quiz_set, etc.)

## Monitoring

The system logs which table is being used:

```
QuestionController: Using chapter_questions table - found 5 questions
QuestionController: Legacy table disabled, no questions found in chapter_questions table
```

## Rollback

To re-enable legacy table support:

```env
DISABLE_LEGACY_QUESTIONS_TABLE=false
```

The system will immediately start using both tables again with the prioritization logic.

## Current Status

- ✅ Configuration implemented
- ✅ QuestionController updated
- ✅ Chapter model updated
- ✅ ProgressController updated
- ✅ Migration tool created
- ✅ Testing completed

The feature is ready for production use.