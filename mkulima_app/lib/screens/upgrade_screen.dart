import 'package:flutter/material.dart';
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
  void _showCheckoutSheet() {
    showModalBottomSheet<void>(
      context: context,
      builder: (context) => SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(Icons.info_outline, size: 48, color: MkColors.accent),
              const SizedBox(height: 16),
              const Text(
                'Malipo ya vifurushi bado hayajawashwa',
                style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 10),
              Text(
                'Hatutatoza ${_getPlanPrice(widget.requiredPlan)} mpaka huduma ya usajili wa kifurushi na uthibitisho wa malipo ikamilike.',
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 20),
              FilledButton(
                onPressed: () => Navigator.pop(context),
                child: const Text('Nimeelewa'),
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

  @override
  Widget build(BuildContext context) {
    final requiredColor = _getPlanColor(widget.requiredPlan);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Boresha Kifurushi'),
        backgroundColor: requiredColor,
        foregroundColor: Colors.white,
      ),
      body: SingleChildScrollView(
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
                  ),
                ],
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 12,
                      vertical: 6,
                    ),
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
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 16),

            ...widget.benefits.map(
              (benefit) => Padding(
                padding: const EdgeInsets.only(bottom: 12),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Icon(
                      Icons.check_circle_outline,
                      color: requiredColor,
                      size: 22,
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Text(
                        benefit,
                        style: const TextStyle(fontSize: 15, height: 1.3),
                      ),
                    ),
                  ],
                ),
              ),
            ),

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
                  style: const TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                  ),
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
                child: const Text(
                  'Rudi Nyuma',
                  style: TextStyle(color: Colors.grey),
                ),
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
