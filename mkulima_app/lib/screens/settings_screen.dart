import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../core/theme.dart';
import '../providers/app_settings_provider.dart';
import '../providers/auth_provider.dart';
import 'login_screen.dart';

class SettingsScreen extends StatelessWidget {
  const SettingsScreen({super.key});

  Future<void> _editName(BuildContext context, AuthProvider auth) async {
    final controller = TextEditingController(text: auth.user?.name ?? '');
    final value = await showDialog<String>(
      context: context,
      builder: (dialogContext) => AlertDialog(
        title: const Text('Badilisha jina'),
        content: TextField(
          controller: controller,
          autofocus: true,
          textCapitalization: TextCapitalization.words,
          decoration: const InputDecoration(labelText: 'Jina kamili'),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(dialogContext),
            child: const Text('Ghairi'),
          ),
          FilledButton(
            onPressed: () =>
                Navigator.pop(dialogContext, controller.text.trim()),
            child: const Text('Hifadhi'),
          ),
        ],
      ),
    );
    controller.dispose();
    if (value == null || value.length < 2 || !context.mounted) return;
    final success = await auth.updateProfile({'name': value});
    if (context.mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            success ? 'Jina limehifadhiwa' : 'Jina halikuhifadhiwa',
          ),
        ),
      );
    }
  }

  Future<void> _selectLanguage(BuildContext context, AuthProvider auth) async {
    final selected = await showModalBottomSheet<String>(
      context: context,
      builder: (context) => SafeArea(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const ListTile(
              title: Text(
                'Chagua lugha',
                style: TextStyle(fontWeight: FontWeight.bold),
              ),
            ),
            ListTile(
              leading: Icon(
                auth.user?.preferredLanguage != 'en'
                    ? Icons.radio_button_checked
                    : Icons.radio_button_off,
              ),
              title: const Text('Kiswahili'),
              onTap: () => Navigator.pop(context, 'sw'),
            ),
            ListTile(
              leading: Icon(
                auth.user?.preferredLanguage == 'en'
                    ? Icons.radio_button_checked
                    : Icons.radio_button_off,
              ),
              title: const Text('English'),
              onTap: () => Navigator.pop(context, 'en'),
            ),
          ],
        ),
      ),
    );
    if (selected == null || !context.mounted) return;
    await auth.updateProfile({'preferred_language': selected});
  }

  void _showInfo(BuildContext context, String title, String body) {
    showDialog<void>(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(title),
        content: Text(body),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Sawa'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final settings = context.watch<AppSettingsProvider>();
    return Scaffold(
      appBar: AppBar(title: const Text('Mipangilio')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          const _SectionTitle('Akaunti'),
          Card(
            child: Column(
              children: [
                ListTile(
                  leading: const Icon(Icons.person_outline),
                  title: const Text('Jina kamili'),
                  subtitle: Text(auth.user?.name ?? '—'),
                  trailing: const Icon(Icons.edit_outlined),
                  onTap: auth.isAuthenticated
                      ? () => _editName(context, auth)
                      : null,
                ),
                const Divider(height: 1),
                ListTile(
                  leading: const Icon(Icons.phone_outlined),
                  title: const Text('Namba ya simu'),
                  subtitle: Text(
                    auth.user?.phone.isNotEmpty == true
                        ? auth.user!.phone
                        : 'Haijawekwa',
                  ),
                  trailing: const Icon(Icons.info_outline),
                  onTap: () => _showInfo(
                    context,
                    'Namba ya simu',
                    'Mabadiliko ya namba yanahitaji uthibitisho wa OTP. Wasiliana na msaada kwa sasa.',
                  ),
                ),
                const Divider(height: 1),
                ListTile(
                  leading: const Icon(Icons.lock_outline),
                  title: const Text('Rejesha nenosiri'),
                  subtitle: const Text(
                    'Tumia namba yako na OTP kuingia salama',
                  ),
                  trailing: const Icon(Icons.chevron_right),
                  onTap: () => Navigator.of(context).push(
                    MaterialPageRoute(builder: (_) => const LoginScreen()),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 24),
          const _SectionTitle('Mapendeleo'),
          Card(
            child: Column(
              children: [
                ListTile(
                  leading: const Icon(Icons.language_outlined),
                  title: const Text('Lugha'),
                  subtitle: Text(
                    auth.user?.preferredLanguage == 'en'
                        ? 'English'
                        : 'Kiswahili',
                  ),
                  trailing: const Icon(Icons.chevron_right),
                  onTap: auth.isAuthenticated
                      ? () => _selectLanguage(context, auth)
                      : null,
                ),
                const Divider(height: 1),
                SwitchListTile(
                  secondary: const Icon(Icons.notifications_outlined),
                  title: const Text('Arifa'),
                  subtitle: const Text(
                    'Hifadhi upendeleo wa arifa kwenye kifaa hiki',
                  ),
                  value: settings.notificationsEnabled,
                  onChanged: settings.setNotificationsEnabled,
                ),
                const Divider(height: 1),
                SwitchListTile(
                  secondary: const Icon(Icons.dark_mode_outlined),
                  title: const Text('Hali ya giza'),
                  value: settings.darkMode,
                  onChanged: settings.setDarkMode,
                ),
              ],
            ),
          ),
          const SizedBox(height: 24),
          const _SectionTitle('Kuhusu'),
          Card(
            child: Column(
              children: [
                const ListTile(
                  leading: Icon(Icons.info_outline),
                  title: Text('Toleo'),
                  subtitle: Text('1.0.0+1'),
                ),
                const Divider(height: 1),
                ListTile(
                  leading: const Icon(Icons.help_outline),
                  title: const Text('Msaada'),
                  trailing: const Icon(Icons.chevron_right),
                  onTap: () => _showInfo(
                    context,
                    'Msaada',
                    'Kwa msaada wa akaunti, malipo au huduma, tumia sehemu ya mawasiliano ya MkulimaForum kwenye tovuti.',
                  ),
                ),
                const Divider(height: 1),
                ListTile(
                  leading: const Icon(Icons.privacy_tip_outlined),
                  title: const Text('Sera ya Faragha'),
                  trailing: const Icon(Icons.chevron_right),
                  onTap: () => _showInfo(
                    context,
                    'Sera ya Faragha',
                    'Taarifa zako hutumika kutoa huduma za akaunti, soko na jamii. Hatuhifadhi nenosiri kama maandishi ya kawaida.',
                  ),
                ),
              ],
            ),
          ),
          if (auth.isAuthenticated) ...[
            const SizedBox(height: 28),
            OutlinedButton.icon(
              onPressed: () async {
                await auth.logout();
                if (context.mounted) {
                  Navigator.of(context).pushAndRemoveUntil(
                    MaterialPageRoute(builder: (_) => const LoginScreen()),
                    (_) => false,
                  );
                }
              },
              icon: const Icon(Icons.logout, color: MkColors.danger),
              label: const Text(
                'Toka kwenye akaunti',
                style: TextStyle(color: MkColors.danger),
              ),
            ),
          ],
        ],
      ),
    );
  }
}

class _SectionTitle extends StatelessWidget {
  final String title;
  const _SectionTitle(this.title);
  @override
  Widget build(BuildContext context) => Padding(
    padding: const EdgeInsets.only(bottom: 10),
    child: Text(
      title,
      style: const TextStyle(
        fontFamily: 'serif',
        fontSize: 20,
        fontWeight: FontWeight.bold,
      ),
    ),
  );
}
