<?php

namespace App\Http\Controllers;

use App\Models\LegalDocument;
use Illuminate\Http\Request;

class LegalDocumentController extends Controller
{
    public function index()
    {
        return response()->json(LegalDocument::where('is_active', true)->get());
    }

    public function getByType($type)
    {
        $document = LegalDocument::where('document_type', $type)
            ->where('is_active', true)
            ->latest('effective_date')
            ->first();

        return response()->json($document);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'document_type' => 'required|in:privacy_policy,terms_of_service,copyright_notice,disclaimer,refund_policy',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'version' => 'required|string',
            'effective_date' => 'required|date',
            'requires_consent' => 'boolean',
        ]);

        $validated['created_by'] = auth()->id();
        $document = LegalDocument::create($validated);

        return response()->json($document, 201);
    }

    public function update(Request $request, $id)
    {
        $document = LegalDocument::findOrFail($id);
        $document->update($request->all());

        return response()->json($document);
    }

    public function activate($id)
    {
        $document = LegalDocument::findOrFail($id);
        LegalDocument::where('document_type', $document->document_type)->update(['is_active' => false]);
        $document->update(['is_active' => true]);

        return response()->json($document);
    }
}
