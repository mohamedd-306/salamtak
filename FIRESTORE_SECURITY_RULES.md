# Firestore Security Rules Documentation

## Overview

This document explains the Firebase Firestore security rules configured for the Salamtak application, specifically for the admin product management feature.

## Security Rules File

The security rules are defined in `firestore.rules` at the root of the project.

## Products Collection Rules

### Read Access
- **Who can read**: All authenticated users (both admin and regular users)
- **Purpose**: Allows users to view products in the app and admins to manage them
- **Rule**: `allow read: if isAuthenticated();`

### Write Access (Create, Update, Delete)
- **Who can write**: Only authenticated admin users
- **Verification**: Checks the `userType` field in the users collection
- **Rule**: `allow create, update, delete: if isAdmin();`

### Data Validation

#### Product Creation Validation
When creating a new product, the following validations are enforced:

1. **Required Fields**: name, description, price, stock, category, image, createdAt, updatedAt
2. **Name**: 
   - Must be a string
   - Cannot be empty
   - Maximum 100 characters
3. **Description**:
   - Must be a string
   - Cannot be empty
   - Maximum 500 characters
4. **Price**:
   - Must be a number
   - Must be greater than 0
5. **Stock**:
   - Must be an integer
   - Must be greater than or equal to 0
6. **Category**:
   - Must be a string
   - Cannot be empty
7. **Image**:
   - Must be a string (URL)
   - Cannot be empty

#### Product Update Validation
When updating a product, all the same validations apply as creation, plus:
- **createdAt timestamp**: Must remain unchanged (cannot be modified during updates)

## Users Collection Rules

### Read Access
- Users can read their own data
- Admins can read all user data

### Write Access
- Users can update their own data (except the `userType` field)
- Only admins can create new users
- Only admins can change the `userType` field
- Only admins can delete users

## Helper Functions

### isAuthenticated()
Checks if the user is logged in (has a valid Firebase Authentication token).

```javascript
function isAuthenticated() {
  return request.auth != null;
}
```

### isAdmin()
Checks if the authenticated user has admin privileges by verifying the `userType` field in the users collection.

```javascript
function isAdmin() {
  return isAuthenticated() && 
         get(/databases/$(database)/documents/users/$(request.auth.uid)).data.userType == 'admin';
}
```

## Deployment Instructions

### Option 1: Using Firebase Console (Recommended for First-Time Setup)

1. Go to the [Firebase Console](https://console.firebase.google.com/)
2. Select your project
3. Navigate to **Firestore Database** in the left sidebar
4. Click on the **Rules** tab
5. Copy the contents of `firestore.rules` file
6. Paste into the rules editor
7. Click **Publish** to deploy the rules

### Option 2: Using Firebase CLI

1. Install Firebase CLI if not already installed:
   ```bash
   npm install -g firebase-tools
   ```

2. Login to Firebase:
   ```bash
   firebase login
   ```

3. Initialize Firebase in your project (if not already done):
   ```bash
   firebase init firestore
   ```
   - Select your Firebase project
   - Accept the default `firestore.rules` file location

4. Deploy the rules:
   ```bash
   firebase deploy --only firestore:rules
   ```

### Option 3: Using firebase.json Configuration

If you have a `firebase.json` file in your project, ensure it includes:

```json
{
  "firestore": {
    "rules": "firestore.rules"
  }
}
```

Then deploy with:
```bash
firebase deploy --only firestore:rules
```

## Testing Security Rules

### Test Admin Access
1. Login as an admin user (userType: 'admin')
2. Try to create, update, or delete a product
3. Operation should succeed

### Test Regular User Access
1. Login as a regular user (userType: 'user')
2. Try to view products (should succeed)
3. Try to create, update, or delete a product (should fail with permission denied)

### Test Unauthenticated Access
1. Logout or use an unauthenticated request
2. Try to read or write products (should fail with permission denied)

## Security Best Practices

1. **Never trust client-side validation**: Always enforce validation in security rules
2. **Principle of least privilege**: Users only have access to what they need
3. **Admin verification**: Admin status is verified server-side by checking the users collection
4. **Data integrity**: Timestamps and critical fields are protected from unauthorized modification
5. **Authentication required**: All operations require authentication

## Troubleshooting

### Permission Denied Errors

If you encounter "permission denied" errors:

1. **Check user authentication**: Ensure the user is logged in
2. **Verify admin status**: Check that the user's `userType` field is set to 'admin' in Firestore
3. **Check rules deployment**: Ensure the latest rules are deployed to Firebase
4. **Review Firestore logs**: Check the Firebase Console for detailed error messages

### Rules Not Taking Effect

If rules changes don't seem to work:

1. **Clear cache**: Clear your browser cache and reload
2. **Redeploy rules**: Deploy the rules again using Firebase CLI
3. **Check syntax**: Ensure there are no syntax errors in the rules file
4. **Wait for propagation**: Rules can take a few seconds to propagate

## Integration with Admin Website

The security rules are designed to work seamlessly with both the Flutter mobile app and the admin website:

- Both platforms share the same Firebase project
- Both platforms use the same authentication system
- Admin users authenticated on either platform can manage products
- Changes made on one platform are immediately visible on the other (real-time sync)

## Audit and Monitoring

To monitor security rule usage:

1. Go to Firebase Console → Firestore Database → Usage tab
2. Review read/write operations
3. Check for any denied requests
4. Set up alerts for suspicious activity

## Future Enhancements

Consider implementing:

1. **Rate limiting**: Prevent abuse by limiting operations per user
2. **Field-level security**: More granular control over specific fields
3. **Audit logging**: Track all admin operations for compliance
4. **Role-based access**: Support for multiple admin roles (super admin, editor, viewer)
5. **IP whitelisting**: Restrict admin access to specific IP addresses

## Related Files

- `firestore.rules` - Security rules definition
- `lib/models/user.dart` - User model with userType field
- `lib/services/product_service.dart` - Product service that interacts with Firestore
- `lib/screens/admin/product_management_screen.dart` - Admin product management UI

## Support

For issues or questions about security rules:
1. Check Firebase documentation: https://firebase.google.com/docs/firestore/security/get-started
2. Review Firebase security rules reference: https://firebase.google.com/docs/rules
3. Contact the development team
