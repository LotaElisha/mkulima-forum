import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../core/theme.dart';

class LoginModal extends StatefulWidget {
  final String? action;
  final VoidCallback? onLoginSuccess;

  const LoginModal({
    super.key,
    this.action,
    this.onLoginSuccess,
  });

  @override
  State<LoginModal> createState() => _LoginModalState();

  static Future<bool> show(BuildContext context, {String? action}) async {
    final result = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => LoginModal(action: action),
    );
    return result ?? false;
  }
}

class _LoginModalState extends State<LoginModal> {
  final _phoneController = TextEditingController();
  final _otpController = TextEditingController();
  bool _otpSent = false;
  bool _isLoading = false;

  @override
  void dispose() {
    _phoneController.dispose();
    _otpController.dispose();
    super.dispose();
  }

  Future<void> _requestOtp() async {
    final phone = _phoneController.text.trim();
    if (phone.isEmpty || phone.length < 9) {
      _showError('Weka namba sahihi ya simu');
      return;
    }

    setState(() => _isLoading = true);

    final auth = Provider.of<AuthProvider>(context, listen: false);
    final success = await auth.requestOtp(phone, 'login');

    setState(() => _isLoading = false);

    if (success && mounted) {
      setState(() => _otpSent = true);
      if (auth.devOtp != null) {
        _showMessage('OTP ya majaribio: ${auth.devOtp}');
      }
    } else if (mounted) {
      _showError(auth.error ?? 'Imeshindwa kutuma OTP');
    }
  }

  Future<void> _verifyOtp() async {
    final auth = Provider.of<AuthProvider>(context, listen: false);

    setState(() => _isLoading = true);

    final success = await auth.verifyOtp(
      phone: _phoneController.text.trim(),
      code: _otpController.text.trim(),
      purpose: 'login',
    );

    setState(() => _isLoading = false);

    if (success && mounted) {
      widget.onLoginSuccess?.call();
      Navigator.of(context).pop(true);
    } else if (mounted) {
      _showError(auth.error ?? 'OTP si sahihi');
    }
  }

  void _showError(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message), backgroundColor: Colors.red),
    );
  }

  void _showMessage(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message)),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Container(
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
          // Handle bar
          Center(
            child: Container(
              width: 45,
              height: 5,
              decoration: BoxDecoration(
                color: Colors.grey[300],
                borderRadius: BorderRadius.circular(2.5),
              ),
            ),
          ),
          const SizedBox(height: 24),

          // Title & Icon Row
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: MkColors.primary.withValues(alpha: 0.1),
                  shape: BoxShape.circle,
                ),
                child: const Icon(
                  Icons.lock_outline,
                  color: MkColors.primary,
                  size: 28,
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      widget.action != null
                          ? 'Ingia ili uendelee'
                          : 'Ingia kwenye akaunti',
                      style: Theme.of(context).textTheme.titleLarge?.copyWith(
                            fontWeight: FontWeight.bold,
                          ),
                    ),
                    if (widget.action != null) ...[
                      const SizedBox(height: 4),
                      Text(
                        'Unatakiwa kuingia ili ${widget.action}.',
                        style: TextStyle(color: Colors.grey[600], fontSize: 13),
                      ),
                    ],
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 24),
          const Divider(height: 1),
          const SizedBox(height: 24),

          Text(
            'Weka namba yako ya simu ya mkononi ili upate kodi ya uthibitisho (OTP) ya kuingia.',
            style: TextStyle(color: Colors.grey[600], fontSize: 13, height: 1.3),
          ),
          const SizedBox(height: 20),

          // Phone input
          TextField(
            controller: _phoneController,
            keyboardType: TextInputType.phone,
            enabled: !_otpSent && !_isLoading,
            decoration: InputDecoration(
              labelText: 'Namba ya Simu',
              hintText: '2557XXXXXXXX',
              prefixIcon: const Icon(Icons.phone),
              filled: true,
              fillColor: Colors.grey[50],
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: BorderSide(color: Colors.grey[300]!),
              ),
              focusedBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: const BorderSide(color: MkColors.primary, width: 2),
              ),
            ),
          ),

          // OTP input
          if (_otpSent) ...[
            const SizedBox(height: 16),
            TextField(
              controller: _otpController,
              keyboardType: TextInputType.number,
              maxLength: 6,
              enabled: !_isLoading,
              decoration: InputDecoration(
                labelText: 'OTP Code',
                hintText: 'Ingiza namba 6 za siri',
                prefixIcon: const Icon(Icons.password),
                filled: true,
                fillColor: Colors.grey[50],
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: BorderSide(color: Colors.grey[300]!),
                ),
                focusedBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: const BorderSide(color: MkColors.primary, width: 2),
                ),
              ),
            ),
          ],

          const SizedBox(height: 24),

          // Action button
          SizedBox(
            width: double.infinity,
            height: 56,
            child: ElevatedButton(
              onPressed: _isLoading
                  ? null
                  : (_otpSent ? _verifyOtp : _requestOtp),
              style: ElevatedButton.styleFrom(
                backgroundColor: MkColors.primary,
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
                elevation: 2,
              ),
              child: _isLoading
                  ? const SizedBox(
                      width: 24,
                      height: 24,
                      child: CircularProgressIndicator(
                        strokeWidth: 2,
                        valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                      ),
                    )
                  : Text(
                      _otpSent ? 'Thibitisha na Uingie' : 'Pata Kodi ya Uthibitisho',
                      style: const TextStyle(fontWeight: FontWeight.bold),
                    ),
            ),
          ),

          const SizedBox(height: 12),

          // Cancel button
          SizedBox(
            width: double.infinity,
            height: 48,
            child: TextButton(
              onPressed: () => Navigator.of(context).pop(false),
              style: TextButton.styleFrom(
                foregroundColor: Colors.grey[600],
              ),
              child: const Text('Ghairi na Rudi nyuma'),
            ),
          ),
        ],
      ),
    );
  }
}
