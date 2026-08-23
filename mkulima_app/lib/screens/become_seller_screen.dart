import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../core/theme.dart';
import '../models/seller_state.dart';
import '../providers/auth_provider.dart';
import '../services/api_service.dart';

/// Applying to sell.
///
/// This screen is the missing middle of the flow. Before it, the only two
/// states an account could be in were "farmer" and "seller", the app offered
/// the Seller Dashboard to both, and a farmer who tapped it got
///
///   DioException ... status code 403
///
/// Now a farmer who wants to sell says so, gives the business details a
/// reviewer needs, and waits somewhere that explains itself.
class BecomeSellerScreen extends StatefulWidget {
  const BecomeSellerScreen({super.key});

  @override
  State<BecomeSellerScreen> createState() => _BecomeSellerScreenState();
}

class _BecomeSellerScreenState extends State<BecomeSellerScreen> {
  final _formKey = GlobalKey<FormState>();
  final _businessName = TextEditingController();
  final _region = TextEditingController();
  final _district = TextEditingController();
  final _phone = TextEditingController();
  final _description = TextEditingController();

  String _businessType = 'agrodealer';
  bool _busy = false;
  String? _error;
  Map<String, String> _fieldErrors = {};

  static const Map<String, String> _types = {
    'agrodealer': 'Duka la pembejeo (Agrovet)',
    'farmer_producer': 'Mkulima ninayeuza mazao yangu',
    'cooperative': 'Chama cha ushirika',
    'transporter': 'Usafirishaji',
  };

  @override
  void initState() {
    super.initState();
    // Pre-fill from the account so the farmer types as little as possible;
    // the number is still editable because a business line is often not the
    // number they signed up with.
    final user = context.read<AuthProvider>().user;
    final phone = user?.phone ?? '';
    _phone.text = phone.startsWith('255') ? phone.substring(3) : phone;
  }

  @override
  void dispose() {
    _businessName.dispose();
    _region.dispose();
    _district.dispose();
    _phone.dispose();
    _description.dispose();
    super.dispose();
  }

  String get _fullPhone =>
      '255${_phone.text.replaceAll(RegExp(r'\D'), '').replaceFirst(RegExp(r'^0+'), '')}';

  Future<void> _submit() async {
    setState(() {
      _error = null;
      _fieldErrors = {};
    });

    if (!(_formKey.currentState?.validate() ?? false)) return;

    setState(() => _busy = true);

    try {
      final state = await context.read<ApiService>().submitSellerApplication(
            businessName: _businessName.text.trim(),
            businessType: _businessType,
            region: _region.text.trim(),
            district: _district.text.trim(),
            contactPhone: _fullPhone,
            description: _description.text.trim(),
          );
      if (!mounted) return;
      // Refresh the account so the profile redraws with the pending state
      // before this screen closes.
      await context.read<AuthProvider>().refreshUser();
      if (!mounted) return;
      Navigator.of(context).pop(state);
    } catch (error) {
      if (!mounted) return;
      final apiError = ApiService.asApiError(error);
      setState(() {
        _busy = false;
        _error = apiError.message;
        _fieldErrors = {
          for (final entry in apiError.fieldErrors.entries)
            entry.key: entry.value.first,
        };
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Anza kuuza')),
      body: SafeArea(
        child: Form(
          key: _formKey,
          child: ListView(
            padding: const EdgeInsets.all(20),
            children: [
              const _IntroCard(),
              const SizedBox(height: 24),

              _label('Jina la biashara'),
              TextFormField(
                controller: _businessName,
                textCapitalization: TextCapitalization.words,
                decoration: InputDecoration(
                  hintText: 'Mfano: Njombe Agrovet',
                  errorText: _fieldErrors['business_name'],
                ),
                validator: (v) => (v == null || v.trim().length < 3)
                    ? 'Weka jina la biashara yako.'
                    : null,
              ),
              const SizedBox(height: 18),

              _label('Aina ya biashara'),
              DropdownButtonFormField<String>(
                initialValue: _businessType,
                items: _types.entries
                    .map((e) => DropdownMenuItem(value: e.key, child: Text(e.value)))
                    .toList(),
                onChanged: (v) => setState(() => _businessType = v ?? 'agrodealer'),
              ),
              const SizedBox(height: 18),

              _label('Mkoa'),
              TextFormField(
                controller: _region,
                textCapitalization: TextCapitalization.words,
                decoration: InputDecoration(
                  hintText: 'Mfano: Njombe',
                  errorText: _fieldErrors['region'],
                ),
                validator: (v) =>
                    (v == null || v.trim().isEmpty) ? 'Weka mkoa wako.' : null,
              ),
              const SizedBox(height: 18),

              _label('Wilaya (si lazima)'),
              TextFormField(
                controller: _district,
                textCapitalization: TextCapitalization.words,
                decoration: InputDecoration(errorText: _fieldErrors['district']),
              ),
              const SizedBox(height: 18),

              _label('Namba ya simu ya biashara'),
              TextFormField(
                controller: _phone,
                keyboardType: TextInputType.phone,
                decoration: InputDecoration(
                  prefixText: '+255 ',
                  hintText: '7XX XXX XXX',
                  errorText: _fieldErrors['contact_phone'],
                ),
                validator: (_) => RegExp(r'^255[0-9]{9}$').hasMatch(_fullPhone)
                    ? null
                    : 'Namba ya simu si sahihi.',
              ),
              const SizedBox(height: 18),

              _label('Unauza nini? (si lazima)'),
              TextFormField(
                controller: _description,
                maxLines: 3,
                maxLength: 1000,
                decoration: InputDecoration(
                  hintText: 'Mfano: Mbegu bora, mbolea na dawa za mimea.',
                  errorText: _fieldErrors['description'],
                ),
              ),

              if (_error != null) ...[
                const SizedBox(height: 8),
                _ErrorBanner(message: _error!),
              ],

              const SizedBox(height: 24),
              SizedBox(
                height: 52,
                child: FilledButton(
                  onPressed: _busy ? null : _submit,
                  child: _busy
                      ? const SizedBox.square(
                          dimension: 22,
                          child: CircularProgressIndicator(
                            strokeWidth: 2.5,
                            color: Colors.white,
                          ),
                        )
                      : const Text('Tuma maombi'),
                ),
              ),
              const SizedBox(height: 12),
              const Text(
                'Maombi yako yatapitiwa na timu yetu. Tutakujulisha '
                'yakishakubaliwa.',
                textAlign: TextAlign.center,
                style: TextStyle(color: MkColors.muted, fontSize: 13, height: 1.4),
              ),
              const SizedBox(height: 24),
            ],
          ),
        ),
      ),
    );
  }

  Widget _label(String text) => Padding(
        padding: const EdgeInsets.only(bottom: 8),
        child: Text(
          text,
          style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14),
        ),
      );
}

class _IntroCard extends StatelessWidget {
  const _IntroCard();

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: MkColors.leafPale,
        borderRadius: BorderRadius.circular(14),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: const [
          Text(
            'Uza mazao na pembejeo zako',
            style: TextStyle(fontSize: 17, fontWeight: FontWeight.w700),
          ),
          SizedBox(height: 8),
          Text(
            'Ukikubaliwa utaweza kuweka bidhaa sokoni, kupokea maagizo na '
            'kufuatilia mauzo yako. Akaunti yako ya sasa haibadiliki — '
            'unaendelea kutumia zana zote za kilimo.',
            style: TextStyle(color: MkColors.muted, height: 1.5),
          ),
        ],
      ),
    );
  }
}

class _ErrorBanner extends StatelessWidget {
  final String message;

  const _ErrorBanner({required this.message});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: MkColors.danger.withValues(alpha: 0.08),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: MkColors.danger.withValues(alpha: 0.25)),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Icon(Icons.error_outline, size: 20, color: MkColors.danger),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              message,
              style: const TextStyle(color: MkColors.danger, height: 1.4),
            ),
          ),
        ],
      ),
    );
  }
}

/// The card the profile shows in place of the Seller Dashboard while an
/// application is being reviewed, or after one was refused.
class SellerStatusCard extends StatelessWidget {
  final SellerState state;
  final VoidCallback onApply;

  const SellerStatusCard({super.key, required this.state, required this.onApply});

  @override
  Widget build(BuildContext context) {
    if (state.isPending) {
      return _Card(
        icon: Icons.hourglass_bottom,
        title: 'Maombi ya kuuza yanasubiri idhini',
        body: state.businessName == null
            ? 'Tutakujulisha yakishapitiwa.'
            : '${state.businessName} — tutakujulisha yakishapitiwa.',
      );
    }

    if (state.isRejected) {
      return _Card(
        icon: Icons.info_outline,
        title: 'Maombi hayakukubaliwa',
        body: state.rejectionReason ?? 'Unaweza kutuma maombi mapya.',
        actionLabel: 'Tuma maombi tena',
        onAction: onApply,
      );
    }

    return _Card(
      icon: Icons.storefront_outlined,
      title: 'Anza kuuza',
      body: 'Uza mazao au pembejeo zako kwa wakulima wengine.',
      actionLabel: 'Omba kuwa muuzaji',
      onAction: onApply,
    );
  }
}

class _Card extends StatelessWidget {
  final IconData icon;
  final String title;
  final String body;
  final String? actionLabel;
  final VoidCallback? onAction;

  const _Card({
    required this.icon,
    required this.title,
    required this.body,
    this.actionLabel,
    this.onAction,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: MkColors.leafPale,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: MkColors.border),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, color: MkColors.primary),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(title,
                    style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 15)),
                const SizedBox(height: 6),
                Text(body,
                    style: const TextStyle(color: MkColors.muted, height: 1.4, fontSize: 13)),
                if (actionLabel != null && onAction != null) ...[
                  const SizedBox(height: 12),
                  SizedBox(
                    height: 44,
                    child: FilledButton(
                      onPressed: onAction,
                      child: Text(actionLabel!),
                    ),
                  ),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }
}
