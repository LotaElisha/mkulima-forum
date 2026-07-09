import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'dart:async';
import 'dart:ui';
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

  late PageController _pageController;
  int _currentSliderIndex = 0;
  Timer? _sliderTimer;

  final List<Map<String, String>> _sliderBanners = const [
    {
      'title': 'Pembejeo Bora za Kilimo',
      'subtitle': 'Mbegu bora, mbolea, na viatilifu shambani kwako.',
      'gradient': 'green',
    },
    {
      'title': 'Miamala Salama na Escrow',
      'subtitle': 'Linda malipo ya mazao yako mpaka mzigo utapopokelewa.',
      'gradient': 'orange',
    },
    {
      'title': 'Msaidizi wa Kilimo wa AI',
      'subtitle': 'Uliza maswali na kagua magonjwa ya mimea live.',
      'gradient': 'blue',
    },
  ];

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
    _pageController = PageController(initialPage: 0);
    _loadProducts();
    _loadWeather();
    _startSliderAutoPlay();
  }

  @override
  void dispose() {
    _pageController.dispose();
    _sliderTimer?.cancel();
    super.dispose();
  }

  void _startSliderAutoPlay() {
    _sliderTimer = Timer.periodic(const Duration(seconds: 4), (timer) {
      if (_pageController.hasClients) {
        int nextPage = (_currentSliderIndex + 1) % _sliderBanners.length;
        _pageController.animateToPage(
          nextPage,
          duration: const Duration(milliseconds: 600),
          curve: Curves.easeInOutCubic,
        );
      }
    });
  }

  Future<void> _loadProducts() async {
    try {
      setState(() {
        _isLoading = true;
        _error = null;
      });
      final api = context.read<ApiService>();
      final response = await api.getProducts();
      setState(() {
        _products = response as List<dynamic>;
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  Future<void> _loadWeather() async {
    try {
      final api = context.read<ApiService>();
      final response = await api.getWeather();
      setState(() {
        _weather = response;
      });
    } catch (e) {
      // Weather is optional, ignore errors
    }
  }

  List<dynamic> get _filteredProducts {
    return _products.where((product) {
      // Handle both Product objects and Map data
      final name = product is Product ? product.name : product['name']?.toString() ?? '';
      final description = product is Product ? product.description : product['description']?.toString() ?? '';
      final category = product is Product ? product.categoryId : product['category']?.toString() ?? '';
      
      final matchesSearch = _searchQuery.isEmpty ||
          name.toLowerCase().contains(_searchQuery.toLowerCase()) ||
          description.toLowerCase().contains(_searchQuery.toLowerCase());
      final matchesCategory = _selectedCategory == null ||
          _selectedCategory == 'All' ||
          category == _selectedCategory;
      return matchesSearch && matchesCategory;
    }).toList();
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        // Hero section with weather and slider
        Container(
          padding: const EdgeInsets.all(16),
          decoration: const BoxDecoration(
            gradient: LinearGradient(
              colors: [MkColors.primary, MkColors.primaryDark],
            ),
          ),
          child: Column(
            children: [
              // Promotional Slider with floating Weather overlay
              Stack(
                clipBehavior: Clip.none,
                children: [
                  _buildPromotionalSlider(),
                  if (_weather != null)
                    Positioned(
                      top: 8,
                      right: 8,
                      child: GestureDetector(
                        onTap: () => Navigator.of(context).push(
                          MaterialPageRoute(builder: (_) => const WeatherScreen()),
                        ),
                        child: ClipRRect(
                          borderRadius: BorderRadius.circular(14),
                          child: BackdropFilter(
                            filter: ImageFilter.blur(sigmaX: 12, sigmaY: 12),
                            child: Container(
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                              decoration: BoxDecoration(
                                color: Colors.black.withValues(alpha: 0.35),
                                borderRadius: BorderRadius.circular(14),
                                border: Border.all(
                                  color: Colors.white.withValues(alpha: 0.2),
                                ),
                              ),
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Icon(
                                    _getWeatherIcon(_weather!['condition']),
                                    color: Colors.white,
                                    size: 18,
                                  ),
                                  const SizedBox(width: 6),
                                  Text(
                                    '${_weather!['temperature'] ?? 28}°C',
                                    style: const TextStyle(
                                      color: Colors.white,
                                      fontSize: 13,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        ),
                      ),
                    ),
                ],
              ),
              const SizedBox(height: 16),
              // Search bar
              Container(
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: TextField(
                  onChanged: (value) => setState(() => _searchQuery = value),
                  style: const TextStyle(color: Colors.white),
                  decoration: InputDecoration(
                    hintText: MkStrings.searchProducts,
                    hintStyle: TextStyle(color: Colors.white.withValues(alpha: 0.7)),
                    prefixIcon: const Icon(Icons.search, color: Colors.white70),
                    border: InputBorder.none,
                    contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                  ),
                ),
              ),
              const SizedBox(height: 12),
              // Categories
              SizedBox(
                height: 36,
                child: ListView.builder(
                  scrollDirection: Axis.horizontal,
                  itemCount: _categories.length,
                  itemBuilder: (context, index) {
                    final category = _categories[index];
                    final isSelected = _selectedCategory == category ||
                        (category == 'All' && _selectedCategory == null);
                    return Padding(
                      padding: const EdgeInsets.only(right: 8),
                      child: FilterChip(
                        label: Text(category),
                        selected: isSelected,
                        onSelected: (selected) {
                          setState(() {
                            _selectedCategory = selected ? category : null;
                            if (_selectedCategory == 'All') _selectedCategory = null;
                          });
                        },
                        backgroundColor: Colors.white.withValues(alpha: 0.2),
                        selectedColor: Colors.white,
                        labelStyle: TextStyle(
                          color: isSelected ? MkColors.primary : Colors.white,
                          fontSize: 12,
                        ),
                        padding: EdgeInsets.zero,
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
    switch (condition?.toLowerCase()) {
      case 'sunny':
      case 'clear':
        return Icons.wb_sunny;
      case 'cloudy':
        return Icons.wb_cloudy;
      case 'rainy':
      case 'rain':
        return Icons.water_drop;
      case 'storm':
      case 'thunderstorm':
        return Icons.thunderstorm;
      default:
        return Icons.wb_sunny;
    }
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

  Widget _buildPromotionalSlider() {
    return Column(
      children: [
        SizedBox(
          height: 120,
          child: PageView.builder(
            controller: _pageController,
            onPageChanged: (index) {
              setState(() => _currentSliderIndex = index);
            },
            itemCount: _sliderBanners.length,
            itemBuilder: (context, index) {
              final banner = _sliderBanners[index];
              final gradient = _getSliderGradient(banner['gradient']!);
              final icon = _getSliderIcon(banner['gradient']!);

              return Container(
                margin: const EdgeInsets.symmetric(horizontal: 4),
                decoration: BoxDecoration(
                  gradient: gradient,
                  borderRadius: BorderRadius.circular(14),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.15),
                      blurRadius: 6,
                      offset: const Offset(0, 3),
                    )
                  ],
                ),
                padding: const EdgeInsets.all(16),
                child: Row(
                  children: [
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Text(
                            banner['title']!,
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          const SizedBox(height: 6),
                          Text(
                            banner['subtitle']!,
                            style: const TextStyle(
                              color: Colors.white70,
                              fontSize: 11,
                              height: 1.2,
                            ),
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(width: 12),
                    Icon(
                      icon,
                      size: 52,
                      color: Colors.white.withValues(alpha: 0.25),
                    ),
                  ],
                ),
              );
            },
          ),
        ),
        const SizedBox(height: 8),
        // Indicators
        Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: List.generate(
            _sliderBanners.length,
            (index) => Container(
              margin: const EdgeInsets.symmetric(horizontal: 3),
              width: _currentSliderIndex == index ? 16 : 6,
              height: 6,
              decoration: BoxDecoration(
                color: _currentSliderIndex == index ? Colors.white : Colors.white54,
                borderRadius: BorderRadius.circular(3),
              ),
            ),
          ),
        ),
      ],
    );
  }

  LinearGradient _getSliderGradient(String type) {
    if (type == 'orange') {
      return const LinearGradient(
        colors: [MkColors.accent, Color(0xFFE65100)],
        begin: Alignment.topLeft,
        end: Alignment.bottomRight,
      );
    } else if (type == 'blue') {
      return const LinearGradient(
        colors: [Color(0xFF0288D1), Color(0xFF0A4F8F)],
        begin: Alignment.topLeft,
        end: Alignment.bottomRight,
      );
    } else {
      return const LinearGradient(
        colors: [Color(0xFF81C784), MkColors.primary],
        begin: Alignment.topLeft,
        end: Alignment.bottomRight,
      );
    }
  }

  IconData _getSliderIcon(String type) {
    if (type == 'orange') {
      return Icons.shield_outlined;
    } else if (type == 'blue') {
      return Icons.psychology_outlined;
    } else {
      return Icons.agriculture_outlined;
    }
  }
}

