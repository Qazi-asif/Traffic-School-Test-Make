<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class MissouriFaqSeeder extends Seeder
{
    public function run()
    {
        $faqs = [
            [
                'question' => 'Who Can Take the Missouri Online Driver Improvement Program?',
                'answer' => 'Our Missouri Driver Improvement Course is designed for drivers who need to meet court requirements, reduce points on their driving record, or simply improve their driving skills. You can take this course if you\'ve: • Received a moving violation and want to avoid points on your driving record. • Been ordered by a Missouri court to complete a driver-improvement program. • Voluntarily want to take the course to refresh your safe-driving skills or qualify for an insurance discount.',
                'category' => 'Missouri',
                'order' => 1,
            ],
            [
                'question' => 'What to Expect from Our Missouri Online Driver Improvement Course?',
                'answer' => 'The course is 100% online, state-approved by the Missouri Safety Center and meets all requirements for point reduction on your Missouri driver\'s license. Learn Anywhere: Access on any computer, tablet or smartphone with internet. Learn Anytime: Available 24/7 – start, stop and resume as your schedule allows. Go at Your Own Pace: Dive in one sitting or spread it out over time; your progress is saved automatically.',
                'category' => 'Missouri',
                'order' => 2,
            ],
            [
                'question' => 'What\'s Included in the Course?',
                'answer' => '• 11 easy-to-understand chapters covering topics like Missouri traffic law, defensive driving, highway & night driving, vehicle maintenance, DUI/substance abuse laws. • At the end of each chapter: a 10-question multiple-choice quiz. • Final exam: 50 multiple-choice questions. Score 80% or higher to pass. • Unlimited retakes: You can retake quizzes and the final exam as many times as needed.',
                'category' => 'Missouri',
                'order' => 3,
            ],
            [
                'question' => 'What is MO Form 4444?',
                'answer' => 'When you complete the course, you\'ll receive MO Form 4444 (the "Record of Participation and Completion of Driver Improvement Program" form). This form is crucial because it is the official document that shows you completed the required program.',
                'category' => 'Missouri',
                'order' => 4,
            ],
            [
                'question' => 'How do I submit Form 4444 for point reduction?',
                'answer' => 'If you are taking the course to reduce points after a moving violation (and the court granted you permission): you must print or download your Form 4444, then have your court or judge sign it (if required), and then send it to the Missouri Department of Revenue (DOR) within 15 days of completing the course.',
                'category' => 'Missouri',
                'order' => 5,
            ],
            [
                'question' => 'What is the 15-day deadline?',
                'answer' => 'The form must arrive at DOR within 15 days of course completion when required for point removal. This is a strict deadline enforced by Missouri law.',
                'category' => 'Missouri',
                'order' => 6,
            ],
            [
                'question' => 'How often can I take the course for point reduction?',
                'answer' => 'You may only take a driver improvement course for point reduction once in a 36-month period.',
                'category' => 'Missouri',
                'order' => 7,
            ],
            [
                'question' => 'Do I need court permission before enrolling?',
                'answer' => 'You must usually have the court\'s permission before enrolling in the course if you\'re doing it to avoid points.',
                'category' => 'Missouri',
                'order' => 8,
            ],
            [
                'question' => 'How do I receive my certificate?',
                'answer' => 'Once you\'ve completed the online course and passed all required quizzes and the final exam, you\'ll receive your Form 4444 electronically (PDF) or by mail, depending on the provider.',
                'category' => 'Missouri',
                'order' => 9,
            ],
            [
                'question' => 'What if I\'m taking the course for insurance discount?',
                'answer' => 'If you are taking it for voluntary reasons (e.g., insurance discount) or the Fine Collection Center ("FCC") authorized it: you must submit the Form 4444 according to the instructions — often to the insurance company or FCC, or to the DOR if required.',
                'category' => 'Missouri',
                'order' => 10,
            ],
            [
                'question' => 'Does the course remove the violation from my record?',
                'answer' => 'Understand that the course removes points, not the violation itself.',
                'category' => 'Missouri',
                'order' => 11,
            ],
            [
                'question' => 'Is the course mobile-friendly?',
                'answer' => 'Yes! The course is 100% online and accessible on any computer, tablet, or smartphone with internet connection.',
                'category' => 'Missouri',
                'order' => 12,
            ],
            [
                'question' => 'Can I save my progress?',
                'answer' => 'Yes, your progress is saved automatically. You can start, stop, and resume as your schedule allows.',
                'category' => 'Missouri',
                'order' => 13,
            ],
            [
                'question' => 'What is the passing score for the final exam?',
                'answer' => 'You must score 80% or higher on the 50-question final exam to pass.',
                'category' => 'Missouri',
                'order' => 14,
            ],
            [
                'question' => 'Can I retake the quizzes and final exam?',
                'answer' => 'Yes, you can retake quizzes and the final exam as many times as needed.',
                'category' => 'Missouri',
                'order' => 15,
            ],
        ];

        foreach ($faqs as $faq) {
            Faq::create($faq);
        }
    }
}
