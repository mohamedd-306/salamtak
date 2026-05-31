<?php
$_SESSION = []; // Bypass session
require_once '../config.php';

$products = queryFirestoreCollection('products');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Test Images - User Folder</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .product { background: white; padding: 20px; margin: 15px 0; border-radius: 10px; }
        img { max-width: 150px; border: 2px solid #ccc; margin: 5px; }
        .success { border-color: green !important; }
        .failed { border-color: red !important; }
    </style>
</head>
<body>
    <h1>Image Test from /user/ Folder</h1>
    
    <?php foreach ($products as $product): ?>
        <?php
        $imageUrl = getProductImageUrl($product);
        $imageField = $product['image'] ?? '(empty)';
        ?>
        <div class="product">
            <h3><?= htmlspecialchars($product['name']) ?></h3>
            <p><strong>Image field:</strong> <?= htmlspecialchars($imageField) ?></p>
            <p><strong>Computed URL:</strong> <?= htmlspecialchars($imageUrl) ?></p>
            
            <div>
                <p><strong>Test 1: getProductImageUrl() result</strong></p>
                <img src="<?= htmlspecialchars($imageUrl) ?>?v=<?= time() ?>" 
                     alt="<?= htmlspecialchars($product['name']) ?>"
                     onload="this.className='success'"
                     onerror="this.className='failed'; this.alt='FAILED TO LOAD'">
            </div>
            
            <div>
                <p><strong>Test 2: Direct ../assets/products/ path</strong></p>
                <img src="../assets/products/<?= htmlspecialchars($imageField) ?>?v=<?= time() ?>" 
                     alt="<?= htmlspecialchars($product['name']) ?>"
                     onload="this.className='success'"
                     onerror="this.className='failed'; this.alt='FAILED TO LOAD'">
            </div>
        </div>
    <?php endforeach; ?>
    
    <hr>
    <p><a href="products.php">Go to Products Page</a></p>
</body>
</html>
