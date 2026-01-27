<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Delaware3HourRefresherSeeder extends Seeder
{
    public function run()
    {
        // Create Delaware 3-Hour Refresher/Renewal Course
        $courseId = DB::table('florida_courses')->insertGetId([
            'course_type' => 'Refresher',
            'title' => 'Delaware Defensive Driving - 3 Year Refresher/Renewal 3 Hour Course',
            'description' => 'Delaware 3-hour refresher course for defensive driving renewal and insurance benefits.',
            'state_code' => 'DE',
            'min_pass_score' => 80,
            'total_duration' => 180, // 3 hours
            'price' => 17.95,
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

        $this->command->info('✅ Delaware 3-Hour Refresher Course created successfully!');
    }

    private function getChapters()
    {
        return [
            [
                'title' => 'The Dangers of City Driving',
                'duration' => 18,
                'content' => '<h3>Chapter 1 of 10: The Dangers of City Driving</h3><p>This Chapter gives you 7 BASIC Tips on how to drive safely in a city.</p>',
                'questions' => [
                    ['question' => 'What is the recommended scanning distance ahead in city driving?', 'options' => ['5-8 seconds', '10-15 seconds', '20-25 seconds', '30 seconds'], 'correct_answer' => '10-15 seconds', 'explanation' => 'Scanning 10-15 seconds ahead gives you time to see hazards early.'],
                    ['question' => 'The 3-second rule refers to:', 'options' => ['Time to check mirrors', 'Following distance', 'Time to signal', 'Reaction time'], 'correct_answer' => 'Following distance', 'explanation' => 'The 3-second rule helps maintain safe following distance.'],
                    ['question' => 'When should you avoid riding the brakes?', 'options' => ['Never', 'In city driving', 'On highways only', 'At night only'], 'correct_answer' => 'In city driving', 'explanation' => 'Riding brakes in city driving can desensitize other drivers to your brake lights.'],
                ],
            ],
            [
                'title' => 'Reading Signs for Safer Driving',
                'duration' => 18,
                'content' => '<h3>Chapter 2 of 10: Reading Signs for Safer Driving</h3><p>This Chapter gives you 5 BASIC Tips about how you can read signs for safer driving.</p>',
                'questions' => [
                    ['question' => 'What type of sign tells you what you must or must not do?', 'options' => ['Warning signs', 'Regulatory signs', 'Guide signs', 'Information signs'], 'correct_answer' => 'Regulatory signs', 'explanation' => 'Regulatory signs inform drivers of traffic laws that must be obeyed.'],
                    ['question' => 'Warning signs are typically what color?', 'options' => ['Red', 'Yellow', 'Blue', 'Green'], 'correct_answer' => 'Yellow', 'explanation' => 'Warning signs are typically yellow to alert drivers to hazards ahead.'],
                ],
            ],
            [
                'title' => 'Handling Emergencies',
                'duration' => 18,
                'content' => '<h3>Chapter 3 of 10: Handling Emergencies</h3><p>This Chapter gives you 6 BASIC Tips on How to Handle Emergencies.</p>',
                'questions' => [
                    ['question' => 'When your brakes fail, you should:', 'options' => ['Panic and swerve', 'Pump the brakes and use emergency brake', 'Speed up', 'Turn off the engine'], 'correct_answer' => 'Pump the brakes and use emergency brake', 'explanation' => 'Pumping brakes may restore pressure, and emergency brake provides backup stopping power.'],
                    ['question' => 'If your tire blows out while driving, you should:', 'options' => ['Brake hard immediately', 'Grip wheel firmly and slow gradually', 'Swerve to avoid traffic', 'Speed up'], 'correct_answer' => 'Grip wheel firmly and slow gradually', 'explanation' => 'Maintaining control and gradually slowing prevents loss of vehicle control.'],
                ],
            ],
            [
                'title' => 'Guarding Your Driving Privileges',
                'duration' => 18,
                'content' => '<h3>Chapter 4 of 10: Guarding Your Driving Privileges</h3><p>This Chapter gives you 11 BASIC Tips about guarding your driving privileges in Delaware.</p>',
                'questions' => [
                    ['question' => 'Delaware uses what system to track driving violations?', 'options' => ['Warning system', 'Point system', 'Fine system', 'License system'], 'correct_answer' => 'Point system', 'explanation' => 'Delaware uses a point system where violations add points to your record.'],
                    ['question' => 'Liability insurance in Delaware is:', 'options' => ['Optional', 'Required', 'Only for new drivers', 'Only for commercial vehicles'], 'correct_answer' => 'Required', 'explanation' => 'Delaware law requires all drivers to carry liability insurance.'],
                ],
            ],
            [
                'title' => 'Open Highway Driving',
                'duration' => 18,
                'content' => '<h3>Chapter 5 of 10: Open Highway Driving</h3><p>This Chapter gives you 13 BASIC Tips about safe driving on Delaware highways and freeways.</p>',
                'questions' => [
                    ['question' => 'On highways, your following distance should be:', 'options' => ['Same as city driving', 'Less than city driving', 'Greater than city driving', 'It doesn\'t matter'], 'correct_answer' => 'Greater than city driving', 'explanation' => 'Higher highway speeds require greater following distances for safe stopping.'],
                    ['question' => 'When merging onto a highway, you should:', 'options' => ['Stop and wait', 'Match traffic speed', 'Drive slowly', 'Use hazard lights'], 'correct_answer' => 'Match traffic speed', 'explanation' => 'Matching traffic speed makes merging safer for all drivers.'],
                ],
            ],
            [
                'title' => 'Choosing Your Path and Making a Pass',
                'duration' => 18,
                'content' => '<h3>Chapter 6 of 10: Choosing Your Path and Making a Pass</h3><p>This Chapter gives you 9 BASIC Tips about different types of lanes and safe passing.</p>',
                'questions' => [
                    ['question' => 'Which lane should slower traffic use?', 'options' => ['Left lane', 'Right lane', 'Center lane', 'Any lane'], 'correct_answer' => 'Right lane', 'explanation' => 'Slower traffic should keep right to allow faster traffic to pass safely.'],
                    ['question' => 'Before changing lanes, you should:', 'options' => ['Just use mirrors', 'Check blind spots', 'Only signal', 'Speed up'], 'correct_answer' => 'Check blind spots', 'explanation' => 'Always check blind spots by turning your head before changing lanes.'],
                ],
            ],
            [
                'title' => 'Speed Laws and Backing Up Safely',
                'duration' => 18,
                'content' => '<h3>Chapter 7 of 10: Speed Laws and Backing Up Safely</h3><p>This Chapter gives you 5 BASIC Tips about safe speed on the road and proper backing.</p>',
                'questions' => [
                    ['question' => 'Posted speed limits represent:', 'options' => ['Minimum speeds', 'Maximum speeds under ideal conditions', 'Recommended speeds in all conditions', 'Average traffic speeds'], 'correct_answer' => 'Maximum speeds under ideal conditions', 'explanation' => 'Speed limits are maximums that should be reduced based on conditions.'],
                    ['question' => 'When backing up, you should:', 'options' => ['Go fast to get it over with', 'Look over your shoulder', 'Only use mirrors', 'Honk continuously'], 'correct_answer' => 'Look over your shoulder', 'explanation' => 'Looking over your shoulder provides the best view when backing up.'],
                ],
            ],
            [
                'title' => 'Safety Equipment and Hazardous Conditions',
                'duration' => 18,
                'content' => '<h3>Chapter 8 of 10: Safety Equipment and Hazardous Conditions</h3><p>This Chapter gives you 18 BASIC Tips about your car\'s safety equipment and hazardous conditions.</p>',
                'questions' => [
                    ['question' => 'The most important safety equipment in your vehicle is:', 'options' => ['Airbags', 'Seat belts', 'Anti-lock brakes', 'Headlights'], 'correct_answer' => 'Seat belts', 'explanation' => 'Seat belts are the most effective safety device for preventing injury in crashes.'],
                    ['question' => 'In foggy conditions, you should use:', 'options' => ['High beams', 'Low beams', 'Hazard lights', 'No lights'], 'correct_answer' => 'Low beams', 'explanation' => 'Low beams provide better visibility in fog without reflecting back at you.'],
                ],
            ],
            [
                'title' => 'Under the Influence',
                'duration' => 18,
                'content' => '<h3>Chapter 9 of 10: Under the Influence</h3><p>This Chapter gives you 6 BASIC Tips about how drugs and alcohol affect driving safety.</p>',
                'questions' => [
                    ['question' => 'What is the legal BAC limit for drivers 21 and over in Delaware?', 'options' => ['0.05%', '0.08%', '0.10%', '0.12%'], 'correct_answer' => '0.08%', 'explanation' => 'The legal BAC limit for drivers 21 and over is 0.08% in Delaware.'],
                    ['question' => 'Prescription medications can:', 'options' => ['Never affect driving', 'Sometimes impair driving', 'Only help driving', 'Only affect young drivers'], 'correct_answer' => 'Sometimes impair driving', 'explanation' => 'Many prescription medications can impair driving ability and should be used with caution.'],
                ],
            ],
            [
                'title' => 'Common Sense Driving',
                'duration' => 18,
                'content' => '<h3>Chapter 10 of 10: Common Sense Driving</h3><p>This Chapter gives you 13 BASIC Tips about common sense in driving and driving defensively.</p>',
                'questions' => [
                    ['question' => 'Defensive driving means:', 'options' => ['Driving aggressively', 'Anticipating problems', 'Driving slowly always', 'Honking frequently'], 'correct_answer' => 'Anticipating problems', 'explanation' => 'Defensive driving involves anticipating and preparing for potential hazards.'],
                    ['question' => 'The best way to avoid road rage is to:', 'options' => ['Drive aggressively back', 'Stay calm and courteous', 'Speed up', 'Make gestures'], 'correct_answer' => 'Stay calm and courteous', 'explanation' => 'Staying calm and courteous helps prevent escalation of road rage incidents.'],
                ],
            ],
        ];
    }
}
