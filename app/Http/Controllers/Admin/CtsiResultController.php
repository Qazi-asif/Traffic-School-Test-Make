<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CtsiResult;
use Illuminate\Http\Request;

class CtsiResultController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');

        $query = CtsiResult::with(['enrollment.user', 'enrollment.course'])
            ->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $results = $query->paginate(50);

        // Status counts
        $pending = CtsiResult::pending()->count();
        $success = CtsiResult::success()->count();
        $failed = CtsiResult::failed()->count();

        return view('admin.ctsi-results.index', compact(
            'results',
            'status',
            'pending',
            'success',
            'failed'
        ));
    }

    public function show($id)
    {
        $result = CtsiResult::with(['enrollment.user', 'enrollment.course', 'enrollment.californiaCertificate'])
            ->findOrFail($id);

        return view('admin.ctsi-results.show', compact('result'));
    }

    public function destroy($id)
    {
        $result = CtsiResult::findOrFail($id);
        $result->delete();

        return redirect()->route('admin.ctsi-results.index')
            ->with('success', 'CTSI result deleted successfully');
    }
}
