@echo off
REM Batch file to convert USER_MANUAL.md to PDF
REM Usage: convert-manual-to-pdf.bat

echo.
echo ========================================
echo   USER_MANUAL.md to PDF Converter
echo ========================================
echo.

REM Check if USER_MANUAL.md exists
if not exist "USER_MANUAL.md" (
    echo [ERROR] USER_MANUAL.md not found!
    pause
    exit /b 1
)

echo [OK] Found USER_MANUAL.md
echo.

REM Try PowerShell script first
echo Checking for PowerShell...
where powershell >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo [OK] PowerShell found
    echo.
    echo Running PowerShell conversion script...
    powershell -ExecutionPolicy Bypass -File convert-manual-to-pdf.ps1
    goto :end
)

REM Try Python
echo Checking for Python...
where python >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo [OK] Python found
    echo.
    echo Running Python conversion script...
    python convert-manual-to-pdf.py
    goto :end
)

REM Try Pandoc directly
echo Checking for Pandoc...
where pandoc >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo [OK] Pandoc found
    echo.
    echo Converting with Pandoc...
    pandoc USER_MANUAL.md -o USER_MANUAL.pdf --pdf-engine=xelatex --toc --toc-depth=3 --number-sections -V geometry:margin=1in -V fontsize=11pt
    
    if exist "USER_MANUAL.pdf" (
        echo.
        echo ========================================
        echo   Conversion Complete!
        echo ========================================
        echo.
        echo PDF Location: %CD%\USER_MANUAL.pdf
        echo.
        goto :end
    ) else (
        echo [ERROR] PDF creation failed
    )
) else (
    echo [WARNING] Pandoc not found
)

REM If nothing worked, show alternatives
echo.
echo ========================================
echo   Alternative Methods
echo ========================================
echo.
echo 1. Install Pandoc:
echo    Download from: https://pandoc.org/installing.html
echo    Or use: choco install pandoc
echo.
echo 2. Install Python packages:
echo    pip install weasyprint markdown
echo    Then run: python convert-manual-to-pdf.py
echo.
echo 3. Use VS Code:
echo    - Install "Markdown PDF" extension
echo    - Open USER_MANUAL.md
echo    - Right-click ^> Markdown PDF: Export (pdf)
echo.
echo 4. Use Browser:
echo    - Install "Markdown Viewer" extension
echo    - Open USER_MANUAL.md in Chrome/Edge
echo    - Press Ctrl+P and save as PDF
echo.
echo 5. Online Converter:
echo    Visit: https://www.markdowntopdf.com/
echo.

:end
echo.
pause
