import 'package:flutter/material.dart';

import '../core/strings.dart';
import '../core/theme.dart';

/// Error panel: what failed, the server's own message, and a retry.
///
/// [message] should already be user-facing Swahili — pass the result of
/// `ApiService.formatError`, never `e.toString()`.
class MkErrorState extends StatelessWidget {
  final String title;
  final String? message;
  final VoidCallback? onRetry;

  const MkErrorState({
    super.key,
    this.title = MkStrings.errorGeneric,
    this.message,
    this.onRetry,
  });

  @override
  Widget build(BuildContext context) {
    return Center(
      child: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            border: Border.all(color: MkColors.dangerBorder),
            borderRadius: BorderRadius.circular(MkRadii.card),
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Icon(Icons.error_outline, color: MkColors.danger),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(title, style: MkText.label),
                        if (message != null && message!.isNotEmpty) ...[
                          const SizedBox(height: 4),
                          Text(message!, style: MkText.bodyMuted),
                        ],
                      ],
                    ),
                  ),
                ],
              ),
              if (onRetry != null) ...[
                const SizedBox(height: 16),
                OutlinedButton.icon(
                  onPressed: onRetry,
                  icon: const Icon(Icons.refresh),
                  label: const Text(MkStrings.retry),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }
}
