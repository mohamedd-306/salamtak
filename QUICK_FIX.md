# Quick Fix - Use Open Firestore Rules

Since Firebase Auth is causing issues on Windows, use these open Firestore rules:

## Step 1: Update Firestore Rules

1. Go to https://console.firebase.google.com/
2. Select project: `salmtak-6fffe`
3. Go to **Firestore Database** → **Rules**
4. Replace with:

```javascript
rules_version = '2';
service cloud.firestore {
  match /databases/{database}/documents {
    match /{document=**} {
      allow read, write: if true;
    }
  }
}
```

5. Click **Publish**

## Step 2: Restart Your App

After publishing the rules, restart your app.

## Credentials

**Admin:**
- National ID: `12345678901234`
- Password: `admin123456`

**Test User:**
- National ID: `11111111111111`
- Password: `user123456`

These will work immediately with the open rules.

## Why This Works

The open rules allow all read/write operations without authentication, so the hardcoded users (which don't have Firebase Auth sessions) can access Firestore.

## Security Note

These rules are NOT secure for production. They're only for development/testing. Once you deploy to production, you'll need to:
1. Fix Firebase Auth for Windows
2. Use secure rules that require authentication
