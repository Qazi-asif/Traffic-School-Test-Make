<?php

namespace App\Services;

use App\Models\BookletOrder;
use App\Models\BookletTemplate;
use App\Models\Course;
use App\Models\CourseBooklet;
use App\Models\User;
use App\Models\UserCourseEnrollment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BookletService
{
    /**
     * Create a new course booklet
     */
    public function createBooklet($course, array $data): CourseBooklet
    {
        $filePath = $this->generateMasterBooklet($course);

        $fileSize = Storage::size($filePath);

        return CourseBooklet::create([
            'course_id' => $course->id,
            'version' => $data['version'] ?? date('Y').'.1',
            'title' => $data['title'] ?? $course->title.' - Course Booklet',
            'state_code' => $data['state_code'] ?? null,
            'file_path' => $filePath,
            'page_count' => $data['page_count'] ?? 0,
            'file_size' => $fileSize,
            'is_active' => $data['is_active'] ?? true,
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * Update an existing booklet
     */
    public function updateBooklet(CourseBooklet $booklet, array $data): CourseBooklet
    {
        // If regenerating, create new PDF
        if (isset($data['regenerate']) && $data['regenerate']) {
            $filePath = $this->generateMasterBooklet($booklet->course);
            $data['file_path'] = $filePath;
            $data['file_size'] = Storage::size($filePath);
        }

        $booklet->update($data);

        return $booklet->fresh();
    }

    /**
     * Generate master booklet PDF for a course
     */
    public function generateMasterBooklet($course): string
    {
        $html = $this->compileMasterBookletHtml($course);

        $pdf = Pdf::loadHTML($html)
            ->setPaper('letter')
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-left', 15)
            ->setOption('margin-right', 15);

        $filename = 'booklets/master/'.Str::slug($course->title).'-'.time().'.pdf';
        Storage::put($filename, $pdf->output());

        return $filename;
    }

    /**
     * Create a booklet order
     */
    public function createOrder(UserCourseEnrollment $enrollment, string $format): BookletOrder
    {
        $booklet = CourseBooklet::where('course_id', $enrollment->course_id)
            ->active()
            ->latest()
            ->first();

        if (! $booklet) {
            throw new \Exception('No active booklet found for this course');
        }

        return BookletOrder::create([
            'enrollment_id' => $enrollment->id,
            'booklet_id' => $booklet->id,
            'format' => $format,
            'status' => 'pending',
            'personalization_data' => [
                'student_name' => $enrollment->user->full_name,
                'student_email' => $enrollment->user->email,
                'enrollment_date' => $enrollment->created_at->format('Y-m-d'),
            ],
        ]);
    }

    /**
     * Generate personalized booklet for a student
     */
    public function generatePersonalizedBooklet(BookletOrder $order): string
    {
        $enrollment = $order->enrollment;
        $course = $enrollment->course;
        $student = $enrollment->user;

        $html = $this->compilePersonalizedBookletHtml($course, $student, $order->personalization_data);

        $pdf = Pdf::loadHTML($html)
            ->setPaper('letter')
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-left', 15)
            ->setOption('margin-right', 15);

        $filename = 'booklets/personalized/'.
            Str::slug($course->title).'-'.
            $student->id.'-'.
            time().'.pdf';

        Storage::put($filename, $pdf->output());

        return $filename;
    }

    /**
     * Process a booklet order
     */
    public function processOrder(BookletOrder $order): void
    {
        try {
            $order->update(['status' => 'generating']);

            $filePath = $this->generatePersonalizedBooklet($order);

            $order->update([
                'status' => 'ready',
                'file_path' => $filePath,
            ]);
        } catch (\Exception $e) {
            $order->markFailed($e->getMessage());
            throw $e;
        }
    }

    /**
     * Process all pending orders
     */
    public function processPendingOrders(): int
    {
        $orders = BookletOrder::pending()->get();
        $processed = 0;

        foreach ($orders as $order) {
            try {
                $this->processOrder($order);
                $processed++;
            } catch (\Exception $e) {
                \Log::error('Failed to process booklet order '.$order->id.': '.$e->getMessage());
            }
        }

        return $processed;
    }

    /**
     * Get print queue
     */
    public function getPrintQueue(): Collection
    {
        return BookletOrder::printQueue()
            ->with(['enrollment.user', 'enrollment.course', 'booklet'])
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Get template by type
     */
    public function getTemplate(string $type): BookletTemplate
    {
        $template = BookletTemplate::active()
            ->ofType($type)
            ->first();

        if (! $template) {
            return $this->getDefaultTemplate($type);
        }

        return $template;
    }

    /**
     * Render a template with data
     */
    public function renderTemplate(BookletTemplate $template, array $data): string
    {
        return $template->render($data);
    }

    /**
     * Compile master booklet HTML
     */
    protected function compileMasterBookletHtml($course): string
    {
        $html = '';

        // Cover page
        $coverTemplate = $this->getTemplate('cover');
        $html .= $coverTemplate->render([
            'course' => $course,
            'title' => $course->title,
            'state' => $course->state_code ?? 'Multi-State',
        ]);

        $html .= '<div style="page-break-after: always;"></div>';

        // Table of contents
        $tocTemplate = $this->getTemplate('toc');
        $html .= $tocTemplate->render([
            'course' => $course,
            'chapters' => $course->chapters,
        ]);

        $html .= '<div style="page-break-after: always;"></div>';

        // Chapters
        foreach ($course->chapters as $chapter) {
            $chapterTemplate = $this->getTemplate('chapter');
            $html .= $chapterTemplate->render([
                'chapter' => $chapter,
                'course' => $course,
            ]);

            $html .= '<div style="page-break-after: always;"></div>';
        }

        // Footer
        $footerTemplate = $this->getTemplate('footer');
        $html .= $footerTemplate->render([
            'course' => $course,
            'generated_at' => now()->format('F d, Y'),
        ]);

        return $this->wrapHtml($html);
    }

    /**
     * Compile personalized booklet HTML
     */
    protected function compilePersonalizedBookletHtml($course, User $student, array $personalizationData): string
    {
        $html = '';

        // Personalized cover page
        $coverTemplate = $this->getTemplate('cover');
        $html .= $coverTemplate->render([
            'course' => $course,
            'student' => $student,
            'title' => $course->title,
            'state' => $course->state_code ?? 'Multi-State',
            'student_name' => $personalizationData['student_name'] ?? $student->full_name,
        ]);

        $html .= '<div style="page-break-after: always;"></div>';

        // Rest of the content (same as master)
        $tocTemplate = $this->getTemplate('toc');
        $html .= $tocTemplate->render([
            'course' => $course,
            'chapters' => $course->chapters,
        ]);

        $html .= '<div style="page-break-after: always;"></div>';

        foreach ($course->chapters as $chapter) {
            $chapterTemplate = $this->getTemplate('chapter');
            $html .= $chapterTemplate->render([
                'chapter' => $chapter,
                'course' => $course,
            ]);

            $html .= '<div style="page-break-after: always;"></div>';
        }

        $footerTemplate = $this->getTemplate('footer');
        $html .= $footerTemplate->render([
            'course' => $course,
            'student' => $student,
            'generated_at' => now()->format('F d, Y'),
        ]);

        return $this->wrapHtml($html);
    }

    /**
     * Wrap HTML with proper structure
     */
    protected function wrapHtml(string $content): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        h1 { font-size: 24px; margin-bottom: 20px; }
        h2 { font-size: 20px; margin-bottom: 15px; }
        h3 { font-size: 16px; margin-bottom: 10px; }
        p { margin-bottom: 10px; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    {$content}
</body>
</html>
HTML;
    }

    /**
     * Get default template for a type
     */
    protected function getDefaultTemplate(string $type): BookletTemplate
    {
        $templates = [
            'cover' => [
                'name' => 'Default Cover',
                'content' => '<div style="text-align: center; padding: 100px 20px;"><h1>{{ $title }}</h1><h2>{{ $state }}</h2>@if(isset($student_name))<p style="margin-top: 50px;">Prepared for:<br><strong>{{ $student_name }}</strong></p>@endif</div>',
            ],
            'toc' => [
                'name' => 'Default Table of Contents',
                'content' => '<h1>Table of Contents</h1><ol>@foreach($chapters as $chapter)<li>{{ $chapter->title }}</li>@endforeach</ol>',
            ],
            'chapter' => [
                'name' => 'Default Chapter',
                'content' => '<h2>{{ $chapter->title }}</h2><div>{!! $chapter->content !!}</div>',
            ],
            'footer' => [
                'name' => 'Default Footer',
                'content' => '<div style="text-align: center; padding: 20px; font-size: 12px; color: #666;"><p>{{ $course->title }}</p><p>Generated on {{ $generated_at }}</p></div>',
            ],
        ];

        $data = $templates[$type] ?? $templates['chapter'];

        return new BookletTemplate([
            'name' => $data['name'],
            'type' => $type,
            'content' => $data['content'],
            'is_active' => true,
        ]);
    }
}
