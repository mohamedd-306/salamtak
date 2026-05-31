# 🚀 Flutter App - Quick Start Guide

## ⚠️ IMPORTANT: Fix Errors First!

The Flutter app currently has **localization errors** and won't run until fixed.

---

## 📁 Launcher Files Created

### 1. **RUN_FLUTTER_CHROME.bat** ⭐ (Easiest for Web)
- **Double-click** to run app on Chrome
- Automatically gets dependencies
- Shows hot reload commands

### 2. **RUN_FLUTTER_WINDOWS.bat** 🖥️ (Desktop App)
- **Double-click** to run as Windows desktop app
- Native Windows application

### 3. **run-flutter.ps1** 🎯 (Advanced Menu)
- PowerShell script with interactive menu
- Multiple platform options
- Build and clean options

### 4. **RUN_FLUTTER_APP.md** 📖 (Complete Documentation)
- All Flutter commands
- Troubleshooting guide
- How to fix localization errors

---

## 🎬 How to Run (After Fixing Errors)

### Method 1: Double-Click Batch File ⭐
```
Double-click: RUN_FLUTTER_CHROME.bat
```

### Method 2: Command Line
```bash
cd "c:\New folder\htdocs\swebsite\salamtak - Copy"
flutter run -d chrome
```

### Method 3: PowerShell Menu
```powershell
.\run-flutter.ps1
```

---

## 🐛 Fix Localization Errors First!

The app has errors in these files:
- `lib/screens/user/problem_report_screen.dart`
- `lib/screens/user/products_screen.dart`
- `lib/screens/user/cart_screen.dart`
- `lib/screens/user/product_details_screen.dart`
- `lib/screens/user/order_history_screen.dart`
- `lib/screens/user/invoice_screen.dart`

### Quick Fix Option 1: Remove l10n Usage

Find and replace in each file:
```dart
// Find:
l10n.products
l10n.cart
l10n.addToCart
// etc...

// Replace with hardcoded strings:
'Products'
'Cart'
'Add to Cart'
// etc...
```

### Quick Fix Option 2: Add Localization Context

Add this to the build method of each affected widget:
```dart
@override
Widget build(BuildContext context) {
  final l10n = AppLocalizations.of(context)!;
  // ... rest of code
}
```

And add this import at the top:
```dart
import 'package:flutter_gen/gen_l10n/app_localizations.dart';
```

---

## 📱 Available Platforms

Your Flutter app can run on:
- ✅ **Chrome** (Web) - Best for testing
- ✅ **Windows** (Desktop) - Native app
- ✅ **Edge** (Web) - Alternative browser

---

## 🔥 Hot Reload Commands

While app is running, press:
- **`r`** - Hot reload (fast, keeps state)
- **`R`** - Hot restart (slower, resets state)
- **`q`** - Quit app
- **`h`** - Show help
- **`c`** - Clear console

---

## 📦 Flutter Commands Reference

### Get Dependencies:
```bash
flutter pub get
```

### Clean Build:
```bash
flutter clean
flutter pub get
```

### Run on Chrome:
```bash
flutter run -d chrome
```

### Run on Windows:
```bash
flutter run -d windows
```

### Check Devices:
```bash
flutter devices
```

### Check Flutter Health:
```bash
flutter doctor
```

### Build for Production:
```bash
flutter build web --release
flutter build windows --release
```

---

## 🎯 Quick Start Steps

### Step 1: Fix Localization Errors
Choose one of the fix options above

### Step 2: Get Dependencies
```bash
cd "c:\New folder\htdocs\swebsite\salamtak - Copy"
flutter pub get
```

### Step 3: Run the App
**Option A - Easiest:**
```
Double-click: RUN_FLUTTER_CHROME.bat
```

**Option B - Command:**
```bash
flutter run -d chrome
```

### Step 4: Test Hot Reload
1. Make a change to any Dart file
2. Save the file
3. Press `r` in the terminal
4. See changes instantly!

---

## 🌐 Web vs Desktop

### Chrome (Web):
- ✅ Fast development
- ✅ Easy debugging
- ✅ No installation needed
- ❌ Limited native features

### Windows (Desktop):
- ✅ Full native features
- ✅ Better performance
- ✅ Standalone executable
- ❌ Slower build time

---

## 💡 Pro Tips

### Tip 1: Use Chrome for Development
Chrome has better debugging tools and faster hot reload

### Tip 2: Create Desktop Shortcuts
Right-click batch files → Send to → Desktop

### Tip 3: Keep Terminal Open
Don't close the terminal while app is running - you need it for hot reload!

### Tip 4: Use Hot Reload
Press `r` instead of restarting the app - it's much faster!

### Tip 5: Check Logs
If something breaks, check the terminal output for error messages

---

## 🔍 Troubleshooting

### Problem: "l10n is not defined"
**Status:** Known issue, needs fixing (see above)

### Problem: "No devices found"
**Solution:**
```bash
flutter config --enable-web
flutter config --enable-windows-desktop
flutter devices
```

### Problem: Batch file doesn't work
**Solution:** Right-click → Run as administrator

### Problem: Changes don't show
**Solution:** Press `R` (capital R) for hot restart

### Problem: App crashes on startup
**Solution:** Check terminal for error messages

---

## 📊 Project Info

**Flutter Version:** 3.29.0  
**Dart Version:** 3.7.0  
**Platforms:** Web (Chrome, Edge), Windows Desktop  
**Status:** ⚠️ Needs localization fixes

---

## 📞 Need More Help?

Check these files:
- **RUN_FLUTTER_APP.md** - Complete documentation
- **RUN_APP_COMMANDS.md** - Web app commands
- **README_LAUNCHER.md** - Web app launcher guide

---

## 🎯 Next Steps

1. ✅ Fix localization errors in the 6 affected files
2. ✅ Run `flutter pub get`
3. ✅ Double-click `RUN_FLUTTER_CHROME.bat`
4. ✅ Test the app
5. ✅ Use hot reload (`r`) for fast development

---

**Created:** May 16, 2026  
**Project:** Salamtak Safety Equipment Platform  
**Type:** Flutter Mobile/Web/Desktop App
