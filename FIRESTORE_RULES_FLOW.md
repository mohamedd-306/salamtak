# Firestore Security Rules Flow Diagram

## Products Collection Access Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                         User Request                             │
│                  (Read/Write Product Data)                       │
└────────────────────────────┬────────────────────────────────────┘
                             │
                             ▼
                  ┌──────────────────────┐
                  │  Is User Authenticated? │
                  │  (request.auth != null) │
                  └──────────┬──────────────┘
                             │
                ┌────────────┴────────────┐
                │                         │
               NO                        YES
                │                         │
                ▼                         ▼
         ┌─────────────┐         ┌──────────────┐
         │   DENIED    │         │ What Operation?│
         │ (401 Error) │         └──────┬─────────┘
         └─────────────┘                │
                                        │
                        ┌───────────────┼───────────────┐
                        │               │               │
                       READ           CREATE         UPDATE/DELETE
                        │               │               │
                        ▼               ▼               ▼
                 ┌─────────────┐  ┌──────────────┐  ┌──────────────┐
                 │   ALLOWED   │  │  Is Admin?   │  │  Is Admin?   │
                 │  (200 OK)   │  │ (Check users │  │ (Check users │
                 └─────────────┘  │  collection) │  │  collection) │
                                  └──────┬───────┘  └──────┬───────┘
                                         │                  │
                                    ┌────┴────┐        ┌────┴────┐
                                    │         │        │         │
                                   YES       NO       YES       NO
                                    │         │        │         │
                                    ▼         ▼        ▼         ▼
                            ┌──────────┐  ┌────────┐ ┌────────┐ ┌────────┐
                            │ Validate │  │ DENIED │ │ Validate│ │ DENIED │
                            │   Data   │  │(403)   │ │  Data  │ │(403)   │
                            └────┬─────┘  └────────┘ └────┬───┘ └────────┘
                                 │                         │
                            ┌────┴────┐              ┌────┴────┐
                            │         │              │         │
                          VALID    INVALID        VALID    INVALID
                            │         │              │         │
                            ▼         ▼              ▼         ▼
                      ┌─────────┐ ┌────────┐  ┌─────────┐ ┌────────┐
                      │ ALLOWED │ │ DENIED │  │ ALLOWED │ │ DENIED │
                      │ (200)   │ │ (400)  │  │ (200)   │ │ (400)  │
                      └─────────┘ └────────┘  └─────────┘ └────────┘
```

## Admin Verification Process

```
┌─────────────────────────────────────────────────────────────────┐
│                    Admin Verification Flow                       │
└────────────────────────────┬────────────────────────────────────┘
                             │
                             ▼
                  ┌──────────────────────┐
                  │  Get User Auth UID   │
                  │  (request.auth.uid)  │
                  └──────────┬───────────┘
                             │
                             ▼
                  ┌──────────────────────┐
                  │  Read User Document  │
                  │  from users/{uid}    │
                  └──────────┬───────────┘
                             │
                             ▼
                  ┌──────────────────────┐
                  │  Check userType      │
                  │  field in document   │
                  └──────────┬───────────┘
                             │
                ┌────────────┴────────────┐
                │                         │
        userType == 'admin'       userType != 'admin'
                │                         │
                ▼                         ▼
         ┌─────────────┐           ┌─────────────┐
         │  IS ADMIN   │           │ NOT ADMIN   │
         │  ✅ Allow   │           │  ❌ Deny    │
         └─────────────┘           └─────────────┘
```

## Data Validation Flow (Create/Update)

```
┌─────────────────────────────────────────────────────────────────┐
│                    Product Data Validation                       │
└────────────────────────────┬────────────────────────────────────┘
                             │
                             ▼
              ┌──────────────────────────────┐
              │  Check Required Fields       │
              │  [name, description, price,  │
              │   stock, category, image,    │
              │   createdAt, updatedAt]      │
              └──────────────┬───────────────┘
                             │
                        ┌────┴────┐
                        │         │
                      PASS      FAIL
                        │         │
                        ▼         ▼
              ┌──────────────┐  ┌────────┐
              │ Validate Name│  │ DENIED │
              │ (1-100 chars)│  │ (400)  │
              └──────┬───────┘  └────────┘
                     │
                ┌────┴────┐
                │         │
              PASS      FAIL
                │         │
                ▼         ▼
      ┌──────────────┐  ┌────────┐
      │ Validate Desc│  │ DENIED │
      │ (1-500 chars)│  │ (400)  │
      └──────┬───────┘  └────────┘
             │
        ┌────┴────┐
        │         │
      PASS      FAIL
        │         │
        ▼         ▼
┌──────────────┐  ┌────────┐
│Validate Price│  │ DENIED │
│ (> 0)        │  │ (400)  │
└──────┬───────┘  └────────┘
       │
  ┌────┴────┐
  │         │
PASS      FAIL
  │         │
  ▼         ▼
┌──────────────┐  ┌────────┐
│Validate Stock│  │ DENIED │
│ (>= 0, int)  │  │ (400)  │
└──────┬───────┘  └────────┘
       │
  ┌────┴────┐
  │         │
PASS      FAIL
  │         │
  ▼         ▼
┌──────────────┐  ┌────────┐
│Validate Other│  │ DENIED │
│ Fields       │  │ (400)  │
└──────┬───────┘  └────────┘
       │
  ┌────┴────┐
  │         │
PASS      FAIL
  │         │
  ▼         ▼
┌──────────────┐  ┌────────┐
│ For Update:  │  │ DENIED │
│ Check        │  │ (400)  │
│ createdAt    │  └────────┘
│ unchanged    │
└──────┬───────┘
       │
  ┌────┴────┐
  │         │
PASS      FAIL
  │         │
  ▼         ▼
┌──────────┐  ┌────────┐
│ ALLOWED  │  │ DENIED │
│ (200)    │  │ (400)  │
└──────────┘  └────────┘
```

## User Types and Permissions Matrix

```
┌─────────────────────────────────────────────────────────────────┐
│                    Permission Matrix                             │
├──────────────────┬──────────────┬──────────────┬────────────────┤
│   User Type      │  Read        │  Create      │  Update/Delete │
├──────────────────┼──────────────┼──────────────┼────────────────┤
│ Unauthenticated  │  ❌ DENIED   │  ❌ DENIED   │  ❌ DENIED     │
├──────────────────┼──────────────┼──────────────┼────────────────┤
│ Regular User     │  ✅ ALLOWED  │  ❌ DENIED   │  ❌ DENIED     │
│ (userType:user)  │              │              │                │
├──────────────────┼──────────────┼──────────────┼────────────────┤
│ Admin User       │  ✅ ALLOWED  │  ✅ ALLOWED  │  ✅ ALLOWED    │
│ (userType:admin) │              │  (validated) │  (validated)   │
└──────────────────┴──────────────┴──────────────┴────────────────┘
```

## Real-World Scenarios

### Scenario 1: Regular User Views Products
```
User (userType: 'user') → Request: Read Products
                        ↓
                  Authenticated? ✅ YES
                        ↓
                  Operation: READ
                        ↓
                  Result: ✅ ALLOWED
                        ↓
                  Returns: Product List
```

### Scenario 2: Regular User Tries to Create Product
```
User (userType: 'user') → Request: Create Product
                        ↓
                  Authenticated? ✅ YES
                        ↓
                  Operation: CREATE
                        ↓
                  Is Admin? ❌ NO
                        ↓
                  Result: ❌ DENIED (403)
                        ↓
                  Error: "Permission denied"
```

### Scenario 3: Admin Creates Product
```
Admin (userType: 'admin') → Request: Create Product
                          ↓
                    Authenticated? ✅ YES
                          ↓
                    Operation: CREATE
                          ↓
                    Is Admin? ✅ YES
                          ↓
                    Validate Data? ✅ PASS
                          ↓
                    Result: ✅ ALLOWED (200)
                          ↓
                    Product Created Successfully
```

### Scenario 4: Admin Updates Product with Invalid Data
```
Admin (userType: 'admin') → Request: Update Product
                          ↓
                    Authenticated? ✅ YES
                          ↓
                    Operation: UPDATE
                          ↓
                    Is Admin? ✅ YES
                          ↓
                    Validate Data? ❌ FAIL
                    (e.g., price = -10)
                          ↓
                    Result: ❌ DENIED (400)
                          ↓
                    Error: "Invalid data"
```

### Scenario 5: Unauthenticated Access
```
Anonymous User → Request: Read/Write Products
               ↓
         Authenticated? ❌ NO
               ↓
         Result: ❌ DENIED (401)
               ↓
         Error: "Authentication required"
```

## Security Layers

```
┌─────────────────────────────────────────────────────────────────┐
│                      Security Layers                             │
│                                                                  │
│  Layer 1: Authentication                                         │
│  ├─ Firebase Authentication                                      │
│  └─ Valid Auth Token Required                                    │
│                                                                  │
│  Layer 2: Authorization                                          │
│  ├─ Check userType in Firestore                                 │
│  └─ Verify Admin Status                                          │
│                                                                  │
│  Layer 3: Data Validation                                        │
│  ├─ Field Type Validation                                        │
│  ├─ Field Length Validation                                      │
│  ├─ Field Value Validation                                       │
│  └─ Timestamp Protection                                         │
│                                                                  │
│  Layer 4: Operation Control                                      │
│  ├─ Read: All Authenticated                                      │
│  ├─ Write: Admin Only                                            │
│  └─ Default: Deny All                                            │
└─────────────────────────────────────────────────────────────────┘
```

## Integration Points

```
┌─────────────────────────────────────────────────────────────────┐
│                    System Integration                            │
│                                                                  │
│  ┌──────────────────┐         ┌──────────────────┐             │
│  │  Flutter Mobile  │         │  Admin Website   │             │
│  │      App         │         │                  │             │
│  └────────┬─────────┘         └────────┬─────────┘             │
│           │                            │                        │
│           │    Firebase Auth Token     │                        │
│           └────────────┬───────────────┘                        │
│                        │                                        │
│                        ▼                                        │
│           ┌────────────────────────┐                            │
│           │  Firebase Firestore    │                            │
│           │  Security Rules Engine │                            │
│           └────────────┬───────────┘                            │
│                        │                                        │
│           ┌────────────┴───────────┐                            │
│           │                        │                            │
│           ▼                        ▼                            │
│  ┌────────────────┐      ┌────────────────┐                    │
│  │ Users          │      │ Products        │                    │
│  │ Collection     │      │ Collection      │                    │
│  │ (userType)     │      │ (data)          │                    │
│  └────────────────┘      └────────────────┘                    │
└─────────────────────────────────────────────────────────────────┘
```

## Error Codes Reference

```
┌─────────────────────────────────────────────────────────────────┐
│                      Error Codes                                 │
├──────────┬──────────────────────────────────────────────────────┤
│  Code    │  Description                                          │
├──────────┼──────────────────────────────────────────────────────┤
│  200     │  Success - Operation allowed and completed            │
├──────────┼──────────────────────────────────────────────────────┤
│  400     │  Bad Request - Data validation failed                 │
│          │  (e.g., invalid price, name too long)                 │
├──────────┼──────────────────────────────────────────────────────┤
│  401     │  Unauthorized - User not authenticated                │
│          │  (no valid Firebase Auth token)                       │
├──────────┼──────────────────────────────────────────────────────┤
│  403     │  Forbidden - User not authorized                      │
│          │  (authenticated but not admin)                        │
├──────────┼──────────────────────────────────────────────────────┤
│  404     │  Not Found - Document doesn't exist                   │
│          │  (e.g., trying to update non-existent product)        │
└──────────┴──────────────────────────────────────────────────────┘
```

---

## Quick Reference

### ✅ Allowed Operations
- Regular users: Read products
- Admin users: Read, Create, Update, Delete products

### ❌ Denied Operations
- Unauthenticated: All operations
- Regular users: Create, Update, Delete products
- All users: Operations with invalid data

### 🔒 Security Features
- Server-side enforcement
- Cannot be bypassed by client
- Real-time admin verification
- Comprehensive data validation
- Timestamp protection
- Default deny policy

---

This visual guide helps understand how the Firestore security rules work in practice!
