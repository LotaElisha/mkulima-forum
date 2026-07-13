import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../core/theme.dart';
import '../services/api_service.dart';

/// Bei za Masoko — real recorded market prices with filters.
/// Every price shows the date it was recorded; entries older than 14 days
/// arrive flagged `is_stale` from the API and are labelled clearly.
class MarketPricesScreen extends StatefulWidget {
  const MarketPricesScreen({super.key});

  @override
  State<MarketPricesScreen> createState() => _MarketPricesScreenState();
}

class _MarketPricesScreenState extends State<MarketPricesScreen> {
  List<dynamic> _prices = [];
  List<dynamic> _commodities = [];
  List<dynamic> _regions = [];
  String? _commodity;
  String? _region;
  bool _isLoading = true;
  bool _failed = false;

  @override
  void initState() {
    super.initState();
    _loadFilters();
    _loadPrices();
  }

  Future<void> _loadFilters() async {
    try {
      final api = context.read<ApiService>();
      final response = await api.get('/market-prices/filters');
      setState(() {
        _commodities = response.data['commodities'] ?? [];
        _regions = response.data['regions'] ?? [];
      });
    } catch (_) {
      // Filters are optional; the list still works without them.
    }
  }

  Future<void> _loadPrices() async {
    setState(() {
      _isLoading = true;
      _failed = false;
    });
    try {
      final api = context.read<ApiService>();
      final response = await api.get('/market-prices', queryParameters: {
        'latest': 1,
        if (_commodity != null) 'commodity': _commodity,
        if (_region != null) 'region': _region,
      });
      setState(() {
        _prices = response.data['data'] ?? [];
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _isLoading = false;
        _failed = true;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Bei za Masoko'),
        backgroundColor: MkColors.primary,
        foregroundColor: Colors.white,
      ),
      body: Column(
        children: [
          // Filters
          Padding(
            padding: const EdgeInsets.all(12),
            child: Row(
              children: [
                Expanded(
                  child: DropdownButtonFormField<String?>(
                    initialValue: _commodity,
                    isExpanded: true,
                    decoration: const InputDecoration(
                      labelText: 'Zao',
                      border: OutlineInputBorder(),
                      contentPadding:
                          EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                    ),
                    items: [
                      const DropdownMenuItem(value: null, child: Text('Yote')),
                      ..._commodities.map((c) => DropdownMenuItem(
                          value: c.toString(), child: Text(c.toString()))),
                    ],
                    onChanged: (value) {
                      setState(() => _commodity = value);
                      _loadPrices();
                    },
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: DropdownButtonFormField<String?>(
                    initialValue: _region,
                    isExpanded: true,
                    decoration: const InputDecoration(
                      labelText: 'Mkoa',
                      border: OutlineInputBorder(),
                      contentPadding:
                          EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                    ),
                    items: [
                      const DropdownMenuItem(value: null, child: Text('Yote')),
                      ..._regions.map((r) => DropdownMenuItem(
                          value: r.toString(), child: Text(r.toString()))),
                    ],
                    onChanged: (value) {
                      setState(() => _region = value);
                      _loadPrices();
                    },
                  ),
                ),
              ],
            ),
          ),

          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator())
                : RefreshIndicator(
                    onRefresh: _loadPrices,
                    child: _failed
                        ? ListView(
                            children: const [
                              SizedBox(height: 120),
                              Icon(Icons.cloud_off,
                                  size: 64, color: Colors.grey),
                              SizedBox(height: 12),
                              Center(
                                  child: Text(
                                      'Imeshindikana kupakia bei. Vuta chini kujaribu tena.')),
                            ],
                          )
                        : _prices.isEmpty
                            ? ListView(
                                children: const [
                                  SizedBox(height: 120),
                                  Icon(Icons.price_change_outlined,
                                      size: 64, color: Colors.grey),
                                  SizedBox(height: 12),
                                  Center(
                                      child: Text(
                                          'Hakuna bei zilizorekodiwa bado.')),
                                ],
                              )
                            : ListView.builder(
                                padding:
                                    const EdgeInsets.symmetric(horizontal: 12),
                                itemCount: _prices.length,
                                itemBuilder: (context, index) =>
                                    _PriceCard(price: _prices[index]),
                              ),
                  ),
          ),
        ],
      ),
    );
  }
}

class _PriceCard extends StatelessWidget {
  final dynamic price;

  const _PriceCard({required this.price});

  @override
  Widget build(BuildContext context) {
    final trend = price['trend']?.toString() ?? 'stable';
    final isStale = price['is_stale'] == true;

    final (trendIcon, trendColor) = switch (trend) {
      'up' => (Icons.trending_up, Colors.green),
      'down' => (Icons.trending_down, Colors.red),
      _ => (Icons.trending_flat, Colors.grey),
    };

    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Expanded(
                  child: Text(
                    '${price['commodity']}',
                    style: const TextStyle(
                        fontSize: 16, fontWeight: FontWeight.bold),
                  ),
                ),
                Icon(trendIcon, color: trendColor, size: 20),
              ],
            ),
            const SizedBox(height: 4),
            Text(
              '${price['market']}, ${price['region']}',
              style: TextStyle(color: Colors.grey[600], fontSize: 13),
            ),
            const SizedBox(height: 8),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  'TZS ${price['min_price']} – ${price['max_price']} / ${price['unit']}',
                  style: const TextStyle(
                    fontWeight: FontWeight.bold,
                    color: MkColors.primary,
                  ),
                ),
                Row(
                  children: [
                    if (isStale) ...[
                      Icon(Icons.history, size: 14, color: Colors.amber[800]),
                      const SizedBox(width: 4),
                    ],
                    Text(
                      '${price['price_date']}',
                      style: TextStyle(
                        fontSize: 12,
                        color: isStale ? Colors.amber[800] : Colors.grey[600],
                      ),
                    ),
                  ],
                ),
              ],
            ),
            if (price['source'] != null)
              Padding(
                padding: const EdgeInsets.only(top: 4),
                child: Text(
                  'Chanzo: ${price['source']}',
                  style: TextStyle(fontSize: 11, color: Colors.grey[500]),
                ),
              ),
          ],
        ),
      ),
    );
  }
}
