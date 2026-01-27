<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Survey</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
</head>
<body>
    <x-theme-switcher />
    <x-navbar />
    
    <div class="container-fluid mt-4" style="margin-left: 280px; max-width: calc(100% - 300px);">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-plus-circle"></i> Create Survey</h2>
            <a href="{{ route('admin.surveys.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Surveys
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.surveys.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Survey Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               class="form-control @error('name') is-invalid @enderror">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" rows="3" class="form-control">{{ old('description') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">State (Optional)</label>
                        <select name="state_code" class="form-select">
                            <option value="">All States</option>
                            @foreach($states as $code => $name)
                                <option value="{{ $code }}" {{ old('state_code') === $code ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Leave empty for general survey</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Course (Optional)</label>
                        <select name="course_id" class="form-select">
                            <option value="">All Courses</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->display_id }}" {{ old('course_id') == $course->display_id ? 'selected' : '' }}>
                                    {{ $course->display_title }}
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Leave empty for general survey</small>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input type="hidden" name="is_required" value="0">
                            <input type="checkbox" name="is_required" value="1" {{ old('is_required', true) ? 'checked' : '' }} class="form-check-input" id="isRequired">
                            <label class="form-check-label" for="isRequired">
                                Required (students must complete before certificate)
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active') ? 'checked' : '' }} class="form-check-input" id="isActive">
                            <label class="form-check-label" for="isActive">
                                Active
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Display Order</label>
                        <input type="number" name="display_order" value="{{ old('display_order', 0) }}" min="0" class="form-control">
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Survey
                        </button>
                        <a href="{{ route('admin.surveys.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-footer />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
