# Survey System Implementation

## Overview
Complete post-course survey system integrated into the Laravel traffic school platform. Students must complete satisfaction surveys after course completion and before certificate generation.

## Database Schema

### Tables Created
1. **surveys** - Survey definitions with state/course targeting
2. **survey_questions** - Questions with multiple types (rating, scale, text, multiple choice, yes/no)
3. **survey_responses** - User survey submissions
4. **survey_answers** - Individual question answers

## Features Implemented

### User-Facing Features
- **Survey Display** (`/survey/{enrollment}`)
  - Dynamic question rendering based on type
  - Progress tracking
  - Validation for required questions
  - Mobile-responsive design

- **Survey Submission** 
  - Transaction-safe submission
  - Auto-save capability
  - Redirect to certificate after completion

- **Thank You Page**
  - Auto-redirect to certificate (5 seconds)
  - Manual proceed button

### Admin Features

#### Survey Management (`/admin/surveys`)
- Create/Edit/Delete surveys
- State-specific surveys (FL, MO, TX, DE)
- Course-specific surveys
- Active/Inactive toggle
- Duplicate surveys with questions
- Question management with drag-and-drop ordering

#### Question Types Supported
1. **Scale (1-5)** - Standard 5-point scale
2. **Scale (1-10)** - Extended 10-point scale
3. **Rating** - Star rating system
4. **Yes/No** - Binary choice
5. **Multiple Choice** - Radio button options
6. **Text** - Open-ended responses

#### Survey Reports (`/admin/survey-reports`)
- Overall dashboard with statistics
- Survey-specific reports with charts
- State-specific reports
- Course-specific reports
- Date range filtering
- CSV/Excel export
- Delaware-specific printable report

## Integration Points

### Certificate Flow Integration
The survey check is integrated into the certificate generation flow:

```php
// In routes/web.php - generate-certificate route
$surveyService = app(\App\Services\SurveyService::class);
if (!$surveyService->hasCompletedRequiredSurvey($enrollment)) {
    return redirect()->route('survey.show', $enrollment->id);
}
```

### Survey Selection Logic
Surveys are selected with priority:
1. Course-specific survey
2. State-specific survey
3. General survey (all states/courses)

## Routes

### User Routes
```
GET  /survey/{enrollment}           - Display survey
POST /survey/{enrollment}           - Submit survey
GET  /survey/{enrollment}/thank-you - Thank you page
```

### Admin Routes
```
GET    /admin/surveys                      - List surveys
GET    /admin/surveys/create               - Create form
POST   /admin/surveys                      - Store survey
GET    /admin/surveys/{survey}             - View survey
GET    /admin/surveys/{survey}/edit        - Edit form
PUT    /admin/surveys/{survey}             - Update survey
DELETE /admin/surveys/{survey}             - Delete survey
POST   /admin/surveys/{survey}/duplicate   - Duplicate survey
PATCH  /admin/surveys/{survey}/toggle-active - Toggle status
GET    /admin/surveys/{survey}/responses   - View responses
GET    /admin/surveys/{survey}/export      - Export CSV

POST   /admin/surveys/{survey}/questions   - Add question
PUT    /admin/surveys/{survey}/questions/{question} - Update question
DELETE /admin/surveys/{survey}/questions/{question} - Delete question
POST   /admin/surveys/{survey}/questions/reorder - Reorder questions

GET    /admin/survey-reports               - Reports dashboard
GET    /admin/survey-reports/by-survey/{survey} - Survey report
GET    /admin/survey-reports/by-state/{state} - State report
GET    /admin/survey-reports/by-course/{course} - Course report
GET    /admin/survey-reports/delaware      - Delaware report
```

## Models & Relationships

### Survey
- `hasMany` questions
- `hasMany` responses
- `belongsTo` course (optional)

### SurveyQuestion
- `belongsTo` survey
- `hasMany` answers

### SurveyResponse
- `belongsTo` survey
- `belongsTo` user
- `belongsTo` enrollment
- `hasMany` answers

### SurveyAnswer
- `belongsTo` surveyResponse
- `belongsTo` surveyQuestion

## Services

### SurveyService
Key methods:
- `findApplicableSurvey($enrollment)` - Find correct survey
- `hasCompletedRequiredSurvey($enrollment)` - Check completion
- `startSurvey($survey, $enrollment)` - Create response
- `saveAnswer($response, $question, $answer)` - Save answer
- `completeSurvey($response)` - Mark complete
- `generateStatistics($survey, $from, $to)` - Generate stats
- `generateStateReport($stateCode, $from, $to)` - State report
- `exportResponses($survey, $format)` - Export data

## Events & Listeners

### SurveyCompleted Event
Fired when a user completes a survey.

### LogSurveyCompletion Listener
Logs survey completion to application logs.

## Seeded Data

Default surveys created for:
- General satisfaction (all states)
- Florida-specific
- Delaware-specific
- Missouri-specific

Each includes 4-6 sample questions covering:
- Overall satisfaction
- Content quality
- Recommendations
- Improvements
- How they heard about the course

## Views Created

### User Views
- `resources/views/survey/show.blade.php` - Survey form
- `resources/views/survey/thank-you.blade.php` - Completion page

### Admin Views
- `resources/views/admin/surveys/index.blade.php` - Survey list
- `resources/views/admin/surveys/create.blade.php` - Create form
- `resources/views/admin/surveys/edit.blade.php` - Edit form
- `resources/views/admin/surveys/show.blade.php` - Survey details with questions
- `resources/views/admin/survey-reports/index.blade.php` - Reports dashboard
- `resources/views/admin/survey-reports/by-survey.blade.php` - Survey report

## Usage

### For Students
1. Complete course
2. Redirected to survey (if required)
3. Fill out survey questions
4. Submit survey
5. Redirected to certificate generation

### For Admins
1. Navigate to `/admin/surveys`
2. Create new survey or edit existing
3. Add questions with appropriate types
4. Set state/course targeting
5. Activate survey
6. View reports at `/admin/survey-reports`

## Testing

To test the survey system:

1. Complete a course enrollment
2. Navigate to certificate generation
3. Should be redirected to survey
4. Complete and submit survey
5. Should proceed to certificate

## Future Enhancements

Potential additions:
- Survey templates
- Question branching/conditional logic
- Anonymous surveys
- Survey scheduling (time-based activation)
- Email notifications for low satisfaction scores
- Integration with CRM systems
- Multi-language support
- Survey versioning
