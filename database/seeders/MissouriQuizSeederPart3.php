<?php

namespace Database\Seeders;

use App\Models\MissouriCourseStructure;
use App\Models\MissouriQuizBank;
use Illuminate\Database\Seeder;

class MissouriQuizSeederPart3 extends Seeder
{
    public function run()
    {
        $chapters = MissouriCourseStructure::orderBy('chapter_number')->get();

        $quizzes = [
            // Chapter 7 Quiz
            [7, 'Alcohol is a:', 'stimulant.', 'depressant.', 'hallucinogen.', 'narcotic.', null, 'B', 'easy'],
            [7, 'The legal limit for blood alcohol content in Missouri is:', '0.05%', '0.08%', '0.10%', '0.12%', null, 'B', 'easy'],
            [7, 'If you are under 21, the legal limit for blood alcohol content is:', '0.00%', '0.02%', '0.05%', '0.08%', null, 'B', 'medium'],
            [7, 'If you are convicted of DWI, you will:', 'have your license suspended.', 'have to pay a fine.', 'have to attend an alcohol education program.', 'all of the above.', null, 'D', 'easy'],
            [7, 'Implied consent means:', 'you agree to take a chemical test if asked.', 'you agree to take a field sobriety test if asked.', 'you agree to take a breathalyzer test if asked.', 'all of the above.', null, 'A', 'medium'],
            [7, 'If you refuse to take a chemical test:', 'your license will be suspended for 1 year.', 'your license will be suspended for 2 years.', 'your license will be suspended for 3 years.', 'your license will be suspended for 5 years.', null, 'A', 'hard'],
            [7, 'The only way to sober up is:', 'to drink coffee.', 'to take a cold shower.', 'to wait for time to pass.', 'to exercise.', null, 'C', 'easy'],
            [7, 'One drink equals:', '12 oz. of beer.', '5 oz. of wine.', '1.5 oz. of liquor.', 'all of the above.', null, 'D', 'medium'],
            [7, 'Alcohol affects:', 'your judgment.', 'your coordination.', 'your reaction time.', 'all of the above.', null, 'D', 'easy'],
            [7, 'If you are convicted of DWI and someone is injured:', 'you will be charged with a felony.', 'you will be charged with a misdemeanor.', 'you will be charged with a traffic violation.', 'none of the above.', null, 'A', 'hard'],

            // Chapter 8 Quiz
            [8, 'Before you start your car, you should:', 'adjust your mirrors.', 'adjust your seat.', 'fasten your seatbelt.', 'all of the above.', null, 'D', 'easy'],
            [8, 'When backing up, you should:', 'look over your right shoulder.', 'look over your left shoulder.', 'look in your rearview mirror.', 'all of the above.', null, 'D', 'easy'],
            [8, 'When parallel parking, you should:', 'signal your intention to park.', 'pull up alongside the car in front of the space.', 'back into the space.', 'all of the above.', null, 'D', 'easy'],
            [8, 'When parking on a hill with a curb:', 'turn your wheels toward the curb if facing downhill.', 'turn your wheels away from the curb if facing uphill.', 'turn your wheels toward the curb if facing uphill.', 'both A and B.', null, 'D', 'medium'],
            [8, 'When parking on a hill without a curb:', 'turn your wheels toward the side of the road.', 'turn your wheels away from the side of the road.', 'keep your wheels straight.', 'none of the above.', null, 'A', 'medium'],
            [8, 'When entering a highway:', 'use the acceleration lane to speed up.', 'merge when it is safe.', 'signal your intention to merge.', 'all of the above.', null, 'D', 'easy'],
            [8, 'When exiting a highway:', 'signal your intention to exit.', 'move into the exit lane.', 'slow down in the deceleration lane.', 'all of the above.', null, 'D', 'easy'],
            [8, 'When changing lanes:', 'signal your intention to change lanes.', 'check your mirrors.', 'check your blind spot.', 'all of the above.', null, 'D', 'easy'],
            [8, 'When passing another vehicle:', 'signal your intention to pass.', 'check your mirrors.', 'check your blind spot.', 'all of the above.', null, 'D', 'easy'],
            [8, 'When being passed by another vehicle:', 'maintain your speed.', 'move to the right.', 'do not speed up.', 'all of the above.', null, 'D', 'medium'],

            // Chapter 9 Quiz
            [9, 'Road rage is:', 'aggressive driving.', 'angry driving.', 'dangerous driving.', 'all of the above.', null, 'D', 'easy'],
            [9, 'To avoid road rage, you should:', 'not tailgate.', 'not cut off other drivers.', 'not make obscene gestures.', 'all of the above.', null, 'D', 'easy'],
            [9, 'If someone is tailgating you:', 'slow down and let them pass.', 'speed up.', 'slam on your brakes.', 'none of the above.', null, 'A', 'easy'],
            [9, 'If someone cuts you off:', 'honk your horn.', 'flash your lights.', 'let it go.', 'chase them down.', null, 'C', 'medium'],
            [9, 'Distracted driving includes:', 'talking on a cell phone.', 'texting.', 'eating.', 'all of the above.', null, 'D', 'easy'],
            [9, 'If you are tired while driving:', 'pull over and rest.', 'drink coffee.', 'open the window.', 'turn up the radio.', null, 'A', 'easy'],
            [9, 'If you are taking medication:', 'read the label.', 'ask your doctor if it is safe to drive.', 'do not drive if it causes drowsiness.', 'all of the above.', null, 'D', 'easy'],
            [9, 'If you are emotional while driving:', 'pull over and calm down.', 'keep driving.', 'drive faster.', 'none of the above.', null, 'A', 'medium'],
            [9, 'Carbon monoxide poisoning can occur:', 'if your exhaust system is leaking.', 'if you run your car in a closed garage.', 'if you drive with your windows closed.', 'both A and B.', null, 'D', 'hard'],
            [9, 'To prevent carbon monoxide poisoning:', 'have your exhaust system checked regularly.', 'do not run your car in a closed garage.', 'open your windows if you smell exhaust.', 'all of the above.', null, 'D', 'medium'],

            // Chapter 10 Quiz
            [10, 'If you are involved in an accident, you should:', 'stop immediately.', 'call 911 if anyone is injured.', 'exchange information with the other driver.', 'all of the above.', null, 'D', 'easy'],
            [10, 'If you hit a parked car:', 'leave a note with your information.', 'call the police.', 'wait for the owner to return.', 'both A and B.', null, 'D', 'medium'],
            [10, 'If you are involved in an accident with an uninsured driver:', 'you will have to pay for your own damages.', 'your insurance will cover your damages.', 'the other driver will have to pay for your damages.', 'none of the above.', null, 'B', 'hard'],
            [10, 'If you are involved in an accident and you are at fault:', 'your insurance rates will go up.', 'you will have to pay a deductible.', 'you will have to pay for the other driver\'s damages.', 'all of the above.', null, 'D', 'medium'],
            [10, 'If you are involved in an accident and you are not at fault:', 'the other driver\'s insurance will pay for your damages.', 'you will not have to pay a deductible.', 'your insurance rates will not go up.', 'all of the above.', null, 'D', 'medium'],
            [10, 'If you are involved in an accident and the other driver leaves:', 'call the police.', 'try to get the license plate number.', 'get witness information.', 'all of the above.', null, 'D', 'easy'],
            [10, 'If you are involved in an accident and you are injured:', 'call 911.', 'do not move unless you are in danger.', 'wait for help to arrive.', 'all of the above.', null, 'D', 'easy'],
            [10, 'If you are involved in an accident and someone else is injured:', 'call 911.', 'do not move them unless they are in danger.', 'wait for help to arrive.', 'all of the above.', null, 'D', 'easy'],
            [10, 'If you are involved in an accident and there is a fire:', 'get away from the vehicle.', 'call 911.', 'do not try to put out the fire.', 'all of the above.', null, 'D', 'easy'],
            [10, 'If you are involved in an accident and there is a fuel spill:', 'get away from the vehicle.', 'call 911.', 'do not smoke.', 'all of the above.', null, 'D', 'easy'],
        ];

        foreach ($quizzes as $quiz) {
            $chapterNum = $quiz[0];
            $chapter = $chapters->where('chapter_number', $chapterNum)->first();

            if ($chapter) {
                MissouriQuizBank::create([
                    'chapter_id' => $chapter->id,
                    'question_text' => $quiz[1],
                    'option_a' => $quiz[2],
                    'option_b' => $quiz[3],
                    'option_c' => $quiz[4],
                    'option_d' => $quiz[5],
                    'option_e' => $quiz[6],
                    'correct_answer' => $quiz[7],
                    'difficulty_level' => $quiz[8],
                ]);
            }
        }
    }
}
