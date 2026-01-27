<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DelawareAggressiveDrivingSeeder extends Seeder
{
    public function run()
    {
        // Create Delaware Aggressive Driving Course
        $courseId = DB::table('florida_courses')->insertGetId([
            'course_type' => 'Aggressive Driving',
            'title' => 'Delaware Driving/Ticket Dismissal – Aggressive Driving Course',
            'description' => 'Court-ordered Delaware aggressive driving course for ticket dismissal. License BMC09.',
            'state_code' => 'DE',
            'min_pass_score' => 80,
            'total_duration' => 240, // 4 hours
            'price' => 100.00,
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

        $this->command->info('✅ Delaware Aggressive Driving Course created successfully!');
    }

    private function getChapters()
    {
        return [
            [
                'title' => 'Aggressive Driving vs. Road Rage',
                'duration' => 35,
                'content' => '<h3>Chapter I: Aggressive Driving vs. Road Rage</h3><p>Understanding the differences between aggressive driving and road rage, and Delaware laws.</p>',
                'questions' => [
                    ['question' => 'Delaware law defines aggressive driving as committing how many violations in one incident?', 'options' => ['1 or more', '2 or more', '3 or more', '5 or more'], 'correct_answer' => '3 or more', 'explanation' => 'Delaware law defines aggressive driving as committing three or more specific violations in one incident.'],
                    ['question' => 'Which of the following is considered aggressive driving?', 'options' => ['Following the speed limit', 'Tailgating other vehicles', 'Using turn signals', 'Stopping at red lights'], 'correct_answer' => 'Tailgating other vehicles', 'explanation' => 'Tailgating (following too closely) is a form of aggressive driving that increases crash risk.'],
                    ['question' => 'Penalties for aggressive driving in Delaware may include:', 'options' => ['Only warnings', 'Fines between $100-$300', 'Free driving lessons', 'Vehicle impoundment'], 'correct_answer' => 'Fines between $100-$300', 'explanation' => 'Delaware law provides for fines between $100-$300 for aggressive driving violations.'],
                    ['question' => 'Road rage differs from aggressive driving because it involves:', 'options' => ['Faster speeds', 'Intentional violence', 'Better cars', 'More experience'], 'correct_answer' => 'Intentional violence', 'explanation' => 'Road rage involves intentional violent actions, while aggressive driving may not be intentionally violent.'],
                ],
            ],
            [
                'title' => 'Psychology of Aggression',
                'duration' => 35,
                'content' => '<h3>Chapter II: Psychology of Aggression</h3><p>Understanding the psychological factors that contribute to aggressive driving behavior.</p>',
                'questions' => [
                    ['question' => 'Frustration Aggression Theory suggests people become aggressive when:', 'options' => ['They are happy', 'Their path to a goal is blocked', 'They are well-rested', 'They have no goals'], 'correct_answer' => 'Their path to a goal is blocked', 'explanation' => 'The theory states people become aggressive when obstacles prevent them from reaching their goals.'],
                    ['question' => 'Type A personalities are more prone to aggressive driving because they:', 'options' => ['Are more relaxed', 'Have high urgency and competitiveness', 'Drive slower', 'Are more patient'], 'correct_answer' => 'Have high urgency and competitiveness', 'explanation' => 'Type A personalities tend to be impatient, competitive, and have a high sense of urgency.'],
                    ['question' => 'Dehumanization of other drivers means:', 'options' => ['Seeing them as people', 'Treating them with respect', 'Viewing them as obstacles', 'Helping them'], 'correct_answer' => 'Viewing them as obstacles', 'explanation' => 'Dehumanization involves mentally reducing other drivers to obstacles rather than seeing them as people.'],
                ],
            ],
            [
                'title' => 'Stress and Anger Management',
                'duration' => 35,
                'content' => '<h3>Chapter III: Stress and Anger Management</h3><p>Techniques for managing stress and anger while driving.</p>',
                'questions' => [
                    ['question' => 'When you feel anger building while driving, you should:', 'options' => ['Speed up to get away', 'Take deep breaths and calm down', 'Honk your horn repeatedly', 'Follow the other driver closely'], 'correct_answer' => 'Take deep breaths and calm down', 'explanation' => 'Deep breathing and calming techniques help manage anger and prevent aggressive behaviors.'],
                    ['question' => 'A common trigger for road rage is:', 'options' => ['Good weather', 'Light traffic', 'Time pressure', 'New car smell'], 'correct_answer' => 'Time pressure', 'explanation' => 'Feeling rushed or under time pressure is a major trigger for aggressive driving behaviors.'],
                    ['question' => 'Stress can be reduced by:', 'options' => ['Leaving later', 'Planning your route in advance', 'Driving faster', 'Ignoring traffic reports'], 'correct_answer' => 'Planning your route in advance', 'explanation' => 'Planning your route and allowing extra time reduces stress and prevents aggressive driving.'],
                ],
            ],
            [
                'title' => 'Communication and Courtesy',
                'duration' => 30,
                'content' => '<h3>Chapter IV: Communication and Courtesy</h3><p>Learning proper communication and courteous driving behaviors.</p>',
                'questions' => [
                    ['question' => 'The best way to communicate with other drivers is:', 'options' => ['Honking frequently', 'Using proper signals', 'Flashing high beams', 'Shouting'], 'correct_answer' => 'Using proper signals', 'explanation' => 'Turn signals and other proper vehicle signals are the safest way to communicate intentions.'],
                    ['question' => 'Courteous driving includes:', 'options' => ['Blocking intersections', 'Allowing others to merge', 'Racing through yellow lights', 'Tailgating'], 'correct_answer' => 'Allowing others to merge', 'explanation' => 'Allowing others to merge safely is an example of courteous driving behavior.'],
                ],
            ],
            [
                'title' => 'Aggressive Driving Styles and Prevention',
                'duration' => 35,
                'content' => '<h3>Chapter V: Aggressive Driving Styles and Prevention</h3><p>Identifying different types of aggressive drivers and prevention strategies.</p>',
                'questions' => [
                    ['question' => 'The "speeding driver" is characterized by:', 'options' => ['Always driving slowly', 'Feeling perpetually rushed', 'Never changing lanes', 'Following all rules'], 'correct_answer' => 'Feeling perpetually rushed', 'explanation' => 'Speeding drivers feel a constant sense of time urgency and need to get ahead of traffic.'],
                    ['question' => 'A "competitive driver" tends to:', 'options' => ['Avoid other cars', 'Create mental competitions while driving', 'Drive very slowly', 'Never pass anyone'], 'correct_answer' => 'Create mental competitions while driving', 'explanation' => 'Competitive drivers turn routine driving situations into contests with winners and losers.'],
                ],
            ],
            [
                'title' => 'Conflict Resolution and De-escalation',
                'duration' => 35,
                'content' => '<h3>Chapter VI: Conflict Resolution and De-escalation</h3><p>How to handle conflicts with other drivers safely and effectively.</p>',
                'questions' => [
                    ['question' => 'If another driver is acting aggressively toward you, you should:', 'options' => ['Respond aggressively back', 'Give them plenty of space', 'Speed up to get away', 'Get out of your car'], 'correct_answer' => 'Give them plenty of space', 'explanation' => 'Giving aggressive drivers space helps de-escalate the situation and keeps you safe.'],
                    ['question' => 'When should you call 911 while driving?', 'options' => ['When someone cuts you off', 'When you feel threatened or unsafe', 'When traffic is heavy', 'When you\'re running late'], 'correct_answer' => 'When you feel threatened or unsafe', 'explanation' => 'Call 911 if you feel threatened, are being followed, or are in immediate danger.'],
                ],
            ],
            [
                'title' => 'Legal Consequences and Rehabilitation',
                'duration' => 35,
                'content' => '<h3>Chapter VII: Legal Consequences and Rehabilitation</h3><p>Understanding the legal and personal consequences of aggressive driving and the path to rehabilitation.</p>',
                'questions' => [
                    ['question' => 'Aggressive driving convictions can result in:', 'options' => ['Lower insurance rates', 'Increased insurance premiums', 'Free defensive driving courses', 'Bonus points on license'], 'correct_answer' => 'Increased insurance premiums', 'explanation' => 'Aggressive driving violations typically result in higher insurance premiums due to increased risk.'],
                    ['question' => 'The most important step in rehabilitation is:', 'options' => ['Paying fines quickly', 'Commitment to behavior change', 'Buying a new car', 'Avoiding all driving'], 'correct_answer' => 'Commitment to behavior change', 'explanation' => 'True rehabilitation requires a genuine commitment to changing aggressive driving behaviors.'],
                    ['question' => 'Delaware requires aggressive driving offenders to:', 'options' => ['Only pay fines', 'Complete a behavior modification course', 'Sell their vehicle', 'Move to another state'], 'correct_answer' => 'Complete a behavior modification course', 'explanation' => 'Delaware law requires completion of a behavior modification or attitudinal driving course.'],
                ],
            ],
        ];
    }
}
