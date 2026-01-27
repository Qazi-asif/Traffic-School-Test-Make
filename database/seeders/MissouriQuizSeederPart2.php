<?php

namespace Database\Seeders;

use App\Models\MissouriCourseStructure;
use App\Models\MissouriQuizBank;
use Illuminate\Database\Seeder;

class MissouriQuizSeederPart2 extends Seeder
{
    public function run()
    {
        $chapters = MissouriCourseStructure::orderBy('chapter_number')->get();

        $quizzes = [
            // Chapter 4 Quiz
            [4, 'Class F licenses allow drivers to operate________.', 'Any noncommercial vehicles.', 'Only motorcycles.', 'Any four-axle vehicle.', 'None of the above.', null, 'A', 'easy'],
            [4, 'When you obtain your license it is a big commitment. It is very much like a __________!', 'marriage.', 'conquest.', 'shotgun wedding.', 'trip around the world.', 'honeymoon.', 'A', 'easy'],
            [4, 'You may apply for a full drivers license in the state of Missouri if you are_______.', '16', '15', '21', '18', null, 'D', 'medium'],
            [4, 'Which of the following statements is true regarding intermediate licenses?', 'From 16 to 18 years of age you may apply for intermediate licenses.', 'You may not have more than 1 person under 19 in the car with you for the first 6 months on an intermediate license.', 'You must have 40 hours of instruction, 10 hours behind the wheel to apply.', 'All of the above are true.', null, 'D', 'hard'],
            [4, 'When a police officer signals for you to pull over:', 'you must slow down while driving home.', 'correct any poor driving problem you were exhibiting.', 'you must direct your car to the side of the road and stop at the first safe place.', 'you should immediately call 911 if you have a cell phone.', 'none of the above', 'C', 'easy'],
            [4, 'If the DOR learns that you do not have insurance:', 'you can still drive in a nearby state.', 'you will receive a notice of suspension of your driver license and vehicle plates.', 'you will get a letter requesting that you get it soon.', 'you will not be able to attend driving school.', null, 'B', 'medium'],
            [4, 'When you have an accident, you must report it to the DMV if:', 'Someone in the accident did not have liability coverage.', 'There is damage to any one person\'s property to excess of $500.', 'There is damage to property in excess of $750.', 'Both A and B', null, 'D', 'hard'],
            [4, 'If you are convicted of driving without insurance:', 'you will have to pay a fine of up to $500.', 'you will have to pay a fine of up to $1000.', 'you will have to pay a fine of up to $2000.', 'you will have to pay a fine of up to $5000.', null, 'A', 'medium'],
            [4, 'If you are convicted of driving without insurance, you will also:', 'have your license suspended for 90 days.', 'have your license suspended for 1 year.', 'have your license suspended for 2 years.', 'have your license suspended for 5 years.', null, 'B', 'hard'],
            [4, 'If you are convicted of driving without insurance, you will also have to:', 'pay a reinstatement fee of $20.', 'pay a reinstatement fee of $50.', 'pay a reinstatement fee of $100.', 'pay a reinstatement fee of $200.', null, 'A', 'medium'],

            // Chapter 5 Quiz
            [5, 'The point system is used to:', 'keep track of your driving record.', 'determine if you are a safe driver.', 'determine if you should be allowed to drive.', 'all of the above.', null, 'D', 'easy'],
            [5, 'If you accumulate 8 points in 18 months:', 'you will have your license suspended for 30 days.', 'you will have your license suspended for 60 days.', 'you will have your license suspended for 90 days.', 'you will have your license suspended for 1 year.', null, 'A', 'medium'],
            [5, 'If you accumulate 12 points in 12 months:', 'you will have your license suspended for 30 days.', 'you will have your license suspended for 60 days.', 'you will have your license suspended for 90 days.', 'you will have your license suspended for 1 year.', null, 'D', 'hard'],
            [5, 'If you accumulate 18 points in 24 months:', 'you will have your license suspended for 30 days.', 'you will have your license suspended for 60 days.', 'you will have your license suspended for 90 days.', 'you will have your license suspended for 1 year.', null, 'D', 'hard'],
            [5, 'Points are removed from your record:', 'after 1 year.', 'after 2 years.', 'after 3 years.', 'after 5 years.', null, 'C', 'medium'],
            [5, 'If you are convicted of a moving violation:', 'you will receive 2 points.', 'you will receive 3 points.', 'you will receive 4 points.', 'you will receive 5 points.', null, 'A', 'easy'],
            [5, 'If you are convicted of speeding:', 'you will receive 2 points.', 'you will receive 3 points.', 'you will receive 4 points.', 'you will receive 5 points.', null, 'B', 'medium'],
            [5, 'If you are convicted of careless and imprudent driving:', 'you will receive 2 points.', 'you will receive 3 points.', 'you will receive 4 points.', 'you will receive 5 points.', null, 'C', 'hard'],
            [5, 'If you are convicted of leaving the scene of an accident:', 'you will receive 6 points.', 'you will receive 8 points.', 'you will receive 10 points.', 'you will receive 12 points.', null, 'D', 'hard'],
            [5, 'If you are convicted of DWI:', 'you will receive 6 points.', 'you will receive 8 points.', 'you will receive 10 points.', 'you will receive 12 points.', null, 'B', 'hard'],

            // Chapter 6 Quiz
            [6, 'The best way to avoid a collision is to:', 'drive defensively.', 'drive offensively.', 'drive aggressively.', 'drive passively.', null, 'A', 'easy'],
            [6, 'Defensive driving means:', 'being aware of other drivers.', 'being aware of road conditions.', 'being aware of weather conditions.', 'all of the above.', null, 'D', 'easy'],
            [6, 'When driving in fog:', 'use your high beams.', 'use your low beams.', 'use your parking lights.', 'use your hazard lights.', null, 'B', 'medium'],
            [6, 'When driving in rain:', 'increase your following distance.', 'decrease your following distance.', 'maintain your following distance.', 'none of the above.', null, 'A', 'easy'],
            [6, 'When driving in snow:', 'increase your following distance.', 'decrease your following distance.', 'maintain your following distance.', 'none of the above.', null, 'A', 'easy'],
            [6, 'When driving on ice:', 'increase your following distance.', 'decrease your following distance.', 'maintain your following distance.', 'none of the above.', null, 'A', 'easy'],
            [6, 'If your car starts to skid:', 'steer in the direction you want to go.', 'steer in the opposite direction you want to go.', 'slam on the brakes.', 'pump the brakes.', null, 'A', 'medium'],
            [6, 'If your brakes fail:', 'pump the brakes.', 'use the emergency brake.', 'shift to a lower gear.', 'all of the above.', null, 'D', 'hard'],
            [6, 'If your accelerator sticks:', 'shift to neutral.', 'turn off the ignition.', 'apply the brakes.', 'all of the above.', null, 'A', 'medium'],
            [6, 'If your hood flies up while driving:', 'look through the crack under the hood.', 'stick your head out the window.', 'pull over immediately.', 'both A and C.', null, 'D', 'hard'],
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
