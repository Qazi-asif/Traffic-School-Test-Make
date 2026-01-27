# PowerShell Script to Convert USER_MANUAL.md to PDF
# Usage: .\convert-manual-to-pdf.ps1

Write-Host "🔄 Converting USER_MANUAL.md to PDF..." -ForegroundColor Cyan
Write-Host ""

# Check if USER_MANUAL.md exists
if (-Not (Test-Path "USER_MANUAL.md")) {
    Write-Host "❌ Error: USER_MANUAL.md not found!" -ForegroundColor Red
    exit 1
}

Write-Host "📄 Found USER_MANUAL.md" -ForegroundColor Green
Write-Host ""

# Method 1: Check for Pandoc
Write-Host "🔍 Checking for Pandoc..." -ForegroundColor Yellow

$pandocInstalled = Get-Command pandoc -ErrorAction SilentlyContinue

if ($pandocInstalled) {
    Write-Host "✅ Pandoc found! Converting..." -ForegroundColor Green
    Write-Host ""
    
    # Convert using Pandoc with nice formatting
    pandoc USER_MANUAL.md -o USER_MANUAL.pdf `
        --pdf-engine=xelatex `
        --toc `
        --toc-depth=3 `
        --number-sections `
        -V geometry:margin=1in `
        -V fontsize=11pt `
        -V documentclass=report `
        --highlight-style=tango
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✅ PDF created successfully!" -ForegroundColor Green
        Write-Host "📄 Location: $PWD\USER_MANUAL.pdf" -ForegroundColor Cyan
        Write-Host ""
        Write-Host "🎉 Conversion complete!" -ForegroundColor Green
        
        # Open the PDF
        $openPDF = Read-Host "Would you like to open the PDF? (Y/N)"
        if ($openPDF -eq "Y" -or $openPDF -eq "y") {
            Start-Process "USER_MANUAL.pdf"
        }
        exit 0
    } else {
        Write-Host "❌ Pandoc conversion failed" -ForegroundColor Red
    }
} else {
    Write-Host "⚠️  Pandoc not found" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "📝 Alternative Methods:" -ForegroundColor Cyan
Write-Host ""

Write-Host "1. Install Pandoc (Recommended):" -ForegroundColor White
Write-Host "   Download from: https://pandoc.org/installing.html" -ForegroundColor Gray
Write-Host "   Or use Chocolatey: choco install pandoc" -ForegroundColor Gray
Write-Host "   Then run this script again" -ForegroundColor Gray
Write-Host ""

Write-Host "2. Use Python (if installed):" -ForegroundColor White
Write-Host "   pip install markdown-pdf" -ForegroundColor Gray
Write-Host "   python convert-manual-to-pdf.py" -ForegroundColor Gray
Write-Host ""

Write-Host "3. Use VS Code:" -ForegroundColor White
Write-Host "   - Install 'Markdown PDF' extension" -ForegroundColor Gray
Write-Host "   - Open USER_MANUAL.md" -ForegroundColor Gray
Write-Host "   - Right-click > Markdown PDF: Export (pdf)" -ForegroundColor Gray
Write-Host ""

Write-Host "4. Use Chrome/Edge Browser:" -ForegroundColor White
Write-Host "   - Install 'Markdown Viewer' extension" -ForegroundColor Gray
Write-Host "   - Open USER_MANUAL.md in browser" -ForegroundColor Gray
Write-Host "   - Press Ctrl+P and save as PDF" -ForegroundColor Gray
Write-Host ""

Write-Host "5. Online Converter:" -ForegroundColor White
Write-Host "   Visit: https://www.markdowntopdf.com/" -ForegroundColor Gray
Write-Host "   Upload USER_MANUAL.md and download PDF" -ForegroundColor Gray
Write-Host ""

# Offer to install Pandoc via Chocolatey
$installPandoc = Read-Host "Would you like to install Pandoc now using Chocolatey? (Y/N)"
if ($installPandoc -eq "Y" -or $installPandoc -eq "y") {
    Write-Host ""
    Write-Host "🔄 Installing Pandoc via Chocolatey..." -ForegroundColor Cyan
    
    # Check if Chocolatey is installed
    $chocoInstalled = Get-Command choco -ErrorAction SilentlyContinue
    
    if ($chocoInstalled) {
        choco install pandoc -y
        Write-Host ""
        Write-Host "✅ Pandoc installed! Please run this script again." -ForegroundColor Green
    } else {
        Write-Host "❌ Chocolatey not found. Please install from: https://chocolatey.org/" -ForegroundColor Red
    }
}
