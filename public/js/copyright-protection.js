// Copyright Protection for Course Content
(function() {
    'use strict';

    // Disable right-click
    document.addEventListener('contextmenu', function(e) {
        if (e.target.closest('.course-content, .chapter-content')) {
            e.preventDefault();
            return false;
        }
    });

    // Disable text selection
    document.addEventListener('selectstart', function(e) {
        if (e.target.closest('.course-content, .chapter-content')) {
            e.preventDefault();
            return false;
        }
    });

    // Disable copy
    document.addEventListener('copy', function(e) {
        if (e.target.closest('.course-content, .chapter-content')) {
            e.preventDefault();
            return false;
        }
    });

    // Disable cut
    document.addEventListener('cut', function(e) {
        if (e.target.closest('.course-content, .chapter-content')) {
            e.preventDefault();
            return false;
        }
    });

    // Disable paste
    document.addEventListener('paste', function(e) {
        if (e.target.closest('.course-content, .chapter-content')) {
            e.preventDefault();
            return false;
        }
    });

    // Disable keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.target.closest('.course-content, .chapter-content')) {
            // Ctrl+C, Ctrl+X, Ctrl+V, Ctrl+A, Ctrl+P, Ctrl+S
            if (e.ctrlKey && (e.key === 'c' || e.key === 'x' || e.key === 'v' || 
                e.key === 'a' || e.key === 'p' || e.key === 's')) {
                e.preventDefault();
                return false;
            }
            // F12, Ctrl+Shift+I, Ctrl+Shift+J, Ctrl+U
            if (e.key === 'F12' || 
                (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'J')) ||
                (e.ctrlKey && e.key === 'U')) {
                e.preventDefault();
                return false;
            }
        }
    });

    // Add watermark to course content
    function addWatermark() {
        const courseContent = document.querySelectorAll('.course-content, .chapter-content');
        courseContent.forEach(function(element) {
            if (!element.querySelector('.watermark')) {
                const watermark = document.createElement('div');
                watermark.className = 'watermark';
                watermark.textContent = 'DummiesTrafficSchool.com - Copyrighted Material';
                watermark.style.cssText = `
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%) rotate(-45deg);
                    font-size: 48px;
                    color: rgba(0, 0, 0, 0.05);
                    pointer-events: none;
                    user-select: none;
                    z-index: 1;
                    white-space: nowrap;
                `;
                element.style.position = 'relative';
                element.appendChild(watermark);
            }
        });
    }

    // Apply watermark on page load and content changes
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', addWatermark);
    } else {
        addWatermark();
    }

    // Observe for dynamic content
    const observer = new MutationObserver(addWatermark);
    observer.observe(document.body, { childList: true, subtree: true });

})();
