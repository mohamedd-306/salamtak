# Task 1.1 - Bug Condition Exploration Test Instructions

## Overview

This document provides instructions for running the bug condition exploration test for Task 1.1.

## Test File Created

**Location:** `test/bug_condition_exploration_test.dart`

## What This Test Does

The test creates a product named "cones" with the image path `assets/products/cones.jpeg` stored in the `product.image` field, then verifies that the ProductCard widget displays the image from the database field rather than using hardcoded name-matching logic.

## Expected Behavior on UNFIXED Code

**The test is EXPECTED TO FAIL.** This failure confirms the bug exists.

### Why It Should Fail

1. The current `ProductCard` implementation uses the `_getImagePath(product.name)` method
2. This method checks if the product name contains specific keywords: "vest", "jacket", "boots", "helmet", "hard hat", or "earmuffs"
3. For "cones", none of these conditions match
4. Therefore, `_getImagePath()` returns `'assets/products/placeholder.png'`
5. The test expects `'assets/products/cones.jpeg'` (from the database)
6. **Result:** Test fails, confirming the bug

### Expected Counterexample

```
Product 'cones' with image path 'assets/products/cones.jpeg' displays placeholder icon instead of actual image
```

## How to Run the Test

### Option 1: Command Line

```bash
flutter test test/bug_condition_exploration_test.dart
```

### Option 2: VS Code

1. Open `test/bug_condition_exploration_test.dart` in VS Code
2. Click the "Run" button above the test function
3. Or use the Testing sidebar to run the test

### Option 3: Run All Tests

```bash
flutter test
```

## Expected Test Output

The test should fail with output similar to:

```
══╡ EXCEPTION CAUGHT BY FLUTTER TEST FRAMEWORK ╞════════════════════════════════════════════════════
The following TestFailure was thrown running a test:
Expected: 'assets/products/cones.jpeg'
  Actual: 'assets/products/placeholder.png'
   Which: is different.
          Expected: assets/products/cones.jpeg
            Actual: assets/products/placeholder.png
                    ^
           Differ at offset 17

ProductCard should use product.image field from database, not hardcoded name-matching logic. 
Expected: assets/products/cones.jpeg, Got: assets/products/placeholder.png
This failure confirms the bug exists: ProductCard ignores the database image field and uses hardcoded name matching.
```

## What to Do After Running

1. **Document the failure:** Note that the test failed as expected
2. **Record the counterexample:** The actual image path used was `'assets/products/placeholder.png'` instead of `'assets/products/cones.jpeg'`
3. **Mark task 1.1 as complete:** The test has been written, run, and the failure has been documented
4. **DO NOT fix the code yet:** This is an exploration test - the fix will be implemented in Task 3.1

## Troubleshooting

### If Flutter is not found

Make sure Flutter is installed and in your PATH:

```bash
flutter --version
```

If not found, add Flutter to your PATH or use the full path to the Flutter executable.

### If the test passes unexpectedly

This would indicate that:
- The code has already been fixed, OR
- The test is not correctly checking the image source

In this case, review the test logic and the ProductCard implementation.

### If you get localization errors

The test includes localization support. If you still get errors, you may need to run:

```bash
flutter pub get
flutter gen-l10n
```

## Next Steps

After confirming the test fails as expected:

1. Proceed to Task 1.2 (if assigned)
2. Or wait for Task 3.1 where the fix will be implemented
3. After the fix, this same test should PASS, confirming the bug is resolved

## Requirements Validated

- **Requirement 1.1:** WHEN a product named "cones" is loaded from Firestore THEN the system displays a placeholder icon instead of the product image (current defect)
- **Requirement 2.1:** WHEN a product named "cones" is loaded from Firestore THEN the system SHALL display the image specified in the product's `image` field from the database (expected behavior)
- **Requirement 2.2:** WHEN a product exists in Firestore with an `image` field containing a valid path THEN the system SHALL use the database image path to load and display the product image (expected behavior)
