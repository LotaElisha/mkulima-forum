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

class MarketplaceScreen extends StatefulWidget {
  const MarketplaceScreen({super.key});

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

  final List<String> _categories = [
    'All',
    'Mbegu',
    'Mbolea',
    'Dawa za Wadudu',
    'Mazao',
    'Mifugo',
    'Mashine',
  ];

  @override
  void initState() {
    super.initState();
    _loadProducts();
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
          _selectedCategory == null ||
          _selectedCategory == 'All' ||
          category == _selectedCategory;
      return matchesSearch && matchesCategory;
    }).toList();
  }

  @override
  Widget build(BuildContext context) {
    // The shell's app bar already says "Soko", so the screen opens straight
    // onto search and categories rather than repeating a large title.
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        Padding(
          padding: const EdgeInsets.fromLTRB(16, 4, 16, 0),
          child: TextField(
            controller: _searchController,
            onChanged: (value) => setState(() => _searchQuery = value),
            textInputAction: TextInputAction.search,
            decoration: const InputDecoration(
              hintText: 'Tafuta mbegu, mazao, vifaa...',
              prefixIcon: Icon(Icons.search),
              fillColor: MkColors.surfaceMuted,
            ),
          ),
        ),
        const SizedBox(height: 12),
        SizedBox(
          height: 48,
          child: ListView.separated(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            scrollDirection: Axis.horizontal,
            itemCount: _categories.length,
            separatorBuilder: (_, _) => const SizedBox(width: 8),
            itemBuilder: (context, index) {
              final category = _categories[index];
              final selected =
                  _selectedCategory == category ||
                  (category == 'All' && _selectedCategory == null);
              return FilterChip(
                label: Text(category == 'All' ? 'Zote' : category),
                selected: selected,
                showCheckmark: false,
                side: BorderSide(
                  color: selected ? MkColors.primary : MkColors.border,
                ),
                labelStyle: TextStyle(
                  fontSize: 15,
                  color: selected ? MkColors.primaryDark : MkColors.ink,
                  fontWeight: selected ? FontWeight.w700 : FontWeight.w500,
                ),
                onSelected: (_) => setState(
                  () => _selectedCategory = category == 'All' ? null : category,
                ),
              );
            },
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
          : 'Bidhaa za wauzaji zitaonekana hapa zikiwekwa sokoni.',
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
