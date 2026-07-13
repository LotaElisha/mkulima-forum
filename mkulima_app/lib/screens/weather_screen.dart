import '../core/theme.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../services/api_service.dart';

class WeatherScreen extends StatefulWidget {
  const WeatherScreen({super.key});

  @override
  State<WeatherScreen> createState() => _WeatherScreenState();
}

class _WeatherScreenState extends State<WeatherScreen> {
  Map<String, dynamic>? _report;
  bool _isLoading = true;
  bool _failed = false;
  String _location = 'Dar es Salaam';

  static const _locations = [
    'Dar es Salaam',
    'Arusha',
    'Dodoma',
    'Mwanza',
    'Mbeya',
    'Morogoro',
    'Tanga',
    'Iringa',
    'Kigoma',
    'Mtwara',
  ];

  @override
  void initState() {
    super.initState();
    _loadWeather();
  }

  Future<void> _loadWeather() async {
    setState(() {
      _isLoading = true;
      _failed = false;
    });
    try {
      final api = Provider.of<ApiService>(context, listen: false);
      final data = await api.getWeather(location: _location);
      if (!mounted) return;
      setState(() {
        _report = data;
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

  bool get _available => _report?['available'] == true;
  bool get _isStale => _report?['is_stale'] == true;
  Map<String, dynamic>? get _current =>
      _available ? (_report?['current'] as Map<String, dynamic>?) : null;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Hali ya Hewa'),
        backgroundColor: MkColors.primary,
        foregroundColor: Colors.white,
        actions: [
          PopupMenuButton<String>(
            icon: const Icon(Icons.location_on_outlined),
            initialValue: _location,
            onSelected: (value) {
              setState(() => _location = value);
              _loadWeather();
            },
            itemBuilder: (context) => _locations
                .map((l) => PopupMenuItem(value: l, child: Text(l)))
                .toList(),
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _loadWeather,
              child: SingleChildScrollView(
                physics: const AlwaysScrollableScrollPhysics(),
                child: (_failed || !_available)
                    ? _UnavailableState(
                        location: _location,
                        message: _report?['message'],
                        onRetry: _loadWeather,
                      )
                    : Column(
                        children: [
                          if (_isStale)
                            Container(
                              width: double.infinity,
                              color: Colors.amber[100],
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 16, vertical: 8),
                              child: Row(
                                children: [
                                  Icon(Icons.history,
                                      size: 16, color: Colors.amber[900]),
                                  const SizedBox(width: 8),
                                  Expanded(
                                    child: Text(
                                      'Taarifa za awali — mtandao haupatikani kwa sasa.',
                                      style: TextStyle(
                                          fontSize: 12,
                                          color: Colors.amber[900]),
                                    ),
                                  ),
                                ],
                              ),
                            ),

                          // Current weather
                          Container(
                            margin: const EdgeInsets.all(16),
                            padding: const EdgeInsets.all(24),
                            decoration: BoxDecoration(
                              gradient: const LinearGradient(
                                colors: [Color(0xFF42A5F5), Color(0xFF1976D2)],
                                begin: Alignment.topLeft,
                                end: Alignment.bottomRight,
                              ),
                              borderRadius: BorderRadius.circular(20),
                            ),
                            child: Column(
                              children: [
                                Text(
                                  _report?['location']?.toString() ?? _location,
                                  style: const TextStyle(
                                    color: Colors.white,
                                    fontSize: 20,
                                  ),
                                ),
                                const SizedBox(height: 16),
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  children: [
                                    _conditionIcon(
                                        _current?['description']?.toString(),
                                        size: 64,
                                        color: Colors.white),
                                    const SizedBox(width: 16),
                                    Text(
                                      '${_current?['temperature'] ?? '--'}°C',
                                      style: const TextStyle(
                                        color: Colors.white,
                                        fontSize: 48,
                                        fontWeight: FontWeight.bold,
                                      ),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 8),
                                Text(
                                  _current?['description']?.toString() ?? '',
                                  style: const TextStyle(
                                    color: Colors.white70,
                                    fontSize: 16,
                                  ),
                                ),
                                const SizedBox(height: 24),
                                Row(
                                  mainAxisAlignment:
                                      MainAxisAlignment.spaceAround,
                                  children: [
                                    _WeatherDetail(
                                      icon: Icons.water_drop,
                                      label: 'Unyevu',
                                      value:
                                          '${_current?['humidity'] ?? '--'}%',
                                    ),
                                    _WeatherDetail(
                                      icon: Icons.air,
                                      label: 'Upepo',
                                      value:
                                          '${_current?['wind_speed'] ?? '--'} m/s',
                                    ),
                                    _WeatherDetail(
                                      icon: Icons.compress,
                                      label: 'Presha',
                                      value:
                                          '${_current?['pressure'] ?? '--'} hPa',
                                    ),
                                  ],
                                ),
                              ],
                            ),
                          ),

                          // Forecast
                          const Padding(
                            padding: EdgeInsets.symmetric(horizontal: 16),
                            child: Align(
                              alignment: Alignment.centerLeft,
                              child: Text(
                                'Tabiri ya Siku 5',
                                style: TextStyle(
                                  fontSize: 18,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ),
                          ),
                          const SizedBox(height: 8),
                          if ((_report?['forecast'] as List?)?.isEmpty ?? true)
                            const Padding(
                              padding: EdgeInsets.all(16),
                              child: Text(
                                'Tabiri haipatikani kwa sasa.',
                                style: TextStyle(color: Colors.grey),
                              ),
                            )
                          else
                            ...(_report!['forecast'] as List).map(
                              (day) => Card(
                                margin: const EdgeInsets.symmetric(
                                    horizontal: 16, vertical: 4),
                                child: ListTile(
                                  leading: _conditionIcon(
                                      day['description']?.toString()),
                                  title: Text(day['day_name']?.toString() ??
                                      day['date']?.toString() ??
                                      ''),
                                  subtitle:
                                      Text(day['description']?.toString() ?? ''),
                                  trailing: Column(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    crossAxisAlignment: CrossAxisAlignment.end,
                                    children: [
                                      Text(
                                        '${_round(day['temp_max'])}° / ${_round(day['temp_min'])}°',
                                        style: const TextStyle(
                                            fontWeight: FontWeight.bold),
                                      ),
                                      Text(
                                        'Mvua: ${_round(day['rain_chance'])}%',
                                        style: const TextStyle(fontSize: 12),
                                      ),
                                    ],
                                  ),
                                ),
                              ),
                            ),

                          // Farming advisory
                          const SizedBox(height: 16),
                          const Padding(
                            padding: EdgeInsets.symmetric(horizontal: 16),
                            child: Align(
                              alignment: Alignment.centerLeft,
                              child: Text(
                                'Ushauri wa Kilimo',
                                style: TextStyle(
                                  fontSize: 18,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ),
                          ),
                          const SizedBox(height: 8),
                          ...((_report?['advisory'] as List?) ?? []).map(
                            (advice) => Padding(
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 16, vertical: 4),
                              child: Card(
                                color: Colors.green[50],
                                child: ListTile(
                                  leading: const Icon(Icons.eco,
                                      color: MkColors.primary),
                                  title: Text(
                                      advice['title']?.toString() ?? ''),
                                  subtitle: Text(
                                      advice['message']?.toString() ?? ''),
                                ),
                              ),
                            ),
                          ),
                          const SizedBox(height: 32),
                        ],
                      ),
              ),
            ),
    );
  }

  String _round(dynamic value) {
    if (value == null) return '--';
    final n = double.tryParse(value.toString());
    return n == null ? value.toString() : n.round().toString();
  }

  Widget _conditionIcon(String? description,
      {double size = 32, Color? color}) {
    final desc = (description ?? '').toLowerCase();
    IconData icon;
    Color defaultColor;

    if (desc.contains('rain') || desc.contains('drizzle')) {
      icon = Icons.water_drop;
      defaultColor = Colors.blue;
    } else if (desc.contains('thunder') || desc.contains('storm')) {
      icon = Icons.flash_on;
      defaultColor = Colors.purple;
    } else if (desc.contains('cloud')) {
      icon = Icons.cloud;
      defaultColor = Colors.grey;
    } else {
      icon = Icons.wb_sunny;
      defaultColor = Colors.orange;
    }

    return Icon(icon, color: color ?? defaultColor, size: size);
  }
}

class _UnavailableState extends StatelessWidget {
  final String location;
  final String? message;
  final VoidCallback onRetry;

  const _UnavailableState({
    required this.location,
    required this.onRetry,
    this.message,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.all(32),
      child: Column(
        children: [
          const SizedBox(height: 80),
          const Icon(Icons.cloud_off, size: 72, color: Colors.grey),
          const SizedBox(height: 16),
          Text(
            message ??
                'Taarifa za hali ya hewa za $location hazipatikani kwa sasa.',
            textAlign: TextAlign.center,
            style: const TextStyle(fontSize: 16),
          ),
          const SizedBox(height: 16),
          ElevatedButton.icon(
            onPressed: onRetry,
            icon: const Icon(Icons.refresh),
            label: const Text('Jaribu Tena'),
            style: ElevatedButton.styleFrom(
              backgroundColor: MkColors.primary,
              foregroundColor: Colors.white,
            ),
          ),
        ],
      ),
    );
  }
}

class _WeatherDetail extends StatelessWidget {
  final IconData icon;
  final String label;
  final String value;

  const _WeatherDetail({
    required this.icon,
    required this.label,
    required this.value,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Icon(icon, color: Colors.white70, size: 24),
        const SizedBox(height: 4),
        Text(value,
            style: const TextStyle(
                color: Colors.white, fontWeight: FontWeight.bold)),
        Text(label, style: const TextStyle(color: Colors.white70, fontSize: 12)),
      ],
    );
  }
}
