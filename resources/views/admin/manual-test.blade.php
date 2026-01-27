<!DOCTYPE html>
<html>
<head>
    <title>Manual Generation Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .btn { 
            display: inline-block; 
            padding: 10px 20px; 
            margin: 10px; 
            background: #007bff; 
            color: white; 
            text-decoration: none; 
            border-radius: 5px; 
        }
        .btn:hover { background: #0056b3; }
    </style>
</head>
<body>
    <h1>Admin Manual Generation</h1>
    <p>Test the manual generation functionality:</p>
    
    <a href="{{ route('admin.manual.pdf') }}" class="btn">Download PDF Manual</a>
    <a href="{{ route('admin.manual.word') }}" class="btn">Download Word Manual</a>
    <a href="{{ route('admin.manual.preview') }}" class="btn">Preview Manual</a>
    
    <h2>Manual Generation Status</h2>
    <p>Click the buttons above to test manual generation. If there are any errors, they will be displayed here.</p>
</body>
</html>