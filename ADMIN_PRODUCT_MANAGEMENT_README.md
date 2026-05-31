# Admin Product Management Feature

## Overview

The Admin Product Management feature allows administrators to manage the product catalog through the mobile app. This feature provides full CRUD (Create, Read, Update, Delete) operations for products, with real-time synchronization between the mobile app and the admin website.

## Features

### ✅ Implemented Features

1. **Product List Management**
   - View all products in real-time
   - Search products by name (case-insensitive)
   - Filter products by category
   - Real-time updates when products are added, edited, or deleted
   - Empty state and no results messages

2. **Add New Products**
   - Product name (required, max 100 characters)
   - Description (required, max 500 characters)
   - Price in EGP (required, positive number, max 2 decimals)
   - Stock quantity (required, non-negative integer)
   - Category selection from predefined list
   - Image upload with validation and compression

3. **Edit Existing Products**
   - Pre-populated form with current product data
   - Update any product field
   - Replace product image (old image automatically deleted)
   - Maintains creation timestamp

4. **Delete Products**
   - Confirmation dialog before deletion
   - Automatic image cleanup from Firebase Storage
   - Real-time list update after deletion

5. **Image Management**
   - Pick images from device gallery
   - Automatic compression for images > 1MB
   - Format validation (JPEG, PNG, WebP)
   - Size validation (max 5MB)
   - Unique filename generation with timestamps
   - Firebase Storage integration
   - Image caching for performance

6. **Security**
   - Admin-only access (verified via Firebase Auth)
   - Firebase Security Rules enforce server-side permissions
   - Input validation and sanitization
   - Secure HTTPS communication

## Architecture

### File Structure

```
lib/
├── constants/
│   └── product_categories.dart          # Product category definitions
├── models/
│   └── product.dart                     # Product data model
├── services/
│   └── product_service.dart             # Firebase integration service
├── screens/
│   └── admin/
│       ├── product_management_screen.dart  # Product list screen
│       └── product_form_screen.dart        # Add/Edit product form
└── widgets/
    └── product_card.dart                # Product display widget
```

### Firebase Structure

```
firestore/
└── products/
    └── {productId}/
        ├── name: string
        ├── description: string
        ├── price: number
        ├── stock: number
        ├── category: string
        ├── image: string (Firebase Storage URL)
        ├── createdAt: timestamp
        └── updatedAt: timestamp

storage/
└── products/
    └── product_{timestamp}.{ext}        # Product images
```

## Usage Guide

### For Administrators

#### Accessing Product Management

1. Log in to the app with admin credentials
2. Navigate to Admin Home Screen
3. Tap the "Manage Products" button (inventory icon)

#### Adding a New Product

1. In Product Management screen, tap the "+" floating action button
2. Fill in all required fields:
   - Product Name
   - Description
   - Price (in EGP)
   - Stock quantity
   - Category (select from dropdown)
3. Tap the image placeholder to select a product image
4. Review the information
5. Tap "Create Product"
6. Wait for confirmation message

#### Editing a Product

1. In Product Management screen, find the product you want to edit
2. Tap the edit icon (pencil) on the product card
3. Modify the desired fields
4. Optionally change the product image
5. Tap "Update Product"
6. Wait for confirmation message

#### Deleting a Product

1. In Product Management screen, find the product you want to delete
2. Tap the delete icon (trash) on the product card
3. Confirm deletion in the dialog
4. The product and its image will be permanently removed

#### Searching and Filtering

- **Search**: Type in the search bar to filter products by name
- **Filter by Category**: Select a category from the dropdown menu
- **Clear Filters**: Clear the search text or select "All Categories"

### For Developers

#### Using ProductService

```dart
import 'package:salamtak/services/product_service.dart';
import 'package:salamtak/models/product.dart';

final productService = ProductService();

// Get all products (real-time stream)
Stream<List<Product>> productsStream = productService.getAllProductsStream();

// Get a single product
Product? product = await productService.getProductById('productId');

// Create a new product
String? productId = await productService.createProduct(newProduct);

// Update a product
await productService.updateProduct('productId', updatedProduct);

// Delete a product
await productService.deleteProduct('productId');

// Upload an image
String? imageUrl = await productService.uploadProductImage(imageFile);

// Search products
Stream<List<Product>> results = productService.searchProducts('query');

// Filter by category
Stream<List<Product>> filtered = productService.getProductsByCategory('Safety Equipment');
```

#### Product Categories

Available categories are defined in `lib/constants/product_categories.dart`:

- Safety Equipment
- Filters
- Pumps
- Pipes
- Valves
- Tools
- Accessories
- Chemicals
- Other (default)

To add new categories, update the `ProductCategories.all` list.

## Firebase Configuration

### Security Rules

#### Firestore Rules (`firestore.rules`)

```javascript
// Products collection - Read: all authenticated users, Write: admin only
match /products/{productId} {
  allow read: if isAuthenticated();
  allow create, update, delete: if isAdmin();
}
```

#### Storage Rules (`storage.rules`)

```javascript
// Products folder - Read: public, Write: admin only
match /products/{imageId} {
  allow read: if true;
  allow create, update, delete: if isAdmin();
}
```

### Deployment

Deploy the security rules to Firebase:

```bash
# Deploy Firestore rules
firebase deploy --only firestore:rules

# Deploy Storage rules
firebase deploy --only storage

# Deploy indexes
firebase deploy --only firestore:indexes
```

## Validation Rules

### Product Fields

| Field | Type | Validation |
|-------|------|------------|
| Name | String | Required, 1-100 characters |
| Description | String | Required, 1-500 characters |
| Price | Number | Required, positive, max 2 decimals |
| Stock | Integer | Required, non-negative |
| Category | String | Required, must be from predefined list |
| Image | String (URL) | Required for new products |

### Image Files

- **Formats**: JPEG, JPG, PNG, WebP
- **Max Size**: 5MB
- **Compression**: Automatic for files > 1MB
- **Max Dimensions**: Resized to 1920px width if larger

## Real-time Synchronization

The feature uses Firestore real-time listeners to ensure data consistency:

- **Mobile App ↔ Admin Website**: Changes made in either platform are immediately reflected in the other
- **Multiple Devices**: Multiple admins can manage products simultaneously
- **Automatic Updates**: Product list updates automatically when data changes

## Error Handling

The feature includes comprehensive error handling:

- **Network Errors**: User-friendly messages for connection issues
- **Permission Errors**: Clear feedback when admin privileges are missing
- **Validation Errors**: Inline error messages for invalid input
- **Firebase Errors**: Graceful handling of database and storage errors
- **Image Errors**: Fallback placeholders for failed image loads

## Performance Optimizations

- **Image Compression**: Reduces bandwidth and storage costs
- **Image Caching**: Cached network images for faster loading
- **Firestore Indexes**: Optimized queries for category filtering
- **Real-time Streams**: Efficient data synchronization
- **Lazy Loading**: Images loaded on demand

## Testing

### Manual Testing Checklist

- [ ] Admin can access Product Management screen
- [ ] Non-admin users cannot access the screen
- [ ] Product list displays correctly
- [ ] Search functionality works
- [ ] Category filter works
- [ ] Can create a new product with image
- [ ] Can edit an existing product
- [ ] Can delete a product
- [ ] Image compression works for large files
- [ ] Real-time sync works between app and website
- [ ] Validation errors display correctly
- [ ] Success/error messages appear
- [ ] Navigation flow works correctly

### Test Scenarios

1. **Create Product Flow**
   - Fill form with valid data → Success
   - Leave required fields empty → Validation errors
   - Upload image > 5MB → Size validation error
   - Upload invalid format → Format validation error

2. **Edit Product Flow**
   - Edit product without changing image → Success
   - Edit product and change image → Old image deleted, new image uploaded
   - Edit with invalid data → Validation errors

3. **Delete Product Flow**
   - Delete product → Confirmation dialog appears
   - Confirm deletion → Product and image removed
   - Cancel deletion → Product remains

4. **Real-time Sync**
   - Add product in app → Appears in website
   - Edit product in website → Updates in app
   - Delete product in app → Removed from website

## Troubleshooting

### Common Issues

**Issue**: "Permission denied" error when creating/editing products

**Solution**: Ensure the user has `userType: 'admin'` in their Firestore user document

---

**Issue**: Images not displaying

**Solution**: 
1. Check Firebase Storage rules are deployed
2. Verify image URLs are valid Firebase Storage URLs
3. Check network connection

---

**Issue**: Products not syncing between app and website

**Solution**:
1. Verify both use the same Firebase project
2. Check Firestore rules are deployed
3. Ensure real-time listeners are active

---

**Issue**: Image upload fails

**Solution**:
1. Check image size (must be < 5MB)
2. Verify image format (JPEG, PNG, WebP only)
3. Check Firebase Storage quota

## Future Enhancements

Potential improvements for future versions:

- [ ] Bulk product import/export
- [ ] Product variants (size, color, etc.)
- [ ] Inventory alerts and notifications
- [ ] Product analytics and reports
- [ ] Barcode scanning for quick product lookup
- [ ] Multi-language support for product descriptions
- [ ] Product reviews and ratings management
- [ ] Advanced search with filters (price range, stock level)
- [ ] Product history and audit trail
- [ ] Batch operations (bulk delete, bulk price update)

## Support

For issues or questions:

1. Check this README for common solutions
2. Review Firebase Console logs for errors
3. Check the implementation files for inline documentation
4. Consult Firebase documentation for platform-specific issues

## License

This feature is part of the Salamtak application.

---

**Last Updated**: 2024
**Version**: 1.0.0
**Status**: Production Ready ✅
