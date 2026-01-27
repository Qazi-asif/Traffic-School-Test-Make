<?php

namespace App\Http\Controllers;

use App\Models\EmailLog;
use Illuminate\Http\Request;

class EmailLogController extends Controller
{
    public function index(Request $request)
    {
        $query = EmailLog::with('template');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('template_id')) {
            $query->where('template_id', $request->template_id);
        }

        if ($request->has('recipient_email')) {
            $query->where('recipient_email', 'like', '%'.$request->recipient_email.'%');
        }

        if ($request->has('date_from')) {
            $query->whereDate('sent_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('sent_at', '<=', $request->date_to);
        }

        $logs = $query->orderBy('sent_at', 'desc')->paginate(50);

        return response()->json($logs);
    }

    public function show(EmailLog $emailLog)
    {
        return response()->json($emailLog->load('template'));
    }

    public function stats()
    {
        $stats = [
            'total_sent' => EmailLog::count(),
            'delivered' => EmailLog::where('status', 'delivered')->count(),
            'opened' => EmailLog::where('status', 'opened')->count(),
            'failed' => EmailLog::where('status', 'failed')->count(),
            'bounced' => EmailLog::where('status', 'bounced')->count(),
            'today_sent' => EmailLog::whereDate('sent_at', today())->count(),
        ];

        return response()->json($stats);
    }
}
