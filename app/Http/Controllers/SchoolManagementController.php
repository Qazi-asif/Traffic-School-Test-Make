<?php

namespace App\Http\Controllers;

use App\Models\FloridaSchool;
use App\Models\SchoolManagementLog;
use Illuminate\Http\Request;

class SchoolManagementController extends Controller
{
    public function togglePrinting(Request $request, $id)
    {
        $school = FloridaSchool::findOrFail($id);
        $printingEnabled = $request->input('printing_enabled');

        SchoolManagementLog::create([
            'school_id' => $id,
            'action' => $printingEnabled ? 'enabled' : 'disabled',
            'performed_by' => auth()->id(),
            'details' => ['printing_enabled' => $printingEnabled],
            'performed_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function addCourses(Request $request, $id)
    {
        $school = FloridaSchool::findOrFail($id);

        SchoolManagementLog::create([
            'school_id' => $id,
            'action' => 'course_added',
            'performed_by' => auth()->id(),
            'details' => $request->courses,
            'performed_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }
}
