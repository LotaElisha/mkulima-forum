import 'package:flutter/material.dart';

import '../core/strings.dart';
import '../core/theme.dart';

/// Forum thread row: author, title, snippet, then reply and upvote counts,
/// region and an expert badge. Rows are separated by hairlines rather than
/// boxed in cards, so a long list reads as one feed on a narrow screen.
class MkThreadTile extends StatelessWidget {
  final Map<String, dynamic> thread;
  final VoidCallback onTap;

  const MkThreadTile({super.key, required this.thread, required this.onTap});

  @override
  Widget build(BuildContext context) {
    final region = thread['region']?.toString() ?? '';
    final user = thread['user'];
    final author = user?['name']?.toString() ?? '';
    final isExpert = user?['is_verified_expert'] == true;
    final body = thread['body']?.toString() ?? '';

    return InkWell(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 16),
        decoration: const BoxDecoration(
          border: Border(bottom: BorderSide(color: MkColors.border)),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (author.isNotEmpty || isExpert)
              Padding(
                padding: const EdgeInsets.only(bottom: 6),
                child: Wrap(
                  spacing: 8,
                  runSpacing: 4,
                  crossAxisAlignment: WrapCrossAlignment.center,
                  children: [
                    if (author.isNotEmpty)
                      Text(
                        author,
                        style: MkText.caption.copyWith(
                          color: MkColors.ink,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    if (isExpert) const _ExpertBadge(),
                  ],
                ),
              ),
            Text(
              thread['title'] ?? '',
              style: MkText.title,
              maxLines: 3,
              overflow: TextOverflow.ellipsis,
            ),
            if (body.isNotEmpty) ...[
              const SizedBox(height: 4),
              Text(
                body,
                style: MkText.bodyMuted,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
              ),
            ],
            const SizedBox(height: 8),
            Wrap(
              spacing: 14,
              runSpacing: 4,
              crossAxisAlignment: WrapCrossAlignment.center,
              children: [
                _Count(
                  Icons.chat_bubble_outline,
                  thread['reply_count'],
                  'Majibu',
                ),
                _Count(
                  Icons.thumb_up_outlined,
                  thread['upvote_count'],
                  MkStrings.upvote,
                ),
                if (region.isNotEmpty)
                  _Count(Icons.place_outlined, null, region),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

class _ExpertBadge extends StatelessWidget {
  const _ExpertBadge();

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
      decoration: BoxDecoration(
        color: MkColors.leafPale,
        borderRadius: BorderRadius.circular(999),
      ),
      child: const Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(Icons.verified, size: 14, color: MkColors.primaryDark),
          SizedBox(width: 4),
          Text(
            MkStrings.expertBadge,
            style: TextStyle(
              fontSize: 13,
              fontWeight: FontWeight.w600,
              color: MkColors.primaryDark,
            ),
          ),
        ],
      ),
    );
  }
}

class _Count extends StatelessWidget {
  final IconData icon;
  final dynamic count;
  final String label;

  const _Count(this.icon, this.count, this.label);

  @override
  Widget build(BuildContext context) {
    final text = count == null ? label : '$label ${count ?? 0}';
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(icon, size: 16, color: MkColors.muted),
        const SizedBox(width: 4),
        Text(text, style: MkText.caption),
      ],
    );
  }
}
