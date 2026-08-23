import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../core/strings.dart';
import '../core/theme.dart';
import '../models/product.dart';
import '../services/api_service.dart';
import '../widgets/mk_empty_state.dart';
import '../widgets/mk_product_tile.dart';
import 'product_detail_screen.dart';
import 'weather_screen.dart';

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
  String? _selectedCategory;
  Map<String, dynamic>? _weather;

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
    _loadWeather();
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

  Future<void> _loadWeather() async {
    try {
      final api = context.read<ApiService>();
      final response = await api.getWeather();
      if (!mounted) return;
      setState(() {
        // Only show the chip when real data is available — never a made-up
        // default temperature.
        _weather = response['available'] == true
            ? response['current'] as Map<String, dynamic>?
            : null;
      });
    } catch (e) {
      // Weather is optional, ignore errors
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
    return Column(
      children: [
        Padding(
          padding: const EdgeInsets.fromLTRB(16, 12, 16, 8),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  const Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Soko la Mkulima',
                          style: TextStyle(
                            fontFamily: 'serif',
                            fontSize: 28,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                        SizedBox(height: 3),
                        Text(
                          'Nunua na uza kwa uaminifu',
                          style: TextStyle(color: MkColors.muted),
                        ),
                      ],
                    ),
                  ),
                  if (_weather != null)
                    ActionChip(
                      avatar: Icon(
                        _getWeatherIcon(_weather!['description']?.toString()),
                        size: 18,
                      ),
                      label: Text('${_weather!['temperature']}°C'),
                      onPressed: () => Navigator.of(context).push(
                        MaterialPageRoute(
                          builder: (_) => const WeatherScreen(),
                        ),
                      ),
                    ),
                ],
              ),
              const SizedBox(height: 16),
              TextField(
                onChanged: (value) => setState(() => _searchQuery = value),
                decoration: const InputDecoration(
                  hintText: 'Tafuta mbegu, mazao, vifaa...',
                  prefixIcon: Icon(Icons.search),
                  suffixIcon: Icon(Icons.tune),
                ),
              ),
              const SizedBox(height: 12),
              SizedBox(
                height: 40,
                child: ListView.builder(
                  scrollDirection: Axis.horizontal,
                  itemCount: _categories.length,
                  itemBuilder: (context, index) {
                    final category = _categories[index];
                    final selected =
                        _selectedCategory == category ||
                        (category == 'All' && _selectedCategory == null);
                    return Padding(
                      padding: const EdgeInsets.only(right: 8),
                      child: FilterChip(
                        label: Text(category == 'All' ? 'Zote' : category),
                        selected: selected,
                        onSelected: (_) => setState(
                          () => _selectedCategory = category == 'All'
                              ? null
                              : category,
                        ),
                        selectedColor: MkColors.primary,
                        checkmarkColor: Colors.white,
                        labelStyle: TextStyle(
                          color: selected ? Colors.white : MkColors.charcoal,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    );
                  },
                ),
              ),
            ],
          ),
        ),
        Expanded(
          child: _isLoading
              ? const Center(child: CircularProgressIndicator())
              : _error != null
              ? _buildErrorView()
              : _filteredProducts.isEmpty
              ? _buildEmptyView()
              : _buildProductGrid(),
        ),
      ],
    );
  }

  IconData _getWeatherIcon(String? condition) {
    // OpenWeather descriptions are phrases ("overcast clouds", "light rain"),
    // so match by substring.
    final desc = (condition ?? '').toLowerCase();
    if (desc.contains('thunder') || desc.contains('storm')) {
      return Icons.thunderstorm;
    }
    if (desc.contains('rain') || desc.contains('drizzle')) {
      return Icons.water_drop;
    }
    if (desc.contains('cloud')) {
      return Icons.wb_cloudy;
    }
    return Icons.wb_sunny;
  }

  Widget _buildErrorView() {
    return MkEmptyState(
      icon: Icons.error_outline,
      title: MkStrings.productsLoadFailed,
      subtitle: _error,
      actionLabel: MkStrings.retry,
      onAction: _loadProducts,
    );
  }

  Widget _buildEmptyView() {
    return const MkEmptyState(
      icon: Icons.search_off,
      title: MkStrings.noProductsFound,
    );
  }

  Widget _buildProductGrid() {
    return RefreshIndicator(
      onRefresh: _loadProducts,
      child: GridView.builder(
        padding: const EdgeInsets.all(16),
        gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
          crossAxisCount: 2,
          childAspectRatio: 0.75,
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
