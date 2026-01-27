# Quiz Placements Setup Steps

## Current Issue
The error "View [admin.free-response-quiz-placements.index] not found" suggests the view files are created but Laravel can't find them.

## Step-by-Step Fix

### 1. Clear Laravel Caches
Run these commands in your terminal (in the project root):
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

### 2. Verify Routes Are Added
Make sure these routes are added to your `routes/web.php` file:

```php
// Add this inside the admin middleware group
Route::middleware(['auth', 'role:super-admin,admin'])->group(function () {
    
    // Quiz Placement Management Routes
    Route::resource('/admin/free-response-quiz-placements', App\Http\Controllers\Admin\FreeResponseQuizPlacementController::class)->names([
        'index' => 'admin.free-response-quiz-placements.index',
        'create' => 'admin.free-response-quiz-placements.create',
        'store' => 'admin.free-response-quiz-placements.store',
        'show' => 'admin.free-response-quiz-placements.show',
        'edit' => 'admin.free-response-quiz-placements.edit',
        'update' => 'admin.free-response-quiz-placements.update',
        'destroy' => 'admin.free-response-quiz-placements.destroy',
    ]);
    
    Route::post('/admin/free-response-quiz-placements/{id}/toggle', [App\Http\Controllers\Admin\FreeResponseQuizPlacementController::class, 'toggleActive'])
        ->name('admin.free-response-quiz-placements.toggle');
});
```

### 3. Verify Controller Exists
Make sure the file exists: `app/Http/Controllers/Admin/FreeResponseQuizPlacementController.php`

### 4. Verify View Files Exist
Check these files exist:
- `resources/views/admin/free-response-quiz-placements/index.blade.php` ✅
- `resources/views/admin/free-response-quiz-placements/create.blade.php` ✅
- `resources/views/admin/free-response-quiz-placements/edit.blade.php` ✅

### 5. Verify Database Tables
Make sure these tables exist:
- `free_response_quiz_placements`
- `free_response_questions` (with new columns)

Run the SQL from `manual_free_response_quiz_tables.sql` if needed.

### 6. Test the Route
Try accessing: `/admin/free-response-quiz-placements`

## Quick Test Commands

### Check if routes are registered:
```bash
php artisan route:list | grep "free-response-quiz-placements"
```

### Check if controller exists:
```bash
ls -la app/Http/Controllers/Admin/FreeResponseQuizPlacementController.php
```

### Check if view files exist:
```bash
ls -la resources/views/admin/free-response-quiz-placements/
```

## Alternative Access Methods

### 1. Direct URL
Try: `http://your-domain.com/admin/free-response-quiz-placements`

### 2. From Sidebar
Click "Quiz Placements" in the admin sidebar

### 3. From Navbar
Click "Quiz Placements" in the admin navbar

## If Still Not Working

### Option 1: Simple Test Route
Add this temporary route to test:
```php
Route::get('/test-quiz-placements', function() {
    return view('admin.free-response-quiz-placements.index', [
        'courses' => collect(),
        'placements' => collect(),
        'chapters' => collect(),
        'courseId' => null
    ]);
})->middleware(['auth', 'role:super-admin,admin']);
```

### Option 2: Check Laravel Logs
Look in `storage/logs/laravel.log` for detailed error messages.

### Option 3: Debug Mode
Make sure `APP_DEBUG=true` in your `.env` file to see detailed errors.

## Expected Behavior After Fix

1. **Access**: `/admin/free-response-quiz-placements` should load
2. **Display**: Shows course selection and placements table
3. **Create**: Can add new quiz placements
4. **Edit**: Can modify existing placements
5. **Toggle**: Can activate/deactivate placements

## Navigation Links Added

- **Sidebar**: "Quiz Placements" link added
- **Navbar**: "Quiz Placements" link added
- **Breadcrumbs**: Working navigation between pages

The view files are created and should work. The issue is likely caching or route registration.