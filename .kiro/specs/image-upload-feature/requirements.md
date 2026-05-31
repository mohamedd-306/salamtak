# Requirements Document

## Introduction

This document specifies the requirements for implementing image upload functionality for the Add Product page in the admin panel. The system allows admin users to upload product images from their device, store them in Firebase Storage, and display them throughout the website. The feature supports bilingual content (English/Arabic) and provides comprehensive error handling and user feedback.

## Glossary

- **Admin_User**: A user with administrative privileges who can add and manage products
- **Add_Product_Form**: The web form interface where admin users enter product details
- **Firebase_Storage**: Google's cloud storage service for storing and serving user-generated content
- **Product_Image**: A digital image file representing a product, stored in Firebase Storage
- **Image_Preview**: A visual representation of the selected image displayed before upload
- **Storage_Bucket**: A container in Firebase Storage that holds uploaded files
- **Image_URL**: The publicly accessible web address of an uploaded image in Firebase Storage
- **Firestore**: Google's NoSQL database where product metadata (including image URLs) is stored
- **File_Input**: An HTML form element that allows users to select files from their device
- **MIME_Type**: A standard identifier for file formats (e.g., image/jpeg, image/png)
- **Validation**: The process of checking that uploaded files meet specified criteria
- **Fallback_Image**: A default placeholder image displayed when a product has no uploaded image
- **Translation_Key**: A unique identifier used to retrieve bilingual text content

## Requirements

### Requirement 1: File Upload Form Interface

**User Story:** As an admin user, I want to select image files from my device using a file input field, so that I can upload product images directly from my computer.

#### Acceptance Criteria

1. WHEN the Add Product form loads, THE Add_Product_Form SHALL display a file input element with type="file" for image selection
2. WHEN the file input is rendered, THE Add_Product_Form SHALL set the accept attribute to "image/jpeg,image/jpg,image/png,image/gif,image/webp" to filter selectable files
3. WHEN the form is submitted, THE Add_Product_Form SHALL use enctype="multipart/form-data" to enable file uploads
4. WHEN the file input label is displayed, THE Add_Product_Form SHALL show bilingual text using the appropriate Translation_Key for the current language
5. WHEN a user clicks the file input, THE Add_Product_Form SHALL open the native file selection dialog

### Requirement 2: Image Preview Functionality

**User Story:** As an admin user, I want to see a preview of the selected image before uploading, so that I can verify I've chosen the correct file.

#### Acceptance Criteria

1. WHEN a user selects an image file, THE Add_Product_Form SHALL display a preview of the selected image
2. WHEN the preview is displayed, THE Add_Product_Form SHALL show the image with maximum dimensions of 300x300 pixels while maintaining aspect ratio
3. WHEN no image is selected, THE Add_Product_Form SHALL display a placeholder icon or message
4. WHEN a user selects a different image, THE Add_Product_Form SHALL replace the previous preview with the new image
5. WHEN the preview area is rendered, THE Add_Product_Form SHALL include the image filename below the preview

### Requirement 3: Firebase Storage Configuration

**User Story:** As a system administrator, I want Firebase Storage properly configured in the application, so that image uploads can be stored and retrieved successfully.

#### Acceptance Criteria

1. THE config.php file SHALL define a constant FIREBASE_STORAGE_BUCKET with the value "salmtak-6fffe.appspot.com"
2. WHEN uploading an image, THE system SHALL construct the Firebase Storage API URL using the FIREBASE_STORAGE_BUCKET constant
3. WHEN retrieving an image URL, THE system SHALL construct the download URL using the FIREBASE_STORAGE_BUCKET constant
4. THE Firebase Storage bucket SHALL have public read access configured for the "products/" path
5. WHEN the application starts, THE system SHALL have access to the Firebase Storage bucket without requiring additional authentication

### Requirement 4: Image File Validation

**User Story:** As an admin user, I want the system to validate uploaded images, so that only appropriate files are accepted and stored.

#### Acceptance Criteria

1. WHEN a file is uploaded, THE system SHALL verify the file extension is one of: jpg, jpeg, png, gif, webp
2. WHEN a file is uploaded, THE system SHALL verify the file size is less than or equal to 5MB (5,000,000 bytes)
3. WHEN a file is uploaded, THE system SHALL verify the MIME_Type matches the file extension
4. IF an invalid file type is uploaded, THEN THE system SHALL reject the upload and display an error message using the appropriate Translation_Key
5. IF a file exceeds the size limit, THEN THE system SHALL reject the upload and display an error message using the appropriate Translation_Key

### Requirement 5: Image Upload to Firebase Storage

**User Story:** As an admin user, I want my selected images uploaded to Firebase Storage, so that they are permanently stored and accessible via URL.

#### Acceptance Criteria

1. WHEN a valid image is submitted, THE system SHALL generate a unique filename using the pattern "product_{timestamp}_{random}.{extension}"
2. WHEN uploading to Firebase Storage, THE system SHALL store the image in the "products/" path
3. WHEN uploading to Firebase Storage, THE system SHALL send the file content with the correct MIME_Type header
4. WHEN the upload succeeds, THE system SHALL retrieve the public download URL from Firebase Storage
5. IF the upload fails, THEN THE system SHALL log the HTTP error code and display an error message to the user

### Requirement 6: Firestore Integration

**User Story:** As an admin user, I want the uploaded image URL stored with the product data, so that the image can be displayed when viewing the product.

#### Acceptance Criteria

1. WHEN a product is created with an uploaded image, THE system SHALL store the Firebase Storage Image_URL in the Firestore "image" field
2. WHEN a product is created without an uploaded image, THE system SHALL store an empty string in the Firestore "image" field
3. WHEN saving to Firestore, THE system SHALL include the image URL in the product document alongside name, description, price, stock, and category
4. WHEN the Firestore save succeeds, THE system SHALL redirect the user to prevent form resubmission
5. IF the Firestore save fails, THEN THE system SHALL display an error message using the appropriate Translation_Key

### Requirement 7: Error Handling and User Feedback

**User Story:** As an admin user, I want clear feedback about upload success or failure, so that I know whether my image was uploaded correctly.

#### Acceptance Criteria

1. WHEN an upload succeeds, THE system SHALL display a success message using the appropriate Translation_Key
2. WHEN an upload fails due to file type, THE system SHALL display an error message specifying allowed file types
3. WHEN an upload fails due to file size, THE system SHALL display an error message specifying the maximum size limit
4. WHEN an upload fails due to Firebase Storage error, THE system SHALL display an error message including the HTTP error code
5. WHEN displaying error or success messages, THE system SHALL use bilingual text based on the current language setting

### Requirement 8: Product Image Display

**User Story:** As a website user, I want to see product images in product listings and detail pages, so that I can visually identify products.

#### Acceptance Criteria

1. WHEN displaying a product with an uploaded image, THE system SHALL show the image from the Firebase Storage Image_URL
2. WHEN displaying a product without an uploaded image, THE system SHALL show the Fallback_Image (placeholder.svg)
3. WHEN an image fails to load, THE system SHALL display the Fallback_Image using the onerror attribute
4. WHEN displaying product images in listings, THE system SHALL show images with consistent dimensions (80x80 pixels)
5. WHEN displaying product images in detail views, THE system SHALL show images with larger dimensions while maintaining aspect ratio

### Requirement 9: Bilingual Support

**User Story:** As an admin user, I want all image upload interface text in both English and Arabic, so that I can use the system in my preferred language.

#### Acceptance Criteria

1. THE translations.php file SHALL include Translation_Keys for all image upload related text in both English and Arabic
2. WHEN the language is set to Arabic, THE Add_Product_Form SHALL display all labels, buttons, and messages in Arabic
3. WHEN the language is set to English, THE Add_Product_Form SHALL display all labels, buttons, and messages in English
4. THE system SHALL include translations for: "Upload Image", "Select Image File", "Image Preview", "File too large", "Invalid file type", "Upload failed", "Image uploaded successfully"
5. WHEN displaying file size limits in messages, THE system SHALL use the same numeric format (5MB) in both languages

### Requirement 10: Existing Product Image Migration

**User Story:** As a system administrator, I want existing products to continue displaying their images, so that the new upload feature doesn't break existing functionality.

#### Acceptance Criteria

1. WHEN displaying products created before the upload feature, THE system SHALL check if the image field contains a Firebase Storage URL or a local path
2. IF the image field contains a local path (e.g., "vest.jpeg"), THEN THE system SHALL prepend "../assets/products/" to construct the full path
3. IF the image field contains a Firebase Storage URL, THEN THE system SHALL use the URL directly
4. WHEN the image field is empty or null, THE system SHALL use the Fallback_Image path
5. THE system SHALL maintain backward compatibility with the existing image display logic in product listings
