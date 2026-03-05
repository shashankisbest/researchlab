<?php
// Directory index - shows all uploaded files
// FLAG{uploads_index_accessible}

$path = __DIR__;
$files = scandir($path);

echo "<h1>Uploads Directory</h1>";
echo "<p>Path: $path</p>";
echo "<ul>";

foreach ($files as $file) {
    if ($file != '.' && $file != '..') {
        echo "<li><a href='$file'>$file</a></li>";
    }
}
echo "</ul>";

// Hidden flag
// FLAG{directory_index_flag}
?>