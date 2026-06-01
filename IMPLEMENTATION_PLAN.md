# Implementation Plan: Admin Sync, UI Fixes & Maps

## Overview
This document outlines the implementation of three major improvements:
1. **Admin System Synchronization** between website and mobile app
2. **UI Overflow Fixes** with responsive design
3. **Maps Functionality** in mobile app

## Current State Analysis

### Admin System Discrepancies
- **Website**: Single "admin" type with full access
- **Mobile**: Two distinct types:
  - `moderator` (Work ID: 221001001) - Reports only
  - `product_manager` (Work ID: 221002002) - Products & Orders

### Map Issues
- Mobile app requires Google Maps API key
- Currently shows fallback UI with manual coordinate entry
- Website likely has working map implementation

### UI Overflow
- Bottom navigation items overflowing by 30 pixels
- Modal dialogs may overflow on small screens
- Need responsive design improvements

## Implementation Tasks

### Task 1: Synchronize Admin Types (Website → Mobile Consistency)

#### 1.1 Update Website Authentication (salamtak_web/login.php)
- Add moderator and product_manager credentials
- Store specific admin type in session
- Update session variables to include `admin_role`

#### 1.2 Update Website Functions (salamtak_web/includes/functions.php)
- Add `isModerator()` and `isProductManager()` helper functions
- Update `isAdmin()` to check both types
- Add role-based access control functions

#### 1.3 Update Website Admin Dashboard (salamtak_web/admin/dashboard.php)
- Show/hide features based on admin role
- Moderators: Reports only
- Product Managers: Products, Orders, Inventory

#### 1.4 Update Website Navigation (salamtak_web/admin/includes/admin_navbar.php)
- Dynamic navigation based on admin role
- Hide Products/Orders links for moderators

### Task 2: Fix UI Overflow Issues

#### 2.1 Mobile App Bottom Navigation
- Add responsive sizing for navigation items
- Implement media queries for small screens
- Use `Flexible` widgets instead of fixed widths

#### 2.2 Admin Home Screen Cards
- Add `SingleChildScrollView` wrappers where needed
- Implement `LayoutBuilder` for responsive sizing
- Fix modal bottom sheets with proper constraints

#### 2.3 Responsive Typography
- Scale font sizes based on screen width
- Add minimum/maximum font sizes
- Use `MediaQuery` for dynamic sizing

### Task 3: Fix Maps in Mobile App

#### 3.1 Add Google Maps API Key Configuration
- Create environment configuration file
- Add API key to Android manifest
- Add API key to iOS Info.plist
- Update web/index.html with API key

#### 3.2 Implement Platform-Specific Map Handling
- Use `google_maps_flutter` for mobile
- Use `google_maps_flutter_web` for web
- Add proper error handling and fallbacks

#### 3.3 Test Map Functionality
- Verify map loads on Android
- Verify map loads on iOS
- Verify map loads on Web
- Test marker placement and dragging

## Credentials Reference

### Mobile App Admin Credentials
```
Moderator (Reports Only):
- Work ID: 221001001
- Password: mod2024

Product Manager (Products & Orders):
- Work ID: 221002002
- Password: prod2024

Legacy Admin (Full Access):
- Work ID: 221007689
- Password: 631663
```

### Website Admin Credentials (To Be Added)
```
Moderator:
- Work ID: 221001001
- Password: mod2024

Product Manager:
- Work ID: 221002002
- Password: prod2024
```

## Testing Checklist

### Admin Synchronization
- [ ] Moderator can login on website
- [ ] Moderator sees only Reports dashboard
- [ ] Product Manager can login on website
- [ ] Product Manager sees Products, Orders, Inventory
- [ ] Mobile app navigation matches user role
- [ ] Session persistence works correctly

### UI Overflow Fixes
- [ ] Bottom navigation displays correctly on small screens (< 360px)
- [ ] Modal dialogs don't overflow
- [ ] Text truncates properly with ellipsis
- [ ] Responsive font sizes work on all screen sizes
- [ ] Landscape orientation works correctly

### Maps Functionality
- [ ] Map loads on Android with API key
- [ ] Map loads on iOS with API key
- [ ] Map loads on Web with API key
- [ ] Marker can be placed and dragged
- [ ] Coordinates update correctly
- [ ] Fallback UI works when API key missing
- [ ] Manual coordinate entry works

## Files to Modify

### Website (PHP)
1. `salamtak_web/login.php` - Add moderator/product_manager login
2. `salamtak_web/includes/functions.php` - Add role helper functions
3. `salamtak_web/admin/dashboard.php` - Role-based dashboard
4. `salamtak_web/admin/includes/admin_navbar.php` - Dynamic navigation
5. `salamtak_web/admin/products.php` - Check product_manager role
6. `salamtak_web/admin/inventory.php` - Check product_manager role
7. `salamtak_web/admin/add_product.php` - Check product_manager role

### Mobile App (Flutter)
1. `lib/screens/admin/admin_navigation.dart` - Add responsive sizing
2. `lib/screens/admin/admin_home_screen.dart` - Fix overflow issues
3. `lib/screens/admin/orders_management_screen.dart` - Fix overflow
4. `lib/screens/admin/product_management_screen.dart` - Fix overflow
5. `lib/widgets/location_picker.dart` - Add API key configuration
6. `android/app/src/main/AndroidManifest.xml` - Add Maps API key
7. `ios/Runner/Info.plist` - Add Maps API key
8. `web/index.html` - Add Maps API key
9. `lib/config/maps_config.dart` - New file for API key management

## Priority Order
1. **HIGH**: Admin synchronization (security & consistency)
2. **HIGH**: UI overflow fixes (user experience)
3. **MEDIUM**: Maps functionality (feature enhancement)

## Estimated Implementation Time
- Admin Sync: 2-3 hours
- UI Fixes: 1-2 hours
- Maps Setup: 1-2 hours
- Testing: 2-3 hours
**Total: 6-10 hours**
