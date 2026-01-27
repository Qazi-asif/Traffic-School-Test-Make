<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Accessibility Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
</head>
<body>
    <x-theme-switcher />
    <x-navbar />
        <div class="container mt-4" style="margin-left: 300px; max-width: calc(100% - 320px);">
            <h2>Accessibility Settings</h2>
            
            <div class="card">
                <div class="card-body">
                    <form id="accessibilityForm">
                        <div class="mb-3">
                            <label for="fontSize" class="form-label">Font Size</label>
                            <select class="form-control" id="fontSize" onchange="applyFontSize()" required>
                                <option value="small">Small</option>
                                <option value="medium" selected>Medium</option>
                                <option value="large">Large</option>
                                <option value="xlarge">Extra Large</option>
                            </select>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="highContrast" onchange="applyHighContrast()">
                            <label class="form-check-label" for="highContrast">High Contrast Mode</label>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="reducedAnimations" onchange="applyReducedAnimations()">
                            <label class="form-check-label" for="reducedAnimations">Reduce Animations</label>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="screenReader" onchange="applyScreenReader()">
                            <label class="form-check-label" for="screenReader">Screen Reader Optimized</label>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="keyboardNav" onchange="applyKeyboardNav()" checked>
                            <label class="form-check-label" for="keyboardNav">Enhanced Keyboard Navigation</label>
                        </div>
                        
                        <button type="button" onclick="showKeyboardGuide()" class="btn btn-info me-2">
                            <i class="fas fa-keyboard"></i> Keyboard Guide
                        </button>
                        <button type="button" onclick="saveSettings()" class="btn btn-primary">Save Settings</button>
                        <button type="button" onclick="resetSettings()" class="btn btn-secondary">Reset to Defaults</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Keyboard Navigation Guide Modal -->
    <div class="modal fade" id="keyboardGuideModal" tabindex="-1" aria-labelledby="keyboardGuideTitle" aria-describedby="keyboardGuideDesc">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="keyboardGuideTitle">Keyboard Navigation Guide</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="keyboardGuideDesc">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Basic Navigation</h6>
                            <ul>
                                <li><kbd>Tab</kbd> - Move to next element</li>
                                <li><kbd>Shift + Tab</kbd> - Move to previous element</li>
                                <li><kbd>Enter</kbd> - Activate buttons/links</li>
                                <li><kbd>Space</kbd> - Toggle checkboxes</li>
                                <li><kbd>Esc</kbd> - Close modals/dropdowns</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Site Navigation</h6>
                            <ul>
                                <li><kbd>Alt + 1</kbd> - Skip to main content</li>
                                <li><kbd>Alt + 2</kbd> - Navigation menu</li>
                                <li><kbd>Alt + 3</kbd> - Search</li>
                                <li><kbd>Alt + 4</kbd> - Footer</li>
                                <li><kbd>F6</kbd> - Cycle through page regions</li>
                            </ul>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <h6>Form Controls</h6>
                            <ul>
                                <li><kbd>↑↓</kbd> - Navigate select options</li>
                                <li><kbd>Home/End</kbd> - First/last option</li>
                                <li><kbd>Page Up/Down</kbd> - Jump in lists</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Screen Reader</h6>
                            <ul>
                                <li><kbd>H</kbd> - Next heading</li>
                                <li><kbd>L</kbd> - Next link</li>
                                <li><kbd>B</kbd> - Next button</li>
                                <li><kbd>F</kbd> - Next form field</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Screen Reader Announcements -->
    <div id="sr-announcements" aria-live="polite" aria-atomic="true" class="sr-only"></div>

    <style>
        /* Font Size Classes */
        .font-small * { font-size: 14px !important; }
        .font-medium * { font-size: 16px !important; }
        .font-large * { font-size: 18px !important; }
        .font-xlarge * { font-size: 20px !important; }
        
        /* High Contrast Mode */
        .high-contrast {
            background: #000000 !important;
            color: #ffffff !important;
        }
        .high-contrast .card {
            background: #1a1a1a !important;
            border-color: #ffffff !important;
            color: #ffffff !important;
        }
        .high-contrast .btn-primary {
            background: #ffffff !important;
            color: #000000 !important;
        }
        .high-contrast .form-control {
            background: #333333 !important;
            color: #ffffff !important;
            border-color: #ffffff !important;
        }
        
        /* Reduced Motion */
        .reduced-motion * {
            animation-duration: 0.01ms !important;
            transition-duration: 0.01ms !important;
        }
        
        /* Enhanced Focus */
        .enhanced-keyboard *:focus {
            outline: 3px solid #007bff !important;
            outline-offset: 2px !important;
        }
        
        /* Screen Reader Only */
        .sr-only {
            position: absolute !important;
            width: 1px !important;
            height: 1px !important;
            padding: 0 !important;
            margin: -1px !important;
            overflow: hidden !important;
            clip: rect(0, 0, 0, 0) !important;
            white-space: nowrap !important;
            border: 0 !important;
        }
    </style>

    <script>
        let preferences = {};

        async function loadSettings() {
            console.log('🔄 Starting to load accessibility settings...');
            try {
                console.log('📡 Making request to /web/accessibility/preferences');
                const response = await fetch('/web/accessibility/preferences', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'same-origin'
                });
                
                console.log('📊 Response status:', response.status);
                console.log('📊 Response headers:', response.headers);
                
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('❌ Response not OK:', response.status, errorText);
                    alert(`Error loading settings: ${response.status} - ${errorText}`);
                    return;
                }
                
                preferences = await response.json();
                console.log('✅ Loaded preferences:', preferences);
                
                document.getElementById('fontSize').value = preferences.font_size || 'medium';
                document.getElementById('highContrast').checked = preferences.high_contrast_mode || false;
                document.getElementById('reducedAnimations').checked = preferences.reduced_animations || false;
                document.getElementById('screenReader').checked = preferences.screen_reader_optimized || false;
                document.getElementById('keyboardNav').checked = preferences.keyboard_navigation !== false;
                
                console.log('✅ Applied settings to form elements');
                applyAllSettings();
            } catch (error) {
                console.error('❌ Failed to load settings:', error);
                alert(`Failed to load settings: ${error.message}`);
            }
        }

        function applyAllSettings() {
            console.log('🎨 Applying all accessibility settings:', preferences);
            document.body.className = document.body.className.replace(/font-\w+|high-contrast|reduced-motion|enhanced-keyboard/g, '');
            document.body.classList.add(`font-${preferences.font_size || 'medium'}`);
            
            if (preferences.high_contrast_mode) {
                document.body.classList.add('high-contrast');
            }
            
            if (preferences.reduced_animations) {
                document.body.classList.add('reduced-motion');
            }
            
            if (preferences.keyboard_navigation) {
                document.body.classList.add('enhanced-keyboard');
            }
            
            console.log('✅ Applied body classes:', document.body.className);
        }

        async function saveSettings() {
            console.log('💾 Starting to save accessibility settings...');
            const data = {
                font_size: document.getElementById('fontSize').value,
                high_contrast_mode: document.getElementById('highContrast').checked,
                reduced_animations: document.getElementById('reducedAnimations').checked,
                screen_reader_optimized: document.getElementById('screenReader').checked,
                keyboard_navigation: document.getElementById('keyboardNav').checked
            };
            
            console.log('📝 Data to save:', data);

            try {
                console.log('📡 Making PUT request to /web/accessibility/preferences');
                const response = await fetch('/web/accessibility/preferences', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(data)
                });
                
                console.log('📊 Save response status:', response.status);
                
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('❌ Save response not OK:', response.status, errorText);
                    alert(`Error saving settings: ${response.status} - ${errorText}`);
                    return;
                }
                
                const savedPrefs = await response.json();
                console.log('✅ Settings saved successfully:', savedPrefs);
                preferences = savedPrefs;
                applyAllSettings();
                alert('Settings saved successfully!');
            } catch (error) {
                console.error('❌ Error saving settings:', error);
                alert(`Error saving settings: ${error.message}`);
            }
        }

        async function resetSettings() {
            console.log('🔄 Starting to reset accessibility settings...');
            try {
                console.log('📡 Making POST request to /web/accessibility/reset-preferences');
                const response = await fetch('/web/accessibility/reset-preferences', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'same-origin'
                });
                
                console.log('📊 Reset response status:', response.status);
                
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('❌ Reset response not OK:', response.status, errorText);
                    alert(`Error resetting settings: ${response.status} - ${errorText}`);
                    return;
                }
                
                preferences = await response.json();
                console.log('✅ Settings reset successfully:', preferences);
                loadSettings();
                alert('Settings reset to defaults');
            } catch (error) {
                console.error('❌ Error resetting settings:', error);
                alert(`Error resetting settings: ${error.message}`);
            }
        }

        function applyFontSize() {
            const fontSize = document.getElementById('fontSize').value;
            console.log('🎨 Applying font size:', fontSize);
            
            document.body.className = document.body.className.replace(/font-\w+/g, '');
            document.body.classList.add(`font-${fontSize}`);
            
            preferences.font_size = fontSize;
            announceToScreenReader(`Font size changed to ${fontSize}`);
            saveSettingsAuto();
        }

        function applyHighContrast() {
            const enabled = document.getElementById('highContrast').checked;
            console.log('🎨 Applying high contrast:', enabled);
            
            if (enabled) {
                document.body.classList.add('high-contrast');
            } else {
                document.body.classList.remove('high-contrast');
            }
            
            preferences.high_contrast_mode = enabled;
            announceToScreenReader(`High contrast mode ${enabled ? 'enabled' : 'disabled'}`);
            saveSettingsAuto();
        }

        function applyReducedAnimations() {
            const enabled = document.getElementById('reducedAnimations').checked;
            console.log('🎨 Applying reduced animations:', enabled);
            
            if (enabled) {
                document.body.classList.add('reduced-motion');
            } else {
                document.body.classList.remove('reduced-motion');
            }
            
            preferences.reduced_animations = enabled;
            announceToScreenReader(`Reduced animations ${enabled ? 'enabled' : 'disabled'}`);
            saveSettingsAuto();
        }

        function applyScreenReader() {
            const enabled = document.getElementById('screenReader').checked;
            console.log('🎨 Applying screen reader optimization:', enabled);
            
            preferences.screen_reader_optimized = enabled;
            announceToScreenReader(`Screen reader optimization ${enabled ? 'enabled' : 'disabled'}`);
            saveSettingsAuto();
        }

        function applyKeyboardNav() {
            const enabled = document.getElementById('keyboardNav').checked;
            console.log('🎨 Applying enhanced keyboard navigation:', enabled);
            
            if (enabled) {
                document.body.classList.add('enhanced-keyboard');
            } else {
                document.body.classList.remove('enhanced-keyboard');
            }
            
            preferences.keyboard_navigation = enabled;
            announceToScreenReader(`Enhanced keyboard navigation ${enabled ? 'enabled' : 'disabled'}`);
            saveSettingsAuto();
        }

        function announceToScreenReader(message) {
            if (preferences.screen_reader_optimized) {
                const announcer = document.getElementById('sr-announcements');
                announcer.textContent = message;
                setTimeout(() => announcer.textContent = '', 1000);
            }
        }

        function showKeyboardGuide() {
            const modal = new bootstrap.Modal(document.getElementById('keyboardGuideModal'));
            modal.show();
            announceToScreenReader('Keyboard navigation guide opened');
        }

        async function saveSettingsAuto() {
            try {
                await fetch('/web/accessibility/preferences', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(preferences)
                });
                console.log('✅ Auto-saved preferences');
            } catch (error) {
                console.error('❌ Auto-save failed:', error);
            }
        }

        console.log('🚀 Initializing accessibility settings page...');
        console.log('🔍 CSRF Token:', document.querySelector('meta[name="csrf-token"]')?.content);
        console.log('🔍 Current URL:', window.location.href);
        
        // Add keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.altKey) {
                switch(e.key) {
                    case '1':
                        e.preventDefault();
                        document.getElementById('main-content')?.focus();
                        announceToScreenReader('Jumped to main content');
                        break;
                    case '2':
                        e.preventDefault();
                        document.querySelector('.navbar')?.focus();
                        announceToScreenReader('Jumped to navigation');
                        break;
                    case '3':
                        e.preventDefault();
                        document.querySelector('input[type="search"]')?.focus();
                        announceToScreenReader('Jumped to search');
                        break;
                }
            }
            
            if (e.key === 'F6') {
                e.preventDefault();
                // Cycle through main page regions
                const regions = ['header', 'nav', 'main', 'footer'];
                // Implementation for region cycling
                announceToScreenReader('Cycling through page regions');
            }
        });
        
        loadSettings();
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
