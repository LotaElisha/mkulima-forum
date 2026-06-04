import 'package:flutter/material.dart';

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  final List<Map<String, dynamic>> _notifications = [
    {
      'title': 'Order Imethibitishwa',
      'message': 'Order yako #12345 imethibitishwa na muuzaji',
      'time': '2 dakika zilizopita',
      'read': false,
      'icon': Icons.check_circle,
      'color': Colors.green,
    },
    {
      'title': 'Bidhaa Mpya',
      'message': 'Mbolea ya NPK 23-23-0 sasa inapatikana',
      'time': '1 saa iliyopita',
      'read': false,
      'icon': Icons.new_releases,
      'color': Colors.orange,
    },
    {
      'title': 'Mwisho wa Ofa',
      'message': 'Ofa ya mbegu za mahindi inaisha leo',
      'time': '3 saa zilizopita',
      'read': true,
      'icon': Icons.timer,
      'color': Colors.red,
    },
    {
      'title': 'Majibu ya AI',
      'message': 'Mtaalamu wa AI amejibu swali lako kuhusu ukame',
      'time': 'Jana',
      'read': true,
      'icon': Icons.psychology,
      'color': Colors.blue,
    },
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Arifa'),
        backgroundColor: const Color(0xFF2E7D32),
        foregroundColor: Colors.white,
        actions: [
          TextButton(
            onPressed: () {
              setState(() {
                for (var n in _notifications) {
                  n['read'] = true;
                }
              });
            },
            child: const Text(
              'Soma Zote',
              style: TextStyle(color: Colors.white),
            ),
          ),
        ],
      ),
      body: _notifications.isEmpty
          ? Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.notifications_off,
                      size: 80, color: Colors.grey[400]),
                  const SizedBox(height: 16),
                  Text(
                    'Hakuna arifa',
                    style: TextStyle(color: Colors.grey[600]),
                  ),
                ],
              ),
            )
          : ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: _notifications.length,
              itemBuilder: (context, index) {
                final notif = _notifications[index];
                return Card(
                  margin: const EdgeInsets.only(bottom: 12),
                  color: notif['read'] ? null : Colors.green[50],
                  child: ListTile(
                    leading: CircleAvatar(
                      backgroundColor: notif['color'].withOpacity(0.2),
                      child: Icon(
                        notif['icon'],
                        color: notif['color'],
                      ),
                    ),
                    title: Text(
                      notif['title'],
                      style: TextStyle(
                        fontWeight: notif['read']
                            ? FontWeight.normal
                            : FontWeight.bold,
                      ),
                    ),
                    subtitle: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(notif['message']),
                        const SizedBox(height: 4),
                        Text(
                          notif['time'],
                          style: TextStyle(
                            fontSize: 12,
                            color: Colors.grey[600],
                          ),
                        ),
                      ],
                    ),
                    isThreeLine: true,
                    onTap: () {
                      setState(() {
                        notif['read'] = true;
                      });
                    },
                  ),
                );
              },
            ),
    );
  }
}
