import '../core/theme.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../services/api_service.dart';
import '../providers/auth_provider.dart';
import 'login_modal.dart';

class EscrowScreen extends StatefulWidget {
  const EscrowScreen({super.key});

  @override
  State<EscrowScreen> createState() => _EscrowScreenState();
}

class _EscrowScreenState extends State<EscrowScreen> {
  List<dynamic> _escrows = [];
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadEscrows();
  }

  Future<void> _loadEscrows() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });
    try {
      final api = context.read<ApiService>();
      // Real escrow API — escrows are created automatically when an order
      // is paid through /payments/initiate.
      final response = await api.get('/payments/escrows');
      if (!mounted) return;
      setState(() {
        _escrows = response.data['data'] ?? [];
        _isLoading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _isLoading = false;
        _error = 'Imeshindikana kupakia escrow. Jaribu tena.';
      });
    }
  }

  Future<void> _confirmDelivery(String uuid) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Thibitisha Upokeaji'),
        content: const Text(
          'Umepokea bidhaa na umeridhika? Malipo yatatolewa kwa muuzaji. '
          'Hatua hii haiwezi kurudishwa.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Ghairi'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Ndiyo, Nimepokea'),
          ),
        ],
      ),
    );
    if (confirmed != true || !mounted) return;

    try {
      final api = context.read<ApiService>();
      await api.post('/payments/escrows/$uuid/confirm');
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Malipo yametolewa kwa muuzaji')),
      );
      _loadEscrows();
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Imeshindikana. Jaribu tena.')),
      );
    }
  }

  Future<void> _requestRefund(String uuid) async {
    final controller = TextEditingController();
    final reason = await showDialog<String>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Omba Kurudishiwa Pesa'),
        content: TextField(
          controller: controller,
          maxLines: 3,
          decoration: const InputDecoration(
            labelText: 'Eleza tatizo',
            border: OutlineInputBorder(),
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Ghairi'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, controller.text.trim()),
            child: const Text('Wasilisha'),
          ),
        ],
      ),
    );
    if (reason == null || reason.isEmpty || !mounted) return;

    try {
      final api = context.read<ApiService>();
      await api.post('/payments/escrows/$uuid/refund', data: {'reason': reason});
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Ombi la refund limewasilishwa')),
      );
      _loadEscrows();
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Imeshindikana. Jaribu tena.')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context);

    if (!auth.isAuthenticated) {
      return Scaffold(
        appBar: AppBar(
          title: const Text('Mkulima Escrow'),
          backgroundColor: MkColors.primary,
          foregroundColor: Colors.white,
        ),
        body: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(Icons.security, size: 64, color: Colors.grey),
              const SizedBox(height: 16),
              const Text('Ingia kuona escrow zako'),
              const SizedBox(height: 16),
              ElevatedButton(
                onPressed: () => LoginModal.show(context),
                child: const Text('Ingia'),
              ),
            ],
          ),
        ),
      );
    }

    return Scaffold(
      appBar: AppBar(
        title: const Text('Mkulima Escrow'),
        backgroundColor: MkColors.primary,
        foregroundColor: Colors.white,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _loadEscrows,
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  Container(
                    padding: const EdgeInsets.all(20),
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(
                        colors: [Color(0xFF4CAF50), MkColors.primary],
                      ),
                      borderRadius: BorderRadius.circular(16),
                    ),
                    child: const Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Icon(Icons.security, size: 48, color: Colors.white),
                        SizedBox(height: 12),
                        Text(
                          'Mkulima Escrow',
                          style: TextStyle(
                            fontSize: 24,
                            fontWeight: FontWeight.bold,
                            color: Colors.white,
                          ),
                        ),
                        SizedBox(height: 8),
                        Text(
                          'Pesa zako hulindwa hadi bidhaa ikufikie. '
                          'Escrow huundwa moja kwa moja unapolipia oda.',
                          style: TextStyle(color: Colors.white70, fontSize: 14),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 24),
                  const Text(
                    'Mikataba Yangu',
                    style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 16),
                  if (_error != null)
                    Card(
                      child: Padding(
                        padding: const EdgeInsets.all(24),
                        child: Center(
                          child: Column(
                            children: [
                              const Icon(Icons.cloud_off,
                                  size: 48, color: Colors.grey),
                              const SizedBox(height: 12),
                              Text(_error!),
                              const SizedBox(height: 12),
                              TextButton(
                                onPressed: _loadEscrows,
                                child: const Text('Jaribu Tena'),
                              ),
                            ],
                          ),
                        ),
                      ),
                    )
                  else if (_escrows.isEmpty)
                    const Card(
                      child: Padding(
                        padding: EdgeInsets.all(24),
                        child: Center(
                          child: Column(
                            children: [
                              Icon(Icons.folder_open,
                                  size: 48, color: Colors.grey),
                              SizedBox(height: 12),
                              Text('Huna escrow yoyote'),
                              SizedBox(height: 4),
                              Text(
                                'Escrow huundwa unapolipia oda sokoni.',
                                style:
                                    TextStyle(fontSize: 12, color: Colors.grey),
                              ),
                            ],
                          ),
                        ),
                      ),
                    )
                  else
                    ..._escrows.map((escrow) => _EscrowCard(
                          escrow: escrow,
                          onConfirm: _confirmDelivery,
                          onRefund: _requestRefund,
                        )),
                ],
              ),
            ),
    );
  }
}

class _EscrowCard extends StatelessWidget {
  final dynamic escrow;
  final void Function(String uuid) onConfirm;
  final void Function(String uuid) onRefund;

  const _EscrowCard({
    required this.escrow,
    required this.onConfirm,
    required this.onRefund,
  });

  Color _statusColor(String status) {
    switch (status) {
      case 'released':
      case 'finalized':
        return Colors.green;
      case 'held':
        return Colors.orange;
      case 'pending':
        return Colors.blueGrey;
      case 'disputed':
      case 'failed':
        return Colors.red;
      case 'refunded':
        return Colors.blue;
      default:
        return Colors.grey;
    }
  }

  String _statusLabel(String status) {
    switch (status) {
      case 'pending':
        return 'INASUBIRI MALIPO';
      case 'held':
        return 'PESA IMESHIKILIWA';
      case 'released':
        return 'IMETOLEWA';
      case 'finalized':
        return 'IMEKAMILIKA';
      case 'refunded':
        return 'IMERUDISHWA';
      case 'disputed':
        return 'INA MGOGORO';
      case 'failed':
        return 'IMESHINDIKANA';
      default:
        return status.toUpperCase();
    }
  }

  @override
  Widget build(BuildContext context) {
    final status = (escrow['status'] ?? '').toString();
    final uuid = (escrow['uuid'] ?? '').toString();
    final reference =
        (escrow['reference'] ?? (uuid.length >= 8 ? uuid.substring(0, 8) : uuid))
            .toString();
    final isBuying = escrow['direction'] == 'buying';
    final amount = escrow['amount']?.toString() ?? '0';
    final currency = escrow['currency']?.toString() ?? 'TZS';

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
                Flexible(
                  child: Text(
                    reference,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      fontWeight: FontWeight.bold,
                      fontSize: 16,
                    ),
                  ),
                ),
                Chip(
                  label: Text(
                    _statusLabel(status),
                    style: const TextStyle(color: Colors.white, fontSize: 11),
                  ),
                  backgroundColor: _statusColor(status),
                ),
              ],
            ),
            const SizedBox(height: 4),
            Text(
              isBuying ? 'Unanunua' : 'Unauza',
              style: TextStyle(color: Colors.grey[600], fontSize: 13),
            ),
            const SizedBox(height: 12),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  '$currency $amount',
                  style: const TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                    color: MkColors.primary,
                  ),
                ),
                if (status == 'held' && isBuying)
                  Row(
                    children: [
                      TextButton(
                        onPressed: () => onConfirm(uuid),
                        child: const Text('Nimepokea'),
                      ),
                      TextButton(
                        onPressed: () => onRefund(uuid),
                        child: const Text('Omba Refund',
                            style: TextStyle(color: Colors.red)),
                      ),
                    ],
                  ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
