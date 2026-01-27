<?php

namespace App\Services;

use App\Models\MissouriForm4444;
use App\Models\UserCourseEnrollment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class MissouriForm4444PdfService
{
    public function generateForm4444Pdf(MissouriForm4444 $form)
    {
        $enrollment = $form->enrollment;
        $user = $form->user;
        $course = $this->getCourse($enrollment);

        $data = [
            'form' => $form,
            'user' => $user,
            'enrollment' => $enrollment,
            'course' => $course,
            'completion_date' => $form->completion_date->format('m/d/Y'),
            'submission_deadline' => $form->submission_deadline->format('m/d/Y'),
            'form_number' => $form->form_number,
            'student_name' => $user->first_name . ' ' . $user->last_name,
            'student_address' => $this->formatAddress($user),
            'driver_license' => $user->driver_license ?? 'N/A',
            'date_of_birth' => $this->formatDateOfBirth($user),
            'course_title' => $course->title ?? 'Missouri Driver Improvement Program',
            'course_hours' => $this->formatDuration($course->duration ?? 480),
            'provider_info' => $this->getProviderInfo(),
            'instructions' => $this->getSubmissionInstructions($form->submission_method),
        ];

        $pdf = Pdf::loadView('certificates.missouri-form-4444', $data);
        $pdf->setPaper('letter', 'portrait');

        $filename = "missouri_form_4444_{$form->form_number}.pdf";
        $path = "certificates/missouri/{$filename}";

        // Store the PDF
        Storage::put($path, $pdf->output());

        // Update the form record with PDF path
        $form->update(['pdf_path' => $path]);

        return [
            'pdf' => $pdf,
            'path' => $path,
            'filename' => $filename,
        ];
    }

    private function getCourse($enrollment)
    {
        $courseTable = $enrollment->course_table ?? 'courses';
        
        if ($courseTable === 'missouri_courses') {
            return \App\Models\MissouriCourse::find($enrollment->course_id);
        }
        
        return \App\Models\Course::find($enrollment->course_id);
    }

    private function formatAddress($user)
    {
        $address = [];
        
        if ($user->address) {
            $address[] = $user->address;
        }
        
        if ($user->city || $user->state || $user->zip) {
            $cityStateZip = [];
            if ($user->city) $cityStateZip[] = $user->city;
            if ($user->state) $cityStateZip[] = $user->state;
            if ($user->zip) $cityStateZip[] = $user->zip;
            
            $address[] = implode(', ', $cityStateZip);
        }
        
        return implode("\n", $address) ?: 'Address not provided';
    }

    private function formatDateOfBirth($user)
    {
        if ($user->birth_month && $user->birth_day && $user->birth_year) {
            return sprintf('%02d/%02d/%04d', $user->birth_month, $user->birth_day, $user->birth_year);
        }
        
        return 'N/A';
    }

    private function formatDuration($minutes)
    {
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;
        
        if ($remainingMinutes > 0) {
            return "{$hours} hours {$remainingMinutes} minutes";
        }
        
        return "{$hours} hours";
    }

    private function getProviderInfo()
    {
        return [
            'name' => config('app.name', 'Dummies Traffic School'),
            'address' => '524 N. Mountain View Ave. #2',
            'city_state_zip' => 'San Bernardino, CA 92401',
            'phone' => '(877) 388-0829',
            'website' => 'www.dummiestrafficschool.com',
            'approval_number' => 'MO-DIP-2024-001', // Replace with actual approval number
        ];
    }

    private function getSubmissionInstructions($submissionMethod)
    {
        switch ($submissionMethod) {
            case 'point_reduction':
                return [
                    'title' => 'Point Reduction Instructions',
                    'steps' => [
                        '1. Print this Form 4444',
                        '2. Have your court or judge sign it (if required)',
                        '3. Submit to Missouri Department of Revenue (DOR) within 15 days',
                        '4. Keep a copy for your records',
                    ],
                    'deadline' => 'Must be received by DOR within 15 days of course completion',
                    'address' => 'Missouri Department of Revenue, Driver License Bureau, P.O. Box 200, Jefferson City, MO 65105-0200',
                ];
                
            case 'court_ordered':
                return [
                    'title' => 'Court-Ordered Completion Instructions',
                    'steps' => [
                        '1. Print this Form 4444',
                        '2. Submit to the court or clerk\'s office as instructed',
                        '3. Follow any additional court requirements',
                        '4. Keep a copy for your records',
                    ],
                    'deadline' => 'Submit according to court instructions',
                    'address' => 'Submit to the court that ordered the course',
                ];
                
            case 'insurance_discount':
                return [
                    'title' => 'Insurance Discount Instructions',
                    'steps' => [
                        '1. Print this Form 4444',
                        '2. Submit to your insurance provider',
                        '3. Contact your insurance company to confirm receipt',
                        '4. Keep a copy for your records',
                    ],
                    'deadline' => 'Submit according to insurance company requirements',
                    'address' => 'Submit to your insurance provider',
                ];
                
            default:
                return [
                    'title' => 'General Submission Instructions',
                    'steps' => [
                        '1. Print this Form 4444',
                        '2. Submit according to your specific requirements',
                        '3. Keep a copy for your records',
                    ],
                    'deadline' => 'Follow applicable deadlines',
                    'address' => 'Submit to appropriate authority',
                ];
        }
    }

    public function downloadForm4444(MissouriForm4444 $form)
    {
        if (!$form->pdf_path || !Storage::exists($form->pdf_path)) {
            // Generate PDF if it doesn't exist
            $result = $this->generateForm4444Pdf($form);
            return $result['pdf']->download("missouri_form_4444_{$form->form_number}.pdf");
        }

        return Storage::download($form->pdf_path, "missouri_form_4444_{$form->form_number}.pdf");
    }

    public function emailForm4444(MissouriForm4444 $form, $emailAddress = null)
    {
        $user = $form->user;
        $emailAddress = $emailAddress ?? $user->email;

        if (!$form->pdf_path || !Storage::exists($form->pdf_path)) {
            $this->generateForm4444Pdf($form);
        }

        // Send email with Form 4444 attachment
        \Mail::send('emails.missouri-form-4444', [
            'user' => $user,
            'form' => $form,
            'instructions' => $this->getSubmissionInstructions($form->submission_method),
        ], function ($message) use ($emailAddress, $form, $user) {
            $message->to($emailAddress, $user->first_name . ' ' . $user->last_name)
                    ->subject('Missouri Form 4444 - Driver Improvement Program Completion')
                    ->attach(Storage::path($form->pdf_path), [
                        'as' => "missouri_form_4444_{$form->form_number}.pdf",
                        'mime' => 'application/pdf',
                    ]);
        });

        return true;
    }
}