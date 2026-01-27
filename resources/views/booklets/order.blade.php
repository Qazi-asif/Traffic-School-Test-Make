<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Course Booklet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
</head>
<body>
    <x-theme-switcher />
    <x-navbar />
    
    <div class="container-fluid mt-4" style="margin-left: 280px; max-width: calc(100% - 300px);">
        <div class="mb-4">
            <h2><i class="fas fa-book"></i> Order Course Booklet</h2>
            <p class="text-muted">Select your preferred format</p>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title mb-4">Course Information</h5>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <strong>Course:</strong> {{ $enrollment->course->title ?? 'Course Not Found' }}
                    </div>
                    <div class="col-md-6 mb-2">
                        <strong>Booklet:</strong> {{ $booklet->title }}
                    </div>
                    <div class="col-md-6 mb-2">
                        <strong>Version:</strong> {{ $booklet->version }}
                    </div>
                    <div class="col-md-6 mb-2">
                        <strong>Pages:</strong> {{ $booklet->page_count }}
                    </div>
                </div>
            </div>
        </div>

        <form action="{{ route('booklets.store', $enrollment) }}" method="POST">
            @csrf

            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-4">Select Format</h5>

                    <div class="mb-3">
                        <div class="form-check p-3 border rounded mb-3">
                            <input type="radio" name="format" value="pdf_download" checked class="form-check-input" id="pdf">
                            <label class="form-check-label" for="pdf">
                                <div class="fw-bold">PDF Download</div>
                                <div class="text-muted small">Download a personalized PDF booklet instantly</div>
                                <div class="text-success small mt-1">FREE</div>
                            </label>
                        </div>

                        <div class="form-check p-3 border rounded mb-3">
                            <input type="radio" name="format" value="print_mail" class="form-check-input" id="mail">
                            <label class="form-check-label" for="mail">
                                <div class="fw-bold">Print & Mail</div>
                                <div class="text-muted small">Receive a printed booklet by mail</div>
                                <div class="text-muted small">Processing time: 3-5 business days</div>
                            </label>
                        </div>

                        <div class="form-check p-3 border rounded">
                            <input type="radio" name="format" value="print_pickup" class="form-check-input" id="pickup">
                            <label class="form-check-label" for="pickup">
                                <div class="fw-bold">Print & Pickup</div>
                                <div class="text-muted small">Pick up a printed booklet at our location</div>
                                <div class="text-muted small">Ready in 1-2 business days</div>
                            </label>
                        </div>
                    </div>

                    @error('format')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('my-enrollments') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check"></i> Place Order
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
