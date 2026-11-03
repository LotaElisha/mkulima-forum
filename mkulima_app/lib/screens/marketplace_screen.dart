import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../core/strings.dart';
import '../core/theme.dart';
import '../models/product.dart';
import '../services/api_service.dart';
import '../widgets/mk_empty_state.dart';
import '../widgets/mk_error_state.dart';
import '../widgets/mk_skeleton.dart';
import '../widgets/mk_product_tile.dart';
import 'product_detail_screen.dart';

/// Pembejeo — the agrodealer marketplace: seeds, fertilizer and crop
/// protection from input dealers. Not a produce market: farmers selling
/// maize or beans is a separate service ("Soko la mazao").
///
/// [standalone] is true when pushed from "Huduma zote" (it then has its own
/// app bar); as a bottom-nav tab the shell provides the bar.
class MarketplaceScreen extends StatefulWidget {
  final bool standalone;

  const MarketplaceScreen({super.key, this.standalone = false});

  @override
  State<MarketplaceScreen> createState() => _MarketplaceScreenState();
}

class _MarketplaceScreenState extends State<MarketplaceScreen> {
  List<dynamic> _products = [];
  bool _isLoading = true;
  String? _error;
  String _searchQuery = '';
  final _searchController = TextEditingController();
  String? _selectedCategory;

  /// Input categories from the API, as (id, label). Loaded rather than
  /// hard-coded: the old list filtered a category id against a Swahili label,
  /// so every chip showed an empty grid, and it mixed in produce and
  /// livestock, which are not inputs.
  List<({String id, String label})> _categories = [];

  /// Swahili names by slug; categories added in admin fall back to their name.
  static const _labels = {
    'seeds': 'Mbegu',
    'fertilizers': 'Mbolea',
    'pesticides': 'Viuatilifu',
    'herbicides': 'Viuatilifu vya magugu',
    'fungicides': 'Dawa za fangasi',
    'insecticides': 'Dawa za wadudu',
    'plant-nutrients': 'Virutubisho',
    'tools': 'Zana',
    'animal-feed': 'Chakula cha mifugo',
    'irrigation': 'Umwagiliaji',
    'other-inputs': 'Nyingine',
  };

  @override
  void initState() {
    super.initState();
    _loadCategories();
    _loadProducts();
  }

  Future<void> _loadCategories() async {
    try {
      final r = await context.read<ApiService>().get('/marketplace/categories');
      final list = (r.data['categories'] as List?) ?? [];
      if (!mounted) return;
      setState(() {
        _categories = [
          for (final c in list)
            (
              id: c['id'].toString(),
              label: _labels[c['slug']] ?? (c['name'] ?? '').toString(),
            ),
        ];
      });
    } catch (_) {
      // Chips are optional; search and the full list still work.
    }
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _loadProducts() async {
    try {
      setState(() {
        _isLoading = true;
        _error = null;
      });
      final api = context.read<ApiService>();
      final response = await api.getProducts();
      if (!mounted) return;
      setState(() {
        _products = response as List<dynamic>;
        _isLoading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _error = ApiService.formatError(e);
        _isLoading = false;
      });
    }
  }

  List<dynamic> get _filteredProducts {
    return _products.where((product) {
      // Handle both Product objects and Map data
      final name = product is Product
          ? product.name
          : product['name']?.toString() ?? '';
      final description = product is Product
          ? product.description
          : product['description']?.toString() ?? '';
      final category = product is Product
          ? product.categoryId
          : product['category']?.toString() ?? '';

      final matchesSearch =
          _searchQuery.isEmpty ||
          name.toLowerCase().contains(_searchQuery.toLowerCase()) ||
          description.toLowerCase().contains(_searchQuery.toLowerCase());
      final matchesCategory =
          _selectedCategory == null || category == _selectedCategory;
      return matchesSearch && matchesCategory;
    }).toList();
  }

  @override
  Widget build(BuildContext context) {
    final body = Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        const Padding(
          padding: EdgeInsets.fromLTRB(16, 4, 16, 8),
          child: Text(
            'Pata pembejeo bora kutoka kwa wauzaji wa pembejeo.',
            style: MkText.bodyMuted,
          ),
        ),
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16),
          child: TextField(
            controller: _searchController,
            onChanged: (value) => setState(() => _searchQuery = value),
            textInputAction: TextInputAction.search,
            decoration: const InputDecoration(
              hintText: 'Tafuta mbegu, mbolea, viuatilifu...',
              prefixIcon: Icon(Icons.search),
              fillColor: MkColors.surfaceMuted,
            ),
          ),
        ),
        const SizedBox(height: 12),
        if (_categories.isNotEmpty)
          SizedBox(
            height: 48,
            child: ListView(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              scrollDirection: Axis.horizontal,
              children: [
                _chip(null, 'Zote'),
                for (final c in _categories) _chip(c.id, c.label),
              ],
            ),
          ),
        Expanded(
          child: _isLoading
              ? const SingleChildScrollView(child: MkGridSkeleton())
              : _error != null
              ? _buildErrorView()
              : _filteredProducts.isEmpty
              ? _buildEmptyView()
              : _buildProductGrid(),
        ),
      ],
    );

    if (!widget.standalone) return body;
    return Scaffold(appBar: AppBar(title: const Text('Pembejeo')), body: body);
  }

  Widget _chip(String? id, String label) {
    final selected = _selectedCategory == id;
    return Padding(
      padding: const EdgeInsets.only(right: 8),
      child: FilterChip(
        label: Text(label),
        selected: selected,
        showCheckmark: false,
        side: BorderSide(color: selected ? MkColors.primary : MkColors.border),
        labelStyle: TextStyle(
          fontSize: 15,
          color: selected ? MkColors.primaryDark : MkColors.ink,
          fontWeight: selected ? FontWeight.w700 : FontWeight.w500,
        ),
        onSelected: (_) => setState(() => _selectedCategory = id),
      ),
    );
  }

  Widget _buildErrorView() {
    return MkErrorState(
      title: MkStrings.productsLoadFailed,
      message: _error,
      onRetry: _loadProducts,
    );
  }

  Widget _buildEmptyView() {
    final filtering = _searchQuery.isNotEmpty || _selectedCategory != null;
    return MkEmptyState(
      icon: Icons.search_off,
      title: MkStrings.noProductsFound,
      subtitle: filtering
          ? 'Jaribu neno jingine, au angalia aina zote.'
          : 'Pembejeo za wauzaji zitaonekana hapa zikiwekwa.',
      actionLabel: filtering ? 'Ona bidhaa zote' : null,
      onAction: filtering
          ? () => setState(() {
              _searchController.clear();
              _searchQuery = '';
              _selectedCategory = null;
            })
          : null,
    );
  }

  Widget _buildProductGrid() {
    return RefreshIndicator(
      onRefresh: _loadProducts,
      child: GridView.builder(
        padding: const EdgeInsets.all(16),
        gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
          crossAxisCount: 2,
          childAspectRatio: 0.66,
          crossAxisSpacing: 12,
          mainAxisSpacing: 12,
        ),
        itemCount: _filteredProducts.length,
        itemBuilder: (context, index) {
          final product = _filteredProducts[index];
          return MkProductTile(
            product: product,
            onTap: () {
              Navigator.of(context).push(
                MaterialPageRoute(
                  builder: (_) => ProductDetailScreen(product: product),
                ),
              );
            },
          );
        },
      ),
    );
  }
}
