# How to Run the Salamtak Flutter Application

## Prerequisites

1. **Flutter SDK** installed and configured
2. **Firebase project** set up with:
   - Authentication enabled
   - Firestore database
   - Firebase Storage
3. **Windows development** environment (or Android/iOS emulator)

---

## Step 1: Install Dependencies

Open terminal in the project directory and run:

```bash
flutter pub get
```

This will download all required packages including:
- Firebase packages (auth, firestore, storage)
- Map packages (flutter_map, latlong2)
- Image picker and other utilities

---

## Step 2: Check Available Devices

```bash
flutter devices
```

You should see available devices like:
- Windows (desktop)
- Chrome (web)
- Edge (web)
- Android emulator (if configured)

---

## Step 3: Run the Application

### Option A: Run on Windows Desktop (Recommended)
```bash
flutter run -d windows
```

### Option B: Run on Chrome
```bash
flutter run -d chrome
```

### Option C: Run on Android Emulator
```bash
flutter run -d emulator-5554
```
(Replace `emulator-5554` with your actual emulator ID from `flutter devices`)

---

## Step 4: Login Credentials

### Test User Account
- **National ID:** `11111111111111`
- **Password:** `user123456`

### Admin Account
- **Work ID:** `221007689`
- **Password:** `631663`

---

## Features to Test

### 1. Report Submission
1. Login as test user
2. Navigate to **Services** tab
3. Select a problem type (Pothole, Broken Pipe, etc.)
4. Upload a photo
5. Select location using the **Leaflet map**:
   - Tap on the map to set location
   - OR use Egyptian cities quick select
   - OR enter coordinates manually
6. Enter description (voice input available)
7. Select severity
8. Submit report

**Expected Result:** Report appears in History tab and syncs with website

### 2. Product Reviews
1. Navigate to **Products** tab
2. Tap on any product card
3. View product details
4. Scroll down to see reviews
5. Tap **"Write Review"**
6. Select star rating (1-5)
7. Write review comment
8. Submit

**Expected Result:** Review appears in the product's review list

### 3. Shopping Cart
1. Browse products
2. Tap on a product
3. Adjust quantity
4. Tap **"Add to Cart"**
5. View cart from top-right icon
6. Proceed to checkout

---

## Troubleshooting

### Issue: "Waiting for another flutter command to release the startup lock"
**Solution:**
```bash
taskkill /F /IM dart.exe
flutter pub get
```

### Issue: "No devices found"
**Solution:**
- For Windows: Ensure Windows development is enabled
  ```bash
  flutter config --enable-windows-desktop
  ```
- For Android: Start an emulator first
- For Web: Use Chrome or Edge

### Issue: "Firebase not initialized"
**Solution:**
- Check `google-services.json` (Android) or `GoogleService-Info.plist` (iOS)
- Verify Firebase configuration in `lib/firebase_options.dart`
- Run: `flutterfire configure`

### Issue: "Image upload fails"
**Solution:**
- Check Firebase Storage rules:
  ```
  rules_version = '2';
  service firebase.storage {
    match /b/{bucket}/o {
      match /reports/{imageId} {
        allow read: if true;
        allow write: if request.auth != null;
      }
    }
  }
  ```

### Issue: "Maps not loading"
**Solution:**
- Check internet connection (OpenStreetMap tiles require internet)
- Verify `flutter_map` and `latlong2` packages are installed
- Check console for tile loading errors

---

## Development Tips

### Hot Reload
While the app is running, press:
- `r` - Hot reload (fast, preserves state)
- `R` - Hot restart (slower, resets state)
- `q` - Quit

### Debug Mode
The app runs in debug mode by default. For better performance:
```bash
flutter run --release -d windows
```

### View Logs
```bash
flutter logs
```

### Clear Build Cache (if needed)
```bash
flutter clean
flutter pub get
flutter run -d windows
```

---

## Project Structure

```
salamtak/
├── lib/
│   ├── main.dart                    # App entry point
│   ├── models/                      # Data models
│   │   ├── report.dart
│   │   ├── review.dart
│   │   ├── product.dart
│   │   └── user.dart
│   ├── screens/                     # UI screens
│   │   ├── user/
│   │   │   ├── dashboard_screen.dart
│   │   │   ├── problem_report_screen.dart
│   │   │   ├── history_screen.dart
│   │   │   ├── products_screen.dart
│   │   │   └── product_details_screen.dart
│   │   └── admin/
│   ├── services/                    # Business logic
│   │   ├── database_service.dart
│   │   └── image_classifier.dart
│   ├── widgets/                     # Reusable widgets
│   │   └── leaflet_location_picker.dart
│   └── providers/                   # State management
├── assets/                          # Images and resources
├── pubspec.yaml                     # Dependencies
└── README.md
```

---

## Firebase Console Links

- **Authentication:** https://console.firebase.google.com/project/YOUR_PROJECT/authentication
- **Firestore:** https://console.firebase.google.com/project/YOUR_PROJECT/firestore
- **Storage:** https://console.firebase.google.com/project/YOUR_PROJECT/storage

---

## Support

For issues or questions:
1. Check the `IMPLEMENTATION_SUMMARY.md` file
2. Review Firebase console for data
3. Check Flutter logs for errors
4. Verify all dependencies are installed

---

**Last Updated:** May 12, 2026
**Flutter Version:** 3.7.0+
**Dart Version:** 3.7.0+
