# Implementation Plan: Image Upload Feature

## Overview

This implementation plan breaks down the image upload feature into discrete, actionable tasks. The feature enables admin users to upload product images from their device, store them in Firebase Storage, and display them throughout the website with full bilingual support (English/Arabic).

The implementation builds upon existing partially-implemented upload logic and adds:
- Frontend file input with live preview
- Firebase Storage integration
- Comprehensive validation and error handling
- Bilingual user feedback
- Backward compatibility for existing products

## Tasks

- [x] 1. Add Firebase Storage configuration constant
  - Open `salamtak_web/config.php`
  - Add the constant: `define('FIREBASE_STORAGE_BUCKET', 'salmtak-6fffe.appspot.com');`
  - Verify the constant is accessible throughout the application
  - _Requirements: 3.1, 3.2, 3.3_

- [x] 2. Add bilingual translation keys for image upload
  - Open `salamtak_web/translations.php`
  - Add English translations for: 'upload_product_image', 'select_image_file', 'image_preview', 'no_image_selected', 'max_file_size_5mb', 'invalid_file_type', 'file_too_large', 'invalid_file_type_error', 'file_size_exceeded_error', 'mime_type_mismatch_error', 'firebase_upload_failed', 'image_uploaded_successfully'
  - Add corresponding Arabic translations for all keys
  - Verify translation keys are accessible via the `t()` function
  - _Requirements: 9.1, 9.2, 9.3, 9.4, 9.5_

- [x] 3. Update Add Product form with file input
  - [x] 3.1 Add file input field to the form
    - Open `salamtak_web/admin/add_product.php`
    - Add `enctype="multipart/form-data"` to the form tag
    - Add file input element with `name="product_image"`, `id="product_image"`, `accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"`
    - Add label using `t('upload_product_image')`
    - Add help text using `t('max_file_size_5mb')`
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5_

  - [x] 3.2 Add image preview container
    - Add preview container div with id `image-preview-container`
    - Add img element with id `image-preview` (initially hidden)
    - Add placeholder div with id `preview-placeholder` containing upload icon and `t('no_image_selected')` text
    - Add filename display paragraph with id `image-filename`
    - Add CSS for preview styling (max 300x300px, maintain aspect ratio)
    - _Requirements: 2.1, 2.2, 2.3, 2.5_

- [x] 4. Implement JavaScript image preview functionality
  - [x] 4.1 Create previewImage() function
    - Add JavaScript function `previewImage(event)` in `add_product.php`
    - Get selected file from event.target.files[0]
    - Validate file type against allowed types array
    - Validate file size (max 5MB = 5,000,000 bytes)
    - If validation fails, show alert with appropriate translation key and clear input
    - _Requirements: 2.1, 4.1, 4.2, 4.4, 4.5_

  - [x] 4.2 Display preview using FileReader
    - Use FileReader to read selected file as data URL
    - Set preview img src to the data URL
    - Show preview img and hide placeholder
    - Display filename below preview
    - Handle case when no file is selected (hide preview, show placeholder)
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5_

  - [x] 4.3 Wire preview function to file input
    - Add `onchange="previewImage(event)"` attribute to file input element
    - Test that preview updates when file is selected
    - _Requirements: 2.1, 2.4_

- [x] 5. Implement backend file upload handler
  - [x] 5.1 Add file validation logic
    - In `add_product.php` PHP section, check if `$_FILES['product_image']` is set and has no upload errors
    - Validate file extension against allowed array: ['jpg', 'jpeg', 'png', 'gif', 'webp']
    - Validate file size is ≤ 5,000,000 bytes
    - Validate MIME type matches file extension using `mime_content_type()`
    - Set appropriate error messages using translation keys for each validation failure
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5_

  - [x] 5.2 Generate unique filename
    - Create filename using pattern: `product_{timestamp}_{random}.{extension}`
    - Use `time()` for timestamp
    - Use `bin2hex(random_bytes(8))` for random component
    - Preserve original file extension
    - _Requirements: 5.1_

  - [x] 5.3 Create Firebase Storage upload function
    - Create function `uploadToFirebaseStorage($filePath, $fileName, $mimeType)`
    - Construct storage path: `products/{$fileName}`
    - Build upload URL using FIREBASE_STORAGE_BUCKET constant
    - Read file content using `file_get_contents()`
    - Initialize cURL with POST request
    - Set Content-Type and Content-Length headers
    - Execute request and capture HTTP response code
    - If successful (HTTP 200), construct and return download URL
    - If failed, log error with HTTP code and response, return false
    - _Requirements: 5.2, 5.3, 5.4, 5.5_

  - [x] 5.4 Integrate upload function into form handler
    - Call `uploadToFirebaseStorage()` after validation passes
    - Store returned image URL in `$imageUrl` variable
    - Handle upload failure by setting error message using `t('firebase_upload_failed')`
    - If no image uploaded, set `$imageUrl` to empty string
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5_

- [x] 6. Update Firestore integration for image URL
  - [x] 6.1 Add image field to product document
    - In the Firestore document creation code, add 'image' field with value from `$imageUrl`
    - Ensure image field is included alongside name, description, price, stock, category
    - Handle case where `$imageUrl` is empty string (no image uploaded)
    - _Requirements: 6.1, 6.2, 6.3_

  - [x] 6.2 Add success/error handling
    - On successful Firestore save, set success message using `t('image_uploaded_successfully')`
    - Redirect user after successful save to prevent form resubmission
    - On Firestore save failure, display error message using appropriate translation key
    - _Requirements: 6.4, 6.5, 7.1_

- [x] 7. Update product display pages with new image logic
  - [x] 7.1 Create image URL helper function
    - Create function `getProductImageUrl($product)` that takes product array
    - Check if image field is empty/null → return placeholder path
    - Check if image contains 'firebasestorage.googleapis.com' → return as-is
    - Check if image starts with '../assets/' or 'assets/' → return as-is
    - Otherwise, prepend '../assets/products/' to image value
    - _Requirements: 8.1, 8.2, 10.1, 10.2, 10.3, 10.4_

  - [x] 7.2 Update product listing pages
    - Open all product listing pages (products.php, admin product views, etc.)
    - Replace direct image field access with `getProductImageUrl($product)` call
    - Ensure img tags include `onerror="this.src='../assets/products/placeholder.svg'"` fallback
    - Add `htmlspecialchars()` around image URL for security
    - Verify images display with consistent dimensions (80x80px for listings)
    - _Requirements: 8.1, 8.2, 8.3, 8.4, 10.5_

  - [x] 7.3 Update product detail pages
    - Open product detail pages
    - Replace direct image field access with `getProductImageUrl($product)` call
    - Ensure img tags include onerror fallback to placeholder
    - Add `htmlspecialchars()` around image URL
    - Verify images display with larger dimensions while maintaining aspect ratio
    - _Requirements: 8.1, 8.2, 8.3, 8.5_

- [x] 8. Checkpoint - Test complete upload and display flow
  - Test uploading a new product with JPG image
  - Test uploading a new product with PNG image
  - Test uploading a new product without image (optional field)
  - Verify uploaded images display correctly on product listing pages
  - Verify uploaded images display correctly on product detail pages
  - Verify existing products with local image paths still display correctly
  - Verify products without images show placeholder
  - Test error handling for invalid file types
  - Test error handling for files over 5MB
  - Test preview functionality in browser
  - Test all functionality in English language mode
  - Test all functionality in Arabic language mode
  - Ensure all tests pass, ask the user if questions arise.

- [ ]* 9. Write unit tests for validation functions
  - [ ]* 9.1 Test file extension validation
    - Test valid extensions: jpg, jpeg, png, gif, webp
    - Test invalid extensions: exe, pdf, txt, doc
    - _Requirements: 4.1_

  - [ ]* 9.2 Test file size validation
    - Test file under limit (4,999,999 bytes)
    - Test file at limit (5,000,000 bytes)
    - Test file over limit (5,000,001 bytes)
    - _Requirements: 4.2_

  - [ ]* 9.3 Test MIME type validation
    - Test correct MIME types for each extension
    - Test MIME type mismatches
    - _Requirements: 4.3_

  - [ ]* 9.4 Test filename generation
    - Test unique filename format
    - Test filename includes timestamp
    - Test filename includes random component
    - Test filename preserves extension
    - _Requirements: 5.1_

  - [ ]* 9.5 Test image URL helper function
    - Test Firebase URL detection and passthrough
    - Test local path detection and prefix addition
    - Test empty/null handling with fallback
    - Test various path formats
    - _Requirements: 10.1, 10.2, 10.3, 10.4_

- [ ]* 10. Write integration tests for upload flow
  - [ ]* 10.1 Test end-to-end upload with valid image
    - Simulate file upload with valid JPG
    - Verify Firebase Storage upload is called
    - Verify download URL is returned
    - Verify Firestore document contains correct URL
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 6.1, 6.2, 6.3_

  - [ ]* 10.2 Test form submission without image
    - Submit form with all fields except image
    - Verify product is created with empty image field
    - Verify no Firebase upload is attempted
    - _Requirements: 6.2_

  - [ ]* 10.3 Test backward compatibility
    - Create product with local image path
    - Verify display logic handles local paths correctly
    - Create product with Firebase URL
    - Verify display logic handles Firebase URLs correctly
    - Create product with empty image
    - Verify fallback to placeholder works
    - _Requirements: 10.1, 10.2, 10.3, 10.4, 10.5_

  - [ ]* 10.4 Test bilingual functionality
    - Test error messages display in English
    - Test error messages display in Arabic
    - Test form labels display in both languages
    - Verify all translation keys exist
    - _Requirements: 9.1, 9.2, 9.3, 9.4, 9.5_

- [x] 11. Final checkpoint - Verify deployment readiness
  - Verify FIREBASE_STORAGE_BUCKET constant is in config.php
  - Verify all translation keys are in translations.php
  - Verify Firebase Storage rules allow public read for products/ path
  - Review error logs for any upload failures
  - Confirm all manual testing checklist items pass
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional testing tasks and can be skipped for faster MVP
- Each task references specific requirements for traceability
- The implementation builds on existing partially-implemented upload logic in add_product.php
- Firebase Storage bucket must have public read access configured for the "products/" path
- All user-facing text uses translation keys for bilingual support (English/Arabic)
- Image upload is an optional field - products can be created without images
- Backward compatibility is maintained for existing products with local image paths
- The checkpoint tasks (8, 11) ensure incremental validation of the complete feature
