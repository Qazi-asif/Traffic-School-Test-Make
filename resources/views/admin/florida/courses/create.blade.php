@extends('admin.layouts.app')

@section('title', 'Create Florida Course')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Create New Florida Course</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.florida.courses.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Courses
                        </a>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.florida.courses.store') }}">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="title">Course Title *</label>
                                    <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" 
                                           value="{{ old('title') }}" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" 
                                              rows="4">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="course_type">Course Type *</label>
                                            <select name="course_type" id="course_type" class="form-control @error('course_type') is-invalid @enderror" required>
                                                <option value="">Select Type</option>
                                                <option value="BDI" {{ old('course_type') == 'BDI' ? 'selected' : '' }}>BDI (Basic Driver Improvement)</option>
                                                <option value="ADI" {{ old('course_type') == 'ADI' ? 'selected' : '' }}>ADI (Advanced Driver Improvement)</option>
                                                <option value="TLSAE" {{ old('course_type') == 'TLSAE' ? 'selected' : '' }}>TLSAE (Traffic Law & Substance Abuse Education)</option>
                                            </select>
                                            @error('course_type')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="delivery_type">Delivery Type *</label>
                                            <select name="delivery_type" id="delivery_type" class="form-control @error('delivery_type') is-invalid @enderror" required>
                                                <option value="">Select Delivery</option>
                                                <option value="Internet" {{ old('delivery_type') == 'Internet' ? 'selected' : '' }}>Internet</option>
                                                <option value="In Person" {{ old('delivery_type') == 'In Person' ? 'selected' : '' }}>In Person</option>
                                                <option value="CD-Rom" {{ old('delivery_type') == 'CD-Rom' ? 'selected' : '' }}>CD-Rom</option>
                                                <option value="Video" {{ old('delivery_type') == 'Video' ? 'selected' : '' }}>Video</option>
                                                <option value="DVD" {{ old('delivery_type') == 'DVD' ? 'selected' : '' }}>DVD</option>
                                            </select>
                                            @error('delivery_type')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="duration">Duration (hours) *</label>
                                            <input type="number" name="duration" id="duration" class="form-control @error('duration') is-invalid @enderror" 
                                                   value="{{ old('duration') }}" min="1" required>
                                            @error('duration')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="price">Price ($) *</label>
                                            <input type="number" name="price" id="price" class="form-control @error('price') is-invalid @enderror" 
                                                   value="{{ old('price') }}" min="0" step="0.01" required>
                                            @error('price')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="passing_score">Passing Score (%) *</label>
                                            <input type="number" name="passing_score" id="passing_score" class="form-control @error('passing_score') is-invalid @enderror" 
                                                   value="{{ old('passing_score', 80) }}" min="1" max="100" required>
                                            @error('passing_score')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="certificate_type">Certificate Type</label>
                                    <input type="text" name="certificate_type" id="certificate_type" class="form-control @error('certificate_type') is-invalid @enderror" 
                                           value="{{ old('certificate_type') }}">
                                    @error('certificate_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Course Settings</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" name="is_active" id="is_active" class="custom-control-input" 
                                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="is_active">Active Course</label>
                                            </div>
                                            <small class="text-muted">Active courses are available for enrollment</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="card mt-3">
                                    <div class="card-header">
                                        <h5>Florida Compliance</h5>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-muted small">
                                            This course will be created according to Florida DHSMV requirements and will be eligible for DICDS integration.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Course
                        </button>
                        <a href="{{ route('admin.florida.courses.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection