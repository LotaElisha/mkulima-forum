import 'package:flutter/material.dart';
import '../models/product.dart';
import 'payment_screen.dart';

class ProductDetailScreen extends StatelessWidget {
  final Product product;

  const ProductDetailScreen({super.key, required this.product});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(product.name),
        backgroundColor: const Color(0xFF2E7D32),
        foregroundColor: Colors.white,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              height: 250,
              width: double.infinity,
              decoration: BoxDecoration(
                color: Colors.grey[200],
                borderRadius: BorderRadius.circular(16),
              ),
              child: product.images != null && product.images!.isNotEmpty
                  ? ClipRRect(
                      borderRadius: BorderRadius.circular(16),
                      child: Image.network(
                        product.images!.first,
                        fit: BoxFit.cover,
                        width: double.infinity,
                      ),
                    )
                  : const Center(
                      child: Icon(Icons.image, size: 80, color: Colors.grey),
                    ),
            ),
            const SizedBox(height: 20),
            Text(
              product.name,
              style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                    fontWeight: FontWeight.bold,
                  ),
            ),
            const SizedBox(height: 8),
            Text(
              'TSh ${product.price.toStringAsFixed(0)} / ${product.unit}',
              style: TextStyle(
                fontSize: 24,
                fontWeight: FontWeight.bold,
                color: Colors.green[700],
              ),
            ),
            const SizedBox(height: 8),
            Row(
              children: [
                Icon(Icons.inventory_2, size: 16, color: Colors.grey[600]),
                const SizedBox(width: 4),
                Text(
                  'Stock: ${product.stock} ${product.unit}',
                  style: TextStyle(color: Colors.grey[600]),
                ),
                const SizedBox(width: 16),
                Icon(Icons.local_shipping, size: 16, color: Colors.grey[600]),
                const SizedBox(width: 4),
                Text(
                  'Escrow',
                  style: TextStyle(color: Colors.grey[600]),
                ),
              ],
            ),
            const SizedBox(height: 20),
            const Text(
              'Maelezo',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 8),
            Text(
              product.description,
              style: TextStyle(fontSize: 16, color: Colors.grey[700]),
            ),
            const SizedBox(height: 32),
            Row(
              children: [
                Expanded(
                  child: SizedBox(
                    height: 56,
                    child: ElevatedButton.icon(
                      onPressed: () {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(
                            content: Text('Imeongezwa kwenye rukwama'),
                          ),
                        );
                      },
                      icon: const Icon(Icons.shopping_cart),
                      label: const Text('Ongeza Rukwama'),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.white,
                        foregroundColor: const Color(0xFF2E7D32),
                        side: const BorderSide(color: Color(0xFF2E7D32)),
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: SizedBox(
                    height: 56,
                    child: ElevatedButton.icon(
                      onPressed: () {
                        Navigator.of(context).push(
                          MaterialPageRoute(
                            builder: (_) => PaymentScreen(
                              amount: product.price,
                              orderId: product.id,
                            ),
                          ),
                        );
                      },
                      icon: const Icon(Icons.payment),
                      label: const Text('Nunua Sasa'),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF2E7D32),
                        foregroundColor: Colors.white,
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
