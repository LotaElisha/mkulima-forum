import 'package:flutter/material.dart';

class PaymentScreen extends StatefulWidget {
  final double amount;
  final String orderId;

  const PaymentScreen({
    super.key,
    required this.amount,
    required this.orderId,
  });

  @override
  State<PaymentScreen> createState() => _PaymentScreenState();
}

class _PaymentScreenState extends State<PaymentScreen> {
  String _selectedMethod = 'mpesa';
  final _phoneController = TextEditingController();
  bool _isProcessing = false;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Malipo'),
        backgroundColor: const Color(0xFF2E7D32),
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
                        color: Color(0xFF2E7D32),
                      ),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 24),
            const Text(
              'Chagua Njia ya Malipo:',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 16),
            _buildPaymentMethod(
              value: 'mpesa',
              title: 'M-Pesa',
              subtitle: 'Lipa kwa M-Pesa',
              icon: Icons.phone_android,
            ),
            _buildPaymentMethod(
              value: 'tigopesa',
              title: 'Tigo Pesa',
              subtitle: 'Lipa kwa Tigo Pesa',
              icon: Icons.phone_android,
            ),
            _buildPaymentMethod(
              value: 'escrow',
              title: 'Escrow (Salama)',
              subtitle: 'Pesa zinahifadhiwa hadi bidhaa ifike',
              icon: Icons.security,
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
                onPressed: _isProcessing
                    ? null
                    : () {
                        setState(() => _isProcessing = true);
                        // Process payment
                        Future.delayed(const Duration(seconds: 2), () {
                          setState(() => _isProcessing = false);
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(
                              content: Text('Ombi la malipo limetumwa'),
                            ),
                          );
                          Navigator.of(context).pop();
                        });
                      },
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF2E7D32),
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
        groupValue: _selectedMethod,
        onChanged: (v) => setState(() => _selectedMethod = v!),
        title: Text(title),
        subtitle: Text(subtitle),
        secondary: Icon(icon, color: const Color(0xFF2E7D32)),
      ),
    );
  }
}
