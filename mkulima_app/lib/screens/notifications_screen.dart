import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../services/api_service.dart';
import '../providers/auth_provider.dart';

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  List<dynamic> _notifications = [];
  bool _isLoading = true;
  String? _error;
  int _unreadCount = 0;

  @override
  void initState() {
    super.initState();
    _loadNotifications();
  }

  Future<void> _loadNotifications() async {
    final auth = Provider.of<AuthProvider>(context, listen: false);
    if (!auth.isAuthenticated) {
      setState(() => _isLoading = false);
      return;
    }

    try {
      final api = Provider.of<ApiService>(context, listen: false);
      final data = await api.getNotifications();
      setState(() {
        _notifications = data['notifications'] ?? [];
        _unreadCount = data['unread_count'] ?? 0;
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  Future<void> _markAllRead() async {
    try {
      final api = Provider.of<ApiService>(context, listen: false);
      await api.markAllNotificationsRead();
      setState(() {
        for (var n in _notifications) {
          n['read'] = true;
        }
        _unreadCount = 0;
      });
    } catch (e) {
      // Silently fail
    }
  }

  Future<void> _markRead(String id) async {
    try {
      final api = Provider.of<ApiService>(context, listen: false);
      await api.markNotificationRead(id);
      setState(() {
        final index = _notifications.indexWhere((n) => n['id'] == id);
        if (index >= 0) {
          _notifications[index]['read'] = true;
          _unreadCount = (_unreadCount - 1).clamp(0, 999);
        }
      });
    } catch (e) {
      // Silently fail
    }
  }

  IconData _notificationIcon(String type) {
    switch (type) {
      case 'order':
        return Icons.shopping_bag;
      case 'system':
        return Icons.info;
      case 'forum':
        return Icons.forum;
      default:
        return Icons.notifications;
    }
  }

  Color _notificationColor(String type) {
    switch (type) {
      case 'order':
        return Colors.orange;
      case 'system':
        return Colors.blue;
      case 'forum':
        return Colors.green;
      default:
        return Colors.grey;
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context);

    if (!auth.isAuthenticated) {
      return Scaffold(
        appBar: AppBar(
          title: const Text('Arifa'),
          backgroundColor: const Color(0xFF2E7D32),
          foregroundColor: Colors.white,
        ),
        body: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.lock_outline, size: 64, color: Colors.grey[400]),
              const SizedBox(height: 16),
              const Text('Ingia kuona arifa zako'),
              const SizedBox(height: 16),
              ElevatedButton(
                onPressed: () async {
                  final ok = await AuthProvider.requireAuth(
                    context,
                    action: 'kuangalia arifa',
                  );
                  if (ok) _loadNotifications();
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF2E7D32),
                  foregroundColor: Colors.white,
                ),
                child: const Text('Ingia'),
              ),
            ],
          ),
        ),
      );
    }

    return Scaffold(
      appBar: AppBar(
        title: const Text('Arifa'),
        backgroundColor: const Color(0xFF2E7D32),
        foregroundColor: Colors.white,
        actions: [
          if (_notifications.isNotEmpty)
            TextButton(
              onPressed: _markAllRead,
              child: const Text(
                'Soma Zote',
                style: TextStyle(color: Colors.white),
              ),
            ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
          ? Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.error, size: 64, color: Colors.red[300]),
                  const SizedBox(height: 16),
                  Text('Kosa: $_error'),
                  const SizedBox(height: 16),
                  ElevatedButton(
                    onPressed: _loadNotifications,
                    child: const Text('Jaribu Tena'),
                  ),
                ],
              ),
            )
          : _notifications.isEmpty
          ? Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(
                    Icons.notifications_off,
                    size: 80,
                    color: Colors.grey[400],
                  ),
                  const SizedBox(height: 16),
                  Text(
                    'Hakuna arifa',
                    style: TextStyle(color: Colors.grey[600]),
                  ),
                ],
              ),
            )
          : RefreshIndicator(
              onRefresh: _loadNotifications,
              child: ListView.builder(
                padding: const EdgeInsets.all(16),
                itemCount: _notifications.length,
                itemBuilder: (context, index) {
                  final notif = _notifications[index];
                  final isRead = notif['read'] ?? true;
                  return Card(
                    margin: const EdgeInsets.only(bottom: 12),
                    color: isRead ? null : Colors.green[50],
                    child: ListTile(
                      leading: CircleAvatar(
                        backgroundColor: _notificationColor(
                          notif['type'] ?? 'general',
                        ).withOpacity(0.2),
                        child: Icon(
                          _notificationIcon(notif['type'] ?? 'general'),
                          color: _notificationColor(notif['type'] ?? 'general'),
                        ),
                      ),
                      title: Text(
                        notif['title'] ?? 'Arifa',
                        style: TextStyle(
                          fontWeight: isRead
                              ? FontWeight.normal
                              : FontWeight.bold,
                        ),
                      ),
                      subtitle: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(notif['message'] ?? ''),
                          const SizedBox(height: 4),
                          Text(
                            notif['created_at'] ?? '',
                            style: TextStyle(
                              fontSize: 12,
                              color: Colors.grey[600],
                            ),
                          ),
                        ],
                      ),
                      isThreeLine: true,
                      onTap: () => _markRead(notif['id'] ?? ''),
                    ),
                  );
                },
              ),
            ),
    );
  }
}
