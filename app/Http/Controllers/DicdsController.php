<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\FloridaCourse;
use App\Models\FloridaSchool;
use App\Models\Instructor;
use App\Models\School;
use App\Models\SchoolCourse;
use Illuminate\Http\Request;

class DicdsController extends Controller
{
    public function mainMenu()
    {
        return view('dicds.main-menu');
    }

    public function welcome()
    {
        return view('dicds.welcome');
    }

    public function providerMenu()
    {
        return view('dicds.provider-menu');
    }

    // School Management
    public function addSchool()
    {
        return view('dicds.schools.add');
    }

    public function storeSchool(Request $request)
    {
        $request->validate([
            'school_name' => 'required|string',
            'address' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'zip_code' => 'required|string',
            'phone' => 'required|string',
            'email' => 'required|email',
        ]);

        School::create($request->all());

        return redirect()->route('dicds.provider-menu')->with('success', 'School added successfully');
    }

    public function maintainSchool(Request $request)
    {
        $schools = School::when($request->search, fn ($q) => $q->where('school_name', 'like', "%{$request->search}%"))
            ->get();

        return view('dicds.schools.maintain', compact('schools'));
    }

    public function editSchool($id)
    {
        $school = School::findOrFail($id);

        return view('dicds.schools.edit', compact('school'));
    }

    public function updateSchool(Request $request, $id)
    {
        $request->validate([
            'school_name' => 'required|string',
            'address' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'zip_code' => 'required|string',
            'phone' => 'required|string',
            'email' => 'required|email',
        ]);

        $school = School::findOrFail($id);
        $school->update($request->all());

        return redirect()->route('dicds.schools.maintain')->with('success', 'School updated successfully');
    }

    public function destroySchool($id)
    {
        School::findOrFail($id)->delete();

        return redirect()->route('dicds.schools.maintain')->with('success', 'School deleted successfully');
    }

    public function searchSchool(Request $request)
    {
        $schools = School::when($request->search, fn ($q) => $q->where('school_name', 'like', "%{$request->search}%"))
            ->get();

        return view('dicds.schools.maintain', compact('schools'));
    }

    // Course Management
    public function addCourse()
    {
        $schools = FloridaSchool::all() ?? collect();
        $courses = FloridaCourse::all() ?? collect();

        return view('dicds.courses.add', compact('schools', 'courses'));
    }

    public function storeCourse(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'course_type' => 'nullable|string',
            'delivery_type' => 'nullable|string',
            'price' => 'nullable|numeric',
            'min_pass_score' => 'nullable|integer',
            'total_duration' => 'nullable|integer',
        ]);

        SchoolCourse::create($validated);

        return redirect()->route('dicds.provider-menu')->with('success', 'Course added to school successfully');
    }

    // Contact
    public function contact()
    {
        return view('dicds.contact');
    }

    public function submitContact(Request $request)
    {
        return redirect()->route('dicds.provider-menu')->with('success', 'Your message has been sent successfully');
    }

    // Instructor Management
    public function manageInstructors(Request $request)
    {
        $instructors = Instructor::with('school')
            ->when($request->search, fn ($q) => $q->where('first_name', 'like', "%{$request->search}%")
                ->orWhere('last_name', 'like', "%{$request->search}%"))
            ->get();

        return view('dicds.instructors.manage', compact('instructors'));
    }

    public function addInstructor()
    {
        $schools = FloridaSchool::all() ?? collect();

        return view('dicds.instructors.add', compact('schools'));
    }

    public function storeInstructor(Request $request)
    {
        $validated = $request->validate([
            'school_id' => 'required|exists:dicds_schools,id',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
            'address' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'zip_code' => 'required|string',
        ]);

        Instructor::create($validated);

        return redirect()->route('dicds.instructors.manage')->with('success', 'Instructor added successfully');
    }

    public function editInstructor($id)
    {
        $instructor = Instructor::findOrFail($id);
        $schools = FloridaSchool::all() ?? collect();

        return view('dicds.instructors.edit', compact('instructor', 'schools'));
    }

    public function updateInstructor(Request $request, $id)
    {
        $validated = $request->validate([
            'school_id' => 'required|exists:dicds_schools,id',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
            'address' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'zip_code' => 'required|string',
        ]);

        Instructor::findOrFail($id)->update($validated);

        return redirect()->route('dicds.instructors.manage')->with('success', 'Instructor updated successfully');
    }

    public function destroyInstructor($id)
    {
        Instructor::findOrFail($id)->delete();

        return redirect()->route('dicds.instructors.manage')->with('success', 'Instructor deleted successfully');
    }

    // Certificate Management
    public function orderCertificates()
    {
        $courses = FloridaCourse::all() ?? collect();
        $schools = FloridaSchool::all() ?? collect();

        return view('dicds.certificates.order', compact('courses', 'schools'));
    }

    public function storeOrder(Request $request)
    {
        $orders = $request->orders ?? [];
        $total = 0;
        foreach ($orders as $order) {
            if ($order['count'] > 0) {
                Certificate::create([
                    'course_id' => $order['course_id'],
                    'certificate_count' => $order['count'],
                    'provider_id' => auth('dicds')->id(),
                    'status' => 'Pending',
                    'total_amount' => $order['count'] * 25,
                    'order_number' => 'ORD-'.time(),
                ]);
                $total += $order['count'] * 25;
            }
        }

        return view('dicds.certificates.receipt', compact('total'));
    }

    public function distributeCertificates()
    {
        $schools = FloridaSchool::all() ?? collect();
        $courses = FloridaCourse::all() ?? collect();

        return view('dicds.certificates.distribute', compact('schools', 'courses'));
    }

    public function storeDistribution(Request $request)
    {
        $request->validate([
            'school_id' => 'required|exists:florida_schools,id',
            'course_id' => 'required|exists:florida_courses,id',
            'amount' => 'required|integer|min:1',
        ]);

        // Logic to distribute certificates
        return redirect()->route('dicds.certificates.distribute')->with('success', 'Certificates distributed successfully');
    }

    public function reclaimCertificates()
    {
        $schools = FloridaSchool::all() ?? collect();
        $courses = FloridaCourse::all() ?? collect();

        return view('dicds.certificates.reclaim', compact('schools', 'courses'));
    }

    public function maintainCertificates()
    {
        return view('dicds.certificates.maintain');
    }

    // Reports
    public function schoolsCertificates()
    {
        return view('dicds.reports.schools-certificates');
    }

    public function certificateReports()
    {
        return view('dicds.reports.menu');
    }

    public function certificateLookup()
    {
        return view('dicds.reports.certificate-lookup');
    }

    public function schoolActivity(Request $request)
    {
        $schools = FloridaSchool::all() ?? collect();
        $courses = FloridaCourse::all() ?? collect();

        $report = null;
        if ($request->has('date_from')) {
            $school = FloridaSchool::find($request->school_id);
            $course = FloridaCourse::find($request->course_id);

            $certificates = \App\Models\FloridaCertificate::where('course_name', $course->title ?? '')
                ->whereBetween('completion_date', [$request->date_from, $request->date_to])
                ->get();

            $report = [
                'school' => $school,
                'course' => $course,
                'date_from' => $request->date_from,
                'date_to' => $request->date_to,
                'certificates' => $certificates,
                'total_count' => $certificates->count(),
            ];
        }

        return view('dicds.reports.school-activity', compact('schools', 'courses', 'report'));
    }

    public function webServiceInfo()
    {
        return view('dicds.reports.web-service-info');
    }
}
