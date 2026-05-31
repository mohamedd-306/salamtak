# Deploy Firestore Security Rules - Quick Guide

## Task 1.2: Configure Firebase Security Rules for Products Collection

This guide provides step-by-step instructions to deploy the Firestore security rules for admin-only write access to the products collection.

## Prerequisites

- Firebase project already set up
- Firebase CLI installed (optional, for CLI deployment)
- Admin access to Firebase Console

## Files Created

1. **firestore.rules** - Security rules definition
2. **firebase.json** - Firebase project configuration
3. **firestore.indexes.json** - Firestore indexes configuration
4. **FIRESTORE_SECURITY_RULES.md** - Comprehensive documentation

## Quick Deployment (Firebase Console)

### Step 1: Access Firebase Console
1. Open your browser and go to https://console.firebase.google.com/
2. Select your Salamtak project

### Step 2: Navigate to Firestore Rules
1. Click on **Firestore Database** in the left sidebar
2. Click on the **Rules** tab at the top

### Step 3: Copy and Paste Rules
1. Open the `firestore.rules` file in your project
2. Copy all the contents
3. Paste into the Firebase Console rules editor (replace existing rules)

### Step 4: Publish Rules
1. Click the **Publish** button
2. Wait for confirmation message
3. Rules are now live!

## Deployment Using Firebase CLI

### Step 1: Install Firebase CLI (if not installed)
```bash
npm install -g firebase-tools
```

### Step 2: Login to Firebase
```bash
firebase login
```
Follow the prompts to authenticate with your Google account.

### Step 3: Initialize Firebase (if not already done)
```bash
firebase init firestore
```
- Select your Firebase project from the list
- Accept the default `firestore.rules` file location
- Accept the default `firestore.indexes.json` file location

### Step 4: Deploy Rules
```bash
firebase deploy --only firestore:rules
```

You should see output like:
```
=== Deploying to 'your-project-id'...

i  deploying firestore
i  firestore: checking firestore.rules for compilation errors...
✔  firestore: rules file firestore.rules compiled successfully
i  firestore: uploading rules firestore.rules...
✔  firestore: released rules firestore.rules to cloud.firestore

✔  Deploy complete!
```

## Verification

### Test 1: Admin User Can Write
1. Login to the app as an admin user
2. Navigate to Product Management
3. Try to create a new product
4. **Expected**: Product created successfully

### Test 2: Regular User Can Read
1. Login to the app as a regular user
2. Navigate to Products screen
3. **Expected**: Products are visible

### Test 3: Regular User Cannot Write
1. While logged in as a regular user
2. Try to create/edit/delete a product (if you have access to the API)
3. **Expected**: Permission denied error

### Test 4: Unauthenticated Access Denied
1. Logout from the app
2. Try to access products
3. **Expected**: Permission denied error

## What the Rules Do

### Products Collection
- ✅ **Read**: All authenticated users (admin and regular users)
- ✅ **Create**: Admin users only
- ✅ **Update**: Admin users only
- ✅ **Delete**: Admin users only

### Validation Rules
- Product name: 1-100 characters
- Product description: 1-500 characters
- Price: Must be positive number
- Stock: Must be non-negative integer
- Category: Must not be empty
- Image: Must not be empty
- createdAt: Cannot be modified during updates

### Admin Verification
- Admin status is verified by checking `userType == 'admin'` in the users collection
- This check happens server-side, so it cannot be bypassed by client code

## Troubleshooting

### Error: "Permission denied"
**Cause**: User doesn't have admin privileges or isn't authenticated

**Solution**:
1. Check that the user is logged in
2. Verify the user's `userType` field in Firestore is set to 'admin'
3. Try logging out and logging back in

### Error: "Rules compilation failed"
**Cause**: Syntax error in firestore.rules file

**Solution**:
1. Check the rules file for syntax errors
2. Ensure all brackets and parentheses are properly closed
3. Use the Firebase Console rules editor to validate syntax

### Rules Not Taking Effect
**Cause**: Rules not deployed or cached

**Solution**:
1. Redeploy the rules using Firebase CLI
2. Clear browser cache
3. Wait 30-60 seconds for rules to propagate
4. Check Firebase Console to confirm rules are published

## Security Notes

⚠️ **Important Security Considerations**:

1. **Never bypass security rules**: Always enforce permissions server-side
2. **Admin verification**: Admin status is checked in Firestore, not in client code
3. **Data validation**: All product data is validated before being written
4. **Timestamp protection**: createdAt timestamp cannot be modified during updates
5. **Authentication required**: All operations require a valid Firebase Auth token

## Next Steps

After deploying the rules:

1. ✅ Test with admin user account
2. ✅ Test with regular user account
3. ✅ Verify real-time sync between mobile app and admin website
4. ✅ Monitor Firebase Console for any permission denied errors
5. ✅ Proceed to Task 1.3: Configure Firebase Storage Security Rules

## Related Documentation

- **FIRESTORE_SECURITY_RULES.md** - Comprehensive security rules documentation
- **firestore.rules** - The actual rules file
- **firebase.json** - Firebase project configuration

## Support

If you encounter issues:
1. Check the Firebase Console logs
2. Review the FIRESTORE_SECURITY_RULES.md documentation
3. Consult Firebase documentation: https://firebase.google.com/docs/firestore/security/get-started

---

**Task Status**: ✅ Configuration files created and ready for deployment
**Next Action**: Deploy rules using one of the methods above
