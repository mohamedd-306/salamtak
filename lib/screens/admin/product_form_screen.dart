import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import '../../models/product.dart';
import '../../services/product_service.dart';
import '../../theme.dart';
import '../../constants/product_categories.dart';

/// Screen for creating new products or editing existing ones
///
/// Provides a form with validation for all product fields including
/// image upload functionality. Handles both create and update operations.
class ProductFormScreen extends StatefulWidget {
  final Product? product; // null for add, non-null for edit

  const ProductFormScreen({super.key, this.product});

  @override
  State<ProductFormScreen> createState() => _ProductFormScreenState();
}

class _ProductFormScreenState extends State<ProductFormScreen> {
  final _formKey = GlobalKey<FormState>();
  final ProductService _productService = ProductService();
  final ImagePicker _imagePicker = ImagePicker();

  // Form Controllers
  late final TextEditingController _nameController;
  late final TextEditingController _descriptionController;
  late final TextEditingController _priceController;
  late final TextEditingController _stockController;

  // Form State
  String _selectedCategory = ProductCategories.defaultCategory;
  XFile? _selectedImage;
  String? _existingImageUrl;
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();

    // Initialize controllers
    _nameController = TextEditingController(text: widget.product?.name ?? '');
    _descriptionController = TextEditingController(
      text: widget.product?.description ?? '',
    );
    _priceController = TextEditingController(
      text: widget.product?.price.toStringAsFixed(2) ?? '',
    );
    _stockController = TextEditingController(
      text: widget.product?.stock.toString() ?? '',
    );

    // Set existing values for edit mode
    if (widget.product != null) {
      _selectedCategory = widget.product!.category;
      _existingImageUrl = widget.product!.image;
    }
  }

  @override
  void dispose() {
    _nameController.dispose();
    _descriptionController.dispose();
    _priceController.dispose();
    _stockController.dispose();
    super.dispose();
  }

  // ==================== Image Handling ====================

  /// Pick image from gallery
  Future<void> _pickImage() async {
    try {
      final XFile? image = await _imagePicker.pickImage(
        source: ImageSource.gallery,
        maxWidth: 1920,
        imageQuality: 85,
      );

      if (image != null) {
        // Validate image
        final validationError = _productService.validateImage(image);
        if (validationError != null) {
          _showError(validationError);
          return;
        }

        // Validate image size
        final sizeError = await _productService.validateImageSize(image);
        if (sizeError != null) {
          _showError(sizeError);
          return;
        }

        setState(() {
          _selectedImage = image;
        });
      }
    } catch (e) {
      _showError('Failed to pick image: $e');
    }
  }

  // ==================== Form Validation ====================

  String? _validateName(String? value) {
    if (value == null || value.trim().isEmpty) {
      return 'Product name is required';
    }
    if (value.length > 100) {
      return 'Name must be 100 characters or less';
    }
    return null;
  }

  String? _validateDescription(String? value) {
    if (value == null || value.trim().isEmpty) {
      return 'Description is required';
    }
    if (value.length > 500) {
      return 'Description must be 500 characters or less';
    }
    return null;
  }

  String? _validatePrice(String? value) {
    if (value == null || value.trim().isEmpty) {
      return 'Price is required';
    }

    final price = double.tryParse(value);
    if (price == null) {
      return 'Please enter a valid number';
    }

    if (price <= 0) {
      return 'Price must be greater than 0';
    }

    // Check for max 2 decimal places
    if (value.contains('.') && value.split('.')[1].length > 2) {
      return 'Price can have maximum 2 decimal places';
    }

    return null;
  }

  String? _validateStock(String? value) {
    if (value == null || value.trim().isEmpty) {
      return 'Stock is required';
    }

    final stock = int.tryParse(value);
    if (stock == null) {
      return 'Please enter a valid number';
    }

    if (stock < 0) {
      return 'Stock cannot be negative';
    }

    return null;
  }

  String? _validateImage() {
    if (widget.product == null && _selectedImage == null) {
      return 'Please select a product image';
    }
    return null;
  }

  // ==================== Save Product ====================

  Future<void> _saveProduct() async {
    // Dismiss keyboard
    FocusScope.of(context).unfocus();

    // Validate form
    if (!_formKey.currentState!.validate()) {
      return;
    }

    // Validate image
    final imageError = _validateImage();
    if (imageError != null) {
      _showError(imageError);
      return;
    }

    setState(() {
      _isLoading = true;
    });

    try {
      // Handle image upload
      String imageUrl = _existingImageUrl ?? '';

      if (_selectedImage != null) {
        // Upload new image
        final uploadedUrl = await _productService.uploadProductImage(
          _selectedImage!,
        );
        if (uploadedUrl == null) {
          throw Exception('Failed to upload image');
        }
        imageUrl = uploadedUrl;

        // Delete old image if updating
        if (widget.product != null &&
            _existingImageUrl != null &&
            _existingImageUrl!.isNotEmpty) {
          await _productService.deleteProductImage(_existingImageUrl!);
        }
      }

      // Create product object
      final now = DateTime.now();
      final product = Product(
        id: widget.product?.id ?? '',
        name: _nameController.text.trim(),
        description: _descriptionController.text.trim(),
        price: double.parse(_priceController.text),
        image: imageUrl,
        stock: int.parse(_stockController.text),
        category: _selectedCategory,
        createdAt: widget.product?.createdAt ?? now,
        updatedAt: now,
      );

      // Save to Firestore
      if (widget.product == null) {
        // Create new product
        await _productService.createProduct(product);
        _showSuccess('Product created successfully');
      } else {
        // Update existing product
        await _productService.updateProduct(widget.product!.id, product);
        _showSuccess('Product updated successfully');
      }

      // Navigate back
      if (mounted) {
        Navigator.pop(context);
      }
    } catch (e) {
      _showError('Failed to save product: $e');
    } finally {
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
    }
  }

  // ==================== UI Helpers ====================

  void _showError(String message) {
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(message),
          backgroundColor: AppTheme.danger,
          behavior: SnackBarBehavior.floating,
        ),
      );
    }
  }

  void _showSuccess(String message) {
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(message),
          backgroundColor: AppTheme.success,
          behavior: SnackBarBehavior.floating,
        ),
      );
    }
  }

  // ==================== Build UI ====================

  @override
  Widget build(BuildContext context) {
    final isEditMode = widget.product != null;

    return Scaffold(
      appBar: AppBar(
        title: Text(isEditMode ? 'Edit Product' : 'Add Product'),
        backgroundColor: AppTheme.primary,
        foregroundColor: Colors.white,
      ),
      body: Stack(
        children: [
          // Form
          SingleChildScrollView(
            padding: const EdgeInsets.all(16),
            child: Form(
              key: _formKey,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  // Image Picker
                  _buildImagePicker(),
                  const SizedBox(height: 24),

                  // Product Name
                  TextFormField(
                    controller: _nameController,
                    decoration: const InputDecoration(
                      labelText: 'Product Name *',
                      hintText: 'Enter product name',
                      border: OutlineInputBorder(),
                      prefixIcon: Icon(Icons.shopping_bag),
                    ),
                    validator: _validateName,
                    textCapitalization: TextCapitalization.words,
                  ),
                  const SizedBox(height: 16),

                  // Description
                  TextFormField(
                    controller: _descriptionController,
                    decoration: const InputDecoration(
                      labelText: 'Description *',
                      hintText: 'Enter product description',
                      border: OutlineInputBorder(),
                      prefixIcon: Icon(Icons.description),
                    ),
                    validator: _validateDescription,
                    maxLines: 3,
                    textCapitalization: TextCapitalization.sentences,
                  ),
                  const SizedBox(height: 16),

                  // Price and Stock Row
                  Row(
                    children: [
                      // Price
                      Expanded(
                        child: TextFormField(
                          controller: _priceController,
                          decoration: const InputDecoration(
                            labelText: 'Price (EGP) *',
                            hintText: '0.00',
                            border: OutlineInputBorder(),
                            prefixIcon: Icon(Icons.attach_money),
                          ),
                          validator: _validatePrice,
                          keyboardType: const TextInputType.numberWithOptions(
                            decimal: true,
                          ),
                        ),
                      ),
                      const SizedBox(width: 16),

                      // Stock
                      Expanded(
                        child: TextFormField(
                          controller: _stockController,
                          decoration: const InputDecoration(
                            labelText: 'Stock *',
                            hintText: '0',
                            border: OutlineInputBorder(),
                            prefixIcon: Icon(Icons.inventory),
                          ),
                          validator: _validateStock,
                          keyboardType: TextInputType.number,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),

                  // Category Dropdown
                  DropdownButtonFormField<String>(
                    value: _selectedCategory,
                    decoration: const InputDecoration(
                      labelText: 'Category *',
                      border: OutlineInputBorder(),
                      prefixIcon: Icon(Icons.category),
                    ),
                    items:
                        ProductCategories.all.map((category) {
                          return DropdownMenuItem(
                            value: category,
                            child: Text(category),
                          );
                        }).toList(),
                    onChanged: (value) {
                      setState(() {
                        _selectedCategory =
                            value ?? ProductCategories.defaultCategory;
                      });
                    },
                  ),
                  const SizedBox(height: 24),

                  // Save Button
                  ElevatedButton(
                    onPressed: _isLoading ? null : _saveProduct,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppTheme.primary,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(8),
                      ),
                    ),
                    child: Text(
                      isEditMode ? 'Update Product' : 'Create Product',
                      style: const TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                  const SizedBox(height: 12),

                  // Cancel Button
                  OutlinedButton(
                    onPressed: _isLoading ? null : () => Navigator.pop(context),
                    style: OutlinedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(8),
                      ),
                    ),
                    child: const Text('Cancel', style: TextStyle(fontSize: 16)),
                  ),
                  const SizedBox(height: 32),
                ],
              ),
            ),
          ),

          // Loading Overlay
          if (_isLoading)
            Container(
              color: Colors.black.withOpacity(0.5),
              child: const Center(
                child: Card(
                  child: Padding(
                    padding: EdgeInsets.all(24),
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        CircularProgressIndicator(),
                        SizedBox(height: 16),
                        Text(
                          'Saving product...',
                          style: TextStyle(fontSize: 16),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildImagePicker() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Product Image *',
          style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
        ),
        const SizedBox(height: 8),
        GestureDetector(
          onTap: _pickImage,
          child: Container(
            height: 200,
            decoration: BoxDecoration(
              border: Border.all(color: Colors.grey[300]!),
              borderRadius: BorderRadius.circular(8),
              color: Colors.grey[100],
            ),
            child:
                _selectedImage != null
                    ? ClipRRect(
                      borderRadius: BorderRadius.circular(8),
                      child: Image.file(
                        File(_selectedImage!.path),
                        fit: BoxFit.cover,
                        width: double.infinity,
                      ),
                    )
                    : _existingImageUrl != null && _existingImageUrl!.isNotEmpty
                    ? ClipRRect(
                      borderRadius: BorderRadius.circular(8),
                      child: Image.network(
                        _existingImageUrl!,
                        fit: BoxFit.cover,
                        width: double.infinity,
                        errorBuilder: (context, error, stackTrace) {
                          return _buildImagePlaceholder();
                        },
                      ),
                    )
                    : _buildImagePlaceholder(),
          ),
        ),
        const SizedBox(height: 8),
        Text(
          'Tap to select image (JPG, PNG, WebP - Max 5MB)',
          style: TextStyle(fontSize: 12, color: Colors.grey[600]),
        ),
      ],
    );
  }

  Widget _buildImagePlaceholder() {
    return const Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.add_photo_alternate, size: 64, color: Colors.grey),
          SizedBox(height: 8),
          Text(
            'Tap to select image',
            style: TextStyle(color: Colors.grey, fontSize: 16),
          ),
        ],
      ),
    );
  }
}
