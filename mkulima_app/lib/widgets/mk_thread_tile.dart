import 'package:flutter/material.dart';

import '../core/strings.dart';
import '../core/theme.dart';

/// Forum thread list tile: title, snippet, reply/view/upvote counts,
/// optional region chip and expert badge.
class MkThreadTile extends StatelessWidget {
  final Map<String, dynamic> thread;
  final VoidCallback onTap;

  const MkThreadTile({super.key, required this.thread, required this.onTap});

  @override
  Widget build(BuildContext context) {
    final region = thread['region']?.toString();
    final isExpert = thread['user']?['is_verified_expert'] == true;

    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: ListTile(
        title: Text(
          thread['title'] ?? '',
          style: const TextStyle(fontWeight: FontWeight.bold),
        ),
        subtitle: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              thread['body'] ?? '',
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
            ),
            const SizedBox(height: 4),
            Row(
              children: [
                _CountIcon(Icons.comment, thread['reply_count']),
                const SizedBox(width: 12),
                _CountIcon(Icons.remove_red_eye, thread['view_count']),
                const SizedBox(width: 12),
                _CountIcon(Icons.thumb_up_outlined, thread['upvote_count']),
                if (region != null && region.isNotEmpty) ...[
                  const SizedBox(width: 12),
                  Icon(Icons.place, size: 14, color: Colors.grey[600]),
                  Text(
                    region,
                    style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                  ),
                ],
                if (isExpert) ...[
                  const SizedBox(width: 12),
                  const Icon(Icons.verified, size: 14, color: MkColors.primary),
                  const Text(
                    ' ${MkStrings.expertBadge}',
                    style: TextStyle(fontSize: 12, color: MkColors.primary),
                  ),
                ],
              ],
            ),
          ],
        ),
        isThreeLine: true,
        onTap: onTap,
      ),
    );
  }
}

class _CountIcon extends StatelessWidget {
  final IconData icon;
  final dynamic count;

  const _CountIcon(this.icon, this.count);

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(icon, size: 14, color: Colors.grey[600]),
        Text(
          ' ${count ?? 0}',
          style: TextStyle(fontSize: 12, color: Colors.grey[600]),
        ),
      ],
    );
  }
}
