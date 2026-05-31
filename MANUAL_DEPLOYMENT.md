# 🚀 Manual Deployment Guide

## Quick Start - 3 Options

### Option 1: Use the Batch Script (Easiest)

1. **Double-click** `deploy.bat` in the project folder
2. Follow the prompts
3. Done!

### Option 2: Command Prompt (Manual)

Open **Command Prompt** and run these commands:

```cmd
cd "c:\New folder\htdocs\swebsite\salamtak - Copy"

firebase login

firebase use --add
(Select: salmtak-6fffe from the list)

firebase deploy --only firestore:rules,storage,firestore:indexes
```

### Option 3: Firebase Console (Web Interface)

If Firebase CLI has issues, deploy rules manually:

#### Deploy Firestore Rules

1. Go to: https://console.firebase.google.com/project/salmtak-6fffe/firestore/rules
2. Click **"Rules"** tab
3. Copy content from `firestore.rules` file
4. Paste into the editor
5. Click **"Publish"**

#### Deploy Storage Rules

1. Go to: https://console.firebase.google.com/project/salmtak-6fffe/storage/rules
2. Click **"Rules"** tab
3. Copy content from `storage.rules` file
4. Paste into the editor
5. Click **"Publish"**

#### Deploy Firestore Indexes

1. Go to: https://console.firebase.google.com/project/salmtak-6fffe/firestore/indexes
2. Click **"Indexes"** tab
3. Click **"Add Index"**
4. Configure:
   - Collection: `products`
   - Field 1: `category` (Ascending)
   - Field 2: `createdAt` (Descending)
5. Click **"Create"**

---

## Verify Admin User

**IMPORTANT:** Your admin user must have `userType: 'admin'` in Firestore.

1. Go to: https://console.firebase.google.com/project/salmtak-6fffe/firestore/data
2. Open `users` collection
3. Find your user document (mr121150@gmail.com)
4. Check if field exists: `userType: "admin"`
5. If missing, click **"Add field"**:
   - Field: `userType`
   - Type: `string`
   - Value: `admin`
6. Click **"Update"**

---

## Test the Feature

After deployment:

```cmd
cd "c:\New folder\htdocs\swebsite\salamtak - Copy"
flutter run
```

1. Login as admin (mr121150@gmail.com)
2. Go to Admin Home
3. Click **"Manage Products"** button
4. Test: Add, Edit, Delete products
5. Verify changes sync to website

---

## Troubleshooting

### Firebase CLI Authentication Issues

**Problem:** "Invalid authentication credentials" or "Invalid refresh token"

**Solution:**
1. Logout: `firebase logout`
2. Login again: `firebase login`
3. Or use Option 3 (Firebase Console) above

### Cannot Find Project

**Problem:** "Invalid project selection"

**Solution:**
1. Use: `firebase use --add`
2. Select `salmtak-6fffe` from the list
3. Or deploy with: `firebase deploy --project salmtak-6fffe --only firestore:rules,storage,firestore:indexes`

### Permission Denied in App

**Problem:** Cannot add/edit/delete products

**Solution:**
- Verify `userType: 'admin'` in Firestore (see "Verify Admin User" above)

### Images Not Uploading

**Problem:** Image upload fails

**Solution:**
- Verify Storage rules are deployed
- Check Firebase Console → Storage → Rules

---

## Files to Deploy

### firestore.rules
Location: `c:\New folder\htdocs\swebsite\salamtak - Copy\firestore.rules`

This file contains:
- Admin-only write access to products collection
- Read access for all authenticated users
- Field validation for product data

### storage.rules
Location: `c:\New folder\htdocs\swebsite\salamtak - Copy\storage.rules`

This file contains:
- Admin-only upload to products folder
- Public read access for product images
- Image format and size validation

### firestore.indexes.json
Location: `c:\New folder\htdocs\swebsite\salamtak - Copy\firestore.indexes.json`

This file contains:
- Index for category + createdAt queries
- Optimizes product filtering

---

## Verification Checklist

After deployment, verify:

- [ ] Firestore rules deployed
- [ ] Storage rules deployed
- [ ] Firestore indexes created
- [ ] Admin user has `userType: 'admin'`
- [ ] App compiles: `flutter run`
- [ ] Can login as admin
- [ ] "Manage Products" button appears
- [ ] Can add product with image
- [ ] Can edit product
- [ ] Can delete product
- [ ] Search works
- [ ] Filter works
- [ ] Changes sync to website

---

## Quick Reference

**Project ID:** salmtak-6fffe
**Admin Email:** mr121150@gmail.com

**Firebase Console:**
- Overview: https://console.firebase.google.com/project/salmtak-6fffe
- Firestore: https://console.firebase.google.com/project/salmtak-6fffe/firestore
- Storage: https://console.firebase.google.com/project/salmtak-6fffe/storage
- Rules: https://console.firebase.google.com/project/salmtak-6fffe/firestore/rules

**Local Files:**
- Rules: `firestore.rules`, `storage.rules`
- Indexes: `firestore.indexes.json`
- Deployment script: `deploy.bat`

---

## Need Help?

1. **Check Firebase Console** - Verify rules are deployed
2. **Check Firestore** - Verify admin user has `userType: 'admin'`
3. **Check logs** - Look at `firebase-debug.log` for errors
4. **Re-authenticate** - Run `firebase logout` then `firebase login`
5. **Use web interface** - Deploy rules via Firebase Console (Option 3)

---

## Summary

**Easiest Method:** Double-click `deploy.bat`

**Manual Method:** Run commands in Command Prompt

**Web Method:** Copy-paste rules in Firebase Console

**All methods achieve the same result!**

Choose the method that works best for you. 🚀
