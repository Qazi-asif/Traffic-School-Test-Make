<?php

namespace Database\Seeders;

use App\Models\LegalDocument;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PrivacyPolicySeeder extends Seeder
{
    public function run()
    {
        // Create admin user first if it doesn't exist
        $adminUser = \App\Models\User::firstOrCreate(
            ['email' => 'admin@dummiestrafficschool.com'],
            [
                'first_name' => 'Admin',
                'last_name' => 'User',
                'password' => \Hash::make('password'),
                'role_id' => 1,
                'status' => 'active',
            ]
        );

        LegalDocument::create([
            'document_type' => 'privacy_policy',
            'title' => 'Dummies Traffic School Privacy Statement',
            'content' => 'Dummies Traffic School holds the privacy of our customers in highest regard. Please look over our privacy policy below so that you can feel secure that none of your information will be used in a way that you do not approve of. The information that we gather from you is merely for our identity verification and internal database policies. We are not associated with any other commercial entity that would be interested in such information.

Governing Law: You agree that by using our website or our services, your use is governed by the laws of the State of California and any applicable federal laws.',
            'version' => '1.0',
            'effective_date' => Carbon::now(),
            'is_active' => true,
            'requires_consent' => true,
            'created_by' => $adminUser->id,
        ]);

        LegalDocument::create([
            'document_type' => 'terms_of_service',
            'title' => 'Terms and Conditions',
            'content' => 'By accessing and using DummiesTrafficSchool.com, you agree to be bound by these Terms and Conditions.

1. Course Enrollment: Upon enrollment, you agree to complete the course according to state requirements and regulations.

2. Payment: All course fees must be paid in full before accessing course materials.

3. Course Completion: You must complete all required chapters, quizzes, and final exams to receive your certificate.

4. Time Requirements: Courses must be completed within the time limits specified by your state regulations.

5. Academic Integrity: You agree to complete all coursework yourself without assistance from others.

6. Changes to Terms: Dummies Traffic School reserves the right to change these terms and conditions at any time without notice. Your continued use of our website constitutes acceptance of any changes.',
            'version' => '1.0',
            'effective_date' => Carbon::now(),
            'is_active' => true,
            'requires_consent' => true,
            'created_by' => $adminUser->id,
        ]);

        LegalDocument::create([
            'document_type' => 'refund_policy',
            'title' => 'Refund and Cancellation Policy',
            'content' => 'Refund Policy:

1. Full Refund: Available if requested before accessing any course materials.

2. Partial Refund: May be available if less than 25% of the course has been completed, subject to state regulations.

3. No Refund: Once you have completed more than 25% of the course or received your certificate, no refunds will be issued.

4. Processing Time: Refunds are processed within 7-10 business days of approval.

5. State-Specific Rules: Some states have specific refund requirements that supersede this policy.

Cancellation Policy:

You may cancel your enrollment at any time by contacting our support team at Support@DummiesTrafficSchool.com or calling (877) 382-3700.

Contact Us:
For refund requests or questions, please contact:
Email: Support@DummiesTrafficSchool.com
Phone: (877) 382-3700
Hours: Monday-Friday 8am-4pm PST',
            'version' => '1.0',
            'effective_date' => Carbon::now(),
            'is_active' => true,
            'requires_consent' => false,
            'created_by' => $adminUser->id,
        ]);

        LegalDocument::create([
            'document_type' => 'copyright_notice',
            'title' => 'Copyright and Trademark Information',
            'content' => 'The trademarks, trade names, lesson materials, exam questions, and site design are the sole property of Dummies Traffic School. No one is authorized to use any of these items in any matter without the prior express consent of the owner of Dummies Traffic School. Individuals are further prohibited from transmitting, broadcasting, copying, adapting, reverse-engineering, or displaying its contents without the prior express consent of Dummies Traffic School. The materials displayed are protected by U.S. and International copyright laws and treaties.',
            'version' => '1.0',
            'effective_date' => Carbon::now(),
            'is_active' => true,
            'requires_consent' => false,
            'created_by' => $adminUser->id,
        ]);

        LegalDocument::create([
            'document_type' => 'disclaimer',
            'title' => 'No Warranties and Limitation of Liability',
            'content' => 'No warranties: From time to time Dummies Traffic School.com may include information from third parties, and/or links to other websites. Dummies Traffic School does not make any warranties, express or implied, regarding any third party information or any links to other websites, and Dummies Traffic School assumes no responsibility for the accuracy, completeness, reliability or suitability of the information provided by third parties or information, software (if any), offers or activity found on other websites which may be linked to our website.

Limitation of liability: Dummies Traffic School disclaims liability for any and all claims, losses, costs, expenses (including attorneys\' fees), and damages of whatever kind or nature including without limitation general, special, incidental, consequential, punitive, exemplary or treble damages ("Damages") based on any theory of liability, in connection with any use of Dummies Traffic School website and the information contained therein, even if Dummies Traffic School has been advised of the possibility of such Damages. 

Changes: Dummies Traffic School reserves the right to change these terms and conditions, or the content of any portion of our website at any time without notice. Your continued access of Dummies Traffic School website will be subject to the terms and conditions in effect at the time you access our website.',
            'version' => '1.0',
            'effective_date' => Carbon::now(),
            'is_active' => true,
            'requires_consent' => false,
            'created_by' => $adminUser->id,
        ]);
    }
}
