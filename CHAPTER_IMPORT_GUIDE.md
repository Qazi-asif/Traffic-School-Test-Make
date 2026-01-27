# Chapter Import from DOCX - Quick Guide

## Setup (One Time)

1. Create the import folder:
```bash
mkdir -p storage/chapter-imports
```

2. Place your DOCX files in the folder with descriptive names:
```
storage/chapter-imports/
  ├── 01-Introduction-to-Safe-Driving.docx
  ├── 02-Traffic-Laws-and-Regulations.docx
  ├── 03-Defensive-Driving-Techniques.docx
  └── 04-Road-Signs-and-Signals.docx
```

**Tip:** Name files with numbers (01, 02, 03) to control the order.

## Usage

### Import All Chapters for a Course

```bash
php artisan chapters:import {course_id}
```

**Example:**
```bash
# Import all DOCX files from storage/chapter-imports into course ID 5
php artisan chapters:import 5
```

### Import from Custom Folder

```bash
php artisan chapters:import {course_id} --path=/path/to/docx/files
```

**Example:**
```bash
# Import from a specific folder
php artisan chapters:import 5 --path=storage/florida-course-chapters
```

### Set Starting Order Index

```bash
php artisan chapters:import {course_id} --start-order=10
```

**Example:**
```bash
# Start numbering chapters from order 10 (useful if course already has chapters)
php artisan chapters:import 5 --start-order=10
```

## What It Does

✅ Reads all .docx files from the folder
✅ Extracts text content and formatting
✅ Extracts and uploads images
✅ Creates chapters in the database
✅ Sets proper order based on filename
✅ Shows progress bar and summary

## File Naming Tips

**Good naming:**
- `01-Chapter-Title.docx` → Chapter title: "01-Chapter-Title"
- `Chapter 1 - Introduction.docx` → Chapter title: "Chapter 1 - Introduction"
- `Safe Driving Basics.docx` → Chapter title: "Safe Driving Basics"

**The filename becomes the chapter title**, so name them clearly!

## Batch Import Multiple Courses

Create separate folders for each course:

```bash
# Course 1
php artisan chapters:import 1 --path=storage/imports/course-1

# Course 2
php artisan chapters:import 2 --path=storage/imports/course-2

# Course 3
php artisan chapters:import 3 --path=storage/imports/course-3
```

## For Your 100+ Documents

1. **Organize by course:**
```
storage/chapter-imports/
  ├── florida-defensive-driving/
  │   ├── 01-chapter.docx
  │   ├── 02-chapter.docx
  │   └── ...
  ├── missouri-driver-improvement/
  │   ├── 01-chapter.docx
  │   └── ...
  └── texas-defensive-driving/
      └── ...
```

2. **Run imports:**
```bash
php artisan chapters:import 1 --path=storage/chapter-imports/florida-defensive-driving
php artisan chapters:import 2 --path=storage/chapter-imports/missouri-driver-improvement
php artisan chapters:import 3 --path=storage/chapter-imports/texas-defensive-driving
```

3. **Done!** All chapters imported in minutes instead of hours.

## Troubleshooting

**"Course not found"**
- Check the course ID exists in your database

**"No DOCX files found"**
- Verify files are in the correct folder
- Make sure files have .docx extension (not .doc)

**"Failed to import"**
- Check the error message
- Some Word features (WMF images, complex tables) might not import perfectly
- You can manually edit chapters after import

## Time Savings

- **Manual:** 5-10 minutes per chapter × 100 chapters = 8-16 hours
- **Command:** 1-2 minutes for 100 chapters
- **Savings:** ~15 hours!
