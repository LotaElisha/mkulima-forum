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

  @override
  void initState() {
    super.initState();
    _loadEscrows();
  }

  Future<void> _loadEscrows() async {
    try {
      final api = context.read<ApiService>();
      final response = await api.get('/escrow/my-escrows');
      setState(() {
        _escrows = response.data['escrows'] ?? [];
        _isLoading = false;
      });
    } catch (e) {
      setState(() => _isLoading = false);
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
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Icon(Icons.security, size: 48, color: Colors.white),
                        const SizedBox(height: 12),
                        const Text(
                          'Mkulima Escrow',
                          style: TextStyle(
                            fontSize: 24,
                            fontWeight: FontWeight.bold,
                            color: Colors.white,
                          ),
                        ),
                        const SizedBox(height: 8),
                        const Text(
                          'Linda pesa zako hadi bidhaa ikufikie',
                          style: TextStyle(color: Colors.white70, fontSize: 14),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 24),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text(
                        'Mikataba Yangu',
                        style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                      ),
                      TextButton.icon(
                        onPressed: () => _showCreateEscrowDialog(),
                        icon: const Icon(Icons.add),
                        label: const Text('Unda Mpya'),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
                  if (_escrows.isEmpty)
                    const Card(
                      child: Padding(
                        padding: EdgeInsets.all(24),
                        child: Center(
                          child: Column(
                            children: [
                              Icon(Icons.folder_open, size: 48, color: Colors.grey),
                              SizedBox(height: 12),
                              Text('Huna escrow yoyote'),
                            ],
                          ),
                        ),
                      ),
                    )
                  else
                    ..._escrows.map((escrow) => _EscrowCard(escrow: escrow)),
                ],
              ),
            ),
    );
  }

  void _showCreateEscrowDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Unda Escrow Mpya'),
        content: const Text(
          'Escrow inakulinda wewe na mwenza.\n\n'
          '1. Weka pesa kwenye escrow\n'
          '2. Muuzaji atuma bidhaa\n'
          '3. Ukiridhika, toa malipo\n'
          '4. Ikiwa kuna shida, wasiliana nasi',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Ghairi'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              _showEscrowForm();
            },
            child: const Text('Endelea'),
          ),
        ],
      ),
    );
  }

  void _showEscrowForm() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      builder: (context) => Padding(
        padding: EdgeInsets.only(
          bottom: MediaQuery.of(context).viewInsets.bottom,
          left: 16,
          right: 16,
          top: 16,
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Text(
              'Unda Escrow',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 16),
            TextFormField(
              decoration: const InputDecoration(
                labelText: 'ID ya Muuzaji',
                prefixIcon: Icon(Icons.person),
                border: OutlineInputBorder(),
              ),
            ),
            const SizedBox(height: 12),
            TextFormField(
              decoration: const InputDecoration(
                labelText: 'ID ya Bidhaa',
                prefixIcon: Icon(Icons.inventory),
                border: OutlineInputBorder(),
              ),
            ),
            const SizedBox(height: 12),
            TextFormField(
              keyboardType: TextInputType.number,
              decoration: const InputDecoration(
                labelText: 'Kiasi (TZS)',
                prefixIcon: Icon(Icons.money),
                border: OutlineInputBorder(),
              ),
            ),
            const SizedBox(height: 12),
            TextFormField(
              keyboardType: TextInputType.number,
              decoration: const InputDecoration(
                labelText: 'Siku za Uwasilishaji',
                prefixIcon: Icon(Icons.calendar_today),
                border: OutlineInputBorder(),
              ),
            ),
            const SizedBox(height: 24),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: () {
                  Navigator.pop(context);
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(content: Text('Escrow imeundwa')),
                  );
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: MkColors.primary,
                  foregroundColor: Colors.white,
                ),
                child: const Text('Unda Escrow'),
              ),
            ),
            const SizedBox(height: 16),
          ],
        ),
      ),
    );
  }
}

class _EscrowCard extends StatelessWidget {
  final dynamic escrow;

  const _EscrowCard({required this.escrow});

  Color _getStatusColor(String status) {
    switch (status) {
      case 'completed':
        return Colors.green;
      case 'in_escrow':
        return Colors.orange;
      case 'disputed':
        return Colors.red;
      default:
        return Colors.grey;
    }
  }

  @override
  Widget build(BuildContext context) {
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
                  escrow['id'],
                  style: const TextStyle(
                    fontWeight: FontWeight.bold,
                    fontSize: 16,
                  ),
                ),
                Chip(
                  label: Text(
                    escrow['status'].toUpperCase(),
                    style: const TextStyle(color: Colors.white, fontSize: 12),
                  ),
                  backgroundColor: _getStatusColor(escrow['status']),
                ),
              ],
            ),
            const SizedBox(height: 8),
            Text(
              escrow['product_name'],
              style: const TextStyle(fontSize: 15),
            ),
            const SizedBox(height: 4),
            Text(
              escrow['type'] == 'buying' ? 'Unanunua kutoka' : 'Unauza kwa',
              style: TextStyle(color: Colors.grey[600], fontSize: 13),
            ),
            Text(
              escrow['other_party'],
              style: const TextStyle(fontWeight: FontWeight.w500),
            ),
            const SizedBox(height: 12),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  'TZS ${escrow['amount'].toString()}',
                  style: const TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                    color: MkColors.primary,
                  ),
                ),
                if (escrow['status'] == 'in_escrow')
                  Row(
                    children: [
                      TextButton(
                        onPressed: () {},
                        child: const Text('Toa Malipo'),
                      ),
                      TextButton(
                        onPressed: () {},
                        child: const Text('Lalamika', style: TextStyle(color: Colors.red)),
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
