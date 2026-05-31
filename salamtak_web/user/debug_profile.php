<?php
require_once '../config.php';

if (!isLoggedIn() || isAdmin()) {
    die('Please login first');
}

$user_id = $_SESSION['user_id'];
$user = getFirestoreDocument('users', $user_id);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Profile Debug</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .debug-box { background: #ffffcc; border: 3px solid #ff0000; padding: 20px; margin: 20px 0; }
        .debug-box h2 { color: #ff0000; margin-top: 0; }
        .info { margin: 10px 0; padding: 10px; background: white; }
        .label { font-weight: bold; color: #333; }
        .value { color: #0066cc; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>Profile Picture Debug Information</h1>
    
    <div class="debug-box">
        <h2>User Information</h2>
        <div class="info">
            <span class="label">User ID:</span> 
            <span class="value"><?= htmlspecialchars($user_id) ?></span>
        </div>
        <div class="info">
            <span class="label">Name:</span> 
            <span class="value"><?= htmlspecialchars($user['name'] ?? 'Not set') ?></span>
        </div>
    </div>
    
    <div class="debug-box">
        <h2>Profile Picture Information</h2>
        <div class="info">
            <span class="label">Profile Picture Path in Database:</span><br>
            <span class="value"><?= htmlspecialchars($user['profilePicture'] ?? 'NOT SET IN DATABASE') ?></span>
        </div>
        
        <?php if (!empty($user['profilePicture'])): ?>
            <?php 
            $relative_path = $user['profilePicture'];
            $full_path = '../' . $relative_path;
            $file_exists = file_exists($full_path);
            ?>
            
            <div class="info">
                <span class="label">Relative Path:</span><br>
                <span class="value"><?= htmlspecialchars($relative_path) ?></span>
            </div>
            
            <div class="info">
                <span class="label">Full Server Path:</span><br>
                <span class="value"><?= htmlspecialchars($full_path) ?></span>
            </div>
            
            <div class="info">
                <span class="label">File Exists on Server:</span><br>
                <span class="<?= $file_exists ? 'success' : 'error' ?>">
                    <?= $file_exists ? '✓ YES - File found!' : '✗ NO - File not found!' ?>
                </span>
            </div>
            
            <?php if ($file_exists): ?>
                <div class="info">
                    <span class="label">File Size:</span><br>
                    <span class="value"><?= number_format(filesize($full_path) / 1024, 2) ?> KB</span>
                </div>
                
                <div class="info">
                    <span class="label">Image Preview:</span><br>
                    <img src="../<?= htmlspecialchars($relative_path) ?>?v=<?= time() ?>" style="max-width: 200px; border: 2px solid #333; margin-top: 10px;">
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="info">
                <span class="error">No profile picture set in database</span>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="debug-box">
        <h2>Upload Directory Information</h2>
        <div class="info">
            <span class="label">Upload Directory Path:</span><br>
            <span class="value"><?= realpath('../uploads/profiles/') ?></span>
        </div>
        <div class="info">
            <span class="label">Directory Writable:</span><br>
            <span class="<?= is_writable('../uploads/profiles/') ? 'success' : 'error' ?>">
                <?= is_writable('../uploads/profiles/') ? '✓ YES' : '✗ NO' ?>
            </span>
        </div>
        <div class="info">
            <span class="label">Files in Directory:</span><br>
            <?php
            $files = glob('../uploads/profiles/*');
            if ($files) {
                echo "<ul>";
                foreach ($files as $file) {
                    if (is_file($file)) {
                        echo "<li>" . basename($file) . " (" . number_format(filesize($file) / 1024, 2) . " KB)</li>";
                    }
                }
                echo "</ul>";
            } else {
                echo "<span class='error'>No files found</span>";
            }
            ?>
        </div>
    </div>
    
    <p><a href="account.php">← Back to Account Page</a></p>
</body>
</html>
