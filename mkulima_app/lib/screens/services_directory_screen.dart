import 'package:flutter/material.dart';
import '../core/services_catalog.dart';
import '../core/theme.dart';

/// Huduma Zote — every Mkulima Forum service, grouped.
///
/// Replaces the old FeaturesScreen, which listed the same services but had
/// no way in from the app, so the scanner, Kagua Dawa and others vanished
/// from view. Services that are not switched on stay listed and say so.
class ServicesDirectoryScreen extends StatelessWidget {
  const ServicesDirectoryScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Huduma zote')),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(16, 8, 16, 24),
        children: [
          for (final group in MkServices.groups) ...[
            Padding(
              padding: const EdgeInsets.only(top: 16, bottom: 8),
              child: Text(group, style: MkText.section),
            ),
            for (final service in MkServices.all.where((s) => s.group == group))
              _ServiceRow(service: service),
          ],
        ],
      ),
    );
  }
}

class _ServiceRow extends StatelessWidget {
  final MkService service;
  const _ServiceRow({required this.service});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Material(
        color: MkColors.surface,
        borderRadius: BorderRadius.circular(MkRadii.card),
        child: InkWell(
          borderRadius: BorderRadius.circular(MkRadii.card),
          onTap: () => MkServices.open(context, service),
          child: Container(
            constraints: const BoxConstraints(minHeight: 64),
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
            decoration: BoxDecoration(
              border: Border.all(color: MkColors.border),
              borderRadius: BorderRadius.circular(MkRadii.card),
            ),
            child: Row(
              children: [
                Container(
                  width: 40,
                  height: 40,
                  decoration: BoxDecoration(
                    color: service.isLive ? MkColors.leafPale : MkColors.surfaceMuted,
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Icon(service.icon, color: service.isLive ? MkColors.primary : MkColors.muted),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Flexible(child: Text(service.label, style: MkText.title)),
                          if (!service.isLive) ...[
                            const SizedBox(width: 8),
                            const Text('Bado', style: MkText.caption),
                          ],
                        ],
                      ),
                      const SizedBox(height: 2),
                      Text(service.description, style: MkText.bodyMuted, maxLines: 2, overflow: TextOverflow.ellipsis),
                    ],
                  ),
                ),
                const Icon(Icons.chevron_right, color: MkColors.muted),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
