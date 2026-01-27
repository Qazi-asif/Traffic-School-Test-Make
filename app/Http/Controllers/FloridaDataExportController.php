<?php

namespace App\Http\Controllers;

use App\Models\FloridaDataExport;
use Illuminate\Http\Request;

class FloridaDataExportController extends Controller
{
    public function request(Request $request)
    {
        $request->validate([
            'export_type' => 'required|in:gdpr,ccpa,florida_public_records,internal_audit',
        ]);

        $export = FloridaDataExport::create([
            'user_id' => auth()->id(),
            'export_type' => $request->export_type,
            'status' => 'pending',
            'requested_at' => now(),
        ]);

        return response()->json($export);
    }

    public function status($id)
    {
        $export = FloridaDataExport::findOrFail($id);

        return response()->json($export);
    }

    public function download($id)
    {
        $export = FloridaDataExport::findOrFail($id);

        if ($export->status !== 'completed' || ! $export->file_path) {
            return response()->json(['error' => 'Export not ready'], 400);
        }

        return response()->download(storage_path('app/'.$export->file_path));
    }
}
