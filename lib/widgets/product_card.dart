import 'package:flutter/material.dart';
import '../models/product.dart';
import '../theme.dart';
import '../widgets/product_image_widget.dart';

/// A card widget for displaying product information in a list
///
/// Shows product image, name, price, stock level, and category with
/// action buttons for editing and deleting. Includes a low stock warning
/// indicator when stock is below 10 units.
class ProductCard extends StatelessWidget {
  final Product product;
  final VoidCallback onEdit;
  final VoidCallback onDelete;
  final VoidCallback? onTap;

  const ProductCard({
    super.key,
    required this.product,
    required this.onEdit,
    required this.onDelete,
    this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final bool isLowStock = product.stock < 10;

    return Card(
      elevation: 2,
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(12),
        child: Padding(
          padding: const EdgeInsets.all(12),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Product Image
              ClipRRect(
                borderRadius: BorderRadius.circular(8),
                child: ProductImageWidget(
                  imagePath: product.image,
                  width: 80,
                  height: 80,
                  fit: BoxFit.cover,
                ),
              ),
              const SizedBox(width: 12),

              // Product Info
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Product Name
                    Text(
                      product.name,
                      style: const TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                        color: AppTheme.textPrimary,
                      ),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 4),

                    // Category
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 8,
                        vertical: 2,
                      ),
                      decoration: BoxDecoration(
                        color: AppTheme.primary.withOpacity(0.1),
                        borderRadius: BorderRadius.circular(4),
                      ),
                      child: Text(
                        product.category,
                        style: const TextStyle(
                          fontSize: 12,
                          color: AppTheme.primary,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                    ),
                    const SizedBox(height: 8),

                    // Price
                    Text(
                      'EGP ${product.price.toStringAsFixed(2)}',
                      style: const TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                        color: AppTheme.primary,
                      ),
                    ),
                    const SizedBox(height: 4),

                    // Stock Level with Warning
                    Row(
                      children: [
                        Icon(
                          isLowStock ? Icons.warning : Icons.inventory_2,
                          size: 16,
                          color:
                              isLowStock ? AppTheme.warning : AppTheme.success,
                        ),
                        const SizedBox(width: 4),
                        Text(
                          'Stock: ${product.stock}',
                          style: TextStyle(
                            fontSize: 14,
                            color:
                                isLowStock
                                    ? AppTheme.warning
                                    : AppTheme.textSecondary,
                            fontWeight:
                                isLowStock
                                    ? FontWeight.bold
                                    : FontWeight.normal,
                          ),
                        ),
                        if (isLowStock) ...[
                          const SizedBox(width: 4),
                          const Text(
                            '(Low)',
                            style: TextStyle(
                              fontSize: 12,
                              color: AppTheme.warning,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ],
                      ],
                    ),
                  ],
                ),
              ),

              // Action Buttons
              Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  // Edit Button
                  IconButton(
                    icon: const Icon(Icons.edit, size: 20),
                    color: AppTheme.primary,
                    onPressed: onEdit,
                    tooltip: 'Edit Product',
                    padding: EdgeInsets.zero,
                    constraints: const BoxConstraints(
                      minWidth: 36,
                      minHeight: 36,
                    ),
                  ),
                  const SizedBox(height: 4),
                  // Delete Button
                  IconButton(
                    icon: const Icon(Icons.delete, size: 20),
                    color: AppTheme.danger,
                    onPressed: onDelete,
                    tooltip: 'Delete Product',
                    padding: EdgeInsets.zero,
                    constraints: const BoxConstraints(
                      minWidth: 36,
                      minHeight: 36,
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}
