/// Product categories for the admin product management system
///
/// This list defines all available product categories that can be assigned
/// to products in the system. Categories are used for filtering and organizing
/// products in the product management interface.
class ProductCategories {
  // Private constructor to prevent instantiation
  ProductCategories._();

  /// List of all available product categories (matches website categories)
  static const List<String> all = [
    'Safety Wear',
    'Head Protection',
    'Footwear',
    'Other',
  ];

  /// Default category for new products
  static const String defaultCategory = 'Other';

  /// Validates if a category is valid
  static bool isValid(String category) {
    return all.contains(category);
  }

  /// Gets the display name for a category
  static String getDisplayName(String category) {
    return category;
  }
}
