# Rich Text Editor for Chapter Content

## What's Been Added

TinyMCE rich text editor has been integrated into the chapter content field at:
`http://127.0.0.1:8000/create-course`

## Features

### 1. **Rich Text Formatting**
- Bold, Italic, Underline, Strikethrough
- Headings (H1-H6)
- Text alignment (left, center, right, justify)
- Bullet lists and numbered lists
- Indentation

### 2. **Copy/Paste from Word Documents**
- Paste content directly from Word (.docx) files
- Formatting is preserved (headings, lists, bold, italic, etc.)
- Tables are maintained
- Images are embedded

### 3. **Image Support**
- Click the image icon in toolbar to insert images
- Drag and drop images directly into the editor
- Images from Word documents are automatically embedded
- Images are stored as base64 (embedded in content)

### 4. **Tables**
- Insert and edit tables
- Tables from Word documents are preserved

### 5. **Links**
- Add hyperlinks to text
- Edit existing links

## How to Use

### Adding New Chapter Content

1. Go to `http://127.0.0.1:8000/create-course`
2. Select a course or create a new one
3. Click "Add Chapter"
4. You'll see a rich text editor instead of plain textarea

### Copying from Word Document

**Method 1: Copy/Paste**
1. Open your Word document (e.g., `FL - Chapter 1.docx`)
2. Select all content (Ctrl+A)
3. Copy (Ctrl+C)
4. Click in the TinyMCE editor
5. Paste (Ctrl+V)
6. All formatting and images will be preserved!

**Method 2: Drag & Drop Images**
1. Open your Word document
2. Right-click on an image → Save as Picture
3. Drag the image file into the TinyMCE editor

### Editing Existing Content

1. Click "Edit" on any chapter
2. The content will load in the rich text editor
3. Make your changes
4. Click "Save Chapter"

## Toolbar Buttons

- **Undo/Redo**: Undo or redo changes
- **Blocks**: Change paragraph style (Normal, H1, H2, etc.)
- **Bold/Italic/Underline**: Text formatting
- **Alignment**: Left, center, right, justify
- **Lists**: Bullet or numbered lists
- **Indent/Outdent**: Increase or decrease indentation
- **Remove Format**: Clear all formatting
- **Image**: Insert image
- **Table**: Insert table
- **Link**: Add hyperlink

## Technical Details

- **Editor**: TinyMCE 6
- **Height**: 400px (adjustable)
- **Image Storage**: Base64 embedded in content
- **Word Paste**: Preserves formatting and images
- **Auto-save**: Content is saved when you submit the form

## Troubleshooting

### Images not showing after paste
- Make sure you're using Ctrl+V (not right-click paste)
- Try saving the image from Word first, then insert it using the image button

### Formatting looks different
- TinyMCE uses standard HTML formatting
- Some Word-specific styles may be simplified

### Content not saving
- Make sure to click "Save Chapter" button
- Check browser console (F12) for any errors

## Notes

- The editor automatically saves content to the textarea when you submit
- All HTML formatting is preserved in the database
- Images are embedded as base64, so no separate image upload needed
- The editor works with existing chapters - just click Edit to modify them
