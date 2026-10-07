import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../core/theme.dart';
import 'register_screen.dart';

class LoginModal extends StatefulWidget {
  final String? action;
  final VoidCallback? onLoginSuccess;

  const LoginModal({super.key, this.action, this.onLoginSuccess});

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
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  final _phoneController = TextEditingController();
  final _otpController = TextEditingController();
  // Email is the default: phone/OTP stays dark until an SMS provider is
  // credentialled (auth.otp_enabled), so it cannot be the only way in.
  bool _usePhone = false;
  bool _showPassword = false;
  bool _otpSent = false;
  bool _isLoading = false;

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    _phoneController.dispose();
    _otpController.dispose();
    super.dispose();
  }

  Future<void> _loginWithEmail() async {
    final email = _emailController.text.trim();
    if (!email.contains('@') || _passwordController.text.isEmpty) {
      _showError('Weka barua pepe na nenosiri sahihi');
      return;
    }

    setState(() => _isLoading = true);
    final auth = Provider.of<AuthProvider>(context, listen: false);
    final success = await auth.loginWithEmail(email, _passwordController.text);
    if (!mounted) return;
    setState(() => _isLoading = false);

    if (success) {
      widget.onLoginSuccess?.call();
      Navigator.of(context).pop(true);
    } else {
      _showError(auth.error ?? 'Imeshindwa kuingia');
    }
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
    if (!mounted) return;

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
    if (!mounted) return;

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
      SnackBar(content: Text(message), backgroundColor: MkColors.danger),
    );
  }

  void _showMessage(String message) {
    ScaffoldMessenger.of(
      context,
    ).showSnackBar(SnackBar(content: Text(message)));
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
                color: MkColors.border,
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
                  color: MkColors.leafPale,
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
                        style: TextStyle(color: MkColors.muted, fontSize: 13),
                      ),
                    ],
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 24),
          if (!_usePhone) ...[
            TextField(
              controller: _emailController,
              keyboardType: TextInputType.emailAddress,
              textInputAction: TextInputAction.next,
              autofillHints: const [AutofillHints.email],
              enabled: !_isLoading,
              decoration: const InputDecoration(
                labelText: 'Barua pepe',
                hintText: 'jina@example.com',
                prefixIcon: Icon(Icons.mail_outline),
              ),
            ),
            const SizedBox(height: 14),
            TextField(
              controller: _passwordController,
              obscureText: !_showPassword,
              textInputAction: TextInputAction.done,
              autofillHints: const [AutofillHints.password],
              enabled: !_isLoading,
              onSubmitted: (_) => _loginWithEmail(),
              decoration: InputDecoration(
                labelText: 'Nenosiri',
                prefixIcon: const Icon(Icons.lock_outline),
                suffixIcon: IconButton(
                  tooltip: _showPassword
                      ? 'Ficha nenosiri'
                      : 'Onyesha nenosiri',
                  onPressed: () =>
                      setState(() => _showPassword = !_showPassword),
                  icon: Icon(
                    _showPassword ? Icons.visibility_off : Icons.visibility,
                  ),
                ),
              ),
            ),
          ] else ...[
            const Text(
              'Weka namba yako ya simu upate kodi ya uthibitisho (OTP).',
              style: TextStyle(
                color: MkColors.muted,
                fontSize: 15,
                height: 1.35,
              ),
            ),
            const SizedBox(height: 14),
            TextField(
              controller: _phoneController,
              keyboardType: TextInputType.phone,
              enabled: !_otpSent && !_isLoading,
              decoration: const InputDecoration(
                labelText: 'Namba ya simu',
                hintText: '2557XXXXXXXX',
                prefixIcon: Icon(Icons.phone_outlined),
              ),
            ),
            if (_otpSent) ...[
              const SizedBox(height: 14),
              TextField(
                controller: _otpController,
                keyboardType: TextInputType.number,
                maxLength: 6,
                enabled: !_isLoading,
                decoration: const InputDecoration(
                  labelText: 'Namba ya uthibitisho',
                  hintText: 'Tarakimu 6',
                  prefixIcon: Icon(Icons.password_outlined),
                ),
              ),
            ],
          ],

          const SizedBox(height: 20),

          SizedBox(
            width: double.infinity,
            height: 48,
            child: FilledButton(
              onPressed: _isLoading
                  ? null
                  : (!_usePhone
                        ? _loginWithEmail
                        : (_otpSent ? _verifyOtp : _requestOtp)),
              child: _isLoading
                  ? const SizedBox(
                      width: 22,
                      height: 22,
                      child: CircularProgressIndicator(
                        strokeWidth: 2,
                        color: Colors.white,
                      ),
                    )
                  : Text(
                      !_usePhone
                          ? 'Ingia'
                          : (_otpSent
                                ? 'Thibitisha na uingie'
                                : 'Pata kodi ya uthibitisho'),
                    ),
            ),
          ),

          const SizedBox(height: 8),
          Wrap(
            alignment: WrapAlignment.spaceBetween,
            crossAxisAlignment: WrapCrossAlignment.center,
            children: [
              TextButton(
                onPressed: _isLoading
                    ? null
                    : () => setState(() {
                        _usePhone = !_usePhone;
                        _otpSent = false;
                      }),
                child: Text(
                  _usePhone ? 'Tumia barua pepe' : 'Tumia namba ya simu',
                ),
              ),
              TextButton(
                onPressed: _isLoading
                    ? null
                    : () {
                        final navigator = Navigator.of(context);
                        navigator.pop(false);
                        navigator.push(
                          MaterialPageRoute(
                            builder: (_) => const RegisterScreen(),
                          ),
                        );
                      },
                child: const Text('Fungua akaunti'),
              ),
            ],
          ),

          // Cancel button
          SizedBox(
            width: double.infinity,
            height: 48,
            child: TextButton(
              onPressed: () => Navigator.of(context).pop(false),
              style: TextButton.styleFrom(foregroundColor: MkColors.muted),
              child: const Text('Ghairi na Rudi nyuma'),
            ),
          ),
        ],
      ),
    );
  }
}
