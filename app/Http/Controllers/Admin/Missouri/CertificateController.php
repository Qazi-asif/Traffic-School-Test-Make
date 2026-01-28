<?php

namespace App\Http\Controllers\Admin\Missouri;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\UserCourseEnrollment;
use App\Models\MissouriForm4444;
use App\Services\MissouriForm4444PdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CertificateController extends Controller
{
    protected $form4444Service;

    public function __construct(MissouriForm4444PdfService $form4444Service = null)
    {
        $this->form4444Service = $form4444Service;
    }

    public function index(Request $request)
    {
        $query = Certificate::with(['enrollment.user', 'enrollment.course'])
            ->whereHas('enrollment.course', function($q) {
                $q->where('state', 'missouri');
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
                $q->where('state', 'missouri');
            })->count(),
            'pending' => Certificate::whereHas('enrollment.course', function($q) {
                $q->where('state', 'missouri');
            })->where('status', 'pending')->count(),
            'generated' => Certificate::whereHas('enrollment.course', function($q) {
                $q->where('state', 'missouri');
            })->where('status', 'generated')->count(),
            'sent' => Certificate::whereHas('enrollment.course', function($q) {
                $q->where('state', 'missouri');
            })->where('status', 'sent')->count(),
        ];

        return view('admin.missouri.certificates.index', compact('certificates', 'stats'));
    }

    public function create()
    {
        $enrollments = UserCourseEnrollment::with(['user', 'course'])
            ->whereHas('course', function($q) {
                $q->where('state', 'missouri');
            })
            ->whereNotNull('completed_at')
            ->whereDoesntHave('certificate')
            ->orderBy('completed_at', 'desc')
            ->get();

        return view('admin.missouri.certificates.create', compact('enrollments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'enrollment_id' => 'required|exists:user_course_enrollments,id',
            'certificate_number' => 'nullable|string|unique:certificates,certificate_number',
            'issue_date' => 'nullable|date',
        ]);

        $enrollment = UserCourseEnrollment::with('course')->findOrFail($request->enrollment_id);

        // Verify enrollment is for Missouri course
        if ($enrollment->course->state !== 'missouri') {
            return redirect()->back()->with('error', 'Invalid enrollment selection.');
        }

        // Check if certificate already exists
        if ($enrollment->certificate) {
            return redirect()->route('admin.missouri.certificates.index')
                ->with('error', 'Certificate already exists for this enrollment.');
        }

        $certificate = Certificate::create([
            'enrollment_id' => $request->enrollment_id,
            'certificate_number' => $request->certificate_number ?? $this->generateCertificateNumber(),
            'issue_date' => $request->issue_date ?? now(),
            'status' => 'pending',
        ]);

        return redirect()->route('admin.missouri.certificates.show', $certificate)
            ->with('success', 'Missouri certificate created successfully.');
    }

    public function show(Certificate $certificate)
    {
        $certificate->load(['enrollment.user', 'enrollment.course']);

        // Get Missouri Form 4444 if exists
        $form4444 = MissouriForm4444::where('enrollment_id', $certificate->enrollment_id)->first();

        return view('admin.missouri.certificates.show', compact('certificate', 'form4444'));
    }

    public function edit(Certificate $certificate)
    {
        return view('admin.missouri.certificates.edit', compact('certificate'));
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

        return redirect()->route('admin.missouri.certificates.show', $certificate)
            ->with('success', 'Missouri certificate updated successfully.');
    }

    public function destroy(Certificate $certificate)
    {
        // Delete PDF file if exists
        if ($certificate->pdf_path && Storage::exists($certificate->pdf_path)) {
            Storage::delete($certificate->pdf_path);
        }

        $certificate->delete();

        return redirect()->route('admin.missouri.certificates.index')
            ->with('success', 'Missouri certificate deleted successfully.');
    }

    public function generate(Certificate $certificate)
    {
        try {
            // For Missouri, we might generate both certificate and Form 4444
            // This is a placeholder - implement based on your Missouri requirements
            
            $certificate->update([
                'status' => 'generated',
                'generated_at' => now(),
            ]);

            return redirect()->route('admin.missouri.certificates.show', $certificate)
                ->with('success', 'Missouri certificate generated successfully.');

        } catch (\Exception $e) {
            $certificate->update([
                'status' => 'error',
                'notes' => 'Generation failed: ' . $e->getMessage(),
            ]);

            return redirect()->route('admin.missouri.certificates.show', $certificate)
                ->with('error', 'Failed to generate Missouri certificate: ' . $e->getMessage());
        }
    }

    public function generateForm4444(Certificate $certificate)
    {
        if (!$this->form4444Service) {
            return redirect()->back()
                ->with('error', 'Missouri Form 4444 service not available.');
        }

        try {
            $form4444 = $this->form4444Service->generateForm4444($certificate->enrollment);

            return redirect()->route('admin.missouri.certificates.show', $certificate)
                ->with('success', 'Missouri Form 4444 generated successfully.');

        } catch (\Exception $e) {
            return redirect()->route('admin.missouri.certificates.show', $certificate)
                ->with('error', 'Failed to generate Form 4444: ' . $e->getMessage());
        }
    }

    public function downloadForm4444(Certificate $certificate)
    {
        $form4444 = MissouriForm4444::where('enrollment_id', $certificate->enrollment_id)->first();

        if (!$form4444 || !$form4444->pdf_path || !Storage::exists($form4444->pdf_path)) {
            return redirect()->back()
                ->with('error', 'Form 4444 PDF not found. Please generate it first.');
        }

        return Storage::download(
            $form4444->pdf_path,
            "missouri_form4444_{$certificate->certificate_number}.pdf"
        );
    }

    private function generateCertificateNumber()
    {
        $prefix = 'MO' . date('Y');
        $lastCertificate = Certificate::whereHas('enrollment.course', function($q) {
                $q->where('state', 'missouri');
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