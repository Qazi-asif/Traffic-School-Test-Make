# Survey System - Quick Start Guide

## What Was Implemented

A complete post-course survey system that requires students to complete satisfaction surveys before receiving their certificates. The system includes admin management, reporting, and multi-state support.

## Quick Access URLs

### Admin Access (Super Admin/Admin only)
- **Survey Management**: `/admin/surveys`
- **Survey Reports**: `/admin/survey-reports`

### Student Flow
Students are automatically redirected to surveys when attempting to generate certificates if:
1. Course is completed
2. Survey is required
3. Survey hasn't been completed yet

## Getting Started

### 1. View Existing Surveys
Navigate to `/admin/surveys` to see the 4 pre-seeded surveys:
- General Course Satisfaction Survey (all states)
- Florida Course Feedback
- Delaware Course Evaluation
- Missouri Course Feedback

### 2. Create a New Survey
1. Click "Create New Survey" button
2. Fill in:
   - Survey Name (required)
   - Description (optional)
   - State (optional - leave blank for all states)
   - Course (optional - leave blank for all courses)
   - Required checkbox (checked = must complete before certificate)
   - Active checkbox (checked = survey is live)
3. Click "Create Survey"
4. Add questions on the next page

### 3. Add Questions to Survey
On the survey detail page:
1. Click "Add Question"
2. Enter question text
3. Select question type:
   - **Scale (1-5)**: Standard satisfaction scale
   - **Scale (1-10)**: Extended scale
   - **Rating**: Star rating
   - **Yes/No**: Binary choice
   - **Multiple Choice**: Add options (one per line)
   - **Text**: Open-ended response
4. Set display order
5. Check "Required" if answer is mandatory
6. Click "Add Question"

### 4. View Survey Reports
Navigate to `/admin/survey-reports`:
- See overall statistics
- Click "View Report" on any survey
- View charts and distributions
- Export to CSV

### 5. Test the Survey Flow
1. Log in as a student
2. Complete a course
3. Navigate to certificate generation
4. You'll be redirected to the survey
5. Complete and submit
6. Automatically redirected to certificate

## Survey Selection Logic

The system automatically selects the most specific survey:
1. **First Priority**: Course-specific survey (if exists)
2. **Second Priority**: State-specific survey (if exists)
3. **Third Priority**: General survey (no state/course specified)

Example:
- If a Florida BDI course has a specific survey → uses that
- If no course-specific survey → uses Florida state survey
- If no state survey → uses general survey

## Question Types Explained

### Scale (1-5)
Best for: Overall satisfaction, quality ratings
Display: 5 radio buttons with "Poor" to "Excellent" labels

### Scale (1-10)
Best for: Detailed ratings, NPS-style questions
Display: 10 radio buttons numbered 1-10

### Rating
Best for: Star ratings, visual feedback
Display: 5 stars (clickable)

### Yes/No
Best for: Binary questions, recommendations
Display: Two radio buttons

### Multiple Choice
Best for: "How did you hear about us?", categorical questions
Display: Radio buttons with custom options

### Text
Best for: Feedback, suggestions, comments
Display: Large text area

## Database Tables

All survey data is stored in:
- `surveys` - Survey definitions
- `survey_questions` - Questions
- `survey_responses` - User submissions
- `survey_answers` - Individual answers

## Key Features

✅ Multi-state support (FL, MO, TX, DE)
✅ Course-specific surveys
✅ Required vs optional surveys
✅ Active/inactive toggle
✅ Question reordering
✅ Survey duplication
✅ Response tracking
✅ Statistical reports with charts
✅ CSV export
✅ Event logging
✅ Mobile responsive
✅ Validation for required questions
✅ Auto-redirect after completion

## Admin Capabilities

### Survey Management
- Create unlimited surveys
- Target by state and/or course
- Set as required or optional
- Activate/deactivate anytime
- Duplicate surveys with all questions
- Delete surveys (soft delete)

### Question Management
- Add/edit/delete questions
- Reorder questions
- 6 question types
- Mark as required/optional
- Add custom options for multiple choice

### Reporting
- View response counts
- See completion rates
- Analyze by question
- Filter by date range
- Export to CSV
- Print-friendly views
- State-specific reports

## Integration with Certificate Flow

The survey system is seamlessly integrated:

```
Course Completion → Survey Check → Survey (if required) → Certificate
```

If survey is not required or already completed, students proceed directly to certificate.

## Seeded Sample Data

The system comes with 4 pre-configured surveys:

### 1. General Survey (All States)
- Overall experience (1-5)
- Content clarity (1-5)
- Materials quality (1-5)
- Would recommend? (Yes/No)
- Improvements (Text)
- How did you hear about us? (Multiple choice)

### 2. Florida Survey
- Florida content satisfaction (1-5)
- Meets DHSMV requirements? (Yes/No)
- Traffic law info quality (1-10)
- Additional comments (Text)

### 3. Delaware Survey
- Overall satisfaction (1-5)
- 6-hour length appropriate? (Yes/No)
- Delaware scenarios rating (1-5)
- Most valuable aspect (Text)

### 4. Missouri Survey
- Program rating (1-5)
- Content helpful? (Yes/No)
- Instruction quality (1-10)
- Suggestions (Text)

## Troubleshooting

### Survey Not Showing
- Check if survey is active
- Verify state/course targeting
- Ensure course is completed
- Check if already submitted

### Questions Not Saving
- Verify all required fields filled
- For multiple choice, add options (one per line)
- Check display order is numeric

### Reports Not Loading
- Ensure responses exist
- Check date range filters
- Verify survey has questions

## Next Steps

1. Review the 4 seeded surveys
2. Customize questions for your needs
3. Create state-specific surveys as needed
4. Test the flow with a student account
5. Monitor responses in reports
6. Export data for analysis

## Support

For detailed implementation information, see:
- `SURVEY_SYSTEM_IMPLEMENTATION.md` - Full technical documentation
- Database migrations in `database/migrations/2025_12_03_*`
- Models in `app/Models/Survey*.php`
- Controllers in `app/Http/Controllers/Admin/Survey*.php`
- Views in `resources/views/survey/` and `resources/views/admin/surveys/`
