@extends('layouts.dicds')
@section('title', 'School Activity Report')
@section('content')
<div class="login-container">
    <div class="header-banner">
        <div class="florida-seal">Florida Department of<br><strong>HIGHWAY SAFETY & MOTOR VEHICLES</strong></div>
        <div class="tagline">"Making Highways Safe"</div>
    </div>

    <h1>School Activity Report</h1>

    <form method="GET" action="{{ route('dicds.reports.school-activity') }}">
        <div class="form-group">
            <label>Date From *</label>
            <input type="date" name="date_from" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Date To *</label>
            <input type="date" name="date_to" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Select School *</label>
            <select name="school_id" class="form-control" required>
                <option value="">-- Select School --</option>
                @foreach($schools as $school)
                    <option value="{{ $school->id }}">{{ $school->school_name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Select Course *</label>
            <select name="course_id" class="form-control" required>
                <option value="">-- Select Course --</option>
                @foreach($courses as $course)
                    <option value="{{ $course->id }}">{{ $course->title }}</option>
                @endforeach
            </select>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <button type="submit" class="btn">Generate Report</button>
            <a href="{{ route('dicds.reports.menu') }}" class="btn">Return to Reports Menu</a>
        </div>
    </form>

    @if(isset($report))
    <div style="margin-top: 40px; padding: 20px; background: white; border: 1px solid #ccc;">
        <h2>Report Results</h2>
        <p><strong>School:</strong> {{ $report['school']->school_name ?? 'N/A' }}</p>
        <p><strong>Course:</strong> {{ $report['course']->title ?? 'N/A' }}</p>
        <p><strong>Period:</strong> {{ $report['date_from'] }} to {{ $report['date_to'] }}</p>
        <p><strong>Total Certificates:</strong> {{ $report['total_count'] }}</p>
        
        @if($report['certificates']->count() > 0)
        <table style="width: 100%; margin-top: 20px; border-collapse: collapse;">
            <thead>
                <tr style="background: #f0f0f0;">
                    <th style="border: 1px solid #ccc; padding: 8px;">Certificate #</th>
                    <th style="border: 1px solid #ccc; padding: 8px;">Student Name</th>
                    <th style="border: 1px solid #ccc; padding: 8px;">Completion Date</th>
                    <th style="border: 1px solid #ccc; padding: 8px;">Score</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report['certificates'] as $cert)
                <tr>
                    <td style="border: 1px solid #ccc; padding: 8px;">{{ $cert->dicds_certificate_number }}</td>
                    <td style="border: 1px solid #ccc; padding: 8px;">{{ $cert->student_name }}</td>
                    <td style="border: 1px solid #ccc; padding: 8px;">{{ $cert->completion_date }}</td>
                    <td style="border: 1px solid #ccc; padding: 8px;">{{ $cert->final_exam_score }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p style="margin-top: 20px; color: #666;">No certificates found for the selected criteria.</p>
        @endif
    </div>
    @endif
</div>
@endsection
