import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:firebase_core/firebase_core.dart';

/// Helper script to list all products in Firestore database
/// This helps diagnose image path issues
///
/// Run with: flutter run scripts/list_products.dart
void main() async {
  print('=== FIRESTORE PRODUCTS DIAGNOSTIC ===\n');

  try {
    // Initialize Firebase
    await Firebase.initializeApp(
      options: const FirebaseOptions(
        apiKey: 'AIzaSyDY9lX8swlfKx3umnW57O5DA2Ka1Pdc0Fk',
        authDomain: 'salmtak-6fffe.firebaseapp.com',
        projectId: 'salmtak-6fffe',
        storageBucket: 'salmtak-6fffe.firebasestorage.app',
        messagingSenderId: '1048763383483',
        appId: '1:1048763383483:web:f9a6140078484b5552f39e',
      ),
    );
    print('✓ Firebase initialized\n');

    // Get all products from Firestore
    final productsSnapshot =
        await FirebaseFirestore.instance.collection('products').get();

    if (productsSnapshot.docs.isEmpty) {
      print('⚠️  No products found in Firestore database');
      print('   You need to add products to the "products" collection\n');
      return;
    }

    print('Found ${productsSnapshot.docs.length} products:\n');
    print(
      '${'#'.padRight(4)} ${'Product Name'.padRight(30)} ${'Image Path'.padRight(40)} Status',
    );
    print('${'─' * 4} ${'─' * 30} ${'─' * 40} ${'─' * 20}');

    int index = 1;
    final availableAssets = [
      'boots.jpeg',
      'earmuffs.jpeg',
      'hardhat.jpeg',
      'helmet.jpeg',
      'jacket.jpeg',
      'vest.jpeg',
    ];

    for (var doc in productsSnapshot.docs) {
      final data = doc.data();
      final name = data['name'] ?? 'N/A';
      final imagePath = data['image'] ?? '';

      // Check if image exists in assets
      String status;
      String normalizedPath = imagePath;

      if (imagePath.isEmpty) {
        status = '❌ EMPTY';
      } else if (imagePath.startsWith('http://') ||
          imagePath.startsWith('https://')) {
        status = '🌐 NETWORK URL';
      } else {
        // Normalize path for checking
        if (imagePath.startsWith('assets/products/')) {
          normalizedPath = imagePath.replaceFirst('assets/products/', '');
        } else if (imagePath.startsWith('assets/')) {
          normalizedPath = imagePath.replaceFirst('assets/', '');
        }

        if (availableAssets.contains(normalizedPath)) {
          status = '✓ EXISTS';
        } else {
          status = '❌ NOT FOUND';
        }
      }

      print(
        '${index.toString().padRight(4)} ${name.padRight(30)} ${imagePath.padRight(40)} $status',
      );
      index++;
    }

    print('\n=== AVAILABLE ASSETS ===');
    print('Files in assets/products/:');
    for (var asset in availableAssets) {
      print('  • $asset');
    }

    print('\n=== RECOMMENDATIONS ===');
    print('For products with "❌ NOT FOUND" status:');
    print(
      '  Option 1: Update Firestore image field to match an existing asset',
    );
    print('  Option 2: Add the missing image file to assets/products/');
    print('\nFor products with "❌ EMPTY" status:');
    print('  Update Firestore to add an image path');
    print('\nFor products with "🌐 NETWORK URL" status:');
    print('  These should work if the URL is valid and accessible');
  } catch (e) {
    print('❌ Error: $e');
    print('\nMake sure:');
    print('  1. Firebase is properly configured');
    print('  2. You have internet connection');
    print('  3. Firestore rules allow read access');
  }

  print('\n=== END DIAGNOSTIC ===');
}
