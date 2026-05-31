# Salamtak App Architecture

## System Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                        SALAMTAK SYSTEM                          │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌──────────────────┐              ┌──────────────────┐        │
│  │   Flutter App    │◄────────────►│   PHP Website    │        │
│  │   (Mobile/Web)   │              │   (Web Portal)   │        │
│  └────────┬─────────┘              └────────┬─────────┘        │
│           │                                  │                  │
│           │         ┌────────────────────────┘                  │
│           │         │                                           │
│           ▼         ▼                                           │
│  ┌─────────────────────────────────────────────────┐           │
│  │            Firebase Backend                     │           │
│  ├─────────────────────────────────────────────────┤           │
│  │  • Authentication (Email/Password)              │           │
│  │  • Firestore Database (Reports, Reviews, etc.)  │           │
│  │  • Storage (Images, Documents)                  │           │
│  └─────────────────────────────────────────────────┘           │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## Flutter App Architecture

### Layer Structure

```
┌─────────────────────────────────────────────────────────────────┐
│                         PRESENTATION LAYER                      │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐         │
│  │   Screens    │  │   Widgets    │  │   Providers  │         │
│  ├──────────────┤  ├──────────────┤  ├──────────────┤         │
│  │ • Dashboard  │  │ • Location   │  │ • Cart       │         │
│  │ • Reports    │  │   Picker     │  │ • Language   │         │
│  │ • Products   │  │ • Map View   │  │ • Auth       │         │
│  │ • History    │  │ • Cards      │  └──────────────┘         │
│  └──────────────┘  └──────────────┘                            │
│                                                                 │
├─────────────────────────────────────────────────────────────────┤
│                         BUSINESS LOGIC LAYER                    │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌──────────────────────────────────────────────────┐          │
│  │              Services                            │          │
│  ├──────────────────────────────────────────────────┤          │
│  │ • DatabaseService (CRUD operations)              │          │
│  │ • ImageClassifier (ML classification)            │          │
│  │ • AuthService (Login/Signup)                     │          │
│  └──────────────────────────────────────────────────┘          │
│                                                                 │
├─────────────────────────────────────────────────────────────────┤
│                         DATA LAYER                              │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐         │
│  │    Models    │  │   Firebase   │  │    Local     │         │
│  ├──────────────┤  ├──────────────┤  ├──────────────┤         │
│  │ • Report     │  │ • Firestore  │  │ • SharedPrefs│         │
│  │ • Review     │  │ • Storage    │  │ • Cache      │         │
│  │ • Product    │  │ • Auth       │  └──────────────┘         │
│  │ • User       │  └──────────────┘                            │
│  └──────────────┘                                              │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## Data Flow Diagrams

### 1. Report Submission Flow

```
┌──────────┐
│   User   │
└────┬─────┘
     │ 1. Select problem type
     ▼
┌─────────────────────┐
│ Report Screen       │
├─────────────────────┤
│ • Upload photo      │◄──┐
│ • Select location   │   │ 2. Pick image
│ • Enter description │   │
│ • Choose severity   │   │
└────┬────────────────┘   │
     │ 3. Submit          │
     ▼                    │
┌─────────────────────┐   │
│ DatabaseService     │   │
├─────────────────────┤   │
│ uploadReportImage() │───┘ 4. Upload to Storage
│ createReport()      │
└────┬────────────────┘
     │ 5. Save to Firestore
     ▼
┌─────────────────────┐
│ Firebase Storage    │
│ + Firestore DB      │
└────┬────────────────┘
     │ 6. Sync
     ▼
┌─────────────────────┐
│ Website + App       │
│ (Real-time update)  │
└─────────────────────┘
```

### 2. Product Review Flow

```
┌──────────┐
│   User   │
└────┬─────┘
     │ 1. Browse products
     ▼
┌─────────────────────┐
│ Products Screen     │
└────┬────────────────┘
     │ 2. Tap product
     ▼
┌─────────────────────┐
│ Product Details     │
├─────────────────────┤
│ • View info         │
│ • Read reviews      │
│ • Write review      │◄──┐
└────┬────────────────┘   │
     │ 3. Submit review   │
     ▼                    │
┌─────────────────────┐   │
│ DatabaseService     │   │
├─────────────────────┤   │
│ createReview()      │───┘ 4. Save to Firestore
└────┬────────────────┘
     │ 5. Real-time update
     ▼
┌─────────────────────┐
│ Reviews Stream      │
│ (Live updates)      │
└─────────────────────┘
```

### 3. Location Selection Flow

```
┌──────────┐
│   User   │
└────┬─────┘
     │ 1. Tap location field
     ▼
┌─────────────────────────────┐
│ Leaflet Location Picker     │
├─────────────────────────────┤
│                             │
│  ┌─────────────────────┐   │
│  │  OpenStreetMap      │   │
│  │  (Leaflet)          │   │
│  └─────────────────────┘   │
│                             │
│  Options:                   │
│  ┌─────────────────────┐   │
│  │ • Tap on map        │◄──┼─── 2a. Manual tap
│  │ • Egyptian cities   │◄──┼─── 2b. Quick select
│  │ • Enter coordinates │◄──┼─── 2c. Manual entry
│  └─────────────────────┘   │
│                             │
└────┬────────────────────────┘
     │ 3. Confirm location
     ▼
┌─────────────────────┐
│ Report Screen       │
│ (Location set)      │
└─────────────────────┘
```

---

## Database Schema

### Firestore Collections

#### 1. **reports** Collection
```javascript
{
  "reportId": {
    uid: "string",              // User ID
    nationalId: "string",       // National ID
    name: "string",             // User name
    type: "string",             // Problem type
    description: "string",      // Problem description
    imagePath: "string",        // Firebase Storage URL
    status: "string",           // pending | in_progress | resolved
    severity: "string",         // Low | Medium | High | Critical
    latitude: number,           // Location latitude
    longitude: number,          // Location longitude
    location: "string",         // Address/label
    createdAt: "string",        // ISO 8601 timestamp
    updatedAt: "string"         // ISO 8601 timestamp
  }
}
```

#### 2. **reviews** Collection
```javascript
{
  "reviewId": {
    productId: "string",        // Product reference
    userId: "string",           // User ID
    userName: "string",         // Display name
    rating: number,             // 1-5 stars
    comment: "string",          // Review text
    createdAt: "string"         // ISO 8601 timestamp
  }
}
```

#### 3. **products** Collection
```javascript
{
  "productId": {
    name: "string",             // Product name
    description: "string",      // Product description
    price: number,              // Price in EGP
    stock: number,              // Available quantity
    category: "string",         // Product category
    imageUrl: "string"          // Product image URL
  }
}
```

#### 4. **users** Collection
```javascript
{
  "userId": {
    nationalId: "string",       // National ID
    name: "string",             // Full name
    email: "string",            // Email address
    phone: "string",            // Phone number
    address: "string",          // Physical address
    userType: "string",         // user | admin
    createdAt: "string"         // ISO 8601 timestamp
  }
}
```

#### 5. **carts** Collection
```javascript
{
  "userId": {
    userId: "string",           // User reference
    items: {                    // Map of productId: quantity
      "productId1": number,
      "productId2": number
    },
    createdAt: "string",
    updatedAt: "string"
  }
}
```

---

## Firebase Storage Structure

```
salamtak-storage/
├── reports/
│   ├── 1715520000000_image1.jpg
│   ├── 1715520001000_image2.png
│   └── ...
├── products/
│   ├── vest.jpeg
│   ├── helmet.jpeg
│   └── ...
└── users/
    └── avatars/
        └── ...
```

---

## State Management

### Provider Pattern

```
┌─────────────────────────────────────────────────────────────────┐
│                         App Root                                │
│                    (MultiProvider)                              │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌──────────────────┐  ┌──────────────────┐                    │
│  │  CartProvider    │  │ LanguageProvider │                    │
│  ├──────────────────┤  ├──────────────────┤                    │
│  │ • items          │  │ • locale         │                    │
│  │ • totalPrice     │  │ • isArabic       │                    │
│  │ • addItem()      │  │ • setLanguage()  │                    │
│  │ • removeItem()   │  └──────────────────┘                    │
│  │ • clearCart()    │                                           │
│  └──────────────────┘                                           │
│                                                                 │
│  ┌──────────────────────────────────────────────────┐          │
│  │              Consumer Widgets                    │          │
│  ├──────────────────────────────────────────────────┤          │
│  │ • CartScreen (listens to CartProvider)           │          │
│  │ • ProductsScreen (listens to CartProvider)       │          │
│  │ • All screens (listen to LanguageProvider)       │          │
│  └──────────────────────────────────────────────────┘          │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## Navigation Structure

```
┌─────────────────────────────────────────────────────────────────┐
│                         Main App                                │
└────┬────────────────────────────────────────────────────────────┘
     │
     ├─► Login Screen
     │   └─► Signup Screen
     │
     ├─► User Home (Bottom Navigation)
     │   ├─► Dashboard Tab
     │   │   └─► Services Screen
     │   │       └─► Problem Report Screen
     │   │           └─► Leaflet Location Picker
     │   │
     │   ├─► History Tab
     │   │   └─► Report Details
     │   │
     │   ├─► Products Tab
     │   │   ├─► Product Details Screen
     │   │   │   └─► Review Form
     │   │   └─► Cart Screen
     │   │       └─► Checkout Screen
     │   │
     │   └─► Profile Tab
     │       └─► Settings
     │
     └─► Admin Home (Bottom Navigation)
         ├─► Dashboard Tab
         ├─► Reports Management Tab
         ├─► Products Management Tab
         └─► Inventory Tab
```

---

## Security Architecture

### Firebase Security Rules

#### Firestore Rules
```javascript
rules_version = '2';
service cloud.firestore {
  match /databases/{database}/documents {
    
    // Users can read/write their own data
    match /users/{userId} {
      allow read: if request.auth != null;
      allow write: if request.auth.uid == userId;
    }
    
    // Reports: authenticated users can create, read own
    match /reports/{reportId} {
      allow read: if request.auth != null;
      allow create: if request.auth != null;
      allow update: if request.auth != null && 
                       (request.auth.uid == resource.data.uid || 
                        get(/databases/$(database)/documents/users/$(request.auth.uid)).data.userType == 'admin');
    }
    
    // Reviews: authenticated users can create, all can read
    match /reviews/{reviewId} {
      allow read: if true;
      allow create: if request.auth != null;
      allow update, delete: if request.auth.uid == resource.data.userId;
    }
    
    // Products: all can read, admin can write
    match /products/{productId} {
      allow read: if true;
      allow write: if request.auth != null && 
                      get(/databases/$(database)/documents/users/$(request.auth.uid)).data.userType == 'admin';
    }
  }
}
```

#### Storage Rules
```javascript
rules_version = '2';
service firebase.storage {
  match /b/{bucket}/o {
    
    // Report images: authenticated users can upload
    match /reports/{imageId} {
      allow read: if true;
      allow write: if request.auth != null &&
                      request.resource.size < 5 * 1024 * 1024 && // 5MB max
                      request.resource.contentType.matches('image/.*');
    }
  }
}
```

---

## Performance Optimizations

### 1. Image Handling
- Compress images before upload
- Use cached network images
- Lazy load images in lists
- Thumbnail generation for previews

### 2. Database Queries
- Index frequently queried fields
- Use pagination for large lists
- Cache user data locally
- Stream only necessary data

### 3. State Management
- Use Provider for efficient rebuilds
- Implement selective listening
- Cache computed values
- Dispose controllers properly

---

## Testing Strategy

### Unit Tests
- Model serialization/deserialization
- Service methods
- Utility functions

### Widget Tests
- Screen rendering
- User interactions
- Form validation

### Integration Tests
- End-to-end flows
- Firebase integration
- Navigation flows

---

**Last Updated:** May 12, 2026
**Version:** 1.0.0
