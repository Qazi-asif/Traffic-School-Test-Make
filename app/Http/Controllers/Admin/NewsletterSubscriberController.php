<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use App\Services\NewsletterService;
use Illuminate\Http\Request;

class NewsletterSubscriberController extends Controller
{
    protected $newsletterService;

    public function __construct(NewsletterService $newsletterService)
    {
        $this->newsletterService = $newsletterService;
    }

    public function index(Request $request)
    {
        $query = NewsletterSubscriber::query();

        if ($request->filled('state')) {
            $query->where('state_code', $request->state);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('email', 'like', "%{$request->search}%")
                    ->orWhere('first_name', 'like', "%{$request->search}%")
                    ->orWhere('last_name', 'like', "%{$request->search}%");
            });
        }

        $subscribers = $query->latest()->paginate(50);
        $stats = $this->newsletterService->getSubscriberStats();

        return view('admin.newsletter.subscribers.index', compact('subscribers', 'stats'));
    }

    public function create()
    {
        return view('admin.newsletter.subscribers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:newsletter_subscribers,email',
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'state_code' => 'nullable|string|size:2',
            'source' => 'required|in:registration,checkout,website_form,import,manual',
        ]);

        $this->newsletterService->subscribe($validated['email'], $validated, $validated['source']);

        return redirect()->route('admin.newsletter.subscribers.index')
            ->with('success', 'Subscriber added successfully.');
    }

    public function edit(NewsletterSubscriber $subscriber)
    {
        return view('admin.newsletter.subscribers.edit', compact('subscriber'));
    }

    public function update(Request $request, NewsletterSubscriber $subscriber)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:newsletter_subscribers,email,'.$subscriber->id,
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'state_code' => 'nullable|string|size:2',
            'is_active' => 'boolean',
        ]);

        $subscriber->update($validated);

        return redirect()->route('admin.newsletter.subscribers.index')
            ->with('success', 'Subscriber updated successfully.');
    }

    public function destroy(NewsletterSubscriber $subscriber)
    {
        $subscriber->delete();

        return redirect()->route('admin.newsletter.subscribers.index')
            ->with('success', 'Subscriber deleted successfully.');
    }

    public function import(Request $request)
    {
        if ($request->isMethod('post')) {
            $request->validate([
                'file' => 'required|file|mimes:csv,txt|max:10240',
                'source' => 'required|in:import,manual',
            ]);

            $file = $request->file('file');
            $path = $file->storeAs('imports', 'subscribers_'.time().'.csv');
            $fullPath = storage_path('app/'.$path);

            $stats = $this->newsletterService->importFromCsv($fullPath, $request->source);

            return redirect()->route('admin.newsletter.subscribers.index')
                ->with('success', "Import complete: {$stats['imported']} imported, {$stats['updated']} updated, {$stats['skipped']} skipped.");
        }

        return view('admin.newsletter.subscribers.import');
    }

    public function export(Request $request)
    {
        $filters = $request->only(['state', 'source', 'status']);
        $filepath = $this->newsletterService->exportToCsv($filters);

        return response()->download($filepath)->deleteFileAfterSend(true);
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'subscribers' => 'required|array',
        ]);

        $subscribers = NewsletterSubscriber::whereIn('id', $request->subscribers)->get();

        foreach ($subscribers as $subscriber) {
            switch ($request->action) {
                case 'activate':
                    $subscriber->subscribe();
                    break;
                case 'deactivate':
                    $subscriber->unsubscribe();
                    break;
                case 'delete':
                    $subscriber->delete();
                    break;
            }
        }

        return redirect()->route('admin.newsletter.subscribers.index')
            ->with('success', 'Bulk action completed successfully.');
    }

    public function statistics()
    {
        $stats = $this->newsletterService->getSubscriberStats();

        return view('admin.newsletter.subscribers.statistics', compact('stats'));
    }
}
