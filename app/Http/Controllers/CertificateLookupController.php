<?php

namespace App\Http\Controllers;

use App\Models\CertificateLookupLog;
use App\Models\FloridaCertificate;
use Illuminate\Http\Request;

class CertificateLookupController extends Controller
{
    public function search(Request $request)
    {
        $validated = $request->validate([
            'search_type' => 'required|in:certificate_number,student_name',
            'search_term' => 'required|string|min:2',
        ]);

        $query = FloridaCertificate::query();

        if ($validated['search_type'] === 'certificate_number') {
            $query->where('certificate_number', $validated['search_term']);
        } else {
            $query->where('student_last_name', 'LIKE', '%'.$validated['search_term'].'%');
        }

        $results = $query->get();

        CertificateLookupLog::create([
            'searched_by' => auth()->id(),
            'search_type' => $validated['search_type'],
            'search_term' => $validated['search_term'],
            'results_count' => $results->count(),
            'searched_at' => now(),
        ]);

        return response()->json($results);
    }

    public function reprint($id)
    {
        $certificate = FloridaCertificate::findOrFail($id);

        CertificateLookupLog::where('searched_by', auth()->id())
            ->latest()
            ->first()
            ->update(['certificate_reprinted' => true]);

        return response()->json($certificate);
    }
}
