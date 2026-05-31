<?php
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Get all products
$products = queryFirestoreCollection('products');

$updated_count = 0;
$errors = [];

foreach ($products as $product) {
    $productName = strtolower($product['name']);
    $imageFilename = '';
    
    // Map product names to image files
    if (strpos($productName, 'vest') !== false) {
        $imageFilename = 'vest.jpeg';
    } elseif (strpos($productName, 'ear') !== false || strpos($productName, 'muff') !== false) {
        $imageFilename = 'earmuffs.jpeg';
    } elseif (strpos($productName, 'jacket') !== false) {
        $imageFilename = 'jacket.jpeg';
    } elseif (strpos($productName, 'hard hat') !== false || strpos($productName, 'hardhat') !== false) {
        $imageFilename = 'hardhat.jpeg';
    } elseif (strpos($productName, 'helmet') !== false) {
        $imageFilename = 'helmet.jpeg';
    } elseif (strpos($productName, 'boots') !== false || strpos($productName, 'boot') !== false) {
        $imageFilename = 'boots.jpeg';
    }
    
    // Only update if we found a matching image and the product doesn't already have an image
    if (!empty($imageFilename) && empty($product['image'])) {
        $success = updateFirestoreDocument('products', $product['id'], [
            'image' => $imageFilename,
            'updatedAt' => date('Y-m-d H:i:s')
        ]);
        
        if ($success) {
            $updated_count++;
        } else {
            $errors[] = "Failed to update: " . $product['name'];
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Update Product Images</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
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
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #0f1d3f;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .btn:hover {
            background: #1a2d5a;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Product Images Update</h1>
        
        <?php if ($updated_count > 0): ?>
            <div class="success">
                ✓ Successfully updated <?= $updated_count ?> product(s) with image filenames.
            </div>
        <?php endif; ?>
        
        <?php if (count($errors) > 0): ?>
            <div class="error">
                <strong>Errors:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if ($updated_count === 0 && count($errors) === 0): ?>
            <div class="success">
                ✓ All products already have images assigned or no matching images found.
            </div>
        <?php endif; ?>
        
        <a href="inventory.php" class="btn">Go to Inventory</a>
        <a href="add_product.php" class="btn">Go to Add Product</a>
    </div>
</body>
</html>
