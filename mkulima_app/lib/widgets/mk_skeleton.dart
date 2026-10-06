import 'package:flutter/material.dart';

import '../core/theme.dart';

/// A flat placeholder block shown while content loads.
///
/// Skeletons replace bare spinners on list and grid screens: on a slow 3G
/// connection a spinner on a white page reads as "broken", while the shape of
/// the content tells the farmer what is coming. Deliberately static (no
/// shimmer animation) so it costs nothing on low-end phones.
class MkSkeleton extends StatelessWidget {
  final double? width;
  final double height;
  final double radius;

  const MkSkeleton({
    super.key,
    this.width,
    this.height = 14,
    this.radius = 8,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      width: width,
      height: height,
      decoration: BoxDecoration(
        color: MkColors.skeleton,
        borderRadius: BorderRadius.circular(radius),
      ),
    );
  }
}

/// Skeleton for a vertical list of rows (threads, orders, notifications).
class MkListSkeleton extends StatelessWidget {
  final int rows;
  final bool leading;
  final EdgeInsetsGeometry padding;

  const MkListSkeleton({
    super.key,
    this.rows = 5,
    this.leading = true,
    this.padding = const EdgeInsets.all(16),
  });

  @override
  Widget build(BuildContext context) {
    return Semantics(
      label: 'Inapakia',
      child: ListView.separated(
        padding: padding,
        physics: const NeverScrollableScrollPhysics(),
        shrinkWrap: true,
        itemCount: rows,
        separatorBuilder: (_, _) => const SizedBox(height: 20),
        itemBuilder: (_, i) => Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (leading) ...[
              const MkSkeleton(width: 40, height: 40, radius: 20),
              const SizedBox(width: 12),
            ],
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  FractionallySizedBox(
                    widthFactor: i.isEven ? .9 : .75,
                    child: const MkSkeleton(height: 15),
                  ),
                  const SizedBox(height: 8),
                  const FractionallySizedBox(
                    widthFactor: .45,
                    child: MkSkeleton(height: 13),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

/// Skeleton matching the two-column product grid.
class MkGridSkeleton extends StatelessWidget {
  final int items;
  final double childAspectRatio;

  const MkGridSkeleton({
    super.key,
    this.items = 4,
    this.childAspectRatio = .62,
  });

  @override
  Widget build(BuildContext context) {
    return Semantics(
      label: 'Inapakia',
      child: GridView.builder(
        padding: const EdgeInsets.all(16),
        physics: const NeverScrollableScrollPhysics(),
        shrinkWrap: true,
        gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
          crossAxisCount: 2,
          mainAxisSpacing: 12,
          crossAxisSpacing: 12,
          childAspectRatio: childAspectRatio,
        ),
        itemCount: items,
        itemBuilder: (_, _) => Container(
          decoration: BoxDecoration(
            border: Border.all(color: MkColors.border),
            borderRadius: BorderRadius.circular(MkRadii.card),
          ),
          clipBehavior: Clip.antiAlias,
          child: const Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Expanded(child: MkSkeleton(height: double.infinity, radius: 0)),
              Padding(
                padding: EdgeInsets.all(12),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    MkSkeleton(height: 14),
                    SizedBox(height: 8),
                    MkSkeleton(width: 70, height: 17),
                    SizedBox(height: 12),
                    MkSkeleton(height: 44, radius: 12),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
