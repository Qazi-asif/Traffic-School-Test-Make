<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index(Request $request)
    {
        $faqs = Faq::where('is_active', true)
            ->when($request->category, function ($query, $category) {
                return $query->where('category', $category);
            })
            ->when($request->language, function ($query, $language) {
                return $query->where('language', $language);
            })
            ->orderBy('order')
            ->get()
            ->groupBy('category');

        // For web view
        if (! $request->expectsJson() && ! $request->is('api/*')) {
            return view('admin.faqs');
        }

        return response()->json($faqs);
    }

    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|string|max:255',
            'question' => 'required|string',
            'answer' => 'required|string',
            'order' => 'nullable|integer',
            'language' => 'nullable|string|size:2',
        ]);

        $faq = Faq::create($request->all());

        return response()->json(['success' => true, 'faq' => $faq]);
    }

    public function update(Request $request, $id)
    {
        $faq = Faq::findOrFail($id);

        $request->validate([
            'category' => 'string|max:255',
            'question' => 'string',
            'answer' => 'string',
            'order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $faq->update($request->all());

        return response()->json(['success' => true, 'faq' => $faq]);
    }

    public function destroy($id)
    {
        $faq = Faq::findOrFail($id);
        $faq->delete();

        return response()->json(['success' => true]);
    }
}
