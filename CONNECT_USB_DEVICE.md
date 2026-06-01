# 📱 Connect Your Phone via USB - Step by Step

## 🔌 For Android Phone:

### Step 1: Enable Developer Options
1. Open **Settings** on your phone
2. Go to **About Phone**
3. Find **Build Number**
4. Tap **Build Number** 7 times
5. You'll see "You are now a developer!"

### Step 2: Enable USB Debugging
1. Go back to **Settings**
2. Find **Developer Options** (usually in System or Additional Settings)
3. Enable **USB Debugging**
4. Enable **Install via USB** (if available)

### Step 3: Connect Phone to PC
1. Connect your phone to PC using USB cable
2. On your phone, you'll see a popup: **"Allow USB debugging?"**
3. Check **"Always allow from this computer"**
4. Tap **OK**

### Step 4: Verify Connection
Run this command to check if phone is detected:
```bash
flutter devices
```

You should see something like:
```
SM G960F (mobile) • 1234567890 • android-arm64 • Android 10 (API 29)
```

### Step 5: Run the App
```bash
flutter run
```

Flutter will automatically detect your phone and install the app!

---

## 🍎 For iPhone:

### Step 1: Trust Computer
1. Connect iPhone to PC via USB
2. On iPhone, tap **Trust This Computer**
3. Enter your passcode

### Step 2: Install Dependencies
You need Xcode and iOS development tools (Mac only)

### Step 3: Run
```bash
flutter run
```

---

## 🐛 Troubleshooting:

### Phone Not Detected?

**Check USB Cable**:
- Use original cable or data cable (not charging-only cable)
- Try different USB port

**Check USB Mode**:
1. When connected, swipe down notification
2. Tap "USB for charging"
3. Select **"File Transfer"** or **"MTP"**

**Restart ADB**:
```bash
adb kill-server
adb start-server
adb devices
```

**Check Drivers** (Windows):
- Install Google USB Driver
- Or install phone manufacturer's USB driver

### Still Not Working?

**Run Flutter Doctor**:
```bash
flutter doctor -v
```

This will show what's missing.

**Check ADB Devices**:
```bash
adb devices
```

Should show:
```
List of devices attached
1234567890    device
```

If shows "unauthorized", check phone for USB debugging popup.

---

## ⚡ Quick Commands:

### Check Connected Devices
```bash
flutter devices
```

### Run on Specific Device
```bash
flutter run -d <device-id>
```

### Run on Any Connected Device
```bash
flutter run
```

### Hot Reload (while app is running)
Press `r` in terminal

### Hot Restart (while app is running)
Press `R` in terminal

### Stop App
Press `q` in terminal

---

## 📋 Checklist:

Before connecting:
- [ ] Developer Options enabled
- [ ] USB Debugging enabled
- [ ] USB cable is data cable (not charging-only)
- [ ] Phone is unlocked
- [ ] USB mode set to File Transfer

After connecting:
- [ ] "Allow USB debugging" popup appeared
- [ ] Tapped "Always allow"
- [ ] `flutter devices` shows your phone
- [ ] Ready to run!

---

## 🎯 Once Connected:

Your phone will appear in `flutter devices` like:
```
Samsung Galaxy S10 (mobile) • 1234567890 • android-arm64 • Android 10
```

Then just run:
```bash
flutter run
```

The app will:
1. Build for Android (2-5 minutes first time)
2. Install on your phone
3. Launch automatically
4. Show hot reload ready!

---

**Need help? Let me know what error you see!** 🚀
