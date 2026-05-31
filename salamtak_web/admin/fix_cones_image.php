<?php
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Get all products
$products = queryFirestoreCollection('products');

$cones_products = [];
$updated = [];
$errors = [];

// Find all products that might be cones
foreach ($products as $product) {
    $productName = $product['name'];
    $productNameLower = strtolower($productName);
    
    // Check if product name contains "cone" or "traffic"
    if (strpos($productNameLower, 'cone') !== false || strpos($productNameLower, 'traffic') !== false) {
        $cones_products[] = [
            'id' => $product['id'],
            'name' => $productName,
            'current_image' => $product['image'] ?? '(empty)'
        ];
        
        // Force update with the cones image
        $success = updateFirestoreDocument('products', $product['id'], [
            'image' => 'product_1778950426_6a08a11a95eb7.jpeg',
            'updatedAt' => date('Y-m-d H:i:s')
        ]);
        
        if ($success) {
            $updated[] = $productName;
        } else {
            $errors[] = $productName;
        }
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Fix Cones Image</title>
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        th {
            background: #f9fafb;
            font-weight: 700;
            color: #374151;
        }
        .image-preview {
            max-width: 80px;
            max-height: 80px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Fix Cones Product Image</h1>
        
        <?php if (count($cones_products) === 0): ?>
            <div class="info">
                ℹ No products found with "cone" or "traffic" in the name.
            </div>
        <?php else: ?>
            <?php if (count($updated) > 0): ?>
                <div class="success">
                    <strong>✓ Successfully updated <?= count($updated) ?> product(s):</strong>
                    <ul>
                        <?php foreach ($updated as $name): ?>
                            <li><?= htmlspecialchars($name) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <?php if (count($errors) > 0): ?>
                <div class="error">
                    <strong>✗ Failed to update <?= count($errors) ?> product(s):</strong>
                    <ul>
                        <?php foreach ($errors as $name): ?>
                            <li><?= htmlspecialchars($name) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <h3>Cones Products Found:</h3>
            <table>
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Product ID</th>
                        <th>Previous Image</th>
                        <th>New Image</th>
                        <th>Preview</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cones_products as $product): ?>
                        <tr>
                            <td><?= htmlspecialchars($product['name']) ?></td>
                            <td style="font-family: monospace; font-size: 11px;"><?= htmlspecialchars($product['id']) ?></td>
                            <td><?= htmlspecialchars($product['current_image']) ?></td>
                            <td style="color: #059669; font-weight: 600;">product_1778950426_6a08a11a95eb7.jpeg</td>
                            <td>
                                <img src="../assets/products/product_1778950426_6a08a11a95eb7.jpeg" 
                                     class="image-preview" 
                                     alt="Cones"
                                     onerror="this.alt='Failed to load'">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        
        <div style="margin-top: 30px;">
            <a href="../user/products.php" class="btn" style="background: #059669;">View User Products Page</a>
            <a href="inventory.php" class="btn">View Inventory</a>
            <a href="debug_products.php" class="btn">Debug All Products</a>
        </div>
    </div>
</body>
</html>
