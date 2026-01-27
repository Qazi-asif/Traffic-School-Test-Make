-- Add some test courses if the courses table is empty
-- This will help test the Quiz Random Selection dropdown

INSERT IGNORE INTO courses (id, title, description, state, duration, price, passing_score, is_active, course_type, delivery_type, certificate_type, created_at, updated_at) VALUES
(1, 'Florida 4-Hour Basic Driver Improvement', 'Florida 4-hour BDI course for traffic violations', 'FL', 240, 29.95, 80, 1, 'BDI', 'Internet', 'BDI', NOW(), NOW()),
(2, 'Florida 8-Hour Advanced Driver Improvement', 'Florida 8-hour ADI course for serious violations', 'FL', 480, 49.95, 80, 1, 'ADI', 'Internet', 'ADI', NOW(), NOW()),
(3, 'Florida 12-Hour Advanced Driver Improvement', 'Florida 12-hour ADI course for multiple violations', 'FL', 720, 69.95, 80, 1, 'ADI', 'Internet', 'ADI', NOW(), NOW()),
(4, 'Texas Defensive Driving Course', 'Texas 6-hour defensive driving course', 'TX', 360, 39.95, 70, 1, 'DDC', 'Internet', 'DDC', NOW(), NOW()),
(5, 'Missouri Driver Safety Program', 'Missouri driver improvement program', 'MO', 480, 34.95, 80, 1, 'DSP', 'Internet', 'DSP', NOW(), NOW());

-- Add some test questions for the Florida 4-hour course (course_id = 1)
-- These will be final exam questions (chapter_id = NULL)
INSERT IGNORE INTO questions (id, course_id, chapter_id, question_text, question_type, options, correct_answer, explanation, points, order_index, quiz_set, created_at, updated_at) VALUES
(1, 1, NULL, 'What is the speed limit in a school zone?', 'multiple_choice', '["15 mph", "20 mph", "25 mph", "30 mph"]', 1, 'School zones typically have a 20 mph speed limit during school hours.', 1, 1, 1, NOW(), NOW()),
(2, 1, NULL, 'When should you use your turn signal?', 'multiple_choice', '["Only when turning left", "Only when changing lanes", "At least 100 feet before turning", "Only in heavy traffic"]', 2, 'Turn signals should be used at least 100 feet before making any turn or lane change.', 1, 2, 1, NOW(), NOW()),
(3, 1, NULL, 'What does a red traffic light mean?', 'multiple_choice', '["Slow down", "Stop completely", "Proceed with caution", "Yield to oncoming traffic"]', 1, 'A red light means you must come to a complete stop.', 1, 3, 1, NOW(), NOW()),
(4, 1, NULL, 'How far should you follow behind another vehicle?', 'multiple_choice', '["1 second", "2 seconds", "3 seconds", "4 seconds"]', 2, 'The 3-second rule provides adequate following distance in normal conditions.', 1, 4, 1, NOW(), NOW()),
(5, 1, NULL, 'What should you do at a four-way stop?', 'multiple_choice', '["Go in order of arrival", "Largest vehicle goes first", "Turn right first", "Honk your horn"]', 0, 'At a four-way stop, vehicles proceed in the order they arrived.', 1, 5, 1, NOW(), NOW());

-- Add more questions to reach a larger pool (simulating the 500 question pool)
INSERT IGNORE INTO questions (course_id, chapter_id, question_text, question_type, options, correct_answer, explanation, points, order_index, quiz_set, created_at, updated_at) 
SELECT 
    1, -- course_id
    NULL, -- chapter_id (final exam)
    CONCAT('Sample question #', n.num, ' - What is the correct driving procedure?'),
    'multiple_choice',
    '["Option A", "Option B", "Option C", "Option D"]',
    FLOOR(RAND() * 4), -- random correct answer
    CONCAT('Explanation for question #', n.num),
    1,
    n.num + 5, -- order_index starting after the 5 manual questions
    1,
    NOW(),
    NOW()
FROM (
    SELECT 6 as num UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10 UNION
    SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14 UNION SELECT 15 UNION
    SELECT 16 UNION SELECT 17 UNION SELECT 18 UNION SELECT 19 UNION SELECT 20 UNION
    SELECT 21 UNION SELECT 22 UNION SELECT 23 UNION SELECT 24 UNION SELECT 25 UNION
    SELECT 26 UNION SELECT 27 UNION SELECT 28 UNION SELECT 29 UNION SELECT 30 UNION
    SELECT 31 UNION SELECT 32 UNION SELECT 33 UNION SELECT 34 UNION SELECT 35 UNION
    SELECT 36 UNION SELECT 37 UNION SELECT 38 UNION SELECT 39 UNION SELECT 40 UNION
    SELECT 41 UNION SELECT 42 UNION SELECT 43 UNION SELECT 44 UNION SELECT 45 UNION
    SELECT 46 UNION SELECT 47 UNION SELECT 48 UNION SELECT 49 UNION SELECT 50
) n;

-- Show what was created
SELECT 'Courses created:' as info;
SELECT id, title, state FROM courses WHERE id IN (1,2,3,4,5);

SELECT 'Questions created for Florida 4-hour course:' as info;
SELECT COUNT(*) as total_questions FROM questions WHERE course_id = 1 AND chapter_id IS NULL;