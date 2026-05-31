# Admin Account Credentials

This document contains the credentials for different admin account types in the Salamtak application.

## Account Types Overview

The admin system has been divided into two specialized roles:

1. **Moderator** - Responsible for managing user reports only
2. **Product Manager** - Responsible for managing products and orders

---

## 1. Moderator Account (Reports Management Only)

**Purpose:** Manage and respond to user-submitted reports

**Credentials:**
- **Work ID:** `221001001`
- **Password:** `mod2024`

**Access:**
- ✅ View all user reports
- ✅ Filter reports by status (All, Pending, In Progress, Resolved)
- ✅ Update report status
- ✅ View report details with images and location
- ✅ Profile settings and language toggle
- ❌ Cannot access products management
- ❌ Cannot access orders management

**Navigation:**
- Reports Management (Home)
- Profile

---

## 2. Product Manager Account (Products & Orders Management)

**Purpose:** Manage product catalog and customer orders

**Credentials:**
- **Work ID:** `221002002`
- **Password:** `prod2024`

**Access:**
- ✅ View and manage all orders
- ✅ Update order status (Pending, Processing, Completed, Cancelled)
- ✅ View order details and customer information
- ✅ Add, edit, and delete products
- ✅ Search and filter products by category
- ✅ Manage product inventory and pricing
- ✅ Upload product images
- ✅ Profile settings and language toggle
- ❌ Cannot access reports management

**Navigation:**
- Orders Management
- Product Management
- Profile

---

## 3. Legacy Admin Account (Full Access - Backward Compatibility)

**Purpose:** Maintain backward compatibility with existing admin account

**Credentials:**
- **Work ID:** `221007689`
- **Password:** `631663`

**Access:**
- ✅ Full access to all product manager features
- ✅ Orders management
- ✅ Products management
- ✅ Profile settings

**Note:** This account has been mapped to Product Manager role for full access.

---

## Regular User Account (For Testing)

**Credentials:**
- **National ID:** `11111111111111` (14 digits)
- **Password:** `user123456`

**Access:**
- User dashboard
- Report problems
- Browse and purchase products
- View order history
- Manage profile

---

## Login Instructions

### For Admin Accounts:
1. Open the Salamtak app
2. On the login screen, select **"Admin"** radio button
3. Enter the **Work ID** (9 digits)
4. Enter the **Password**
5. Click **Sign In**

### For User Accounts:
1. Open the Salamtak app
2. On the login screen, select **"User"** radio button
3. Enter the **National ID** (14 digits)
4. Enter the **Password**
5. Click **Sign In**

---

## Security Notes

⚠️ **Important:**
- These are hardcoded credentials for development/testing purposes
- In production, implement proper authentication with Firebase Auth
- Store credentials securely and never commit them to public repositories
- Implement role-based access control in Firestore security rules
- Add audit logging for admin actions
- Consider implementing 2FA for admin accounts

---

## Role Comparison Table

| Feature | Moderator | Product Manager | Regular User |
|---------|-----------|-----------------|--------------|
| View Reports | ✅ | ❌ | Own reports only |
| Manage Reports | ✅ | ❌ | ❌ |
| View Orders | ❌ | ✅ | Own orders only |
| Manage Orders | ❌ | ✅ | ❌ |
| View Products | ❌ | ✅ | ✅ |
| Manage Products | ❌ | ✅ | ❌ |
| Submit Reports | ❌ | ❌ | ✅ |
| Purchase Products | ❌ | ❌ | ✅ |
| Language Toggle | ✅ | ✅ | ✅ |

---

## Implementation Details

### User Model Changes
The `User` model now includes helper methods:
- `isUser` - Returns true for regular users
- `isModerator` - Returns true for moderators
- `isProductManager` - Returns true for product managers
- `isAdmin` - Returns true for any admin type (moderator or product manager)

### Navigation Adaptation
The admin navigation automatically adapts based on the user role:
- **Moderators** see: Reports, Profile
- **Product Managers** see: Orders, Products, Profile

### Visual Consistency
- All admin screens maintain the same design language
- Color scheme: Dark blue primary (#0f1d3f), gold accent (#FBBF24)
- Consistent card-based layouts with rounded corners
- Status badges with color coding
- Responsive design for mobile and web

---

## Testing Checklist

### Moderator Account Testing:
- [ ] Login with moderator credentials
- [ ] Verify only Reports and Profile tabs are visible
- [ ] View all reports with filtering
- [ ] Update report status
- [ ] View report details with images
- [ ] Toggle language (English/Arabic)
- [ ] Sign out successfully

### Product Manager Account Testing:
- [ ] Login with product manager credentials
- [ ] Verify only Orders, Products, and Profile tabs are visible
- [ ] View and filter orders
- [ ] Update order status
- [ ] Add new product with image
- [ ] Edit existing product
- [ ] Delete product
- [ ] Search and filter products
- [ ] Toggle language (English/Arabic)
- [ ] Sign out successfully

### Cross-Role Testing:
- [ ] Verify moderator cannot access products/orders
- [ ] Verify product manager cannot access reports
- [ ] Verify role badge displays correctly
- [ ] Verify profile shows correct role name
- [ ] Test language switching for both roles

---

## Future Enhancements

1. **Dynamic Role Assignment**
   - Admin interface to create/manage admin users
   - Assign roles dynamically through Firestore

2. **Granular Permissions**
   - Fine-grained permissions within each role
   - Permission groups and custom roles

3. **Audit Logging**
   - Track all admin actions
   - View audit logs in admin panel

4. **Multi-Factor Authentication**
   - Add 2FA for admin accounts
   - SMS or authenticator app support

5. **Role-Based Firestore Rules**
   - Implement security rules based on userType
   - Prevent unauthorized access at database level

---

**Last Updated:** May 31, 2026
**Version:** 2.0.0
