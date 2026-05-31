# Implementation Plan

- [ ] 1. Write bug condition exploration tests
  - **Property 1: Bug Condition** - Product and Report Images Load from Database
  - **CRITICAL**: These tests MUST FAIL on unfixed code - failure confirms the bug exists
  - **DO NOT attempt to fix the test or the code when it fails**
  - **NOTE**: These tests encode the expected behavior - they will validate the fix when they pass after implementation
  - **GOAL**: Surface counterexamples that demonstrate the bug exists
  - **Scoped PBT Approach**: For deterministic bugs, scope the property to the concrete failing case(s) to ensure reproducibility
  
  - [x] 1.1 Test product "cones" displays database image instead of placeholder
    - Create test product named "cones" with `image: "assets/products/cones.jpeg"` in Firestore
    - Test that ProductCard displays the image from `product.image` field (Bug Condition from design)
    - Expected behavior: Image should load from `assets/products/cones.jpeg` (Property 1 from design)
    - Run test on UNFIXED code
    - **EXPECTED OUTCOME**: Test FAILS (displays placeholder icon instead of cones image - this is correct, it proves the bug exists)
    - Document counterexample: "Product 'cones' with image path 'assets/products/cones.jpeg' displays placeholder icon instead of actual image"
    - Mark task complete when test is written, run, and failure is documented
    - _Requirements: 1.1, 2.1, 2.2_
  
  - [ ] 1.2 Test product with Firebase Storage URL displays network image instead of placeholder
    - Create test product with `image: "https://firebasestorage.googleapis.com/.../product.jpg"` in Firestore
    - Test that ProductCard displays the image from network URL (Bug Condition from design)
    - Expected behavior: Image should load from Firebase Storage URL (Property 1 from design)
    - Run test on UNFIXED code
    - **EXPECTED OUTCOME**: Test FAILS (displays placeholder icon instead of loading from network - this is correct, it proves the bug exists)
    - Document counterexample: "Product with Firebase Storage URL displays placeholder icon instead of loading from network"
    - Mark task complete when test is written, run, and failure is documented
    - _Requirements: 1.2, 2.2, 2.5_
  
  - [ ] 1.3 Test product with non-matching name displays database image instead of placeholder
    - Create test product named "gloves" with `image: "assets/products/gloves.jpeg"` in Firestore
    - Test that ProductCard displays the image from `product.image` field (Bug Condition from design)
    - Expected behavior: Image should load from `assets/products/gloves.jpeg` (Property 1 from design)
    - Run test on UNFIXED code
    - **EXPECTED OUTCOME**: Test FAILS (displays placeholder icon because "gloves" doesn't match hardcoded names - this is correct, it proves the bug exists)
    - Document counterexample: "Product 'gloves' with image path displays placeholder icon instead of actual image"
    - Mark task complete when test is written, run, and failure is documented
    - _Requirements: 1.1, 2.1, 2.2_
  
  - [ ] 1.4 Test report image on emulator loads from correct URL
    - Create test report with `imagePath: "uploads/report_123.jpg"` in Firestore
    - Test that ReportImageWidget constructs accessible URL (Bug Condition from design)
    - Expected behavior: Image should load successfully or display error placeholder if server not running (Property 2 from design)
    - Run test on UNFIXED code in emulator
    - **EXPECTED OUTCOME**: Test may FAIL if server is not running at `http://10.0.2.2:8000` (this confirms the bug exists)
    - Document counterexample: "Report image attempts to load from emulator-specific URL which may not be accessible"
    - Mark task complete when test is written, run, and failure is documented
    - _Requirements: 1.3, 2.3_
  
  - [ ] 1.5 Test report image on physical device loads from correct URL
    - Create test report with `imagePath: "uploads/report_456.jpg"` in Firestore
    - Test that ReportImageWidget constructs accessible URL on physical device (Bug Condition from design)
    - Expected behavior: Image should load successfully from accessible URL (Property 2 from design)
    - Run test on UNFIXED code on physical device
    - **EXPECTED OUTCOME**: Test FAILS (10.0.2.2 address not accessible on physical device - this is correct, it proves the bug exists)
    - Document counterexample: "Report image fails to load on physical device due to emulator-specific base URL"
    - Mark task complete when test is written, run, and failure is documented
    - _Requirements: 1.3, 2.3_
  
  - [ ] 1.6 Test report with Firebase Storage URL loads correctly
    - Create test report with `imagePath: "https://firebasestorage.googleapis.com/.../report.jpg"` in Firestore
    - Test that ReportImageWidget loads image from Firebase Storage URL (Bug Condition from design)
    - Expected behavior: Image should load successfully from Firebase Storage (Property 2 from design)
    - Run test on UNFIXED code
    - **EXPECTED OUTCOME**: Test should PASS if URL is valid (this is correct behavior to preserve)
    - Document result: "Report with Firebase Storage URL loads correctly on unfixed code"
    - Mark task complete when test is written, run, and result is documented
    - _Requirements: 2.3_

- [ ] 2. Write preservation property tests (BEFORE implementing fix)
  - **Property 2: Preservation** - Existing Product and Report Functionality
  - **IMPORTANT**: Follow observation-first methodology
  - Observe behavior on UNFIXED code for non-buggy inputs
  - Write property-based tests capturing observed behavior patterns from Preservation Requirements
  - Property-based testing generates many test cases for stronger guarantees
  - Run tests on UNFIXED code
  - **EXPECTED OUTCOME**: Tests PASS (this confirms baseline behavior to preserve)
  - Mark task complete when tests are written, run, and passing on unfixed code
  
  - [ ] 2.1 Observe and test existing product images continue working
    - Observe: Products with names "vest", "jacket", "boots", "helmet", "hard hat", "earmuffs" display correctly on unfixed code
    - Write property-based test: For all products with these names, images display correctly (Property 3 from design)
    - Verify test passes on UNFIXED code
    - **EXPECTED OUTCOME**: Test PASSES (confirms baseline behavior)
    - _Requirements: 3.1_
  
  - [ ] 2.2 Observe and test empty report image displays placeholder
    - Observe: Reports with empty `imagePath` display "No image" placeholder on unfixed code
    - Write property-based test: For all reports with empty imagePath, "No image" placeholder displays (Property 5 from design)
    - Verify test passes on UNFIXED code
    - **EXPECTED OUTCOME**: Test PASSES (confirms baseline behavior)
    - _Requirements: 3.2_
  
  - [ ] 2.3 Observe and test image error handling displays error placeholder
    - Observe: Invalid image paths display error placeholder with "Image unavailable" message on unfixed code
    - Write property-based test: For all invalid image paths, error placeholder displays (Property 5 from design)
    - Verify test passes on UNFIXED code
    - **EXPECTED OUTCOME**: Test PASSES (confirms baseline behavior)
    - _Requirements: 3.3_
  
  - [ ] 2.4 Observe and test product card interactions preserve functionality
    - Observe: Tapping product cards navigates to details screen on unfixed code
    - Observe: Clicking "Add to Cart" adds product and shows success snackbar on unfixed code
    - Write property-based test: For all product card interactions, navigation and cart operations work correctly (Property 4 from design)
    - Verify test passes on UNFIXED code
    - **EXPECTED OUTCOME**: Test PASSES (confirms baseline behavior)
    - _Requirements: 3.4, 3.6, 3.7_
  
  - [ ] 2.5 Observe and test report display preserves functionality
    - Observe: Report information (type, description, status, date, location) displays correctly on unfixed code
    - Write property-based test: For all report displays, information displays correctly (Property 5 from design)
    - Verify test passes on UNFIXED code
    - **EXPECTED OUTCOME**: Test PASSES (confirms baseline behavior)
    - _Requirements: 3.5_

- [ ] 3. Fix for Product and Report Image Display Issues

  - [ ] 3.1 Implement ProductCard image loading fix
    - Modify `lib/screens/user/products_screen.dart` ProductCard widget
    - Remove or deprecate the `_getImagePath()` method (no longer needed)
    - Replace `Image.asset(_getImagePath(product.name))` with conditional logic:
      - If `product.image` starts with "assets/", use `Image.asset(product.image)`
      - If `product.image` starts with "http://" or "https://", use `Image.network(product.image)` or `CachedNetworkImage`
      - If `product.image` is empty, use proper placeholder image
    - Add `errorBuilder` callbacks to both `Image.asset()` and `Image.network()` for error handling
    - Consider creating reusable `ProductImageWidget` similar to `ReportImageWidget`
    - _Bug_Condition: isBugCondition(input) where input.product.image IS NOT EMPTY AND input.product.name NOT IN hardcoded list_
    - _Expected_Behavior: expectedBehavior(result) from design - images load from database-specified paths (Property 1)_
    - _Preservation: Preservation Requirements from design - existing product images, error handling, and interactions continue working (Properties 3, 4, 5)_
    - _Requirements: 1.1, 1.2, 2.1, 2.2, 2.4, 2.5, 3.1, 3.4, 3.6, 3.7_
  
  - [ ] 3.2 Verify report image loading configuration
    - Review `lib/widgets/report_image_widget.dart` ReportImageWidget implementation
    - Review `lib/config/app_config.dart` AppConfig.getImageUrl() method
    - Verify that Firebase Storage URLs are handled correctly (should pass through unchanged)
    - Verify that relative paths are constructed with base URL correctly
    - Document limitation: `baseUrl = 'http://10.0.2.2:8000'` only works in Android emulator
    - Consider adding environment detection or configuration for physical device testing
    - If changes needed, update AppConfig to handle physical device URLs
    - _Bug_Condition: isBugCondition(input) where input.report.imagePath IS NOT EMPTY AND imageUrl is inaccessible_
    - _Expected_Behavior: expectedBehavior(result) from design - images load from correct location with proper error handling (Property 2)_
    - _Preservation: Preservation Requirements from design - empty image placeholder and error handling continue working (Property 5)_
    - _Requirements: 1.3, 1.4, 2.3, 3.2, 3.3, 3.5_
  
  - [ ] 3.3 Verify bug condition exploration tests now pass
    - **Property 1: Expected Behavior** - Product and Report Images Load from Database
    - **IMPORTANT**: Re-run the SAME tests from task 1 - do NOT write new tests
    - The tests from task 1 encode the expected behavior
    - When these tests pass, it confirms the expected behavior is satisfied
    - Run bug condition exploration tests from step 1 (tasks 1.1-1.6)
    - **EXPECTED OUTCOME**: Tests 1.1-1.5 PASS (confirms bugs are fixed), Test 1.6 continues to PASS
    - _Requirements: Expected Behavior Properties from design - Property 1 and Property 2_
  
  - [ ] 3.4 Verify preservation tests still pass
    - **Property 2: Preservation** - Existing Product and Report Functionality
    - **IMPORTANT**: Re-run the SAME tests from task 2 - do NOT write new tests
    - Run preservation property tests from step 2 (tasks 2.1-2.5)
    - **EXPECTED OUTCOME**: All tests PASS (confirms no regressions)
    - Confirm all tests still pass after fix (no regressions)
    - _Requirements: Preservation Requirements from design - Properties 3, 4, 5_

- [ ] 4. Checkpoint - Ensure all tests pass
  - Verify all bug condition exploration tests pass (tasks 1.1-1.6)
  - Verify all preservation property tests pass (tasks 2.1-2.5)
  - Run full test suite to ensure no regressions
  - Test on both emulator and physical device if possible
  - Ask the user if questions arise or if additional testing is needed
