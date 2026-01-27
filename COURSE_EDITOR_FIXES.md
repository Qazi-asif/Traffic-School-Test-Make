# Course Editor Image and Alignment Fixes

## Issues Identified

### 1. Copy-Paste Image Issues
**Problem**: When copying content from Word documents, images don't appear because:
- Images have `file://` URLs that browsers can't access
- TinyMCE strips these images during paste preprocessing
- Users see warnings but no clear solution

### 2. DOCX Import Text Alignment Issues
**Problem**: When importing DOCX files, text alignment and formatting is lost because:
- The `extractTextFromDocumentXml` method only extracts plain text
- Paragraph alignment properties are ignored
- Text formatting (bold, italic, indentation) is not preserved

## Solutions Implemented

### 1. Enhanced TinyMCE Configuration

**File**: `resources/views/create-course.blade.php`

**Improved Image Paste Handling**:
```javascript
// Enhanced paste preprocessing with better user guidance
paste_preprocess: function(plugin, args) {
    let content = args.content;
    imagesStrippedDuringPaste = 0;
    
    // Count and remove file:// image tags - they can't be loaded in browser
    content = content.replace(/<img[^>]*src=["']file:\/\/[^"']*["'][^>]*>/gi, function(match) {
        imagesStrippedDuringPaste++;
        // Replace with placeholder that explains the issue
        return '<div class="image-placeholder" style="background: #fff3cd; border: 1px dashed #ffeaa7; padding: 15px; margin: 10px 0; border-radius: 4px; color: #856404; text-align: center;"><i class="fas fa-image"></i> Image removed (local file path)<br><small>Use "Import from DOCX" button or paste images individually</small></div>';
    });
    
    args.content = content;
}
```

### 2. Enhanced DOCX Import with Formatting Preservation

**File**: `app/Http/Controllers/ChapterController.php`

**Updated `extractTextFromDocumentXml` method** to preserve:
- **Text Alignment**: Left, center, right, justify
- **Text Formatting**: Bold, italic, underline
- **Indentation**: Left margin, right margin, first-line indent
- **Paragraph Spacing**: Proper paragraph breaks

**Key Improvements**:
```php
// Check for paragraph properties (alignment, indentation, etc.)
$pPr = $xpath->query('.//w:pPr', $paragraph)->item(0);
if ($pPr) {
    // Extract alignment
    $jc = $xpath->query('.//w:jc', $pPr)->item(0);
    if ($jc) {
        $alignValue = $jc->getAttribute('w:val');
        switch ($alignValue) {
            case 'center': $alignment = 'text-align: center;'; break;
            case 'right': $alignment = 'text-align: right;'; break;
            case 'justify': $alignment = 'text-align: justify;'; break;
            default: $alignment = 'text-align: left;'; break;
        }
    }
    
    // Extract indentation
    $ind = $xpath->query('.//w:ind', $pPr)->item(0);
    if ($ind) {
        $leftIndent = $ind->getAttribute('w:left');
        $rightIndent = $ind->getAttribute('w:right');
        $firstLine = $ind->getAttribute('w:firstLine');
        
        // Convert twips to pixels for web display
        if ($leftIndent) {
            $leftPx = round($leftIndent / 20 * 1.33);
            $indentation .= "margin-left: {$leftPx}px;";
        }
        // ... similar for right and first-line indent
    }
}

// Process text runs with formatting
$runs = $xpath->query('.//w:r', $paragraph);
foreach ($runs as $run) {
    // Check for run properties (bold, italic, etc.)
    $rPr = $xpath->query('.//w:rPr', $run)->item(0);
    if ($rPr) {
        $isBold = $xpath->query('.//w:b', $rPr)->length > 0;
        $isItalic = $xpath->query('.//w:i', $rPr)->length > 0;
        $isUnderline = $xpath->query('.//w:u', $rPr)->length > 0;
        
        // Apply formatting
        if ($isBold) $runFormatting .= 'font-weight: bold;';
        if ($isItalic) $runFormatting .= 'font-style: italic;';
        if ($isUnderline) $runFormatting .= 'text-decoration: underline;';
    }
    
    // Wrap formatted text in spans
    if (!empty($runFormatting) && !empty($runText)) {
        $paragraphHtml .= '<span style="' . $runFormatting . '">' . htmlspecialchars($runText) . '</span>';
    }
}

// Apply paragraph-level styling
$paragraphStyle = trim($alignment . $indentation);
if (!empty($paragraphStyle)) {
    $html .= '<p style="' . $paragraphStyle . '">' . $paragraphHtml . '</p>';
} else {
    $html .= '<p>' . $paragraphHtml . '</p>';
}
```

### 3. Better Image Upload Guidance

**Enhanced User Experience**:
- Clear placeholders when images are stripped during paste
- Better error messages explaining why images don't work
- Step-by-step guidance for importing images from Word

**Updated Warning System**:
```javascript
function showImagePasteWarning(count) {
    // Enhanced toast with clear instructions
    const toast = document.createElement('div');
    toast.innerHTML = `
        <div class="toast show">
            <div class="toast-header">
                <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                <strong>Images Not Imported</strong>
            </div>
            <div class="toast-body">
                <p>${count} image(s) from Word couldn't be pasted directly.</p>
                <p><strong>To import images from Word:</strong></p>
                <ol>
                    <li>Save your Word document as .docx</li>
                    <li>Click the <strong>"Import from DOCX"</strong> button above</li>
                    <li>Select your .docx file</li>
                </ol>
                <p class="text-muted small">Or copy images individually and paste them one at a time.</p>
            </div>
        </div>
    `;
}
```

## Technical Improvements

### 1. **Formatting Preservation**
- **Alignment**: Preserves left, center, right, justify alignment from Word
- **Indentation**: Converts Word indentation to CSS margins and text-indent
- **Text Formatting**: Maintains bold, italic, underline formatting
- **Lists**: Better detection and conversion of numbered/bulleted lists

### 2. **Image Handling**
- **Supported Formats**: PNG, JPG, JPEG, GIF, WEBP work properly
- **Unsupported Formats**: WMF, EMF, EPS, TIFF, BMP show clear error messages
- **Fallback Processing**: Attempts to extract supported images even when some fail
- **Clear Guidance**: Users know exactly what to do when images fail

### 3. **Better Error Handling**
- **Graceful Degradation**: Continues processing even when some elements fail
- **Detailed Logging**: Better error tracking for debugging
- **User-Friendly Messages**: Clear explanations instead of technical errors

## User Experience Improvements

### Copy-Paste Workflow:
1. **User pastes content** from Word
2. **System detects** unsupported images
3. **Shows clear message** with step-by-step instructions
4. **Provides alternative**: Use DOCX import button

### DOCX Import Workflow:
1. **User clicks** "Import from DOCX" button
2. **System processes** document with formatting preservation
3. **Extracts supported images** and converts unsupported ones to placeholders
4. **Maintains text alignment** and formatting from original document

## Files Modified

### 1. `resources/views/create-course.blade.php`
- Enhanced TinyMCE paste preprocessing
- Better image paste warning system
- Improved user guidance messages

### 2. `app/Http/Controllers/ChapterController.php`
- Updated `extractTextFromDocumentXml()` method
- Enhanced formatting preservation
- Better image processing with alignment
- Improved error handling and user feedback

## Testing Checklist

### Copy-Paste Testing:
- [ ] Copy text from Word → Should paste with formatting
- [ ] Copy images from Word → Should show helpful warning
- [ ] Paste individual images → Should upload successfully
- [ ] Mixed content paste → Should handle gracefully

### DOCX Import Testing:
- [ ] Import simple text document → Should preserve alignment
- [ ] Import document with images → Should extract supported images
- [ ] Import with unsupported images → Should show clear placeholders
- [ ] Import with formatting → Should preserve bold, italic, alignment
- [ ] Import with lists → Should convert to proper HTML lists
- [ ] Import with tables → Should convert to HTML tables

## Benefits

1. **Better User Experience**: Clear guidance when things don't work
2. **Preserved Formatting**: Text alignment and styling maintained from Word
3. **Robust Image Handling**: Graceful handling of both supported and unsupported formats
4. **Professional Output**: Course content looks more like the original Word document
5. **Reduced Support Requests**: Users understand what to do when images don't work

## Recommendations for Users

### For Best Results:
1. **Use DOCX Import** for documents with images and complex formatting
2. **Save as DOCX** in Word before importing (not .doc)
3. **Convert Images**: Use PNG or JPG format for images in Word documents
4. **Individual Upload**: For problematic images, upload them separately using the media upload field
5. **Test Import**: Always preview the imported content before saving