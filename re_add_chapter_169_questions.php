<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Question;
use App\Models\Chapter;

// Chapter ID
$chapterId = 169;

// Verify chapter exists
$chapter = Chapter::find($chapterId);
if (!$chapter) {
    echo "❌ Chapter {$chapterId} not found!\n";
    exit(1);
}

echo "📚 Re-adding questions to Chapter {$chapterId}: {$chapter->title}\n";
echo "Course ID: {$chapter->course_id}\n\n";

// Questions data
$questions = [
    [
        'question_text' => 'The two-way left turn lane_______.',
        'options' => [
            'A' => 'may not be used for passing.',
            'B' => 'Can never be used for U-turns.',
            'C' => 'Are set aside for the use of vehicles turning left or right.',
            'D' => 'Both C and B are correct.'
        ],
        'correct_answer' => 'A'
    ],
    [
        'question_text' => 'You should scan the road__________ ahead of your vehicle.',
        'options' => [
            'A' => '1 to 2 seconds',
            'B' => '30 to 35 seconds',
            'C' => '10-15 seconds',
            'D' => '½ mile'
        ],
        'correct_answer' => 'C'
    ],
    [
        'question_text' => 'The following is incorrect. Pedestrians have a duty to__________.',
        'options' => [
            'A' => 'Cross at an intersection or crosswalk',
            'B' => 'Stay out of bike lanes.',
            'C' => 'Yield to vehicles when in crosswalk',
            'D' => 'Not cross diagonally at an intersection.'
        ],
        'correct_answer' => 'C'
    ],
    [
        'question_text' => 'Drivers must obey signals from school crossing guards____________.',
        'options' => [
            'A' => 'During school hours',
            'B' => 'If they go to that school',
            'C' => 'At all times',
            'D' => 'If it isn\'t a school holiday'
        ],
        'correct_answer' => 'C'
    ],
    [
        'question_text' => 'Drivers in the city face___________.',
        'options' => [
            'A' => 'A greater space cushion.',
            'B' => 'Higher speeds and faster decision times',
            'C' => 'Slower vehicles in special lanes',
            'D' => 'Distractions from noise, advertisements and traffic signs'
        ],
        'correct_answer' => 'D'
    ],
    [
        'question_text' => 'Blind pedestrians____________.',
        'options' => [
            'A' => 'Always have a white cane and a dog for easy recognition.',
            'B' => 'May or may not have a white cane or dog with them.',
            'C' => 'Must follow all regular pedestrian rules.',
            'D' => 'Must yield the right of way in heavy traffic.'
        ],
        'correct_answer' => 'B'
    ],
    [
        'question_text' => 'When emerging from an alley, you are not suppose to____________.',
        'options' => [
            'A' => 'your right of way to make a right turn onto the roadway.',
            'B' => 'permitted to sit over a sidewalk before you carefully make a turn.',
            'C' => 'illegal to stop on the sidewalk while you check traffic to make your turn.',
            'D' => 'all of the above.'
        ],
        'correct_answer' => 'D'
    ],
    [
        'question_text' => 'Endangerment of a highway worker is now a________________.',
        'options' => [
            'A' => 'problem debated in the state legislature.',
            'B' => 'Crime punishable by a fine of up to $2000, if no one is hurt.',
            'C' => 'Crime punishable by a fine of up to $10,000 if there is injury.',
            'D' => 'B and C are correct.'
        ],
        'correct_answer' => 'D'
    ],
    [
        'question_text' => 'Missouri\'s "Move Over Law" states in part that,',
        'options' => [
            'A' => 'When an emergency vehicle approaches, motorists must move over.',
            'B' => 'When law enforcement flashes a blue light, traffic must move over.',
            'C' => 'When a trucker is in your blind spot, the motorist must move over.',
            'D' => 'When a slow-moving vehicle blocks 5 or more cars it must move over.'
        ],
        'correct_answer' => 'A'
    ],
    [
        'question_text' => 'Motorcycles are on city streets, and:',
        'options' => [
            'A' => '98% of accidents with motorcycles and bikes result in injury.',
            'B' => 'Drivers of cars often violate the motorcyclist right of way.',
            'C' => 'It can be harder to see motorcyclists on the road because of their size.',
            'D' => 'All of the above.'
        ],
        'correct_answer' => 'D'
    ]
];

// Add questions to database
$addedCount = 0;
foreach ($questions as $index => $questionData) {
    $questionNumber = $index + 1;
    
    try {
        $question = Question::create([
            'chapter_id' => $chapterId,
            'course_id' => $chapter->course_id,
            'question_text' => $questionData['question_text'],
            'question_type' => 'multiple_choice',
            'options' => $questionData['options'],
            'correct_answer' => $questionData['correct_answer'],
            'explanation' => null,
            'points' => 1,
            'order_index' => $questionNumber,
            'quiz_set' => 1
        ]);
        
        echo "✅ Question {$questionNumber}: {$question->question_text}\n";
        echo "   Correct Answer: {$questionData['correct_answer']} - {$questionData['options'][$questionData['correct_answer']]}\n\n";
        
        $addedCount++;
    } catch (Exception $e) {
        echo "❌ Error adding question {$questionNumber}: " . $e->getMessage() . "\n";
    }
}

echo "🎉 Successfully added {$addedCount} questions to Chapter {$chapterId}!\n";
echo "📊 Total questions in chapter: " . Question::where('chapter_id', $chapterId)->count() . "\n";