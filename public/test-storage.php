<?php

// Test storage access
$file = 'storage/course-media/1761175955_indian-man-7061278_640__1_.jpg';
$fullPath = __DIR__.'/'.$file;

echo "Testing storage access:\n";
echo 'File path: '.$file."\n";
echo 'Full path: '.$fullPath."\n";
echo 'File exists: '.(file_exists($fullPath) ? 'YES' : 'NO')."\n";
echo 'File readable: '.(is_readable($fullPath) ? 'YES' : 'NO')."\n";
echo 'File size: '.(file_exists($fullPath) ? filesize($fullPath) : 'N/A')." bytes\n";

if (file_exists($fullPath)) {
    echo 'File contents (first 100 bytes): '.bin2hex(substr(file_get_contents($fullPath), 0, 100))."\n";
}
