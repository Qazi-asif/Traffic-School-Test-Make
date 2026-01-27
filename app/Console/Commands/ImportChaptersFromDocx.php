<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Chapter;
use App\Models\Course;
use PhpOffice\PhpWord\IOFactory;

class ImportChaptersFromDocx extends Command
{
    protected $signature = 'chapters:import 
                            {course_id : The ID of the course}
                            {--path= : Path to folder containing DOCX files (default: storage/chapter-imports)}
                            {--start-order=1 : Starting order index for chapters}';

    protected $description = 'Import chapters from DOCX files in a folder';

    public function handle()
    {
        $courseId = $this->argument('course_id');
        $path = $this->option('path') ?: storage_path('chapter-imports');
        $startOrder = (int) $this->option('start-order');

        // Verify course exists - check both tables
        $course = Course::find($courseId);
        $courseTable = 'courses';
        
        if (!$course) {
            // Try florida_courses table
            $course = \DB::table('florida_courses')->where('id', $courseId)->first();
            if ($course) {
                $courseTable = 'florida_courses';
            }
        }
        
        if (!$course) {
            $this->error("Course with ID {$courseId} not found in courses or florida_courses tables!");
            return 1;
        }

        $this->info("Importing chapters for course: {$course->title}");
        $this->info("Course table: {$courseTable}");
        $this->info("Looking for DOCX files in: {$path}");

        // Check if directory exists
        if (!is_dir($path)) {
            $this->error("Directory not found: {$path}");
            $this->info("Creating directory...");
            mkdir($path, 0755, true);
            $this->warn("Please place your DOCX files in: {$path}");
            return 1;
        }

        // Get all DOCX files
        $files = glob($path . '/*.docx');
        
        if (empty($files)) {
            $this->warn("No DOCX files found in {$path}");
            return 1;
        }

        $this->info("Found " . count($files) . " DOCX files");
        
        // Sort files alphabetically
        sort($files);

        $bar = $this->output->createProgressBar(count($files));
        $bar->start();

        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        foreach ($files as $index => $file) {
            $filename = basename($file);
            
            try {
                // Extract chapter title from filename (remove .docx extension)
                $chapterTitle = pathinfo($filename, PATHINFO_FILENAME);
                
                // Load DOCX and convert to HTML
                $html = $this->convertDocxToHtml($file);
                
                // Create chapter
                Chapter::create([
                    'course_id' => $courseId,
                    'course_table' => $courseTable,
                    'title' => $chapterTitle,
                    'content' => $html,
                    'order_index' => $startOrder + $index,
                    'duration' => 30, // Default 30 minutes
                    'required_min_time' => 30,
                    'is_active' => true,
                ]);
                
                $successCount++;
            } catch (\Exception $e) {
                $errorCount++;
                $errors[] = [
                    'file' => $filename,
                    'error' => $e->getMessage()
                ];
            }
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Summary
        $this->info("Import completed!");
        $this->table(
            ['Status', 'Count'],
            [
                ['Success', $successCount],
                ['Errors', $errorCount],
                ['Total', count($files)]
            ]
        );

        // Show errors if any
        if (!empty($errors)) {
            $this->newLine();
            $this->error("Errors encountered:");
            foreach ($errors as $error) {
                $this->line("  • {$error['file']}: {$error['error']}");
            }
        }

        return 0;
    }

    private function convertDocxToHtml($filePath)
    {
        $phpWord = IOFactory::load($filePath);
        $html = '';
        $imageCount = 0;

        // Ensure upload directory exists
        $uploadDir = storage_path('app/public/course-media');
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                $html .= $this->processElement($element, $imageCount);
            }
        }

        return $html;
    }

    private function processElement($element, &$imageCount)
    {
        $html = '';
        $elementClass = get_class($element);

        switch ($elementClass) {
            case 'PhpOffice\PhpWord\Element\TextRun':
                $html .= '<p>';
                foreach ($element->getElements() as $childElement) {
                    $html .= $this->processElement($childElement, $imageCount);
                }
                $html .= '</p>';
                break;

            case 'PhpOffice\PhpWord\Element\Text':
                $text = htmlspecialchars($element->getText(), ENT_QUOTES, 'UTF-8');
                $fontStyle = $element->getFontStyle();
                
                if ($fontStyle) {
                    $style = '';
                    if ($fontStyle->isBold()) {
                        $text = '<strong>' . $text . '</strong>';
                    }
                    if ($fontStyle->isItalic()) {
                        $text = '<em>' . $text . '</em>';
                    }
                }
                
                $html .= $text;
                break;

            case 'PhpOffice\PhpWord\Element\Image':
                try {
                    $imageSource = $element->getSource();
                    $imageData = file_get_contents($imageSource);
                    
                    $imageCount++;
                    $extension = pathinfo($imageSource, PATHINFO_EXTENSION) ?: 'png';
                    $filename = 'chapter_import_' . time() . '_' . $imageCount . '.' . $extension;
                    
                    $uploadPath = storage_path('app/public/course-media/' . $filename);
                    file_put_contents($uploadPath, $imageData);
                    
                    $fileUrl = '/storage/course-media/' . $filename;
                    $html .= '<div class="chapter-media"><img src="' . $fileUrl . '" alt="Chapter image" class="img-fluid" style="max-width: 100%;"></div>';
                } catch (\Exception $e) {
                    // Skip problematic images
                    $this->warn("Skipped image: " . $e->getMessage());
                }
                break;

            case 'PhpOffice\PhpWord\Element\Table':
                $html .= '<table class="table table-bordered">';
                foreach ($element->getRows() as $row) {
                    $html .= '<tr>';
                    foreach ($row->getCells() as $cell) {
                        $html .= '<td>';
                        foreach ($cell->getElements() as $cellElement) {
                            $html .= $this->processElement($cellElement, $imageCount);
                        }
                        $html .= '</td>';
                    }
                    $html .= '</tr>';
                }
                $html .= '</table>';
                break;

            case 'PhpOffice\PhpWord\Element\ListItem':
                $html .= '<li>' . htmlspecialchars($element->getTextObject()->getText(), ENT_QUOTES, 'UTF-8') . '</li>';
                break;
        }

        return $html;
    }
}
