<?php

namespace Database\Seeders;

use App\Models\BookletTemplate;
use Illuminate\Database\Seeder;

class BookletTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Default Cover Page',
                'type' => 'cover',
                'content' => '<div style="text-align: center; padding: 150px 40px;">
    <h1 style="font-size: 36px; margin-bottom: 20px; color: #1e3a5f;">{{ $title }}</h1>
    <h2 style="font-size: 24px; margin-bottom: 40px; color: #4a5568;">{{ $state }}</h2>
    @if(isset($student_name))
    <div style="margin-top: 80px; padding: 20px; border-top: 2px solid #cbd5e0;">
        <p style="font-size: 14px; color: #718096; margin-bottom: 10px;">This booklet is prepared for:</p>
        <p style="font-size: 20px; font-weight: bold; color: #2d3748;">{{ $student_name }}</p>
    </div>
    @endif
</div>',
                'css' => 'body { font-family: Arial, sans-serif; }',
                'variables' => ['title', 'state', 'student_name'],
                'is_active' => true,
            ],
            [
                'name' => 'Default Table of Contents',
                'type' => 'toc',
                'content' => '<div style="padding: 40px;">
    <h1 style="font-size: 28px; margin-bottom: 30px; color: #1e3a5f; border-bottom: 3px solid #4299e1; padding-bottom: 10px;">Table of Contents</h1>
    <ol style="list-style-type: decimal; padding-left: 20px; line-height: 2;">
        @foreach($chapters as $index => $chapter)
        <li style="font-size: 16px; margin-bottom: 10px; color: #2d3748;">
            <span style="font-weight: 600;">{{ $chapter->title }}</span>
            @if($chapter->description)
            <p style="font-size: 14px; color: #718096; margin-left: 20px; margin-top: 5px;">{{ $chapter->description }}</p>
            @endif
        </li>
        @endforeach
    </ol>
</div>',
                'css' => null,
                'variables' => ['chapters'],
                'is_active' => true,
            ],
            [
                'name' => 'Default Chapter Template',
                'type' => 'chapter',
                'content' => '<div style="padding: 40px;">
    <h2 style="font-size: 24px; margin-bottom: 20px; color: #1e3a5f; border-left: 4px solid #4299e1; padding-left: 15px;">{{ $chapter->title }}</h2>
    @if($chapter->description)
    <p style="font-size: 14px; color: #718096; margin-bottom: 20px; font-style: italic;">{{ $chapter->description }}</p>
    @endif
    <div style="font-size: 14px; line-height: 1.8; color: #2d3748;">
        {!! $chapter->content !!}
    </div>
</div>',
                'css' => null,
                'variables' => ['chapter', 'course'],
                'is_active' => true,
            ],
            [
                'name' => 'Default Quiz Template',
                'type' => 'quiz',
                'content' => '<div style="padding: 40px;">
    <h2 style="font-size: 24px; margin-bottom: 20px; color: #1e3a5f;">Quiz: {{ $chapter->title }}</h2>
    <p style="font-size: 14px; color: #718096; margin-bottom: 30px;">Test your knowledge of this chapter.</p>
    @if(isset($questions))
    <div style="font-size: 14px; line-height: 1.8;">
        @foreach($questions as $index => $question)
        <div style="margin-bottom: 25px; padding: 15px; background-color: #f7fafc; border-left: 3px solid #4299e1;">
            <p style="font-weight: 600; margin-bottom: 10px;">{{ $index + 1 }}. {{ $question->question_text }}</p>
            <div style="margin-left: 20px;">
                <p>A) {{ $question->option_a }}</p>
                <p>B) {{ $question->option_b }}</p>
                <p>C) {{ $question->option_c }}</p>
                <p>D) {{ $question->option_d }}</p>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>',
                'css' => null,
                'variables' => ['chapter', 'questions'],
                'is_active' => true,
            ],
            [
                'name' => 'Default Certificate Page',
                'type' => 'certificate',
                'content' => '<div style="text-align: center; padding: 100px 40px; border: 10px double #1e3a5f;">
    <h1 style="font-size: 32px; margin-bottom: 30px; color: #1e3a5f;">Certificate of Completion</h1>
    <p style="font-size: 18px; margin-bottom: 20px;">This certifies that</p>
    <p style="font-size: 24px; font-weight: bold; margin-bottom: 30px; color: #2d3748;">{{ $student_name }}</p>
    <p style="font-size: 16px; margin-bottom: 20px;">has successfully completed</p>
    <p style="font-size: 20px; font-weight: 600; margin-bottom: 40px; color: #1e3a5f;">{{ $course->title }}</p>
    @if(isset($completion_date))
    <p style="font-size: 14px; color: #718096;">Completed on {{ $completion_date }}</p>
    @endif
</div>',
                'css' => null,
                'variables' => ['student_name', 'course', 'completion_date'],
                'is_active' => true,
            ],
            [
                'name' => 'Default Footer',
                'type' => 'footer',
                'content' => '<div style="text-align: center; padding: 30px 20px; border-top: 2px solid #e2e8f0; margin-top: 40px;">
    <p style="font-size: 14px; color: #718096; margin-bottom: 5px;">{{ $course->title }}</p>
    @if(isset($course->school_name))
    <p style="font-size: 12px; color: #a0aec0; margin-bottom: 5px;">{{ $course->school_name }}</p>
    @endif
    <p style="font-size: 12px; color: #a0aec0;">Generated on {{ $generated_at }}</p>
    @if(isset($student))
    <p style="font-size: 11px; color: #cbd5e0; margin-top: 10px;">Prepared for {{ $student->full_name }}</p>
    @endif
</div>',
                'css' => null,
                'variables' => ['course', 'generated_at', 'student'],
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            BookletTemplate::updateOrCreate(
                ['name' => $template['name'], 'type' => $template['type']],
                $template
            );
        }

        $this->command->info('Booklet templates seeded successfully!');
    }
}
