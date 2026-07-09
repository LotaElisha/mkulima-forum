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
  Map<String, dynamic>? _weather;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadWeather();
  }

  Future<void> _loadWeather() async {
    try {
      final api = Provider.of<ApiService>(context, listen: false);
      final data = await api.getWeather();
      setState(() {
        _weather = data;
        _isLoading = false;
      });
    } catch (e) {
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Hali ya Hewa'),
        backgroundColor: MkColors.primary,
        foregroundColor: Colors.white,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _loadWeather,
              child: SingleChildScrollView(
                physics: const AlwaysScrollableScrollPhysics(),
                child: Column(
                  children: [
                    // Current Weather
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
                            _weather?['location']?['city'] ?? 'Dar es Salaam',
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 20,
                            ),
                          ),
                          const SizedBox(height: 16),
                          Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              const Icon(Icons.wb_sunny,
                                  size: 64, color: Colors.white),
                              const SizedBox(width: 16),
                              Text(
                                '${_weather?['current']?['temp'] ?? 28}°C',
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
                            _weather?['current']?['description'] ??
                                'Clear sky',
                            style: const TextStyle(
                              color: Colors.white70,
                              fontSize: 16,
                            ),
                          ),
                          const SizedBox(height: 24),
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceAround,
                            children: [
                              _WeatherDetail(
                                icon: Icons.water_drop,
                                label: 'Unyevu',
                                value:
                                    '${_weather?['current']?['humidity'] ?? 70}%',
                              ),
                              _WeatherDetail(
                                icon: Icons.air,
                                label: 'Upepo',
                                value:
                                    '${_weather?['current']?['wind_speed'] ?? 15} km/h',
                              ),
                              _WeatherDetail(
                                icon: Icons.visibility,
                                label: 'Mwangaza',
                                value:
                                    '${_weather?['current']?['uvi'] ?? 5} UV',
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
                    if (_weather?['forecast'] != null)
                      ...(_weather!['forecast'] as List).map((day) => Card(
                            margin: const EdgeInsets.symmetric(
                                horizontal: 16, vertical: 4),
                            child: ListTile(
                              leading: _getWeatherIcon(day['condition']),
                              title: Text(day['day'] ?? ''),
                              subtitle: Text(day['description'] ?? ''),
                              trailing: Column(
                                mainAxisAlignment: MainAxisAlignment.center,
                                crossAxisAlignment: CrossAxisAlignment.end,
                                children: [
                                  Text(
                                    '${day['temp_max']}° / ${day['temp_min']}°',
                                    style: const TextStyle(
                                        fontWeight: FontWeight.bold),
                                  ),
                                  Text(
                                    'Mvua: ${day['rain_chance']}%',
                                    style: const TextStyle(fontSize: 12),
                                  ),
                                ],
                              ),
                            ),
                          )),

                    // Farming Advisory
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
                    if (_weather?['advisory']?['farming_tips'] != null)
                      ...(_weather!['advisory']['farming_tips'] as List).map(
                        (tip) => Padding(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 16, vertical: 4),
                          child: Card(
                            color: Colors.green[50],
                            child: ListTile(
                              leading: const Icon(Icons.eco,
                                  color: MkColors.primary),
                              title: Text(tip),
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

  Widget _getWeatherIcon(String? condition) {
    IconData icon = Icons.wb_sunny;
    Color color = Colors.orange;

    switch (condition?.toLowerCase()) {
      case 'rain':
      case 'drizzle':
        icon = Icons.water_drop;
        color = Colors.blue;
        break;
      case 'clouds':
        icon = Icons.cloud;
        color = Colors.grey;
        break;
      case 'thunderstorm':
        icon = Icons.flash_on;
        color = Colors.purple;
        break;
      default:
        icon = Icons.wb_sunny;
        color = Colors.orange;
    }

    return Icon(icon, color: color, size: 32);
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
