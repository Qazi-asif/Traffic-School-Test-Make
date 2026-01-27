<?php

require_once 'vendor/autoload.php';

echo "=== DEEP SEARCH FOR ALL QUESTIONS ===\n\n";

$docxFile = 'finalexam.docx';

try {
    $zip = new ZipArchive();
    if ($zip->open($docxFile) === TRUE) {
        
        // List all files in the DOCX
        echo "📁 Files in DOCX archive:\n";
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            echo "  {$filename}\n";
        }
        echo "\n";
        
        // Check main document
        $documentXml = $zip->getFromName('word/document.xml');
        
        // Check if there are other document parts
        $otherDocs = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            if (preg_match('/word\/document\d*\.xml/', $filename) && $filename !== 'word/document.xml') {
                $otherDocs[] = $filename;
            }
        }
        
        if (!empty($otherDocs)) {
            echo "📄 Found additional document parts:\n";
            foreach ($otherDocs as $doc) {
                echo "  {$doc}\n";
            }
        }
        
        $zip->close();
        
        // Parse main document more thoroughly
        $dom = new DOMDocument();
        $dom->loadXML($documentXml);
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
        
        // Get raw text to search for any number patterns
        $allText = strip_tags($documentXml);
        
        echo "📊 Raw document analysis:\n";
        echo "Total characters: " . strlen($allText) . "\n";
        
        // Search for any 3-digit numbers that might be questions
        preg_match_all('/\b([4-5]\d{2})\b/', $allText, $matches);
        $highNumbers = array_unique($matches[1]);
        sort($highNumbers);
        
        echo "Found 3-digit numbers in 400-500 range: " . implode(', ', $highNumbers) . "\n\n";
        
        // Look for specific patterns around these numbers
        foreach ($highNumbers as $num) {
            if ($num >= 496 && $num <= 500) {
                echo "🔍 Searching for question {$num}:\n";
                
                // Multiple search patterns
                $patterns = [
                    $num . '.)',
                    $num . ' .)',
                    $num . '.',
                    'Question ' . $num,
                    $num . ')'
                ];
                
                foreach ($patterns as $pattern) {
                    $pos = strpos($allText, $pattern);
                    if ($pos !== false) {
                        $context = substr($allText, max(0, $pos - 50), 200);
                        echo "  Found '{$pattern}' at position {$pos}\n";
                        echo "  Context: " . trim($context) . "\n";
                        break;
                    }
                }
                echo "\n";
            }
        }
        
        // Check if the document might be truncated
        echo "📋 Document ending analysis:\n";
        $endText = substr($allText, -1000);
        echo "Last 200 characters: " . substr($endText, -200) . "\n\n";
        
        // Look for the highest numbered question we can find
        preg_match_all('/(\d+)\.\)/', $allText, $questionMatches);
        $questionNumbers = array_map('intval', $questionMatches[1]);
        $maxQuestion = max($questionNumbers);
        
        echo "🎯 Highest numbered question found: {$maxQuestion}\n";
        
        // Check around question 495 to see what comes after
        $pattern495 = '/495\.\)(.*?)$/s';
        if (preg_match($pattern495, $allText, $match)) {
            echo "Content after question 495:\n";
            echo substr($match[1], 0, 500) . "\n";
        }
        
    } else {
        throw new Exception("Could not open DOCX file");
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}