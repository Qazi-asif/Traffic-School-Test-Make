<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FloridaDefensiveDrivingCompleteSeeder extends Seeder
{
    public function run()
    {
        // Get the Florida DDC course
        $course = DB::table('florida_courses')->where('title', 'LIKE', '%Defensive Driving%')->first();

        if (! $course) {
            $this->command->error('Florida Defensive Driving Course not found. Run FloridaDefensiveDrivingSeeder first.');

            return;
        }

        $this->addRemainingChapters($course->id);
    }

    private function addRemainingChapters($courseId)
    {
        $chapters = [
            [
                'title' => 'Chapter 9: Reading the Road - Signs, Signals and Markings',
                'description' => 'Understanding traffic signs, signals, road markings and their meanings',
                'duration' => 40,
                'content' => 'Traffic signs, signals, and markings are essential communication tools that guide, inform, and instruct drivers. This chapter covers the comprehensive system of road communication.

**Types of Traffic Signals:**
- Traffic Lights (Red, Yellow, Green)
- Signs (Stop, Yield, Warning, Regulatory)
- Arrows (Directional guidance)
- Flashing Signals (Special conditions)
- Lane Signals (Lane-specific instructions)

**Traffic Light Meanings:**
- **RED**: Complete stop required
- **RED ARROW**: No turn in arrow direction
- **FLASHING RED**: Treat as stop sign
- **YELLOW**: Prepare to stop, red light coming
- **FLASHING YELLOW**: Proceed with caution
- **GREEN**: Proceed when safe, yield to pedestrians
- **GREEN ARROW**: Protected turn, cross traffic stopped

**Sign Colors and Meanings:**
- **RED/WHITE**: Stop, yield, or prohibition
- **GREEN**: Directional guidance, place names
- **YELLOW**: Warning of hazards ahead
- **ORANGE**: Construction or maintenance zones
- **BLUE**: Motorist services (gas, food, lodging)
- **BROWN**: Recreational or cultural sites

**Sign Shapes:**
- **Octagon**: Stop
- **Triangle**: Yield
- **Circle**: Railroad crossing
- **Diamond**: Warning
- **Rectangle**: Regulatory or informational
- **Pentagon**: School zone',
                'questions' => [
                    [
                        'question' => 'What does a flashing red traffic signal mean?',
                        'options' => [
                            'Proceed with caution',
                            'Treat it as a stop sign',
                            'Yield to oncoming traffic',
                            'Speed up to clear the intersection',
                        ],
                        'correct_answer' => 'Treat it as a stop sign',
                        'explanation' => 'A flashing red signal operates exactly like a stop sign - you must come to a complete stop and proceed when safe.',
                    ],
                    [
                        'question' => 'What color are construction zone signs?',
                        'options' => [
                            'Yellow',
                            'Orange',
                            'Red',
                            'Blue',
                        ],
                        'correct_answer' => 'Orange',
                        'explanation' => 'Orange signs indicate construction or maintenance zones and provide warnings and guidance for work areas.',
                    ],
                ],
            ],
            [
                'title' => 'Chapter 10: Licensing and Legal Responsibilities',
                'description' => 'Understanding driving privileges, insurance requirements, and legal obligations',
                'duration' => 35,
                'content' => 'Having a driver\'s license is a privilege, not a right. This chapter covers the legal responsibilities and requirements for maintaining your driving privileges.

**Driving as a Privilege:**
- Licenses can be suspended or revoked for violations
- Point system tracks driving infractions
- Insurance is mandatory in most states
- Regular renewal and testing may be required

**Insurance Requirements:**
- Liability coverage protects others from your actions
- Minimum coverage amounts vary by state
- Proof of insurance must be carried while driving
- Driving without insurance can result in license suspension

**Point System:**
- Traffic violations add points to your record
- Accumulating too many points can result in suspension
- Defensive driving florida_courses may reduce points
- Points typically remain on record for several years

**Legal Responsibilities:**
- Obey all traffic laws and regulations
- Report accidents as required by law
- Maintain vehicle registration and inspection
- Update address changes with DMV
- Appear in court when required

**Consequences of Violations:**
- Fines and court costs
- License suspension or revocation
- Increased insurance rates
- Possible jail time for serious offenses
- Vehicle impoundment in some cases',
                'questions' => [
                    [
                        'question' => 'Driving is considered:',
                        'options' => [
                            'A constitutional right',
                            'A privilege that can be revoked',
                            'Guaranteed for all adults',
                            'Optional for experienced drivers',
                        ],
                        'correct_answer' => 'A privilege that can be revoked',
                        'explanation' => 'Driving is a privilege granted by the state that can be suspended or revoked for violations of traffic laws.',
                    ],
                    [
                        'question' => 'What happens if you drive without insurance?',
                        'options' => [
                            'Nothing if you don\'t get caught',
                            'Only a small fine',
                            'License suspension and registration suspension',
                            'Just a warning for first offense',
                        ],
                        'correct_answer' => 'License suspension and registration suspension',
                        'explanation' => 'Driving without insurance can result in license and registration suspension, plus significant fines.',
                    ],
                ],
            ],
            [
                'title' => 'Chapter 11: Highway and Freeway Driving',
                'description' => 'Safe techniques for highway driving, merging, passing, and freeway navigation',
                'duration' => 45,
                'content' => 'Highway and freeway driving requires special skills and awareness due to higher speeds and complex traffic patterns.

**Entering the Freeway:**
- Use acceleration lane to match traffic speed
- Check for adequate gaps in traffic
- Signal early and merge smoothly
- Never stop at the end of an on-ramp unless absolutely necessary

**Lane Selection:**
- Right lane: Slower traffic and vehicles preparing to exit
- Center lanes: Through traffic at normal speeds
- Left lane: Passing and faster traffic only
- Carpool lanes: Marked with diamonds, special restrictions

**Space Cushion Management:**
- Maintain 3-second following distance minimum
- Increase to 4+ seconds in poor conditions
- Leave space on all sides when possible
- Avoid driving in clusters of vehicles

**Passing Safely:**
- Use left lane only
- Signal before and after passing
- Check blind spots thoroughly
- Return to right lane when safe
- Never pass on the right except in specific situations

**Highway Hypnosis:**
- Trance-like state from monotonous driving
- Stay alert by varying your scanning pattern
- Take breaks every 2 hours on long trips
- Keep eyes moving, don\'t stare ahead

**Exiting the Freeway:**
- Plan ahead, know your exit
- Move to exit lane early
- Signal your intention
- Reduce speed gradually in deceleration lane
- Never cross medians or make illegal exits',
                'questions' => [
                    [
                        'question' => 'When entering a freeway, you should:',
                        'options' => [
                            'Stop at the end of the on-ramp to look for traffic',
                            'Enter at any speed and let other drivers adjust',
                            'Use the acceleration lane to match traffic speed',
                            'Always yield to all freeway traffic',
                        ],
                        'correct_answer' => 'Use the acceleration lane to match traffic speed',
                        'explanation' => 'The acceleration lane is designed to help you reach freeway speeds before merging with traffic.',
                    ],
                    [
                        'question' => 'What is "highway hypnosis"?',
                        'options' => [
                            'Being mesmerized by oncoming headlights',
                            'A trance-like condition from monotonous driving',
                            'Falling asleep while driving',
                            'Being distracted by roadside attractions',
                        ],
                        'correct_answer' => 'A trance-like condition from monotonous driving',
                        'explanation' => 'Highway hypnosis is a dangerous trance-like state caused by continuous, monotonous driving conditions.',
                    ],
                ],
            ],
            [
                'title' => 'Chapter 12: Sharing the Road with Large Vehicles',
                'description' => 'Safe driving around trucks, buses, motorcycles, and slow-moving vehicles',
                'duration' => 35,
                'content' => 'Large vehicles and motorcycles have different capabilities and limitations that affect how you should interact with them on the road.

**Truck Characteristics:**
- Require twice the stopping distance of cars
- Have large blind spots ("No Zones")
- Make wide right turns
- Take longer to accelerate
- May block your view of traffic ahead

**The "No Zone" - Truck Blind Spots:**
- Directly in front (20-25 feet)
- Directly behind (200+ feet)
- Left side (one lane width)
- Right side (two lane widths)

**Safe Practices Around Trucks:**
- If you can\'t see the driver in their mirror, they can\'t see you
- Pass on the left side only
- Don\'t linger alongside trucks
- Allow extra space when following
- Be patient with wide turns

**Slow-Moving Vehicles (SMVs):**
- Farm equipment, construction vehicles
- Travel 25 mph or less
- Display orange triangle warning sign
- Pass only in designated passing zones
- Allow them to pull over when safe

**Motorcycle Safety:**
- Motorcycles are harder to see
- Give them full lane width
- Increase following distance
- Check blind spots carefully
- Be extra cautious in intersections

**School Buses:**
- Stop when red lights are flashing
- Never pass a stopped school bus loading/unloading children
- Remain stopped until lights stop flashing and bus moves
- Watch for children crossing the street',
                'questions' => [
                    [
                        'question' => 'Large trucks need approximately how much more stopping distance than cars?',
                        'options' => [
                            'Same distance',
                            'Twice as much',
                            'Three times as much',
                            'Half as much',
                        ],
                        'correct_answer' => 'Twice as much',
                        'explanation' => 'Due to their weight and size, large trucks require approximately twice the stopping distance of passenger cars.',
                    ],
                    [
                        'question' => 'If you cannot see a truck driver in their side mirror:',
                        'options' => [
                            'You are in a safe position',
                            'The truck driver cannot see you',
                            'You should speed up to get alongside',
                            'You should honk your horn',
                        ],
                        'correct_answer' => 'The truck driver cannot see you',
                        'explanation' => 'If you can\'t see the truck driver in their mirror, you are in their blind spot and they cannot see you.',
                    ],
                ],
            ],
        ];

        foreach ($chapters as $index => $chapterData) {
            // Start chapter numbering from 9 (since previous seeders have chapters 1-8)
            $chapterOrder = $index + 9;

            $chapterId = DB::table('chapters')->insertGetId([
                'course_id' => $courseId,
                'title' => $chapterData['title'],
                'content' => $chapterData['content'],

                'order_index' => $chapterOrder,
                'duration' => $chapterData['duration'],

                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            // Create questions for this chapter
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

        $this->command->info('Complete Florida Defensive Driving Course chapters created successfully!');
    }
}
