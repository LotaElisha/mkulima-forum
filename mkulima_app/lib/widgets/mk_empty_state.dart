import 'package:flutter/material.dart';

import '../core/theme.dart';

/// Empty state used across list screens. The subtitle should say what to do
/// next ("Ukinunua bidhaa sokoni, oda zako zitaonekana hapa"), not just that
/// there is no data, and [actionLabel] should be that next step.
class MkEmptyState extends StatelessWidget {
  final IconData icon;
  final String title;
  final String? subtitle;
  final String? actionLabel;
  final VoidCallback? onAction;

  const MkEmptyState({
    super.key,
    this.icon = Icons.inbox_outlined,
    required this.title,
    this.subtitle,
    this.actionLabel,
    this.onAction,
  });

  @override
  Widget build(BuildContext context) {
    return Center(
      child: SingleChildScrollView(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 64,
              height: 64,
              decoration: BoxDecoration(
                color: MkColors.leafPale,
                borderRadius: BorderRadius.circular(20),
              ),
              child: Icon(icon, size: 30, color: MkColors.primary),
            ),
            const SizedBox(height: 16),
            Text(title, style: MkText.title, textAlign: TextAlign.center),
            if (subtitle != null) ...[
              const SizedBox(height: 8),
              Text(
                subtitle!,
                style: MkText.bodyMuted,
                textAlign: TextAlign.center,
              ),
            ],
            if (actionLabel != null && onAction != null) ...[
              const SizedBox(height: 20),
              FilledButton(onPressed: onAction, child: Text(actionLabel!)),
            ],
          ],
        ),
      ),
    );
  }
}
