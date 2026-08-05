import 'package:flutter/material.dart';

import '../core/strings.dart';
import '../core/theme.dart';
import '../models/product.dart';

/// Marketplace grid tile. Accepts either a [Product] or a raw API map so
/// both online and drift-cached data render identically.
class MkProductTile extends StatelessWidget {
  final dynamic product;
  final VoidCallback onTap;

  const MkProductTile({super.key, required this.product, required this.onTap});

  @override
  Widget build(BuildContext context) {
    final price = product is Product ? product.price : (product['price'] ?? 0);
    final stock = product is Product
        ? product.stock
        : (product['stock_quantity'] ?? product['stock'] ?? 0);
    final imageUrl = product is Product
        ? (product.images?.isNotEmpty == true ? product.images!.first : null)
        : (product['image_url'] ?? product['image']);
    final name =
        product is Product ? product.name : (product['name'] ?? 'Bidhaa');

    return Card(
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(MkRadii.card),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Expanded(
              flex: 3,
              child: ClipRRect(
                borderRadius:
                    const BorderRadius.vertical(top: Radius.circular(MkRadii.card)),
                child: imageUrl != null
                    ? Image.network(
                        imageUrl,
                        fit: BoxFit.cover,
                        width: double.infinity,
                        errorBuilder: (_, __, ___) => const _ImagePlaceholder(),
                      )
                    : const _ImagePlaceholder(),
              ),
            ),
            Expanded(
              flex: 2,
              child: Padding(
                padding: const EdgeInsets.all(12),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      name,
                      style: const TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 14,
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'TZS $price',
                      style: const TextStyle(
                        color: MkColors.primary,
                        fontWeight: FontWeight.bold,
                        fontSize: 16,
                      ),
                    ),
                    const Spacer(),
                    Row(
                      children: [
                        Icon(
                          Icons.inventory_2_outlined,
                          size: 14,
                          color: Colors.grey[600],
                        ),
                        const SizedBox(width: 4),
                        Text(
                          '$stock ${MkStrings.stockLeft}',
                          style: TextStyle(
                            fontSize: 12,
                            color: Colors.grey[600],
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _ImagePlaceholder extends StatelessWidget {
  const _ImagePlaceholder();

  @override
  Widget build(BuildContext context) {
    return Container(
      color: Colors.grey[200],
      child: const Center(
        child: Icon(Icons.image, size: 40, color: Colors.grey),
      ),
    );
  }
}
