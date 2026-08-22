import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../core/theme.dart';
import '../providers/auth_provider.dart';
import 'home_screen.dart';
import 'register_screen.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _email = TextEditingController();
  final _password = TextEditingController();
  final _phone = TextEditingController();
  final _otp = TextEditingController();
  bool _showPassword = false;
  bool _useOtp = false;
  bool _otpSent = false;

  @override
  void dispose() {
    _email.dispose();
    _password.dispose();
    _phone.dispose();
    _otp.dispose();
    super.dispose();
  }

  void _finish(bool success) {
    if (!success || !mounted) return;
    Navigator.of(context).pushAndRemoveUntil(
      MaterialPageRoute(builder: (_) => const HomeScreen()),
      (_) => false,
    );
  }

  Future<void> _submit() async {
    final auth = context.read<AuthProvider>();
    if (!_useOtp) {
      if (!_email.text.contains('@') || _password.text.isEmpty) {
        _message('Weka barua pepe na nenosiri sahihi');
        return;
      }
      _finish(await auth.loginWithEmail(_email.text.trim(), _password.text));
      return;
    }
    if (!_otpSent) {
      if (_phone.text.trim().length < 9) {
        _message('Weka namba sahihi ya simu');
        return;
      }
      final sent = await auth.requestOtp(_phone.text.trim(), 'login');
      if (sent && mounted) setState(() => _otpSent = true);
      return;
    }
    _finish(
      await auth.verifyOtp(
        phone: _phone.text.trim(),
        code: _otp.text.trim(),
        purpose: 'login',
      ),
    );
  }

  void _message(String text) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(text)));
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    return Scaffold(
      backgroundColor: MkColors.surface,
      body: SafeArea(
        child: Center(
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 480),
            child: SingleChildScrollView(
              padding: const EdgeInsets.fromLTRB(24, 18, 24, 32),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Align(
                    alignment: Alignment.centerLeft,
                    child: IconButton.outlined(
                      tooltip: 'Rudi',
                      onPressed: () => Navigator.maybePop(context),
                      icon: const Icon(Icons.arrow_back),
                    ),
                  ),
                  const SizedBox(height: 18),
                  Center(
                    child: ClipRRect(
                      borderRadius: BorderRadius.circular(22),
                      child: Image.asset(
                        'assets/images/app_icon.jpg',
                        width: 76,
                        height: 76,
                        fit: BoxFit.cover,
                      ),
                    ),
                  ),
                  const SizedBox(height: 22),
                  const Text(
                    'Karibu Mkulima',
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      fontFamily: 'serif',
                      fontSize: 34,
                      height: 1.1,
                      fontWeight: FontWeight.w700,
                      color: MkColors.charcoal,
                    ),
                  ),
                  const SizedBox(height: 8),
                  const Text(
                    'Ingia kwenye maarifa, masoko na jamii yako.',
                    textAlign: TextAlign.center,
                    style: TextStyle(color: MkColors.muted, height: 1.4),
                  ),
                  const SizedBox(height: 30),
                  if (!_useOtp) ...[
                    TextField(
                      controller: _email,
                      keyboardType: TextInputType.emailAddress,
                      textInputAction: TextInputAction.next,
                      autofillHints: const [AutofillHints.email],
                      decoration: const InputDecoration(
                        labelText: 'Barua pepe',
                        hintText: 'jina@example.com',
                        prefixIcon: Icon(Icons.mail_outline),
                      ),
                    ),
                    const SizedBox(height: 14),
                    TextField(
                      controller: _password,
                      obscureText: !_showPassword,
                      textInputAction: TextInputAction.done,
                      autofillHints: const [AutofillHints.password],
                      onSubmitted: (_) => _submit(),
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
                            _showPassword
                                ? Icons.visibility_off
                                : Icons.visibility,
                          ),
                        ),
                      ),
                    ),
                    Align(
                      alignment: Alignment.centerRight,
                      child: TextButton(
                        onPressed: () => setState(() {
                          _useOtp = true;
                          _otpSent = false;
                        }),
                        child: const Text('Umesahau nenosiri?'),
                      ),
                    ),
                  ] else ...[
                    TextField(
                      controller: _phone,
                      keyboardType: TextInputType.phone,
                      enabled: !_otpSent,
                      decoration: const InputDecoration(
                        labelText: 'Namba ya simu',
                        hintText: '2557XXXXXXXX',
                        prefixIcon: Icon(Icons.phone_outlined),
                      ),
                    ),
                    if (_otpSent) ...[
                      const SizedBox(height: 14),
                      TextField(
                        controller: _otp,
                        keyboardType: TextInputType.number,
                        maxLength: 6,
                        decoration: const InputDecoration(
                          labelText: 'Namba ya uthibitisho',
                          prefixIcon: Icon(Icons.password_outlined),
                        ),
                      ),
                    ],
                    Align(
                      alignment: Alignment.centerRight,
                      child: TextButton(
                        onPressed: () => setState(() => _otpSent = false),
                        child: const Text('Badilisha namba'),
                      ),
                    ),
                  ],
                  if (auth.error != null) ...[
                    const SizedBox(height: 8),
                    _ErrorMessage(auth.error!),
                  ],
                  // Forgot-password entry point. The mobile app had none,
                  // because until now the platform had no reset flow at all.
                  if (!_useOtp)
                    Align(
                      alignment: Alignment.centerRight,
                      child: TextButton(
                        onPressed: auth.isLoading
                            ? null
                            : () => _showForgotPasswordSheet(context, auth),
                        child: const Text('Umesahau nenosiri?'),
                      ),
                    ),
                  const SizedBox(height: 12),
                  FilledButton(
                    onPressed: auth.isLoading ? null : _submit,
                    style: FilledButton.styleFrom(
                      backgroundColor: MkColors.primary,
                      foregroundColor: Colors.white,
                    ),
                    child: auth.isLoading
                        ? const SizedBox.square(
                            dimension: 22,
                            child: CircularProgressIndicator(strokeWidth: 2.5),
                          )
                        : Text(
                            _useOtp
                                ? (_otpSent
                                      ? 'Thibitisha na uingie'
                                      : 'Tuma OTP')
                                : 'Ingia',
                          ),
                  ),
                  const SizedBox(height: 20),
                  const _DividerLabel(),
                  const SizedBox(height: 16),
                  Row(
                    children: [
                      Expanded(
                        child: OutlinedButton.icon(
                          onPressed: auth.isLoading
                              ? null
                              : () async =>
                                    _finish(await auth.signInWithGoogle()),
                          icon: const Icon(Icons.g_mobiledata, size: 25),
                          label: const Text('Google'),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: OutlinedButton.icon(
                          onPressed: auth.isLoading
                              ? null
                              : () async =>
                                    _finish(await auth.signInWithApple()),
                          icon: const Icon(Icons.apple),
                          label: const Text('Apple'),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 10),
                  TextButton.icon(
                    onPressed: () => setState(() {
                      _useOtp = !_useOtp;
                      _otpSent = false;
                    }),
                    icon: Icon(
                      _useOtp ? Icons.mail_outline : Icons.phone_outlined,
                    ),
                    label: Text(
                      _useOtp
                          ? 'Ingia kwa barua pepe'
                          : 'Ingia kwa simu na OTP',
                    ),
                  ),
                  const SizedBox(height: 8),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Text('Huna akaunti?'),
                      TextButton(
                        onPressed: () => Navigator.of(context).push(
                          MaterialPageRoute(
                            builder: (_) => const RegisterScreen(),
                          ),
                        ),
                        child: const Text('Jisajili'),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  /// Collects an email address and asks the backend to send a reset link.
  ///
  /// Shows the same confirmation whatever the backend says, matching the
  /// endpoint's own refusal to reveal whether an address is registered.
  Future<void> _showForgotPasswordSheet(
    BuildContext context,
    AuthProvider auth,
  ) async {
    final controller = TextEditingController(text: _email.text.trim());

    await showModalBottomSheet<void>(
      context: context,
      isScrollControlled: true,
      builder: (sheetContext) {
        var sending = false;
        var sent = false;

        return StatefulBuilder(
          builder: (sheetContext, setSheetState) {
            return Padding(
              padding: EdgeInsets.only(
                left: 20,
                right: 20,
                top: 24,
                bottom: MediaQuery.of(sheetContext).viewInsets.bottom + 24,
              ),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  const Text(
                    'Umesahau nenosiri?',
                    style: TextStyle(
                      fontSize: 20,
                      fontWeight: FontWeight.w800,
                      color: MkColors.ink,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    sent
                        ? 'Kama barua pepe hiyo ina akaunti, tumetuma kiungo cha kuweka nenosiri jipya. Angalia kikasha chako na folda ya taka.'
                        : 'Weka barua pepe uliyotumia kujisajili. Tutakutumia kiungo cha kuweka nenosiri jipya.',
                    style: const TextStyle(color: MkColors.muted, height: 1.5),
                  ),
                  const SizedBox(height: 20),
                  if (!sent)
                    TextField(
                      controller: controller,
                      keyboardType: TextInputType.emailAddress,
                      autocorrect: false,
                      decoration: const InputDecoration(
                        labelText: 'Barua pepe',
                        prefixIcon: Icon(Icons.mail_outline),
                      ),
                    ),
                  const SizedBox(height: 20),
                  FilledButton(
                    onPressed: sending
                        ? null
                        : () async {
                            if (sent) {
                              Navigator.of(sheetContext).pop();
                              return;
                            }
                            final email = controller.text.trim();
                            if (!email.contains('@')) return;

                            setSheetState(() => sending = true);
                            final ok = await auth.requestPasswordReset(email);
                            setSheetState(() {
                              sending = false;
                              sent = ok;
                            });
                          },
                    child: sending
                        ? const SizedBox.square(
                            dimension: 20,
                            child: CircularProgressIndicator(strokeWidth: 2.5),
                          )
                        : Text(sent ? 'Sawa' : 'Tuma kiungo'),
                  ),
                ],
              ),
            );
          },
        );
      },
    );

    controller.dispose();
  }
}

class _DividerLabel extends StatelessWidget {
  const _DividerLabel();

  @override
  Widget build(BuildContext context) => const Row(
    children: [
      Expanded(child: Divider()),
      Padding(
        padding: EdgeInsets.symmetric(horizontal: 12),
        child: Text(
          'AU ENDELEA NA',
          style: TextStyle(fontSize: 11, color: MkColors.muted),
        ),
      ),
      Expanded(child: Divider()),
    ],
  );
}

class _ErrorMessage extends StatelessWidget {
  final String message;
  const _ErrorMessage(this.message);

  @override
  Widget build(BuildContext context) => Container(
    padding: const EdgeInsets.all(12),
    decoration: BoxDecoration(
      color: MkColors.danger.withValues(alpha: .08),
      borderRadius: BorderRadius.circular(12),
      border: Border.all(color: MkColors.danger.withValues(alpha: .25)),
    ),
    child: Row(
      children: [
        const Icon(Icons.error_outline, color: MkColors.danger),
        const SizedBox(width: 10),
        Expanded(
          child: Text(message, style: const TextStyle(color: MkColors.danger)),
        ),
      ],
    ),
  );

}
