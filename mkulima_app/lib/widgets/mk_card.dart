import 'package:flutter/material.dart';

import '../core/theme.dart';

/// Standard content card: token radius, optional tap, consistent padding.
/// Use instead of hand-rolled Container(decoration: ...) blocks.
class MkCard extends StatelessWidget {
  final Widget child;
  final EdgeInsetsGeometry padding;
  final VoidCallback? onTap;
  final Color? color;

  const MkCard({
    super.key,
    required this.child,
    this.padding = const EdgeInsets.all(16),
    this.onTap,
    this.color,
  });

  @override
  Widget build(BuildContext context) {
    final card = Card(
      color: color,
      margin: EdgeInsets.zero,
      child: Padding(padding: padding, child: child),
    );
    if (onTap == null) return card;
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(MkRadii.card),
      child: card,
    );
  }
}
