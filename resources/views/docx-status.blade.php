<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>DOCX Import Status Check</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .card { border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .card-header { background: linear-gradient(135deg, #667eea, #764ba2); color: white; border-radius: 15px 15px 0 0 !important; }
        .status-item { padding: 15px; margin: 10px 0; border-radius: 8px; border-left: 4px solid; }
        .status-success { background: #d4edda; border-left-color: #28a745; color: #155724; }
        .status-error { background: #f8d7da; border-left-color: #dc3545; color: #721c24; }
        .status-warning { background: #fff3cd; border-left-color: #ffc107; color: #856404; }
        .status-info { background: #d1ecf1; border-left-color: #17a2b8; color: #0c5460; }
        .code { background: #f8f9fa; padding: 8px 12px; border-radius: 4px; font-family: monospace; }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-header text-center py-4">
                        <h1 class="mb-0"><i class="fas fa-stethoscope me-2"></i>DOCX Import System Status</h1>
                        <p class="mb-0 mt-2 opacity-75">Comprehensive system health check</p>
                    </div>
                    <div class="card-body p-4">
                        
                        <!-- Laravel Configuration -->
                        <h3><i class="fas fa-cogs me-2"></i>Laravel Configuration</h3>
                        
                        <div class="status-item {{ config('app.key') ? 'status-success' : 'status-error' }}">
                            <strong><i class="fas fa-key me-2"></i>Application Key</strong><br>
                            @if(config('app.key'))
                                ✅ Application key is configured
                                <div class="code mt-2">{{ substr(config('app.key'), 0, 20) }}...</div>
                            @else
                                ❌ Application key is missing - run <code>php artisan key:generate</code>
                            @endif
                        </div>
                        
                        <div class="status-item {{ config('app.debug') ? 'status-warning' : 'status-info' }}">
                            <strong><i class="fas fa-bug me-2"></i>Debug Mode</strong><br>
                            @if(config('app.debug'))
                                ⚠️ Debug mode is enabled (good for testing, disable in production)
                            @else
                                ℹ️ Debug mode is disabled (enable for better error messages during testing)
                            @endif
                        </div>
                        
                        <div class="status-item status-success">
                            <strong><i class="fas fa-shield-alt me-2"></i>CSRF Token</strong><br>
                            ✅ CSRF token is active: <code>{{ csrf_token() }}</code>
                        </div>
                        
                        <!-- Routes Check -->
                        <h3 class="mt-4"><i class="fas fa-route me-2"></i>Routes Configuration</h3>
                        
                        @php
                            $routes = collect(Route::getRoutes())->map(function($route) {
                                return [
                                    'uri' => $route->uri(),
                                    'methods' => $route->methods(),
                                    'name' => $route->getName(),
                                    'action' => $route->getActionName()
                                ];
                            });
                            
                            $docxRoute = $routes->first(function($route) {
                                return $route['uri'] === 'api/import-docx' && in_array('POST', $route['methods']);
                            });
                            
                            $testRoute = $routes->first(function($route) {
                                return $route['uri'] === 'test-docx-import';
                            });
                        @endphp
                        
                        <div class="status-item {{ $docxRoute ? 'status-success' : 'status-error' }}">
                            <strong><i class="fas fa-upload me-2"></i>DOCX Import Route</strong><br>
                            @if($docxRoute)
                                ✅ POST /api/import-docx route is configured
                                <div class="code mt-2">{{ $docxRoute['action'] }}</div>
                            @else
                                ❌ DOCX import route not found
                            @endif
                        </div>
                        
                        <div class="status-item {{ $testRoute ? 'status-success' : 'status-error' }}">
                            <strong><i class="fas fa-flask me-2"></i>Test Page Route</strong><br>
                            @if($testRoute)
                                ✅ /test-docx-import route is configured
                            @else
                                ❌ Test page route not found
                            @endif
                        </div>
                        
                        <!-- Controller Check -->
                        <h3 class="mt-4"><i class="fas fa-code me-2"></i>Controller Status</h3>
                        
                        @php
                            $controllerExists = class_exists('App\Http\Controllers\ChapterController');
                            $importMethodExists = false;
                            $fallbackMethodExists = false;
                            
                            if ($controllerExists) {
                                $importMethodExists = method_exists('App\Http\Controllers\ChapterController', 'importDocx');
                                $fallbackMethodExists = method_exists('App\Http\Controllers\ChapterController', 'importDocxWithImageSkipping');
                            }
                        @endphp
                        
                        <div class="status-item {{ $controllerExists ? 'status-success' : 'status-error' }}">
                            <strong><i class="fas fa-file-code me-2"></i>ChapterController</strong><br>
                            @if($controllerExists)
                                ✅ ChapterController class exists
                            @else
                                ❌ ChapterController class not found
                            @endif
                        </div>
                        
                        <div class="status-item {{ $importMethodExists ? 'status-success' : 'status-error' }}">
                            <strong><i class="fas fa-function me-2"></i>Import Method</strong><br>
                            @if($importMethodExists)
                                ✅ importDocx() method exists
                            @else
                                ❌ importDocx() method not found
                            @endif
                        </div>
                        
                        <div class="status-item {{ $fallbackMethodExists ? 'status-success' : 'status-warning' }}">
                            <strong><i class="fas fa-life-ring me-2"></i>Fallback Method</strong><br>
                            @if($fallbackMethodExists)
                                ✅ importDocxWithImageSkipping() fallback method exists
                            @else
                                ⚠️ Fallback method not found (optional but recommended)
                            @endif
                        </div>
                        
                        <!-- Dependencies Check -->
                        <h3 class="mt-4"><i class="fas fa-puzzle-piece me-2"></i>Dependencies</h3>
                        
                        @php
                            $phpWordExists = class_exists('PhpOffice\PhpWord\IOFactory');
                            $zipArchiveExists = class_exists('ZipArchive');
                            $domDocumentExists = class_exists('DOMDocument');
                        @endphp
                        
                        <div class="status-item {{ $phpWordExists ? 'status-success' : 'status-error' }}">
                            <strong><i class="fas fa-file-word me-2"></i>PHPWord Library</strong><br>
                            @if($phpWordExists)
                                ✅ PHPWord library is available
                            @else
                                ❌ PHPWord library not found - run <code>composer install</code>
                            @endif
                        </div>
                        
                        <div class="status-item {{ $zipArchiveExists ? 'status-success' : 'status-error' }}">
                            <strong><i class="fas fa-file-archive me-2"></i>ZipArchive</strong><br>
                            @if($zipArchiveExists)
                                ✅ ZipArchive class is available
                            @else
                                ❌ ZipArchive extension not installed
                            @endif
                        </div>
                        
                        <div class="status-item {{ $domDocumentExists ? 'status-success' : 'status-error' }}">
                            <strong><i class="fas fa-code me-2"></i>DOMDocument</strong><br>
                            @if($domDocumentExists)
                                ✅ DOMDocument class is available
                            @else
                                ❌ DOM extension not installed
                            @endif
                        </div>
                        
                        <!-- Storage Check -->
                        <h3 class="mt-4"><i class="fas fa-hdd me-2"></i>Storage Configuration</h3>
                        
                        @php
                            $storagePath = storage_path('app/public/course-media');
                            $storageExists = is_dir($storagePath);
                            $storageWritable = $storageExists && is_writable($storagePath);
                            
                            if (!$storageExists) {
                                try {
                                    mkdir($storagePath, 0755, true);
                                    $storageExists = true;
                                    $storageWritable = is_writable($storagePath);
                                } catch (Exception $e) {
                                    // Could not create directory
                                }
                            }
                        @endphp
                        
                        <div class="status-item {{ $storageExists ? 'status-success' : 'status-error' }}">
                            <strong><i class="fas fa-folder me-2"></i>Course Media Directory</strong><br>
                            @if($storageExists)
                                ✅ Directory exists: <code>{{ $storagePath }}</code>
                            @else
                                ❌ Directory does not exist: <code>{{ $storagePath }}</code>
                            @endif
                        </div>
                        
                        <div class="status-item {{ $storageWritable ? 'status-success' : 'status-error' }}">
                            <strong><i class="fas fa-edit me-2"></i>Directory Permissions</strong><br>
                            @if($storageWritable)
                                ✅ Directory is writable
                            @else
                                ❌ Directory is not writable - check permissions
                            @endif
                        </div>
                        
                        <!-- Test Pages -->
                        <h3 class="mt-4"><i class="fas fa-vial me-2"></i>Test Pages</h3>
                        
                        @php
                            $testPages = [
                                'test-docx-import' => 'Enhanced DOCX Import Test',
                                'working-docx-upload' => 'Working DOCX Upload Test',
                                'working-course-creation' => 'Working Course Creation Test'
                            ];
                        @endphp
                        
                        @foreach($testPages as $route => $title)
                            @php
                                $viewExists = view()->exists(str_replace('-', '-', $route));
                            @endphp
                            <div class="status-item {{ $viewExists ? 'status-success' : 'status-error' }}">
                                <strong><i class="fas fa-external-link-alt me-2"></i>{{ $title }}</strong><br>
                                @if($viewExists)
                                    ✅ Available at: <a href="/{{ $route }}" target="_blank">/{{ $route }}</a>
                                @else
                                    ❌ View not found for /{{ $route }}
                                @endif
                            </div>
                        @endforeach
                        
                        <!-- System Summary -->
                        <h3 class="mt-4"><i class="fas fa-chart-pie me-2"></i>System Summary</h3>
                        
                        @php
                            $checks = [
                                config('app.key') ? 1 : 0,
                                $docxRoute ? 1 : 0,
                                $controllerExists ? 1 : 0,
                                $importMethodExists ? 1 : 0,
                                $phpWordExists ? 1 : 0,
                                $zipArchiveExists ? 1 : 0,
                                $storageExists ? 1 : 0,
                                $storageWritable ? 1 : 0
                            ];
                            $totalChecks = count($checks);
                            $passedChecks = array_sum($checks);
                            $percentage = round(($passedChecks / $totalChecks) * 100);
                            
                            $statusClass = 'status-error';
                            $statusIcon = 'fas fa-times-circle';
                            $statusText = 'System has critical issues';
                            
                            if ($percentage >= 90) {
                                $statusClass = 'status-success';
                                $statusIcon = 'fas fa-check-circle';
                                $statusText = 'System is ready for DOCX import';
                            } elseif ($percentage >= 70) {
                                $statusClass = 'status-warning';
                                $statusIcon = 'fas fa-exclamation-triangle';
                                $statusText = 'System has minor issues';
                            }
                        @endphp
                        
                        <div class="status-item {{ $statusClass }}">
                            <strong><i class="{{ $statusIcon }} me-2"></i>Overall Status</strong><br>
                            {{ $statusText }} ({{ $passedChecks }}/{{ $totalChecks }} checks passed - {{ $percentage }}%)
                            
                            <div class="progress mt-3" style="height: 20px;">
                                <div class="progress-bar 
                                    @if($percentage >= 90) bg-success 
                                    @elseif($percentage >= 70) bg-warning 
                                    @else bg-danger @endif" 
                                    style="width: {{ $percentage }}%">
                                    {{ $percentage }}%
                                </div>
                            </div>
                        </div>
                        
                        <!-- Action Items -->
                        <div class="mt-4 p-4 bg-light rounded">
                            <h4><i class="fas fa-tasks me-2"></i>Next Steps</h4>
                            
                            @if($percentage >= 90)
                                <div class="alert alert-success">
                                    <h5>🎉 System Ready!</h5>
                                    <p>Your DOCX import system is properly configured. You can now:</p>
                                    <ul>
                                        <li>Test DOCX import at: <a href="/test-docx-import" target="_blank">/test-docx-import</a></li>
                                        <li>Use the working examples for reference</li>
                                        <li>Apply the CSRF handling patterns to your main application</li>
                                    </ul>
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    <h5>🔧 Action Required</h5>
                                    <p>Please fix the issues marked with ❌ above, then refresh this page.</p>
                                    <p><strong>Common fixes:</strong></p>
                                    <ul>
                                        <li>Run <code>composer install</code> to install dependencies</li>
                                        <li>Run <code>php artisan key:generate</code> to generate app key</li>
                                        <li>Check file permissions on storage directories</li>
                                        <li>Ensure all required PHP extensions are installed</li>
                                    </ul>
                                </div>
                            @endif
                            
                            <div class="mt-3">
                                <button class="btn btn-primary" onclick="location.reload()">
                                    <i class="fas fa-sync-alt me-2"></i>Refresh Status
                                </button>
                                
                                @if($percentage >= 70)
                                    <a href="/test-docx-import" class="btn btn-success ms-2" target="_blank">
                                        <i class="fas fa-flask me-2"></i>Test DOCX Import
                                    </a>
                                @endif
                                
                                <button class="btn btn-info ms-2" onclick="toggleDetails()">
                                    <i class="fas fa-info-circle me-2"></i>Show Details
                                </button>
                            </div>
                        </div>
                        
                        <!-- Detailed Information (Hidden by default) -->
                        <div id="detailsSection" style="display: none;" class="mt-4 p-4 bg-light rounded">
                            <h4><i class="fas fa-info-circle me-2"></i>Detailed Information</h4>
                            
                            <h5>Environment</h5>
                            <ul>
                                <li><strong>Laravel Version:</strong> {{ app()->version() }}</li>
                                <li><strong>PHP Version:</strong> {{ PHP_VERSION }}</li>
                                <li><strong>Environment:</strong> {{ config('app.env') }}</li>
                                <li><strong>Timezone:</strong> {{ config('app.timezone') }}</li>
                            </ul>
                            
                            <h5>File Upload Limits</h5>
                            <ul>
                                <li><strong>upload_max_filesize:</strong> {{ ini_get('upload_max_filesize') }}</li>
                                <li><strong>post_max_size:</strong> {{ ini_get('post_max_size') }}</li>
                                <li><strong>max_execution_time:</strong> {{ ini_get('max_execution_time') }}s</li>
                                <li><strong>memory_limit:</strong> {{ ini_get('memory_limit') }}</li>
                            </ul>
                            
                            <h5>Session Configuration</h5>
                            <ul>
                                <li><strong>Driver:</strong> {{ config('session.driver') }}</li>
                                <li><strong>Lifetime:</strong> {{ config('session.lifetime') }} minutes</li>
                                <li><strong>Cookie Name:</strong> {{ config('session.cookie') }}</li>
                            </ul>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleDetails() {
            const details = document.getElementById('detailsSection');
            if (details.style.display === 'none') {
                details.style.display = 'block';
            } else {
                details.style.display = 'none';
            }
        }
    </script>
</body>
</html>