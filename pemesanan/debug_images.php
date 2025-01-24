<?php
// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the image directory exists and is readable
$imageDir = __DIR__ . '/image';
echo "Image directory path: " . $imageDir . "<br>";
echo "Directory exists: " . (is_dir($imageDir) ? 'Yes' : 'No') . "<br>";
echo "Directory readable: " . (is_readable($imageDir) ? 'Yes' : 'No') . "<br>";

// List all files in the image directory
echo "<h3>Files in image directory:</h3>";
if ($handle = opendir($imageDir)) {
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != "..") {
            $fullPath = $imageDir . '/' . $file;
            echo "File: " . htmlspecialchars($file) . "<br>";
            echo "- Exists: " . (file_exists($fullPath) ? 'Yes' : 'No') . "<br>";
            echo "- Readable: " . (is_readable($fullPath) ? 'Yes' : 'No') . "<br>";
            echo "- File size: " . filesize($fullPath) . " bytes<br>";
            echo "- MIME type: " . mime_content_type($fullPath) . "<br><br>";
        }
    }
    closedir($handle);
}
?>
