<?php
/**
 * Test script to verify DOCX list import functionality
 * 
 * This script helps test the list detection logic without needing to go through the full UI.
 * 
 * Usage:
 * 1. Place a test DOCX file with lists in the same directory as this script
 * 2. Update the $testFile variable below with your filename
 * 3. Run: php test-docx-list-import.php
 */

require __DIR__.'/vendor/autoload.php';

// Test file - update this with your test DOCX filename
$testFile = 'test-lists.docx';

if (!file_exists($testFile)) {
    echo "Error: Test file '$testFile' not found.\n";
    echo "Please create a DOCX file with numbered and bullet lists and update the \$testFile variable.\n";
    exit(1);
}

echo "Loading DOCX file: $testFile\n";
echo str_repeat("=", 80) . "\n\n";

try {
    $phpWord = \PhpOffice\PhpWord\IOFactory::load($testFile);
    
    foreach ($phpWord->getSections() as $sectionIndex => $section) {
        echo "Section $sectionIndex:\n";
        echo str_repeat("-", 80) . "\n";
        
        foreach ($section->getElements() as $elementIndex => $element) {
            $className = get_class($element);
            echo "Element $elementIndex: $className\n";
            
            // Check if it's a ListItem or ListItemRun
            if ($element instanceof \PhpOffice\PhpWord\Element\ListItem || 
                get_class($element) === 'PhpOffice\\PhpWord\\Element\\ListItemRun') {
                
                echo "  → This is a LIST ITEM!\n";
                
                // Get the style
                $style = method_exists($element, 'getStyle') ? $element->getStyle() : null;
                
                if ($style && is_object($style)) {
                    // Check getListType()
                    if (method_exists($style, 'getListType')) {
                        $listTypeValue = $style->getListType();
                        echo "  → getListType() = $listTypeValue\n";
                        
                        // Determine if numbered or bullet
                        if (in_array($listTypeValue, [7, 8, 9])) {
                            echo "  → DETECTED AS: Numbered List (ol)\n";
                        } else {
                            echo "  → DETECTED AS: Bullet List (ul)\n";
                        }
                    }
                    
                    // Check getNumStyle()
                    if (method_exists($style, 'getNumStyle')) {
                        $numStyle = $style->getNumStyle();
                        echo "  → getNumStyle() = " . ($numStyle ?: 'null') . "\n";
                    }
                    
                    // Check getNumId()
                    if (method_exists($style, 'getNumId')) {
                        $numId = $style->getNumId();
                        echo "  → getNumId() = $numId\n";
                    }
                }
                
                // Get the text
                if (get_class($element) === 'PhpOffice\\PhpWord\\Element\\ListItemRun') {
                    if (method_exists($element, 'getElements')) {
                        $text = '';
                        foreach ($element->getElements() as $subElement) {
                            if (method_exists($subElement, 'getText')) {
                                $text .= $subElement->getText();
                            }
                        }
                        echo "  → Text: " . substr($text, 0, 100) . "\n";
                    }
                } else {
                    $textObj = $element->getTextObject();
                    if ($textObj && method_exists($textObj, 'getText')) {
                        echo "  → Text: " . substr($textObj->getText(), 0, 100) . "\n";
                    }
                }
                
                echo "\n";
            }
        }
        echo "\n";
    }
    
    echo str_repeat("=", 80) . "\n";
    echo "Test completed successfully!\n";
    echo "\nLegend:\n";
    echo "  TYPE_NUMBER = 7 (Numbered list)\n";
    echo "  TYPE_NUMBER_NESTED = 8 (Nested numbered list)\n";
    echo "  TYPE_ALPHANUM = 9 (Alphabetic list)\n";
    echo "  TYPE_BULLET_FILLED = 3 (Bullet list)\n";
    echo "  TYPE_BULLET_EMPTY = 5 (Empty bullet list)\n";
    echo "  TYPE_SQUARE_FILLED = 1 (Square bullet list)\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
