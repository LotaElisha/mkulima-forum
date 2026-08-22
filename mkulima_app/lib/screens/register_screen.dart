import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../core/theme.dart';
import '../providers/auth_provider.dart';
import 'home_screen.dart';

class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final _formKey = GlobalKey<FormState>();
  final _name = TextEditingController();
  final _email = TextEditingController();
  final _password = TextEditingController();
  String _role = 'farmer';
  bool _showPassword = false;
  bool _accepted = false;

  static const _roles = <String, String>{
    'farmer': 'Mkulima',
    'buyer': 'Mnunuzi',
    'agrodealer': 'Muuzaji pembejeo',
    'seller': 'Muuzaji',
    'agronomist': 'Mtaalamu wa kilimo',
    'veterinary': 'Mtaalamu wa mifugo',
    'logistics': 'Msafirishaji',
  };

  @override
  void dispose() {
    _name.dispose();
    _email.dispose();
    _password.dispose();
    super.dispose();
  }

  void _finish(bool success) {
    if (!success || !mounted) return;
    Navigator.of(context).pushAndRemoveUntil(
      MaterialPageRoute(builder: (_) => const HomeScreen()),
      (_) => false,
    );
  }

  Future<void> _register() async {
    if (!_formKey.currentState!.validate()) return;
    if (!_accepted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Kubali masharti ili kuendelea')),
      );
      return;
    }
    final auth = context.read<AuthProvider>();
    _finish(
      await auth.registerWithEmail(
        name: _name.text.trim(),
        email: _email.text.trim(),
        password: _password.text,
        role: _role,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    return Scaffold(
      backgroundColor: MkColors.surface,
      body: SafeArea(
        child: Center(
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 520),
            child: SingleChildScrollView(
              padding: const EdgeInsets.fromLTRB(24, 18, 24, 32),
              child: Form(
                key: _formKey,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    Row(
                      children: [
                        IconButton.outlined(
                          tooltip: 'Rudi',
                          onPressed: () => Navigator.maybePop(context),
                          icon: const Icon(Icons.arrow_back),
                        ),
                        const Spacer(),
                        ClipRRect(
                          borderRadius: BorderRadius.circular(14),
                          child: Image.asset(
                            'assets/images/app_icon.jpg',
                            width: 48,
                            height: 48,
                            fit: BoxFit.cover,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 28),
                    const Text(
                      'Jiunge na jamii ya wakulima',
                      style: TextStyle(
                        fontFamily: 'serif',
                        fontSize: 33,
                        height: 1.08,
                        fontWeight: FontWeight.w700,
                        color: MkColors.charcoal,
                      ),
                    ),
                    const SizedBox(height: 10),
                    const Text(
                      'Fungua akaunti kwa barua pepe. Ni rahisi, salama na haraka.',
                      style: TextStyle(color: MkColors.muted, height: 1.4),
                    ),
                    const SizedBox(height: 26),
                    TextFormField(
                      controller: _name,
                      textCapitalization: TextCapitalization.words,
                      textInputAction: TextInputAction.next,
                      autofillHints: const [AutofillHints.name],
                      decoration: const InputDecoration(
                        labelText: 'Jina kamili',
                        prefixIcon: Icon(Icons.person_outline),
                      ),
                      validator: (value) =>
                          value == null || value.trim().length < 2
                          ? 'Weka jina lako kamili'
                          : null,
                    ),
                    const SizedBox(height: 14),
                    TextFormField(
                      controller: _email,
                      keyboardType: TextInputType.emailAddress,
                      textInputAction: TextInputAction.next,
                      autofillHints: const [AutofillHints.email],
                      decoration: const InputDecoration(
                        labelText: 'Barua pepe',
                        hintText: 'jina@example.com',
                        prefixIcon: Icon(Icons.mail_outline),
                      ),
                      validator: (value) => value != null && value.contains('@')
                          ? null
                          : 'Weka barua pepe sahihi',
                    ),
                    const SizedBox(height: 14),
                    TextFormField(
                      controller: _password,
                      obscureText: !_showPassword,
                      textInputAction: TextInputAction.next,
                      autofillHints: const [AutofillHints.newPassword],
                      decoration: InputDecoration(
                        labelText: 'Nenosiri',
                        helperText: 'Tumia angalau herufi 12',
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
                      validator: (value) => (value?.length ?? 0) >= 12
                          ? null
                          : 'Nenosiri lazima liwe na herufi 12 au zaidi',
                    ),
                    const SizedBox(height: 14),
                    DropdownButtonFormField<String>(
                      initialValue: _role,
                      decoration: const InputDecoration(
                        labelText: 'Unajiunga kama nani?',
                        prefixIcon: Icon(Icons.badge_outlined),
                      ),
                      items: _roles.entries
                          .map(
                            (entry) => DropdownMenuItem(
                              value: entry.key,
                              child: Text(entry.value),
                            ),
                          )
                          .toList(),
                      onChanged: (value) =>
                          setState(() => _role = value ?? 'farmer'),
                    ),
                    const SizedBox(height: 12),
                    CheckboxListTile(
                      value: _accepted,
                      onChanged: (value) =>
                          setState(() => _accepted = value ?? false),
                      contentPadding: EdgeInsets.zero,
                      activeColor: MkColors.charcoal,
                      controlAffinity: ListTileControlAffinity.leading,
                      title: const Text(
                        'Nakubali Masharti ya Matumizi na Sera ya Faragha.',
                        style: TextStyle(fontSize: 13, height: 1.35),
                      ),
                    ),
                    if (auth.error != null) ...[
                      const SizedBox(height: 6),
                      Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: MkColors.danger.withValues(alpha: .08),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Text(
                          auth.error!,
                          style: const TextStyle(color: MkColors.danger),
                        ),
                      ),
                    ],
                    const SizedBox(height: 14),
                    FilledButton(
                      onPressed: auth.isLoading ? null : _register,
                      style: FilledButton.styleFrom(
                        backgroundColor: MkColors.primary,
                        foregroundColor: Colors.white,
                      ),
                      child: auth.isLoading
                          ? const SizedBox.square(
                              dimension: 22,
                              child: CircularProgressIndicator(
                                strokeWidth: 2.5,
                              ),
                            )
                          : const Text('Fungua akaunti'),
                    ),
                    const SizedBox(height: 20),
                    const Row(
                      children: [
                        Expanded(child: Divider()),
                        Padding(
                          padding: EdgeInsets.symmetric(horizontal: 12),
                          child: Text(
                            'AU JISAJILI NA',
                            style: TextStyle(
                              fontSize: 11,
                              color: MkColors.muted,
                            ),
                          ),
                        ),
                        Expanded(child: Divider()),
                      ],
                    ),
                    const SizedBox(height: 16),
                    Row(
                      children: [
                        Expanded(
                          child: OutlinedButton.icon(
                            onPressed: auth.isLoading
                                ? null
                                : () async => _finish(
                                    await auth.signInWithGoogle(role: _role),
                                  ),
                            icon: const Icon(Icons.g_mobiledata, size: 25),
                            label: const Text('Google'),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: OutlinedButton.icon(
                            onPressed: auth.isLoading
                                ? null
                                : () async => _finish(
                                    await auth.signInWithApple(role: _role),
                                  ),
                            icon: const Icon(Icons.apple),
                            label: const Text('Apple'),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 16),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Text('Tayari una akaunti?'),
                        TextButton(
                          onPressed: () => Navigator.maybePop(context),
                          child: const Text('Ingia'),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}
