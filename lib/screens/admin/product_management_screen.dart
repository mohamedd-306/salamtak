import 'package:flutter/material.dart';
import '../../models/product.dart';
import '../../services/product_service.dart';
import '../../theme.dart';
import '../../widgets/product_card.dart';
import '../../constants/product_categories.dart';
import 'product_form_screen.dart';

/// Admin screen for managing products
///
/// Provides a complete interface for viewing, searching, filtering, adding,
/// editing, and deleting products. All operations sync in real-time with
/// Firebase Firestore.
class ProductManagementScreen extends StatefulWidget {
  const ProductManagementScreen({super.key});

  @override
  State<ProductManagementScreen> createState() =>
      _ProductManagementScreenState();
}

class _ProductManagementScreenState extends State<ProductManagementScreen> {
  final ProductService _productService = ProductService();
  final TextEditingController _searchController = TextEditingController();

  String _searchQuery = '';
  String _selectedCategory = 'All';

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  /// Filter products based on search query and selected category
  List<Product> _filterProducts(List<Product> products) {
    var filtered = products;

    // Filter by search query
    if (_searchQuery.isNotEmpty) {
      filtered =
          filtered.where((product) {
            return product.name.toLowerCase().contains(
              _searchQuery.toLowerCase(),
            );
          }).toList();
    }

    // Filter by category
    if (_selectedCategory != 'All') {
      filtered =
          filtered.where((product) {
            return product.category == _selectedCategory;
          }).toList();
    }

    return filtered;
  }

  /// Navigate to add product screen
  void _navigateToAddProduct() {
    Navigator.push(
      context,
      MaterialPageRoute(builder: (context) => const ProductFormScreen()),
    );
  }

  /// Navigate to edit product screen
  void _navigateToEditProduct(Product product) {
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (context) => ProductFormScreen(product: product),
      ),
    );
  }

  /// Show delete confirmation dialog
  Future<void> _showDeleteConfirmation(Product product) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder:
          (context) => AlertDialog(
            title: const Text('Delete Product'),
            content: Text(
              'Are you sure you want to delete "${product.name}"?\n\nThis action cannot be undone.',
            ),
            actions: [
              TextButton(
                onPressed: () => Navigator.pop(context, false),
                child: const Text('Cancel'),
              ),
              ElevatedButton(
                onPressed: () => Navigator.pop(context, true),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppTheme.danger,
                  foregroundColor: Colors.white,
                ),
                child: const Text('Delete'),
              ),
            ],
          ),
    );

    if (confirmed == true) {
      await _deleteProduct(product);
    }
  }

  /// Delete product and its image
  Future<void> _deleteProduct(Product product) async {
    try {
      // Show loading indicator
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Deleting product...'),
            duration: Duration(seconds: 1),
          ),
        );
      }

      // Delete image from Storage
      if (product.image.isNotEmpty) {
        await _productService.deleteProductImage(product.image);
      }

      // Delete product from Firestore
      await _productService.deleteProduct(product.id);

      // Show success message
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('${product.name} deleted successfully'),
            backgroundColor: AppTheme.success,
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    } catch (e) {
      // Show error message
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Error deleting product: $e'),
            backgroundColor: AppTheme.danger,
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Manage Products'),
        backgroundColor: AppTheme.primary,
        foregroundColor: Colors.white,
      ),
      body: Column(
        children: [
          // Search and Filter Section
          Container(
            padding: const EdgeInsets.all(16),
            color: Colors.grey[100],
            child: Column(
              children: [
                // Search Field
                TextField(
                  controller: _searchController,
                  decoration: InputDecoration(
                    hintText: 'Search products...',
                    prefixIcon: const Icon(Icons.search),
                    suffixIcon:
                        _searchQuery.isNotEmpty
                            ? IconButton(
                              icon: const Icon(Icons.clear),
                              onPressed: () {
                                _searchController.clear();
                                setState(() {
                                  _searchQuery = '';
                                });
                              },
                            )
                            : null,
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                      borderSide: BorderSide.none,
                    ),
                    filled: true,
                    fillColor: Colors.white,
                  ),
                  onChanged: (value) {
                    setState(() {
                      _searchQuery = value;
                    });
                  },
                ),
                const SizedBox(height: 12),

                // Category Filter
                Row(
                  children: [
                    const Text(
                      'Category:',
                      style: TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 14,
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: DropdownButtonFormField<String>(
                        value: _selectedCategory,
                        decoration: InputDecoration(
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(8),
                            borderSide: BorderSide.none,
                          ),
                          filled: true,
                          fillColor: Colors.white,
                          contentPadding: const EdgeInsets.symmetric(
                            horizontal: 12,
                            vertical: 8,
                          ),
                        ),
                        items: [
                          const DropdownMenuItem(
                            value: 'All',
                            child: Text('All Categories'),
                          ),
                          ...ProductCategories.all.map((category) {
                            return DropdownMenuItem(
                              value: category,
                              child: Text(category),
                            );
                          }),
                        ],
                        onChanged: (value) {
                          setState(() {
                            _selectedCategory = value ?? 'All';
                          });
                        },
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),

          // Products List
          Expanded(
            child: StreamBuilder<List<Product>>(
              stream: _productService.getAllProductsStream(),
              builder: (context, snapshot) {
                // Loading State
                if (snapshot.connectionState == ConnectionState.waiting) {
                  return const Center(child: CircularProgressIndicator());
                }

                // Error State
                if (snapshot.hasError) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(
                          Icons.error_outline,
                          size: 64,
                          color: AppTheme.danger,
                        ),
                        const SizedBox(height: 16),
                        Text(
                          'Error loading products',
                          style: TextStyle(
                            fontSize: 18,
                            color: Colors.grey[600],
                          ),
                        ),
                        const SizedBox(height: 8),
                        Text(
                          snapshot.error.toString(),
                          style: TextStyle(
                            fontSize: 14,
                            color: Colors.grey[500],
                          ),
                          textAlign: TextAlign.center,
                        ),
                      ],
                    ),
                  );
                }

                // Get and filter products
                final allProducts = snapshot.data ?? [];
                final filteredProducts = _filterProducts(allProducts);

                // Empty State
                if (allProducts.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.inventory_2_outlined,
                          size: 64,
                          color: Colors.grey[400],
                        ),
                        const SizedBox(height: 16),
                        Text(
                          'No products yet',
                          style: TextStyle(
                            fontSize: 18,
                            color: Colors.grey[600],
                          ),
                        ),
                        const SizedBox(height: 8),
                        Text(
                          'Tap the + button to add your first product',
                          style: TextStyle(
                            fontSize: 14,
                            color: Colors.grey[500],
                          ),
                        ),
                      ],
                    ),
                  );
                }

                // No Results State
                if (filteredProducts.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.search_off,
                          size: 64,
                          color: Colors.grey[400],
                        ),
                        const SizedBox(height: 16),
                        Text(
                          'No products found',
                          style: TextStyle(
                            fontSize: 18,
                            color: Colors.grey[600],
                          ),
                        ),
                        const SizedBox(height: 8),
                        Text(
                          'Try adjusting your search or filter',
                          style: TextStyle(
                            fontSize: 14,
                            color: Colors.grey[500],
                          ),
                        ),
                      ],
                    ),
                  );
                }

                // Products List
                return ListView.builder(
                  itemCount: filteredProducts.length,
                  padding: const EdgeInsets.only(bottom: 80),
                  itemBuilder: (context, index) {
                    final product = filteredProducts[index];
                    return ProductCard(
                      product: product,
                      onEdit: () => _navigateToEditProduct(product),
                      onDelete: () => _showDeleteConfirmation(product),
                    );
                  },
                );
              },
            ),
          ),
        ],
      ),

      // Floating Action Button to Add Product
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _navigateToAddProduct,
        backgroundColor: AppTheme.primary,
        foregroundColor: Colors.white,
        icon: const Icon(Icons.add),
        label: const Text('Add Product'),
      ),
    );
  }
}
