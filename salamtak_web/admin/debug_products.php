<?php
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Get all products
$products = queryFirestoreCollection('products');

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Debug Products</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#f5f5f5;}";
echo ".product{background:white;padding:15px;margin:10px 0;border-radius:5px;border-left:4px solid #0f1d3f;}";
echo ".field{margin:5px 0;}.label{font-weight:bold;color:#0f1d3f;}.value{color:#059669;}";
echo ".empty{color:#dc2626;font-style:italic;}</style></head><body>";

echo "<h1>Product Debug Information</h1>";
echo "<p>Total products: " . count($products) . "</p>";

foreach ($products as $product) {
    echo "<div class='product'>";
    echo "<div class='field'><span class='label'>ID:</span> <span class='value'>" . htmlspecialchars($product['id']) . "</span></div>";
    echo "<div class='field'><span class='label'>Name:</span> <span class='value'>" . htmlspecialchars($product['name']) . "</span></div>";
    
    $imageValue = $product['image'] ?? '';
    if (empty($imageValue)) {
        echo "<div class='field'><span class='label'>Image:</span> <span class='empty'>(empty/not set)</span></div>";
    } else {
        echo "<div class='field'><span class='label'>Image:</span> <span class='value'>" . htmlspecialchars($imageValue) . "</span></div>";
    }
    
    $imageUrl = getProductImageUrl($product);
    echo "<div class='field'><span class='label'>Computed URL:</span> <span class='value'>" . htmlspecialchars($imageUrl) . "</span></div>";
    
    // Check if file exists
    $filePath = str_replace('../', '', $imageUrl);
    $fullPath = __DIR__ . '/../' . $filePath;
    $fileExists = file_exists($fullPath);
    echo "<div class='field'><span class='label'>File exists:</span> <span class='" . ($fileExists ? 'value' : 'empty') . "'>" . ($fileExists ? 'YES' : 'NO') . "</span></div>";
    echo "<div class='field'><span class='label'>Full path checked:</span> <span style='color:#6b7280;font-size:11px;'>" . htmlspecialchars($fullPath) . "</span></div>";
    
    echo "<div class='field'><span class='label'>Image preview:</span><br>";
    echo "<img src='" . htmlspecialchars($imageUrl) . "' style='max-width:100px;max-height:100px;border:2px solid #e5e7eb;margin-top:5px;' onerror=\"this.style.border='2px solid red';this.alt='FAILED TO LOAD'\">";
    echo "</div>";
    
    echo "</div>";
}

echo "<br><a href='inventory.php' style='display:inline-block;padding:10px 20px;background:#0f1d3f;color:white;text-decoration:none;border-radius:5px;'>Back to Inventory</a>";
echo "</body></html>";
?>
