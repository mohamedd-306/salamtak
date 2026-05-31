<?php
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Get all products
$products = queryFirestoreCollection('products');

// Define category mappings
$category_map = [
    'boots' => 'Foot Protection',
    'helmet' => 'Head Protection',
    'hardhat' => 'Head Protection',
    'hard hat' => 'Head Protection',
    'jacket' => 'Safety Wear',
    'vest' => 'Safety Wear',
    'earmuffs' => 'Head Protection',
    'ear muffs' => 'Head Protection',
    'cone' => 'Safety Equipment',
    'traffic' => 'Safety Equipment',
];

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Check Product Categories</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1000px;
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
        .category-ok {
            color: #059669;
            font-weight: 600;
        }
        .category-missing {
            color: #dc2626;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Product Categories Check</h1>
        <p>Checking all product categories for filter compatibility</p>
        
        <table>
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Current Category</th>
                    <th>Status</th>
                    <th>Data Attribute</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <?php
                    $category = $product['category'] ?? 'N/A';
                    $dataCategory = strtolower(str_replace(' ', '-', $category));
                    $hasCategory = !empty($category) && $category !== 'N/A';
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($product['name']) ?></td>
                        <td><?= htmlspecialchars($category) ?></td>
                        <td class="<?= $hasCategory ? 'category-ok' : 'category-missing' ?>">
                            <?= $hasCategory ? '✓ Has Category' : '✗ Missing Category' ?>
                        </td>
                        <td><code>data-category="<?= htmlspecialchars($dataCategory) ?>"</code></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div style="margin-top: 30px; padding: 20px; background: #dbeafe; border-radius: 8px;">
            <h3 style="color: #1e40af; margin-top: 0;">Filter Values:</h3>
            <ul>
                <li><strong>all</strong> - Shows all products</li>
                <li><strong>safety-wear</strong> - Matches category "Safety Wear"</li>
                <li><strong>head-protection</strong> - Matches category "Head Protection"</li>
                <li><strong>footwear</strong> - Matches category "Footwear" or "Foot Protection"</li>
            </ul>
        </div>
        
        <a href="../user/products.php" class="btn" style="background: #059669;">Test Filters on Products Page</a>
        <a href="inventory.php" class="btn">Back to Inventory</a>
    </div>
</body>
</html>
