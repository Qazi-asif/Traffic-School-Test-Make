<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContentAccessLogController extends Controller
{
    public function log(Request $request)
    {
        $request->validate([
            'action' => 'required|string',
            'timestamp' => 'required|string',
            'user_agent' => 'required|string',
        ]);

        try {
            DB::table('content_access_logs')->insert([
                'user_id' => auth()->id(),
                'action' => $request->action,
                'timestamp' => $request->timestamp,
                'user_agent' => $request->user_agent,
                'ip_address' => $request->ip(),
                'created_at' => now(),
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('Failed to log content access: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to log access'], 500);
        }
    }
}
