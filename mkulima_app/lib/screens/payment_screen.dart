import '../core/theme.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/cart_provider.dart';
import '../services/api_service.dart';

class PaymentScreen extends StatefulWidget {
  final double amount;

  const PaymentScreen({super.key, required this.amount});

  @override
  State<PaymentScreen> createState() => _PaymentScreenState();
}

class _PaymentScreenState extends State<PaymentScreen> {
  String _selectedMethod = 'mpesa';
  final _phoneController = TextEditingController();
  final _regionController = TextEditingController();
  final _districtController = TextEditingController();
  final _wardController = TextEditingController();
  bool _isProcessing = false;
  int? _createdOrderId;
  double? _createdTotal;

  @override
  void dispose() {
    _phoneController.dispose();
    _regionController.dispose();
    _districtController.dispose();
    _wardController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Malipo'),
        backgroundColor: MkColors.primary,
        foregroundColor: Colors.white,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  children: [
                    const Text(
                      'Jumla ya Kulipa',
                      style: TextStyle(fontSize: 16),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      'TSh ${widget.amount.toStringAsFixed(0)}',
                      style: const TextStyle(
                        fontSize: 32,
                        fontWeight: FontWeight.bold,
                        color: MkColors.primary,
                      ),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 24),
            const Text(
              'Anwani ya Utumiaji:',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: _regionController,
              decoration: InputDecoration(
                labelText: 'Mkoa',
                hintText: 'Mfano: Dar es Salaam',
                prefixIcon: const Icon(Icons.location_city),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: _districtController,
              decoration: InputDecoration(
                labelText: 'Wilaya',
                hintText: 'Mfano: Ilala',
                prefixIcon: const Icon(Icons.map),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: _wardController,
              decoration: InputDecoration(
                labelText: 'Kata',
                hintText: 'Mfano: Kariakoo',
                prefixIcon: const Icon(Icons.place),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
            ),
            const SizedBox(height: 24),
            const Text(
              'Chagua Njia ya Malipo:',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 16),
            RadioGroup<String>(
              groupValue: _selectedMethod,
              onChanged: (v) => setState(() => _selectedMethod = v!),
              child: Column(
                children: [
                  _buildPaymentMethod(
                    value: 'mpesa',
                    title: 'M-Pesa',
                    subtitle: 'Utapokea ombi la malipo kwenye simu',
                    icon: Icons.phone_android,
                  ),
                  _buildPaymentMethod(
                    value: 'tigopesa',
                    title: 'Tigo Pesa',
                    subtitle: 'Utapokea ombi la malipo kwenye simu',
                    icon: Icons.phone_android,
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),
            TextField(
              controller: _phoneController,
              keyboardType: TextInputType.phone,
              decoration: InputDecoration(
                labelText: 'Namba ya Simu ya Malipo',
                hintText: '2557XXXXXXXX',
                prefixIcon: const Icon(Icons.phone),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
            ),
            const SizedBox(height: 24),
            SizedBox(
              width: double.infinity,
              height: 50,
              child: ElevatedButton(
                onPressed: _isProcessing ? null : _processPayment,
                style: ElevatedButton.styleFrom(
                  backgroundColor: MkColors.primary,
                  foregroundColor: Colors.white,
                ),
                child: _isProcessing
                    ? const SizedBox(
                        width: 20,
                        height: 20,
                        child: CircularProgressIndicator(
                          color: Colors.white,
                          strokeWidth: 2,
                        ),
                      )
                    : const Text('Thibitisha Malipo'),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _processPayment() async {
    final phone = _phoneController.text.trim();
    final region = _regionController.text.trim();
    final district = _districtController.text.trim();
    final ward = _wardController.text.trim();

    if (region.isEmpty || district.isEmpty || ward.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Tafadhali jaza anwani yote')),
      );
      return;
    }

    if (!RegExp(r'^255[0-9]{9}$').hasMatch(phone)) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Weka namba ya tarakimu 12 inayoanza na 255'),
        ),
      );
      return;
    }

    setState(() => _isProcessing = true);

    try {
      final api = Provider.of<ApiService>(context, listen: false);
      final cart = Provider.of<CartProvider>(context, listen: false);

      // Build order items from cart
      final items = cart.items.map((item) {
        return {'product_uuid': item.product.id, 'quantity': item.quantity};
      }).toList();

      final deliveryPhone = phone.isNotEmpty ? phone : '255714524007';

      Map<String, dynamic>? order;
      if (_createdOrderId == null) {
        order = await api.createOrder({
          'items': items,
          'delivery_address': {
            'region': region,
            'district': district,
            'ward': ward,
            'street': '',
          },
          'delivery_phone': deliveryPhone,
          'notes': 'Order placed via mobile app',
        });
        final rawOrderId = order['id'];
        _createdOrderId = rawOrderId is int
            ? rawOrderId
            : int.tryParse(rawOrderId.toString());
        _createdTotal = double.tryParse(order['total'].toString());
        cart.clearCart();
      }
      final orderId = _createdOrderId;
      if (orderId == null) {
        throw StateError('Seva haikurudisha namba sahihi ya oda.');
      }

      await api.initiatePayment(
        orderId: orderId,
        paymentMethod: _selectedMethod,
        phone: phone,
      );

      final chargedAmount = _createdTotal ?? widget.amount;

      if (mounted) {
        setState(() => _isProcessing = false);
        showDialog(
          context: context,
          barrierDismissible: false,
          builder: (_) => AlertDialog(
            title: const Row(
              children: [
                Icon(Icons.check_circle, color: Colors.green),
                SizedBox(width: 8),
                Text('Umefanikiwa!'),
              ],
            ),
            content: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Oda imeundwa na ombi la malipo limetumwa kwenye simu yako.',
                ),
                const SizedBox(height: 8),
                Text(
                  'Thibitisha malipo ya TSh ${chargedAmount.toStringAsFixed(0)} kwenye simu. Escrow itashikilia fedha baada ya mtoa huduma kuthibitisha malipo.',
                  style: const TextStyle(fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 8),
                const Text(
                  'Usifunge programu hadi uone uthibitisho wa mtoa huduma.',
                  style: TextStyle(fontSize: 13),
                ),
              ],
            ),
            actions: [
              TextButton(
                onPressed: () {
                  Navigator.of(context).pop();
                  Navigator.of(context).pop();
                  Navigator.of(context).pop();
                },
                child: const Text('OK'),
              ),
            ],
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isProcessing = false);
        ScaffoldMessenger.of(
          context,
        ).showSnackBar(
          SnackBar(
            content: Text(
              _createdOrderId == null
                  ? 'Kosa: ${ApiService.formatError(e)}'
                  : 'Oda imehifadhiwa lakini malipo hayakuanzishwa. Jaribu tena: ${ApiService.formatError(e)}',
            ),
          ),
        );
      }
    }
  }

  Widget _buildPaymentMethod({
    required String value,
    required String title,
    required String subtitle,
    required IconData icon,
  }) {
    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      child: RadioListTile<String>(
        value: value,
        title: Text(title),
        subtitle: Text(subtitle),
        secondary: Icon(icon, color: MkColors.primary),
      ),
    );
  }
}
