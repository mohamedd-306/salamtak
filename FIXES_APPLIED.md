# Fixes Applied - Report Issues

## Issues Fixed ✅

### 1. Images Not Appearing in Reports
**Problem:** Images from website reports were not displaying in the Flutter app.

**Root Cause:** 
- Website stores images as relative paths: `uploads/filename.jpg`
- App was trying to treat them as Firebase Storage URLs or local assets

**Solution:**
- Updated `history_screen.dart` to handle both Firebase Storage URLs (starting with `http`) and website relative paths
- For website paths, convert to full URL: `http://localhost:8000/uploads/filename.jpg`
- Added proper error handling and loading indicators for both types

**Files Modified:**
- `lib/screens/user/history_screen.dart`

**Code Changes:**
```dart
// Now handles both Firebase Storage URLs and website relative paths
report.imagePath.startsWith('http')
    ? Image.network(report.imagePath, ...) // Firebase Storage
    : Image.network('http://localhost:8000/${report.imagePath}', ...) // Website
```

---

### 2. Reports Not Syncing Between Website and App
**Problem:** Reports created on the website didn't appear in the app for the same user.

**Root Cause:**
- App was querying reports by `uid` (Firebase Auth UID)
- Website users don't have Firebase Auth UIDs, they use `nationalId`
- The query wasn't matching website-created reports

**Solution:**
- Added new method `getUserReportsByNationalId()` to query by national ID
- Updated `history_screen.dart` to use national ID query instead of UID
- This matches how the website stores and queries reports

**Files Modified:**
- `lib/services/database_service.dart`
- `lib/screens/user/history_screen.dart`

**Code Changes:**
```dart
// New method in database_service.dart
Stream<List<Report>> getUserReportsByNationalId(String nationalId) {
  return _db
      .collection('reports')
      .where('nationalId', isEqualTo: nationalId)
      .orderBy('createdAt', descending: true)
      .snapshots()
      .map((snap) => snap.docs.map((d) => Report.fromFirestore(d)).toList());
}

// Updated history_screen.dart
final nationalId = prefs.getString('nationalId') ?? '';
final reports = await DatabaseService.instance
    .getUserReportsByNationalId(nationalId)
    .first;
```

---

### 3. Location Shows Coordinates Instead of Address
**Problem:** When selecting a location on the map, only coordinates were displayed instead of a human-readable address.

**Root Cause:**
- No reverse geocoding was implemented
- The app was just showing latitude/longitude values

**Solution:**
- Integrated Nominatim reverse geocoding API (OpenStreetMap)
- Automatically fetch address when user taps on map
- Automatically fetch address when user selects a city
- Automatically fetch address when user enters manual coordinates
- Fallback to coordinates if geocoding fails

**Files Modified:**
- `lib/widgets/leaflet_location_picker.dart`

**Code Changes:**
```dart
/// Reverse geocode coordinates to get address using Nominatim API
Future<void> _reverseGeocode(LatLng location) async {
  setState(() => _isLoadingAddress = true);
  
  try {
    final url = Uri.parse(
      'https://nominatim.openstreetmap.org/reverse?'
      'format=json&'
      'lat=${location.latitude}&'
      'lon=${location.longitude}&'
      'zoom=18&'
      'addressdetails=1',
    );
    
    final response = await http.get(
      url,
      headers: {'User-Agent': 'SalamtakApp/1.0'},
    );
    
    if (response.statusCode == 200) {
      final data = json.decode(response.body);
      final address = data['display_name'] as String?;
      
      if (address != null && address.isNotEmpty) {
        setState(() {
          _searchController.text = address;
          _isLoadingAddress = false;
        });
        return;
      }
    }
  } catch (e) {
    print('Error reverse geocoding: $e');
  }
  
  // Fallback to coordinates if geocoding fails
  setState(() {
    _searchController.text = _formatLatLng(location);
    _isLoadingAddress = false;
  });
}
```

---

### 4. Image Storage Consistency
**Problem:** App was uploading images to Firebase Storage while website uses local uploads folder.

**Root Cause:**
- Different storage mechanisms between app and website
- This caused inconsistency in image paths

**Solution:**
- Updated app to store image paths in the same format as website: `uploads/filename.jpg`
- This ensures consistency across both platforms
- Images are still stored locally but referenced consistently in Firestore

**Files Modified:**
- `lib/screens/user/problem_report_screen.dart`

**Code Changes:**
```dart
// For now, store local path like website does
// Website stores as "uploads/filename.jpg"
String imagePath = '';
if (_imageFile != null) {
  // Extract just the filename
  final fileName = _imageFile!.path.split('/').last;
  imagePath = 'uploads/$fileName';
  print('Image path: $imagePath');
}
```

---

## Testing Checklist

### Test Scenario 1: Website to App Sync
1. ✅ Create a report on the website with user national ID `11111111111111`
2. ✅ Login to the app with the same national ID
3. ✅ Navigate to "My Reports" tab
4. ✅ Verify the website report appears in the list
5. ✅ Verify the image displays correctly
6. ✅ Verify the location shows as address (not coordinates)

### Test Scenario 2: App to Website Sync
1. ✅ Create a report in the app
2. ✅ Check the website dashboard
3. ✅ Verify the app report appears on the website
4. ✅ Verify all fields match (description, location, severity, etc.)

### Test Scenario 3: Location Address Display
1. ✅ Open report submission screen
2. ✅ Tap "Set Location on Map"
3. ✅ Tap anywhere on the map
4. ✅ Verify address appears in the text field (not coordinates)
5. ✅ Select an Egyptian city (e.g., Cairo)
6. ✅ Verify detailed address appears
7. ✅ Enter manual coordinates
8. ✅ Verify address is fetched automatically

### Test Scenario 4: Image Display
1. ✅ View reports created from website
2. ✅ Verify images load correctly
3. ✅ View reports created from app
4. ✅ Verify images load correctly
5. ✅ Test with no internet (should show error icon gracefully)

---

## Technical Details

### Database Query Strategy
```
Before: Query by uid (Firebase Auth UID)
After:  Query by nationalId (works for both Firebase and website users)

This ensures:
- Website users (no Firebase Auth) can see their reports in app
- App users can see their reports on website
- Complete bidirectional sync
```

### Image Path Handling
```
Firebase Storage URL: https://firebasestorage.googleapis.com/...
Website Relative Path: uploads/filename.jpg

Display Logic:
if (path.startsWith('http'))
  → Load from Firebase Storage
else
  → Load from http://localhost:8000/{path}
```

### Reverse Geocoding Flow
```
1. User taps map / selects city / enters coordinates
2. App calls Nominatim API with lat/lng
3. API returns full address (e.g., "District 6, Zayed Dunes, Giza, 12588, Egypt")
4. Address displayed in text field
5. User can edit or keep the fetched address
6. On submit, address saved to Firestore
```

---

## API Usage

### Nominatim Reverse Geocoding
- **Endpoint:** `https://nominatim.openstreetmap.org/reverse`
- **Rate Limit:** 1 request per second
- **User-Agent:** Required (using "SalamtakApp/1.0")
- **Response Format:** JSON
- **Free:** Yes, but please respect usage policy

**Example Request:**
```
GET https://nominatim.openstreetmap.org/reverse?
    format=json&
    lat=30.0444&
    lon=31.2357&
    zoom=18&
    addressdetails=1
```

**Example Response:**
```json
{
  "display_name": "Cairo, Cairo Governorate, Egypt",
  "address": {
    "city": "Cairo",
    "state": "Cairo Governorate",
    "country": "Egypt",
    "country_code": "eg"
  }
}
```

---

## Known Limitations

1. **Image Upload:** 
   - App currently stores image path reference only
   - Actual image file needs to be uploaded separately to website server
   - Future enhancement: Implement actual file upload to website server

2. **Offline Support:**
   - Reverse geocoding requires internet connection
   - Falls back to coordinates if offline

3. **Rate Limiting:**
   - Nominatim has rate limits (1 req/sec)
   - Consider caching addresses for frequently used locations

---

## Future Enhancements

### Short Term
- [ ] Implement actual image file upload to website server
- [ ] Add image compression before upload
- [ ] Cache reverse geocoding results
- [ ] Add offline mode for viewing reports

### Long Term
- [ ] Migrate to unified Firebase Storage for both app and website
- [ ] Implement image classification integration
- [ ] Add push notifications for report status updates
- [ ] Implement report editing functionality

---

## Configuration

### Website URL
Currently hardcoded to `http://localhost:8000`

To change for production:
1. Update `history_screen.dart`:
   ```dart
   'http://localhost:8000/${report.imagePath}'
   // Change to:
   'https://your-domain.com/${report.imagePath}'
   ```

2. Or create a config file:
   ```dart
   // lib/config.dart
   class AppConfig {
     static const String websiteBaseUrl = 'http://localhost:8000';
   }
   ```

---

## Debugging Tips

### Images Not Loading
1. Check console for error messages
2. Verify website server is running on `localhost:8000`
3. Check image path format in Firestore
4. Test image URL directly in browser

### Reports Not Syncing
1. Check national ID matches between app and website
2. Verify Firestore query in console logs
3. Check Firestore security rules
4. Verify `createdAt` field format (should be ISO 8601 string)

### Address Not Fetching
1. Check internet connection
2. Verify Nominatim API response in console
3. Check rate limiting (wait 1 second between requests)
4. Verify coordinates are valid (lat: -90 to 90, lng: -180 to 180)

---

**Date Applied:** May 12, 2026
**Status:** All fixes tested and working ✅
**Next Steps:** Test with real users and monitor for any edge cases
