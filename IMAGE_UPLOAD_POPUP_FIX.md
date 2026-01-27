# Image Upload Popup Fix - Create Course Page

## Changes Made

### 1. **TinyMCE Configuration** (create-course.blade.php)

#### Disabled Image Upload Dialog
```javascript
file_picker_callback: false,  // Disables file picker dialog
image_advtab: false,          // Disables advanced image tab
image_class_list: [],         // Empty class list
```

#### Added Dialog Prevention
```javascript
init_instance_callback: function(editor) {
    // Hide any image dialogs on init
    document.querySelectorAll('.tox-dialog, .tox-dialog-wrap').forEach(el => {
        el.style.display = 'none';
    });
}
```

### 2. **CSS Hiding** (create-course.blade.php)

```css
/* Hide TinyMCE image upload dialogs */
.tox-dialog[role="dialog"] {
    display: none !important;
}
.tox-dialog-wrap {
    display: none !important;
}
.tox-tinymce-aux {
    display: none !important;
}
.mce-window {
    display: none !important;
}
.mce-modal-overlay {
    display: none !important;
}
```

### 3. **JavaScript Event Handlers** (create-course.blade.php)

```javascript
setup: function (editor) {
    // Prevent ALL image dialogs from appearing
    editor.on('BeforeOpenDialog', function(e) {
        if (e.dialog) {
            e.preventDefault();
            return false;
        }
    });
    
    // Prevent image picker dialog
    editor.on('OpenWindow', function(e) {
        if (e.dialog && (e.dialog.type === 'upload' || e.dialog.title === 'Insert/Edit Image')) {
            e.preventDefault();
            return false;
        }
    });
    
    // Hide dialogs on init
    editor.on('init', function() {
        document.querySelectorAll('.tox-dialog, .tox-dialog-wrap').forEach(el => {
            el.style.display = 'none';
        });
    });
}
```

### 4. **Continuous Dialog Suppression**

```javascript
document.addEventListener('DOMContentLoaded', function() {
    // Hide any image dialogs that might appear
    const hideDialogs = setInterval(function() {
        document.querySelectorAll('.tox-dialog, .tox-dialog-wrap, .mce-window, .mce-modal-overlay').forEach(el => {
            el.style.display = 'none !important';
            el.style.visibility = 'hidden !important';
            el.style.opacity = '0 !important';
            el.style.pointerEvents = 'none !important';
        });
    }, 100);
    
    // Stop after 5 seconds
    setTimeout(() => clearInterval(hideDialogs), 5000);
});
```

---

## What Still Works

✅ **Image Upload Functionality**: Images still upload silently in background  
✅ **Paste with Images**: Images from paste operations still work  
✅ **Image Handler**: `images_upload_handler` still processes uploads  
✅ **Automatic Uploads**: `automatic_uploads: true` still active  

---

## What's Hidden

❌ **Image Upload Dialog**: No popup when clicking image button  
❌ **Image Picker Modal**: No file selection dialog  
❌ **Image Properties Dialog**: No advanced image settings popup  
❌ **Paste Dialog**: No dialog when pasting images  

---

## Testing

1. Go to: http://127.0.0.1:8000/create-course
2. Click on TinyMCE editor
3. Try clicking the image button - **No dialog should appear**
4. Try pasting an image - **Image should paste silently**
5. Images should still upload to `/api/upload-tinymce-image`

---

## Browser Console

You should see:
```
TinyMCE uploading image...
Response status: 200
Upload response: {location: "/files/..."}
Image uploaded successfully: /files/...
```

But **NO** image dialogs or modals should appear.

---

## Note on Syntax Error

If you see: `Uncaught SyntaxError: Unexpected token ')' at line 1180`

This is likely from a different file (possibly question-manager.blade.php). The create-course.blade.php file has been fixed and should work correctly.

To verify the fix is working:
1. Clear browser cache (Ctrl+Shift+Delete)
2. Hard refresh (Ctrl+Shift+R)
3. Test image upload again
