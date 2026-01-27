#!/usr/bin/env python3
"""
Convert USER_MANUAL.md to PDF using Python
Supports multiple conversion methods
"""

import os
import sys
import subprocess
import shutil

def print_header(text):
    print(f"\n{'='*60}")
    print(f"  {text}")
    print(f"{'='*60}\n")

def check_file_exists():
    """Check if USER_MANUAL.md exists"""
    if not os.path.exists('USER_MANUAL.md'):
        print("❌ Error: USER_MANUAL.md not found!")
        sys.exit(1)
    print("✅ Found USER_MANUAL.md")
    return True

def check_command(command):
    """Check if a command is available"""
    return shutil.which(command) is not None

def convert_with_pandoc():
    """Convert using Pandoc"""
    print("🔄 Converting with Pandoc...")
    
    cmd = [
        'pandoc',
        'USER_MANUAL.md',
        '-o', 'USER_MANUAL.pdf',
        '--pdf-engine=xelatex',
        '--toc',
        '--toc-depth=3',
        '--number-sections',
        '-V', 'geometry:margin=1in',
        '-V', 'fontsize=11pt',
        '-V', 'documentclass=report',
        '--highlight-style=tango'
    ]
    
    try:
        result = subprocess.run(cmd, check=True, capture_output=True, text=True)
        print("✅ PDF created successfully with Pandoc!")
        return True
    except subprocess.CalledProcessError as e:
        print(f"❌ Pandoc conversion failed: {e}")
        return False
    except Exception as e:
        print(f"❌ Error: {e}")
        return False

def convert_with_markdown2pdf():
    """Convert using markdown2pdf Python package"""
    print("🔄 Converting with markdown2pdf...")
    
    try:
        import markdown2pdf
        markdown2pdf.convert('USER_MANUAL.md', 'USER_MANUAL.pdf')
        print("✅ PDF created successfully with markdown2pdf!")
        return True
    except ImportError:
        print("⚠️  markdown2pdf not installed")
        print("   Install with: pip install markdown2pdf")
        return False
    except Exception as e:
        print(f"❌ Error: {e}")
        return False

def convert_with_weasyprint():
    """Convert using WeasyPrint"""
    print("🔄 Converting with WeasyPrint...")
    
    try:
        import markdown
        from weasyprint import HTML, CSS
        
        # Read markdown file
        with open('USER_MANUAL.md', 'r', encoding='utf-8') as f:
            md_content = f.read()
        
        # Convert markdown to HTML
        html_content = markdown.markdown(
            md_content,
            extensions=['extra', 'codehilite', 'toc']
        )
        
        # Add CSS styling
        styled_html = f"""
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <style>
                body {{
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    margin: 40px;
                    color: #333;
                }}
                h1 {{
                    color: #2c3e50;
                    border-bottom: 3px solid #3498db;
                    padding-bottom: 10px;
                }}
                h2 {{
                    color: #34495e;
                    border-bottom: 2px solid #95a5a6;
                    padding-bottom: 8px;
                    margin-top: 30px;
                }}
                h3 {{
                    color: #7f8c8d;
                    margin-top: 20px;
                }}
                code {{
                    background-color: #f4f4f4;
                    padding: 2px 6px;
                    border-radius: 3px;
                    font-family: 'Courier New', monospace;
                }}
                pre {{
                    background-color: #f4f4f4;
                    padding: 15px;
                    border-radius: 5px;
                    overflow-x: auto;
                }}
                ul, ol {{
                    margin-left: 20px;
                }}
                table {{
                    border-collapse: collapse;
                    width: 100%;
                    margin: 20px 0;
                }}
                th, td {{
                    border: 1px solid #ddd;
                    padding: 12px;
                    text-align: left;
                }}
                th {{
                    background-color: #3498db;
                    color: white;
                }}
                @page {{
                    margin: 2cm;
                    @top-center {{
                        content: "Traffic School CRM - User Manual";
                    }}
                    @bottom-center {{
                        content: counter(page);
                    }}
                }}
            </style>
        </head>
        <body>
            {html_content}
        </body>
        </html>
        """
        
        # Convert HTML to PDF
        HTML(string=styled_html).write_pdf('USER_MANUAL.pdf')
        print("✅ PDF created successfully with WeasyPrint!")
        return True
        
    except ImportError:
        print("⚠️  WeasyPrint or markdown not installed")
        print("   Install with: pip install weasyprint markdown")
        return False
    except Exception as e:
        print(f"❌ Error: {e}")
        return False

def show_alternatives():
    """Show alternative conversion methods"""
    print_header("Alternative Conversion Methods")
    
    print("1. Install Pandoc (Recommended):")
    print("   Windows: https://pandoc.org/installing.html")
    print("   Or: choco install pandoc")
    print("   Then run: pandoc USER_MANUAL.md -o USER_MANUAL.pdf\n")
    
    print("2. Install Python packages:")
    print("   pip install weasyprint markdown")
    print("   Then run this script again\n")
    
    print("3. Use VS Code:")
    print("   - Install 'Markdown PDF' extension")
    print("   - Open USER_MANUAL.md")
    print("   - Right-click > Markdown PDF: Export (pdf)\n")
    
    print("4. Use Chrome/Edge:")
    print("   - Install 'Markdown Viewer' extension")
    print("   - Open USER_MANUAL.md in browser")
    print("   - Press Ctrl+P and save as PDF\n")
    
    print("5. Online Converter:")
    print("   - Visit: https://www.markdowntopdf.com/")
    print("   - Upload USER_MANUAL.md")
    print("   - Download PDF\n")

def main():
    print_header("USER_MANUAL.md to PDF Converter")
    
    # Check if file exists
    check_file_exists()
    
    # Try different conversion methods
    success = False
    
    # Method 1: Try Pandoc
    if check_command('pandoc'):
        print("\n✅ Pandoc found!")
        success = convert_with_pandoc()
    else:
        print("\n⚠️  Pandoc not found")
    
    # Method 2: Try WeasyPrint
    if not success:
        print("\n📦 Trying WeasyPrint...")
        success = convert_with_weasyprint()
    
    # Method 3: Try markdown2pdf
    if not success:
        print("\n📦 Trying markdown2pdf...")
        success = convert_with_markdown2pdf()
    
    # Show results
    if success:
        print_header("Conversion Complete!")
        print(f"📄 PDF Location: {os.path.abspath('USER_MANUAL.pdf')}")
        print(f"📊 File Size: {os.path.getsize('USER_MANUAL.pdf') / 1024:.2f} KB")
        print("\n🎉 Success! Your PDF is ready.")
    else:
        print_header("Conversion Failed")
        show_alternatives()
        sys.exit(1)

if __name__ == "__main__":
    main()
