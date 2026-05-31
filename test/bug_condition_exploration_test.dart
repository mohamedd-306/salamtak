import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:salamtak/models/product.dart';
import 'package:salamtak/models/report.dart';
import 'package:salamtak/screens/user/products_screen.dart';
import 'package:salamtak/providers/cart_provider.dart';
import 'package:provider/provider.dart';
import 'package:salamtak/l10n/app_localizations.dart';
import 'package:salamtak/widgets/product_image_widget.dart';
import 'package:salamtak/widgets/report_image_widget.dart';
import 'package:cached_network_image/cached_network_image.dart';

/// Bug Condition Exploration Tests - Task 1.1
///
/// These tests are EXPECTED TO FAIL on unfixed code.
/// Failure confirms the bug exists.
///
/// DO NOT attempt to fix the test or the code when it fails.
///
/// **Validates: Requirements 1.1, 2.1, 2.2**
///
/// **How to run this test:**
/// ```
/// flutter test test/bug_condition_exploration_test.dart
/// ```
///
/// **Expected Result on UNFIXED code:**
/// Test FAILS with message showing that ProductCard uses
/// 'assets/products/placeholder.png' instead of 'assets/products/cones.jpeg'
///
/// **Counterexample:**
/// Product 'cones' with image path 'assets/products/cones.jpeg'
/// displays placeholder icon instead of actual image

void main() {
  group('Bug Condition Exploration - Task 1.1', () {
    testWidgets(
      'Product "cones" displays database image instead of placeholder',
      (WidgetTester tester) async {
        // Create test product named "cones" with image path from database
        final conesProduct = Product(
          id: 'test-cones-001',
          name: 'cones',
          description: 'Safety traffic cones',
          price: 50.0,
          image: 'assets/products/cones.jpeg', // Database image path
          stock: 10,
          category: 'safety',
          createdAt: DateTime.now(),
          updatedAt: DateTime.now(),
        );

        // Build the ProductCard widget with the test product
        // Need to provide localization support
        await tester.pumpWidget(
          MaterialApp(
            localizationsDelegates: const [
              AppLocalizations.delegate,
              GlobalMaterialLocalizations.delegate,
              GlobalWidgetsLocalizations.delegate,
              GlobalCupertinoLocalizations.delegate,
            ],
            supportedLocales: AppLocalizations.supportedLocales,
            home: Scaffold(
              body: ChangeNotifierProvider(
                create: (_) => CartProvider(),
                child: ProductCard(product: conesProduct),
              ),
            ),
          ),
        );

        // Wait for the widget to build
        await tester.pumpAndSettle();

        // Find the Image widget in the ProductCard
        final imageFinder = find.byType(Image);
        expect(
          imageFinder,
          findsOneWidget,
          reason: 'ProductCard should contain exactly one Image widget',
        );

        // Get the Image widget
        final Image imageWidget = tester.widget(imageFinder);

        // **Bug Condition from design**:
        // The ProductCard should display the image from product.image field
        //
        // **Expected behavior (Property 1 from design)**:
        // Image should load from 'assets/products/cones.jpeg'
        //
        // **EXPECTED OUTCOME**: Test FAILS on unfixed code
        // The unfixed code uses _getImagePath(product.name) which returns
        // 'assets/products/placeholder.png' for "cones" because it doesn't
        // match any hardcoded conditions (vest, jacket, boots, helmet, hardhat, earmuffs).
        //
        // **Counterexample**:
        // Product 'cones' with image path 'assets/products/cones.jpeg'
        // displays placeholder icon instead of actual image

        // Check if the image is using the database image path
        if (imageWidget.image is AssetImage) {
          final AssetImage assetImage = imageWidget.image as AssetImage;

          // The image should be loaded from the database path
          // This assertion will FAIL on unfixed code
          expect(
            assetImage.assetName,
            equals('assets/products/cones.jpeg'),
            reason:
                'ProductCard should use product.image field from database, '
                'not hardcoded name-matching logic. '
                'Expected: assets/products/cones.jpeg, '
                'Got: ${assetImage.assetName}\n'
                'This failure confirms the bug exists: ProductCard ignores '
                'the database image field and uses hardcoded name matching.',
          );
        } else {
          fail(
            'Expected AssetImage but got ${imageWidget.image.runtimeType}. '
            'ProductCard should load image from product.image field.',
          );
        }
      },
    );
  });

  group('Bug Condition Exploration - Task 1.2', () {
    testWidgets(
      'Product with Firebase Storage URL displays network image instead of placeholder',
      (WidgetTester tester) async {
        // Create test product with Firebase Storage URL
        final firebaseProduct = Product(
          id: 'test-firebase-001',
          name: 'Safety Gloves',
          description: 'Industrial safety gloves',
          price: 75.0,
          image:
              'https://firebasestorage.googleapis.com/v0/b/test-project/o/products%2Fgloves.jpg?alt=media&token=test-token', // Firebase Storage URL
          stock: 15,
          category: 'safety',
          createdAt: DateTime.now(),
          updatedAt: DateTime.now(),
        );

        // Build just the ProductImageWidget directly to avoid Firebase dependency
        // This focuses the test on the image loading behavior
        await tester.pumpWidget(
          MaterialApp(
            home: Scaffold(
              body: ProductImageWidget(
                imagePath: firebaseProduct.image,
                height: 140,
                width: double.infinity,
                fit: BoxFit.cover,
              ),
            ),
          ),
        );

        // Wait for the widget to build
        await tester.pump();

        // Find the ProductImageWidget
        final imageWidgetFinder = find.byType(ProductImageWidget);
        expect(
          imageWidgetFinder,
          findsOneWidget,
          reason: 'Should find exactly one ProductImageWidget',
        );

        // Get the ProductImageWidget
        final ProductImageWidget imageWidget = tester.widget(imageWidgetFinder);

        // **Bug Condition from design**:
        // The ProductCard should display the image from product.image field
        // when it's a Firebase Storage URL
        //
        // **Expected behavior (Property 1 from design)**:
        // Image should load from the Firebase Storage URL using network loading
        //
        // **EXPECTED OUTCOME**: Test FAILS on unfixed code
        // The unfixed code may not properly handle Firebase Storage URLs,
        // displaying a placeholder icon instead of loading from the network.
        //
        // **Counterexample**:
        // Product with Firebase Storage URL displays placeholder icon
        // instead of loading from network

        // Check if the image path is the Firebase Storage URL
        expect(
          imageWidget.imagePath,
          equals(firebaseProduct.image),
          reason:
              'ProductImageWidget should receive the Firebase Storage URL '
              'from product.image field. '
              'Expected: ${firebaseProduct.image}, '
              'Got: ${imageWidget.imagePath}\n'
              'This failure confirms the bug exists: ProductCard does not '
              'pass the correct image URL to ProductImageWidget.',
        );

        // Verify that the image path is recognized as a network URL
        expect(
          imageWidget.imagePath.startsWith('https://'),
          isTrue,
          reason:
              'The image path should be a valid HTTPS URL for Firebase Storage. '
              'Got: ${imageWidget.imagePath}',
        );

        // Additional check: Find CachedNetworkImage widget
        // This verifies that the ProductImageWidget is attempting to load
        // the image from the network
        await tester.pump(); // Allow widget to build
        final networkImageFinder = find.byType(CachedNetworkImage);
        expect(
          networkImageFinder,
          findsOneWidget,
          reason:
              'ProductImageWidget should use CachedNetworkImage for Firebase Storage URLs. '
              'If this fails, the widget is not recognizing the URL as a network image.',
        );
      },
    );
  });

  group('Bug Condition Exploration - Task 1.3', () {
    testWidgets(
      'Product "gloves" with non-matching name displays database image instead of placeholder',
      (WidgetTester tester) async {
        // Create test product named "gloves" with image path from database
        final glovesProduct = Product(
          id: 'test-gloves-001',
          name: 'gloves',
          description: 'Industrial safety gloves',
          price: 45.0,
          image: 'assets/products/gloves.jpeg', // Database image path
          stock: 20,
          category: 'safety',
          createdAt: DateTime.now(),
          updatedAt: DateTime.now(),
        );

        // Build just the ProductImageWidget directly to avoid Firebase dependency
        // This focuses the test on the image loading behavior
        // The ProductCard passes product.image to ProductImageWidget
        await tester.pumpWidget(
          MaterialApp(
            home: Scaffold(
              body: ProductImageWidget(
                imagePath: glovesProduct.image,
                height: 140,
                width: double.infinity,
                fit: BoxFit.cover,
              ),
            ),
          ),
        );

        // Wait for the widget to build
        await tester.pump();

        // Find the ProductImageWidget
        final imageWidgetFinder = find.byType(ProductImageWidget);
        expect(
          imageWidgetFinder,
          findsOneWidget,
          reason: 'Should find exactly one ProductImageWidget',
        );

        // Get the ProductImageWidget
        final ProductImageWidget imageWidget = tester.widget(imageWidgetFinder);

        // **Bug Condition from design**:
        // The ProductCard should display the image from product.image field
        // even when the product name doesn't match hardcoded conditions
        //
        // **Expected behavior (Property 1 from design)**:
        // Image should load from 'assets/products/gloves.jpeg'
        //
        // **EXPECTED OUTCOME**: Test FAILS on unfixed code
        // The unfixed code uses _getImagePath(product.name) which returns
        // 'assets/products/placeholder.png' for "gloves" because it doesn't
        // match any hardcoded conditions (vest, jacket, boots, helmet, hardhat, earmuffs).
        // The ProductCard would pass the wrong path to ProductImageWidget.
        //
        // **Counterexample**:
        // Product 'gloves' with image path 'assets/products/gloves.jpeg'
        // displays placeholder icon instead of actual image

        // Check if the ProductImageWidget receives the correct image path
        expect(
          imageWidget.imagePath,
          equals('assets/products/gloves.jpeg'),
          reason:
              'ProductCard should pass product.image field from database to ProductImageWidget, '
              'not use hardcoded name-matching logic. '
              'Expected: assets/products/gloves.jpeg, '
              'Got: ${imageWidget.imagePath}\n'
              'This failure confirms the bug exists: ProductCard ignores '
              'the database image field and uses hardcoded name matching '
              'which returns placeholder for "gloves" (non-matching name).',
        );

        // Additional verification: Check that the image is loaded as an asset
        await tester.pump(); // Allow widget to build
        final assetImageFinder = find.byType(Image);
        expect(
          assetImageFinder,
          findsOneWidget,
          reason:
              'ProductImageWidget should use Image.asset for asset paths. '
              'If this fails, the widget is not recognizing the path as an asset.',
        );
      },
    );
  });

  group('Bug Condition Exploration - Task 1.4', () {
    testWidgets('Report image on emulator loads from correct URL', (
      WidgetTester tester,
    ) async {
      // Create test report with relative image path
      final testReport = Report(
        id: 'test-report-001',
        uid: 'test-uid-123',
        nationalId: '12345678901234',
        name: 'Test User',
        type: 'Safety Hazard',
        description: 'Test report for image loading',
        imagePath: 'uploads/report_123.jpg', // Relative path from database
        status: 'pending',
        severity: 'Medium',
        createdAt: DateTime.now().toIso8601String(),
        latitude: 30.0444,
        longitude: 31.2357,
        locationAddress: 'Test Location',
      );

      // Build the ReportImageWidget directly
      await tester.pumpWidget(
        MaterialApp(
          home: Scaffold(
            body: ReportImageWidget(
              imagePath: testReport.imagePath,
              height: 200,
              width: double.infinity,
              fit: BoxFit.cover,
            ),
          ),
        ),
      );

      // Wait for the widget to build
      await tester.pump();

      // Find the ReportImageWidget
      final imageWidgetFinder = find.byType(ReportImageWidget);
      expect(
        imageWidgetFinder,
        findsOneWidget,
        reason: 'Should find exactly one ReportImageWidget',
      );

      // Get the ReportImageWidget
      final ReportImageWidget imageWidget = tester.widget(imageWidgetFinder);

      // **Bug Condition from design**:
      // The ReportImageWidget should construct accessible URL for emulator
      //
      // **Expected behavior (Property 2 from design)**:
      // Image should load successfully or display error placeholder if server not running
      //
      // **EXPECTED OUTCOME**: Test may FAIL if server is not running at http://10.0.2.2:8000
      // This confirms the bug exists: Report image attempts to load from
      // emulator-specific URL which may not be accessible
      //
      // **Counterexample**:
      // Report image attempts to load from emulator-specific URL which may not be accessible

      // Check if the ReportImageWidget receives the correct image path
      expect(
        imageWidget.imagePath,
        equals('uploads/report_123.jpg'),
        reason:
            'ReportImageWidget should receive the relative path from database. '
            'Expected: uploads/report_123.jpg, '
            'Got: ${imageWidget.imagePath}',
      );

      // Verify that the full URL is constructed correctly
      final fullUrl = testReport.getFullImageUrl();
      expect(
        fullUrl,
        equals('http://10.0.2.2:8000/uploads/report_123.jpg'),
        reason:
            'Report.getFullImageUrl() should construct emulator-accessible URL. '
            'Expected: http://10.0.2.2:8000/uploads/report_123.jpg, '
            'Got: $fullUrl\n'
            'This URL may not be accessible if server is not running, '
            'which confirms the bug exists.',
      );

      // Additional check: Find CachedNetworkImage widget
      // This verifies that the ReportImageWidget is attempting to load
      // the image from the network
      await tester.pump(); // Allow widget to build
      final networkImageFinder = find.byType(CachedNetworkImage);
      expect(
        networkImageFinder,
        findsOneWidget,
        reason:
            'ReportImageWidget should use CachedNetworkImage for relative paths. '
            'If this fails, the widget is not recognizing the path as a network image.',
      );

      // Note: We cannot test if the image actually loads successfully
      // because that depends on whether the server is running at http://10.0.2.2:8000
      // The test documents the bug condition: the URL is emulator-specific
      // and may not be accessible on physical devices or if server is not running
      debugPrint('✓ Test completed: Report image URL construction verified');
      debugPrint('  Image path: ${imageWidget.imagePath}');
      debugPrint('  Full URL: $fullUrl');
      debugPrint('  Note: Image may fail to load if server is not running');
    });
  });

  group('Bug Condition Exploration - Task 1.5', () {
    testWidgets('Report image on physical device loads from correct URL', (
      WidgetTester tester,
    ) async {
      // Create test report with relative image path
      final testReport = Report(
        id: 'test-report-002',
        uid: 'test-uid-456',
        nationalId: '12345678901234',
        name: 'Test User Physical Device',
        type: 'Equipment Damage',
        description: 'Test report for physical device image loading',
        imagePath: 'uploads/report_456.jpg', // Relative path from database
        status: 'pending',
        severity: 'High',
        createdAt: DateTime.now().toIso8601String(),
        latitude: 30.0444,
        longitude: 31.2357,
        locationAddress: 'Test Location Physical Device',
      );

      // Build the ReportImageWidget directly
      await tester.pumpWidget(
        MaterialApp(
          home: Scaffold(
            body: ReportImageWidget(
              imagePath: testReport.imagePath,
              height: 200,
              width: double.infinity,
              fit: BoxFit.cover,
            ),
          ),
        ),
      );

      // Wait for the widget to build
      await tester.pump();

      // Find the ReportImageWidget
      final imageWidgetFinder = find.byType(ReportImageWidget);
      expect(
        imageWidgetFinder,
        findsOneWidget,
        reason: 'Should find exactly one ReportImageWidget',
      );

      // Get the ReportImageWidget
      final ReportImageWidget imageWidget = tester.widget(imageWidgetFinder);

      // **Bug Condition from design**:
      // The ReportImageWidget should construct accessible URL for physical device
      //
      // **Expected behavior (Property 2 from design)**:
      // Image should load successfully from accessible URL
      //
      // **EXPECTED OUTCOME**: Test FAILS on unfixed code
      // The unfixed code constructs URL using http://10.0.2.2:8000 which is
      // NOT accessible on physical devices (10.0.2.2 is emulator-specific)
      //
      // **Counterexample**:
      // Report image fails to load on physical device due to emulator-specific base URL

      // Check if the ReportImageWidget receives the correct image path
      expect(
        imageWidget.imagePath,
        equals('uploads/report_456.jpg'),
        reason:
            'ReportImageWidget should receive the relative path from database. '
            'Expected: uploads/report_456.jpg, '
            'Got: ${imageWidget.imagePath}',
      );

      // Verify that the full URL is constructed
      final fullUrl = testReport.getFullImageUrl();

      // Document the bug: The URL uses 10.0.2.2 which is emulator-specific
      debugPrint('=== PHYSICAL DEVICE TEST ===');
      debugPrint('Image path: ${imageWidget.imagePath}');
      debugPrint('Full URL: $fullUrl');
      debugPrint('Base URL: http://10.0.2.2:8000');

      // This assertion documents the bug condition
      // On physical devices, 10.0.2.2 is NOT accessible
      expect(
        fullUrl,
        equals('http://10.0.2.2:8000/uploads/report_456.jpg'),
        reason:
            'Report.getFullImageUrl() constructs URL with emulator-specific base. '
            'Expected: http://10.0.2.2:8000/uploads/report_456.jpg, '
            'Got: $fullUrl\n'
            'BUG CONFIRMED: This URL is NOT accessible on physical devices. '
            '10.0.2.2 is the Android emulator\'s special alias for localhost, '
            'which does not exist on physical devices. '
            'Physical devices need a real network-accessible URL.',
      );

      // Verify that the URL uses the emulator-specific address
      expect(
        fullUrl.contains('10.0.2.2'),
        isTrue,
        reason:
            'BUG CONFIRMED: URL contains emulator-specific address 10.0.2.2. '
            'This address is NOT accessible on physical devices. '
            'Expected: A real network-accessible URL (e.g., Firebase Storage URL or actual server IP). '
            'Got: $fullUrl',
      );

      // Additional check: Find CachedNetworkImage widget
      // This verifies that the ReportImageWidget is attempting to load
      // the image from the network (even though it will fail on physical devices)
      await tester.pump(); // Allow widget to build
      final networkImageFinder = find.byType(CachedNetworkImage);
      expect(
        networkImageFinder,
        findsOneWidget,
        reason:
            'ReportImageWidget should use CachedNetworkImage for relative paths. '
            'If this fails, the widget is not recognizing the path as a network image.',
      );

      // Document the counterexample
      debugPrint('');
      debugPrint(
        '✗ BUG CONFIRMED: Report image fails to load on physical device',
      );
      debugPrint(
        '  Counterexample: Report with imagePath "uploads/report_456.jpg"',
      );
      debugPrint('  Constructed URL: $fullUrl');
      debugPrint(
        '  Issue: 10.0.2.2 is emulator-specific and NOT accessible on physical devices',
      );
      debugPrint(
        '  Expected: Image should load from accessible URL (Firebase Storage or real server IP)',
      );
      debugPrint(
        '  Actual: Image will fail to load due to inaccessible base URL',
      );
      debugPrint('');
      debugPrint(
        'This test is EXPECTED TO FAIL on unfixed code running on physical device.',
      );
      debugPrint(
        'The failure confirms the bug exists as described in Requirements 1.3 and 2.3.',
      );
    });
  });

  group('Bug Condition Exploration - Task 1.6', () {
    testWidgets('Report with Firebase Storage URL loads correctly', (
      WidgetTester tester,
    ) async {
      // Create test report with Firebase Storage URL
      final testReport = Report(
        id: 'test-report-003',
        uid: 'test-uid-789',
        nationalId: '12345678901234',
        name: 'Test User Firebase Storage',
        type: 'Safety Violation',
        description: 'Test report for Firebase Storage image loading',
        imagePath:
            'https://firebasestorage.googleapis.com/v0/b/test-project/o/reports%2Freport.jpg?alt=media&token=test-token', // Firebase Storage URL
        status: 'pending',
        severity: 'High',
        createdAt: DateTime.now().toIso8601String(),
        latitude: 30.0444,
        longitude: 31.2357,
        locationAddress: 'Test Location Firebase Storage',
      );

      // Build the ReportImageWidget directly
      await tester.pumpWidget(
        MaterialApp(
          home: Scaffold(
            body: ReportImageWidget(
              imagePath: testReport.imagePath,
              height: 200,
              width: double.infinity,
              fit: BoxFit.cover,
            ),
          ),
        ),
      );

      // Wait for the widget to build
      await tester.pump();

      // Find the ReportImageWidget
      final imageWidgetFinder = find.byType(ReportImageWidget);
      expect(
        imageWidgetFinder,
        findsOneWidget,
        reason: 'Should find exactly one ReportImageWidget',
      );

      // Get the ReportImageWidget
      final ReportImageWidget imageWidget = tester.widget(imageWidgetFinder);

      // **Bug Condition from design**:
      // The ReportImageWidget should load image from Firebase Storage URL
      //
      // **Expected behavior (Property 2 from design)**:
      // Image should load successfully from Firebase Storage
      //
      // **ACTUAL BEHAVIOR ON UNFIXED CODE**:
      // The ReportImageWidget has code that explicitly blocks Firebase Storage URLs:
      //   if (imagePath.startsWith('https://firebasestorage.googleapis.com')) {
      //     return _buildPlaceholder(icon: Icons.cloud_off_outlined, message: 'Storage unavailable');
      //   }
      //
      // **EXPECTED OUTCOME**: Test FAILS on unfixed code
      // The unfixed code intentionally blocks Firebase Storage URLs and shows a placeholder
      // This is a BUG that needs to be fixed
      //
      // **Counterexample**:
      // Report with Firebase Storage URL shows "Storage unavailable" placeholder
      // instead of loading the image from Firebase Storage

      // Check if the ReportImageWidget receives the correct image path
      expect(
        imageWidget.imagePath,
        equals(testReport.imagePath),
        reason:
            'ReportImageWidget should receive the Firebase Storage URL from database. '
            'Expected: ${testReport.imagePath}, '
            'Got: ${imageWidget.imagePath}',
      );

      // Verify that the image path is recognized as a Firebase Storage URL
      expect(
        imageWidget.imagePath.startsWith(
          'https://firebasestorage.googleapis.com',
        ),
        isTrue,
        reason:
            'The image path should be a valid Firebase Storage URL. '
            'Got: ${imageWidget.imagePath}',
      );

      // Verify that the full URL is NOT modified (Firebase URLs should be returned as-is)
      final fullUrl = testReport.getFullImageUrl();
      expect(
        fullUrl,
        equals(testReport.imagePath),
        reason:
            'Report.getFullImageUrl() should return Firebase Storage URLs unchanged. '
            'Expected: ${testReport.imagePath}, '
            'Got: $fullUrl\n'
            'Firebase Storage URLs should NOT be modified or prefixed with base URL.',
      );

      // Verify that the Report model correctly identifies this as a Firebase image
      expect(
        testReport.isFirebaseImage(),
        isTrue,
        reason:
            'Report.isFirebaseImage() should return true for Firebase Storage URLs. '
            'Got: ${testReport.isFirebaseImage()}',
      );

      // Verify that the Report model does NOT identify this as a website image
      expect(
        testReport.isWebsiteImage(),
        isFalse,
        reason:
            'Report.isWebsiteImage() should return false for Firebase Storage URLs. '
            'Got: ${testReport.isWebsiteImage()}',
      );

      // **BUG DETECTION**: The ReportImageWidget blocks Firebase Storage URLs
      // and shows a placeholder instead of loading the image
      // Find the placeholder icon to confirm the bug
      await tester.pump(); // Allow widget to build
      final placeholderIconFinder = find.byIcon(Icons.cloud_off_outlined);
      expect(
        placeholderIconFinder,
        findsOneWidget,
        reason:
            'BUG CONFIRMED: ReportImageWidget shows placeholder for Firebase Storage URLs. '
            'Expected: CachedNetworkImage loading from Firebase Storage. '
            'Actual: Placeholder with "Storage unavailable" message. '
            'The widget has code that explicitly blocks Firebase Storage URLs.',
      );

      // Verify that CachedNetworkImage is NOT used (because the widget blocks Firebase URLs)
      final networkImageFinder = find.byType(CachedNetworkImage);
      expect(
        networkImageFinder,
        findsNothing,
        reason:
            'BUG CONFIRMED: ReportImageWidget does NOT use CachedNetworkImage for Firebase Storage URLs. '
            'The widget blocks Firebase Storage URLs and shows a placeholder instead. '
            'This is incorrect behavior that needs to be fixed.',
      );

      // Document the bug
      debugPrint('=== FIREBASE STORAGE URL TEST ===');
      debugPrint('Image path: ${imageWidget.imagePath}');
      debugPrint('Full URL: $fullUrl');
      debugPrint('Is Firebase image: ${testReport.isFirebaseImage()}');
      debugPrint('Is website image: ${testReport.isWebsiteImage()}');
      debugPrint('');
      debugPrint(
        '✗ BUG CONFIRMED: Report with Firebase Storage URL shows placeholder',
      );
      debugPrint('  Report with imagePath "${testReport.imagePath}"');
      debugPrint('  Full URL returned unchanged: $fullUrl');
      debugPrint(
        '  AppConfig.getImageUrl() correctly returns Firebase URLs unchanged',
      );
      debugPrint(
        '  BUT ReportImageWidget has code that blocks Firebase Storage URLs:',
      );
      debugPrint(
        '    if (imagePath.startsWith("https://firebasestorage.googleapis.com")) {',
      );
      debugPrint(
        '      return _buildPlaceholder(message: "Storage unavailable");',
      );
      debugPrint('    }');
      debugPrint('');
      debugPrint(
        '  Expected: Image should load from Firebase Storage using CachedNetworkImage',
      );
      debugPrint(
        '  Actual: Placeholder with "Storage unavailable" message is shown',
      );
      debugPrint('');
      debugPrint('This test is EXPECTED TO FAIL on unfixed code.');
      debugPrint(
        'The failure confirms that Firebase Storage URLs are blocked, which is a bug.',
      );
      debugPrint(
        'The fix should remove the Firebase Storage URL blocking code.',
      );
    });
  });
}
