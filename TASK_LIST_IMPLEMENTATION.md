# Task List Implementation Plan

## Website Tasks (1-8)

### 1. ✅ Remove Home/Product logos from navbar
- **Files:** `includes/public_nav.php`, `admin/includes/admin_navbar.php`, `user/includes/nav.php`
- **Action:** Remove SVG icons from Home and Products links

### 2. ✅ Change "Other" color from purple to light grey
- **File:** `user/services.php`
- **Action:** Change purple color to light grey (#9CA3AF or similar)

### 3. ✅ Remove "Certified" text from products
- **Files:** Product pages, product cards
- **Action:** Remove all certified badges/text

### 4. ✅ Make signup page match login page design
- **File:** `signup.php`
- **Action:** Apply same styling as login.php

### 5. ✅ Change in_progress icon from dollar to loading
- **File:** `admin/dashboard.php`
- **Action:** Replace dollar icon with loading/spinner icon

### 6. ✅ Remove Products button from admin dashboard
- **File:** `admin/dashboard.php`
- **Action:** Remove products navigation button

### 7. ✅ Create admin account page (view-only)
- **File:** `admin/account.php`
- **Action:** Create read-only profile page with language switcher and logout

### 8. ⏳ Full Arabic translation for website
- **File:** `translations.php`
- **Action:** Add complete Arabic translations for all pages

## App Tasks (9-17)

### 9. ✅ Create signup page in app
- **File:** `lib/screens/signup_screen.dart`
- **Action:** Create user registration screen

### 10. ✅ Fix reports not showing in app
- **Already addressed in previous update**
- **Action:** Verify StreamBuilder implementation

### 11. ✅ Change product price color to dark blue
- **Files:** Product screens
- **Action:** Change from gold to dark blue

### 12. ✅ Change problem type navbar colors
- **Files:** Problem report screens
- **Action:** Pothole → dark blue, Other → matching color

### 13. ✅ Change in_progress color to light grey
- **Files:** Status displays
- **Action:** Change purple to light grey

### 14. ⏳ Fix Arabic language in app
- **Files:** Localization files
- **Action:** Complete Arabic translations

### 15. ✅ Fix order status icons
- **File:** Order management screen
- **Action:** Update status icons

### 16. ✅ Fix report images not showing
- **Files:** Admin screens
- **Action:** Fix image path handling

### 17. ✅ Fix database sync between app and website
- **Already addressed in previous update**
- **Action:** Verify real-time sync

---

## Implementation Priority

### Phase 1: Critical Fixes (High Priority)
1. Database sync (Task 17) - Already done
2. Reports not showing (Task 10) - Already done
3. Report images (Task 16)
4. Arabic translations (Tasks 8, 14)

### Phase 2: Design Updates (Medium Priority)
1. Navbar logo removal (Task 1)
2. Color changes (Tasks 2, 11, 12, 13)
3. Icon changes (Task 5, 15)
4. Signup pages (Tasks 4, 9)

### Phase 3: Feature Updates (Lower Priority)
1. Remove certified text (Task 3)
2. Remove products button (Task 6)
3. Admin account page (Task 7)

---

## Status Legend
- ✅ Complete
- ⏳ In Progress
- ❌ Not Started
