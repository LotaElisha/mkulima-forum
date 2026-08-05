import 'package:flutter/material.dart';
import '../core/theme.dart';
import 'package:provider/provider.dart';
import '../services/api_service.dart';
import '../providers/auth_provider.dart';
import 'login_modal.dart';

class YieldScreen extends StatefulWidget {
  const YieldScreen({super.key});

  @override
  State<YieldScreen> createState() => _YieldScreenState();
}

class _YieldScreenState extends State<YieldScreen> {
  final _formKey = GlobalKey<FormState>();
  String _cropType = 'mahindi';
  final _acresController = TextEditingController();
  bool _isLoading = false;
  Map<String, dynamic>? _result;

  final List<Map<String, String>> _crops = [
    {'value': 'mahindi', 'label': 'Mahindi'},
    {'value': 'mpunga', 'label': 'Mpunga'},
    {'value': 'maharage', 'label': 'Maharage'},
    {'value': 'alizeti', 'label': 'Alizeti'},
    {'value': 'miwa', 'label': 'Miwa'},
    {'value': 'kahawa', 'label': 'Kahawa'},
    {'value': 'chai', 'label': 'Chai'},
    {'value': 'cassava', 'label': 'Mihogo'},
  ];

  Future<void> _estimate() async {
    if (!(_formKey.currentState?.validate() ?? false)) return;

    setState(() => _isLoading = true);
    try {
      final api = context.read<ApiService>();
      final response = await api.post(
        '/yield/estimate',
        data: {
          'crop_type': _cropType,
          'farm_size_acres': double.parse(_acresController.text),
        },
      );
      setState(() {
        _result = response.data;
        _isLoading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() => _isLoading = false);
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(SnackBar(content: Text('Hitilafu: $e')));
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context);

    if (!auth.isAuthenticated) {
      return Scaffold(
        appBar: AppBar(
          title: const Text('Kadiria Mavuno'),
          backgroundColor: MkColors.primary,
          foregroundColor: Colors.white,
        ),
        body: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(Icons.calculate, size: 64, color: Colors.grey),
              const SizedBox(height: 16),
              const Text('Ingia kutumia kadiria mavuno'),
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
        title: const Text('Kadiria Mavuno'),
        backgroundColor: MkColors.primary,
        foregroundColor: Colors.white,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [Color(0xFFFF9800), Color(0xFFF57C00)],
                ),
                borderRadius: BorderRadius.circular(16),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Icon(Icons.calculate, size: 48, color: Colors.white),
                  const SizedBox(height: 12),
                  const Text(
                    'Kadiria Mavuno yako',
                    style: TextStyle(
                      fontSize: 24,
                      fontWeight: FontWeight.bold,
                      color: Colors.white,
                    ),
                  ),
                  const SizedBox(height: 8),
                  const Text(
                    'Makadirio ya wastani wa kanda kwa mpango wa shamba lako',
                    style: TextStyle(color: Colors.white70, fontSize: 14),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),
            Form(
              key: _formKey,
              child: Column(
                children: [
                  DropdownButtonFormField<String>(
                    initialValue: _cropType,
                    decoration: const InputDecoration(
                      labelText: 'Aina ya Mazao',
                      prefixIcon: Icon(Icons.grass),
                      border: OutlineInputBorder(),
                    ),
                    items: _crops.map((crop) {
                      return DropdownMenuItem(
                        value: crop['value'],
                        child: Text(crop['label']!),
                      );
                    }).toList(),
                    onChanged: (value) => setState(() => _cropType = value!),
                  ),
                  const SizedBox(height: 16),
                  TextFormField(
                    controller: _acresController,
                    keyboardType: TextInputType.number,
                    decoration: const InputDecoration(
                      labelText: 'Ukubwa wa Shamba (Acres)',
                      prefixIcon: Icon(Icons.square_foot),
                      border: OutlineInputBorder(),
                    ),
                    validator: (v) =>
                        v?.isEmpty ?? true ? 'Tafadhali jaza' : null,
                  ),
                  const SizedBox(height: 24),
                  SizedBox(
                    width: double.infinity,
                    height: 50,
                    child: ElevatedButton(
                      onPressed: _isLoading ? null : _estimate,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: MkColors.primary,
                        foregroundColor: Colors.white,
                      ),
                      child: _isLoading
                          ? const CircularProgressIndicator(color: Colors.white)
                          : const Text(
                              'Kadiria',
                              style: TextStyle(fontSize: 16),
                            ),
                    ),
                  ),
                ],
              ),
            ),
            if (_result != null) ...[
              const SizedBox(height: 32),
              _buildResultCard(),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildResultCard() {
    final yield = _result!['estimated_yield'] as Map<String, dynamic>;
    final revenue = _result!['estimated_revenue'] as Map<String, dynamic>;
    final disclaimer = _result!['disclaimer']?.toString();

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Matokeo ya Ukadiriaji',
          style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
        ),
        const SizedBox(height: 16),
        Card(
          color: MkColors.primary.withValues(alpha: 0.1),
          child: Padding(
            padding: const EdgeInsets.all(20),
            child: Column(
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceAround,
                  children: [
                    _ResultItem(
                      icon: Icons.inventory,
                      value: '${yield['total']}',
                      label: '${yield['unit']} za mavuno',
                      color: Colors.green,
                    ),
                    _ResultItem(
                      icon: Icons.attach_money,
                      value: '${revenue['total']}',
                      label: 'TZS mapato',
                      color: Colors.orange,
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
        if (disclaimer != null) ...[
          const SizedBox(height: 16),
          Card(
            color: Colors.amber[50],
            child: Padding(
              padding: const EdgeInsets.all(12),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Icon(Icons.info_outline, color: Colors.amber[800], size: 20),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      disclaimer,
                      style: const TextStyle(fontSize: 13),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ],
    );
  }
}

class _ResultItem extends StatelessWidget {
  final IconData icon;
  final String value;
  final String label;
  final Color color;

  const _ResultItem({
    required this.icon,
    required this.value,
    required this.label,
    required this.color,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Icon(icon, size: 32, color: color),
        const SizedBox(height: 8),
        Text(
          value,
          style: TextStyle(
            fontSize: 24,
            fontWeight: FontWeight.bold,
            color: color,
          ),
        ),
        Text(label, style: TextStyle(color: Colors.grey[600])),
      ],
    );
  }
}
