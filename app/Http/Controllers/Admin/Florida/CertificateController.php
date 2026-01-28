<?php

namespace App\Http\Controllers\Admin\Florida;

use App\Http\Controllers\Controller;
use App\Models\FloridaCertificate;
use App\Models\UserCourseEnrollment;
use App\Services\CertificatePdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CertificateController extends Controller
{
    protected $certificateService;

    public function __construct(CertificatePdfService $certificateService)
    {
        $this->certificateService = $certificateService;
    }

    public function index(Request $request)
    {
        $query = FloridaCertificate::with(['enrollment.user', 'enrollment.floridaCourse'])
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
            'total' => FloridaCertificate::count(),
            'pending' => FloridaCertificate::where('status', 'pending')->count(),
            'generated' => FloridaCertificate::where('status', 'generated')->count(),
            'sent' => FloridaCertificate::where('status', 'sent')->count(),
        ];

        return view('admin.florida.certificates.index', compact('certificates', 'stats'));
    }

    public function create()
    {
        $enrollments = UserCourseEnrollment::with(['user', 'floridaCourse'])
            ->where('course_table', 'florida_courses')
            ->whereNotNull('completed_at')
            ->whereDoesntHave('floridaCertificate')
            ->orderBy('completed_at', 'desc')
            ->get();

        return view('admin.florida.certificates.create', compact('enrollments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'enrollment_id' => 'required|exists:user_course_enrollments,id',
            'certificate_number' => 'nullable|string|unique:florida_certificates,certificate_number',
            'issue_date' => 'nullable|date',
        ]);

        $enrollment = UserCourseEnrollment::findOrFail($request->enrollment_id);

        // Check if certificate already exists
        if ($enrollment->floridaCertificate) {
            return redirect()->route('admin.florida.certificates.index')
                ->with('error', 'Certificate already exists for this enrollment.');
        }

        $certificate = FloridaCertificate::create([
            'enrollment_id' => $request->enrollment_id,
            'certificate_number' => $request->certificate_number ?? $this->generateCertificateNumber(),
            'issue_date' => $request->issue_date ?? now(),
            'status' => 'pending',
        ]);

        return redirect()->route('admin.florida.certificates.show', $certificate)
            ->with('success', 'Certificate created successfully.');
    }

    public function show(FloridaCertificate $certificate)
    {
        $certificate->load(['enrollment.user', 'enrollment.floridaCourse', 'stateTransmissions']);

        return view('admin.florida.certificates.show', compact('certificate'));
    }

    public function edit(FloridaCertificate $certificate)
    {
        return view('admin.florida.certificates.edit', compact('certificate'));
    }

    public function update(Request $request, FloridaCertificate $certificate)
    {
        $request->validate([
            'certificate_number' => 'required|string|unique:florida_certificates,certificate_number,' . $certificate->id,
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

        return redirect()->route('admin.florida.certificates.show', $certificate)
            ->with('success', 'Certificate updated successfully.');
    }

    public function destroy(FloridaCertificate $certificate)
    {
        // Delete PDF file if exists
        if ($certificate->pdf_path && Storage::exists($certificate->pdf_path)) {
            Storage::delete($certificate->pdf_path);
        }

        $certificate->delete();

        return redirect()->route('admin.florida.certificates.index')
            ->with('success', 'Certificate deleted successfully.');
    }

    public function generate(FloridaCertificate $certificate)
    {
        try {
            $pdfPath = $this->certificateService->generateCertificate($certificate);

            $certificate->update([
                'status' => 'generated',
                'pdf_path' => $pdfPath,
                'generated_at' => now(),
            ]);

            return redirect()->route('admin.florida.certificates.show', $certificate)
                ->with('success', 'Certificate PDF generated successfully.');

        } catch (\Exception $e) {
            $certificate->update([
                'status' => 'error',
                'notes' => 'PDF generation failed: ' . $e->getMessage(),
            ]);

            return redirect()->route('admin.florida.certificates.show', $certificate)
                ->with('error', 'Failed to generate certificate PDF: ' . $e->getMessage());
        }
    }

    public function download(FloridaCertificate $certificate)
    {
        if (!$certificate->pdf_path || !Storage::exists($certificate->pdf_path)) {
            return redirect()->back()
                ->with('error', 'Certificate PDF not found. Please generate it first.');
        }

        return Storage::download(
            $certificate->pdf_path,
            "certificate_{$certificate->certificate_number}.pdf"
        );
    }

    public function regenerate(FloridaCertificate $certificate)
    {
        try {
            // Delete old PDF if exists
            if ($certificate->pdf_path && Storage::exists($certificate->pdf_path)) {
                Storage::delete($certificate->pdf_path);
            }

            $pdfPath = $this->certificateService->generateCertificate($certificate);

            $certificate->update([
                'status' => 'generated',
                'pdf_path' => $pdfPath,
                'generated_at' => now(),
                'notes' => 'Certificate regenerated on ' . now()->format('Y-m-d H:i:s'),
            ]);

            return redirect()->route('admin.florida.certificates.show', $certificate)
                ->with('success', 'Certificate PDF regenerated successfully.');

        } catch (\Exception $e) {
            $certificate->update([
                'status' => 'error',
                'notes' => 'PDF regeneration failed: ' . $e->getMessage(),
            ]);

            return redirect()->route('admin.florida.certificates.show', $certificate)
                ->with('error', 'Failed to regenerate certificate PDF: ' . $e->getMessage());
        }
    }

    public function bulkGenerate(Request $request)
    {
        $request->validate([
            'certificate_ids' => 'required|array',
            'certificate_ids.*' => 'integer|exists:florida_certificates,id',
        ]);

        $certificates = FloridaCertificate::whereIn('id', $request->certificate_ids)->get();
        $successCount = 0;
        $errorCount = 0;

        foreach ($certificates as $certificate) {
            try {
                $pdfPath = $this->certificateService->generateCertificate($certificate);

                $certificate->update([
                    'status' => 'generated',
                    'pdf_path' => $pdfPath,
                    'generated_at' => now(),
                ]);

                $successCount++;
            } catch (\Exception $e) {
                $certificate->update([
                    'status' => 'error',
                    'notes' => 'Bulk generation failed: ' . $e->getMessage(),
                ]);

                $errorCount++;
            }
        }

        $message = "Bulk generation completed. Success: {$successCount}, Errors: {$errorCount}";

        return redirect()->route('admin.florida.certificates.index')
            ->with($errorCount > 0 ? 'warning' : 'success', $message);
    }

    public function export(Request $request)
    {
        $query = FloridaCertificate::with(['enrollment.user', 'enrollment.floridaCourse']);

        // Apply same filters as index
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

        $certificates = $query->get();

        $filename = 'florida_certificates_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($certificates) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, [
                'Certificate Number',
                'Student Name',
                'Email',
                'Course',
                'Status',
                'Issue Date',
                'Generated Date',
                'Completion Date'
            ]);

            // Data rows
            foreach ($certificates as $certificate) {
                fputcsv($file, [
                    $certificate->certificate_number,
                    $certificate->enrollment->user->first_name . ' ' . $certificate->enrollment->user->last_name,
                    $certificate->enrollment->user->email,
                    $certificate->enrollment->floridaCourse->title ?? 'N/A',
                    $certificate->status,
                    $certificate->issue_date?->format('Y-m-d'),
                    $certificate->generated_at?->format('Y-m-d H:i:s'),
                    $certificate->enrollment->completed_at?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function generateCertificateNumber()
    {
        $prefix = 'FL' . date('Y');
        $lastCertificate = FloridaCertificate::where('certificate_number', 'like', $prefix . '%')
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