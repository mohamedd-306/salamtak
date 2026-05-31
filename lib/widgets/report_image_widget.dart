import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'dart:convert';
import '../config/app_config.dart';
import '../theme.dart';

/// A reusable widget for displaying report images with smart loading
///
/// Handles different image sources:
/// - Base64 images (data:image/...) with caching
/// - Firebase Storage URLs (https://firebasestorage.googleapis.com/...)
/// - Website relative paths (uploads/image.jpg)
/// - Empty/missing images
///
/// Features:
/// - Loading indicator while fetching
/// - Error placeholder with icon
/// - Image caching for performance
/// - Base64 decoding cache to avoid repeated decoding
/// - Customizable dimensions and border radius
class ReportImageWidget extends StatefulWidget {
  final String imagePath;
  final double? width;
  final double? height;
  final BoxFit fit;
  final BorderRadius? borderRadius;
  final Color? backgroundColor;

  const ReportImageWidget({
    super.key,
    required this.imagePath,
    this.width,
    this.height,
    this.fit = BoxFit.cover,
    this.borderRadius,
    this.backgroundColor,
  });

  @override
  State<ReportImageWidget> createState() => _ReportImageWidgetState();
}

class _ReportImageWidgetState extends State<ReportImageWidget> {
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
  void didUpdateWidget(ReportImageWidget oldWidget) {
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
        debugPrint('❌ Error decoding base64 image: $e');
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
    debugPrint('=== REPORT IMAGE WIDGET ===');
    debugPrint('Image path: ${widget.imagePath}');
    debugPrint('Is base64: ${widget.imagePath.startsWith('data:image')}');
    debugPrint('Cache hit: ${_cachedBytes != null}');

    // Check if it's a base64 image
    if (widget.imagePath.startsWith('data:image')) {
      debugPrint('✓ Rendering as base64 image');
      return _buildBase64Image();
    }

    // For old Firebase Storage URLs that can't be accessed, show placeholder
    if (widget.imagePath.startsWith('https://firebasestorage.googleapis.com')) {
      debugPrint('⚠ Firebase Storage URL detected - showing placeholder');
      return _buildPlaceholder(
        icon: Icons.cloud_off_outlined,
        message: 'Storage unavailable',
        color: Colors.orange,
      );
    }

    // Get the full image URL
    final imageUrl = AppConfig.getImageUrl(widget.imagePath);

    debugPrint('Full URL: $imageUrl');
    debugPrint(
      'Is Firebase: ${AppConfig.isFirebaseStorageUrl(widget.imagePath)}',
    );
    debugPrint('Is Website: ${AppConfig.isWebsitePath(widget.imagePath)}');

    return ClipRRect(
      borderRadius: widget.borderRadius ?? BorderRadius.zero,
      child: Container(
        width: widget.width,
        height: widget.height,
        color: widget.backgroundColor ?? Colors.grey[100],
        child: CachedNetworkImage(
          imageUrl: imageUrl,
          width: widget.width,
          height: widget.height,
          fit: widget.fit,
          placeholder: (context, url) => _buildLoadingIndicator(),
          errorWidget: (context, url, error) {
            debugPrint('❌ Error loading image: $error');
            debugPrint('   URL: $url');
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
              debugPrint('❌ Error displaying cached base64 image: $error');
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
              debugPrint('❌ Error displaying base64 image: $error');
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
      debugPrint('❌ Error decoding base64 image: $e');
      return _buildPlaceholder(
        icon: Icons.broken_image_outlined,
        message: 'Invalid image',
        color: Colors.red[300]!,
      );
    }
  }

  /// Build loading indicator
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

/// Thumbnail variant for smaller images (e.g., in lists)
class ReportImageThumbnail extends StatelessWidget {
  final String imagePath;
  final double size;
  final BorderRadius? borderRadius;

  const ReportImageThumbnail({
    super.key,
    required this.imagePath,
    this.size = 100,
    this.borderRadius,
  });

  @override
  Widget build(BuildContext context) {
    return ReportImageWidget(
      imagePath: imagePath,
      width: size,
      height: size,
      fit: BoxFit.cover,
      borderRadius: borderRadius ?? BorderRadius.circular(12),
    );
  }
}

/// Full-width variant for detail views
class ReportImageFull extends StatelessWidget {
  final String imagePath;
  final double height;
  final BorderRadius? borderRadius;

  const ReportImageFull({
    super.key,
    required this.imagePath,
    this.height = 200,
    this.borderRadius,
  });

  @override
  Widget build(BuildContext context) {
    return ReportImageWidget(
      imagePath: imagePath,
      width: double.infinity,
      height: height,
      fit: BoxFit.cover,
      borderRadius: borderRadius ?? BorderRadius.zero,
    );
  }
}
