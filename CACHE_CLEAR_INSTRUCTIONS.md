# Clear Browser Cache to See Quiz Fix

## The Problem

The duplicate letters fix (A. A. → A.) has been applied to the code, but you're seeing the old version due to browser caching.

## Solution: Clear Browser Cache

### Method 1: Hard Refresh (Recommended)

**Windows/Linux:**

- Press `Ctrl + Shift + R`
- OR Press `Ctrl + F5`

**Mac:**

- Press `Cmd + Shift + R`
- OR Press `Cmd + Option + R`

### Method 2: Clear Cache Manually

**Chrome:**

1. Press `Ctrl + Shift + Delete` (Windows) or `Cmd + Shift + Delete` (Mac)
2. Select "Cached images and files"
3. Click "Clear data"
4. Reload the page

**Firefox:**

1. Press `Ctrl + Shift + Delete` (Windows) or `Cmd + Shift + Delete` (Mac)
2. Select "Cache"
3. Click "Clear Now"
4. Reload the page

**Edge:**

1. Press `Ctrl + Shift + Delete`
2. Select "Cached images and files"
3. Click "Clear now"
4. Reload the page

### Method 3: Open in Incognito/Private Mode

- **Chrome**: `Ctrl + Shift + N` (Windows) or `Cmd + Shift + N` (Mac)
- **Firefox**: `Ctrl + Shift + P` (Windows) or `Cmd + Shift + P` (Mac)
- **Edge**: `Ctrl + Shift + N`

Then navigate to your course and the quiz should show correctly.

## Verify the Fix

After clearing cache, the quiz options should display as:

- ✅ A. alternative routes (NOT "A. A. alternative routes")
- ✅ B. exits (NOT "B. B. exits")
- ✅ C. Side streets (NOT "C. C. Side streets")

## If Still Not Working

If you still see duplicates after clearing cache:

1. Check browser console for JavaScript errors (F12 → Console tab)
2. Verify you're on the correct page (not a cached version)
3. Try a different browser
4. Contact support with a screenshot

## Technical Details

The fix removes duplicate letter prefixes using this code:

```javascript
const cleanOpt = opt
  .toString()
  .replace(/^[A-E]\.\s*/i, "")
  .trim();
```

This regex pattern `/^[A-E]\.\s*/i` removes any "A. ", "B. ", "C. ", "D. ", or "E. " prefix from the beginning of the option text before displaying it.
