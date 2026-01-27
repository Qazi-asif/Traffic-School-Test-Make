/**
 * Convert USER_MANUAL.md to PDF
 * 
 * This script converts the markdown user manual to a professional PDF
 * 
 * Requirements:
 * - Node.js installed
 * - Run: npm install markdown-pdf
 * 
 * Usage:
 * node convert-manual-to-pdf.js
 */

const fs = require('fs');
const { exec } = require('child_process');

console.log('🔄 Converting USER_MANUAL.md to PDF...\n');

// Check if markdown file exists
if (!fs.existsSync('USER_MANUAL.md')) {
    console.error('❌ Error: USER_MANUAL.md not found!');
    process.exit(1);
}

// Method 1: Using markdown-pdf (if installed)
console.log('📦 Checking for markdown-pdf package...');

exec('npm list markdown-pdf', (error, stdout, stderr) => {
    if (error) {
        console.log('⚠️  markdown-pdf not found. Installing...\n');
        
        exec('npm install markdown-pdf', (installError) => {
            if (installError) {
                console.error('❌ Failed to install markdown-pdf');
                console.log('\n📝 Alternative methods:\n');
                showAlternativeMethods();
                return;
            }
            
            console.log('✅ markdown-pdf installed successfully\n');
            convertToPDF();
        });
    } else {
        console.log('✅ markdown-pdf found\n');
        convertToPDF();
    }
});

function convertToPDF() {
    const markdownpdf = require('markdown-pdf');
    
    const options = {
        paperFormat: 'Letter',
        paperOrientation: 'portrait',
        paperBorder: '2cm',
        renderDelay: 1000,
        cssPath: 'manual-styles.css' // Optional custom CSS
    };
    
    markdownpdf(options)
        .from('USER_MANUAL.md')
        .to('USER_MANUAL.pdf', function () {
            console.log('✅ PDF created successfully: USER_MANUAL.pdf\n');
            console.log('📄 File location: ' + __dirname + '/USER_MANUAL.pdf');
            console.log('\n🎉 Conversion complete!');
        });
}

function showAlternativeMethods() {
    console.log('1. Using Pandoc (Recommended):');
    console.log('   Install: https://pandoc.org/installing.html');
    console.log('   Command: pandoc USER_MANUAL.md -o USER_MANUAL.pdf --pdf-engine=xelatex\n');
    
    console.log('2. Using VS Code:');
    console.log('   - Install "Markdown PDF" extension');
    console.log('   - Open USER_MANUAL.md');
    console.log('   - Right-click > Markdown PDF: Export (pdf)\n');
    
    console.log('3. Using Online Converter:');
    console.log('   - Visit: https://www.markdowntopdf.com/');
    console.log('   - Upload USER_MANUAL.md');
    console.log('   - Download PDF\n');
    
    console.log('4. Using Chrome/Edge:');
    console.log('   - Install "Markdown Viewer" extension');
    console.log('   - Open USER_MANUAL.md in browser');
    console.log('   - Print to PDF (Ctrl+P)\n');
}
