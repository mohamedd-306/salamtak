import 'dart:io';
import 'dart:typed_data';
import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:firebase_storage/firebase_storage.dart';
import 'package:image_picker/image_picker.dart';
import 'package:image/image.dart' as img;
import '../models/product.dart';

/// Service class for managing product operations with Firebase
///
/// Handles all CRUD operations, image uploads, and real-time data streaming
/// for products in the Firestore database and Firebase Storage.
class ProductService {
  final FirebaseFirestore _firestore = FirebaseFirestore.instance;
  final FirebaseStorage _storage = FirebaseStorage.instance;

  /// Collection reference for products
  CollectionReference get _productsCollection =>
      _firestore.collection('products');

  // ==================== CRUD Operations ====================

  /// Get a real-time stream of all products
  ///
  /// Returns a stream that emits the complete list of products whenever
  /// any product is added, updated, or deleted in Firestore.
  /// Products are ordered by createdAt timestamp (newest first).
  Stream<List<Product>> getAllProductsStream() {
    try {
      return _productsCollection
          .orderBy('createdAt', descending: true)
          .snapshots()
          .map((snapshot) {
            return snapshot.docs.map((doc) {
              return Product.fromMap(
                doc.data() as Map<String, dynamic>,
                doc.id,
              );
            }).toList();
          });
    } catch (e) {
      print('Error getting products stream: $e');
      return Stream.value([]);
    }
  }

  /// Get a single product by ID
  ///
  /// Returns the product if found, null otherwise.
  Future<Product?> getProductById(String id) async {
    try {
      final doc = await _productsCollection.doc(id).get();
      if (doc.exists) {
        return Product.fromMap(doc.data() as Map<String, dynamic>, doc.id);
      }
      return null;
    } catch (e) {
      print('Error getting product by ID: $e');
      return null;
    }
  }

  /// Create a new product
  ///
  /// Adds a new product document to Firestore and returns the document ID.
  /// Returns null if the operation fails.
  Future<String?> createProduct(Product product) async {
    try {
      final docRef = await _productsCollection.add(product.toMap());
      print('✓ Product created with ID: ${docRef.id}');
      return docRef.id;
    } catch (e) {
      print('Error creating product: $e');
      return null;
    }
  }

  /// Update an existing product
  ///
  /// Updates the product document in Firestore with the new data.
  /// Throws an exception if the operation fails.
  Future<void> updateProduct(String id, Product product) async {
    try {
      await _productsCollection.doc(id).update(product.toMap());
      print('✓ Product updated: $id');
    } catch (e) {
      print('Error updating product: $e');
      throw Exception('Failed to update product: $e');
    }
  }

  /// Delete a product
  ///
  /// Removes the product document from Firestore.
  /// Note: This does not delete the associated image from Storage.
  /// Use deleteProductImage() separately if needed.
  Future<void> deleteProduct(String id) async {
    try {
      await _productsCollection.doc(id).delete();
      print('✓ Product deleted: $id');
    } catch (e) {
      print('Error deleting product: $e');
      throw Exception('Failed to delete product: $e');
    }
  }

  // ==================== Image Management ====================

  /// Upload a product image to Firebase Storage
  ///
  /// Compresses the image if it's larger than 1MB, generates a unique filename,
  /// uploads to Firebase Storage, and returns the download URL.
  /// Returns null if the operation fails.
  Future<String?> uploadProductImage(XFile imageFile) async {
    try {
      print('Uploading product image...');

      // Read the image file
      final bytes = await imageFile.readAsBytes();
      File file = File(imageFile.path);

      // Compress image if larger than 1MB
      if (bytes.length > 1024 * 1024) {
        print(
          'Compressing image (${(bytes.length / 1024 / 1024).toStringAsFixed(2)} MB)...',
        );
        final compressedBytes = await _compressImage(bytes);
        if (compressedBytes != null) {
          // Write compressed bytes to temporary file
          file = await File(
            '${imageFile.path}_compressed',
          ).writeAsBytes(compressedBytes);
          print(
            'Image compressed to ${(compressedBytes.length / 1024 / 1024).toStringAsFixed(2)} MB',
          );
        }
      }

      // Generate unique filename with timestamp
      final timestamp = DateTime.now().millisecondsSinceEpoch;
      final extension = imageFile.path.split('.').last;
      final fileName = 'product_$timestamp.$extension';

      // Create storage reference
      final ref = _storage.ref().child('products').child(fileName);

      // Upload file
      final uploadTask = await ref.putFile(file);

      // Get download URL
      final downloadUrl = await uploadTask.ref.getDownloadURL();

      print('✓ Image uploaded: $downloadUrl');
      return downloadUrl;
    } catch (e) {
      print('Error uploading image: $e');
      return null;
    }
  }

  /// Delete a product image from Firebase Storage
  ///
  /// Extracts the file path from the download URL and deletes the file.
  /// Handles errors gracefully and logs them.
  Future<void> deleteProductImage(String imageUrl) async {
    try {
      if (imageUrl.isEmpty || !imageUrl.contains('firebase')) {
        return; // Not a Firebase Storage URL
      }

      // Extract the file path from the URL
      final ref = _storage.refFromURL(imageUrl);
      await ref.delete();
      print('✓ Image deleted from Storage');
    } catch (e) {
      print('Error deleting image: $e');
      // Don't throw - image deletion failure shouldn't block product deletion
    }
  }

  /// Compress an image to reduce file size
  ///
  /// Decodes the image, resizes if necessary, and encodes as JPEG with 85% quality.
  /// Returns compressed bytes or null if compression fails.
  Future<Uint8List?> _compressImage(List<int> bytes) async {
    try {
      // Decode image
      final image = img.decodeImage(Uint8List.fromList(bytes));
      if (image == null) return null;

      // Resize if too large (max 1920px width)
      img.Image resized = image;
      if (image.width > 1920) {
        resized = img.copyResize(image, width: 1920);
      }

      // Encode as JPEG with 85% quality
      final compressed = img.encodeJpg(resized, quality: 85);
      return Uint8List.fromList(compressed);
    } catch (e) {
      print('Error compressing image: $e');
      return null;
    }
  }

  // ==================== Inventory Management ====================

  /// Update the stock level of a product
  ///
  /// Sets the stock to the specified value and updates the updatedAt timestamp.
  Future<void> updateStock(String id, int newStock) async {
    try {
      await _productsCollection.doc(id).update({
        'stock': newStock,
        'updatedAt': DateTime.now().toIso8601String(),
      });
      print('✓ Stock updated for product $id: $newStock');
    } catch (e) {
      print('Error updating stock: $e');
      throw Exception('Failed to update stock: $e');
    }
  }

  /// Increment the stock level of a product
  ///
  /// Adds the specified amount to the current stock level.
  Future<void> incrementStock(String id, int amount) async {
    try {
      final product = await getProductById(id);
      if (product != null) {
        await updateStock(id, product.stock + amount);
      }
    } catch (e) {
      print('Error incrementing stock: $e');
      throw Exception('Failed to increment stock: $e');
    }
  }

  /// Decrement the stock level of a product
  ///
  /// Subtracts the specified amount from the current stock level.
  /// Ensures stock doesn't go below zero.
  Future<void> decrementStock(String id, int amount) async {
    try {
      final product = await getProductById(id);
      if (product != null) {
        final newStock =
            (product.stock - amount).clamp(0, double.infinity).toInt();
        await updateStock(id, newStock);
      }
    } catch (e) {
      print('Error decrementing stock: $e');
      throw Exception('Failed to decrement stock: $e');
    }
  }

  // ==================== Search and Filter ====================

  /// Search products by name
  ///
  /// Returns a stream of products whose names contain the search query (case-insensitive).
  /// Note: This performs client-side filtering. For large datasets, consider
  /// using Algolia or another search service.
  Stream<List<Product>> searchProducts(String query) {
    return getAllProductsStream().map((products) {
      if (query.isEmpty) return products;

      final lowerQuery = query.toLowerCase();
      return products.where((product) {
        return product.name.toLowerCase().contains(lowerQuery);
      }).toList();
    });
  }

  /// Get products by category
  ///
  /// Returns a stream of products filtered by the specified category.
  Stream<List<Product>> getProductsByCategory(String category) {
    try {
      return _productsCollection
          .where('category', isEqualTo: category)
          .orderBy('createdAt', descending: true)
          .snapshots()
          .map((snapshot) {
            return snapshot.docs.map((doc) {
              return Product.fromMap(
                doc.data() as Map<String, dynamic>,
                doc.id,
              );
            }).toList();
          });
    } catch (e) {
      print('Error getting products by category: $e');
      return Stream.value([]);
    }
  }

  // ==================== Validation ====================

  /// Validate image file
  ///
  /// Checks if the image format and size are acceptable.
  /// Returns an error message if validation fails, null if valid.
  String? validateImage(XFile? imageFile) {
    if (imageFile == null) {
      return 'Please select an image';
    }

    // Check file extension
    final extension = imageFile.path.split('.').last.toLowerCase();
    if (!['jpg', 'jpeg', 'png', 'webp'].contains(extension)) {
      return 'Invalid image format. Please use JPG, PNG, or WebP';
    }

    return null; // Valid
  }

  /// Validate image size
  ///
  /// Checks if the image file size is within acceptable limits (max 5MB).
  /// Returns an error message if validation fails, null if valid.
  Future<String?> validateImageSize(XFile imageFile) async {
    try {
      final bytes = await imageFile.readAsBytes();
      final sizeInMB = bytes.length / (1024 * 1024);

      if (sizeInMB > 5) {
        return 'Image size must be less than 5MB (current: ${sizeInMB.toStringAsFixed(2)} MB)';
      }

      return null; // Valid
    } catch (e) {
      return 'Failed to read image file';
    }
  }
}
