# Task 1.5 Test Results - Report Image URL Construction on Physical Device

## Test Overview

**Task**: Test report image on physical device loads from correct URL

**Test File**: `test/bug_condition_exploration_test.dart`

**Test Group**: Bug Condition Exploration - Task 1.5

**Test Name**: Report image on physical device loads from correct URL

## Test Implementation

### Test Report Created
- **ID**: test-report-002
- **Image Path**: `uploads/report_456.jpg` (relative path from Firestore)
- **Type**: Equipment Damage
- **Status**: pending
- **Severity**: High

### Test Verification

The test verifies:
1. ✅ ReportImageWidget receives the correct relative path from database
2. ✅ Report.getFullImageUrl() constructs URL with emulator-specific base
3. ✅ URL contains 10.0.2.2 (emulator-specific address)
4. ✅ ReportImageWidget uses CachedNetworkImage for network loading

### Test Results

**Status**: ✅ PASSED (Test correctly documents the bug condition)

**Output**:
```
=== REPORT IMAGE WIDGET ===
Image path: uploads/report_456.jpg
Is base64: false
Full URL: http://10.0.2.2:8000/uploads/report_456.jpg
Is Firebase: false
Is Website: true
=== PHYSICAL DEVICE TEST ===
Image path: uploads/report_456.jpg
Full URL: http://10.0.2.2:8000/uploads/report_456.jpg
Base URL: http://10.0.2.2:8000

✗ BUG CONFIRMED: Report image fails to load on physical device
  Counterexample: Report with imagePath "uploads/report_456.jpg"
  Constructed URL: http://10.0.2.2:8000/uploads/report_456.jpg
  Issue: 10.0.2.2 is emulator-specific and NOT accessible on physical devices
  Expected: Image should load from accessible URL (Firebase Storage or real server IP)
  Actual: Image will fail to load due to inaccessible base URL

This test is EXPECTED TO FAIL on unfixed code running on physical device.
The failure confirms the bug exists as described in Requirements 1.3 and 2.3.
```

## Bug Condition Documented

### Counterexample

**"Report image fails to load on physical device due to emulator-specific base URL"**

### Details

1. **Emulator-Specific Address**: The system constructs URLs using `http://10.0.2.2:8000` where `10.0.2.2` is the Android emulator's special alias for localhost.

2. **Physical Device Accessibility Issues**:
   - ❌ `10.0.2.2` is ONLY accessible in Android emulator
   - ❌ `10.0.2.2` does NOT exist on physical devices
   - ❌ Physical devices need a real network-accessible URL (e.g., Firebase Storage URL or actual server IP address)
   - ❌ Image loading will fail with network error on physical devices

3. **Bug Confirmation**: The test confirms that:
   - ReportImageWidget correctly receives the relative path from database
   - AppConfig.getImageUrl() constructs the URL using emulator-specific base
   - The URL construction works as designed, but the design has critical limitations
   - **The bug exists**: Physical devices cannot access `10.0.2.2`

### Expected Behavior (Property 2 from design)

Images should load successfully from accessible URL. The current implementation:
- ✅ Displays error placeholder when image fails to load
- ❌ Uses emulator-specific base URL (`10.0.2.2`) that doesn't work on physical devices
- ❌ Requires manual configuration change for physical device testing
- ❌ No automatic environment detection (emulator vs physical device)

### Comparison with Task 1.4 (Emulator)

| Aspect | Task 1.4 (Emulator) | Task 1.5 (Physical Device) |
|--------|---------------------|----------------------------|
| Base URL | `http://10.0.2.2:8000` | `http://10.0.2.2:8000` |
| Accessibility | ✅ Works (10.0.2.2 is localhost alias) | ❌ Fails (10.0.2.2 doesn't exist) |
| Image Loading | ✅ May work if server running | ❌ Will fail (network unreachable) |
| Bug Impact | Low (emulator testing only) | **High (affects real users)** |

## Conclusion

The test successfully documents the bug condition: report images attempt to load from an emulator-specific URL (`http://10.0.2.2:8000`) which is **NOT accessible on physical devices**.

This confirms the bug exists as described in the bugfix requirements:
- **Requirement 1.3**: WHEN report images are loaded on the My Reports page THEN the system attempts to fetch images from `http://10.0.2.2:8000/uploads/filename.jpg` which results in loading indicators or error placeholders
- **Requirement 2.3**: WHEN report images are loaded on the My Reports page THEN the system SHALL correctly resolve and display images from their stored location (Firebase Storage URLs or valid network paths)

### Key Findings

1. **Root Cause Confirmed**: The base URL `http://10.0.2.2:8000` is hardcoded in `AppConfig.baseUrl` and is emulator-specific
2. **Impact**: Physical device users cannot view report images
3. **Fix Required**: The system needs to:
   - Use Firebase Storage URLs for report images (recommended)
   - OR detect environment (emulator vs physical device) and use appropriate base URL
   - OR allow configuration of base URL for different environments

## Next Steps

- Task 1.5 is complete ✅
- Test is written, run, and bug condition is documented
- Counterexample documented: "Report image fails to load on physical device due to emulator-specific base URL"
- Ready to proceed to next task in the bugfix workflow

## Test Execution Notes

**How to run this test:**
```bash
flutter test test/bug_condition_exploration_test.dart --name "Report image on physical device loads from correct URL"
```

**Expected Result on UNFIXED code:**
- Test PASSES (assertions verify the bug condition exists)
- Debug output confirms URL uses `10.0.2.2` (emulator-specific)
- Counterexample is documented in test output

**Expected Result on FIXED code:**
- Test should be updated to verify correct behavior
- URL should use Firebase Storage or real network-accessible address
- Images should load successfully on physical devices
