<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FloridaDefensiveDrivingFinalSeeder extends Seeder
{
    public function run()
    {
        $course = DB::table('florida_courses')->where('title', 'LIKE', '%Defensive Driving%')->first();

        if (! $course) {
            $this->command->error('Florida Defensive Driving Course not found.');

            return;
        }

        $this->addFinalChapters($course->id);
    }

    private function addFinalChapters($courseId)
    {
        $chapters = [
            [
                'title' => 'Chapter 13: Speed Laws and Backing Safety',
                'description' => 'Understanding speed limits, basic speed law, and safe backing procedures',
                'duration' => 35,
                'content' => 'Speed laws exist to maintain safe traffic flow and prevent collisions. This chapter covers speed regulations and backing safety.

**Basic Speed Law:**
You must never drive faster than is safe for present conditions, regardless of posted speed limits. Conditions affecting safe speed include:
- Weather (rain, fog, snow)
- Traffic density
- Road conditions
- Visibility
- Pedestrian activity

**Speed Limit Guidelines:**
- Reduce speed when children are present
- Slow down in residential and business areas
- Adjust speed for elderly pedestrians
- Drive slower in construction zones
- Fines are often doubled in work zones

**Backing Safety:**
Backing up is unsafe at any speed. Key safety tips:
- Check behind your vehicle before getting in
- Never use guesswork when backing
- Turn your head and look over your shoulder
- Use mirrors but don\'t rely on them alone
- Back up slowly and be prepared to stop
- Have someone guide you when possible

**Stopping Distances:**
At 55 mph, it takes about 400 feet to react and bring a car to complete stop. This includes:
- Reaction time (seeing the hazard)
- Brake application time
- Actual stopping distance

**Railroad Crossings:**
Never drive through, around, or under any closed crossing gate at a railroad crossing. Always stop and look both ways before crossing tracks.',
                'questions' => [
                    [
                        'question' => 'The "basic speed law" says that you _____ drive faster than is safe for present conditions.',
                        'options' => [
                            'may sometimes',
                            'can',
                            'must never',
                            'shouldn\'t',
                        ],
                        'correct_answer' => 'must never',
                        'explanation' => 'The basic speed law requires that you never drive faster than is safe for current conditions, regardless of posted limits.',
                    ],
                    [
                        'question' => 'At 55 mph, it takes about _____ to react and bring a car to a complete stop.',
                        'options' => [
                            '210 feet',
                            '400 feet',
                            '½ mile',
                            'a football field',
                        ],
                        'correct_answer' => '400 feet',
                        'explanation' => 'At 55 mph, the total stopping distance including reaction time is approximately 400 feet.',
                    ],
                ],
            ],
            [
                'title' => 'Chapter 14: Vehicle Safety Equipment and Hazardous Conditions',
                'description' => 'Understanding required safety equipment and handling hazardous driving conditions',
                'duration' => 45,
                'content' => 'Modern vehicles are equipped with essential safety equipment designed to help you navigate hazardous conditions safely.

**Required Safety Equipment:**
- **Lights**: Headlights, taillights, brake lights, turn signals, hazard lights
- **Brakes**: Two separate braking systems (service and parking brakes)
- **Windshields and Mirrors**: Safety glass, clean and unbroken surfaces
- **Reflectors**: Red reflectors on rear of vehicle
- **Horn**: Audible warning device for safety communication
- **Tires**: Proper tread depth (minimum 2/32 inch), correct pressure
- **Safety Belts**: Required for all occupants
- **Airbags**: Supplemental restraint system

**Lighting Requirements:**
Use headlights:
- From sunset to sunrise
- When visibility is less than 1000 feet
- Whenever windshield wipers are in continuous use
- In rain, fog, or other adverse weather
- On winding or narrow roads

**High vs. Low Beams:**
- High beams: Switch to low when approaching vehicle is within 500 feet
- Low beams: Use when following within 300 feet of another vehicle
- Fog: Always use low beams to avoid glare

**Anti-Lock Brake Systems (ABS):**
- Prevents wheel lockup during emergency braking
- Allows steering while braking hard
- May cause pedal pulsation - this is normal
- Brake firmly and steer to avoid obstacles

**Tire Safety:**
- Check air pressure regularly
- Inspect for wear, cuts, or bulges
- Ensure adequate tread depth
- Rotate tires as recommended
- Keep spare tire in good condition',
                'questions' => [
                    [
                        'question' => 'You must use headlights whenever:',
                        'options' => [
                            'Only at night',
                            'Whenever windshield wipers are in continuous use',
                            'Only in heavy rain',
                            'Only when visibility is poor',
                        ],
                        'correct_answer' => 'Whenever windshield wipers are in continuous use',
                        'explanation' => 'Florida law requires headlights whenever windshield wipers are in continuous use, among other conditions.',
                    ],
                    [
                        'question' => 'Minimum tire tread depth should be:',
                        'options' => [
                            '1/32 inch',
                            '2/32 inch',
                            '3/32 inch',
                            '4/32 inch',
                        ],
                        'correct_answer' => '2/32 inch',
                        'explanation' => 'Tire tread should be no less than 2/32 of an inch deep for safe traction on the road.',
                    ],
                ],
            ],
            [
                'title' => 'Chapter 15: Alcohol, Drugs, and Impaired Driving',
                'description' => 'Understanding the dangers of impaired driving and Florida DUI laws',
                'duration' => 50,
                'content' => 'Alcohol and drugs significantly impair driving ability and are major causes of traffic fatalities. Understanding these effects and the legal consequences is crucial for all drivers.

**Alcohol Facts:**
- Alcohol is the most widely used drug in society
- Impairment begins at BAC levels as low as 0.01%
- Legal limit is 0.08% BAC for drivers 21 and over
- Zero tolerance (0.02% BAC) for drivers under 21
- Commercial drivers: 0.04% BAC limit

**Blood Alcohol Content (BAC) Effects:**
- 0.01-0.05%: Behavior appears normal but judgment begins to decline
- 0.03-0.12%: Euphoria stage - decreased judgment and control
- 0.09-0.25%: Excitement stage - emotional instability, impaired balance
- 0.18-0.30%: Confusion stage - staggering, slurred speech
- 0.25-0.40%: Coma stage - unconsciousness, impaired vital functions

**One Drink Equals:**
- 1.5 ounces of hard liquor (80 proof)
- 6 ounces of wine
- 12 ounces of beer

**Factors Affecting BAC:**
- Body weight and composition
- Time period of consumption
- Food consumption
- Gender and age
- Medications

**Drug Impairment:**
- Prescription medications can impair driving
- Over-the-counter drugs may cause drowsiness
- Illegal drugs severely impair judgment and coordination
- Never mix alcohol with any drugs
- Read all medication labels for warnings

**DUI Penalties (First Offense):**
- License revocation: 6 months to 2 years
- Fines: $500 to $1,500
- Possible jail time: 60 days to 6 months
- Mandatory alcohol education program
- Ignition interlock device may be required
- Increased insurance rates

**Designated Driver Program:**
- Must have valid driver\'s license
- Must abstain completely from alcohol
- Must identify themselves to servers
- Helps prevent impaired driving incidents',
                'questions' => [
                    [
                        'question' => 'For drivers under 21, the legal BAC limit is:',
                        'options' => [
                            '0.08%',
                            '0.04%',
                            '0.02%',
                            '0.00%',
                        ],
                        'correct_answer' => '0.02%',
                        'explanation' => 'Florida has a zero tolerance policy with a 0.02% BAC limit for drivers under 21 years of age.',
                    ],
                    [
                        'question' => 'The body can metabolize approximately _____ of alcohol per hour.',
                        'options' => [
                            'half an ounce',
                            'one ounce',
                            'two ounces',
                            'three ounces',
                        ],
                        'correct_answer' => 'one ounce',
                        'explanation' => 'The human body can metabolize approximately one ounce of alcohol per hour, regardless of body size.',
                    ],
                ],
            ],
            [
                'title' => 'Chapter 16: Identifying and Avoiding Impaired Drivers',
                'description' => 'Recognizing signs of impaired drivers and taking evasive action',
                'duration' => 30,
                'content' => 'Learning to identify impaired drivers on the road can help you avoid dangerous situations and potentially save lives.

**Signs of Impaired Driving:**

**Lane Drifting:**
- Vehicle drifting from side to side within lane
- Crossing lane lines repeatedly
- Inability to maintain straight path
- Weaving between lanes

**Speed and Braking Variations:**
- Frequent unexplained braking
- Alternating between slow and fast speeds
- Sudden acceleration or deceleration
- Driving significantly slower than traffic flow

**Erratic Behavior:**
- Turning opposite to signal indication
- Delayed reactions at traffic signals
- Driving too close to curb or center line
- Making wide or abrupt turns
- Stopping inappropriately

**What to Do When You Spot an Impaired Driver:**
- Maintain safe distance - stay back
- Do not attempt to pass unless absolutely safe
- Call 911 if you have a cell phone
- Provide vehicle description and license plate
- Give location and direction of travel
- Take alternate route if possible

**Protecting Yourself:**
- Always wear your seatbelt
- Stay alert, especially during high-risk times:
  - Friday and Saturday nights
  - Holiday periods
  - Late night/early morning hours (midnight to 6 AM)
- Avoid driving during peak impaired driving times when possible
- Be extra cautious at intersections

**Reporting Impaired Drivers:**
When calling 911, provide:
- Vehicle description (make, model, color)
- License plate number if visible
- Location and direction of travel
- Specific dangerous behaviors observed
- Your location and contact information',
                'questions' => [
                    [
                        'question' => 'Which behavior is most indicative of an impaired driver?',
                        'options' => [
                            'Driving exactly at the speed limit',
                            'Lane drifting and erratic speed changes',
                            'Using turn signals properly',
                            'Maintaining proper following distance',
                        ],
                        'correct_answer' => 'Lane drifting and erratic speed changes',
                        'explanation' => 'Lane drifting and erratic speed changes are classic signs of impaired driving due to reduced coordination and judgment.',
                    ],
                    [
                        'question' => 'When you spot a suspected impaired driver, you should:',
                        'options' => [
                            'Try to stop them yourself',
                            'Flash your lights at them',
                            'Maintain safe distance and call 911',
                            'Speed up to get past them quickly',
                        ],
                        'correct_answer' => 'Maintain safe distance and call 911',
                        'explanation' => 'The safest approach is to maintain distance from the impaired driver and report them to authorities.',
                    ],
                ],
            ],
        ];

        foreach ($chapters as $index => $chapterData) {
            $chapterOrder = $index + 13; // Continue from previous chapters

            $chapterId = DB::table('chapters')->insertGetId([
                'course_id' => $courseId,
                'title' => $chapterData['title'],
                'content' => $chapterData['content'],

                'order_index' => $chapterOrder,
                'duration' => $chapterData['duration'],

                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            if (isset($chapterData['questions'])) {
                foreach ($chapterData['questions'] as $qIndex => $questionData) {
                    DB::table('questions')->insert([
                        'chapter_id' => $chapterId,
                        'course_id' => $courseId,
                        'question_text' => $questionData['question'],
                        'question_type' => 'multiple_choice',
                        'options' => json_encode($questionData['options']),
                        'correct_answer' => $questionData['correct_answer'],
                        'explanation' => $questionData['explanation'],
                        'order_index' => $qIndex + 1,
                        'points' => 1,

                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                }
            }
        }

        $this->command->info('Final comprehensive Florida Defensive Driving Course chapters created!');
    }
}
