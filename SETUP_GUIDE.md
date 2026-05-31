# Salamtak - Quick Setup Guide

## Prerequisites Check

Before running the app, make sure you have:
- ✅ Flutter SDK installed (run `flutter --version` to check)
- ✅ Android Studio or VS Code with Flutter extensions
- ✅ Android device with USB debugging enabled OR Android emulator

## Step-by-Step Setup

### 1. Enable Developer Mode (Windows Only)

If you see a symlink error, you need to enable Developer Mode:

```bash
start ms-settings:developers
```

Then toggle "Developer Mode" to ON.

### 2. Check Flutter Setup

```bash
flutter doctor
```

Make sure Android toolchain is installed and working.

### 3. Connect Device or Start Emulator

**Option A: Physical Device**
- Enable USB debugging on your Android phone
- Connect via USB
- Run `flutter devices` to verify connection

**Option B: Emulator**
- Open Android Studio
- Go to Tools > Device Manager
- Create/Start an Android Virtual Device (AVD)

### 4. Run the App

```bash
cd salamtak
flutter run
```

The app will build and install on your device/emulator.

## Testing the App

### Test User Account
1. Open the app
2. Enter credentials:
   - National ID: `12345678901234`
   - Phone: `01234567890`
3. Click "Sign In"
4. Explore:
   - Home: View dashboard
   - Services: Report problems or access Winch
   - History: View your reports
   - Account: Settings and sign out

### Test Admin Account
1. Sign out from user account
2. Enter admin credentials:
   - National ID: `99999999999999`
   - Phone: `01111111111`
3. Click "Sign In"
4. View all reports and update their status

## Common Issues

### Issue: "Building with plugins requires symlink support"
**Solution:** Enable Developer Mode in Windows settings

### Issue: "No devices found"
**Solution:** 
- Check USB connection
- Enable USB debugging on phone
- Or start an Android emulator

### Issue: Camera not working
**Solution:** 
- Grant camera permissions when prompted
- On emulator, use virtual camera or select image from gallery

### Issue: Database errors
**Solution:** 
- Uninstall the app
- Run `flutter clean`
- Run `flutter pub get`
- Run `flutter run` again

## Features to Test

1. **Login System**
   - Try both user and admin accounts
   - Invalid credentials should show error

2. **User Features**
   - Dashboard shows statistics
   - Report a pothole (camera opens)
   - Report broken pipe (camera opens)
   - Report other problem (camera opens)
   - Click Winch service (opens browser)
   - View history of reports
   - Check report status colors

3. **Admin Features**
   - View all user reports
   - Filter by status (All, Pending, In Progress, Resolved)
   - Update report status
   - View statistics

## Database Location

The SQLite database is stored locally on the device at:
- Android: `/data/data/com.example.salamtak/databases/salamtak.db`

## Troubleshooting

If you encounter any issues:

1. Clean the project:
```bash
flutter clean
flutter pub get
```

2. Check for errors:
```bash
flutter analyze
```

3. Rebuild:
```bash
flutter run
```

## Next Steps

The app is fully functional and ready for testing. All features work:
- ✅ Login with dummy credentials
- ✅ User dashboard
- ✅ Report problems with camera
- ✅ View history
- ✅ Admin view all reports
- ✅ Update report status
- ✅ Local database storage

Enjoy testing Salamtak! 🎉
