import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../services/api_service.dart';
import '../providers/auth_provider.dart';

class SellerDashboardScreen extends StatefulWidget {
  const SellerDashboardScreen({super.key});

  @override
  State<SellerDashboardScreen> createState() => _SellerDashboardScreenState();
}

class _SellerDashboardScreenState extends State<SellerDashboardScreen> {
  Map<String, dynamic>? _stats;
  List<dynamic> _recentOrders = [];
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadDashboard();
  }

  Future<void> _loadDashboard() async {
    final auth = Provider.of<AuthProvider>(context, listen: false);
    if (!auth.isAuthenticated) {
      setState(() => _isLoading = false);
      return;
    }

    try {
      final api = Provider.of<ApiService>(context, listen: false);
      final data = await api.getSellerDashboard();
      setState(() {
        _stats = data['stats'];
        _recentOrders = data['recent_orders'] ?? [];
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context);

    if (!auth.isAuthenticated) {
      return Scaffold(
        appBar: AppBar(
          title: const Text('Dashibodi ya Muuzaji'),
          backgroundColor: const Color(0xFF2E7D32),
          foregroundColor: Colors.white,
        ),
        body: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.lock_outline, size: 64, color: Colors.grey[400]),
              const SizedBox(height: 16),
              const Text('Ingia kuona dashibodi yako'),
              const SizedBox(height: 16),
              ElevatedButton(
                onPressed: () async {
                  final ok = await AuthProvider.requireAuth(
                    context,
                    action: 'kuangalia dashibodi',
                  );
                  if (ok) _loadDashboard();
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
        title: const Text('Dashibodi ya Muuzaji'),
        backgroundColor: const Color(0xFF2E7D32),
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadDashboard,
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
                    onPressed: _loadDashboard,
                    child: const Text('Jaribu Tena'),
                  ),
                ],
              ),
            )
          : RefreshIndicator(
              onRefresh: _loadDashboard,
              child: SingleChildScrollView(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _buildStatsGrid(),
                    const SizedBox(height: 24),
                    const Text(
                      'Oda za Hivi Karibuni',
                      style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 12),
                    _buildRecentOrders(),
                  ],
                ),
              ),
            ),
    );
  }

  Widget _buildStatsGrid() {
    final stats = _stats ?? {};
    return GridView.count(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      crossAxisCount: 2,
      childAspectRatio: 1.3,
      crossAxisSpacing: 12,
      mainAxisSpacing: 12,
      children: [
        _statCard(
          'Bidhaa',
          '${stats['total_products'] ?? 0}',
          Icons.inventory,
          Colors.blue,
        ),
        _statCard(
          'Oda',
          '${stats['total_orders'] ?? 0}',
          Icons.shopping_bag,
          Colors.orange,
        ),
        _statCard(
          'Mapato',
          'TSh ${(stats['total_revenue'] ?? 0).toStringAsFixed(0)}',
          Icons.attach_money,
          Colors.green,
        ),
        _statCard(
          'Mwezi Huu',
          'TSh ${(stats['monthly_revenue'] ?? 0).toStringAsFixed(0)}',
          Icons.trending_up,
          Colors.purple,
        ),
      ],
    );
  }

  Widget _statCard(String title, String value, IconData icon, Color color) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Icon(icon, color: color, size: 28),
            const Spacer(),
            Text(
              value,
              style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 4),
            Text(
              title,
              style: TextStyle(fontSize: 13, color: Colors.grey[600]),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildRecentOrders() {
    if (_recentOrders.isEmpty) {
      return Card(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Center(
            child: Column(
              children: [
                Icon(Icons.inbox, size: 48, color: Colors.grey[400]),
                const SizedBox(height: 8),
                Text(
                  'Hakuna oda za hivi karibuni',
                  style: TextStyle(color: Colors.grey[600]),
                ),
              ],
            ),
          ),
        ),
      );
    }

    return Column(
      children: _recentOrders.map((order) {
        final status = order['status'] ?? 'pending';
        final statusColor = _statusColor(status);
        return Card(
          margin: const EdgeInsets.only(bottom: 8),
          child: ListTile(
            leading: CircleAvatar(
              backgroundColor: statusColor.withOpacity(0.2),
              child: Icon(Icons.shopping_bag, color: statusColor),
            ),
            title: Text(
              'Oda #${order['uuid']?.toString().substring(0, 8) ?? '---'}',
            ),
            subtitle: Text(
              '${order['buyer_name'] ?? 'Unknown'} - ${order['items_count'] ?? 0} items',
            ),
            trailing: Text(
              'TSh ${(order['total'] ?? 0).toStringAsFixed(0)}',
              style: const TextStyle(fontWeight: FontWeight.bold),
            ),
          ),
        );
      }).toList(),
    );
  }

  Color _statusColor(String status) {
    switch (status) {
      case 'pending':
        return Colors.orange;
      case 'confirmed':
        return Colors.blue;
      case 'shipped':
        return Colors.purple;
      case 'delivered':
        return Colors.green;
      case 'cancelled':
        return Colors.red;
      default:
        return Colors.grey;
    }
  }
}
