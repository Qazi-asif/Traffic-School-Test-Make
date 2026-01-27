<?php

namespace App\Http\Controllers;

use App\Models\MissouriForm4444;
use App\Models\MissouriSubmissionTracker;
use App\Models\UserCourseEnrollment;
use App\Services\MissouriForm4444PdfService;
use Illuminate\Http\Request;

class MissouriController extends Controller
{
    protected $pdfService;

    public function __construct(MissouriForm4444PdfService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    public function generateForm4444(Request $request)
    {
        $request->validate([
            'enrollment_id' => 'required|exists:user_course_enrollments,id',
            'submission_method' => 'required|in:point_reduction,court_ordered,insurance_discount,voluntary',
        ]);

        $enrollment = UserCourseEnrollment::findOrFail($request->enrollment_id);

        // Check if Form 4444 already exists for this enrollment
        $existingForm = MissouriForm4444::where('enrollment_id', $enrollment->id)->first();
        
        if ($existingForm) {
            return response()->json([
                'success' => true,
                'form' => $existingForm,
                'message' => 'Form 4444 already exists for this enrollment',
                'pdf_url' => route('missouri.form4444.download', $existingForm->id),
            ]);
        }

        // Create Form 4444 record
        $form = MissouriForm4444::create([
            'user_id' => $enrollment->user_id,
            'enrollment_id' => $enrollment->id,
            'form_number' => 'MO-4444-' . time() . '-' . $enrollment->id,
            'completion_date' => now(),
            'submission_deadline' => now()->addDays(15),
            'submission_method' => $request->submission_method,
            'court_signature_required' => $request->submission_method === 'point_reduction',
            'status' => 'ready_for_submission',
        ]);

        // Generate PDF
        try {
            $pdfResult = $this->pdfService->generateForm4444Pdf($form);
            
            // Create submission tracker
            MissouriSubmissionTracker::create([
                'form_4444_id' => $form->id,
                'user_id' => $enrollment->user_id,
                'completion_date' => now(),
                'submission_deadline' => now()->addDays(15),
                'days_remaining' => 15,
                'status' => 'active',
            ]);

            // Send email with Form 4444
            if ($request->send_email !== false) {
                $this->pdfService->emailForm4444($form);
            }

            return response()->json([
                'success' => true,
                'form' => $form,
                'message' => 'Form 4444 generated successfully',
                'pdf_url' => route('missouri.form4444.download', $form->id),
            ]);
            
        } catch (\Exception $e) {
            // If PDF generation fails, delete the form record
            $form->delete();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate Form 4444 PDF: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function downloadForm4444($formId)
    {
        $form = MissouriForm4444::findOrFail($formId);
        
        // Check if user can access this form
        if (auth()->id() !== $form->user_id && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access to Form 4444');
        }

        return $this->pdfService->downloadForm4444($form);
    }

    public function emailForm4444(Request $request, $formId)
    {
        $form = MissouriForm4444::findOrFail($formId);
        
        // Check if user can access this form
        if (auth()->id() !== $form->user_id && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access to Form 4444');
        }

        $emailAddress = $request->email ?? $form->user->email;
        
        try {
            $this->pdfService->emailForm4444($form, $emailAddress);
            
            return response()->json([
                'success' => true,
                'message' => 'Form 4444 sent successfully to ' . $emailAddress,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send Form 4444: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getSubmissionStatus($userId)
    {
        $trackers = MissouriSubmissionTracker::where('user_id', $userId)
            ->with(['form4444', 'user'])
            ->get()
            ->map(function ($tracker) {
                $tracker->days_remaining = $tracker->calculateDaysRemaining();
                $tracker->is_expired = $tracker->isExpired();

                return $tracker;
            });

        return response()->json($trackers);
    }

    public function submitToDOR(Request $request, $formId)
    {
        $form = MissouriForm4444::findOrFail($formId);

        $form->update([
            'submitted_to_dor' => true,
            'dor_submission_date' => now(),
            'status' => 'submitted_to_dor',
        ]);

        // Update tracker
        $tracker = MissouriSubmissionTracker::where('form_4444_id', $formId)->first();
        if ($tracker) {
            $tracker->update(['status' => 'submitted']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Form submitted to Missouri DOR successfully',
        ]);
    }

    public function getExpiringForms()
    {
        $expiringSoon = MissouriSubmissionTracker::whereRaw('DATEDIFF(submission_deadline, NOW()) <= 3')
            ->where('status', 'active')
            ->with(['form4444', 'user'])
            ->get();

        return response()->json($expiringSoon);
    }

    public function getUserForms($userId)
    {
        $forms = MissouriForm4444::where('user_id', $userId)
            ->with(['enrollment', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($forms);
    }

    public function getAllForms()
    {
        $forms = MissouriForm4444::with(['user', 'enrollment'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($forms);
    }
}
