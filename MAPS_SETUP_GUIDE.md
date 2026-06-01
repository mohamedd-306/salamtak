# Google Maps Setup Guide

## Overview
This guide will help you configure Google Maps for the Salamtak mobile app across all platforms (Android, iOS, and Web).

## Step 1: Get Google Maps API Key

1. Go to [Google Cloud Console](https://console.cloud.google.com)
2. Create a new project or select an existing one
3. Enable the following APIs:
   - **Maps SDK for Android**
   - **Maps SDK for iOS**
   - **Maps JavaScript API** (for web)
4. Go to **APIs & Services** → **Credentials**
5. Click **Create Credentials** → **API Key**
6. Copy your API key (it will look like: `AIzaSyXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX`)

## Step 2: Configure Flutter App

### Update `lib/config/maps_config.dart`
```dart
const String kGoogleMapsApiKey = 'YOUR_ACTUAL_API_KEY_HERE';
```

## Step 3: Configure Android

### File: `android/app/src/main/AndroidManifest.xml`

Add the following inside the `<application>` tag:

```xml
<application
    ...>
    
    <!-- Add this meta-data tag -->
    <meta-data
        android:name="com.google.android.geo.API_KEY"
        android:value="YOUR_ACTUAL_API_KEY_HERE"/>
    
    <!-- Rest of your application configuration -->
    ...
</application>
```

### Complete Example:
```xml
<manifest xmlns:android="http://schemas.android.com/apk/res/android">
    <application
        android:label="salamtak"
        android:name="${applicationName}"
        android:icon="@mipmap/ic_launcher">
        
        <!-- Google Maps API Key -->
        <meta-data
            android:name="com.google.android.geo.API_KEY"
            android:value="AIzaSyXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX"/>
        
        <activity
            android:name=".MainActivity"
            ...>
        </activity>
    </application>
</manifest>
```

## Step 4: Configure iOS

### File: `ios/Runner/AppDelegate.swift`

1. Add import at the top:
```swift
import UIKit
import Flutter
import GoogleMaps  // Add this line
```

2. Add API key in the `application` method:
```swift
@UIApplicationMain
@objc class AppDelegate: FlutterAppDelegate {
  override func application(
    _ application: UIApplication,
    didFinishLaunchingWithOptions launchOptions: [UIApplication.LaunchOptionsKey: Any]?
  ) -> Bool {
    // Add this line
    GMSServices.provideAPIKey("YOUR_ACTUAL_API_KEY_HERE")
    
    GeneratedPluginRegistrant.register(with: self)
    return super.application(application, didFinishLaunchingWithOptions: launchOptions)
  }
}
```

## Step 5: Configure Web

### File: `web/index.html`

Add the Google Maps JavaScript API script before the closing `</head>` tag:

```html
<!DOCTYPE html>
<html>
<head>
  ...
  
  <!-- Add this script tag before </head> -->
  <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_ACTUAL_API_KEY_HERE"></script>
</head>
<body>
  ...
</body>
</html>
```

## Step 6: Test the Implementation

### Run on Android:
```bash
flutter run -d android
```

### Run on iOS:
```bash
flutter run -d ios
```

### Run on Web:
```bash
flutter run -d chrome
```

### Test Checklist:
- [ ] Map loads and displays correctly
- [ ] Can tap on map to select location
- [ ] Marker appears at selected location
- [ ] Can drag marker to new location
- [ ] Coordinates update when marker moves
- [ ] Manual coordinate entry works
- [ ] Quick city selection works
- [ ] Fallback UI shows when API key is missing

## Troubleshooting

### Android: Map shows blank/gray screen
- **Solution**: Check that the API key is correctly added to `AndroidManifest.xml`
- **Solution**: Ensure "Maps SDK for Android" is enabled in Google Cloud Console
- **Solution**: Check that the API key has no restrictions or allows your app's package name

### iOS: Map doesn't load
- **Solution**: Verify `GMSServices.provideAPIKey()` is called in `AppDelegate.swift`
- **Solution**: Ensure "Maps SDK for iOS" is enabled in Google Cloud Console
- **Solution**: Run `pod install` in the `ios` directory

### Web: Map doesn't load
- **Solution**: Check browser console for errors
- **Solution**: Verify the script tag is correctly added to `web/index.html`
- **Solution**: Ensure "Maps JavaScript API" is enabled in Google Cloud Console

### API Key Restrictions
If you want to restrict your API key for security:

**Android**:
- Restriction type: Android apps
- Add your package name: `com.example.salamtak`
- Add your SHA-1 certificate fingerprint

**iOS**:
- Restriction type: iOS apps
- Add your bundle identifier: `com.example.salamtak`

**Web**:
- Restriction type: HTTP referrers
- Add your website URLs

## Current Fallback Behavior

If the API key is not configured, the app will show a fallback UI with:
- Manual latitude/longitude input fields
- Quick selection buttons for Egyptian cities
- Coordinate validation
- Address label input

This allows the app to function even without Google Maps, though with reduced functionality.

## Cost Considerations

Google Maps Platform offers:
- **$200 free credit per month**
- Maps SDK for Mobile: $7 per 1,000 requests (after free tier)
- Maps JavaScript API: $7 per 1,000 requests (after free tier)

For most small to medium apps, the free tier is sufficient.

## Security Best Practices

1. **Never commit API keys to version control**
   - Add `lib/config/maps_config.dart` to `.gitignore` if it contains real keys
   - Use environment variables for production builds

2. **Use API key restrictions**
   - Restrict by application (Android/iOS bundle ID)
   - Restrict by HTTP referrer (Web)

3. **Monitor usage**
   - Set up billing alerts in Google Cloud Console
   - Monitor API usage regularly

## Additional Resources

- [Google Maps Platform Documentation](https://developers.google.com/maps/documentation)
- [Flutter Google Maps Plugin](https://pub.dev/packages/google_maps_flutter)
- [Google Cloud Console](https://console.cloud.google.com)
