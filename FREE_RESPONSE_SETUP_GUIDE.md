# Free Response Quiz Setup Guide

## How It Works (Simple Explanation)

Think of it like this:
1. **Placement** = "Where to put the quiz in the course" (like "after chapter 3")
2. **Questions** = "The actual questions students answer" (the question pool)
3. **Random Selection** = "How many questions each student gets from the pool"

## Step-by-Step Setup

### Step 1: Create a Quiz Placement
**URL:** `/admin/free-response-quiz-placements`

**What this does:** Creates a "slot" for a quiz in your course

**Settings:**
- **Course:** Select your course (e.g., Florida 12-Hour ADI)
- **After Chapter:** Choose where to place it (or leave empty for end of course)
- **Quiz Title:** "Free Response Questions" (or whatever you want)
- **Random Selection:** ✅ Enable this
- **Questions to Select:** 10 (how many questions each student gets)
- **24-Hour Grading:** ✅ Enable if you want manual review

### Step 2: Create Questions for the Pool
**URL:** `/admin/free-response-quiz`

**What this does:** Creates the actual questions that go into the pool

**Settings:**
- **Course:** Same course as step 1
- **Quiz Placement:** Select the placement you created in step 1
- **Question Text:** Write your question
- **Points:** 5 points (or whatever)

**Repeat this step** to create 20-50 questions for your pool.

### Step 3: How Students See It

When a student reaches the quiz placement:
- System randomly selects 10 questions from your pool of 20-50
- Each student gets different questions
- Student answers their 10 questions
- If 24-hour grading is enabled, they see "under review" message

## Example Setup: Florida 12-Hour Course

### Placement Settings:
- Course: Florida 12-Hour Advanced Driver Improvement
- After Chapter: (empty - end of course)
- Quiz Title: "Free Response Assessment"
- Random Selection: ✅ Enabled
- Questions to Select: 10
- 24-Hour Grading: ✅ Enabled

### Question Pool:
Create 30 questions like:
1. "Describe three factors that contribute to aggressive driving behavior."
2. "Explain how weather conditions affect safe following distance."
3. "What are the key elements of defensive driving?"
... (27 more questions)

### Result:
- Student A gets questions: 1, 5, 8, 12, 15, 19, 23, 26, 28, 30
- Student B gets questions: 2, 4, 7, 11, 16, 18, 21, 24, 27, 29
- Each student answers 10 questions from the pool of 30

## Troubleshooting

### "No questions showing in course"
1. Check if placement is active
2. Check if questions are linked to the placement
3. Check if questions are active
4. Run debug script: `php debug_free_response_setup.php`

### "All students get same questions"
- Make sure "Random Selection" is enabled in placement
- Check that multiple questions exist for the placement

### "Questions not in pool"
- Make sure questions have the correct `placement_id`
- Check that course_id matches between placement and questions