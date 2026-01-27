<?php

namespace App\Services;

use App\Models\NewsletterSubscriber;
use Illuminate\Support\Facades\Validator;

class NewsletterService
{
    public function subscribe(string $email, array $data = [], string $source = 'website_form'): NewsletterSubscriber
    {
        $subscriber = NewsletterSubscriber::withTrashed()->where('email', $email)->first();

        if ($subscriber) {
            if ($subscriber->trashed()) {
                $subscriber->restore();
            }
            $subscriber->subscribe();
            $subscriber->update($data);
        } else {
            $subscriber = NewsletterSubscriber::create(array_merge([
                'email' => $email,
                'source' => $source,
                'is_active' => true,
                'ip_address' => request()->ip(),
            ], $data));
        }

        return $subscriber;
    }

    public function unsubscribe(string $token): bool
    {
        $subscriber = NewsletterSubscriber::where('unsubscribe_token', $token)->first();

        if ($subscriber) {
            $subscriber->unsubscribe();

            return true;
        }

        return false;
    }

    public function importFromCsv(string $filePath, string $source = 'import'): array
    {
        $stats = [
            'total' => 0,
            'imported' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        if (! file_exists($filePath)) {
            $stats['errors'][] = 'File not found';

            return $stats;
        }

        $file = fopen($filePath, 'r');
        $headers = fgetcsv($file);

        while (($row = fgetcsv($file)) !== false) {
            $stats['total']++;

            $data = array_combine($headers, $row);

            $validator = Validator::make($data, [
                'email' => 'required|email',
            ]);

            if ($validator->fails()) {
                $stats['skipped']++;
                $stats['errors'][] = "Row {$stats['total']}: Invalid email";

                continue;
            }

            try {
                $existing = NewsletterSubscriber::withTrashed()->where('email', $data['email'])->first();

                if ($existing) {
                    $existing->update([
                        'first_name' => $data['first_name'] ?? $existing->first_name,
                        'last_name' => $data['last_name'] ?? $existing->last_name,
                        'state_code' => $data['state_code'] ?? $existing->state_code,
                        'is_active' => true,
                    ]);
                    if ($existing->trashed()) {
                        $existing->restore();
                    }
                    $stats['updated']++;
                } else {
                    NewsletterSubscriber::create([
                        'email' => $data['email'],
                        'first_name' => $data['first_name'] ?? null,
                        'last_name' => $data['last_name'] ?? null,
                        'state_code' => $data['state_code'] ?? null,
                        'source' => $source,
                        'is_active' => true,
                    ]);
                    $stats['imported']++;
                }
            } catch (\Exception $e) {
                $stats['skipped']++;
                $stats['errors'][] = "Row {$stats['total']}: {$e->getMessage()}";
            }
        }

        fclose($file);

        return $stats;
    }

    public function exportToCsv(array $filters = []): string
    {
        $query = NewsletterSubscriber::query();

        if (isset($filters['state'])) {
            $query->where('state_code', $filters['state']);
        }

        if (isset($filters['source'])) {
            $query->where('source', $filters['source']);
        }

        if (isset($filters['status'])) {
            if ($filters['status'] === 'active') {
                $query->active();
            } elseif ($filters['status'] === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $subscribers = $query->get();

        $filename = 'newsletter_subscribers_'.now()->format('Y-m-d_His').'.csv';
        $filepath = storage_path('app/exports/'.$filename);

        if (! file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }

        $file = fopen($filepath, 'w');

        // Headers
        fputcsv($file, ['Email', 'First Name', 'Last Name', 'State', 'Source', 'Status', 'Subscribed At']);

        // Data
        foreach ($subscribers as $subscriber) {
            fputcsv($file, [
                $subscriber->email,
                $subscriber->first_name,
                $subscriber->last_name,
                $subscriber->state_code,
                $subscriber->source,
                $subscriber->is_active ? 'Active' : 'Inactive',
                $subscriber->subscribed_at->format('Y-m-d H:i:s'),
            ]);
        }

        fclose($file);

        return $filepath;
    }

    public function getSubscriberStats(): array
    {
        return [
            'total' => NewsletterSubscriber::count(),
            'active' => NewsletterSubscriber::active()->count(),
            'confirmed' => NewsletterSubscriber::confirmed()->count(),
            'this_month' => NewsletterSubscriber::whereMonth('subscribed_at', now()->month)->count(),
            'by_state' => NewsletterSubscriber::active()
                ->selectRaw('state_code, COUNT(*) as count')
                ->groupBy('state_code')
                ->pluck('count', 'state_code')
                ->toArray(),
            'by_source' => NewsletterSubscriber::active()
                ->selectRaw('source, COUNT(*) as count')
                ->groupBy('source')
                ->pluck('count', 'source')
                ->toArray(),
        ];
    }
}
