<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Enrollment #{{ $enrollment->id }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
    <style>
        .form-section {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: var(--transition);
        }
        .form-section:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        .form-section h5 {
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid var(--accent);
            color: var(--text-primary);
            font-weight: 600;
        }
        .form-section h5 i {
            color: var(--accent);
            margin-right: 8px;
        }
        .page-header {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
        }
        .page-header h2 {
            color: var(--text-primary);
            margin: 0;
        }
        .btn-outline-primary {
            background: transparent;
            border: 2px solid var(--accent);
            color: var(--accent);
        }
        .btn-outline-primary:hover {
            background: var(--accent);
            color: var(--text-primary);
        }
        .btn-outline-secondary {
            background: transparent;
            border: 2px solid var(--border);
            color: var(--text-secondary);
        }
        .btn-outline-secondary:hover {
            background: var(--bg-secondary);
            color: var(--text-primary);
        }
        .btn-outline-info {
            background: transparent;
            border: 2px solid #3b82f6;
            color: #3b82f6;
        }
        .btn-outline-info:hover {
            background: #3b82f6;
            color: white;
        }
        .btn-info {
            background: #3b82f6;
            color: white;
            border: none;
        }
        .btn-info:hover {
            background: #2563eb;
        }
        .btn-warning {
            background: #f59e0b;
            color: white;
            border: none;
        }
        .btn-warning:hover {
            background: #d97706;
        }
        .table-responsive {
            background: var(--bg-card);
            border-radius: 12px;
            overflow: hidden;
        }
        .dropdown-list {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: var(--bg-card);
            color: var(--text-primary);
            border: 1px solid var(--border);
            border-top: none;
            max-height: 300px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .dropdown-item {
            padding: 10px;
            cursor: pointer;
            border-bottom: 1px solid var(--border);
            color: var(--text-primary);
            background: var(--bg-card);
        }
        .dropdown-item:hover {
            background: var(--hover);
        }
    </style>
</head>
<body>
    <x-theme-switcher />
    <x-navbar />
    
    <div class="container-fluid mt-4" style="margin-left: 280px; max-width: calc(100% - 300px);">
        <div class="page-header d-flex justify-content-between align-items-center">
            <h2><i class="fas fa-edit"></i> Edit Enrollment #{{ $enrollment->id }}</h2>
            <a href="/admin/enrollments" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Enrollments
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.enrollments.update', $enrollment->id) }}">
            @csrf
            
            <!-- Declaration -->
            <div class="form-section">
                <h5><i class="fas fa-file-contract"></i> Declaration</h5>
                <div class="alert alert-warning">
                    <strong>DECLARATION:</strong> I certify under penalty of perjury under the laws of the State of California 
                    that the information contained on this form is true and correct, and that 340 minutes of actual instruction 
                    time was completed by each student listed above. I further certify under penalty of perjury that the DMV 
                    approved lesson plan was followed for the duration of the class. (Perjury is punishable by imprisonment, fine, or both.)
                </div>
            </div>

            <!-- Student Information -->
            <div class="form-section">
                <h5><i class="fas fa-user"></i> Student Information</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" 
                               value="{{ $enrollment->user->email }}" 
                               placeholder="{{ $enrollment->user->email ? '' : 'Not Available' }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Full Name</label>
                        <div class="input-group">
                            <input type="text" name="first_name" class="form-control" placeholder="{{ $enrollment->user->first_name ?: 'Not Available' }}" 
                                   value="{{ $enrollment->user->first_name }}" required>
                            <input type="text" name="last_name" class="form-control" placeholder="{{ $enrollment->user->last_name ?: 'Not Available' }}" 
                                   value="{{ $enrollment->user->last_name }}" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Language Type</label>
                        <select name="language_type" class="form-control">
                            <option value="English">English</option>
                            <option value="Spanish">Spanish</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Gender</label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="gender" value="male" {{ strtolower($enrollment->user->gender) == 'male' ? 'checked' : '' }}>
                                <label class="form-check-label">Male</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="gender" value="female" {{ strtolower($enrollment->user->gender) == 'female' ? 'checked' : '' }}>
                                <label class="form-check-label">Female</label>
                            </div>
                            @if(!$enrollment->user->gender)
                                <small class="text-muted d-block">Not Available</small>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Birthday</label>
                        <div class="input-group">
                            <input type="number" name="birth_month" class="form-control" placeholder="{{ $enrollment->user->birth_month ?: 'MM' }}" 
                                   min="1" max="12" value="{{ $enrollment->user->birth_month }}">
                            <input type="number" name="birth_day" class="form-control" placeholder="{{ $enrollment->user->birth_day ?: 'DD' }}" 
                                   min="1" max="31" value="{{ $enrollment->user->birth_day }}">
                            <input type="number" name="birth_year" class="form-control" placeholder="{{ $enrollment->user->birth_year ?: 'YYYY' }}" 
                                   min="1900" max="2024" value="{{ $enrollment->user->birth_year }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Course & Dates -->
            <div class="form-section">
                <h5><i class="fas fa-calendar"></i> Course & Dates</h5>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Start Date</label>
                        <input type="datetime-local" name="start_date" class="form-control" 
                               value="{{ $enrollment->started_at ? $enrollment->started_at->format('Y-m-d\TH:i') : ($enrollment->enrolled_at ? $enrollment->enrolled_at->format('Y-m-d\TH:i') : '') }}"
                               placeholder="Not Available">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Finish Date</label>
                        <input type="datetime-local" name="finish_date" class="form-control" 
                               value="{{ $enrollment->completed_at ? $enrollment->completed_at->format('Y-m-d\TH:i') : '' }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Execute Date</label>
                        <input type="datetime-local" name="execute_date" class="form-control" 
                               value="{{ $enrollment->completed_at ? $enrollment->completed_at->format('Y-m-d\TH:i') : '' }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Course</label>
                        <select name="course_id" class="form-control">
                            <optgroup label="Florida Courses">
                                @foreach($floridaCourses as $course)
                                    <option value="{{ $course->id }}" {{ $enrollment->course_id == $course->id ? 'selected' : '' }}>
                                        {{ $course->title }}
                                    </option>
                                @endforeach
                            </optgroup>
                            <optgroup label="Other Courses">
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}" {{ $enrollment->course_id == $course->id ? 'selected' : '' }}>
                                        {{ $course->title }} ({{ $course->state }})
                                    </option>
                                @endforeach
                            </optgroup>
                        </select>
                    </div>
                </div>
            </div>


            <!-- Driver License Information -->
            <div class="form-section">
                <h5><i class="fas fa-id-card"></i> Driver License Information</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Driver License Number</label>
                        <input type="text" name="driver_license" class="form-control" 
                               value="{{ $enrollment->user->driver_license }}"
                               placeholder="{{ $enrollment->user->driver_license ?: 'Not Available' }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Driver License Class</label>
                        <select name="license_class" class="form-control">
                            <option value="">Select Class</option>
                            <option value="Class A" {{ $enrollment->user->license_class == 'Class A' ? 'selected' : '' }}>Class A</option>
                            <option value="Class B" {{ $enrollment->user->license_class == 'Class B' ? 'selected' : '' }}>Class B</option>
                            <option value="Class C - Basic" {{ $enrollment->user->license_class == 'Class C - Basic' ? 'selected' : '' }}>Class C – Basic</option>
                            <option value="Class C - Commercial" {{ $enrollment->user->license_class == 'Class C - Commercial' ? 'selected' : '' }}>Class C – Commercial</option>
                            <option value="Class M1" {{ $enrollment->user->license_class == 'Class M1' ? 'selected' : '' }}>Class M1</option>
                            <option value="Class M2" {{ $enrollment->user->license_class == 'Class M2' ? 'selected' : '' }}>Class M2</option>
                            <option value="Out of State" {{ $enrollment->user->license_class == 'Out of State' ? 'selected' : '' }}>Out of State</option>
                            <option value="Other" {{ $enrollment->user->license_class == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label">State Issue</label>
                        <select name="license_state" class="form-control">
                            <option value="">Select State</option>
                            @foreach(['Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California', 'Colorado', 'Connecticut', 'Delaware', 'Florida', 'Georgia', 'Hawaii', 'Idaho', 'Illinois', 'Indiana', 'Iowa', 'Kansas', 'Kentucky', 'Louisiana', 'Maine', 'Maryland', 'Massachusetts', 'Michigan', 'Minnesota', 'Mississippi', 'Missouri', 'Montana', 'Nebraska', 'Nevada', 'New Hampshire', 'New Jersey', 'New Mexico', 'New York', 'North Carolina', 'North Dakota', 'Ohio', 'Oklahoma', 'Oregon', 'Pennsylvania', 'Rhode Island', 'South Carolina', 'South Dakota', 'Tennessee', 'Texas', 'Utah', 'Vermont', 'Virginia', 'Washington', 'West Virginia', 'Wisconsin', 'Wyoming'] as $state)
                                <option value="{{ $state }}" {{ $enrollment->user->license_state == $state ? 'selected' : '' }}>{{ $state }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Citation & Court Information -->
            <div class="form-section">
                <h5><i class="fas fa-gavel"></i> Citation & Court Information</h5>
                <div class="row">
                  
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Citation Number/Case Number</label>
                        <input type="text" name="citation_number" class="form-control" 
                               value="{{ $enrollment->citation_number ?? $enrollment->user->citation_number ?? '' }}"
                               placeholder="{{ ($enrollment->citation_number ?? $enrollment->user->citation_number) ? '' : 'Not Available' }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Due Date</label>
                        <div class="input-group">
                            <input type="number" name="due_month" class="form-control" placeholder="{{ $enrollment->user->due_month ?: 'MM' }}" 
                                   min="1" max="12" value="{{ $enrollment->user->due_month }}">
                            <input type="number" name="due_day" class="form-control" placeholder="{{ $enrollment->user->due_day ?: 'DD' }}" 
                                   min="1" max="31" value="{{ $enrollment->user->due_day }}">
                            <input type="number" name="due_year" class="form-control" placeholder="{{ $enrollment->user->due_year ?: 'YYYY' }}" 
                                   value="{{ $enrollment->user->due_year }}">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">State</label>
                        <div class="searchable-dropdown" style="position: relative;">
                            <input type="text" id="state-search" class="form-control" autocomplete="off" 
                                   value="{{ $enrollment->court_state ?? $enrollment->user->state ?? '' }}"
                                   placeholder="{{ ($enrollment->court_state ?? $enrollment->user->state) ? 'Search state...' : 'Not Available' }}">
                            <div id="state-dropdown" class="dropdown-list"></div>
                            <input type="hidden" id="court_state" name="court_state" value="{{ $enrollment->court_state ?? $enrollment->user->state ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">County</label>
                        <div class="searchable-dropdown" style="position: relative;">
                            <input type="text" id="county-search" class="form-control" autocomplete="off"
                                   value="{{ $enrollment->court_county ?? '' }}"
                                   placeholder="{{ $enrollment->court_county ? 'Select state first...' : 'Not Available' }}">
                            <div id="county-dropdown" class="dropdown-list"></div>
                            <input type="hidden" id="court_county" name="court_county" value="{{ $enrollment->court_county ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Court</label>
                        <div class="searchable-dropdown" style="position: relative;">
                            <input type="text" id="court-search" class="form-control" autocomplete="off"
                                   value="{{ $enrollment->court_selected ?? $enrollment->user->court_selected ?? '' }}"
                                   placeholder="{{ ($enrollment->court_selected ?? $enrollment->user->court_selected) ? 'Select county first...' : 'Not Available' }}">
                            <div id="court-dropdown" class="dropdown-list"></div>
                            <input type="hidden" id="court_selected" name="court_selected" value="{{ $enrollment->court_selected ?? $enrollment->user->court_selected ?? '' }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Address Information -->
            <div class="form-section">
                <h5><i class="fas fa-map-marker-alt"></i> Customer Address</h5>
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Address</label>
                        <input type="text" name="address_1" class="form-control" 
                               value="{{ $enrollment->user->mailing_address ?? $enrollment->user->address ?? '' }}"
                               placeholder="{{ ($enrollment->user->mailing_address ?? $enrollment->user->address) ? '' : 'Not Available' }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">City</label>
                        <input type="text" name="city" class="form-control" 
                               value="{{ $enrollment->user->city }}"
                               placeholder="{{ $enrollment->user->city ?: 'Not Available' }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">State</label>
                        <input type="text" name="state" class="form-control" 
                               value="{{ $enrollment->user->state }}"
                               placeholder="{{ $enrollment->user->state ?: 'Not Available' }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Zip</label>
                        <input type="text" name="zip" class="form-control" 
                               value="{{ $enrollment->user->zip }}"
                               placeholder="{{ $enrollment->user->zip ?: 'Not Available' }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Phone</label>
                        @php
                            $phoneValue = '';
                            if($enrollment->user->phone_1 && $enrollment->user->phone_2 && $enrollment->user->phone_3) {
                                $phoneValue = "({$enrollment->user->phone_1}) {$enrollment->user->phone_2}-{$enrollment->user->phone_3}";
                            } elseif($enrollment->user->phone) {
                                $phoneValue = $enrollment->user->phone;
                            }
                        @endphp
                        <input type="text" name="phone" class="form-control" 
                               value="{{ $phoneValue }}"
                               placeholder="{{ $phoneValue ?: 'Not Available' }}">
                    </div>
                </div>
            </div>

            <!-- Payment Information -->
            <div class="form-section">
                <h5><i class="fas fa-credit-card"></i> Payment Information</h5>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Payment Status</label>
                        <select name="payment_status" class="form-control">
                            <option value="pending" {{ $enrollment->payment_status == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="paid" {{ $enrollment->payment_status == 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="failed" {{ $enrollment->payment_status == 'failed' ? 'selected' : '' }}>Failed</option>
                            <option value="refunded" {{ $enrollment->payment_status == 'refunded' ? 'selected' : '' }}>Refunded</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Amount Paid</label>
                        <input type="number" step="0.01" name="amount_paid" class="form-control" value="{{ $enrollment->amount_paid }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method" class="form-control">
                            <option value="">Select Method</option>
                            <option value="CC" {{ $enrollment->payment_method == 'CC' ? 'selected' : '' }}>Credit Card</option>
                            <option value="PayPal" {{ $enrollment->payment_method == 'PayPal' ? 'selected' : '' }}>PayPal</option>
                            <option value="Check" {{ $enrollment->payment_method == 'Check' ? 'selected' : '' }}>Check</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Charge Response</label>
                        <textarea name="charge_response" class="form-control" rows="3" readonly>{{ $enrollment->payment_id }}</textarea>
                    </div>
                </div>

                <!-- Optional Services Information -->
                @php
                    $hasOptionalServices = false;
                    if ($enrollment->optional_services) {
                        if (is_array($enrollment->optional_services)) {
                            $hasOptionalServices = count($enrollment->optional_services) > 0;
                        } elseif (is_string($enrollment->optional_services)) {
                            $decoded = json_decode($enrollment->optional_services, true);
                            $hasOptionalServices = is_array($decoded) && count($decoded) > 0;
                        }
                    }
                @endphp
                @if($hasOptionalServices)
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <div 
                        style=
                        "background: #2A2A2A;
    border: 2px solid #ffffff;
    border-radius: 8px;
    padding: 20px;
    margin-top: 15px;">
                            <h6 style="margin: 0 0 15px 0; color: #e65100; display: flex; align-items: center;">
                                <i class="fas fa-star" style="color: #ffa726; margin-right: 8px;"></i>
                                Optional Services Purchased
                            </h6>
                            <div class="row">
                                @php
                                    $serviceNames = [
                                        'certverify' => 'CertVerify Service',
                                        'mail_certificate' => 'Mail/Postal Certificate Copy',
                                        'fedex_certificate' => 'FedEx 2Day Certificate',
                                        'nextday_certificate' => 'Next Day Certificate',
                                        'email_certificate' => 'Email Certificate Copy (CA Only)'
                                    ];
                                    
                                    // Ensure we have an array to iterate over
                                    $optionalServices = $enrollment->optional_services;
                                    if (is_string($optionalServices)) {
                                        $optionalServices = json_decode($optionalServices, true) ?: [];
                                    } elseif (!is_array($optionalServices)) {
                                        $optionalServices = [];
                                    }
                                @endphp
                                @foreach($optionalServices as $service)
                                <div class="col-md-6 mb-2">
                                    <div style="background:#1A1A1A; border-radius: 6px; padding: 12px;">
                                        <div style="display: flex; justify-content: between; align-items: center;">
                                            <div>
                                                <strong style="color: #e65100;">
                                                    {{ $serviceNames[$service['id']] ?? $service['name'] ?? ucfirst(str_replace('_', ' ', $service['id'])) }}
                                                </strong>
                                                @if(isset($service['description']))
                                                    <p style="margin: 5px 0 0 0; color: #666; font-size: 12px;">{{ $service['description'] }}</p>
                                                @endif
                                            </div>
                                            <div style="text-align: right; margin-left: 10px;">
                                                <span style="font-weight: bold; color: #2e7d32;">
                                                    ${{ number_format($service['price'] ?? 0, 2) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <strong style="color: #e65100;">Total Optional Services:</strong>
                                    <strong style="color: #2e7d32; font-size: 18px;">
                                        ${{ number_format($enrollment->optional_services_total ?? 0, 2) }}
                                    </strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @else
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <div style="background: #f5f5f5; border: 1px solid #ddd; border-radius: 6px; padding: 15px; margin-top: 15px;">
                            <p style="margin: 0; color: #666; text-align: center;">
                                <i class="fas fa-info-circle" style="margin-right: 8px;"></i>
                                No optional services were purchased with this enrollment.
                            </p>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Certificate Information -->
            <div class="form-section">
                <h5><i class="fas fa-certificate"></i> Certificate Information</h5>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Certificate Number</label>
                        <input type="text" name="cert_no" class="form-control" 
                               value="{{ $enrollment->floridaCertificate->dicds_certificate_number ?? '' }}" readonly>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Subscribe Newsletter?</label>
                        <select name="subscribe_newsletter" class="form-control">
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Send Certification to Customer?</label>
                        <select name="send_cert" class="form-control">
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-primary" onclick="reprintCertificate()">
                                <i class="fas fa-print"></i> Reprint Certificate
                            </button>
                            <button type="button" class="btn btn-outline-success" onclick="viewCertificate()">
                                <i class="fas fa-eye"></i> View Certificate
                            </button>
                            <button type="button" class="btn btn-outline-info" onclick="resendPDF()">
                                <i class="fas fa-envelope"></i> Resend PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Florida Transmission -->
            @if($latestTransmission)
            <div class="form-section">
                <h5><i class="fas fa-satellite-dish"></i> TVCC Response/FL Transmission</h5>
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <div class="alert alert-{{ $latestTransmission->status == 'success' ? 'success' : 'danger' }}">
                            <strong>Status:</strong> {{ ucfirst($latestTransmission->status) }}<br>
                            @if($latestTransmission->error_code)
                                <strong>Error #:</strong> {{ $latestTransmission->error_code }} - {{ $latestTransmission->error_message }}<br>
                            @endif
                            <strong>Sent:</strong> {{ $latestTransmission->sent_at ? $latestTransmission->sent_at->format('m/d/Y H:i:s') : 'Not sent' }}
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <button type="button" class="btn btn-warning" onclick="resendTransmission()">
                            <i class="fas fa-redo"></i> Resend FL Transmission
                        </button>
                        <a href="/admin/fl-transmissions/{{ $latestTransmission->id }}" class="btn btn-info" target="_blank">
                            <i class="fas fa-eye"></i> View Transmission Details
                        </a>
                    </div>
                </div>
            </div>
            @endif

            <!-- Course Status -->
            <div class="form-section">
                <h5><i class="fas fa-tasks"></i> Course Status & Progress</h5>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Enrollment Status</label>
                        <select name="status" class="form-control">
                            <option value="active" {{ $enrollment->status == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="completed" {{ $enrollment->status == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="expired" {{ $enrollment->status == 'expired' ? 'selected' : '' }}>Expired</option>
                            <option value="cancelled" {{ $enrollment->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Total Time Spent (minutes)</label>
                        <input type="number" name="total_time_spent" class="form-control" value="{{ $enrollment->total_time_spent ?? 0 }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Progress Percentage</label>
                        <input type="number" name="progress_percentage" class="form-control" value="{{ $enrollment->progress_percentage ?? 0 }}" readonly>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Security Question Blocked</label>
                        <select name="security_blocked" class="form-control">
                            <option value="No">No</option>
                            <option value="Yes">Yes</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Unlock Final Exam (failed twice)</label>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" name="unlock_final_exam" id="unlockFinalExam">
                            <label class="form-check-label" for="unlockFinalExam">Allow Retake</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- IDology & Testing -->
            <div class="form-section">
                <h5><i class="fas fa-shield-alt"></i> IDology & Testing</h5>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">IDology Test</label>
                        <select name="idology_test" class="form-control">
                            <option value="Not Taken">Not Taken</option>
                            <option value="Pass">Pass</option>
                            <option value="Fail">Fail</option>
                            <option value="Retake">Retake</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Paid ID Test</label>
                        <select name="paid_id_test" class="form-control">
                            <option value="No">No</option>
                            <option value="Yes">Yes</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Manual Input Score (ex: 85 for 85%)</label>
                        <input type="number" name="manual_score" class="form-control" min="0" max="100">
                    </div>
                </div>
            </div>

            <!-- Personal Questions -->
            <div class="form-section">
                <h5><i class="fas fa-question-circle"></i> Personal Information Questions</h5>
                <div class="row">
                    @for($i = 1; $i <= 13; $i++)
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Personal Question {{ $i }}</label>
                            @if(in_array($i, [8, 9, 10]))
                                @if($i == 8 || $i == 9)
                                    <input type="text" name="personal_q{{ $i }}" class="form-control" 
                                           value="{{ $enrollment->user->{'security_q' . $i} ?? '' }}">
                                @else
                                    <input type="text" name="personal_q{{ $i }}" class="form-control" 
                                           value="{{ $enrollment->user->{'security_q' . $i} ?? '' }}">
                                @endif
                            @else
                                <input type="text" name="personal_q{{ $i }}" class="form-control" 
                                       value="{{ $enrollment->user->{'security_q' . $i} ?? '' }}"
                                       placeholder="{{ $enrollment->user->{'security_q' . $i} ? '' : 'Not Available' }}">
                            @endif
                        </div>
                    @endfor
                </div>
            </div>

            <!-- Additional Notes -->
            <div class="form-section">
                <h5><i class="fas fa-sticky-note"></i> Additional Notes & Info</h5>
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Note</label>
                        <textarea name="note" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Void Reason</label>
                        <textarea name="void_reason" class="form-control" rows="2"></textarea>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="form-section">
                <div class="d-flex justify-content-between flex-wrap gap-3">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                        <a href="/admin/enrollments" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="button" class="btn btn-outline-primary" onclick="printTermsPage()">
                            <i class="fas fa-file-alt"></i> Print Terms
                        </button>
                        <button type="button" class="btn btn-success" onclick="emailReceipt()">
                            <i class="fas fa-envelope"></i> Email Receipt
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="printReceipt()">
                            <i class="fas fa-print"></i> Print Receipt
                        </button>
                    </div>
                </div>
            </div>

        </form>

        <!-- Chapter Progress Table -->
        @if($enrollment->progress->count() > 0)
        <div class="form-section">
            <h5><i class="fas fa-list-check"></i> Chapter Progress</h5>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Chapter</th>
                            <th>Status</th>
                            <th>Time Spent</th>
                            <th>Started</th>
                            <th>Completed</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($enrollment->progress as $progress)
                        <tr>
                            <td>{{ $progress->chapter->title ?? 'Chapter ' . $progress->chapter_id }}</td>
                            <td>
                                @if($progress->is_completed)
                                    <span class="badge bg-success"><i class="fas fa-check"></i> Completed</span>
                                @else
                                    <span class="badge bg-warning"><i class="fas fa-clock"></i> In Progress</span>
                                @endif
                            </td>
                            <td>{{ $progress->time_spent ?? 0 }} min</td>
                            <td>{{ $progress->started_at ? $progress->started_at->format('M j, Y') : 'N/A' }}</td>
                            <td>{{ $progress->completed_at ? $progress->completed_at->format('M j, Y') : 'N/A' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    <x-footer />

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function reprintCertificate() {
            window.open('/generate-certificates?enrollment_id={{ $enrollment->id }}', '_blank');
        }

        function viewCertificate() {
            window.open('/certificates/{{ $enrollment->id }}', '_blank');
        }

        function resendPDF() {
            if(confirm('Resend certificate PDF to customer email?')) {
                fetch('/api/resend-certificate/{{ $enrollment->id }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                }).then(response => response.json())
                  .then(data => alert(data.message))
                  .catch(error => alert('Error sending certificate'));
            }
        }

        function resendTransmission() {
            if(confirm('Resend Florida transmission?')) {
                fetch('/api/resend-transmission/{{ $enrollment->id }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                }).then(response => response.json())
                  .then(data => {
                      alert(data.message);
                      location.reload();
                  })
                  .catch(error => alert('Error resending transmission'));
            }
        }

        function printTermsPage() {
            window.print();
        }

        function emailReceipt() {
            if(confirm('Send payment receipt to customer email?')) {
                fetch('/api/email-receipt/{{ $enrollment->id }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                }).then(response => response.json())
                  .then(data => alert(data.message))
                  .catch(error => alert('Error sending receipt'));
            }
        }

        function printReceipt() {
            window.open('/invoices/{{ $enrollment->id }}', '_blank');
        }

        // Court/State/County Searchable Dropdowns
        let statesData = [];
        let countiesData = [];
        let courtsData = [];
        let courtPage = 1;
        let selectedState = '';
        let selectedCounty = '';

        // Load states on page load
        async function loadStates() {
            try {
                const response = await fetch('/api/courts/states');
                statesData = await response.json();
                console.log('States loaded:', statesData);
            } catch (error) {
                console.error('Error loading states:', error);
            }
        }

        // Render state dropdown
        function renderStateDropdown(states) {
            const dropdown = document.getElementById('state-dropdown');
            dropdown.innerHTML = states.map(state => 
                `<div class="dropdown-item" data-value="${state}">${state}</div>`
            ).join('');
            
            dropdown.querySelectorAll('.dropdown-item').forEach(item => {
                item.addEventListener('click', selectState);
            });
        }

        // Select state
        function selectState(e) {
            selectedState = e.target.dataset.value;
            document.getElementById('court_state').value = selectedState;
            document.getElementById('state-search').value = selectedState;
            document.getElementById('state-dropdown').style.display = 'none';
            
            // Enable county search and reset
            document.getElementById('county-search').disabled = false;
            document.getElementById('county-search').placeholder = 'Loading counties...';
            document.getElementById('court_county').value = '';
            document.getElementById('county-search').value = '';
            document.getElementById('court_selected').value = '';
            document.getElementById('court-search').value = '';
            document.getElementById('court-search').disabled = true;
            
            loadCounties(selectedState);
        }

        // Load counties for selected state
        async function loadCounties(state) {
            try {
                const response = await fetch(`/api/courts/by-state/${state}`);
                countiesData = await response.json();
                console.log('Counties for ' + state + ':', countiesData);
                document.getElementById('county-search').placeholder = 'Search county...';
            } catch (error) {
                console.error('Error loading counties:', error);
                document.getElementById('county-search').placeholder = 'Error loading counties';
            }
        }

        // Render county dropdown
        function renderCountyDropdown(counties) {
            const dropdown = document.getElementById('county-dropdown');
            if (!counties || counties.length === 0) {
                dropdown.innerHTML = '<div class="dropdown-item">No counties found</div>';
                return;
            }
            
            dropdown.innerHTML = counties.map(county => 
                `<div class="dropdown-item" data-value="${county}">${county}</div>`
            ).join('');
            
            dropdown.querySelectorAll('.dropdown-item').forEach(item => {
                item.addEventListener('click', selectCounty);
            });
        }

        // Select county and load courts
        async function selectCounty(e) {
            selectedCounty = e.target.dataset.value;
            document.getElementById('court_county').value = selectedCounty;
            document.getElementById('county-search').value = selectedCounty;
            document.getElementById('county-dropdown').style.display = 'none';
            
            // Enable court search
            document.getElementById('court-search').disabled = false;
            document.getElementById('court-search').placeholder = 'Loading courts...';
            courtPage = 1;
            await loadCourts(selectedState, selectedCounty, 1);
        }

        // Load courts for selected state and county
        async function loadCourts(state, county, page = 1) {
            try {
                const response = await fetch(`/api/courts/by-county/${state}/${county}?page=${page}`);
                const data = await response.json();
                console.log('Courts response:', data);
                
                if (page === 1) {
                    courtsData = data.courts || [];
                } else {
                    courtsData = [...courtsData, ...(data.courts || [])];
                }
                
                document.getElementById('court-search').placeholder = 'Search court...';
            } catch (error) {
                console.error('Error loading courts:', error);
                document.getElementById('court-search').placeholder = 'Error loading courts';
            }
        }

        // Render court dropdown
        function renderCourtDropdown(courts) {
            const dropdown = document.getElementById('court-dropdown');
            
            if (!courts || courts.length === 0) {
                dropdown.innerHTML = '<div class="dropdown-item">No courts found</div>';
                dropdown.style.display = 'block';
                return;
            }
            
            let html = courts.map(court => {
                const courtName = typeof court === 'string' ? court : court.court;
                return `<div class="dropdown-item" data-value="${courtName}, ${selectedCounty}, ${selectedState}">${courtName}, ${selectedCounty}, ${selectedState}</div>`;
            }).join('');
            
            dropdown.innerHTML = html;
            dropdown.style.display = 'block';
            
            dropdown.querySelectorAll('.dropdown-item').forEach(item => {
                item.addEventListener('click', selectCourt);
            });
        }

        // Select court
        function selectCourt(e) {
            document.getElementById('court_selected').value = e.target.dataset.value;
            document.getElementById('court-search').value = e.target.textContent;
            document.getElementById('court-dropdown').style.display = 'none';
        }

        // State search
        document.getElementById('state-search').addEventListener('focus', () => {
            const search = document.getElementById('state-search').value.toLowerCase();
            const filtered = statesData.filter(state => state.toLowerCase().includes(search));
            renderStateDropdown(filtered);
            document.getElementById('state-dropdown').style.display = 'block';
        });

        document.getElementById('state-search').addEventListener('input', (e) => {
            const search = e.target.value.toLowerCase();
            const filtered = statesData.filter(state => state.toLowerCase().includes(search));
            renderStateDropdown(filtered);
            document.getElementById('state-dropdown').style.display = 'block';
        });

        // County search
        document.getElementById('county-search').addEventListener('focus', () => {
            if (countiesData.length > 0) {
                const search = document.getElementById('county-search').value.toLowerCase();
                const filtered = countiesData.filter(county => county.toLowerCase().includes(search));
                renderCountyDropdown(filtered);
                document.getElementById('county-dropdown').style.display = 'block';
            }
        });

        document.getElementById('county-search').addEventListener('input', (e) => {
            const search = e.target.value.toLowerCase();
            const filtered = countiesData.filter(county => county.toLowerCase().includes(search));
            renderCountyDropdown(filtered);
            document.getElementById('county-dropdown').style.display = 'block';
        });

        // Court search
        document.getElementById('court-search').addEventListener('focus', () => {
            if (courtsData.length > 0) {
                const search = document.getElementById('court-search').value.toLowerCase();
                const filtered = courtsData.filter(court => {
                    const courtName = typeof court === 'string' ? court : court.court;
                    return `${courtName}, ${selectedCounty}, ${selectedState}`.toLowerCase().includes(search);
                });
                renderCourtDropdown(filtered);
            }
        });

        document.getElementById('court-search').addEventListener('input', (e) => {
            const search = e.target.value.toLowerCase();
            const filtered = courtsData.filter(court => {
                const courtName = typeof court === 'string' ? court : court.court;
                return `${courtName}, ${selectedCounty}, ${selectedState}`.toLowerCase().includes(search);
            });
            renderCourtDropdown(filtered);
        });

        // Close dropdowns on outside click
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.searchable-dropdown')) {
                document.getElementById('state-dropdown').style.display = 'none';
                document.getElementById('county-dropdown').style.display = 'none';
                document.getElementById('court-dropdown').style.display = 'none';
            }
        });

        // Load states on page load
        loadStates();
    </script>
</body>
</html>
