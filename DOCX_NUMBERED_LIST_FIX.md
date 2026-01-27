# DOCX Numbered List Import Fix

## Problem
When importing DOCX files, numbered lists were being imported as bullet lists (`<ul>`) instead of ordered lists (`<ol>`). Bullet lists were working correctly.

## Root Cause
The previous implementation relied on `getListType()` and `getNumStyle()` methods which returned `null` or generic style names like "PHPWordList1", "PHPWordList2", etc. These methods don't provide enough information to distinguish between numbered and bullet lists.

## Solution
Access the PHPWord document's numbering definitions to check the actual format type of each list item.

### Changes Made

1. **Updated `processDocxElements()` method signature** to accept the `$phpWord` document object:
   ```php
   private function processDocxElements($elements, &$imageCount, $isInline = false, &$unsupportedImages = [], $phpWord = null)
   ```

2. **Added numbering format detection logic**:
   - Get the numbering ID from the list style (`getNumId()`)
   - Access the document's numbering definitions (`$phpWord->getNumbering()`)
   - Get the abstract numbering for the specific ID
   - Check the format at the current depth level
   - If format is NOT 'bullet', it's a numbered list (`<ol>`)
   - If format is 'bullet', it's a bullet list (`<ul>`)

3. **Updated all calls to `processDocxElements()`** to pass the `$phpWord` parameter:
   - Main section processing
   - Recursive calls for ListItemRun elements
   - Recursive calls for TextRun elements
   - Table cell processing
   - Title text processing
   - Nested element processing

### Format Types
According to PHPWord documentation, numbering formats include:
- **Numbered formats**: `decimal`, `upperRoman`, `lowerRoman`, `upperLetter`, `lowerLetter`
- **Bullet format**: `bullet`

### Detection Flow
```
1. Get list element
2. Get list style from element
3. Get numbering ID from style
4. Access document numbering definitions
5. Get abstract numbering for ID
6. Get level definition for current depth
7. Check format:
   - If format !== 'bullet' → <ol>
   - If format === 'bullet' → <ul>
8. Fallback to previous detection methods if numbering access fails
```

### Logging Added
- `numId`: The numbering definition ID
- `depth`: The list nesting level
- `format`: The detected format type (decimal, bullet, etc.)

## Testing
1. Clear all caches:
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   ```

2. Import a DOCX file with:
   - Numbered lists (1, 2, 3...)
   - Bullet lists (•)
   - Mixed lists

3. Check Laravel log for:
   ```
   Numbering format detected {"format":"decimal","depth":0}
   Found List Element {"listType":"ol"}
   ```

## Expected Results
- Numbered lists should now import as `<ol><li>...</li></ol>`
- Bullet lists should continue to import as `<ul><li>...</li></ul>`
- Mixed documents should correctly distinguish between both types

## Files Modified
- `app/Http/Controllers/ChapterController.php`
  - Updated `processDocxElements()` method signature
  - Added numbering format detection logic
  - Updated all recursive calls to pass `$phpWord` parameter

## Next Steps
If numbered lists still don't work:
1. Check the Laravel log for "Numbering format detected" messages
2. Verify the format value being returned
3. Check if `$phpWord->getNumbering()` is accessible
4. May need to explore alternative PHPWord methods for accessing numbering definitions
