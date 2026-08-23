import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../services/api_service.dart';
import '../providers/auth_provider.dart';

class WalletScreen extends StatefulWidget {
  const WalletScreen({super.key});

  @override
  State<WalletScreen> createState() => _WalletScreenState();
}

class _WalletScreenState extends State<WalletScreen> {
  Map<String, dynamic>? _balance;
  List<dynamic> _transactions = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    try {
      final api = Provider.of<ApiService>(context, listen: false);
      final balance = await api.getWalletBalance();
      final transactions = await api.getWalletTransactions();
      if (!mounted) return;
      setState(() {
        _balance = balance;
        _transactions = transactions;
        _isLoading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context);

    if (auth.user == null) {
      return Scaffold(
        appBar: AppBar(
          title: const Text('Mkulima Pay'),
          backgroundColor: const Color(0xFF2E7D32),
          foregroundColor: Colors.white,
        ),
        body: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(
                Icons.account_balance_wallet,
                size: 64,
                color: Colors.grey,
              ),
              const SizedBox(height: 16),
              const Text('Tafadhali ingia kwanza'),
              const SizedBox(height: 16),
              ElevatedButton(
                onPressed: () => AuthProvider.requireAuth(context),
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
        title: const Text('Mkulima Pay'),
        backgroundColor: const Color(0xFF2E7D32),
        foregroundColor: Colors.white,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _loadData,
              child: SingleChildScrollView(
                physics: const AlwaysScrollableScrollPhysics(),
                child: Column(
                  children: [
                    // Balance Card
                    Container(
                      margin: const EdgeInsets.all(16),
                      padding: const EdgeInsets.all(24),
                      decoration: BoxDecoration(
                        gradient: const LinearGradient(
                          colors: [Color(0xFF2E7D32), Color(0xFF1B5E20)],
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                        ),
                        borderRadius: BorderRadius.circular(20),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.green.withValues(alpha: 0.3),
                            blurRadius: 10,
                            offset: const Offset(0, 5),
                          ),
                        ],
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'Salio Lako',
                            style: TextStyle(
                              color: Colors.white70,
                              fontSize: 16,
                            ),
                          ),
                          const SizedBox(height: 8),
                          Text(
                            'TZS ${_balance?['balance'] ?? '0.00'}',
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 36,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          const SizedBox(height: 16),
                          Row(
                            children: [
                              Expanded(
                                child: _ActionButton(
                                  icon: Icons.add,
                                  label: 'Weka Pesa',
                                  onTap: () => _showDepositDialog(),
                                ),
                              ),
                              const SizedBox(width: 12),
                              Expanded(
                                child: _ActionButton(
                                  icon: Icons.send,
                                  label: 'Tuma',
                                  onTap: () => _showTransferDialog(),
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),

                    // Transactions
                    const Padding(
                      padding: EdgeInsets.symmetric(horizontal: 16),
                      child: Align(
                        alignment: Alignment.centerLeft,
                        child: Text(
                          'Miamala Yangu',
                          style: TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(height: 8),
                    if (_transactions.isEmpty)
                      const Padding(
                        padding: EdgeInsets.all(32),
                        child: Center(
                          child: Text(
                            'Huna miamala yoyote',
                            style: TextStyle(color: Colors.grey),
                          ),
                        ),
                      )
                    else
                      ListView.builder(
                        shrinkWrap: true,
                        physics: const NeverScrollableScrollPhysics(),
                        itemCount: _transactions.length,
                        itemBuilder: (context, index) {
                          final tx = _transactions[index];
                          final isIncoming = tx['amount'] > 0;
                          return ListTile(
                            leading: CircleAvatar(
                              backgroundColor: isIncoming
                                  ? Colors.green[100]
                                  : Colors.red[100],
                              child: Icon(
                                isIncoming
                                    ? Icons.arrow_downward
                                    : Icons.arrow_upward,
                                color: isIncoming ? Colors.green : Colors.red,
                              ),
                            ),
                            title: Text(tx['description'] ?? 'Transaction'),
                            subtitle: Text(tx['created_at'] ?? ''),
                            trailing: Text(
                              '${isIncoming ? '+' : ''}TZS ${tx['amount']}',
                              style: TextStyle(
                                color: isIncoming ? Colors.green : Colors.red,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          );
                        },
                      ),
                  ],
                ),
              ),
            ),
    );
  }

  void _showDepositDialog() {
    showDialog(
      context: context,
      builder: (dialogContext) => AlertDialog(
        title: const Text('Weka Pesa'),
        content: const Text(
          'Uwekaji pesa moja kwa moja kwenye pochi bado haujawashwa. '
          'Malipo ya ununuzi hutumwa kwa M-Pesa au Tigo Pesa na '
          'huthibitishwa na mtoa huduma kabla ya kuonekana kama yamelipwa.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(dialogContext),
            child: const Text('Nimeelewa'),
          ),
        ],
      ),
    );
  }

  void _showTransferDialog() {
    final phoneController = TextEditingController();
    final amountController = TextEditingController();

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Tuma Pesa'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            TextField(
              controller: phoneController,
              keyboardType: TextInputType.phone,
              decoration: const InputDecoration(
                labelText: 'Namba ya Mpokeaji',
                hintText: '2557XXXXXXXX',
              ),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: amountController,
              keyboardType: TextInputType.number,
              decoration: const InputDecoration(
                labelText: 'Kiasi',
                hintText: '5000',
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Ghairi'),
          ),
          ElevatedButton(
            onPressed: () async {
              final phone = phoneController.text.trim();
              final amount = double.tryParse(amountController.text.trim());
              if (!RegExp(r'^255[0-9]{9}$').hasMatch(phone) ||
                  amount == null ||
                  amount < 100) {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(
                    content: Text('Weka namba 255XXXXXXXXX na kiasi cha angalau TZS 100.'),
                  ),
                );
                return;
              }
              Navigator.pop(context);
              try {
                final api = Provider.of<ApiService>(context, listen: false);
                await api.transfer(phone, amount);
                _loadData();
                if (context.mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(content: Text('Pesa imetumwa kikamilifu')),
                  );
                }
              } catch (e) {
                if (context.mounted) {
                  ScaffoldMessenger.of(
                    context,
                  ).showSnackBar(SnackBar(content: Text('Kosa: $e')));
                }
              }
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF2E7D32),
            ),
            child: const Text('Tuma'),
          ),
        ],
      ),
    );
  }
}

class _ActionButton extends StatelessWidget {
  final IconData icon;
  final String label;
  final VoidCallback onTap;

  const _ActionButton({
    required this.icon,
    required this.label,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 12),
        decoration: BoxDecoration(
          color: Colors.white.withValues(alpha: 0.2),
          borderRadius: BorderRadius.circular(12),
        ),
        child: Column(
          children: [
            Icon(icon, color: Colors.white),
            const SizedBox(height: 4),
            Text(
              label,
              style: const TextStyle(color: Colors.white, fontSize: 12),
            ),
          ],
        ),
      ),
    );
  }
}
