<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DICDS - Interactive Table of Contents</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .header { text-align: center; font-size: 32px; font-weight: bold; margin-bottom: 30px; }
        .nav-buttons { position: absolute; top: 20px; right: 20px; }
        .nav-btn { background: #ffd700; border: none; padding: 5px 10px; margin: 2px; cursor: pointer; }
        .content { display: flex; }
        .toc-left { flex: 1; padding-right: 20px; }
        .toc-right { flex: 1; padding-left: 20px; }
        .section { margin-bottom: 20px; }
        .section-title { color: #4a90e2; font-size: 18px; font-weight: bold; margin-bottom: 10px; }
        .toc-item { display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px dotted #ccc; }
        .toc-item a { text-decoration: none; color: #1976d2; }
        .toc-item a:hover { text-decoration: underline; }
        .page-num { color: #1976d2; font-weight: bold; }
        .instructions { background: #e3f2fd; padding: 20px; border-radius: 10px; margin: 20px 0; text-align: center; }
        .footer { text-align: center; margin-top: 30px; font-style: italic; }
        .version-info { position: absolute; bottom: 20px; left: 20px; font-size: 12px; }
    </style>
</head>
<body>
    <div class="nav-buttons">
        <button class="nav-btn" onclick="history.back()">◀</button>
        <button class="nav-btn" onclick="window.location.href='{{ route('dicds.page', 2) }}'">▶</button>
    </div>

    <div class="header">Interactive Table of Contents</div>

    <div class="content">
        <div class="toc-left">
            <div class="section">
                <div class="section-title">Introduction and Administration</div>
                <div class="toc-item">
                    <a href="{{ route('dicds.page', 6) }}">Getting Started</a>
                    <span class="page-num">6</span>
                </div>
                <div class="toc-item">
                    <a href="{{ route('dicds.page', 7) }}">New User Registration</a>
                    <span class="page-num">7</span>
                </div>
                <div class="toc-item">
                    <a href="{{ route('dicds.page', 8) }}">Access Level Request</a>
                    <span class="page-num">8</span>
                </div>
                <div class="toc-item">
                    <a href="{{ route('dicds.page', 9) }}">Request Acceptance</a>
                    <span class="page-num">9</span>
                </div>
                <div class="toc-item">
                    <a href="{{ route('dicds.page', 10) }}">User Role Administration</a>
                    <span class="page-num">10</span>
                </div>
                <div class="toc-item">
                    <a href="{{ route('dicds.page', 16) }}">Reset a Password</a>
                    <span class="page-num">16</span>
                </div>
            </div>

            <div class="section">
                <div class="section-title">Main Menu Selections</div>
                <div class="toc-item">
                    <a href="{{ route('dicds.page', 18) }}">Questions and Comments: Reporting Problems</a>
                    <span class="page-num">18</span>
                </div>
                <div class="toc-item">
                    <a href="{{ route('dicds.page', 19) }}">Welcome Screen</a>
                    <span class="page-num">19</span>
                </div>
            </div>

            <div class="section">
                <div class="section-title">Provider Menu</div>
                <div class="toc-item">
                    <a href="{{ route('dicds.page', 21) }}">Add a School</a>
                    <span class="page-num">21</span>
                </div>
                <div class="toc-item">
                    <a href="{{ route('dicds.page', 22) }}">Add a Course</a>
                    <span class="page-num">22</span>
                </div>
                <div class="toc-item">
                    <a href="{{ route('dicds.page', 23) }}">Maintain a School</a>
                    <span class="page-num">23</span>
                </div>
                <div class="toc-item">
                    <a href="{{ route('dicds.page', 27) }}">Add an Instructor</a>
                    <span class="page-num">27</span>
                </div>
                <div class="toc-item">
                    <a href="{{ route('dicds.page', 28) }}">Update an Instructor</a>
                    <span class="page-num">28</span>
                </div>
            </div>
        </div>

        <div class="toc-right">
            <div class="instructions">
                <h3>Click on a Page # to navigate to the specific section</h3>
                <p><strong>🏛️</strong> Click the state seal on any page to Return to the Table of Contents</p>
                <p>Click <span style="background: #ffd700; padding: 2px 8px;">▶</span> to view the next Page</p>
                <p>Click <span style="background: #ffd700; padding: 2px 8px;">◀</span> to return to the Previous Page</p>
            </div>

            <div class="section">
                <div class="section-title">Order Certificates</div>
                <div class="toc-item">
                    <a href="{{ route('dicds.page', 34) }}">Distribute Certificates</a>
                    <span class="page-num">34</span>
                </div>
                <div class="toc-item">
                    <a href="{{ route('dicds.page', 35) }}">Reclaim Certificates</a>
                    <span class="page-num">35</span>
                </div>
                <div class="toc-item">
                    <a href="{{ route('dicds.page', 36) }}">Maintain Certificates</a>
                    <span class="page-num">36</span>
                </div>
            </div>

            <div class="section">
                <div class="section-title">Web Inquiry Menu</div>
                <div class="toc-item">
                    <a href="{{ route('dicds.page', 38) }}">School's Certificates</a>
                    <span class="page-num">38</span>
                </div>
                <div class="toc-item">
                    <a href="{{ route('dicds.page', 39) }}">Certificate Report Menu</a>
                    <span class="page-num">39</span>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        This manual supersedes the previous User Manual dated Feb 2, 2005
    </div>

    <div class="version-info">
        DICDS Version 1.1 &nbsp;&nbsp; User Manual Update 2.0 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Page 2 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; September 2007
    </div>
</body>
</html>
