import 'package:flutter/material.dart';
import 'package:cloud_firestore/cloud_firestore.dart';
import '../../theme.dart';

/// Diagnostic screen to view all products and their image paths
/// This helps identify which products have incorrect image paths
class ProductsDiagnosticScreen extends StatelessWidget {
  const ProductsDiagnosticScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Products Diagnostic'),
        backgroundColor: AppTheme.primary,
      ),
      body: StreamBuilder<QuerySnapshot>(
        stream: FirebaseFirestore.instance.collection('products').snapshots(),
        builder: (context, snapshot) {
          if (snapshot.hasError) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(Icons.error, size: 64, color: Colors.red),
                  const SizedBox(height: 16),
                  Text('Error: ${snapshot.error}'),
                ],
              ),
            );
          }

          if (snapshot.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          }

          if (!snapshot.hasData || snapshot.data!.docs.isEmpty) {
            return const Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(
                    Icons.inventory_2_outlined,
                    size: 64,
                    color: Colors.grey,
                  ),
                  SizedBox(height: 16),
                  Text(
                    'No products found in database',
                    style: TextStyle(fontSize: 18, color: Colors.grey),
                  ),
                ],
              ),
            );
          }

          final products = snapshot.data!.docs;
          final availableAssets = [
            'boots.jpeg',
            'earmuffs.jpeg',
            'hardhat.jpeg',
            'helmet.jpeg',
            'jacket.jpeg',
            'vest.jpeg',
          ];

          return Column(
            children: [
              // Header with info
              Container(
                padding: const EdgeInsets.all(16),
                color: Colors.blue[50],
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Found ${products.length} products',
                      style: const TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 8),
                    const Text(
                      'Available assets in assets/products/:',
                      style: TextStyle(fontWeight: FontWeight.bold),
                    ),
                    const SizedBox(height: 4),
                    Wrap(
                      spacing: 8,
                      children:
                          availableAssets
                              .map(
                                (asset) => Chip(
                                  label: Text(asset),
                                  backgroundColor: Colors.green[100],
                                ),
                              )
                              .toList(),
                    ),
                  ],
                ),
              ),

              // Products list
              Expanded(
                child: ListView.builder(
                  itemCount: products.length,
                  itemBuilder: (context, index) {
                    final doc = products[index];
                    final data = doc.data() as Map<String, dynamic>;
                    final name = data['name'] ?? 'N/A';
                    final imagePath = data['image'] ?? '';
                    final price = data['price'] ?? 0;
                    final stock = data['stock'] ?? 0;

                    // Determine status
                    String status;
                    Color statusColor;
                    IconData statusIcon;

                    if (imagePath.isEmpty) {
                      status = 'EMPTY';
                      statusColor = Colors.orange;
                      statusIcon = Icons.warning;
                    } else if (imagePath.startsWith('http://') ||
                        imagePath.startsWith('https://')) {
                      status = 'NETWORK URL';
                      statusColor = Colors.blue;
                      statusIcon = Icons.cloud;
                    } else {
                      // Check if asset exists
                      String normalizedPath = imagePath;
                      if (imagePath.startsWith('assets/products/')) {
                        normalizedPath = imagePath.replaceFirst(
                          'assets/products/',
                          '',
                        );
                      } else if (imagePath.startsWith('assets/')) {
                        normalizedPath = imagePath.replaceFirst('assets/', '');
                      }

                      if (availableAssets.contains(normalizedPath)) {
                        status = 'EXISTS';
                        statusColor = Colors.green;
                        statusIcon = Icons.check_circle;
                      } else {
                        status = 'NOT FOUND';
                        statusColor = Colors.red;
                        statusIcon = Icons.error;
                      }
                    }

                    return Card(
                      margin: const EdgeInsets.symmetric(
                        horizontal: 16,
                        vertical: 8,
                      ),
                      child: ListTile(
                        leading: Icon(statusIcon, color: statusColor, size: 32),
                        title: Text(
                          name,
                          style: const TextStyle(fontWeight: FontWeight.bold),
                        ),
                        subtitle: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const SizedBox(height: 4),
                            Text('Image: $imagePath'),
                            Text('Price: EGP $price | Stock: $stock'),
                            const SizedBox(height: 4),
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 8,
                                vertical: 4,
                              ),
                              decoration: BoxDecoration(
                                color: statusColor.withOpacity(0.2),
                                borderRadius: BorderRadius.circular(4),
                              ),
                              child: Text(
                                status,
                                style: TextStyle(
                                  color: statusColor,
                                  fontWeight: FontWeight.bold,
                                  fontSize: 12,
                                ),
                              ),
                            ),
                          ],
                        ),
                        trailing: IconButton(
                          icon: const Icon(Icons.edit),
                          onPressed: () {
                            _showEditDialog(context, doc.id, name, imagePath);
                          },
                        ),
                      ),
                    );
                  },
                ),
              ),
            ],
          );
        },
      ),
    );
  }

  void _showEditDialog(
    BuildContext context,
    String docId,
    String productName,
    String currentImagePath,
  ) {
    final controller = TextEditingController(text: currentImagePath);

    showDialog(
      context: context,
      builder:
          (context) => AlertDialog(
            title: Text('Edit Image Path: $productName'),
            content: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Available options:',
                  style: TextStyle(fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 8),
                const Text('• boots.jpeg'),
                const Text('• earmuffs.jpeg'),
                const Text('• hardhat.jpeg'),
                const Text('• helmet.jpeg'),
                const Text('• jacket.jpeg'),
                const Text('• vest.jpeg'),
                const SizedBox(height: 16),
                TextField(
                  controller: controller,
                  decoration: const InputDecoration(
                    labelText: 'Image Path',
                    hintText: 'e.g., vest.jpeg or assets/products/vest.jpeg',
                    border: OutlineInputBorder(),
                  ),
                ),
              ],
            ),
            actions: [
              TextButton(
                onPressed: () => Navigator.pop(context),
                child: const Text('Cancel'),
              ),
              ElevatedButton(
                onPressed: () async {
                  try {
                    await FirebaseFirestore.instance
                        .collection('products')
                        .doc(docId)
                        .update({'image': controller.text});

                    if (context.mounted) {
                      Navigator.pop(context);
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(
                          content: Text('Image path updated successfully'),
                          backgroundColor: Colors.green,
                        ),
                      );
                    }
                  } catch (e) {
                    if (context.mounted) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(
                          content: Text('Error: $e'),
                          backgroundColor: Colors.red,
                        ),
                      );
                    }
                  }
                },
                child: const Text('Update'),
              ),
            ],
          ),
    );
  }
}
