import 'dart:async';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../core/theme.dart';
import '../services/api_service.dart';
import 'product_detail_screen.dart';
import 'forum_screen.dart';
import 'market_prices_screen.dart';

/// Global search — one box that finds products, discussions, experts,
/// service providers and market prices, in Swahili or English (the API
/// expands agricultural synonyms: "maize" ↔ "mahindi").
class SearchScreen extends StatefulWidget {
  const SearchScreen({super.key});

  @override
  State<SearchScreen> createState() => _SearchScreenState();
}

class _SearchScreenState extends State<SearchScreen> {
  final _controller = TextEditingController();
  Timer? _debounce;
  Map<String, dynamic>? _results;
  List<dynamic> _suggestions = [];
  bool _isLoading = false;
  bool _failed = false;

  @override
  void dispose() {
    _debounce?.cancel();
    _controller.dispose();
    super.dispose();
  }

  void _onChanged(String value) {
    _debounce?.cancel();
    _debounce = Timer(const Duration(milliseconds: 400), () {
      _search(value.trim());
    });
  }

  Future<void> _search(String query) async {
    if (query.length < 2) {
      setState(() {
        _results = null;
        _suggestions = [];
        _failed = false;
      });
      return;
    }

    setState(() {
      _isLoading = true;
      _failed = false;
    });

    try {
      final api = context.read<ApiService>();
      final response =
          await api.get('/search', queryParameters: {'q': query});
      if (!mounted) return;
      setState(() {
        _results = response.data['results'] as Map<String, dynamic>?;
        _suggestions = response.data['suggestions'] ?? [];
        _isLoading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _isLoading = false;
        _failed = true;
      });
    }
  }

  Future<void> _openProduct(String uuid) async {
    try {
      final api = context.read<ApiService>();
      final product = await api.getProduct(uuid);
      if (!mounted) return;
      Navigator.of(context).push(
        MaterialPageRoute(builder: (_) => ProductDetailScreen(product: product)),
      );
    } catch (_) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Imeshindikana kufungua bidhaa.')),
      );
    }
  }

  bool get _isEmpty {
    if (_results == null) return false;
    return _results!.values
        .every((group) => group is List && group.isEmpty);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        backgroundColor: MkColors.primary,
        foregroundColor: Colors.white,
        title: TextField(
          controller: _controller,
          autofocus: true,
          onChanged: _onChanged,
          textInputAction: TextInputAction.search,
          onSubmitted: (v) => _search(v.trim()),
          style: const TextStyle(color: Colors.white),
          cursorColor: Colors.white,
          decoration: InputDecoration(
            hintText: 'Tafuta bidhaa, mijadala, bei, wataalamu...',
            hintStyle: const TextStyle(color: Colors.white60, fontSize: 15),
            border: InputBorder.none,
            suffixIcon: _controller.text.isNotEmpty
                ? IconButton(
                    icon: const Icon(Icons.clear, color: Colors.white70),
                    onPressed: () {
                      _controller.clear();
                      _search('');
                    },
                  )
                : null,
          ),
        ),
      ),
      body: _buildBody(),
    );
  }

  Widget _buildBody() {
    if (_isLoading) {
      return const Center(child: CircularProgressIndicator());
    }

    if (_failed) {
      return _CenteredNote(
        icon: Icons.cloud_off,
        text: 'Utafutaji umeshindikana. Angalia mtandao ujaribu tena.',
        action: TextButton(
          onPressed: () => _search(_controller.text.trim()),
          child: const Text('Jaribu Tena'),
        ),
      );
    }

    if (_results == null) {
      return _CenteredNote(
        icon: Icons.search,
        text: 'Andika neno — kwa Kiswahili au Kiingereza.\n'
            'Mfano: "mahindi" au "maize" vinaleta matokeo yale yale.',
      );
    }

    if (_isEmpty) {
      return ListView(
        padding: const EdgeInsets.all(24),
        children: [
          const SizedBox(height: 40),
          const Icon(Icons.search_off, size: 56, color: Colors.grey),
          const SizedBox(height: 12),
          const Center(child: Text('Hakuna matokeo. Jaribu maneno haya:')),
          const SizedBox(height: 16),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            alignment: WrapAlignment.center,
            children: _suggestions
                .map((s) => ActionChip(
                      label: Text(s.toString()),
                      onPressed: () {
                        _controller.text = s.toString();
                        _search(s.toString());
                      },
                    ))
                .toList(),
          ),
        ],
      );
    }

    final products = (_results!['products'] as List?) ?? [];
    final threads = (_results!['threads'] as List?) ?? [];
    final experts = (_results!['experts'] as List?) ?? [];
    final providers = (_results!['providers'] as List?) ?? [];
    final prices = (_results!['market_prices'] as List?) ?? [];

    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        if (products.isNotEmpty) ...[
          const _GroupHeader('Bidhaa Sokoni'),
          ...products.map((p) => Card(
                margin: const EdgeInsets.only(bottom: 8),
                child: ListTile(
                  leading: const Icon(Icons.store_outlined,
                      color: MkColors.primary),
                  title: Row(
                    children: [
                      Flexible(
                        child: Text('${p['name']}',
                            overflow: TextOverflow.ellipsis),
                      ),
                      if (p['is_verified'] == true) ...[
                        const SizedBox(width: 6),
                        const Icon(Icons.verified,
                            size: 15, color: Colors.blue),
                      ],
                    ],
                  ),
                  subtitle: Text(
                      '${p['currency']} ${p['price']} / ${p['unit']}'),
                  onTap: () => _openProduct('${p['uuid']}'),
                ),
              )),
        ],
        if (threads.isNotEmpty) ...[
          const _GroupHeader('Mijadala Jukwaani'),
          ...threads.map((t) => Card(
                margin: const EdgeInsets.only(bottom: 8),
                child: ListTile(
                  leading: const Icon(Icons.forum_outlined,
                      color: Colors.teal),
                  title: Text('${t['title']}',
                      maxLines: 1, overflow: TextOverflow.ellipsis),
                  subtitle: Text('${t['snippet'] ?? ''}',
                      maxLines: 2, overflow: TextOverflow.ellipsis),
                  onTap: () => Navigator.of(context).push(
                    MaterialPageRoute(
                      builder: (_) => ThreadDetailScreen(
                        threadId: '${t['uuid']}',
                        threadTitle: '${t['title']}',
                      ),
                    ),
                  ),
                ),
              )),
        ],
        if (experts.isNotEmpty) ...[
          const _GroupHeader('Wataalamu Waliothibitishwa'),
          ...experts.map((e) => Card(
                margin: const EdgeInsets.only(bottom: 8),
                child: ListTile(
                  leading:
                      const Icon(Icons.verified_user, color: Colors.indigo),
                  title: Text('${e['name']}'),
                  subtitle: Text('${e['expert_title'] ?? 'Mtaalamu'}'),
                ),
              )),
        ],
        if (providers.isNotEmpty) ...[
          const _GroupHeader('Watoa Huduma'),
          ...providers.map((sp) => Card(
                margin: const EdgeInsets.only(bottom: 8),
                child: ListTile(
                  leading: const Icon(Icons.handyman_outlined,
                      color: Colors.brown),
                  title: Text('${sp['business_name'] ?? sp['service_type']}'),
                  subtitle: Text(
                      '${sp['service_type']} · ${sp['region']} · ⭐ ${sp['rating']}'),
                ),
              )),
        ],
        if (prices.isNotEmpty) ...[
          const _GroupHeader('Bei za Masoko'),
          ...prices.map((p) => Card(
                margin: const EdgeInsets.only(bottom: 8),
                child: ListTile(
                  leading: const Icon(Icons.price_change_outlined,
                      color: Colors.deepOrange),
                  title: Text('${p['commodity']} — ${p['market']}'),
                  subtitle: Text(
                      'TZS ${p['avg_price']} / ${p['unit']} · ${p['price_date']}'),
                  onTap: () => Navigator.of(context).push(
                    MaterialPageRoute(
                        builder: (_) => const MarketPricesScreen()),
                  ),
                ),
              )),
        ],
        const SizedBox(height: 24),
      ],
    );
  }
}

class _GroupHeader extends StatelessWidget {
  final String title;

  const _GroupHeader(this.title);

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 10, top: 8),
      child: Text(
        title,
        style: const TextStyle(
          fontWeight: FontWeight.bold,
          fontSize: 15,
          color: MkColors.primaryDark,
        ),
      ),
    );
  }
}

class _CenteredNote extends StatelessWidget {
  final IconData icon;
  final String text;
  final Widget? action;

  const _CenteredNote({required this.icon, required this.text, this.action});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(icon, size: 56, color: Colors.grey),
            const SizedBox(height: 14),
            Text(text,
                textAlign: TextAlign.center,
                style: TextStyle(color: Colors.grey[700])),
            if (action != null) ...[const SizedBox(height: 8), action!],
          ],
        ),
      ),
    );
  }
}
