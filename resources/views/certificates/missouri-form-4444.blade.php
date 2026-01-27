<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Missouri Form 4444 - Record of Participation and Completion</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
            color: #000;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }
        
        .form-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .form-subtitle {
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .form-number {
            font-size: 12px;
            font-weight: bold;
        }
        
        .section {
            margin-bottom: 25px;
        }
        
        .section-title {
            font-size: 14px;
            font-weight: bold;
            background-color: #f0f0f0;
            padding: 8px;
            border: 1px solid #000;
            margin-bottom: 10px;
        }
        
        .field-row {
            display: flex;
            margin-bottom: 8px;
            align-items: center;
        }
        
        .field-label {
            font-weight: bold;
            width: 150px;
            display: inline-block;
        }
        
        .field-value {
            border-bottom: 1px solid #000;
            padding: 2px 5px;
            min-width: 200px;
            display: inline-block;
        }
        
        .checkbox-section {
            margin: 15px 0;
        }
        
        .checkbox {
            display: inline-block;
            width: 15px;
            height: 15px;
            border: 2px solid #000;
            margin-right: 10px;
            text-align: center;
            line-height: 11px;
            font-weight: bold;
        }
        
        .checked {
            background-color: #000;
            color: white;
        }
        
        .signature-section {
            margin-top: 30px;
            border: 1px solid #000;
            padding: 15px;
        }
        
        .signature-line {
            border-bottom: 1px solid #000;
            height: 30px;
            margin: 10px 0;
            position: relative;
        }
        
        .signature-label {
            position: absolute;
            bottom: -15px;
            font-size: 10px;
        }
        
        .instructions {
            margin-top: 30px;
            border: 2px solid #000;
            padding: 15px;
            background-color: #f9f9f9;
        }
        
        .instructions-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .instructions ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        
        .deadline {
            font-weight: bold;
            color: #d00;
            margin-top: 10px;
        }
        
        .provider-info {
            margin-top: 20px;
            font-size: 10px;
            text-align: center;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        
        td {
            padding: 5px;
            vertical-align: top;
        }
        
        .no-break {
            page-break-inside: avoid;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="form-title">STATE OF MISSOURI</div>
        <div class="form-title">RECORD OF PARTICIPATION AND COMPLETION</div>
        <div class="form-title">OF DRIVER IMPROVEMENT PROGRAM</div>
        <div class="form-subtitle">(Missouri Form 4444)</div>
        <div class="form-number">Form Number: {{ $form_number }}</div>
    </div>

    <div class="section">
        <div class="section-title">STUDENT INFORMATION</div>
        <table>
            <tr>
                <td style="width: 50%;">
                    <div class="field-row">
                        <span class="field-label">Name:</span>
                        <span class="field-value">{{ $student_name }}</span>
                    </div>
                </td>
                <td style="width: 50%;">
                    <div class="field-row">
                        <span class="field-label">Date of Birth:</span>
                        <span class="field-value">{{ $date_of_birth }}</span>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="field-row">
                        <span class="field-label">Address:</span>
                        <span class="field-value" style="min-width: 400px;">{{ $student_address }}</span>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="field-row">
                        <span class="field-label">Driver License #:</span>
                        <span class="field-value">{{ $driver_license }}</span>
                    </div>
                </td>
                <td>
                    <div class="field-row">
                        <span class="field-label">Completion Date:</span>
                        <span class="field-value">{{ $completion_date }}</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">COURSE INFORMATION</div>
        <table>
            <tr>
                <td>
                    <div class="field-row">
                        <span class="field-label">Course Title:</span>
                        <span class="field-value" style="min-width: 300px;">{{ $course_title }}</span>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="field-row">
                        <span class="field-label">Course Duration:</span>
                        <span class="field-value">{{ $course_hours }}</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">PURPOSE OF COURSE</div>
        <div class="checkbox-section">
            <span class="checkbox {{ $form->submission_method === 'point_reduction' ? 'checked' : '' }}">
                {{ $form->submission_method === 'point_reduction' ? '✓' : '' }}
            </span>
            Point Reduction (Court Approved)
        </div>
        <div class="checkbox-section">
            <span class="checkbox {{ $form->submission_method === 'court_ordered' ? 'checked' : '' }}">
                {{ $form->submission_method === 'court_ordered' ? '✓' : '' }}
            </span>
            Court Ordered
        </div>
        <div class="checkbox-section">
            <span class="checkbox {{ $form->submission_method === 'insurance_discount' ? 'checked' : '' }}">
                {{ $form->submission_method === 'insurance_discount' ? '✓' : '' }}
            </span>
            Insurance Discount
        </div>
        <div class="checkbox-section">
            <span class="checkbox {{ $form->submission_method === 'voluntary' ? 'checked' : '' }}">
                {{ $form->submission_method === 'voluntary' ? '✓' : '' }}
            </span>
            Voluntary
        </div>
    </div>

    <div class="section">
        <div class="section-title">PROVIDER INFORMATION</div>
        <table>
            <tr>
                <td>
                    <div class="field-row">
                        <span class="field-label">Provider Name:</span>
                        <span class="field-value" style="min-width: 300px;">{{ $provider_info['name'] }}</span>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="field-row">
                        <span class="field-label">Address:</span>
                        <span class="field-value" style="min-width: 300px;">{{ $provider_info['address'] }}</span>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="field-row">
                        <span class="field-label">City, State, ZIP:</span>
                        <span class="field-value" style="min-width: 300px;">{{ $provider_info['city_state_zip'] }}</span>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="field-row">
                        <span class="field-label">Phone:</span>
                        <span class="field-value">{{ $provider_info['phone'] }}</span>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="field-row">
                        <span class="field-label">Approval Number:</span>
                        <span class="field-value">{{ $provider_info['approval_number'] }}</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section no-break">
        <div class="section-title">CERTIFICATION</div>
        <p>I hereby certify that the above-named student has successfully completed the Driver Improvement Program as required by Missouri law.</p>
        
        <div class="signature-section">
            <div class="signature-line">
                <span class="signature-label">Provider Representative Signature</span>
            </div>
            <div style="margin-top: 20px;">
                <span class="field-label">Date:</span>
                <span class="field-value">{{ $completion_date }}</span>
            </div>
        </div>

        @if($form->court_signature_required)
        <div class="signature-section" style="margin-top: 20px;">
            <p><strong>FOR POINT REDUCTION ONLY - COURT SIGNATURE REQUIRED:</strong></p>
            <div class="signature-line">
                <span class="signature-label">Court/Judge Signature</span>
            </div>
            <div style="margin-top: 20px;">
                <span class="field-label">Date:</span>
                <span class="field-value" style="min-width: 150px;"></span>
            </div>
        </div>
        @endif
    </div>

    <div class="instructions no-break">
        <div class="instructions-title">{{ $instructions['title'] }}</div>
        <ul>
            @foreach($instructions['steps'] as $step)
                <li>{{ $step }}</li>
            @endforeach
        </ul>
        <div class="deadline">{{ $instructions['deadline'] }}</div>
        @if(isset($instructions['address']))
            <p><strong>Submit to:</strong> {{ $instructions['address'] }}</p>
        @endif
    </div>

    <div class="provider-info">
        <p>This form was generated electronically by {{ $provider_info['name'] }} on {{ now()->format('m/d/Y H:i:s') }}</p>
        <p>{{ $provider_info['website'] }} | {{ $provider_info['phone'] }}</p>
    </div>
</body>
</html>