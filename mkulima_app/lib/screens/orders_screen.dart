import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../services/api_service.dart';
import '../providers/auth_provider.dart';

class OrdersScreen extends StatefulWidget {
  const OrdersScreen({super.key});

  @override
  State<OrdersScreen> createState() => _OrdersScreenState();
}

class _OrdersScreenState extends State<OrdersScreen> {
  List<dynamic> _orders = [];
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadOrders();
  }

  Future<void> _loadOrders() async {
    final auth = Provider.of<AuthProvider>(context, listen: false);
    if (!auth.isAuthenticated) {
      setState(() {
        _isLoading = false;
        _orders = [];
      });
      return;
    }

    try {
      final api = Provider.of<ApiService>(context, listen: false);
      final response = await api.getOrders();
      setState(() {
        _orders = response;
        _isLoading = false;
      });
    } catch (e) {
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
        appBar: AppBar(
          title: const Text('Oda Zangu'),
          backgroundColor: const Color(0xFF2E7D32),
          foregroundColor: Colors.white,
        ),
        body: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.lock_outline, size: 64, color: Colors.grey[400]),
              const SizedBox(height: 16),
              const Text('Ingia kuona oda zako'),
              const SizedBox(height: 16),
              ElevatedButton(
                onPressed: () async {
                  final ok = await AuthProvider.requireAuth(
                    context,
                    action: 'kuangalia oda zako',
                  );
                  if (ok) _loadOrders();
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
        title: const Text('Oda Zangu'),
        backgroundColor: const Color(0xFF2E7D32),
        foregroundColor: Colors.white,
        actions: [
          IconButton(icon: const Icon(Icons.refresh), onPressed: _loadOrders),
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
                    onPressed: _loadOrders,
                    child: const Text('Jaribu Tena'),
                  ),
                ],
              ),
            )
          : _orders.isEmpty
          ? Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(
                    Icons.shopping_bag_outlined,
                    size: 80,
                    color: Colors.grey[400],
                  ),
                  const SizedBox(height: 16),
                  Text(
                    'Huna oda zozote',
                    style: TextStyle(color: Colors.grey[600]),
                  ),
                  const SizedBox(height: 16),
                  ElevatedButton(
                    onPressed: () => Navigator.of(context).pop(),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF2E7D32),
                      foregroundColor: Colors.white,
                    ),
                    child: const Text('Nunua Sasa'),
                  ),
                ],
              ),
            )
          : RefreshIndicator(
              onRefresh: _loadOrders,
              child: ListView.builder(
                padding: const EdgeInsets.all(16),
                itemCount: _orders.length,
                itemBuilder: (context, index) {
                  final order = _orders[index];
                  return _buildOrderCard(order);
                },
              ),
            ),
    );
  }

  Widget _buildOrderCard(dynamic order) {
    final status = order['status'] ?? 'pending';
    final statusColor = _statusColor(status);
    final statusText = _statusText(status);

    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  'Oda #${_shortId(order['uuid'])}',
                  style: const TextStyle(
                    fontWeight: FontWeight.bold,
                    fontSize: 16,
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 12,
                    vertical: 4,
                  ),
                  decoration: BoxDecoration(
                    color: statusColor.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(color: statusColor),
                  ),
                  child: Text(
                    statusText,
                    style: TextStyle(
                      color: statusColor,
                      fontWeight: FontWeight.bold,
                      fontSize: 12,
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            if (order['items'] != null)
              ...((order['items'] as List).map(
                (item) => Padding(
                  padding: const EdgeInsets.only(bottom: 4),
                  child: Row(
                    children: [
                      const Icon(
                        Icons.check_circle,
                        size: 16,
                        color: Color(0xFF2E7D32),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          '${item['quantity']}x ${item['product_snapshot']?['name'] ?? 'Bidhaa'}',
                          style: const TextStyle(fontSize: 14),
                        ),
                      ),
                      Text(
                        'TSh ${_money(item['total_price'])}',
                        style: const TextStyle(fontWeight: FontWeight.w600),
                      ),
                    ],
                  ),
                ),
              )),
            const Divider(height: 24),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text('Jumla:'),
                Text(
                  'TSh ${_money(order['total'])}',
                  style: const TextStyle(
                    fontWeight: FontWeight.bold,
                    fontSize: 18,
                    color: Color(0xFF2E7D32),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 8),
            Text(
              'Tarehe: ${order['created_at'] ?? 'N/A'}',
              style: TextStyle(fontSize: 12, color: Colors.grey[600]),
            ),
            if (status == 'pending') ...[
              const SizedBox(height: 12),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton.icon(
                  onPressed: () => _retryPayment(order),
                  icon: const Icon(Icons.payment),
                  label: const Text('Endelea na Malipo'),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  String _money(dynamic value) {
    final amount = value is num
        ? value.toDouble()
        : double.tryParse(value?.toString() ?? '') ?? 0;
    return amount.toStringAsFixed(0);
  }

  String _shortId(dynamic value) {
    final id = value?.toString() ?? '';
    return id.isEmpty ? '---' : id.substring(0, id.length < 8 ? id.length : 8);
  }

  Future<void> _retryPayment(dynamic order) async {
    final phoneController = TextEditingController(
      text: order['delivery_phone']?.toString() ?? '',
    );
    var method = 'mpesa';
    final shouldPay = await showDialog<bool>(
      context: context,
      builder: (dialogContext) => StatefulBuilder(
        builder: (context, setDialogState) => AlertDialog(
          title: const Text('Endelea na Malipo'),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              DropdownButtonFormField<String>(
                initialValue: method,
                items: const [
                  DropdownMenuItem(value: 'mpesa', child: Text('M-Pesa')),
                  DropdownMenuItem(value: 'tigopesa', child: Text('Tigo Pesa')),
                ],
                onChanged: (value) => setDialogState(() => method = value ?? 'mpesa'),
                decoration: const InputDecoration(labelText: 'Njia ya malipo'),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: phoneController,
                keyboardType: TextInputType.phone,
                decoration: const InputDecoration(
                  labelText: 'Namba ya malipo',
                  hintText: '2557XXXXXXXX',
                ),
              ),
            ],
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(dialogContext, false),
              child: const Text('Ghairi'),
            ),
            ElevatedButton(
              onPressed: () => Navigator.pop(dialogContext, true),
              child: const Text('Tuma Ombi'),
            ),
          ],
        ),
      ),
    );
    if (shouldPay != true || !mounted) {
      phoneController.dispose();
      return;
    }
    final phone = phoneController.text.trim();
    phoneController.dispose();
    if (!RegExp(r'^255[0-9]{9}$').hasMatch(phone)) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Weka namba ya tarakimu 12 inayoanza na 255.')),
      );
      return;
    }
    final rawId = order['id'];
    final orderId = rawId is int ? rawId : int.tryParse(rawId.toString());
    if (orderId == null) return;
    try {
      await context.read<ApiService>().initiatePayment(
        orderId: orderId,
        paymentMethod: method,
        phone: phone,
      );
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Ombi la malipo limetumwa kwenye simu yako.')),
        );
        await _loadOrders();
      }
    } catch (error) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(ApiService.formatError(error))),
        );
      }
    }
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

  String _statusText(String status) {
    switch (status) {
      case 'pending':
        return 'Inasubiri';
      case 'confirmed':
        return 'Imethibitishwa';
      case 'shipped':
        return 'Imetumwa';
      case 'delivered':
        return 'Imefika';
      case 'cancelled':
        return 'Imeghairiwa';
      default:
        return status;
    }
  }
}
