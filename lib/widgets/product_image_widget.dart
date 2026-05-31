import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'dart:convert';
import '../theme.dart';

/// A reusable widget for displaying product images with smart loading
///
/// Handles different image sources:
/// - Base64 images (data:image/...) with caching
/// - Asset paths (assets/products/image.jpeg)
/// - Just filenames (image.jpeg) - automatically adds assets/products/ prefix
/// - Firebase Storage URLs (https://firebasestorage.googleapis.com/...)
/// - Network URLs (http:// or https://)
/// - Empty/missing images
///
/// Features:
/// - Loading indicator while fetching network images
/// - Error placeholder with icon
/// - Image caching for network images
/// - Base64 decoding cache to avoid repeated decoding
/// - Automatic fallback to assets if network fails
/// - Smart path normalization
/// - Customizable dimensions and border radius
class ProductImageWidget extends StatefulWidget {
  final String imagePath;
  final double? width;
  final double? height;
  final BoxFit fit;
  final BorderRadius? borderRadius;
  final Color? backgroundColor;

  const ProductImageWidget({
    super.key,
    required this.imagePath,
    this.width,
    this.height,
    this.fit = BoxFit.cover,
    this.borderRadius,
    this.backgroundColor,
  });

  @override
  State<ProductImageWidget> createState() => _ProductImageWidgetState();
}

class _ProductImageWidgetState extends State<ProductImageWidget> {
  // Cache for decoded base64 images
  static final Map<String, Uint8List> _base64Cache = {};
  Uint8List? _cachedBytes;
  bool _isDecoding = false;

  @override
  void initState() {
    super.initState();
    _loadImage();
  }

  @override
  void didUpdateWidget(ProductImageWidget oldWidget) {
    super.didUpdateWidget(oldWidget);
    // Reload if image path changed
    if (oldWidget.imagePath != widget.imagePath) {
      _loadImage();
    }
  }

  Future<void> _loadImage() async {
    if (widget.imagePath.isEmpty ||
        !widget.imagePath.startsWith('data:image')) {
      return;
    }

    // Check cache first
    if (_base64Cache.containsKey(widget.imagePath)) {
      if (mounted) {
        setState(() {
          _cachedBytes = _base64Cache[widget.imagePath];
        });
      }
      return;
    }

    // Decode in background
    if (!_isDecoding) {
      _isDecoding = true;
      try {
        final base64Data = widget.imagePath.split(',')[1];
        final bytes = base64Decode(base64Data);

        // Store in cache
        _base64Cache[widget.imagePath] = bytes;

        if (mounted) {
          setState(() {
            _cachedBytes = bytes;
            _isDecoding = false;
          });
        }
      } catch (e) {
        debugPrint('❌ Error decoding base64 product image: $e');
        if (mounted) {
          setState(() {
            _isDecoding = false;
          });
        }
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    // No image provided
    if (widget.imagePath.isEmpty) {
      return _buildPlaceholder(
        icon: Icons.image_not_supported_outlined,
        message: 'No image',
        color: Colors.grey,
      );
    }

    // Debug logging
    debugPrint('=== PRODUCT IMAGE WIDGET ===');
    debugPrint('Image path: ${widget.imagePath}');
    debugPrint('Is base64: ${widget.imagePath.startsWith('data:image')}');
    debugPrint('Cache hit: ${_cachedBytes != null}');

    // Check if it's a base64 image
    if (widget.imagePath.startsWith('data:image')) {
      debugPrint('✓ Rendering as base64 image');
      return _buildBase64Image();
    }

    Widget imageWidget;
    String finalPath = widget.imagePath;

    // Determine if this is a network URL
    if (_isNetworkUrl(widget.imagePath)) {
      debugPrint('Loading as network URL: ${widget.imagePath}');

      // Check if it's a Firebase Storage URL that might be inaccessible
      if (widget.imagePath.startsWith(
        'https://firebasestorage.googleapis.com',
      )) {
        debugPrint(
          '⚠ Firebase Storage URL detected - will try asset fallback if it fails',
        );
        // Try to load it, but if it fails, try loading from assets
        imageWidget = CachedNetworkImage(
          imageUrl: widget.imagePath,
          width: widget.width,
          height: widget.height,
          fit: widget.fit,
          placeholder: (context, url) => _buildLoadingIndicator(),
          errorWidget: (context, url, error) {
            debugPrint('❌ Firebase Storage URL is inaccessible: $error');
            debugPrint('   URL: $url');
            // Try to extract filename and load from assets
            final filename = _extractFilename(widget.imagePath);
            debugPrint('   Trying asset fallback with filename: $filename');
            return _buildAssetFallback(filename);
          },
        );
      } else {
        // Regular network URL
        imageWidget = CachedNetworkImage(
          imageUrl: widget.imagePath,
          width: widget.width,
          height: widget.height,
          fit: widget.fit,
          placeholder: (context, url) => _buildLoadingIndicator(),
          errorWidget: (context, url, error) {
            debugPrint('❌ Error loading network image: $error');
            debugPrint('   URL: $url');
            return _buildPlaceholder(
              icon: Icons.broken_image_outlined,
              message: 'Image unavailable',
              color: Colors.red[300]!,
            );
          },
        );
      }
    } else {
      // Treat as asset path - normalize if needed
      if (!widget.imagePath.startsWith('assets/')) {
        // Try to construct asset path
        if (widget.imagePath.startsWith('/')) {
          finalPath = 'assets${widget.imagePath}';
        } else {
          // Check if it's just a filename (e.g., "cones.jpg", "earmuffs.jpeg")
          // or a relative path (e.g., "products/cones.jpg")
          if (widget.imagePath.contains('/')) {
            // Has a path separator, might be "products/cones.jpg"
            finalPath = 'assets/${widget.imagePath}';
          } else {
            // Just a filename, add the products folder
            finalPath = 'assets/products/${widget.imagePath}';
          }
        }
      }

      debugPrint('Loading as asset: $finalPath');

      // Load from assets
      imageWidget = Image.asset(
        finalPath,
        width: widget.width,
        height: widget.height,
        fit: widget.fit,
        errorBuilder: (context, error, stackTrace) {
          debugPrint('❌ Error loading asset image: $error');
          debugPrint('   Path: $finalPath');
          debugPrint('   Original path: ${widget.imagePath}');
          return _buildPlaceholder(
            icon: Icons.broken_image_outlined,
            message: 'Image not found',
            color: Colors.red[300]!,
          );
        },
      );
    }

    return ClipRRect(
      borderRadius: widget.borderRadius ?? BorderRadius.zero,
      child: Container(
        width: widget.width,
        height: widget.height,
        color: widget.backgroundColor ?? Colors.grey[100],
        child: imageWidget,
      ),
    );
  }

  /// Build base64 image (with caching)
  Widget _buildBase64Image() {
    // Use cached bytes if available
    if (_cachedBytes != null) {
      return ClipRRect(
        borderRadius: widget.borderRadius ?? BorderRadius.zero,
        child: Container(
          width: widget.width,
          height: widget.height,
          color: widget.backgroundColor ?? Colors.grey[100],
          child: Image.memory(
            _cachedBytes!,
            width: widget.width,
            height: widget.height,
            fit: widget.fit,
            errorBuilder: (context, error, stackTrace) {
              debugPrint(
                '❌ Error displaying cached base64 product image: $error',
              );
              return _buildPlaceholder(
                icon: Icons.broken_image_outlined,
                message: 'Image unavailable',
                color: Colors.red[300]!,
              );
            },
          ),
        ),
      );
    }

    // Show loading while decoding
    if (_isDecoding) {
      return _buildLoadingIndicator();
    }

    // Fallback: decode synchronously (shouldn't happen often)
    try {
      final base64Data = widget.imagePath.split(',')[1];
      final bytes = base64Decode(base64Data);

      return ClipRRect(
        borderRadius: widget.borderRadius ?? BorderRadius.zero,
        child: Container(
          width: widget.width,
          height: widget.height,
          color: widget.backgroundColor ?? Colors.grey[100],
          child: Image.memory(
            bytes,
            width: widget.width,
            height: widget.height,
            fit: widget.fit,
            errorBuilder: (context, error, stackTrace) {
              debugPrint('❌ Error displaying base64 product image: $error');
              return _buildPlaceholder(
                icon: Icons.broken_image_outlined,
                message: 'Image unavailable',
                color: Colors.red[300]!,
              );
            },
          ),
        ),
      );
    } catch (e) {
      debugPrint('❌ Error decoding base64 product image: $e');
      return _buildPlaceholder(
        icon: Icons.broken_image_outlined,
        message: 'Invalid image',
        color: Colors.red[300]!,
      );
    }
  }

  /// Check if the path is a network URL
  bool _isNetworkUrl(String path) {
    return path.startsWith('http://') || path.startsWith('https://');
  }

  /// Extract filename from a path or URL
  /// Examples:
  /// - "https://firebasestorage.googleapis.com/.../cones.jpg" -> "cones.jpg"
  /// - "products/earmuffs.jpeg" -> "earmuffs.jpeg"
  /// - "vest.jpeg" -> "vest.jpeg"
  String _extractFilename(String path) {
    if (path.contains('/')) {
      return path.split('/').last;
    }
    return path;
  }

  /// Try to load image from assets using the filename
  /// This is a fallback when network URLs fail
  Widget _buildAssetFallback(String filename) {
    final assetPath = 'assets/products/$filename';
    debugPrint('Trying asset fallback: $assetPath');

    return Image.asset(
      assetPath,
      width: widget.width,
      height: widget.height,
      fit: widget.fit,
      errorBuilder: (context, error, stackTrace) {
        debugPrint('❌ Asset fallback also failed: $error');
        return _buildPlaceholder(
          icon: Icons.broken_image_outlined,
          message: 'Image not found',
          color: Colors.red[300]!,
        );
      },
    );
  }

  /// Build loading indicator for network images
  Widget _buildLoadingIndicator() {
    return Container(
      width: widget.width,
      height: widget.height,
      color: widget.backgroundColor ?? AppTheme.primary.withValues(alpha: 0.05),
      child: const Center(
        child: CircularProgressIndicator(
          color: AppTheme.primary,
          strokeWidth: 2,
        ),
      ),
    );
  }

  /// Build placeholder for missing or error images
  Widget _buildPlaceholder({
    required IconData icon,
    required String message,
    required Color color,
  }) {
    return Container(
      width: widget.width,
      height: widget.height,
      color: widget.backgroundColor ?? color.withValues(alpha: 0.08),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            icon,
            size: (widget.height != null && widget.height! < 150) ? 32 : 48,
            color: color.withValues(alpha: 0.4),
          ),
          if (widget.height == null || widget.height! >= 100) ...[
            const SizedBox(height: 8),
            Text(
              message,
              style: TextStyle(
                fontSize: 12,
                color: color.withValues(alpha: 0.6),
              ),
              textAlign: TextAlign.center,
            ),
          ],
        ],
      ),
    );
  }
}

/// Thumbnail variant for smaller product images (e.g., in grids)
class ProductImageThumbnail extends StatelessWidget {
  final String imagePath;
  final double size;
  final BorderRadius? borderRadius;

  const ProductImageThumbnail({
    super.key,
    required this.imagePath,
    this.size = 140,
    this.borderRadius,
  });

  @override
  Widget build(BuildContext context) {
    return ProductImageWidget(
      imagePath: imagePath,
      width: size,
      height: size,
      fit: BoxFit.cover,
      borderRadius: borderRadius ?? BorderRadius.circular(16),
    );
  }
}

/// Full-width variant for product detail views
class ProductImageFull extends StatelessWidget {
  final String imagePath;
  final double height;
  final BorderRadius? borderRadius;

  const ProductImageFull({
    super.key,
    required this.imagePath,
    this.height = 300,
    this.borderRadius,
  });

  @override
  Widget build(BuildContext context) {
    return ProductImageWidget(
      imagePath: imagePath,
      width: double.infinity,
      height: height,
      fit: BoxFit.cover,
      borderRadius: borderRadius ?? BorderRadius.zero,
    );
  }
}
