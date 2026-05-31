<?php
// Simple test to check if file uploads work
echo "<h2>File Upload Test</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['test_file'])) {
    echo "<h3>File Upload Attempt:</h3>";
    echo "<pre>";
    print_r($_FILES['test_file']);
    echo "</pre>";
    
    if ($_FILES['test_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/profiles/';
        $target = $upload_dir . 'test_' . time() . '.jpg';
        
        if (move_uploaded_file($_FILES['test_file']['tmp_name'], $target)) {
            echo "<p style='color: green;'>✓ File uploaded successfully to: $target</p>";
            echo "<img src='$target' style='max-width: 200px;'>";
        } else {
            echo "<p style='color: red;'>✗ Failed to move file</p>";
        }
    } else {
        echo "<p style='color: red;'>Upload error code: " . $_FILES['test_file']['error'] . "</p>";
    }
}
?>

<form method="POST" enctype="multipart/form-data">
    <input type="file" name="test_file" accept="image/*" required>
    <button type="submit">Upload Test</button>
</form>

<hr>
<h3>PHP Configuration:</h3>
<pre>
upload_max_filesize: <?= ini_get('upload_max_filesize') ?>

post_max_size: <?= ini_get('post_max_size') ?>

file_uploads: <?= ini_get('file_uploads') ? 'Enabled' : 'Disabled' ?>

upload_tmp_dir: <?= ini_get('upload_tmp_dir') ?: 'Default' ?>
</pre>

<h3>Directory Check:</h3>
<pre>
Directory exists: <?= is_dir('../uploads/profiles/') ? 'Yes' : 'No' ?>

Directory writable: <?= is_writable('../uploads/profiles/') ? 'Yes' : 'No' ?>

Directory path: <?= realpath('../uploads/profiles/') ?>
</pre>
