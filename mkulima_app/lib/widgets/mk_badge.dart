import 'package:flutter/material.dart';
import '../core/theme.dart';

/// Status pill shared with the web badge.
///
/// `live` is for something a farmer can use today. The default tone is the
/// warning chip used for “Inakuja hivi karibuni”.
class MkBadge extends StatelessWidget {
  final String label;
  final bool live;

  const MkBadge(this.label, {super.key, this.live = false});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color: live ? MkColors.leafPale : MkColors.accentSoft,
        borderRadius: BorderRadius.circular(999),
      ),
      child: Text(
        label,
        style: MkText.caption.copyWith(
          color: live ? MkColors.primaryDark : MkColors.warning,
          fontWeight: FontWeight.w700,
        ),
      ),
    );
  }
}
