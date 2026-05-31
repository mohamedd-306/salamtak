# Salamtak App - Credentials & Firebase Setup

## 🔑 Login Credentials

### Admin Account (Hardcoded - Always Works)
```
National ID: 12345678901234
Password: admin123456
```
- This account bypasses Firebase Auth
- Works immediately without any setup
- Has full admin privileges

### Test User Account (Hardcoded - Always Works)
```
National ID: 11111111111111
Password: user123456
```
- This account bypasses Firebase Auth
- Works immediately without any setup
- Regular user privileges

---

## 📝 Signup Functionality

The signup feature is already implemented in your app! Users can:

1. Click "Don't have an account? Sign Up" on the login screen
2. Fill in the registration form:
   - National ID (14 digits)
   - Full Name
   - Address
   - Email
   - Phone Number
   - Password (minimum 6 characters)
   - Confirm Password
3. Click "Create Account"

### Current Status
✅ Signup UI is complete
✅ Form validation is working
✅ Firebase Auth integration is ready
⚠️ **Firebase configuration needs to be completed** (see below)

---

## 🔧 Firebase Setup Required

Your app is experiencing Firebase Auth errors because the Firebase project needs proper configuration for Windows platform. Here's what you need to do:

### Step 1: Fix Firestore Security Rules
1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Select your project: `salmtak-6fffe`
3. Go to **Firestore Database** → **Rules**
4. Replace the rules with:

```javascript
rules_version = '2';
service cloud.firestore {
  match /databases/{database}/documents {
    // Allow users to read/write their own data
    match /users/{userId} {
      allow read, write: if request.auth != null && request.auth.uid == userId;
      allow read: if request.auth != null; // Allow all authenticated users to read user profiles
    }
    
    // Allow users to create and read their own reports
    match /reports/{reportId} {
      allow create: if request.auth != null;
      allow read: if request.auth != null && 
                     (resource.data.uid == request.auth.uid || 
                      get(/databases/$(database)/documents/users/$(request.auth.uid)).data.userType == 'admin');
      allow update, delete: if request.auth != null && 
                               get(/databases/$(database)/documents/users/$(request.auth.uid)).data.userType == 'admin';
    }
  }
}
```

5. Click **Publish**

### Step 2: Enable Email/Password Authentication
1. In Firebase Console, go to **Authentication** → **Sign-in method**
2. Click on **Email/Password**
3. Enable it and click **Save**

### Step 3: Configure Firebase for Windows (Optional but Recommended)
The current Firebase configuration might not be fully compatible with Windows desktop. To fix this:

1. In Firebase Console, go to **Project Settings** (gear icon)
2. Scroll down to **Your apps**
3. If you don't see a Windows app, you may need to add it or use the Web configuration
4. Make sure the API key and other credentials in `lib/main.dart` match your Firebase project

### Step 4: Test Signup
After completing the above steps:

1. Restart your app
2. Click "Don't have an account? Sign Up"
3. Fill in the form with test data:
   ```
   National ID: 22222222222222
   Name: John Doe
   Address: 123 Test Street
   Email: john@example.com
   Phone: 01234567890
   Password: test123456
   Confirm Password: test123456
   ```
4. Click "Create Account"
5. Check the console output for success/error messages

---

## 🐛 Troubleshooting

### If signup still fails after Firebase setup:
1. Check the console output for specific error messages
2. Verify Firestore rules are published
3. Verify Email/Password auth is enabled
4. Try using the hardcoded test accounts first to verify the app works

### If you see "permission-denied" errors:
- Your Firestore security rules need to be updated (see Step 1 above)

### If you see "unknown-error" during signup:
- Firebase Auth might not be properly configured for Windows
- The hardcoded accounts will still work
- Consider testing on web or mobile platforms

---

## 📱 How It Works

### Login Flow:
1. User enters National ID and password
2. App checks hardcoded accounts first (admin & test user)
3. If not hardcoded, tries Firebase Auth with email: `{nationalId}@salamtak.com`
4. On success, fetches user profile from Firestore
5. Redirects to appropriate home screen (admin or user)

### Signup Flow:
1. User fills registration form
2. App creates Firebase Auth account with email: `{nationalId}@salamtak.com`
3. App creates user profile in Firestore with userType: 'user'
4. User is automatically logged in
5. Redirects to user home screen

---

## ✅ Summary

**What's Working Now:**
- ✅ Admin login (hardcoded)
- ✅ Test user login (hardcoded)
- ✅ Signup UI and validation
- ✅ Signup code implementation

**What Needs Firebase Setup:**
- ⚠️ Firestore security rules
- ⚠️ Email/Password authentication enabled
- ⚠️ Real user signup and login via Firebase

**Immediate Next Steps:**
1. Update Firestore security rules (Step 1 above)
2. Enable Email/Password auth (Step 2 above)
3. Test signup with a new user
4. Verify the new user can login

Once Firebase is properly configured, users will be able to sign up and login normally, while the hardcoded accounts will continue to work as a backup.
