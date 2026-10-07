import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';

import '../core/strings.dart';
import '../core/theme.dart';
import '../models/product.dart';
import '../core/format.dart';

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
    final name = product is Product
        ? product.name
        : (product['name'] ?? 'Bidhaa');

    final parsedPrice = price is num
        ? price.toDouble()
        : double.tryParse(price.toString()) ?? 0;

    final stockCount = stock is num
        ? stock.toInt()
        : int.tryParse('$stock') ?? 0;
    final (stockLabel, stockColour) = stockCount <= 0
        ? ('Imeisha', MkColors.danger)
        : stockCount <= 5
        ? ('Zimebaki $stockCount tu', MkColors.warning)
        : ('$stockCount ${MkStrings.stockLeft}', MkColors.muted);

    return Card(
      clipBehavior: Clip.antiAlias,
      child: InkWell(
        onTap: onTap,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Expanded(
              flex: 5,
              child: imageUrl != null
                  ? CachedNetworkImage(
                      imageUrl: imageUrl,
                      fit: BoxFit.cover,
                      width: double.infinity,
                      placeholder: (_, _) => const _ImagePlaceholder(),
                      errorWidget: (_, _, _) => const _ImagePlaceholder(),
                    )
                  : const _ImagePlaceholder(),
            ),
            Expanded(
              flex: 4,
              child: Padding(
                padding: const EdgeInsets.fromLTRB(12, 10, 12, 12),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      name,
                      style: MkText.label.copyWith(height: 1.3),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const Spacer(),
                    Text(
                      mkMoney(parsedPrice),
                      style: MkText.title,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 2),
                    Text(
                      stockLabel,
                      style: MkText.caption.copyWith(
                        color: stockColour,
                        fontWeight: stockColour == MkColors.muted
                            ? FontWeight.w400
                            : FontWeight.w600,
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
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
    return const ColoredBox(
      color: MkColors.surfaceMuted,
      child: Center(
        child: Icon(Icons.image_outlined, size: 36, color: MkColors.muted),
      ),
    );
  }
}
