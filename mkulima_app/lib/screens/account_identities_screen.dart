import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';

import '../core/theme.dart';
import '../providers/auth_provider.dart';
import '../services/api_service.dart';

/// Manage the ways into this account: email, phone, and social sign-in.
///
/// The reason this screen exists: OTP registration keys on phone alone, so a
/// farmer who signed up with an email in January and signed in with their phone
/// in March used to end up with two accounts holding different farm records,
/// orders and wallets. Attaching the number here — from inside the account they
/// already have — is what prevents that.
///
/// Backed by /api/auth/identities and /api/auth/phone/link/*.
class AccountIdentitiesScreen extends StatefulWidget {
  const AccountIdentitiesScreen({super.key});

  @override
  State<AccountIdentitiesScreen> createState() => _AccountIdentitiesScreenState();
}

class _AccountIdentitiesScreenState extends State<AccountIdentitiesScreen> {
  Map<String, dynamic>? _identities;
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final data = await context.read<ApiService>().getIdentities();
      if (!mounted) return;
      setState(() {
        _identities = data;
        _loading = false;
      });
    } catch (error) {
      if (!mounted) return;
      setState(() {
        _error = ApiService.formatError(error);
        _loading = false;
      });
    }
  }

  Future<void> _startPhoneLink() async {
    final linked = await Navigator.of(context).push<bool>(
      MaterialPageRoute(builder: (_) => const LinkPhoneScreen()),
    );
    if (linked == true) {
      await _load();
      if (mounted) await context.read<AuthProvider>().refreshUser();
    }
  }

  Future<void> _unlinkPhone() async {
    final controller = TextEditingController();

    final confirmed = await showDialog<bool>(
      context: context,
      builder: (dialogContext) => StatefulBuilder(
        builder: (dialogContext, setDialogState) => AlertDialog(
          title: const Text('Ondoa namba ya simu'),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                'Hutaweza kuingia kwa namba hii tena. Weka nenosiri lako '
                'kuthibitisha.',
                style: TextStyle(color: MkColors.muted, height: 1.4),
              ),
              const SizedBox(height: 16),
              TextField(
                controller: controller,
                obscureText: true,
                autofocus: true,
                // Without this the dialog never rebuilds as the user types, so
                // the confirm button below stays disabled no matter what they
                // enter. StatefulBuilder only rebuilds when asked.
                onChanged: (_) => setDialogState(() {}),
                decoration: const InputDecoration(
                  labelText: 'Nenosiri lako',
                  prefixIcon: Icon(Icons.lock_outline),
                ),
              ),
            ],
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(dialogContext).pop(false),
              child: const Text('Ghairi'),
            ),
            FilledButton(
              style: FilledButton.styleFrom(backgroundColor: MkColors.danger),
              onPressed: controller.text.isEmpty
                  ? null
                  : () => Navigator.of(dialogContext).pop(true),
              child: const Text('Ondoa'),
            ),
          ],
        ),
      ),
    );

    if (confirmed != true || !mounted) {
      controller.dispose();
      return;
    }

    try {
      await context.read<ApiService>().unlinkPhone(controller.text);
      if (!mounted) return;
      _toast('Namba ya simu imeondolewa.');
      await _load();
    } catch (error) {
      if (mounted) _toast(ApiService.formatError(error), isError: true);
    } finally {
      controller.dispose();
    }
  }

  Future<void> _resendVerification() async {
    try {
      await context.read<ApiService>().resendEmailVerification();
      if (mounted) _toast('Kiungo cha kuthibitisha kimetumwa kwenye barua pepe yako.');
    } catch (error) {
      if (mounted) _toast(ApiService.formatError(error), isError: true);
    }
  }

  void _toast(String message, {bool isError = false}) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: isError ? MkColors.danger : MkColors.primary,
        behavior: SnackBarBehavior.floating,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Njia za kuingia')),
      body: RefreshIndicator(
        onRefresh: _load,
        child: _buildBody(),
      ),
    );
  }

  Widget _buildBody() {
    if (_loading) {
      return const _LoadingList();
    }

    if (_error != null) {
      return _ErrorState(message: _error!, onRetry: _load);
    }

    final email = Map<String, dynamic>.from(_identities?['email'] as Map? ?? {});
    final phone = Map<String, dynamic>.from(_identities?['phone'] as Map? ?? {});
    final social = List<dynamic>.from(_identities?['social'] as List? ?? []);

    return ListView(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.all(16),
      children: [
        const Text(
          'Hizi ndizo njia unazoweza kutumia kuingia kwenye akaunti yako. '
          'Ukiongeza namba ya simu hapa, hutafungua akaunti mpya — '
          'itaunganishwa na akaunti hii.',
          style: TextStyle(color: MkColors.muted, height: 1.5),
        ),
        const SizedBox(height: 20),

        _IdentityCard(
          icon: Icons.mail_outline,
          title: 'Barua pepe',
          value: email['value'] as String? ?? 'Haijawekwa',
          verified: email['verified'] == true,
          pending: email['pending'] as String?,
          action: email['verified'] == true
              ? null
              : _IdentityAction(label: 'Tuma kiungo tena', onTap: _resendVerification),
        ),
        const SizedBox(height: 12),

        _IdentityCard(
          icon: Icons.smartphone_outlined,
          title: 'Namba ya simu',
          value: phone['value'] as String? ?? 'Haijawekwa',
          verified: phone['verified'] == true,
          action: phone['value'] == null
              ? _IdentityAction(label: 'Ongeza namba', onTap: _startPhoneLink)
              : (phone['can_unlink'] == true
                    ? _IdentityAction(
                        label: 'Ondoa',
                        onTap: _unlinkPhone,
                        destructive: true,
                      )
                    : null),
          // Explains why "Ondoa" is absent rather than leaving a dead space.
          note: phone['value'] != null && phone['can_unlink'] != true
              ? 'Hii ndiyo njia pekee ya kuingia. Ongeza barua pepe na nenosiri '
                    'kwanza kabla ya kuondoa namba hii.'
              : null,
        ),

        if (social.isNotEmpty) ...[
          const SizedBox(height: 12),
          for (final account in social)
            Padding(
              padding: const EdgeInsets.only(bottom: 12),
              child: _IdentityCard(
                icon: Icons.account_circle_outlined,
                title: _providerLabel(account['provider'] as String? ?? ''),
                value: account['email'] as String? ?? '—',
                verified: true,
              ),
            ),
        ],
      ],
    );
  }

  String _providerLabel(String provider) => switch (provider) {
    'google' => 'Google',
    'apple' => 'Apple',
    _ => provider.isEmpty ? 'Akaunti ya nje' : provider,
  };
}

// ── Add a phone number ──────────────────────────────────────────────────

/// Two steps: request a code, then confirm it.
///
/// The number is entered as the nine digits people actually know, behind a
/// fixed +255 prefix. The API wants 255XXXXXXXXX and used to reject anything
/// else with a regex error, which is not a message a farmer can act on.
class LinkPhoneScreen extends StatefulWidget {
  const LinkPhoneScreen({super.key});

  @override
  State<LinkPhoneScreen> createState() => _LinkPhoneScreenState();
}

class _LinkPhoneScreenState extends State<LinkPhoneScreen> {
  final _phone = TextEditingController();
  final _code = TextEditingController();

  bool _codeSent = false;
  bool _busy = false;
  String? _error;
  int _resendIn = 0;

  String get _fullPhone => '255${_phone.text.replaceAll(RegExp(r'\D'), '').replaceFirst(RegExp(r'^0+'), '')}';

  bool get _phoneValid => RegExp(r'^255[0-9]{9}$').hasMatch(_fullPhone);

  @override
  void dispose() {
    _phone.dispose();
    _code.dispose();
    super.dispose();
  }

  Future<void> _requestCode() async {
    if (!_phoneValid) {
      setState(() => _error = 'Weka tarakimu 9, mfano 712345678.');
      return;
    }

    setState(() {
      _busy = true;
      _error = null;
    });

    try {
      final result = await context.read<ApiService>().requestPhoneLink(_fullPhone);
      if (!mounted) return;
      setState(() {
        _codeSent = true;
        _busy = false;
        _resendIn = 60;
      });
      _tickResend();

      // Local and testing builds return the code so the flow can be exercised
      // without a live SMS gateway. Production never includes it.
      final devCode = result['dev_code'] as String?;
      if (devCode != null) {
        _code.text = devCode;
        _snack('Msimbo wa majaribio umejazwa: $devCode');
      }
    } catch (error) {
      if (!mounted) return;
      setState(() {
        _busy = false;
        _error = ApiService.formatError(error);
      });
    }
  }

  void _tickResend() {
    Future.delayed(const Duration(seconds: 1), () {
      if (!mounted || _resendIn <= 0) return;
      setState(() => _resendIn--);
      _tickResend();
    });
  }

  Future<void> _confirm() async {
    final code = _code.text.replaceAll(RegExp(r'\D'), '');
    if (code.length != 6) {
      setState(() => _error = 'Msimbo una tarakimu 6.');
      return;
    }

    setState(() {
      _busy = true;
      _error = null;
    });

    try {
      await context.read<ApiService>().confirmPhoneLink(phone: _fullPhone, code: code);
      if (!mounted) return;
      Navigator.of(context).pop(true);
    } catch (error) {
      if (!mounted) return;
      setState(() {
        _busy = false;
        _error = ApiService.formatError(error);
      });
    }
  }

  void _snack(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message), behavior: SnackBarBehavior.floating),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text(_codeSent ? 'Thibitisha namba' : 'Ongeza namba ya simu')),
      body: SafeArea(
        child: ListView(
          padding: const EdgeInsets.all(20),
          children: [
            Text(
              _codeSent
                  ? 'Tumetuma msimbo wa tarakimu 6 kwa +$_fullPhone.'
                  : 'Namba hii itaunganishwa na akaunti yako ya sasa. '
                        'Hutafungua akaunti mpya.',
              style: const TextStyle(color: MkColors.muted, height: 1.5),
            ),
            const SizedBox(height: 24),

            if (!_codeSent) _phoneField() else _codeField(),

            if (_error != null) ...[
              const SizedBox(height: 12),
              _InlineError(message: _error!),
            ],

            const SizedBox(height: 24),
            FilledButton(
              onPressed: _busy ? null : (_codeSent ? _confirm : _requestCode),
              child: _busy
                  ? const SizedBox.square(
                      dimension: 22,
                      child: CircularProgressIndicator(strokeWidth: 2.5, color: Colors.white),
                    )
                  : Text(_codeSent ? 'Thibitisha na uunganishe' : 'Tuma msimbo'),
            ),

            if (_codeSent) ...[
              const SizedBox(height: 12),
              Center(
                child: TextButton(
                  onPressed: _resendIn > 0 || _busy ? null : _requestCode,
                  child: Text(
                    _resendIn > 0 ? 'Tuma tena baada ya sekunde $_resendIn' : 'Tuma msimbo tena',
                  ),
                ),
              ),
              Center(
                child: TextButton(
                  onPressed: _busy
                      ? null
                      : () => setState(() {
                          _codeSent = false;
                          _code.clear();
                          _error = null;
                        }),
                  child: const Text('Badilisha namba'),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _phoneField() {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Container(
          height: 56,
          padding: const EdgeInsets.symmetric(horizontal: 14),
          alignment: Alignment.center,
          decoration: BoxDecoration(
            color: MkColors.surfaceMuted,
            border: Border.all(color: MkColors.border),
            borderRadius: const BorderRadius.horizontal(left: Radius.circular(14)),
          ),
          child: const Text(
            '+255',
            style: TextStyle(fontWeight: FontWeight.w600, color: MkColors.muted),
          ),
        ),
        Expanded(
          child: TextField(
            controller: _phone,
            keyboardType: TextInputType.phone,
            autofocus: true,
            maxLength: 9,
            inputFormatters: [FilteringTextInputFormatter.digitsOnly],
            onChanged: (_) => setState(() => _error = null),
            decoration: const InputDecoration(
              hintText: '712345678',
              counterText: '',
              border: OutlineInputBorder(
                borderRadius: BorderRadius.horizontal(right: Radius.circular(14)),
              ),
            ),
          ),
        ),
      ],
    );
  }

  Widget _codeField() {
    return TextField(
      controller: _code,
      keyboardType: TextInputType.number,
      autofocus: true,
      maxLength: 6,
      textAlign: TextAlign.center,
      inputFormatters: [FilteringTextInputFormatter.digitsOnly],
      style: const TextStyle(fontSize: 26, letterSpacing: 10, fontWeight: FontWeight.bold),
      onChanged: (value) {
        setState(() => _error = null);
        if (value.length == 6) _confirm();
      },
      decoration: const InputDecoration(counterText: '', hintText: '······'),
    );
  }
}

// ── Pieces ──────────────────────────────────────────────────────────────

class _IdentityAction {
  final String label;
  final VoidCallback onTap;
  final bool destructive;

  const _IdentityAction({
    required this.label,
    required this.onTap,
    this.destructive = false,
  });
}

class _IdentityCard extends StatelessWidget {
  final IconData icon;
  final String title;
  final String value;
  final bool verified;
  final String? pending;
  final String? note;
  final _IdentityAction? action;

  const _IdentityCard({
    required this.icon,
    required this.title,
    required this.value,
    required this.verified,
    this.pending,
    this.note,
    this.action,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(9),
                  decoration: BoxDecoration(
                    color: MkColors.leafPale,
                    borderRadius: BorderRadius.circular(11),
                  ),
                  child: Icon(icon, color: MkColors.primary, size: 21),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        title,
                        style: const TextStyle(
                          fontSize: 13,
                          color: MkColors.muted,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        value,
                        style: const TextStyle(
                          fontSize: 15,
                          fontWeight: FontWeight.w700,
                          color: MkColors.ink,
                        ),
                      ),
                    ],
                  ),
                ),
                _StatusChip(verified: verified),
              ],
            ),
            if (pending != null) ...[
              const SizedBox(height: 10),
              _InlineNote(
                icon: Icons.schedule_outlined,
                text: 'Inasubiri uthibitisho wa $pending',
              ),
            ],
            if (note != null) ...[
              const SizedBox(height: 10),
              _InlineNote(icon: Icons.info_outline, text: note!),
            ],
            if (action != null) ...[
              const SizedBox(height: 12),
              Align(
                alignment: Alignment.centerLeft,
                child: action!.destructive
                    ? OutlinedButton(
                        onPressed: action!.onTap,
                        style: OutlinedButton.styleFrom(
                          foregroundColor: MkColors.danger,
                          side: const BorderSide(color: MkColors.danger),
                          minimumSize: const Size(0, 44),
                        ),
                        child: Text(action!.label),
                      )
                    : FilledButton(
                        onPressed: action!.onTap,
                        style: FilledButton.styleFrom(minimumSize: const Size(0, 44)),
                        child: Text(action!.label),
                      ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}

class _StatusChip extends StatelessWidget {
  final bool verified;

  const _StatusChip({required this.verified});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
      decoration: BoxDecoration(
        color: verified ? MkColors.leafPale : MkColors.surfaceMuted,
        borderRadius: BorderRadius.circular(999),
        border: Border.all(color: verified ? MkColors.primary : MkColors.border),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(
            verified ? Icons.check_circle : Icons.remove_circle_outline,
            size: 13,
            color: verified ? MkColors.primary : MkColors.muted,
          ),
          const SizedBox(width: 4),
          Text(
            verified ? 'Imethibitishwa' : 'Haijathibitishwa',
            style: TextStyle(
              fontSize: 11,
              fontWeight: FontWeight.w700,
              color: verified ? MkColors.primary : MkColors.muted,
            ),
          ),
        ],
      ),
    );
  }
}

class _InlineNote extends StatelessWidget {
  final IconData icon;
  final String text;

  const _InlineNote({required this.icon, required this.text});

  @override
  Widget build(BuildContext context) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Icon(icon, size: 15, color: MkColors.muted),
        const SizedBox(width: 7),
        Expanded(
          child: Text(
            text,
            style: const TextStyle(fontSize: 12.5, color: MkColors.muted, height: 1.4),
          ),
        ),
      ],
    );
  }
}

class _InlineError extends StatelessWidget {
  final String message;

  const _InlineError({required this.message});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: MkColors.danger.withValues(alpha: 0.08),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Icon(Icons.error_outline, size: 18, color: MkColors.danger),
          const SizedBox(width: 9),
          Expanded(
            child: Text(
              message,
              style: const TextStyle(color: MkColors.danger, fontSize: 13.5, height: 1.4),
            ),
          ),
        ],
      ),
    );
  }
}

/// Skeleton rows rather than a bare spinner: on a slow connection this screen
/// can take a moment, and a shaped placeholder tells the user what is coming.
class _LoadingList extends StatelessWidget {
  const _LoadingList();

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.all(16),
      children: List.generate(
        3,
        (_) => Container(
          height: 92,
          margin: const EdgeInsets.only(bottom: 12),
          decoration: BoxDecoration(
            color: MkColors.surfaceMuted,
            borderRadius: BorderRadius.circular(MkRadii.card),
          ),
        ),
      ),
    );
  }
}

class _ErrorState extends StatelessWidget {
  final String message;
  final VoidCallback onRetry;

  const _ErrorState({required this.message, required this.onRetry});

  @override
  Widget build(BuildContext context) {
    return ListView(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.all(32),
      children: [
        const SizedBox(height: 60),
        const Icon(Icons.cloud_off_outlined, size: 48, color: MkColors.muted),
        const SizedBox(height: 16),
        Text(
          message,
          textAlign: TextAlign.center,
          style: const TextStyle(color: MkColors.muted, height: 1.5),
        ),
        const SizedBox(height: 20),
        Center(
          child: FilledButton.icon(
            onPressed: onRetry,
            icon: const Icon(Icons.refresh),
            label: const Text('Jaribu tena'),
          ),
        ),
      ],
    );
  }
}
