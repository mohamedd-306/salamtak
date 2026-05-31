<?php
// Clear PHP OpCache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✓ OpCache cleared!<br>";
} else {
    echo "✗ OpCache not enabled<br>";
}

// Clear session
session_start();
session_destroy();
echo "✓ Session cleared!<br>";

echo "<hr>";
echo "<h2>Now try these links:</h2>";
echo "<a href='user/products.php' style='display:block; margin:10px 0; padding:10px; background:#4caf50; color:white; text-decoration:none; border-radius:5px;'>View Products (No Login Required)</a>";
echo "<a href='login.php' style='display:block; margin:10px 0; padding:10px; background:#2196f3; color:white; text-decoration:none; border-radius:5px;'>Login</a>";
?>
