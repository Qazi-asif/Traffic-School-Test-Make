<?php

namespace App\Http\Controllers;

use App\Models\CopyrightProtectionLog;
use Illuminate\Http\Request;

class CopyrightProtectionController extends Controller
{
    public function log(Request $request)
    {
        $log = CopyrightProtectionLog::create([
            'user_id' => auth()->id(),
            'action' => $request->action,
            'page_url' => $request->page_url,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'details' => $request->details,
        ]);

        return response()->json($log, 201);
    }

    public function stats()
    {
        $stats = CopyrightProtectionLog::selectRaw('action, COUNT(*) as count')
            ->groupBy('action')
            ->get();

        return response()->json($stats);
    }
}
