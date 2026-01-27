<?php

namespace App\Http\Controllers;

use App\Models\UserLegalConsent;
use Illuminate\Http\Request;

class ConsentController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'document_id' => 'required|exists:legal_documents,id',
            'consent_given' => 'required|boolean',
        ]);

        $consent = UserLegalConsent::create([
            'user_id' => auth()->id(),
            'document_id' => $validated['document_id'],
            'consent_given' => $validated['consent_given'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'consented_at' => now(),
        ]);

        return response()->json($consent, 201);
    }

    public function status()
    {
        $consents = UserLegalConsent::where('user_id', auth()->id())
            ->with('document')
            ->get();

        return response()->json($consents);
    }

    public function history()
    {
        $history = UserLegalConsent::where('user_id', auth()->id())
            ->with('document')
            ->orderBy('consented_at', 'desc')
            ->get();

        return response()->json($history);
    }
}
