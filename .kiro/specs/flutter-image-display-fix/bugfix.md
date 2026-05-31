# Bugfix Requirements Document

## Introduction

This document addresses two image display issues in the Salamtak Safety Equipment Platform Flutter app:
1. The "cones" product image not displaying on the Products page (shows placeholder icon)
2. Report images not displaying on the My Reports page (shows loading/placeholder state)

Both issues prevent users from viewing important visual content, impacting the user experience and the app's ability to effectively display product and report information.

## Bug Analysis

### Current Behavior (Defect)

1.1 WHEN a product named "cones" is loaded from Firestore THEN the system displays a placeholder icon instead of the product image

1.2 WHEN a product exists in Firestore with an `image` field containing a valid path THEN the system ignores the database image path and uses hardcoded name-matching logic

1.3 WHEN report images are loaded on the My Reports page THEN the system attempts to fetch images from `http://10.0.2.2:8000/uploads/filename.jpg` which results in loading indicators or error placeholders

1.4 WHEN the `_getImagePath()` method in ProductCard receives a product name that doesn't match the hardcoded conditions THEN the system returns `'assets/products/placeholder.png'` which doesn't exist in the assets

### Expected Behavior (Correct)

2.1 WHEN a product named "cones" is loaded from Firestore THEN the system SHALL display the image specified in the product's `image` field from the database

2.2 WHEN a product exists in Firestore with an `image` field containing a valid path THEN the system SHALL use the database image path to load and display the product image

2.3 WHEN report images are loaded on the My Reports page THEN the system SHALL correctly resolve and display images from their stored location (Firebase Storage URLs or valid network paths)

2.4 WHEN the product image path from the database points to an asset file THEN the system SHALL load the image from the Flutter assets bundle

2.5 WHEN the product image path from the database points to a network URL THEN the system SHALL load the image from the network with proper error handling

### Unchanged Behavior (Regression Prevention)

3.1 WHEN a product with a valid asset image path (vest, jacket, boots, helmet, hardhat, earmuffs) is displayed THEN the system SHALL CONTINUE TO display the correct product image

3.2 WHEN a report has no image (empty imagePath) THEN the system SHALL CONTINUE TO display the "No image" placeholder

3.3 WHEN an image fails to load due to network error or invalid path THEN the system SHALL CONTINUE TO display the error placeholder with "Image unavailable" message

3.4 WHEN the Products page loads products from Firestore THEN the system SHALL CONTINUE TO display all product information (name, price, add to cart button) correctly

3.5 WHEN the My Reports page loads reports THEN the system SHALL CONTINUE TO display all report information (type, description, status, date, location) correctly

3.6 WHEN a user taps on a product card THEN the system SHALL CONTINUE TO navigate to the product details screen

3.7 WHEN a user taps "Add to Cart" on a product THEN the system SHALL CONTINUE TO add the product to the cart and show the success snackbar
