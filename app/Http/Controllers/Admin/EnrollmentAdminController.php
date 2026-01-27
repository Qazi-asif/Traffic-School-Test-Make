<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\FloridaCourse;
use App\Models\UserCourseEnrollment;
use Illuminate\Http\Request;

class EnrollmentAdminController extends Controller
{
    public function show($id)
    {
        $enrollment = UserCourseEnrollment::with([
            'user',
            'floridaCourse',
            'legacyCourse',
            'progress.chapter',
            'floridaCertificate',
            'stateTransmissions',
        ])->findOrFail($id);

        // Get all courses for dropdown
        $floridaCourses = FloridaCourse::all();
        $courses = Course::all();

        // Get Florida counties (hardcoded list)
        $counties = collect([
            'Alachua', 'Baker', 'Bay', 'Bradford', 'Brevard', 'Broward', 'Calhoun', 'Charlotte',
            'Citrus', 'Clay', 'Collier', 'Columbia', 'Dade', 'Desto', 'Dixie', 'Duval', 'Escambia',
            'Flagler', 'Franklin', 'Gadsen', 'Gilchrist', 'Glades', 'Gulf', 'Hamilton', 'Hardee',
            'Hendry', 'Hernando', 'Highlands', 'Hillsborough', 'Holmes', 'Indian River', 'Jackson',
            'Jefferson', 'Lafayette', 'Lake', 'Lee', 'Leon', 'Levy', 'Liberty', 'Madison', 'Manatee',
            'Marion', 'Martin', 'Monroe', 'Nassau', 'Okaloosa', 'Okeechobee', 'Orange', 'Osceola',
            'Out Of State', 'Palm Beach', 'Pasco', 'Pinellas', 'Polk', 'Putnam', 'Santa Rosa',
            'Sarasota', 'Seminole', 'St Johns', 'St Lucie', 'Sumter', 'Suwannee', 'Taylor', 'Union',
            'Volusia', 'Wakulla', 'Walton', 'Washington',
        ])->map(fn ($name) => (object) ['name' => $name]);

        $courts = collect([]); // Empty for now, can be populated later

        // Get latest transmission
        $latestTransmission = $enrollment->stateTransmissions()->latest()->first();

        return view('admin.enrollment-edit', compact(
            'enrollment',
            'floridaCourses',
            'courses',
            'counties',
            'courts',
            'latestTransmission'
        ));
    }

    public function update(Request $request, $id)
    {
        $enrollment = UserCourseEnrollment::findOrFail($id);
        $user = $enrollment->user;

        // Update user information
        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'gender' => $request->gender,
            'birth_month' => $request->birth_month,
            'birth_day' => $request->birth_day,
            'birth_year' => $request->birth_year,
            'driver_license' => $request->driver_license,
            'license_class' => $request->license_class,
            'license_state' => $request->license_state,
            'citation_number' => $request->citation_number,
            'due_month' => $request->due_month,
            'due_day' => $request->due_day,
            'due_year' => $request->due_year,
            'state' => $request->state,
            'city' => $request->city,
            'address' => $request->address_1,
            'zip' => $request->zip,
        ]);

        // Update enrollment information
        $enrollmentData = [
            'course_id' => $request->course_id,
            'started_at' => $request->start_date,
            'completed_at' => $request->finish_date,
            'payment_status' => $request->payment_status,
            'payment_method' => $request->payment_method,
            'amount_paid' => $request->amount_paid,
            'status' => $request->status,
            'total_time_spent' => $request->total_time_spent,
        ];

        // Add court information if provided
        if ($request->filled('case_number')) {
            $enrollmentData['case_number'] = $request->case_number;
        }
        if ($request->filled('citation_number')) {
            $enrollmentData['citation_number'] = $request->citation_number;
        }
        if ($request->filled('court_selected')) {
            $enrollmentData['court_selected'] = $request->court_selected;
        }
        if ($request->filled('court_state')) {
            $enrollmentData['court_state'] = $request->court_state;
        }
        if ($request->filled('court_county')) {
            $enrollmentData['court_county'] = $request->court_county;
        }

        $enrollment->update($enrollmentData);

        return redirect()->back()->with('success', 'Enrollment updated successfully!');
    }

    public function resendCertificate($id)
    {
        try {
            $enrollment = UserCourseEnrollment::findOrFail($id);
            $certificate = $enrollment->floridaCertificate;

            if (! $certificate) {
                return response()->json(['message' => 'No certificate found for this enrollment'], 404);
            }

            // Resend certificate email
            $enrollment->user->notify(new \App\Notifications\CertificateGenerated($certificate));

            return response()->json(['message' => 'Certificate PDF sent successfully!']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error sending certificate: '.$e->getMessage()], 500);
        }
    }

    public function resendTransmission($id)
    {
        try {
            $enrollment = UserCourseEnrollment::findOrFail($id);

            // Dispatch the Florida transmission job
            \App\Jobs\SendFloridaTransmissionJob::dispatch($enrollment->id);

            return response()->json(['message' => 'Florida transmission queued successfully!']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error queuing transmission: '.$e->getMessage()], 500);
        }
    }

    public function emailReceipt($id)
    {
        try {
            $enrollment = UserCourseEnrollment::findOrFail($id);

            if ($enrollment->payment_status !== 'paid') {
                return response()->json(['message' => 'Payment not completed for this enrollment'], 400);
            }

            // Send payment receipt email
            \Mail::to($enrollment->user->email)->send(new \App\Mail\PaymentReceipt($enrollment));

            return response()->json(['message' => 'Payment receipt sent successfully!']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error sending receipt: '.$e->getMessage()], 500);
        }
    }
}
