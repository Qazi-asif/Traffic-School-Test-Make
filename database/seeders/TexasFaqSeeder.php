<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TexasFaqSeeder extends Seeder
{
    public function run()
    {
        $faqs = [
            // Course Eligibility
            [
                'category' => 'Course Eligibility',
                'question' => 'Who can take the Texas Defensive Driving Course?',
                'answer' => 'Our Texas Defensive Driving Course is designed for drivers who need to dismiss a ticket, meet court requirements, or refresh their driving skills. You can take this course if you\'ve received a moving violation and want to get your ticket dismissed, been ordered by a Texas court to complete a defensive driving course, want to keep your driving record clean and avoid higher insurance rates, or voluntarily want to improve your safe-driving skills or qualify for an insurance discount.',
                'order' => 1,
                'state_code' => 'TX',
            ],
            [
                'category' => 'Course Eligibility',
                'question' => 'What are the requirements to qualify for ticket dismissal?',
                'answer' => 'To qualify, you must: Have a valid, non-commercial Texas driver\'s license; Have been ticketed for less than 25 mph over the speed limit; Not have completed a defensive driving course in the past 12 months; Not have received the violation in a construction or work zone.',
                'order' => 2,
                'state_code' => 'TX',
            ],

            // Course Details
            [
                'category' => 'Course Details',
                'question' => 'How long is the Texas Defensive Driving Course?',
                'answer' => 'The course is 6 hours long and is approved by the Texas Department of Licensing and Regulation (TDLR). It meets all requirements for ticket dismissal and insurance discounts.',
                'order' => 1,
                'state_code' => 'TX',
            ],
            [
                'category' => 'Course Details',
                'question' => 'What is included in the course?',
                'answer' => 'The course includes easy-to-follow lessons covering Texas traffic laws, defensive driving strategies, safe following distances, and distracted driving awareness. There are short quizzes after each section to help reinforce what you\'ve learned, and a final exam with 25 multiple-choice questions. You must score 70% or higher to pass, with unlimited retakes available.',
                'order' => 2,
                'state_code' => 'TX',
            ],
            [
                'category' => 'Course Details',
                'question' => 'Can I take the course online?',
                'answer' => 'Yes! Our course is 100% online and available 24/7. You can access it on any computer, tablet or smartphone with internet. You can start, stop and resume as your schedule allows, and your progress is saved automatically.',
                'order' => 3,
                'state_code' => 'TX',
            ],

            // Ticket Dismissal Process
            [
                'category' => 'Ticket Dismissal',
                'question' => 'How do I use this course for ticket dismissal?',
                'answer' => 'Step 1: Get court approval by contacting your court to request permission. Step 2: Submit required documents including proof of valid Texas driver\'s license, proof of auto insurance, your signed citation, any court-specific forms, and the court\'s administrative fee. Step 3: Enroll in and complete the TDLR-approved course. Step 4: Submit your completion documents including your certificate and official Texas driving record to your court.',
                'order' => 1,
                'state_code' => 'TX',
            ],
            [
                'category' => 'Ticket Dismissal',
                'question' => 'Do I need court approval before taking the course?',
                'answer' => 'Yes, you must contact your court to request permission to take a defensive driving course before you begin. You can do this by phone, in writing (on the back of your citation), or in person before your appearance date.',
                'order' => 2,
                'state_code' => 'TX',
            ],
            [
                'category' => 'Ticket Dismissal',
                'question' => 'What documents do I need to submit to the court?',
                'answer' => 'You need to submit: Proof of a valid Texas driver\'s license, proof of auto insurance, your signed citation (pleading guilty or no contest), any court-specific affidavits or forms, and the court\'s administrative fee (varies by county).',
                'order' => 3,
                'state_code' => 'TX',
            ],

            // Certificates
            [
                'category' => 'Certificates',
                'question' => 'How will I receive my completion certificate?',
                'answer' => 'When you complete your course, you\'ll receive your Certificate of Completion electronically (PDF) or by mail, depending on your chosen delivery method.',
                'order' => 1,
                'state_code' => 'TX',
            ],
            [
                'category' => 'Certificates',
                'question' => 'What do I do with my certificate after I receive it?',
                'answer' => 'For ticket dismissal: Send your certificate and driving record to your court by the required deadline. For insurance discounts: Submit your certificate directly to your insurance provider.',
                'order' => 2,
                'state_code' => 'TX',
            ],
            [
                'category' => 'Certificates',
                'question' => 'What is the provider license number?',
                'answer' => 'Our Texas provider license number is CP007. This number appears on all certificates and verifies that we are approved by the Texas Department of Licensing and Regulation (TDLR).',
                'order' => 3,
                'state_code' => 'TX',
            ],

            // Technical Questions
            [
                'category' => 'Technical',
                'question' => 'What if I fail the final exam?',
                'answer' => 'Don\'t worry! You get unlimited retakes with no pressure. The final exam has 25 multiple-choice questions and you need to score 70% or higher to pass. Most students pass on their first attempt.',
                'order' => 1,
                'state_code' => 'TX',
            ],
            [
                'category' => 'Technical',
                'question' => 'Can I take the course on my mobile device?',
                'answer' => 'Yes! The course is fully compatible with computers, tablets, and smartphones. As long as you have an internet connection, you can access the course from any device.',
                'order' => 2,
                'state_code' => 'TX',
            ],
            [
                'category' => 'Technical',
                'question' => 'How long do I have to complete the course?',
                'answer' => 'The course is self-paced, but your court may assign a completion deadline. Be sure to finish on time to ensure your ticket dismissal is processed properly.',
                'order' => 3,
                'state_code' => 'TX',
            ],

            // Insurance Discounts
            [
                'category' => 'Insurance',
                'question' => 'Can I get an insurance discount by taking this course?',
                'answer' => 'Yes! Many insurance companies offer discounts for completing a defensive driving course. Contact your insurance provider to confirm they accept our TDLR-approved certificate and to learn about available discounts.',
                'order' => 1,
                'state_code' => 'TX',
            ],
            [
                'category' => 'Insurance',
                'question' => 'How do I submit my certificate for insurance discounts?',
                'answer' => 'Submit your certificate directly to your insurance provider. Contact them to confirm the process and any specific requirements they may have.',
                'order' => 2,
                'state_code' => 'TX',
            ],

            // Legal and Compliance
            [
                'category' => 'Legal',
                'question' => 'Is this course approved by Texas authorities?',
                'answer' => 'Yes! Our course is approved by the Texas Department of Licensing and Regulation (TDLR) and meets all requirements for ticket dismissal and insurance discounts. Our provider license number is CP007.',
                'order' => 1,
                'state_code' => 'TX',
            ],
            [
                'category' => 'Legal',
                'question' => 'How long are student records kept?',
                'answer' => 'We are required to keep student information for 3 years as per Texas regulations. After this period, records can be automatically deleted.',
                'order' => 2,
                'state_code' => 'TX',
            ],
            [
                'category' => 'Legal',
                'question' => 'Does the course dismissal remove the violation from all records?',
                'answer' => 'The course dismisses the citation but does not remove the violation from all records unless processed by the court. We recommend checking your Texas driving record afterward to confirm it\'s been updated.',
                'order' => 3,
                'state_code' => 'TX',
            ],

            // General Information
            [
                'category' => 'General',
                'question' => 'What makes this course engaging and easy?',
                'answer' => 'Our course features short chapters, animations, and multiple-choice quizzes that make learning easy and enjoyable. The content is broken down into manageable sections with interactive elements to keep you engaged.',
                'order' => 1,
                'state_code' => 'TX',
            ],
            [
                'category' => 'General',
                'question' => 'What should I do if my court doesn\'t accept online courses?',
                'answer' => 'It\'s your responsibility to confirm that your court or agency approves online defensive driving courses and accepts your provider\'s certificate before enrolling. Contact your court directly to verify acceptance.',
                'order' => 2,
                'state_code' => 'TX',
            ],
            [
                'category' => 'General',
                'question' => 'Can I get help if I have questions during the course?',
                'answer' => 'Yes! If you need assistance during the course, you can contact our customer support team. We\'re here to help ensure you successfully complete your defensive driving requirements.',
                'order' => 3,
                'state_code' => 'TX',
            ],
        ];

        foreach ($faqs as $faq) {
            DB::table('faqs')->insert([
                'category' => $faq['category'],
                'question' => $faq['question'],
                'answer' => $faq['answer'],
                'order' => $faq['order'],
                'state' => $faq['state_code'],
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        $this->command->info('Texas FAQ entries seeded successfully!');
    }
}
