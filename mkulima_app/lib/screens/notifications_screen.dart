import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../core/theme.dart';
import '../models/app_notification.dart';
import '../providers/auth_provider.dart';
import '../services/api_service.dart';

/// The Arifa feed.
///
/// Rewritten after it crashed on open with
///   type 'List<dynamic>' is not a subtype of type
///   'FutureOr<Map<String, dynamic>>'
/// The parsing now happens once, in [NotificationFeed], and this screen only
/// ever touches typed fields. Timestamps are shown in Swahili relative time
/// rather than the raw ISO8601 string the API sends, which is what the list
/// used to print under every row.
class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  NotificationFeed _feed = NotificationFeed.empty;
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadNotifications();
  }

  Future<void> _loadNotifications() async {
    final auth = context.read<AuthProvider>();
    if (!auth.isAuthenticated) {
      if (mounted) setState(() => _isLoading = false);
      return;
    }

    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final feed = await context.read<ApiService>().getNotifications();
      if (!mounted) return;
      setState(() {
        _feed = feed;
        _isLoading = false;
      });
    } catch (error) {
      if (!mounted) return;
      setState(() {
        _error = ApiService.formatError(error);
        _isLoading = false;
      });
    }
  }

  Future<void> _markAllRead() async {
    // Optimistic: the rows turn read immediately and roll back only if the
    // call fails. A read receipt is not worth a spinner.
    final previous = _feed;
    setState(() {
      _feed = NotificationFeed(
        items: _feed.items.map((n) => n.copyWith(read: true)).toList(),
        unreadCount: 0,
      );
    });

    try {
      await context.read<ApiService>().markAllNotificationsRead();
    } catch (error) {
      if (!mounted) return;
      setState(() => _feed = previous);
      _toast(ApiService.formatError(error));
    }
  }

  Future<void> _markRead(AppNotification notification) async {
    if (notification.read) return;

    final previous = _feed;
    setState(() {
      _feed = NotificationFeed(
        items: _feed.items
            .map((n) => n.id == notification.id ? n.copyWith(read: true) : n)
            .toList(),
        unreadCount: (_feed.unreadCount - 1).clamp(0, 9999),
      );
    });

    try {
      await context.read<ApiService>().markNotificationRead(notification.id);
    } catch (error) {
      if (!mounted) return;
      setState(() => _feed = previous);
      _toast(ApiService.formatError(error));
    }
  }

  void _toast(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message), behavior: SnackBarBehavior.floating),
    );
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();

    return Scaffold(
      appBar: AppBar(
        title: const Text('Arifa'),
        actions: [
          if (auth.isAuthenticated && _feed.items.isNotEmpty)
            TextButton(
              onPressed: _feed.unreadCount == 0 ? null : _markAllRead,
              child: const Text('Soma zote'),
            ),
        ],
      ),
      body: auth.isAuthenticated ? _buildBody() : _buildSignedOut(),
    );
  }

  Widget _buildSignedOut() {
    return _CenteredMessage(
      icon: Icons.lock_outline,
      title: 'Ingia kuona arifa zako',
      body: 'Arifa za oda na taarifa za mfumo zinaonekana ukiwa umeingia.',
      actionLabel: 'Ingia',
      onAction: () async {
        final ok = await AuthProvider.requireAuth(
          context,
          action: 'kuangalia arifa',
        );
        if (ok) _loadNotifications();
      },
    );
  }

  Widget _buildBody() {
    if (_isLoading) {
      return const Center(child: CircularProgressIndicator());
    }

    if (_error != null) {
      return _CenteredMessage(
        icon: Icons.cloud_off_outlined,
        title: 'Hatukuweza kupakia arifa',
        body: _error!,
        actionLabel: 'Jaribu tena',
        onAction: _loadNotifications,
      );
    }

    if (_feed.isEmpty) {
      // Pull-to-refresh has to work on an empty list too, which needs a
      // scrollable that always scrolls - hence the physics override.
      return RefreshIndicator(
        onRefresh: _loadNotifications,
        child: ListView(
          physics: const AlwaysScrollableScrollPhysics(),
          children: const [
            SizedBox(height: 120),
            _CenteredMessage(
              icon: Icons.notifications_none,
              title: 'Hakuna arifa bado',
              body: 'Utapokea taarifa hapa oda yako inapobadilika hali.',
            ),
          ],
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: _loadNotifications,
      child: ListView.builder(
        padding: const EdgeInsets.all(16),
        physics: const AlwaysScrollableScrollPhysics(),
        itemCount: _feed.items.length,
        itemBuilder: (context, index) {
          final notification = _feed.items[index];
          return _NotificationCard(
            notification: notification,
            onTap: () => _markRead(notification),
          );
        },
      ),
    );
  }
}

class _NotificationCard extends StatelessWidget {
  final AppNotification notification;
  final VoidCallback onTap;

  const _NotificationCard({required this.notification, required this.onTap});

  @override
  Widget build(BuildContext context) {
    final unread = !notification.read;

    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      color: unread ? MkColors.leafPale : MkColors.surface,
      child: ListTile(
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        leading: Container(
          width: 44,
          height: 44,
          decoration: BoxDecoration(
            color: MkColors.leafPale,
            borderRadius: BorderRadius.circular(12),
          ),
          child: Icon(_icon(notification.type), color: MkColors.primary),
        ),
        title: Text(
          notification.title.isEmpty ? 'Arifa' : notification.title,
          style: TextStyle(
            fontWeight: unread ? FontWeight.w700 : FontWeight.w500,
          ),
        ),
        subtitle: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const SizedBox(height: 4),
            Text(
              notification.message,
              style: const TextStyle(color: MkColors.muted, height: 1.35),
            ),
            const SizedBox(height: 6),
            Text(
              _relativeTime(notification.createdAt),
              style: const TextStyle(fontSize: 12, color: MkColors.muted),
            ),
          ],
        ),
        trailing: unread
            ? Container(
                width: 9,
                height: 9,
                decoration: const BoxDecoration(
                  color: MkColors.primary,
                  shape: BoxShape.circle,
                ),
              )
            : null,
        isThreeLine: true,
        onTap: onTap,
      ),
    );
  }

  static IconData _icon(String type) => switch (type) {
        'order' => Icons.shopping_bag_outlined,
        'forum' => Icons.forum_outlined,
        'payment' => Icons.account_balance_wallet_outlined,
        'system' => Icons.info_outline,
        _ => Icons.notifications_none,
      };

  /// Swahili relative time. The list used to print the raw ISO8601 string.
  static String _relativeTime(DateTime? time) {
    if (time == null) return '';

    final diff = DateTime.now().difference(time);
    if (diff.isNegative) return 'Sasa hivi';
    if (diff.inMinutes < 1) return 'Sasa hivi';
    if (diff.inMinutes < 60) return 'Dakika ${diff.inMinutes} zilizopita';
    if (diff.inHours < 24) return 'Saa ${diff.inHours} zilizopita';
    if (diff.inDays == 1) return 'Jana';
    if (diff.inDays < 7) return 'Siku ${diff.inDays} zilizopita';
    if (diff.inDays < 30) return 'Wiki ${(diff.inDays / 7).floor()} zilizopita';
    if (diff.inDays < 365) return 'Miezi ${(diff.inDays / 30).floor()} iliyopita';
    return 'Zaidi ya mwaka mmoja uliopita';
  }
}

class _CenteredMessage extends StatelessWidget {
  final IconData icon;
  final String title;
  final String body;
  final String? actionLabel;
  final VoidCallback? onAction;

  const _CenteredMessage({
    required this.icon,
    required this.title,
    required this.body,
    this.actionLabel,
    this.onAction,
  });

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 32, vertical: 24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, size: 56, color: MkColors.muted),
            const SizedBox(height: 16),
            Text(
              title,
              textAlign: TextAlign.center,
              style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w700),
            ),
            const SizedBox(height: 8),
            Text(
              body,
              textAlign: TextAlign.center,
              style: const TextStyle(color: MkColors.muted, height: 1.45),
            ),
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
