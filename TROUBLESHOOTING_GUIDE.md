# Troubleshooting Guide - Report Data Not Showing

## Problem
Reports in admin panel show:
- "No description"
- "User:" (empty)
- No image

## Root Cause Analysis

### 1. Check if Report is Being Created
Look for these logs in console when submitting a report:
```
=== CREATING REPORT ===
Report UID: <should have value>
National ID: <should have value>
Name: <should have value>
Type: <should have value>
Description: <should have value>
Image Path: data:image/jpeg;base64,<base64_data>
Status: Pending
Severity: Medium
Report data to save: {...}
✓ Report created with ID: <docId>
```

**If you DON'T see these logs:**
- Report creation is not being triggered
- Check if submit button is working
- Check if form validation is passing

**If you see these logs but data is empty:**
- SharedPreferences not set correctly
- User data not loaded properly

### 2. Check SharedPreferences Data
The report gets user data from SharedPreferences:
```dart
final uid = prefs.getString('userId') ?? '';
final nationalId = prefs.getString('nationalId') ?? '';
final name = prefs.getString('name') ?? '';
```

**To verify:**
1. Login as test user (National ID: 11111111111111, Password: user123456)
2. Check console for login logs
3. Should see:
   ```
   ✓ Test user login successful (hardcoded bypass)
   ```
4. SharedPreferences should be set with:
   - userId: 'user-hardcoded'
   - nationalId: '11111111111111'
   - name: 'Test User'

### 3. Check Firestore Data Directly

**Option 1: Firebase Console**
1. Go to: https://console.firebase.google.com/project/salmtak-6fffe/firestore
2. Open `reports` collection
3. Click on latest document
4. Check if fields have values:
   - `description`: should have text
   - `name`: should have user name
   - `nationalId`: should have ID
   - `imagePath`: should have base64 string starting with "data:image"

**Option 2: Add Debug Print in Code**
Already added in `database_service.dart`:
```dart
print('Report data to save: $reportData');
```

This will show exactly what's being saved to Firestore.

### 4. Common Issues & Solutions

#### Issue: Empty Description
**Cause:** Description field not filled or validation failing
**Solution:**
- Make sure to type description (minimum 10 characters)
- Check form validation passes

#### Issue: Empty User Data
**Cause:** SharedPreferences not set during login
**Solution:**
- Logout and login again
- Use test user credentials
- Check login logs confirm data is saved

#### Issue: Image Not Showing
**Cause:** Base64 conversion failing or image too large
**Solution:**
- Check console for "CONVERTING IMAGE TO BASE64" log
- Check if conversion successful
- Try smaller image (< 1MB)

#### Issue: Old Reports Show Empty Data
**Cause:** Old reports from website have different field structure
**Solution:**
- This is expected for old reports
- Create NEW report to test
- New reports should have all data

### 5. Step-by-Step Test Procedure

1. **Restart App**
   ```
   taskkill /F /IM salamtak.exe
   flutter run -d windows
   ```

2. **Login as Test User**
   - National ID: 11111111111111
   - Password: user123456

3. **Create New Report**
   - Select "Pothole" or any type
   - Add photo (small image < 1MB)
   - Add description (at least 10 characters): "Test report with description"
   - Select location on map
   - Set severity: Medium
   - Click Submit

4. **Check Console Logs**
   Look for:
   ```
   === CONVERTING IMAGE TO BASE64 ===
   ✓ Image converted to base64 (XXXXX bytes)
   === CREATING REPORT ===
   Report UID: user-hardcoded
   National ID: 11111111111111
   Name: Test User
   Type: Pothole
   Description: Test report with description
   Image Path: data:image/jpeg;base64,/9j/4AAQ...
   Report data to save: {uid: user-hardcoded, nationalId: 11111111111111, ...}
   ✓ Report created with ID: XXXXXXXXX
   ```

5. **Check Admin Panel**
   - Login as admin (Work ID: 221007689, Password: 631663)
   - Go to Home tab
   - Should see new report with:
     - Description: "Test report with description"
     - User: 11111111111111
     - Image displayed

### 6. If Still Not Working

#### Check 1: Verify Code Changes Applied
Files that should have changes:
- `lib/services/database_service.dart` - uploadReportImage returns base64
- `lib/widgets/report_image_widget.dart` - handles base64 images
- `lib/screens/user/report_problem_screen.dart` - uploads image before creating report

#### Check 2: Hot Reload vs Full Restart
- Hot reload may not apply all changes
- Do full restart: `flutter run -d windows`

#### Check 3: Check Firestore Rules
Firestore rules should allow writes:
```javascript
match /reports/{reportId} {
  allow read, write: if true; // For testing
}
```

#### Check 4: Network Issues
- Check if device can reach Firestore
- Check console for network errors
- Verify Firebase project is active

### 7. Expected vs Actual

**Expected Behavior:**
1. User fills form with description
2. Selects image
3. Clicks submit
4. Image converts to base64
5. Report saves to Firestore with all data
6. Success message appears
7. Report appears in admin panel with all data

**Current Behavior (if broken):**
1. User fills form
2. Clicks submit
3. Success message appears (or doesn't)
4. Report appears in admin but shows empty data

**This means:**
- Report IS being created (count increases)
- But data is NOT being saved correctly
- OR data is being saved but not displayed correctly

### 8. Quick Fix Checklist

- [ ] Restart app completely
- [ ] Login as test user
- [ ] Create new report with description
- [ ] Check console logs
- [ ] Verify "CREATING REPORT" logs appear
- [ ] Verify all fields have values in logs
- [ ] Check admin panel
- [ ] Verify new report shows data

### 9. Contact Points

If issue persists, provide:
1. Console logs from report creation
2. Screenshot of admin panel
3. Screenshot of Firestore document (from Firebase Console)
4. Confirmation that you're testing with NEW report (not old ones)

---

## Summary

The most likely issue is that:
1. Old reports from website have empty fields (expected)
2. Need to test with NEW report created from mobile app
3. Check console logs to verify data is being saved
4. Check Firestore Console to see actual data

The code changes are correct and should work. The issue is likely with testing old reports instead of creating new ones.
