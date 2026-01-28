<?php

namespace App\Http\Controllers\Admin\Texas;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\UserCourseEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CertificateController extends Controller
{
    public function index(Request $request)
    {
        $query = Certificate::with(['enrollment.user', 'enrollment.course'])
            ->whereHas('enrollment.course', function($q) {
                $q->where('state', 'texas');
            })
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $query->whereHas('enrollment.user', function($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->search . '%')
                  ->orWhere('last_name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('course_id')) {
            $query->whereHas('enrollment', function($q) use ($request) {
                $q->where('course_id', $request->course_id);
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $certificates = $query->paginate(20);

        $stats = [
            'total' => Certificate::whereHas('enrollment.course', function($q) {
                $q->where('state', 'texas');
            })->count(),
            'pending' => Certificate::whereHas('enrollment.course', function($q) {
                $q->where('state', 'texas');
            })->where('status', 'pending')->count(),
            'generated' => Certificate::whereHas('enrollment.course', function($q) {
                $q->where('state', 'texas');
            })->where('status', 'generated')->count(),
            'sent' => Certificate::whereHas('enrollment.course', function($q) {
                $q->where('state', 'texas');
            })->where('status', 'sent')->count(),
        ];

        return view('admin.texas.certificates.index', compact('certificates', 'stats'));
    }

    public function create()
    {
        $enrollments = UserCourseEnrollment::with(['user', 'course'])
            ->whereHas('course', function($q) {
                $q->where('state', 'texas');
            })
            ->whereNotNull('completed_at')
            ->whereDoesntHave('certificate')
            ->orderBy('completed_at', 'desc')
            ->get();

        return view('admin.texas.certificates.create', compact('enrollments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'enrollment_id' => 'required|exists:user_course_enrollments,id',
            'certificate_number' => 'nullable|string|unique:certificates,certificate_number',
            'issue_date' => 'nullable|date',
        ]);

        $enrollment = UserCourseEnrollment::with('course')->findOrFail($request->enrollment_id);

        if ($enrollment->course->state !== 'texas') {
            return redirect()->back()->with('error', 'Invalid enrollment selection.');
        }

        if ($enrollment->certificate) {
            return redirect()->route('admin.texas.certificates.index')
                ->with('error', 'Certificate already exists for this enrollment.');
        }

        $certificate = Certificate::create([
            'enrollment_id' => $request->enrollment_id,
            'certificate_number' => $request->certificate_number ?? $this->generateCertificateNumber(),
            'issue_date' => $request->issue_date ?? now(),
            'status' => 'pending',
        ]);

        return redirect()->route('admin.texas.certificates.show', $certificate)
            ->with('success', 'Texas certificate created successfully.');
    }

    public function show(Certificate $certificate)
    {
        $certificate->load(['enrollment.user', 'enrollment.course']);

        return view('admin.texas.certificates.show', compact('certificate'));
    }

    public function edit(Certificate $certificate)
    {
        return view('admin.texas.certificates.edit', compact('certificate'));
    }

    public function update(Request $request, Certificate $certificate)
    {
        $request->validate([
            'certificate_number' => 'required|string|unique:certificates,certificate_number,' . $certificate->id,
            'issue_date' => 'required|date',
            'status' => 'required|in:pending,generated,sent,error',
            'notes' => 'nullable|string',
        ]);

        $certificate->update([
            'certificate_number' => $request->certificate_number,
            'issue_date' => $request->issue_date,
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        return redirect()->route('admin.texas.certificates.show', $certificate)
            ->with('success', 'Texas certificate updated successfully.');
    }

    public function destroy(Certificate $certificate)
    {
        if ($certificate->pdf_path && Storage::exists($certificate->pdf_path)) {
            Storage::delete($certificate->pdf_path);
        }

        $certificate->delete();

        return redirect()->route('admin.texas.certificates.index')
            ->with('success', 'Texas certificate deleted successfully.');
    }

    private function generateCertificateNumber()
    {
        $prefix = 'TX' . date('Y');
        $lastCertificate = Certificate::whereHas('enrollment.course', function($q) {
                $q->where('state', 'texas');
            })
            ->where('certificate_number', 'like', $prefix . '%')
            ->orderBy('certificate_number', 'desc')
            ->first();

        if ($lastCertificate) {
            $lastNumber = (int) substr($lastCertificate->certificate_number, -6);
            $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '000001';
        }

        return $prefix . $newNumber;
    }
}