import 'package:flutter/material.dart';
import '../widgets/mk_error_state.dart';
import '../core/theme.dart';
import '../widgets/mk_skeleton.dart';
import 'package:provider/provider.dart';
import '../services/api_service.dart';
import '../providers/auth_provider.dart';
import '../core/format.dart';

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
      if (!mounted) return;
      setState(() {
        _stats = data['stats'];
        _recentOrders = data['recent_orders'] ?? [];
        _isLoading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _error = ApiService.formatError(e);
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context);

    if (!auth.isAuthenticated) {
      return Scaffold(
        appBar: AppBar(title: const Text('Dashibodi ya Muuzaji')),
        body: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.lock_outline, size: 64, color: MkColors.muted),
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
                  backgroundColor: MkColors.primary,
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
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadDashboard,
          ),
        ],
      ),
      body: _isLoading
          ? const MkListSkeleton()
          : _error != null
          ? MkErrorState(message: _error, onRetry: _loadDashboard)
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
          MkColors.primary,
        ),
        _statCard(
          'Oda',
          '${stats['total_orders'] ?? 0}',
          Icons.shopping_bag,
          MkColors.warning,
        ),
        _statCard(
          'Mapato',
          'TSh ${_money(stats['total_revenue'])}',
          Icons.attach_money,
          MkColors.primary,
        ),
        _statCard(
          'Mwezi Huu',
          'TSh ${_money(stats['monthly_revenue'])}',
          Icons.trending_up,
          MkColors.primary,
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
              style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 4),
            Text(title, style: TextStyle(fontSize: 13, color: MkColors.muted)),
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
                Icon(Icons.inbox, size: 48, color: MkColors.muted),
                const SizedBox(height: 8),
                Text(
                  'Hakuna oda za hivi karibuni',
                  style: TextStyle(color: MkColors.muted),
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
              backgroundColor: statusColor.withValues(alpha: 0.2),
              child: Icon(Icons.shopping_bag, color: statusColor),
            ),
            title: Text('Oda #${_shortId(order['uuid'])}'),
            subtitle: Text(
              '${order['buyer_name'] ?? 'Unknown'} - ${order['items_count'] ?? 0} items',
            ),
            trailing: Text(
              'TSh ${_money(order['total'])}',
              style: const TextStyle(fontWeight: FontWeight.bold),
            ),
          ),
        );
      }).toList(),
    );
  }

  String _money(dynamic value) {
    final amount = value is num
        ? value.toDouble()
        : double.tryParse(value?.toString() ?? '') ?? 0;
    return mkAmount(amount);
  }

  String _shortId(dynamic value) {
    final id = value?.toString() ?? '';
    return id.isEmpty ? '---' : id.substring(0, id.length < 8 ? id.length : 8);
  }

  Color _statusColor(String status) {
    switch (status) {
      case 'pending':
        return MkColors.warning;
      case 'confirmed':
        return MkColors.info;
      case 'shipped':
        return MkColors.info;
      case 'delivered':
        return MkColors.primary;
      case 'cancelled':
        return MkColors.danger;
      default:
        return MkColors.muted;
    }
  }
}
