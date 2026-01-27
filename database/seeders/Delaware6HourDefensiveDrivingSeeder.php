<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Delaware6HourDefensiveDrivingSeeder extends Seeder
{
    public function run()
    {
        // Create Delaware 6-Hour Insurance Discount Course
        $courseId = DB::table('florida_courses')->insertGetId([
            'course_type' => 'Insurance Discount',
            'title' => 'Delaware Insurance Discount - 3 Year Refresher/Renewal 6 Hour Course',
            'description' => 'Complete 6-hour Delaware Insurance Discount Course for 10% insurance discount and 3-point credit on driving record.',
            'state_code' => 'DE',
            'min_pass_score' => 80,
            'total_duration' => 360, // 6 hours
            'price' => 25.00,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $chapters = $this->getChapters();

        foreach ($chapters as $index => $chapter) {
            $chapterId = DB::table('chapters')->insertGetId([
                'course_id' => $courseId,
                'title' => $chapter['title'],
                'content' => $chapter['content'],
                'order_index' => $index + 1,
                'duration' => $chapter['duration'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            // Add quiz questions for each chapter
            foreach ($chapter['questions'] as $qIndex => $q) {
                DB::table('questions')->insert([
                    'chapter_id' => $chapterId,
                    'course_id' => $courseId,
                    'question_text' => $q['question'],
                    'question_type' => 'multiple_choice',
                    'options' => json_encode($q['options']),
                    'correct_answer' => $q['correct_answer'],
                    'explanation' => $q['explanation'] ?? null,
                    'order_index' => $qIndex + 1,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }

        $this->command->info('✅ Delaware 6-Hour Defensive Driving Course created successfully!');
    }

    private function getChapters()
    {
        return [
            [
                'title' => 'The Dangers of City Driving',
                'duration' => 35,
                'content' => '<h3>Chapter 1: The Dangers of City Driving</h3><p>7 BASIC Tips on how to drive safely in a city.</p>',
                'questions' => [
                    [
                        'question' => 'What is the recommended scanning distance ahead in city driving?',
                        'options' => ['5-8 seconds', '10-15 seconds', '20-25 seconds', '30 seconds'],
                        'correct_answer' => '10-15 seconds',
                        'explanation' => 'Scanning 10-15 seconds ahead gives you time to see hazards early and make safe decisions.',
                    ],
                    [
                        'question' => 'What insurance discount do you receive for completing this Delaware course?',
                        'options' => ['5%', '10%', '15%', '20%'],
                        'correct_answer' => '10%',
                        'explanation' => 'Delaware guarantees a 10% vehicle insurance discount for completing this course.',
                    ],
                    [
                        'question' => 'When emergency vehicles approach with sirens and lights, you should:',
                        'options' => ['Speed up to get out of the way', 'Pull to the right and stop', 'Continue at normal speed', 'Pull to the left'],
                        'correct_answer' => 'Pull to the right and stop',
                        'explanation' => 'Delaware law requires drivers to pull to the right-hand curb and stop until emergency vehicles pass.',
                    ],
                    [
                        'question' => 'The 3-second rule refers to:',
                        'options' => ['Time to check mirrors', 'Following distance', 'Time to signal', 'Reaction time'],
                        'correct_answer' => 'Following distance',
                        'explanation' => 'The 3-second rule helps maintain a safe following distance behind other vehicles.',
                    ],
                    [
                        'question' => 'Why should you avoid riding the brakes in city driving?',
                        'options' => ['It wastes fuel', 'Cars behind may ignore your brake lights', 'It wears out brakes faster', 'All of the above'],
                        'correct_answer' => 'All of the above',
                        'explanation' => 'Riding brakes causes multiple problems including desensitizing other drivers to your brake lights.',
                    ],
                ],
            ],
            [
                'title' => 'Reading Signs for Safer Driving',
                'duration' => 30,
                'content' => '<h3>Chapter 2: Reading Signs for Safer Driving</h3><p>5 BASIC Tips about reading signs for safer driving.</p>',
                'questions' => [
                    [
                        'question' => 'What type of sign tells you what you must or must not do?',
                        'options' => ['Warning signs', 'Regulatory signs', 'Guide signs', 'Information signs'],
                        'correct_answer' => 'Regulatory signs',
                        'explanation' => 'Regulatory signs inform drivers of traffic laws and regulations that must be obeyed.',
                    ],
                    [
                        'question' => 'Warning signs are designed to:',
                        'options' => ['Give directions', 'Alert you to hazards', 'Show speed limits', 'Indicate parking areas'],
                        'correct_answer' => 'Alert you to hazards',
                        'explanation' => 'Warning signs alert drivers to potential hazards or changes in road conditions ahead.',
                    ],
                    [
                        'question' => 'Guide signs provide information about:',
                        'options' => ['Speed limits', 'Directions and distances', 'Traffic violations', 'Emergency procedures'],
                        'correct_answer' => 'Directions and distances',
                        'explanation' => 'Guide signs help drivers navigate by providing directional and distance information.',
                    ],
                    [
                        'question' => 'A red octagonal sign means:',
                        'options' => ['Yield', 'Stop', 'No entry', 'Caution'],
                        'correct_answer' => 'Stop',
                        'explanation' => 'Red octagonal signs are universally recognized as stop signs.',
                    ],
                ],
            ],
            [
                'title' => 'Guarding Your Driving Privileges',
                'duration' => 40,
                'content' => '<h3>Chapter 3: Guarding Your Driving Privileges in Delaware</h3><p>11 BASIC Tips about guarding your driving privileges.</p>',
                'questions' => [
                    [
                        'question' => 'What happens if you accumulate too many points on your Delaware driving record?',
                        'options' => ['Nothing happens', 'License suspension', 'Mandatory retesting only', 'Fine only'],
                        'correct_answer' => 'License suspension',
                        'explanation' => 'Delaware uses a point system where too many points can result in license suspension.',
                    ],
                    [
                        'question' => 'Liability insurance in Delaware is:',
                        'options' => ['Optional', 'Required', 'Only for new drivers', 'Only for commercial vehicles'],
                        'correct_answer' => 'Required',
                        'explanation' => 'Delaware law requires all drivers to carry liability insurance.',
                    ],
                    [
                        'question' => 'How many points can you receive credit for completing this course?',
                        'options' => ['1 point', '2 points', '3 points', '5 points'],
                        'correct_answer' => '3 points',
                        'explanation' => 'Completing this course may result in a 3-point credit to your Delaware driving record.',
                    ],
                    [
                        'question' => 'Proof of insurance must be:',
                        'options' => ['Kept at home', 'Carried in the vehicle at all times', 'Only shown when buying a car', 'Filed with the DMV annually'],
                        'correct_answer' => 'Carried in the vehicle at all times',
                        'explanation' => 'Delaware law requires drivers to carry proof of insurance in their vehicle at all times.',
                    ],
                ],
            ],
            [
                'title' => 'Open Highway Driving',
                'duration' => 45,
                'content' => '<h3>Chapter 4: The Demands of Open Highway Driving</h3><p>13 BASIC Tips about safe driving on Delaware highways.</p>',
                'questions' => [
                    [
                        'question' => 'On highways, what should you do with your following distance compared to city driving?',
                        'options' => ['Keep it the same', 'Decrease it', 'Increase it', 'It doesn\'t matter'],
                        'correct_answer' => 'Increase it',
                        'explanation' => 'Higher speeds on highways require greater following distances for safe stopping.',
                    ],
                    [
                        'question' => 'When merging onto a highway, you should:',
                        'options' => ['Stop and wait for an opening', 'Match the speed of traffic', 'Drive slowly until you merge', 'Use your hazard lights'],
                        'correct_answer' => 'Match the speed of traffic',
                        'explanation' => 'Matching traffic speed makes merging safer and smoother for all drivers.',
                    ],
                    [
                        'question' => 'The left lane on a multi-lane highway should be used for:',
                        'options' => ['Slow traffic', 'Passing only', 'Any speed you want', 'Trucks only'],
                        'correct_answer' => 'Passing only',
                        'explanation' => 'The left lane is designated for passing slower traffic, not for continuous travel.',
                    ],
                    [
                        'question' => 'When should you use the 4-second rule instead of the 3-second rule?',
                        'options' => ['In good weather', 'When wet or towing', 'Only at night', 'Never'],
                        'correct_answer' => 'When wet or towing',
                        'explanation' => 'Wet conditions and towing require increased following distance for safety.',
                    ],
                ],
            ],
            [
                'title' => 'Choosing Your Path and Making a Pass',
                'duration' => 35,
                'content' => '<h3>Chapter 5: Choosing Your Path and Making a Pass</h3><p>9 BASIC Tips about different types of lanes and safe passing.</p>',
                'questions' => [
                    [
                        'question' => 'Which lane should slower traffic use?',
                        'options' => ['Left lane', 'Right lane', 'Center lane', 'Any lane'],
                        'correct_answer' => 'Right lane',
                        'explanation' => 'Slower traffic should keep to the right to allow faster traffic to pass safely.',
                    ],
                    [
                        'question' => 'You may pass on the right when:',
                        'options' => ['Never', 'The vehicle ahead is turning left', 'You\'re in a hurry', 'On any multi-lane road'],
                        'correct_answer' => 'The vehicle ahead is turning left',
                        'explanation' => 'Delaware law allows passing on the right when the vehicle ahead is making a left turn.',
                    ],
                    [
                        'question' => 'Before changing lanes, you should:',
                        'options' => ['Just use mirrors', 'Check blind spots', 'Only signal', 'Speed up'],
                        'correct_answer' => 'Check blind spots',
                        'explanation' => 'Always check blind spots by turning your head before changing lanes.',
                    ],
                ],
            ],
        ];
    }
}
