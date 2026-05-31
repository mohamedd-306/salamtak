<?php
echo "<h2>Upload Diagnostics</h2>";

// Check PHP settings
echo "<h3>PHP Upload Settings:</h3>";
echo "file_uploads: " . (ini_get('file_uploads') ? 'ON' : 'OFF') . "<br>";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "post_max_size: " . ini_get('post_max_size') . "<br>";
echo "max_file_uploads: " . ini_get('max_file_uploads') . "<br>";
echo "upload_tmp_dir: " . (ini_get('upload_tmp_dir') ?: 'System Default') . "<br>";

// Check directory
$upload_dir = '../uploads/profiles/';
echo "<h3>Directory Check:</h3>";
echo "Path: " . realpath($upload_dir) . "<br>";
echo "Exists: " . (is_dir($upload_dir) ? 'YES' : 'NO') . "<br>";
echo "Writable: " . (is_writable($upload_dir) ? 'YES' : 'NO') . "<br>";
echo "Readable: " . (is_readable($upload_dir) ? 'YES' : 'NO') . "<br>";

// Try to create a test file
$test_file = $upload_dir . 'test_' . time() . '.txt';
$write_test = @file_put_contents($test_file, 'test');
echo "Can write file: " . ($write_test !== false ? 'YES' : 'NO') . "<br>";
if ($write_test !== false) {
    echo "Test file created: $test_file<br>";
    @unlink($test_file);
}

// Check POST data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>POST Data Received:</h3>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    echo "<h3>FILES Data:</h3>";
    echo "<pre>";
    print_r($_FILES);
    echo "</pre>";
}
?>

<h3>Test Form:</h3>
<form method="POST" enctype="multipart/form-data" style="border: 2px solid #ccc; padding: 20px; margin: 20px 0;">
    <p>Select an image file:</p>
    <input type="file" name="test_image" accept="image/*" required style="margin: 10px 0;">
    <br>
    <button type="submit" style="padding: 10px 20px; background: #4CAF50; color: white; border: none; cursor: pointer;">Upload Test</button>
</form>

<p><a href="account.php">← Back to Account</a></p>
