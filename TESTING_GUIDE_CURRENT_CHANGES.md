# Testing Guide - Current Changes

## ✅ What Has Been Changed (8 Tasks)

### Website Changes
1. **Navbar icons removed** - Home & Products
2. **"Other" problem color** - Changed to grey
3. **"Certified" text removed** - All product pages

### App Changes
1. **Product prices** - Changed to dark blue
2. **Problem navbar colors** - All dark blue, Other grey
3. **In_progress status** - Changed to light grey

---

## 🧪 Testing Instructions

### Website Testing

#### Test 1: Navbar Icons Removed
**Pages to check:**
- `home.php`
- `products_public.php`
- `user/products.php`
- `admin/dashboard.php`

**What to verify:**
- [ ] Dashboard link has NO home icon
- [ ] Products link has NO cart icon
- [ ] Links still work correctly
- [ ] Text is readable

**Expected Result:** Clean navbar with text-only links

---

#### Test 2: "Other" Problem Color (Website)
**Page:** `user/services.php`

**Steps:**
1. Login as user
2. Navigate to "Report Problem"
3. Look at the three problem cards

**What to verify:**
- [ ] Pothole card: Orange/red gradient
- [ ] Broken Pipe card: Blue gradient
- [ ] Other card: **Grey gradient** (not purple)

**Expected Result:** Other card should be grey (#9CA3AF to #6B7280)

---

#### Test 3: "Certified" Text Removed
**Pages to check:**
- `products_public.php`
- `user/products.php`
- `user/product_details.php`

**What to verify:**
- [ ] NO "100% Certified" stat in header
- [ ] NO "Certified" badge on product cards
- [ ] NO "Certified" badge on product detail images
- [ ] Products still display correctly

**Expected Result:** No certified text anywhere

---

### App Testing

#### Test 4: Product Prices - Dark Blue
**Screens to check:**
- Products screen
- Product details screen
- Shopping cart screen

**Steps:**
1. Open app
2. Login as user
3. Navigate to Products tab
4. Check product prices
5. Open a product detail
6. Add to cart and check cart

**What to verify:**
- [ ] Product grid prices: **Dark blue** (#0f1d3f)
- [ ] Product detail price: **Dark blue**
- [ ] Cart item prices: **Dark blue**
- [ ] Cart total: **Dark blue**

**Expected Result:** All prices should be dark blue, not gold

---

#### Test 5: Problem Navbar Colors
**Screens to check:**
- Services screen (problem selection)
- Problem report screens

**Steps:**
1. Navigate to "Report Problem" tab
2. Check the three problem cards
3. Tap each problem type
4. Check navbar color

**What to verify:**
- [ ] Pothole card: Construction icon, **dark blue navbar**
- [ ] Broken Pipe card: Plumbing icon, **dark blue navbar**
- [ ] Other card: **Grey gradient**, **dark blue navbar**

**Expected Result:** All navbars dark blue, Other card grey

---

#### Test 6: In_progress Status - Light Grey
**Screens to check:**
- User dashboard (My Reports)
- Admin dashboard

**Steps:**
1. Find a report with "In Progress" status
2. Check the status badge color

**What to verify:**
- [ ] In Progress badge: **Light grey** (#9CA3AF)
- [ ] NOT purple
- [ ] Still readable

**Expected Result:** In Progress status should be light grey

---

## 🔍 Verification Checklist

### Website
- [ ] All navbar links work
- [ ] No icons in navbar
- [ ] Other problem is grey
- [ ] No certified text anywhere
- [ ] Products display correctly

### App
- [ ] App compiles without errors
- [ ] All prices are dark blue
- [ ] All problem navbars are dark blue
- [ ] Other problem card is grey
- [ ] In Progress status is light grey
- [ ] Navigation works correctly

---

## 🐛 Known Issues to Check

### Reports Not Showing (Task 10)
**If reports don't show in app:**
1. Check Flutter console for: `History Screen: Loaded X reports`
2. Verify national ID matches in Firestore
3. Check status is lowercase: "pending", "in_progress", "resolved"

### Images Not Showing (Task 16)
**If report images don't show:**
1. Check image path format
2. Verify Firebase Storage rules
3. Check if path starts with "http" or "uploads/"

---

## 📊 Expected Results Summary

| Change | Expected Result | Status |
|--------|----------------|--------|
| Navbar icons | Removed | ✅ |
| Other color (web) | Grey | ✅ |
| Certified text | Removed | ✅ |
| Product prices | Dark blue | ✅ |
| Problem navbars | Dark blue | ✅ |
| In_progress color | Light grey | ✅ |

---

## 🚀 After Testing

Once you've verified these changes work correctly:

1. **If everything works:** I'll continue with remaining 9 tasks
2. **If issues found:** Let me know which specific issues to fix first

---

## 📝 Testing Notes

**Website:**
- Test in Chrome/Firefox
- Check both English and Arabic (if available)
- Test as both user and admin

**App:**
- Test on emulator or device
- Check all tabs
- Test both user and admin flows
- Check console for errors

---

## ✅ Ready to Test

All changes are:
- ✅ Implemented
- ✅ No compilation errors
- ✅ Safe (no breaking changes)
- ✅ Reversible if needed

**Please test and let me know the results!**

After testing, I'll continue with:
- Task 4: Signup page design
- Task 5: In_progress icon change
- Task 6: Remove Products button
- Task 7: Admin account page
- Task 8: Arabic translations
- Task 9: App signup screen
- Task 10: Fix reports (if needed)
- Task 14-17: Remaining fixes
