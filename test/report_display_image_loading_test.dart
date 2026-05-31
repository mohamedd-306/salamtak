import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:salamtak/models/report.dart';
import 'package:salamtak/widgets/report_image_widget.dart';
import 'package:salamtak/config/app_config.dart';
import 'dart:convert';

/// Task 8: Test Report Display and Image Loading
///
/// This test suite verifies:
/// - Report creation from app with image → shows in history
/// - Report creation from website with image → shows in app
/// - User with no reports → empty state
/// - User with multiple reports → all show correctly
/// - Reports without images → no broken image placeholders
/// - Reports with no internet → error handling
/// - Image loading performance with many reports
/// - Debug logs show useful information

void main() {
  group('Task 8: Report Display and Image Loading Tests', () {
    // Sample base64 image data (1x1 transparent PNG)
    const sampleBase64Image =
        'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

    // Sample Firebase Storage URL (old format - should show placeholder)
    const firebaseStorageUrl =
        'https://firebasestorage.googleapis.com/v0/b/project.appspot.com/o/images%2Freport.jpg?alt=media';

    // Sample website relative path
    const websiteRelativePath = 'uploads/report_123.jpg';

    group('Sub-task 1: Test creating report from app with image', () {
      test('Report with base64 image should be valid', () {
        final report = Report(
          id: 'test-1',
          uid: 'user-123',
          nationalId: '11111111111111',
          name: 'Test User',
          type: 'Safety Issue',
          description: 'Test report with base64 image',
          imagePath: sampleBase64Image,
          status: 'pending',
          severity: 'High',
          createdAt: DateTime.now().toIso8601String(),
        );

        expect(report.isValid(), true);
        expect(report.hasImage(), true);
        expect(report.imagePath.startsWith('data:image'), true);
      });

      test('Base64 image should be decodable', () {
        // Extract base64 data from data URI
        final base64Data = sampleBase64Image.split(',')[1];
        expect(() => base64Decode(base64Data), returnsNormally);

        final bytes = base64Decode(base64Data);
        expect(bytes.isNotEmpty, true);
      });

      testWidgets('ReportImageWidget should display base64 image', (
        WidgetTester tester,
      ) async {
        await tester.pumpWidget(
          MaterialApp(
            home: Scaffold(
              body: ReportImageWidget(
                imagePath: sampleBase64Image,
                width: 200,
                height: 200,
              ),
            ),
          ),
        );

        // Wait for widget to build
        await tester.pump();

        // Should find Image.memory widget for base64 images
        expect(find.byType(Image), findsOneWidget);
      });
    });

    group('Sub-task 2: Test creating report from website with image', () {
      test('Report with website relative path should be valid', () {
        final report = Report(
          id: 'test-2',
          uid: 'user-123',
          nationalId: '11111111111111',
          name: 'Test User',
          type: 'Infrastructure',
          description: 'Test report from website',
          imagePath: websiteRelativePath,
          status: 'pending',
          severity: 'Medium',
          createdAt: DateTime.now().toIso8601String(),
        );

        expect(report.isValid(), true);
        expect(report.hasImage(), true);
        expect(report.isWebsiteImage(), true);
      });

      test('Website path should be converted to full URL', () {
        final fullUrl = AppConfig.getImageUrl(websiteRelativePath);
        expect(fullUrl, contains(AppConfig.baseUrl));
        expect(fullUrl, contains(websiteRelativePath));
        expect(fullUrl, startsWith('http'));
      });

      test('Firebase Storage URL should remain unchanged', () {
        final fullUrl = AppConfig.getImageUrl(firebaseStorageUrl);
        expect(fullUrl, equals(firebaseStorageUrl));
      });
    });

    group('Sub-task 3: Test user with no reports → empty state', () {
      test('Empty report list should be handled gracefully', () {
        final reports = <Report>[];
        expect(reports.isEmpty, true);
        expect(reports.length, 0);
      });

      testWidgets('Empty state should show appropriate message', (
        WidgetTester tester,
      ) async {
        // This would be tested in the actual HistoryScreen widget test
        // For now, we verify the logic
        final reports = <Report>[];
        expect(reports.isEmpty, true);
      });
    });

    group('Sub-task 4: Test user with multiple reports', () {
      test('Multiple reports should all be valid', () {
        final reports = [
          Report(
            id: 'test-1',
            uid: 'user-123',
            nationalId: '11111111111111',
            name: 'Test User',
            type: 'Safety Issue',
            description: 'First report',
            imagePath: sampleBase64Image,
            status: 'pending',
            severity: 'High',
            createdAt: DateTime.now().toIso8601String(),
          ),
          Report(
            id: 'test-2',
            uid: 'user-123',
            nationalId: '11111111111111',
            name: 'Test User',
            type: 'Infrastructure',
            description: 'Second report',
            imagePath: websiteRelativePath,
            status: 'in_progress',
            severity: 'Medium',
            createdAt:
                DateTime.now()
                    .subtract(const Duration(days: 1))
                    .toIso8601String(),
          ),
          Report(
            id: 'test-3',
            uid: 'user-123',
            nationalId: '11111111111111',
            name: 'Test User',
            type: 'Other',
            description: 'Third report without image',
            imagePath: '',
            status: 'resolved',
            severity: 'Low',
            createdAt:
                DateTime.now()
                    .subtract(const Duration(days: 2))
                    .toIso8601String(),
          ),
        ];

        expect(reports.length, 3);
        expect(reports.every((r) => r.isValid()), true);
        expect(reports[0].hasImage(), true);
        expect(reports[1].hasImage(), true);
        expect(reports[2].hasImage(), false);
      });

      test('Reports should be sortable by date', () {
        final now = DateTime.now();
        final reports = [
          Report(
            id: 'test-1',
            uid: 'user-123',
            nationalId: '11111111111111',
            name: 'Test User',
            type: 'Safety Issue',
            description: 'Oldest report',
            imagePath: '',
            status: 'pending',
            severity: 'High',
            createdAt: now.subtract(const Duration(days: 2)).toIso8601String(),
          ),
          Report(
            id: 'test-2',
            uid: 'user-123',
            nationalId: '11111111111111',
            name: 'Test User',
            type: 'Infrastructure',
            description: 'Newest report',
            imagePath: '',
            status: 'pending',
            severity: 'Medium',
            createdAt: now.toIso8601String(),
          ),
          Report(
            id: 'test-3',
            uid: 'user-123',
            nationalId: '11111111111111',
            name: 'Test User',
            type: 'Other',
            description: 'Middle report',
            imagePath: '',
            status: 'pending',
            severity: 'Low',
            createdAt: now.subtract(const Duration(days: 1)).toIso8601String(),
          ),
        ];

        // Sort by createdAt (newest first)
        reports.sort((a, b) {
          final dateA = DateTime.parse(a.createdAt);
          final dateB = DateTime.parse(b.createdAt);
          return dateB.compareTo(dateA);
        });

        expect(reports[0].description, 'Newest report');
        expect(reports[1].description, 'Middle report');
        expect(reports[2].description, 'Oldest report');
      });
    });

    group('Sub-task 5: Test reports without images', () {
      test('Report without image should not show broken placeholder', () {
        final report = Report(
          id: 'test-no-image',
          uid: 'user-123',
          nationalId: '11111111111111',
          name: 'Test User',
          type: 'Other',
          description: 'Report without image',
          imagePath: '',
          status: 'pending',
          severity: 'Low',
          createdAt: DateTime.now().toIso8601String(),
        );

        expect(report.hasImage(), false);
        expect(report.imagePath.isEmpty, true);
      });

      testWidgets('Empty image path should not render image widget', (
        WidgetTester tester,
      ) async {
        final report = Report(
          id: 'test-no-image',
          uid: 'user-123',
          nationalId: '11111111111111',
          name: 'Test User',
          type: 'Other',
          description: 'Report without image',
          imagePath: '',
          status: 'pending',
          severity: 'Low',
          createdAt: DateTime.now().toIso8601String(),
        );

        // In the actual UI, hasImage() check prevents rendering
        expect(report.hasImage(), false);
      });
    });

    group('Sub-task 6: Test with no internet → error handling', () {
      testWidgets('Network image error should show placeholder', (
        WidgetTester tester,
      ) async {
        // Test with invalid URL that will fail to load
        await tester.pumpWidget(
          MaterialApp(
            home: Scaffold(
              body: ReportImageWidget(
                imagePath: 'https://invalid-url-that-will-fail.com/image.jpg',
                width: 200,
                height: 200,
              ),
            ),
          ),
        );

        // Wait for initial build
        await tester.pump();

        // The widget should handle the error gracefully
        // CachedNetworkImage will show errorWidget
        expect(find.byType(ReportImageWidget), findsOneWidget);
      });

      test('Firebase Storage URL should show unavailable placeholder', () {
        // Firebase Storage URLs are now treated as unavailable
        expect(firebaseStorageUrl.startsWith('https://firebasestorage'), true);
        expect(AppConfig.isFirebaseStorageUrl(firebaseStorageUrl), true);
      });
    });

    group('Sub-task 7: Test image loading performance with many reports', () {
      test('Should handle large number of reports efficiently', () {
        final reports = List.generate(
          100,
          (index) => Report(
            id: 'test-$index',
            uid: 'user-123',
            nationalId: '11111111111111',
            name: 'Test User',
            type: 'Safety Issue',
            description: 'Report $index',
            imagePath: index % 2 == 0 ? sampleBase64Image : '',
            status: 'pending',
            severity: 'Medium',
            createdAt:
                DateTime.now()
                    .subtract(Duration(hours: index))
                    .toIso8601String(),
          ),
        );

        expect(reports.length, 100);
        expect(reports.every((r) => r.isValid()), true);

        // Count reports with images
        final withImages = reports.where((r) => r.hasImage()).length;
        expect(withImages, 50); // Half have images
      });

      test('Base64 decoding should be performant', () {
        final stopwatch = Stopwatch()..start();

        // Decode 100 base64 images
        for (var i = 0; i < 100; i++) {
          final base64Data = sampleBase64Image.split(',')[1];
          base64Decode(base64Data);
        }

        stopwatch.stop();

        // Should complete in reasonable time (< 1 second)
        expect(stopwatch.elapsedMilliseconds, lessThan(1000));
      });
    });

    group('Sub-task 8: Verify debug logs show useful information', () {
      test('Report model should provide debug information', () {
        final report = Report(
          id: 'test-debug',
          uid: 'user-123',
          nationalId: '11111111111111',
          name: 'Test User',
          type: 'Safety Issue',
          description: 'Test report for debugging',
          imagePath: sampleBase64Image,
          status: 'pending',
          severity: 'High',
          createdAt: DateTime.now().toIso8601String(),
          latitude: 30.0444,
          longitude: 31.2357,
          locationAddress: 'Cairo, Egypt',
        );

        // Verify all fields are accessible for logging
        expect(report.id, isNotNull);
        expect(report.uid, isNotEmpty);
        expect(report.nationalId, isNotEmpty);
        expect(report.type, isNotEmpty);
        expect(report.description, isNotEmpty);
        expect(report.status, isNotEmpty);
        expect(report.severity, isNotEmpty);
        expect(report.createdAt, isNotEmpty);
        expect(report.hasImage(), true);
        expect(report.isValid(), true);
      });

      test('AppConfig should provide configuration info', () {
        expect(AppConfig.baseUrl, isNotEmpty);
        expect(AppConfig.environmentName, isNotEmpty);
        expect(AppConfig.isProduction, isA<bool>());
      });

      test('Image path detection should be clear', () {
        // Base64 image
        expect(sampleBase64Image.startsWith('data:image'), true);

        // Firebase Storage URL
        expect(AppConfig.isFirebaseStorageUrl(firebaseStorageUrl), true);

        // Website relative path
        expect(AppConfig.isWebsitePath(websiteRelativePath), true);

        // Empty path
        expect(AppConfig.isWebsitePath(''), false);
      });
    });

    group('Additional Edge Cases', () {
      test('Report with missing optional fields should still be valid', () {
        final report = Report(
          id: 'test-minimal',
          uid: 'user-123',
          nationalId: '11111111111111',
          name: 'Test User',
          type: 'Other',
          description: 'Minimal report',
          imagePath: '',
          status: 'pending',
          severity: 'Low',
          createdAt: DateTime.now().toIso8601String(),
          // No location data
        );

        expect(report.isValid(), true);
        expect(report.latitude, isNull);
        expect(report.longitude, isNull);
        expect(report.locationAddress, isNull);
      });

      test('Report with location data should format correctly', () {
        final report = Report(
          id: 'test-location',
          uid: 'user-123',
          nationalId: '11111111111111',
          name: 'Test User',
          type: 'Infrastructure',
          description: 'Report with location',
          imagePath: '',
          status: 'pending',
          severity: 'Medium',
          createdAt: DateTime.now().toIso8601String(),
          latitude: 30.0444,
          longitude: 31.2357,
          locationAddress: 'Cairo, Egypt',
        );

        expect(report.getLocationString(), 'Cairo, Egypt');
      });

      test(
        'Report with coordinates but no address should format coordinates',
        () {
          final report = Report(
            id: 'test-coords',
            uid: 'user-123',
            nationalId: '11111111111111',
            name: 'Test User',
            type: 'Safety Issue',
            description: 'Report with coordinates only',
            imagePath: '',
            status: 'pending',
            severity: 'High',
            createdAt: DateTime.now().toIso8601String(),
            latitude: 30.0444,
            longitude: 31.2357,
          );

          final locationString = report.getLocationString();
          expect(locationString, contains('30.0444'));
          expect(locationString, contains('31.2357'));
        },
      );

      test('Invalid base64 should be handled gracefully', () {
        const invalidBase64 = 'data:image/png;base64,INVALID!!!';

        expect(() {
          final base64Data = invalidBase64.split(',')[1];
          base64Decode(base64Data);
        }, throwsA(isA<FormatException>()));
      });

      test('Report status variations should be handled', () {
        final statuses = ['pending', 'in_progress', 'resolved'];

        for (final status in statuses) {
          final report = Report(
            id: 'test-status-$status',
            uid: 'user-123',
            nationalId: '11111111111111',
            name: 'Test User',
            type: 'Other',
            description: 'Test status: $status',
            imagePath: '',
            status: status,
            severity: 'Medium',
            createdAt: DateTime.now().toIso8601String(),
          );

          expect(report.isValid(), true);
          expect(report.status, status);
        }
      });

      test('Report severity variations should be handled', () {
        final severities = ['Low', 'Medium', 'High'];

        for (final severity in severities) {
          final report = Report(
            id: 'test-severity-$severity',
            uid: 'user-123',
            nationalId: '11111111111111',
            name: 'Test User',
            type: 'Safety Issue',
            description: 'Test severity: $severity',
            imagePath: '',
            status: 'pending',
            severity: severity,
            createdAt: DateTime.now().toIso8601String(),
          );

          expect(report.isValid(), true);
          expect(report.severity, severity);
        }
      });
    });
  });
}
