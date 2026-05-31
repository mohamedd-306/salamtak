<?php
require_once '../config.php';

echo "<h1>Products Page Test</h1>";
echo "<p>Is logged in: " . (isLoggedIn() ? 'YES' : 'NO') . "</p>";
echo "<p>Is admin: " . (isAdmin() ? 'YES' : 'NO') . "</p>";

$isLoggedIn = isLoggedIn();
$isUserLoggedIn = $isLoggedIn && !isAdmin();

echo "<p>Is user logged in (not admin): " . ($isUserLoggedIn ? 'YES' : 'NO') . "</p>";

if ($isUserLoggedIn) {
    echo "<p style='color: green;'>✓ You are logged in as a user - You can add to cart</p>";
} else {
    echo "<p style='color: red;'>✗ You are NOT logged in - You should see 'Login to Add' button</p>";
}

echo "<hr>";
echo "<a href='products.php'>Go to Products Page</a> | ";
echo "<a href='../login.php'>Go to Login</a> | ";
echo "<a href='../logout.php'>Logout</a>";
?>
