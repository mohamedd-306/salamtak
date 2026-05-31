<?php
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

$results = [];

// Get all products
$products = queryFirestoreCollection('products');

foreach ($products as $product) {
    $productName = strtolower($product['name']);
    $productId = $product['id'];
    $currentImage = $product['image'] ?? '';
    
    $results[] = [
        'id' => $productId,
        'name' => $product['name'],
        'name_lower' => $productName,
        'current_image' => $currentImage,
        'action' => 'checking...'
    ];
}

// Now let's force update specific products by their exact names
$updates = [
    'Safety Work Boots - Steel Toe' => 'boots.jpeg',
    'safety helmet-carbon fiber' => 'helmet.jpeg',
    'Hard Hat' => 'hardhat.jpeg',
    'Safety Jacket' => 'jacket.jpeg',
    'Safety Vest' => 'vest.jpeg',
    'Ear Muffs' => 'earmuffs.jpeg',
    'Traffic Cones' => 'product_1778950426_6a08a11a95eb7.jpeg',
    'Safety Cones' => 'product_1778950426_6a08a11a95eb7.jpeg',
    'cones' => 'product_1778950426_6a08a11a95eb7.jpeg'
];

$updated = [];
$errors = [];

foreach ($products as $product) {
    $productName = $product['name'];
    $productId = $product['id'];
    
    // Check if this product name matches any in our update list
    if (isset($updates[$productName])) {
        $imageFilename = $updates[$productName];
        
        // Force update regardless of current value
        $success = updateFirestoreDocument('products', $productId, [
            'image' => $imageFilename,
            'updatedAt' => date('Y-m-d H:i:s')
        ]);
        
        if ($success) {
            $updated[] = $productName . ' → ' . $imageFilename;
        } else {
            $errors[] = 'Failed: ' . $productName;
        }
    } else {
        // Try fuzzy matching for products not in exact list
        $productNameLower = strtolower($productName);
        $imageFilename = '';
        
        if (strpos($productNameLower, 'boot') !== false) {
            $imageFilename = 'boots.jpeg';
        } elseif (strpos($productNameLower, 'helmet') !== false) {
            $imageFilename = 'helmet.jpeg';
        } elseif (strpos($productNameLower, 'hard hat') !== false || strpos($productNameLower, 'hardhat') !== false) {
            $imageFilename = 'hardhat.jpeg';
        } elseif (strpos($productNameLower, 'jacket') !== false) {
            $imageFilename = 'jacket.jpeg';
        } elseif (strpos($productNameLower, 'vest') !== false) {
            $imageFilename = 'vest.jpeg';
        } elseif (strpos($productNameLower, 'ear') !== false || strpos($productNameLower, 'muff') !== false) {
            $imageFilename = 'earmuffs.jpeg';
        } elseif (strpos($productNameLower, 'cone') !== false || strpos($productNameLower, 'traffic') !== false) {
            $imageFilename = 'product_1778950426_6a08a11a95eb7.jpeg';
        }
        
        if (!empty($imageFilename)) {
            $success = updateFirestoreDocument('products', $productId, [
                'image' => $imageFilename,
                'updatedAt' => date('Y-m-d H:i:s')
            ]);
            
            if ($success) {
                $updated[] = $productName . ' → ' . $imageFilename . ' (fuzzy match)';
            } else {
                $errors[] = 'Failed: ' . $productName;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Force Fix Images</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #0f1d3f;
            margin-bottom: 20px;
        }
        .success {
            background: #d1fae5;
            color: #065f46;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .info {
            background: #dbeafe;
            color: #1e40af;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #0f1d3f;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
            margin-right: 10px;
        }
        .btn:hover {
            background: #1a2d5a;
        }
        ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        li {
            margin: 5px 0;
        }
        .debug-section {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
        }
        .debug-section h3 {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .debug-info {
            background: #f9fafb;
            padding: 10px;
            border-radius: 5px;
            font-family: monospace;
            font-size: 12px;
            color: #374151;
            max-height: 300px;
            overflow-y: auto;
            overflow-x: auto;
            word-wrap: break-word;
        }
        .product-debug-item {
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e5e7eb;
        }
        .product-debug-item:last-child {
            border-bottom: none;
        }
        .debug-label {
            font-weight: bold;
            color: #4b5563;
            display: inline-block;
            min-width: 120px;
        }
        .debug-value {
            color: #059669;
            word-break: break-all;
        }
        .debug-empty {
            color: #dc2626;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Force Fix Product Images</h1>
        
        <?php if (count($updated) > 0): ?>
            <div class="success">
                <strong>✓ Successfully updated <?= count($updated) ?> product(s):</strong>
                <ul>
                    <?php foreach ($updated as $update): ?>
                        <li><?= htmlspecialchars($update) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if (count($errors) > 0): ?>
            <div class="error">
                <strong>✗ Errors (<?= count($errors) ?>):</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if (count($updated) === 0 && count($errors) === 0): ?>
            <div class="info">
                ℹ No products were updated. This might mean:
                <ul>
                    <li>All products already have images</li>
                    <li>No products matched the update criteria</li>
                    <li>There are no products in the database</li>
                </ul>
            </div>
        <?php endif; ?>
        
        <div class="debug-section">
            <h3>DEBUG: All Products Found</h3>
            <div class="debug-info">
                <?php foreach ($results as $result): ?>
                    <div class="product-debug-item">
                        <div><span class="debug-label">Name:</span> <span class="debug-value"><?= htmlspecialchars($result['name']) ?></span></div>
                        <div><span class="debug-label">ID:</span> <span class="debug-value"><?= htmlspecialchars($result['id']) ?></span></div>
                        <div><span class="debug-label">Current Image:</span> 
                            <?php if (empty($result['current_image'])): ?>
                                <span class="debug-empty">(empty)</span>
                            <?php else: ?>
                                <span class="debug-value"><?= htmlspecialchars($result['current_image']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <a href="inventory.php" class="btn">Go to Inventory</a>
        <a href="debug_products.php" class="btn">View Debug Info</a>
        <a href="force_fix_images.php" class="btn" style="background: #059669;">Run Again</a>
    </div>
</body>
</html>
