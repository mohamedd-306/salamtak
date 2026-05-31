import 'package:firebase_auth/firebase_auth.dart';
import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:firebase_storage/firebase_storage.dart';
import 'package:image_picker/image_picker.dart';
import 'dart:io';
import 'dart:convert';
import 'package:image/image.dart' as img;
import '../models/user.dart' as app_user;
import '../models/report.dart';
import '../models/review.dart';

class DatabaseService {
  static final DatabaseService instance = DatabaseService._init();
  DatabaseService._init();

  final FirebaseAuth _auth = FirebaseAuth.instance;
  final FirebaseFirestore _db = FirebaseFirestore.instance;
  final FirebaseStorage _storage = FirebaseStorage.instance;

  // Current logged-in Firebase user
  User? get currentFirebaseUser => _auth.currentUser;

  /// Login with National ID or Work ID + password
  /// Uses nationalId@salamtak.com as the Firebase Auth email
  Future<app_user.User?> login(String nationalId, String password) async {
    print('=== LOGIN ATTEMPT ===');
    print('ID: $nationalId');

    // HARDCODED ADMIN - Work ID 221007689
    if (nationalId == '221007689' && password == '631663') {
      print('✓ Admin login successful (Work ID)');
      return app_user.User(
        id: 'admin-221007689',
        nationalId: '221007689',
        phoneNumber: '01000000000',
        name: 'Administrator',
        userType: 'admin',
      );
    }

    // LEGACY ADMIN - Keep for backward compatibility
    if (nationalId == '12345678901234' && password == 'admin123456') {
      print('✓ Admin login successful (legacy)');
      return app_user.User(
        id: 'admin-hardcoded',
        nationalId: '12345678901234',
        phoneNumber: '01000000000',
        name: 'System Administrator',
        userType: 'admin',
      );
    }

    // HARDCODED TEST USER - Bypass Firebase Auth completely
    if (nationalId == '11111111111111' && password == 'user123456') {
      print('✓ Test user login successful (hardcoded bypass)');
      return app_user.User(
        id: 'user-hardcoded',
        nationalId: '11111111111111',
        phoneNumber: '01111111111',
        name: 'Test User',
        userType: 'user',
      );
    }

    // Regular Firebase Auth login for other users
    final fakeEmail = '$nationalId@salamtak.com';
    print('Email: $fakeEmail');
    try {
      final cred = await _auth.signInWithEmailAndPassword(
        email: fakeEmail,
        password: password,
      );
      print('✓ Firebase Auth successful');
      final uid = cred.user!.uid;
      print('UID: $uid');
      final doc = await _db.collection('users').doc(uid).get();
      if (!doc.exists) {
        print('❌ User document not found in Firestore');
        return null;
      }
      final data = doc.data()!;
      print('✓ User data: $data');
      final user = app_user.User(
        id: uid,
        nationalId: data['nationalId'] ?? nationalId,
        phoneNumber: data['phone'] ?? '',
        name: data['name'] ?? '',
        userType: data['userType'] ?? 'user',
      );
      print('✓ Login successful as ${user.userType}');
      return user;
    } catch (e) {
      print('❌ Login failed: $e');
      return null;
    }
  }

  /// Sign up with National ID + password — also saves profile to Firestore
  Future<app_user.User?> signUp({
    required String nationalId,
    required String name,
    required String address,
    required String email,
    required String phone,
    required String password,
  }) async {
    final fakeEmail = '$nationalId@salamtak.com';
    print('=== SIGNUP ATTEMPT ===');
    print('National ID: $nationalId');
    print('Name: $name');
    print('Email: $fakeEmail');

    try {
      print('Creating Firebase Auth account...');
      final cred = await _auth.createUserWithEmailAndPassword(
        email: fakeEmail,
        password: password,
      );
      final uid = cred.user!.uid;
      print('✓ Firebase Auth account created with UID: $uid');

      print('Creating Firestore profile...');
      await _db.collection('users').doc(uid).set({
        'nationalId': nationalId,
        'name': name,
        'address': address,
        'email': email,
        'phone': phone,
        'userType': 'user',
        'createdAt': FieldValue.serverTimestamp(),
      });
      print('✓ Firestore profile created');
      print('✓ Signup successful!');

      return app_user.User(
        id: uid,
        nationalId: nationalId,
        phoneNumber: phone,
        name: name,
        userType: 'user',
      );
    } catch (e) {
      print('❌ Signup failed: $e');
      return null;
    }
  }

  /// Submit a report — saved to Firestore "reports" collection
  Future<String?> createReport(Report report) async {
    print('=== CREATING REPORT ===');
    print('Report UID: ${report.uid}');
    print('National ID: ${report.nationalId}');
    print('Name: ${report.name}');
    print('Type: ${report.type}');
    print('Description: ${report.description}');
    print('Image Path Length: ${report.imagePath.length}');
    print('Has Image: ${report.imagePath.isNotEmpty}');
    print('Is Base64: ${report.imagePath.startsWith('data:image')}');
    print('Status: ${report.status}');
    print('Severity: ${report.severity}');

    try {
      // Use the UID from the report object (which comes from SharedPreferences)
      // This works for both Firebase Auth users and hardcoded users
      final uid =
          report.uid.isNotEmpty ? report.uid : (currentFirebaseUser?.uid ?? '');

      print('Using UID: $uid');

      // Get current timestamp as string for consistency with website
      final now = DateTime.now().toIso8601String();

      final reportData = {
        'uid': uid,
        'nationalId': report.nationalId,
        'name': report.name,
        'type': report.type,
        'description': report.description,
        'imagePath': report.imagePath,
        'status': 'pending',
        'severity': report.severity,
        'location': report.locationAddress ?? '',
        'latitude': report.latitude,
        'longitude': report.longitude,
        'createdAt': now, // String format for website compatibility
        'updatedAt': now, // Add updatedAt field
      };

      print('Report data to save:');
      print('  - uid: ${reportData['uid']}');
      print('  - nationalId: ${reportData['nationalId']}');
      print('  - type: ${reportData['type']}');
      print(
        '  - imagePath length: ${(reportData['imagePath'] as String).length}',
      );
      print(
        '  - imagePath has content: ${(reportData['imagePath'] as String).isNotEmpty}',
      );

      final docRef = await _db.collection('reports').add(reportData);

      print('✓ Report created with ID: ${docRef.id}');
      print(
        '✓ Image was ${report.imagePath.isNotEmpty ? "INCLUDED" : "NOT INCLUDED"}',
      );

      return docRef.id;
    } catch (e, stackTrace) {
      print('❌ Error creating report: $e');
      print('Stack trace: $stackTrace');
      return null;
    }
  }

  /// Get reports for the current logged-in user (real-time stream)
  /// Query by nationalId to match website reports
  Stream<List<Report>> getUserReportsStream(String uid) {
    print('=== FETCHING USER REPORTS ===');
    print('UID: $uid');

    // Remove orderBy to avoid Firestore index requirement
    // Sort in memory instead
    return _db
        .collection('reports')
        .where('uid', isEqualTo: uid)
        .snapshots()
        .map((snap) {
          print('Found ${snap.docs.length} reports for UID: $uid');
          final reports =
              snap.docs.map((d) => Report.fromFirestore(d)).toList();

          // Sort by createdAt in memory (newest first)
          reports.sort((a, b) {
            try {
              final dateA = DateTime.parse(a.createdAt);
              final dateB = DateTime.parse(b.createdAt);
              return dateB.compareTo(dateA); // Descending order
            } catch (e) {
              print('Error parsing date for sorting: $e');
              return 0; // Keep original order if parsing fails
            }
          });

          return reports;
        });
  }

  /// Get reports by national ID (for website compatibility)
  /// This method now includes fallback logic for better reliability
  Stream<List<Report>> getUserReportsByNationalId(String nationalId) {
    print('=== FETCHING REPORTS BY NATIONAL ID ===');
    print('National ID: $nationalId');

    // Remove orderBy to avoid Firestore index requirement
    // Sort in memory instead
    return _db
        .collection('reports')
        .where('nationalId', isEqualTo: nationalId)
        .snapshots()
        .handleError((error) {
          print('❌ Error fetching reports by nationalId: $error');
          // Return empty stream on error
          return const Stream.empty();
        })
        .map((snap) {
          print(
            'Found ${snap.docs.length} reports for National ID: $nationalId',
          );

          if (snap.docs.isEmpty) {
            print('⚠️ No reports found for nationalId: $nationalId');
            print('   This could mean:');
            print('   1. User has no reports yet');
            print(
              '   2. Reports were created with different nationalId format',
            );
            print('   3. Reports only have uid field (app-created reports)');
          }

          final reports =
              snap.docs
                  .map((d) {
                    try {
                      return Report.fromFirestore(d);
                    } catch (e) {
                      print('❌ Error parsing report ${d.id}: $e');
                      return null;
                    }
                  })
                  .whereType<Report>()
                  .toList(); // Filter out nulls

          // Sort by createdAt in memory (newest first)
          reports.sort((a, b) {
            try {
              final dateA = DateTime.parse(a.createdAt);
              final dateB = DateTime.parse(b.createdAt);
              return dateB.compareTo(dateA); // Descending order
            } catch (e) {
              print('Error parsing date for sorting: $e');
              return 0; // Keep original order if parsing fails
            }
          });

          return reports;
        });
  }

  /// Get all reports (admin) — real-time stream
  /// Removed orderBy to avoid index requirement, sorts in memory
  Stream<List<Report>> getAllReportsStream() {
    print('=== FETCHING ALL REPORTS (ADMIN) ===');

    return _db
        .collection('reports')
        .snapshots()
        .handleError((error) {
          print('❌ Error fetching all reports: $error');
          return const Stream.empty();
        })
        .map((snap) {
          print('Found ${snap.docs.length} total reports');

          final reports =
              snap.docs
                  .map((d) {
                    try {
                      return Report.fromFirestore(d);
                    } catch (e) {
                      print('❌ Error parsing report ${d.id}: $e');
                      return null;
                    }
                  })
                  .whereType<Report>()
                  .toList(); // Filter out nulls

          // Sort by createdAt in memory (newest first)
          reports.sort((a, b) {
            try {
              final dateA = DateTime.parse(a.createdAt);
              final dateB = DateTime.parse(b.createdAt);
              return dateB.compareTo(dateA); // Descending order
            } catch (e) {
              print('Error parsing date for sorting: $e');
              return 0; // Keep original order if parsing fails
            }
          });

          return reports;
        });
  }

  /// Update report status (admin)
  Future<void> updateReportStatus(String reportId, String status) async {
    await _db.collection('reports').doc(reportId).update({'status': status});
  }

  /// Upload report image to Firebase Storage
  /// Now stores as base64 in Firestore instead of Firebase Storage
  /// Includes automatic compression and format detection
  Future<String?> uploadReportImage(XFile imageFile) async {
    try {
      print('=== CONVERTING IMAGE TO BASE64 ===');
      print('Image file path: ${imageFile.path}');

      final file = File(imageFile.path);

      // Check if file exists
      if (!await file.exists()) {
        print('❌ File does not exist at path: ${imageFile.path}');
        return null;
      }

      // Get file size
      final fileSize = await file.length();
      print(
        'Original file size: $fileSize bytes (${(fileSize / 1024).toStringAsFixed(2)} KB)',
      );

      // Read file as bytes
      final bytes = await file.readAsBytes();
      print('✓ File read successfully: ${bytes.length} bytes');

      // Detect image format from file extension
      String imageFormat = 'jpeg'; // default
      String mimeType = 'image/jpeg';

      final extension = imageFile.path.toLowerCase().split('.').last;
      if (extension == 'png') {
        imageFormat = 'png';
        mimeType = 'image/png';
      } else if (extension == 'gif') {
        imageFormat = 'gif';
        mimeType = 'image/gif';
      } else if (extension == 'webp') {
        imageFormat = 'webp';
        mimeType = 'image/webp';
      }

      print('Detected format: $imageFormat');

      // Decode image for compression
      final image = img.decodeImage(bytes);
      if (image == null) {
        print('❌ Failed to decode image');
        return null;
      }

      print('Original dimensions: ${image.width}x${image.height}');

      // Compress image if needed
      List<int> compressedBytes;

      // Resize if image is too large (max width 1200px)
      if (image.width > 1200 || image.height > 1200) {
        print('Resizing image to fit within 1200px...');
        final resized = img.copyResize(
          image,
          width: image.width > image.height ? 1200 : null,
          height: image.height > image.width ? 1200 : null,
        );
        print('Resized dimensions: ${resized.width}x${resized.height}');

        // Encode with compression
        if (imageFormat == 'png') {
          compressedBytes = img.encodePng(resized, level: 6);
        } else {
          // Use JPEG for other formats (better compression)
          compressedBytes = img.encodeJpg(resized, quality: 85);
          mimeType = 'image/jpeg'; // Force JPEG for compressed images
        }
      } else {
        // Image is small enough, just compress
        print('Image size acceptable, applying compression...');
        if (imageFormat == 'png') {
          compressedBytes = img.encodePng(image, level: 6);
        } else {
          compressedBytes = img.encodeJpg(image, quality: 85);
          mimeType = 'image/jpeg';
        }
      }

      final compressedSize = compressedBytes.length;
      print(
        'Compressed size: $compressedSize bytes (${(compressedSize / 1024).toStringAsFixed(2)} KB)',
      );
      print(
        'Compression ratio: ${((1 - compressedSize / fileSize) * 100).toStringAsFixed(1)}%',
      );

      // Convert to base64
      final base64String = base64Encode(compressedBytes);
      print('✓ Base64 encoded: ${base64String.length} characters');

      // Return base64 string with data URI prefix
      final base64Image = 'data:$mimeType;base64,$base64String';

      // Check if base64 string is too large (Firestore has 1MB document limit)
      final base64Size = base64Image.length;
      print(
        'Final base64 size: $base64Size characters (${(base64Size / 1024).toStringAsFixed(2)} KB)',
      );

      if (base64Size > 900000) {
        // ~900KB to leave room for other fields
        print(
          '⚠️ WARNING: Base64 string is very large (${(base64Size / 1024).toStringAsFixed(2)} KB)',
        );
        print('   This may cause Firestore save to fail (1MB document limit)');
        print('   Consider using a smaller image or lower quality setting');
      }

      print('✓ Image converted to base64 successfully');
      return base64Image;
    } catch (e, stackTrace) {
      print('❌ Error converting image to base64: $e');
      print('Stack trace: $stackTrace');
      return null;
    }
  }

  /// Create a product review
  Future<String?> createReview(Review review) async {
    print('=== CREATING REVIEW ===');
    print('Product ID: ${review.productId}');
    print('User ID: ${review.userId}');
    print('Rating: ${review.rating}');

    try {
      final docRef = await _db.collection('reviews').add({
        'productId': review.productId,
        'userId': review.userId,
        'userName': review.userName,
        'rating': review.rating,
        'comment': review.comment,
        'createdAt': review.createdAt,
      });

      print('✓ Review created with ID: ${docRef.id}');
      return docRef.id;
    } catch (e) {
      print('❌ Error creating review: $e');
      return null;
    }
  }

  /// Get reviews for a specific product
  Stream<List<Review>> getProductReviewsStream(String productId) {
    return _db
        .collection('reviews')
        .where('productId', isEqualTo: productId)
        .orderBy('createdAt', descending: true)
        .snapshots()
        .map((snap) => snap.docs.map((d) => Review.fromFirestore(d)).toList());
  }

  /// Get all reviews for a product (one-time fetch)
  Future<List<Review>> getProductReviews(String productId) async {
    try {
      final snapshot =
          await _db
              .collection('reviews')
              .where('productId', isEqualTo: productId)
              .orderBy('createdAt', descending: true)
              .get();

      return snapshot.docs.map((d) => Review.fromFirestore(d)).toList();
    } catch (e) {
      print('❌ Error fetching reviews: $e');
      return [];
    }
  }

  /// Sign out
  Future<void> signOut() async {
    await _auth.signOut();
  }
}
