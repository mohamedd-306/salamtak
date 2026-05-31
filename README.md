# Salamtak - Safety Equipment Management System

A comprehensive cross-platform application for managing workplace safety equipment, built with Flutter and PHP.

## 🚀 Features

### Flutter Mobile/Desktop App
- **User Management**: Separate interfaces for users and administrators
- **Product Catalog**: Browse and purchase safety equipment
- **Shopping Cart**: Add products and manage orders
- **Problem Reporting**: Report workplace safety issues with image uploads
- **Admin Dashboard**: Manage products, orders, and user reports
- **Cross-Platform**: Runs on Android, iOS, Windows, macOS, Linux, and Web

### PHP Web Application
- **User Portal**: Browse products and submit safety reports
- **Admin Panel**: Manage inventory, view reports, and handle orders
- **Bilingual Support**: English and Arabic interfaces
- **Image Management**: Automatic image compression and base64 conversion
- **Firebase Integration**: Real-time data synchronization

## 🛠️ Tech Stack

### Frontend (Flutter)
- **Framework**: Flutter 3.x
- **State Management**: Provider
- **Database**: Cloud Firestore
- **Authentication**: Firebase Auth
- **Storage**: Firebase Storage
- **Image Handling**: cached_network_image, image_picker

### Backend (PHP)
- **Server**: Apache/XAMPP
- **Database**: Cloud Firestore (via REST API)
- **Image Processing**: PHP GD Library
- **Authentication**: Firebase Authentication API

## 📋 Prerequisites

### For Flutter App
- Flutter SDK 3.0 or higher
- Dart SDK 2.17 or higher
- Android Studio / VS Code
- Firebase project with Firestore enabled

### For PHP Web App
- XAMPP or similar (Apache + PHP 7.4+)
- PHP GD extension enabled
- cURL extension enabled

## 🔧 Installation

### 1. Clone the Repository
```bash
git clone https://github.com/yourusername/salamtak.git
cd salamtak
```

### 2. Flutter App Setup

```bash
# Install dependencies
flutter pub get

# Run on your preferred platform
flutter run -d windows    # For Windows
flutter run -d chrome     # For Web
flutter run               # For connected mobile device
```

### 3. PHP Web App Setup

1. Copy `salamtak_web` folder to your XAMPP `htdocs` directory
2. Create `config.php` from the template:
```php
<?php
// Firebase Configuration
define('FIREBASE_PROJECT_ID', 'your-project-id');
define('FIREBASE_API_KEY', 'your-api-key');
define('FIREBASE_STORAGE_BUCKET', 'your-bucket.appspot.com');
?>
```
3. Enable PHP GD extension in `php.ini`:
```ini
extension=gd
```
4. Restart Apache server

### 4. Firebase Setup

1. Create a Firebase project at [Firebase Console](https://console.firebase.google.com/)
2. Enable Firestore Database
3. Enable Firebase Authentication (Email/Password)
4. Enable Firebase Storage
5. Download configuration files:
   - For Flutter: `google-services.json` (Android) / `GoogleService-Info.plist` (iOS)
   - For PHP: Use REST API with your project credentials

## 📱 Default Login Credentials

### Admin Account
- **Work ID**: 221007689
- **Password**: 631663

### Test User Account
- **National ID**: 11111111111111
- **Password**: user123456

**⚠️ Important**: Change these credentials in production!

## 🗂️ Project Structure

```
salamtak/
├── lib/                          # Flutter app source code
│   ├── main.dart                # App entry point
│   ├── screens/                 # UI screens
│   │   ├── admin/              # Admin screens
│   │   └── user/               # User screens
│   ├── widgets/                # Reusable widgets
│   ├── providers/              # State management
│   ├── services/               # Business logic
│   └── theme.dart              # App theme
├── salamtak_web/               # PHP web application
│   ├── admin/                  # Admin panel
│   ├── user/                   # User portal
│   ├── assets/                 # Static assets
│   ├── config.php              # Configuration (not in repo)
│   └── translations.php        # Language files
├── assets/                     # Flutter assets
│   ├── images/                # App images
│   └── products/              # Product images
└── pubspec.yaml               # Flutter dependencies
```

## 🔐 Security Notes

**Files NOT included in repository** (for security):
- `config.php` - Contains Firebase credentials
- `google-services.json` - Firebase Android config
- `GoogleService-Info.plist` - Firebase iOS config
- User uploaded files in `salamtak_web/uploads/`

**You must create these files manually** after cloning the repository.

## 🌐 Features in Detail

### Image Management
- Automatic image compression (40-60% size reduction)
- Base64 encoding for cross-platform compatibility
- Smart caching to improve performance
- Fallback to local assets when network fails

### Bilingual Support
- Full English and Arabic translations
- RTL (Right-to-Left) layout support for Arabic
- Language switcher in all interfaces

### Admin Features
- Product inventory management
- Order processing
- User report management
- Statistics dashboard
- Image upload with automatic optimization

### User Features
- Product browsing and search
- Shopping cart with quantity management
- Problem reporting with image attachments
- Order history
- Profile management

## 🐛 Known Issues

- Chrome device data folder can grow large during development
- First-time image loading may be slow (caching improves subsequent loads)
- PHP GD library must be enabled for image compression

## 📝 Recent Updates

### Task 7: Product Image Base64 Conversion (Completed)
- ✅ Modified website to convert product images to base64
- ✅ Added automatic image compression
- ✅ Updated Flutter widget to support base64 images
- ✅ Created conversion scripts for existing products
- ✅ Added diagnostic tools

See `PRODUCT_IMAGE_BASE64_FIX.md` for detailed documentation.

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 👥 Authors

- Your Name - Initial work

## 🙏 Acknowledgments

- Flutter team for the amazing framework
- Firebase for backend services
- PHP GD library for image processing
- All contributors and testers

## 📞 Support

For support, email your-email@example.com or open an issue in the repository.

---

**⚠️ Remember to update Firebase credentials and change default passwords before deploying to production!**
