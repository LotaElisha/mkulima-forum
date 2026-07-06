import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../core/theme.dart';

class UpgradeScreen extends StatefulWidget {
  final String currentPlan;
  final String requiredPlan;
  final String serviceName;
  final List<String> benefits;
  final Widget targetScreen;

  const UpgradeScreen({
    super.key,
    required this.currentPlan,
    required this.requiredPlan,
    required this.serviceName,
    required this.benefits,
    required this.targetScreen,
  });

  @override
  State<UpgradeScreen> createState() => _UpgradeScreenState();
}

class _UpgradeScreenState extends State<UpgradeScreen> {
  bool _isProcessing = false;

  void _showCheckoutSheet() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => StatefulBuilder(
        builder: (context, setSheetState) => Container(
          decoration: const BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
          ),
          padding: EdgeInsets.only(
            bottom: MediaQuery.of(context).viewInsets.bottom + 24,
            left: 24,
            right: 24,
            top: 24,
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Center(
                child: Container(
                  width: 40,
                  height: 4,
                  decoration: BoxDecoration(
                    color: Colors.grey[300],
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: 24),
              Text(
                'Malipo ya Kifurushi',
                style: Theme.of(context).textTheme.titleLarge?.copyWith(
                      fontWeight: FontWeight.bold,
                    ),
              ),
              const SizedBox(height: 8),
              Text(
                'Boresha wasifu wako kwenda kifurushi cha ${widget.requiredPlan} ili kutumia huduma ya ${widget.serviceName}.',
                style: TextStyle(color: Colors.grey[600]),
              ),
              const SizedBox(height: 24),
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.grey[50],
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: Colors.grey[200]!),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      'Kifurushi cha ${widget.requiredPlan}',
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                    ),
                    Text(
                      _getPlanPrice(widget.requiredPlan),
                      style: const TextStyle(
                        color: MkColors.primary,
                        fontWeight: FontWeight.bold,
                        fontSize: 16,
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 24),
              const Text(
                'Chagua Njia ya Malipo',
                style: TextStyle(fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 12),
              _PaymentMethodTile(
                icon: Icons.phone_android,
                title: 'M-Pesa / Tigo Pesa / Airtel Money',
                selected: true,
                onTap: () {},
              ),
              _PaymentMethodTile(
                icon: Icons.credit_card,
                title: 'Kadi ya Benki (Visa/Mastercard)',
                selected: false,
                onTap: () {},
              ),
              const SizedBox(height: 24),
              SizedBox(
                width: double.infinity,
                height: 56,
                child: ElevatedButton(
                  onPressed: _isProcessing
                      ? null
                      : () async {
                          setSheetState(() => _isProcessing = true);
                          Navigator.of(context).pop(); // Close checkout sheet
                          await _processUpgrade();
                        },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: MkColors.primary,
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                  child: const Text('Kamilisha Malipo'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  String _getPlanPrice(String plan) {
    switch (plan.toLowerCase()) {
      case 'pro':
        return 'TSH 10,000 / Mwezi';
      case 'business':
        return 'TSH 25,000 / Mwezi';
      case 'enterprise':
        return 'TSH 50,000 / Mwezi';
      default:
        return 'Bure';
    }
  }

  Future<void> _processUpgrade() async {
    setState(() => _isProcessing = true);

    // Simulate network latency
    await Future.delayed(const Duration(milliseconds: 1500));

    if (!mounted) return;

    final auth = Provider.of<AuthProvider>(context, listen: false);
    await auth.setSubscriptionPlan(widget.requiredPlan);

    setState(() => _isProcessing = false);

    // Show Success Dialog
    if (mounted) {
      await showDialog(
        context: context,
        barrierDismissible: false,
        builder: (context) => AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const SizedBox(height: 16),
              const Icon(Icons.check_circle, size: 72, color: MkColors.primary),
              const SizedBox(height: 24),
              const Text(
                'Imefanikiwa!',
                style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 8),
              Text(
                'Wasifu wako sasa umefanikiwa kuboreshwa kwenda kifurushi cha ${widget.requiredPlan}!',
                textAlign: TextAlign.center,
                style: TextStyle(color: Colors.grey[600]),
              ),
              const SizedBox(height: 24),
              SizedBox(
                width: double.infinity,
                height: 48,
                child: ElevatedButton(
                  onPressed: () => Navigator.of(context).pop(),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: MkColors.primary,
                    foregroundColor: Colors.white,
                  ),
                  child: const Text('Fungua Huduma'),
                ),
              ),
            ],
          ),
        ),
      );

      // Redirect user directly to target screen
      if (mounted) {
        Navigator.of(context).pushReplacement(
          MaterialPageRoute(builder: (_) => widget.targetScreen),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final requiredColor = _getPlanColor(widget.requiredPlan);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Boresha Kifurushi'),
        backgroundColor: requiredColor,
        foregroundColor: Colors.white,
      ),
      body: _isProcessing
          ? const Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              padding: const EdgeInsets.all(24),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Hero Header Card
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(24),
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        colors: [requiredColor, requiredColor.withValues(alpha: 0.8)],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                      borderRadius: BorderRadius.circular(20),
                      boxShadow: [
                        BoxShadow(
                          color: requiredColor.withValues(alpha: 0.3),
                          blurRadius: 12,
                          offset: const Offset(0, 6),
                        )
                      ],
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                          decoration: BoxDecoration(
                            color: Colors.white.withValues(alpha: 0.2),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Text(
                            'KIFURUSHI KINACHOHITAJIKA: ${widget.requiredPlan.toUpperCase()}',
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 12,
                              fontWeight: FontWeight.bold,
                              letterSpacing: 1.1,
                            ),
                          ),
                        ),
                        const SizedBox(height: 16),
                        Text(
                          widget.serviceName,
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 28,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 8),
                        const Text(
                          'Boresha wasifu wako ili kupata ruhusa ya kutumia huduma hii shambani kwako.',
                          style: TextStyle(color: Colors.white70, fontSize: 14),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 32),

                  // Plans Comparison Row
                  Row(
                    children: [
                      Expanded(
                        child: _PlanStatusTile(
                          title: 'Kifurushi Chako',
                          planName: widget.currentPlan,
                          isActive: true,
                        ),
                      ),
                      const Padding(
                        padding: EdgeInsets.symmetric(horizontal: 8),
                        child: Icon(Icons.arrow_forward, color: Colors.grey),
                      ),
                      Expanded(
                        child: _PlanStatusTile(
                          title: 'Utapata',
                          planName: widget.requiredPlan,
                          isActive: false,
                          highlightColor: requiredColor,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 32),

                  // Benefits section
                  const Text(
                    'Faida za Kifurushi Hiki:',
                    style: TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 16),

                  ...widget.benefits.map((benefit) => Padding(
                        padding: const EdgeInsets.only(bottom: 12),
                        child: Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Icon(Icons.check_circle_outline, color: requiredColor, size: 22),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Text(
                                benefit,
                                style: const TextStyle(fontSize: 15, height: 1.3),
                              ),
                            ),
                          ],
                        ),
                      )),

                  const SizedBox(height: 40),

                  // Upgrade Action Buttons
                  SizedBox(
                    width: double.infinity,
                    height: 56,
                    child: ElevatedButton(
                      onPressed: _showCheckoutSheet,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: requiredColor,
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                        elevation: 4,
                      ),
                      child: Text(
                        'Jiunge na ${widget.requiredPlan} Sasa',
                        style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                      ),
                    ),
                  ),
                  const SizedBox(height: 12),
                  SizedBox(
                    width: double.infinity,
                    height: 48,
                    child: OutlinedButton(
                      onPressed: () => Navigator.of(context).pop(),
                      style: OutlinedButton.styleFrom(
                        side: BorderSide(color: Colors.grey[350]!),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                      ),
                      child: const Text('Rudi Nyuma', style: TextStyle(color: Colors.grey)),
                    ),
                  ),
                ],
              ),
            ),
    );
  }

  Color _getPlanColor(String plan) {
    switch (plan.toLowerCase()) {
      case 'pro':
        return MkColors.primary;
      case 'business':
        return MkColors.accent;
      case 'enterprise':
        return Colors.deepPurple;
      default:
        return Colors.grey;
    }
  }
}

class _PlanStatusTile extends StatelessWidget {
  final String title;
  final String planName;
  final bool isActive;
  final Color? highlightColor;

  const _PlanStatusTile({
    required this.title,
    required this.planName,
    required this.isActive,
    this.highlightColor,
  });

  @override
  Widget build(BuildContext context) {
    final finalColor = highlightColor ?? Colors.grey[700]!;

    return Container(
      padding: const EdgeInsets.symmetric(vertical: 16, horizontal: 12),
      decoration: BoxDecoration(
        color: isActive ? Colors.grey[100] : finalColor.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: isActive ? Colors.grey[300]! : finalColor,
          width: isActive ? 1 : 2,
        ),
      ),
      child: Column(
        children: [
          Text(
            title,
            style: TextStyle(
              fontSize: 11,
              color: isActive ? Colors.grey[600] : finalColor,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            planName,
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.bold,
              color: isActive ? Colors.black87 : finalColor,
            ),
          ),
        ],
      ),
    );
  }
}

class _PaymentMethodTile extends StatelessWidget {
  final IconData icon;
  final String title;
  final bool selected;
  final VoidCallback onTap;

  const _PaymentMethodTile({
    required this.icon,
    required this.title,
    required this.selected,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      elevation: 0,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
        side: BorderSide(
          color: selected ? MkColors.primary : Colors.grey[300]!,
          width: selected ? 2 : 1,
        ),
      ),
      margin: const EdgeInsets.only(bottom: 8),
      child: ListTile(
        leading: Icon(icon, color: selected ? MkColors.primary : Colors.grey),
        title: Text(
          title,
          style: TextStyle(
            fontSize: 14,
            fontWeight: selected ? FontWeight.bold : FontWeight.normal,
          ),
        ),
        trailing: selected
            ? const Icon(Icons.radio_button_checked, color: MkColors.primary)
            : const Icon(Icons.radio_button_off, color: Colors.grey),
        onTap: onTap,
      ),
    );
  }
}
