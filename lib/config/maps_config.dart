/// Google Maps API Configuration
/// 
/// To enable maps functionality:
/// 1. Get a Google Maps API key from: https://console.cloud.google.com
/// 2. Enable the following APIs:
///    - Maps SDK for Android
///    - Maps SDK for iOS
///    - Maps JavaScript API (for web)
/// 3. Replace 'YOUR_GOOGLE_MAPS_API_KEY' below with your actual API key
/// 4. Update platform-specific configuration files (see instructions below)

const String kGoogleMapsApiKey = 'YOUR_GOOGLE_MAPS_API_KEY';

/// Check if API key is configured
const bool hasApiKey = kGoogleMapsApiKey != 'YOUR_GOOGLE_MAPS_API_KEY';

/// Platform-specific configuration instructions:
/// 
/// ANDROID (android/app/src/main/AndroidManifest.xml):
/// Add inside <application> tag:
/// ```xml
/// <meta-data
///     android:name="com.google.android.geo.API_KEY"
///     android:value="YOUR_GOOGLE_MAPS_API_KEY"/>
/// ```
/// 
/// iOS (ios/Runner/AppDelegate.swift):
/// Add at the top of the file:
/// ```swift
/// import GoogleMaps
/// ```
/// Add inside application() method:
/// ```swift
/// GMSServices.provideAPIKey("YOUR_GOOGLE_MAPS_API_KEY")
/// ```
/// 
/// WEB (web/index.html):
/// Add before </head> tag:
/// ```html
/// <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY"></script>
/// ```

/// Default map location (Cairo, Egypt)
const double kDefaultLatitude = 30.0444;
const double kDefaultLongitude = 31.2357;
const double kDefaultZoom = 14.0;

/// Egyptian cities for quick selection
const List<Map<String, dynamic>> kEgyptianCities = [
  {'name': 'Cairo', 'lat': 30.0444, 'lng': 31.2357},
  {'name': 'Alexandria', 'lat': 31.2001, 'lng': 29.9187},
  {'name': 'Giza', 'lat': 30.0131, 'lng': 31.2089},
  {'name': 'Luxor', 'lat': 25.6872, 'lng': 32.6396},
  {'name': 'Aswan', 'lat': 24.0889, 'lng': 32.8998},
  {'name': 'Mansoura', 'lat': 31.0364, 'lng': 31.3807},
];
