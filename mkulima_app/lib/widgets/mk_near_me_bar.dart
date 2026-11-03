import 'package:flutter/material.dart';
import '../core/regions.dart';
import '../core/theme.dart';

/// "Karibu nawe" / region switch shown above every near-me list.
///
/// Shows what the list is based on, so a farmer is never left guessing why
/// results are far away: their location, a region they picked, or all of
/// Tanzania.
class MkNearMeBar extends StatelessWidget {
  final bool hasLocation;
  final bool locating;
  final String? region;
  final VoidCallback onUseLocation;
  final ValueChanged<String?> onRegion;

  const MkNearMeBar({
    super.key,
    required this.hasLocation,
    required this.locating,
    required this.region,
    required this.onUseLocation,
    required this.onRegion,
  });

  @override
  Widget build(BuildContext context) {
    final label = locating
        ? 'Inatafuta mahali ulipo...'
        : hasLocation
        ? 'Karibu nawe (km 50)'
        : region != null
        ? 'Mkoa wa $region'
        : 'Tanzania nzima';

    return Container(
      padding: const EdgeInsets.fromLTRB(16, 8, 8, 8),
      decoration: const BoxDecoration(
        border: Border(bottom: BorderSide(color: MkColors.border)),
      ),
      child: Row(
        children: [
          Icon(
            hasLocation ? Icons.my_location : Icons.place_outlined,
            size: 18,
            color: MkColors.primary,
          ),
          const SizedBox(width: 8),
          Expanded(
            child: Text(
              label,
              style: MkText.label,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ),
          if (!hasLocation && !locating)
            IconButton(
              onPressed: onUseLocation,
              tooltip: 'Tumia mahali nilipo',
              icon: const Icon(Icons.my_location, color: MkColors.primary),
            ),
          TextButton(
            onPressed: () => _pickRegion(context),
            child: const Text('Mkoa'),
          ),
        ],
      ),
    );
  }

  Future<void> _pickRegion(BuildContext context) async {
    final picked = await showModalBottomSheet<String>(
      context: context,
      isScrollControlled: true,
      builder: (context) => DraggableScrollableSheet(
        expand: false,
        initialChildSize: 0.7,
        builder: (context, controller) => ListView(
          controller: controller,
          children: [
            const Padding(
              padding: EdgeInsets.fromLTRB(16, 16, 16, 8),
              child: Text('Chagua mkoa', style: MkText.section),
            ),
            ListTile(
              title: const Text('Tanzania nzima'),
              onTap: () => Navigator.pop(context, ''),
            ),
            for (final r in tanzaniaRegions)
              ListTile(
                title: Text(r),
                trailing: r == region
                    ? const Icon(Icons.check, color: MkColors.primary)
                    : null,
                onTap: () => Navigator.pop(context, r),
              ),
          ],
        ),
      ),
    );
    if (picked == null) return;
    onRegion(picked.isEmpty ? null : picked);
  }
}
