# Admin Product Management - Deployment Guide

## Prerequisites

Before deploying the Admin Product Management feature, ensure you have:

- [x] Flutter SDK installed (version 3.7.0 or higher)
- [x] Firebase project created and configured
- [x] Firebase CLI installed (`npm install -g firebase-tools`)
- [x] Admin user account with `userType: 'admin'` in Firestore
- [x] All dependencies installed (`flutter pub get`)

## Deployment Steps

### Step 1: Verify Implementation

Ensure all required files are in place:

```bash
# Check if files exist
ls lib/services/product_service.dart
ls lib/screens/admin/product_management_screen.dart
ls lib/screens/admin/product_form_screen.dart
ls lib/widgets/product_card.dart
ls lib/constants/product_categories.dart
ls firestore.rules
ls storage.rules
ls firestore.indexes.json
```

### Step 2: Deploy Firebase Security Rules

#### 2.1 Login to Firebase

```bash
firebase login
```

Follow the prompts to authenticate with your Google account.

#### 2.2 Initialize Firebase (if not already done)

```bash
firebase init
```

Select:
- Firestore
- Storage

Accept default file locations.

#### 2.3 Deploy Firestore Rules

```bash
firebase deploy --only firestore:rules
```

Expected output:
```
✔  firestore: released rules firestore.rules to cloud.firestore
✔  Deploy complete!
```

#### 2.4 Deploy Storage Rules

```bash
firebase deploy --only storage
```

Expected output:
```
✔  storage: released rules storage.rules
✔  Deploy complete!
```

#### 2.5 Deploy Firestore Indexes

```bash
firebase deploy --only firestore:indexes
```

Expected output:
```
✔  firestore: deployed indexes in firestore.indexes.json successfully
✔  Deploy complete!
```

### Step 3: Verify Firebase Configuration

#### 3.1 Check Firestore Rules

1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Select your project
3. Navigate to **Firestore Database** → **Rules**
4. Verify the rules include:
   - Products collection with admin-only write access
   - Users collection with proper access controls

#### 3.2 Check Storage Rules

1. In Firebase Console, navigate to **Storage** → **Rules**
2. Verify the rules include:
   - Products folder with public read, admin-only write

#### 3.3 Check Indexes

1. In Firebase Console, navigate to **Firestore Database** → **Indexes**
2. Verify the composite index exists:
   - Collection: `products`
   - Fields: `category` (Ascending), `createdAt` (Descending)

### Step 4: Create Admin User

If you don't have an admin user yet:

#### 4.1 Create User via Firebase Console

1. Go to **Authentication** → **Users**
2. Click **Add User**
3. Enter email and password
4. Note the User UID

#### 4.2 Set Admin Privileges

1. Go to **Firestore Database**
2. Navigate to `users` collection
3. Find or create document with the User UID
4. Set the following fields:
   ```json
   {
     "email": "admin@example.com",
     "name": "Admin User",
     "userType": "admin",
     "createdAt": "2024-01-01T00:00:00.000Z"
   }
   ```

### Step 5: Test the Feature

#### 5.1 Build and Run the App

```bash
# For Android
flutter run

# For iOS
flutter run -d ios

# For specific device
flutter devices
flutter run -d <device-id>
```

#### 5.2 Test Admin Access

1. Launch the app
2. Log in with admin credentials
3. Navigate to Admin Home Screen
4. Verify "Manage Products" button is visible
5. Tap the button to open Product Management screen

#### 5.3 Test Product Creation

1. Tap the "+" floating action button
2. Fill in all fields:
   - Name: "Test Product"
   - Description: "This is a test product"
   - Price: "99.99"
   - Stock: "10"
   - Category: Select any category
3. Tap image placeholder and select an image
4. Tap "Create Product"
5. Verify success message appears
6. Verify product appears in the list

#### 5.4 Test Product Editing

1. Find the test product in the list
2. Tap the edit icon (pencil)
3. Modify any field (e.g., change price to "89.99")
4. Tap "Update Product"
5. Verify success message appears
6. Verify changes are reflected in the list

#### 5.5 Test Product Deletion

1. Find the test product in the list
2. Tap the delete icon (trash)
3. Confirm deletion in the dialog
4. Verify success message appears
5. Verify product is removed from the list

#### 5.6 Test Search and Filter

1. Create multiple test products in different categories
2. Test search by typing product names
3. Test category filter by selecting different categories
4. Verify results update correctly

#### 5.7 Test Real-time Sync

1. Open the admin website in a browser
2. Add a product in the website
3. Verify it appears in the mobile app immediately
4. Edit a product in the mobile app
5. Verify changes appear in the website immediately

### Step 6: Verify Security

#### 6.1 Test Non-Admin Access

1. Create a regular user account (without admin privileges)
2. Log in with the regular user account
3. Verify "Manage Products" button is NOT visible in Admin Home
4. Attempt to navigate directly to Product Management (if possible)
5. Verify access is denied

#### 6.2 Test Firebase Rules

Use Firebase Console to test rules:

1. Go to **Firestore Database** → **Rules** → **Rules Playground**
2. Test read access:
   - Location: `/products/{productId}`
   - Authenticated: Yes
   - Expected: Allow
3. Test write access (non-admin):
   - Location: `/products/{productId}`
   - Authenticated: Yes (non-admin user)
   - Expected: Deny
4. Test write access (admin):
   - Location: `/products/{productId}`
   - Authenticated: Yes (admin user)
   - Expected: Allow

### Step 7: Monitor and Debug

#### 7.1 Enable Firebase Logging

In your app, monitor Firebase operations:

```dart
// Check console logs for Firebase operations
// Look for messages like:
// ✓ Product created with ID: abc123
// ✓ Product updated: abc123
// ✓ Image uploaded: https://...
```

#### 7.2 Check Firebase Console Logs

1. Go to Firebase Console
2. Navigate to **Firestore Database** → **Usage**
3. Monitor read/write operations
4. Check for any errors or permission denied messages

#### 7.3 Monitor Storage Usage

1. Go to **Storage** → **Usage**
2. Monitor file uploads and storage size
3. Verify images are being uploaded correctly

### Step 8: Production Deployment

#### 8.1 Build Release Version

For Android:
```bash
flutter build apk --release
# or
flutter build appbundle --release
```

For iOS:
```bash
flutter build ios --release
```

#### 8.2 Test Release Build

1. Install the release build on a test device
2. Repeat all tests from Step 5
3. Verify performance is acceptable
4. Check for any release-specific issues

#### 8.3 Deploy to App Stores

Follow platform-specific guidelines:

**Google Play Store:**
1. Create app listing
2. Upload APK/App Bundle
3. Complete store listing
4. Submit for review

**Apple App Store:**
1. Create app in App Store Connect
2. Upload IPA via Xcode or Transporter
3. Complete app information
4. Submit for review

## Post-Deployment Checklist

- [ ] Firebase rules deployed successfully
- [ ] Firestore indexes created
- [ ] Admin user account configured
- [ ] Product creation tested
- [ ] Product editing tested
- [ ] Product deletion tested
- [ ] Search and filter tested
- [ ] Real-time sync verified
- [ ] Security rules tested
- [ ] Non-admin access blocked
- [ ] Image upload working
- [ ] Image compression working
- [ ] Error handling working
- [ ] Success messages displaying
- [ ] App performance acceptable
- [ ] Release build tested
- [ ] Documentation updated

## Rollback Procedure

If issues are discovered after deployment:

### 1. Revert Firebase Rules

```bash
# Restore previous rules from backup
firebase deploy --only firestore:rules
firebase deploy --only storage
```

### 2. Disable Feature

Temporarily hide the "Manage Products" button in `AdminHomeScreen`:

```dart
// Comment out or remove the Manage Products button
// ElevatedButton(
//   onPressed: () => Navigator.push(...),
//   child: Text('Manage Products'),
// ),
```

### 3. Investigate Issues

1. Check Firebase Console logs
2. Review app crash reports
3. Test in development environment
4. Fix issues and redeploy

## Troubleshooting

### Issue: Rules deployment fails

**Solution:**
```bash
# Check rules syntax
firebase deploy --only firestore:rules --debug

# Verify you're logged in
firebase login --reauth

# Check project selection
firebase use --add
```

### Issue: Indexes not created

**Solution:**
1. Check `firestore.indexes.json` syntax
2. Manually create indexes in Firebase Console
3. Wait for index creation to complete (can take several minutes)

### Issue: Admin user cannot access feature

**Solution:**
1. Verify user document in Firestore has `userType: 'admin'`
2. Check user is authenticated
3. Verify Firebase rules are deployed
4. Check app logs for permission errors

### Issue: Images not uploading

**Solution:**
1. Check Storage rules are deployed
2. Verify Firebase Storage is enabled in project
3. Check storage quota hasn't been exceeded
4. Verify image file size and format

## Monitoring and Maintenance

### Daily Checks

- Monitor Firebase Console for errors
- Check storage usage
- Review user feedback

### Weekly Checks

- Review Firebase usage metrics
- Check for security rule violations
- Monitor app performance

### Monthly Checks

- Review and optimize Firestore queries
- Clean up unused images in Storage
- Update dependencies if needed
- Review and update documentation

## Support Contacts

- **Firebase Support**: https://firebase.google.com/support
- **Flutter Support**: https://flutter.dev/community
- **Project Repository**: [Your repository URL]

---

**Deployment Date**: ___________
**Deployed By**: ___________
**Version**: 1.0.0
**Status**: ✅ Ready for Production
