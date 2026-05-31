# 🚀 Flutter App - Run Commands & Troubleshooting

## ⚠️ Current Status

The Flutter app has **compilation errors** that need to be fixed before it can run.

### Errors Found:
- Multiple screens are missing `l10n` (localization) getter
- Affected files:
  - `lib/screens/user/problem_report_screen.dart`
  - `lib/screens/user/products_screen.dart`
  - `lib/screens/user/cart_screen.dart`
  - `lib/screens/user/product_details_screen.dart`
  - `lib/screens/user/order_history_screen.dart`
  - `lib/screens/user/invoice_screen.dart`

---

## 🔧 How to Run Flutter App (Once Fixed)

### Method 1: Run on Chrome (Web) ⭐
```bash
flutter run -d chrome
```

### Method 2: Run on Windows Desktop
```bash
flutter run -d windows
```

### Method 3: Run on Edge Browser
```bash
flutter run -d edge
```

### Method 4: Run with Hot Reload (Development)
```bash
flutter run -d chrome --hot
```

---

## 📱 Available Devices

Check available devices:
```bash
flutter devices
```

**Your Available Devices:**
- ✅ Windows (desktop) - `windows`
- ✅ Chrome (web) - `chrome`
- ✅ Edge (web) - `edge`

---

## 🛠️ Flutter Commands Cheat Sheet

### Get Dependencies:
```bash
flutter pub get
```

### Clean Build:
```bash
flutter clean
flutter pub get
```

### Check Flutter Installation:
```bash
flutter doctor
```

### Check for Issues:
```bash
flutter doctor -v
```

### Build for Web:
```bash
flutter build web
```

### Build for Windows:
```bash
flutter build windows
```

### Run with Specific Device:
```bash
flutter run -d chrome
flutter run -d windows
flutter run -d edge
```

### Run in Release Mode:
```bash
flutter run -d chrome --release
```

### Run in Profile Mode:
```bash
flutter run -d chrome --profile
```

---

## 🐛 Fix Localization Errors

The app is using `l10n` for localization but it's not properly imported/configured.

### Option 1: Add AppLocalizations Import

Add this import to each affected file:
```dart
import 'package:flutter_gen/gen_l10n/app_localizations.dart';
```

Then use it like:
```dart
final l10n = AppLocalizations.of(context)!;
```

### Option 2: Use Hardcoded Strings (Quick Fix)

Replace all `l10n.xxx` with hardcoded strings:
```dart
// Before:
Text(l10n.products)

// After:
Text('Products')
```

### Option 3: Setup Flutter Localization Properly

1. Add to `pubspec.yaml`:
```yaml
flutter:
  generate: true
```

2. Create `l10n.yaml` in project root:
```yaml
arb-dir: lib/l10n
template-arb-file: app_en.arb
output-localization-file: app_localizations.dart
```

3. Create localization files in `lib/l10n/`:
   - `app_en.arb` (English)
   - `app_ar.arb` (Arabic)

4. Run:
```bash
flutter pub get
flutter gen-l10n
```

---

## 📂 Project Structure

```
salamtak/
├── lib/
│   ├── main.dart
│   ├── screens/
│   │   ├── admin/
│   │   │   └── admin_home_screen.dart
│   │   └── user/
│   │       ├── cart_screen.dart
│   │       ├── dashboard_screen.dart
│   │       ├── products_screen.dart
│   │       ├── product_details_screen.dart
│   │       ├── problem_report_screen.dart
│   │       ├── order_history_screen.dart
│   │       └── invoice_screen.dart
│   └── providers/
│       └── cart_provider.dart
├── assets/
│   ├── logof.png
│   └── products/
├── pubspec.yaml
└── README.md
```

---

## 🎯 Quick Start (After Fixing Errors)

### Step 1: Get Dependencies
```bash
cd "c:\New folder\htdocs\swebsite\salamtak - Copy"
flutter pub get
```

### Step 2: Run on Chrome
```bash
flutter run -d chrome
```

### Step 3: Hot Reload
Press `r` in the terminal to hot reload
Press `R` to hot restart
Press `q` to quit

---

## 🔥 Hot Reload Commands

While the app is running:
- `r` - Hot reload (fast, preserves state)
- `R` - Hot restart (slower, resets state)
- `h` - Help
- `q` - Quit
- `c` - Clear console
- `d` - Detach (keep app running)

---

## 📦 Dependencies

Current dependencies in `pubspec.yaml`:
- ✅ flutter_localizations
- ✅ firebase_core
- ✅ firebase_auth
- ✅ cloud_firestore
- ✅ firebase_storage
- ✅ provider
- ✅ http
- ✅ image_picker
- ✅ google_maps_flutter
- ✅ cached_network_image
- ✅ speech_to_text
- ✅ permission_handler

---

## 🌐 Web Configuration

For web deployment, ensure Firebase is configured in:
```
web/index.html
```

Add Firebase configuration:
```html
<script src="https://www.gstatic.com/firebasejs/10.7.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.7.0/firebase-firestore-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.7.0/firebase-auth-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.7.0/firebase-storage-compat.js"></script>
```

---

## 💻 Windows Desktop Configuration

For Windows desktop, ensure:
1. Windows SDK is installed
2. Visual Studio Build Tools are installed
3. Run: `flutter config --enable-windows-desktop`

---

## 🚨 Common Errors & Solutions

### Error: "l10n is not defined"
**Solution:** Add localization imports or use hardcoded strings

### Error: "No connected devices"
**Solution:** 
```bash
flutter devices
flutter config --enable-web
flutter config --enable-windows-desktop
```

### Error: "Firebase not initialized"
**Solution:** Check `lib/main.dart` has:
```dart
await Firebase.initializeApp();
```

### Error: "Package not found"
**Solution:**
```bash
flutter clean
flutter pub get
```

---

## 📝 Create Launcher Scripts

### Windows Batch File (run-flutter.bat):
```batch
@echo off
echo Starting Flutter App on Chrome...
cd "c:\New folder\htdocs\swebsite\salamtak - Copy"
flutter run -d chrome
pause
```

### PowerShell Script (run-flutter.ps1):
```powershell
Write-Host "Starting Flutter App..." -ForegroundColor Cyan
Set-Location "c:\New folder\htdocs\swebsite\salamtak - Copy"
flutter run -d chrome
```

---

## 🎨 Development Workflow

1. **Make changes** to Dart files
2. **Save** the file
3. **Press `r`** in terminal for hot reload
4. **Test** the changes
5. **Repeat**

For major changes (adding dependencies, changing assets):
- Press `R` for hot restart
- Or stop and restart the app

---

## 📊 Performance Tips

### For Web:
```bash
flutter run -d chrome --web-renderer html
# or
flutter run -d chrome --web-renderer canvaskit
```

### For Production Build:
```bash
flutter build web --release
flutter build windows --release
```

---

## 🔍 Debugging

### Enable Verbose Logging:
```bash
flutter run -d chrome -v
```

### Check Logs:
```bash
flutter logs
```

### Analyze Code:
```bash
flutter analyze
```

### Run Tests:
```bash
flutter test
```

---

## 📱 Emulator Commands

### List Emulators:
```bash
flutter emulators
```

### Launch Emulator:
```bash
flutter emulators --launch <emulator_id>
```

---

## 🎯 Next Steps

1. **Fix localization errors** in the affected screens
2. **Run `flutter pub get`** to ensure dependencies are installed
3. **Run `flutter run -d chrome`** to start the app
4. **Test all features** to ensure everything works

---

**Flutter Version:** 3.29.0  
**Dart Version:** 3.7.0  
**Project:** Salamtak Safety Equipment Platform  
**Status:** ⚠️ Needs localization fixes before running
