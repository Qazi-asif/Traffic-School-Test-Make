<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

class GenerateManualPdf extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'manual:generate {format=pdf} {--output=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate PDF or Word version of the admin user manual';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $format = $this->argument('format');
        $outputFile = $this->option('output');
        
        if (!in_array($format, ['pdf', 'word', 'docx'])) {
            $this->error('Invalid format. Use: pdf, word, or docx');
            return 1;
        }
        
        $this->info("Generating Admin User Manual ({$format})...");
        
        try {
            if ($format === 'pdf') {
                $this->generatePdf($outputFile ?: 'ADMIN_USER_MANUAL.pdf');
            } else {
                $this->generateWord($outputFile ?: 'ADMIN_USER_MANUAL.docx');
            }
            
        } catch (\Exception $e) {
            $this->error('Failed to generate manual: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
    
    private function generatePdf($outputFile)
    {
        // Load the manual PDF view
        $pdf = Pdf::loadView('admin.manual-pdf');
        
        // Set PDF options
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'dpi' => 150,
            'defaultFont' => 'DejaVu Sans',
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
        ]);
        
        // Save PDF to storage
        $pdfContent = $pdf->output();
        file_put_contents(storage_path('app/public/' . $outputFile), $pdfContent);
        
        $this->info("PDF generated successfully: storage/app/public/{$outputFile}");
        $this->info("You can also access it via: /storage/{$outputFile}");
    }
    
    private function generateWord($outputFile)
    {
        $controller = new \App\Http\Controllers\Admin\ManualController();
        
        // Create PHPWord instance
        $phpWord = new PhpWord();
        
        // Set document properties
        $properties = $phpWord->getDocInfo();
        $properties->setCreator('Traffic School Platform');
        $properties->setCompany('Traffic School Platform');
        $properties->setTitle('Admin User Manual');
        $properties->setDescription('Comprehensive guide for system administration');
        $properties->setSubject('Admin Manual');
        
        // Define styles
        $phpWord->addTitleStyle(1, ['name' => 'Arial', 'size' => 20, 'bold' => true, 'color' => '2c3e50']);
        $phpWord->addTitleStyle(2, ['name' => 'Arial', 'size' => 16, 'bold' => true, 'color' => '34495e']);
        $phpWord->addTitleStyle(3, ['name' => 'Arial', 'size' => 14, 'bold' => true, 'color' => '7f8c8d']);
        
        $phpWord->addParagraphStyle('normalText', [
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT,
            'spaceAfter' => 120,
            'lineHeight' => 1.15
        ]);
        
        $phpWord->addFontStyle('boldText', ['bold' => true, 'name' => 'Arial', 'size' => 11]);
        $phpWord->addFontStyle('normalFont', ['name' => 'Arial', 'size' => 11]);
        
        // Create section
        $section = $phpWord->addSection([
            'marginTop' => 1440,    // 1 inch
            'marginBottom' => 1440, // 1 inch
            'marginLeft' => 1440,   // 1 inch
            'marginRight' => 1440,  // 1 inch
        ]);
        
        // Cover Page
        $section->addText('Traffic School Platform', ['name' => 'Arial', 'size' => 28, 'bold' => true, 'color' => '2c3e50'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $section->addTextBreak(1);
        $section->addText('Administrator User Manual', ['name' => 'Arial', 'size' => 18, 'color' => '7f8c8d'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $section->addTextBreak(3);
        $section->addText('Comprehensive Guide for System Administration', ['name' => 'Arial', 'size' => 12, 'color' => '95a5a6'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $section->addText('Version 1.0 | December 2025', ['name' => 'Arial', 'size' => 12, 'color' => '95a5a6'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        
        $section->addPageBreak();
        
        // Add basic content structure
        $section->addTitle('Table of Contents', 1);
        $section->addTextBreak(1);
        
        $tocItems = [
            '1. Getting Started',
            '2. Course Management', 
            '3. Chapter Management',
            '4. Question Management',
            '5. Student Management',
            '6. State Integration Management',
            '7. Payment & Revenue Management',
            '8. Certificate Management',
            '9. System Administration',
            '10. Troubleshooting'
        ];
        
        foreach ($tocItems as $item) {
            $section->addText($item, 'normalFont', 'normalText');
        }
        
        $section->addPageBreak();
        
        // Add main sections (simplified for command line)
        $section->addTitle('Getting Started', 1);
        $section->addText('This manual provides comprehensive guidance for administering the Traffic School Platform.', 'normalFont', 'normalText');
        
        $section->addTitle('Course Management', 1);
        $section->addText('Learn how to create, edit, and manage courses in the system.', 'normalFont', 'normalText');
        
        // Save Word document
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save(storage_path('app/public/' . $outputFile));
        
        $this->info("Word document generated successfully: storage/app/public/{$outputFile}");
        $this->info("You can also access it via: /storage/{$outputFile}");
    }
}
