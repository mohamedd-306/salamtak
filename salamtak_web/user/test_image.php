<?php
require_once '../config.php';

if (!isLoggedIn() || isAdmin()) {
    die('Please login first');
}

$user_id = $_SESSION['user_id'];
$user = getFirestoreDocument('users', $user_id);

echo "<h1>Image Display Test</h1>";
echo "<hr>";

echo "<h2>1. Database Info</h2>";
echo "<strong>User ID:</strong> " . htmlspecialchars($user_id) . "<br>";
echo "<strong>Profile Picture in DB:</strong> " . htmlspecialchars($user['profilePicture'] ?? 'NOT SET') . "<br>";
echo "<hr>";

echo "<h2>2. Path Construction</h2>";
$dbPath = $user['profilePicture'] ?? '';
echo "<strong>Path from DB:</strong> " . htmlspecialchars($dbPath) . "<br>";

$fullPath = '../' . $dbPath;
echo "<strong>Full path (../ + DB path):</strong> " . htmlspecialchars($fullPath) . "<br>";

echo "<strong>File exists?</strong> " . (file_exists($fullPath) ? 'YES ✓' : 'NO ✗') . "<br>";
echo "<hr>";

echo "<h2>3. Image Display Test</h2>";
if (!empty($dbPath) && file_exists($fullPath)) {
    $imgSrc = '../' . $dbPath . '?v=' . time();
    echo "<strong>Image src attribute:</strong> " . htmlspecialchars($imgSrc) . "<br><br>";
    echo "<img src='" . htmlspecialchars($imgSrc) . "' style='width: 200px; height: 200px; border: 3px solid red; object-fit: cover;'>";
    echo "<br><br><strong>If you see the image above, it works!</strong>";
} else {
    echo "<strong style='color: red;'>Cannot display image - file not found or path not set</strong>";
}
echo "<hr>";

echo "<h2>4. All Files in Upload Directory</h2>";
$files = glob('../uploads/profiles/*');
if ($files) {
    echo "<ul>";
    foreach ($files as $file) {
        if (is_file($file)) {
            $basename = basename($file);
            echo "<li>" . htmlspecialchars($basename);
            // Try to display each file
            echo "<br><img src='../uploads/profiles/" . htmlspecialchars($basename) . "' style='width: 100px; height: 100px; object-fit: cover; border: 1px solid #ccc;'>";
            echo "</li>";
        }
    }
    echo "</ul>";
} else {
    echo "<p>No files found</p>";
}

echo "<hr>";
echo "<p><a href='account.php'>← Back to Account</a></p>";
?>
