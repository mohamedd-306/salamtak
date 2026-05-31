# Testing Guide: Reports & Images Bugfix

## Quick Start

### 1. Install Dependencies
```bash
cd "c:\New folder\htdocs\swebsite\salamtak - Copy"
flutter pub get
```

### 2. Configure Base URL
Open `lib/config/app_config.dart` and update the base URL based on your setup:

**For Android Emulator:**
```dart
static const String baseUrl = 'http://10.0.2.2:8000';
```

**For iOS Simulator:**
```dart
static const String baseUrl = 'http://localhost:8000';
```

**For Physical Device (same WiFi network):**
```dart
static const String baseUrl = 'http://192.168.1.100:8000';  // Use your computer's IP
```

### 3. Start PHP Server
Make sure your PHP server is running:
```bash
cd salamtak_web
php -S localhost:8000
```

### 4. Run the App
```bash
flutter run
```

---

## Test Scenarios

### ✅ Test 1: View Existing Reports
**Goal**: Verify reports display correctly

1. Login as test user:
   - National ID: `11111111111111`
   - Password: `user123456`

2. Navigate to History screen
3. **Expected Results**:
   - All reports should display
   - Images should load (with loading indicator first)
   - No "image unavailable" placeholders for valid images
   - Reports sorted by date (newest first)

**Check Console Logs**:
```
=== FETCHING REPORTS BY NATIONAL ID ===
National ID: 11111111111111
Found X reports for National ID: 11111111111111
```

---

### ✅ Test 2: Create Report from App
**Goal**: Verify new reports with Firebase Storage images work

1. Login as test user
2. Go to Services → Select problem type
3. Fill in details and **upload an image**
4. Submit report
5. Go to History screen
6. **Expected Results**:
   - New report appears at the top
   - Image loads correctly
   - Image URL starts with `https://firebasestorage.googleapis.com`

**Check Console Logs**:
```
=== REPORT IMAGE WIDGET ===
Original path: https://firebasestorage.googleapis.com/...
Full URL: https://firebasestorage.googleapis.com/...
Is Firebase: true
Is Website: false
```

---

### ✅ Test 3: View Website Reports
**Goal**: Verify reports created from website display correctly

1. Create a report from the website (PHP)
2. Login to app with same National ID
3. Go to History screen
4. **Expected Results**:
   - Website report appears in list
   - Image loads correctly
   - Image URL constructed as `http://10.0.2.2:8000/uploads/...`

**Check Console Logs**:
```
=== REPORT IMAGE WIDGET ===
Original path: uploads/report_123.jpg
Full URL: http://10.0.2.2:8000/uploads/report_123.jpg
Is Firebase: false
Is Website: true
```

---

### ✅ Test 4: Admin Dashboard
**Goal**: Verify admin can see all reports with images

1. Login as admin:
   - Work ID: `221007689`
   - Password: `631663`

2. View admin dashboard
3. **Expected Results**:
   - All reports display with thumbnails
   - Images load correctly (100x100 thumbnails)
   - Mix of Firebase and website images work

---

### ✅ Test 5: Empty State
**Goal**: Verify empty state works correctly

1. Login with a new user (or user with no reports)
2. Go to History screen
3. **Expected Results**:
   - Empty state message displays
   - No errors in console
   - "No reports yet" message shown

---

### ✅ Test 6: Error Handling
**Goal**: Verify error states work correctly

**Test 6a: No Internet**
1. Turn off WiFi/Data
2. Open History screen
3. **Expected**: Error placeholder with "Image unavailable" icon

**Test 6b: Invalid Image URL**
1. Manually create report with invalid image path in Firestore
2. View in app
3. **Expected**: Error placeholder, no crash

---

### ✅ Test 7: Performance
**Goal**: Verify image caching works

1. Open History screen with multiple reports
2. Scroll through list
3. Go back and return to History
4. **Expected Results**:
   - Images load faster on second view (cached)
   - Smooth scrolling
   - No lag or stuttering

---

### ✅ Test 8: Different Image Sources
**Goal**: Verify all image source types work

Create/view reports with:
- ✅ Firebase Storage URL (`https://firebasestorage.googleapis.com/...`)
- ✅ Website relative path (`uploads/image.jpg`)
- ✅ Website absolute path (`/uploads/image.jpg`)
- ✅ No image (empty string)

**Expected**: All scenarios handled correctly

---

## Troubleshooting

### Problem: Images not loading on physical device
**Solution**: 
1. Find your computer's IP address:
   - Windows: `ipconfig` → Look for IPv4 Address
   - Mac/Linux: `ifconfig` → Look for inet address
2. Update `AppConfig.baseUrl` to `http://YOUR_IP:8000`
3. Ensure phone and computer on same WiFi network

### Problem: "Failed host lookup" error
**Solution**: 
- Check PHP server is running: `php -S localhost:8000`
- Verify base URL is correct for your device type
- Check firewall isn't blocking port 8000

### Problem: Firebase images not loading
**Solution**:
- Check Firebase Storage rules allow read access
- Verify image URLs are complete and valid
- Check Firebase project configuration

### Problem: Reports not showing at all
**Solution**:
1. Check console logs for Firestore errors
2. Verify National ID matches between login and reports
3. Check Firestore collection has data:
   ```
   Firebase Console → Firestore → reports collection
   ```

### Problem: "Firestore index required" error
**Solution**: This should NOT happen anymore (we removed orderBy). If it does:
1. Check `database_service.dart` - ensure no `orderBy` in queries
2. Reports should be sorted in memory, not in Firestore

---

## Debug Checklist

When testing, verify these console logs appear:

### ✅ On Login:
```
=== LOGIN ATTEMPT ===
ID: 11111111111111
✓ Test user login successful (hardcoded bypass)
```

### ✅ On Viewing History:
```
=== FETCHING REPORTS BY NATIONAL ID ===
National ID: 11111111111111
Found X reports for National ID: 11111111111111
```

### ✅ On Loading Images:
```
=== REPORT IMAGE WIDGET ===
Original path: uploads/report_123.jpg
Full URL: http://10.0.2.2:8000/uploads/report_123.jpg
Is Firebase: false
Is Website: true
```

### ❌ Should NOT See:
- `❌ Error fetching reports by nationalId`
- `Firestore index required`
- `Failed host lookup`
- Repeated image loading errors

---

## Success Criteria

All tests pass when:
- ✅ Reports display in user history
- ✅ Reports display in admin dashboard
- ✅ Firebase Storage images load
- ✅ Website uploaded images load
- ✅ Loading indicators show while fetching
- ✅ Error placeholders show for failed images
- ✅ No console errors
- ✅ Smooth performance
- ✅ Image caching works

---

## Performance Benchmarks

**Expected Load Times**:
- First image load: 1-3 seconds (network dependent)
- Cached image load: < 100ms
- Report list load: < 1 second for 10 reports

**Memory Usage**:
- Should remain stable with image caching
- No memory leaks when scrolling

---

## Next Steps After Testing

1. ✅ Verify all tests pass
2. ✅ Check console logs are clean
3. ✅ Test on both emulator and physical device
4. ✅ Test with different network conditions
5. ✅ Update `AppConfig` for production deployment

---

**Happy Testing! 🚀**

If you encounter any issues not covered here, check:
1. Console logs for detailed error messages
2. `BUGFIX_COMPLETE.md` for implementation details
3. Firebase Console for data verification
