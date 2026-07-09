import '../core/theme.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../services/api_service.dart';
import '../providers/auth_provider.dart';
import 'login_modal.dart';

class IoTScreen extends StatefulWidget {
  const IoTScreen({super.key});

  @override
  State<IoTScreen> createState() => _IoTScreenState();
}

class _IoTScreenState extends State<IoTScreen> {
  List<dynamic> _sensors = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadSensors();
  }

  Future<void> _loadSensors() async {
    try {
      final api = context.read<ApiService>();
      final response = await api.get('/iot/my-sensors');
      setState(() {
        _sensors = response.data['sensors'] ?? [];
        _isLoading = false;
      });
    } catch (e) {
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context);

    if (!auth.isAuthenticated) {
      return Scaffold(
        appBar: AppBar(
          title: const Text('Vifaa vya IoT'),
          backgroundColor: MkColors.primary,
          foregroundColor: Colors.white,
        ),
        body: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(Icons.sensors, size: 64, color: Colors.grey),
              const SizedBox(height: 16),
              const Text('Ingia kuona vifaa vyako'),
              const SizedBox(height: 16),
              ElevatedButton(
                onPressed: () => LoginModal.show(context),
                child: const Text('Ingia'),
              ),
            ],
          ),
        ),
      );
    }

    return Scaffold(
      appBar: AppBar(
        title: const Text('Vifaa vya IoT'),
        backgroundColor: MkColors.primary,
        foregroundColor: Colors.white,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _loadSensors,
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  Container(
                    padding: const EdgeInsets.all(20),
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(
                        colors: [Color(0xFF1976D2), Color(0xFF0D47A1)],
                      ),
                      borderRadius: BorderRadius.circular(16),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Icon(Icons.sensors, size: 48, color: Colors.white),
                        const SizedBox(height: 12),
                        const Text(
                          'Vifaa vya Akili',
                          style: TextStyle(
                            fontSize: 24,
                            fontWeight: FontWeight.bold,
                            color: Colors.white,
                          ),
                        ),
                        const SizedBox(height: 8),
                        const Text(
                          'Fuatilia unyevu wa udongo, joto, na hali ya hewa kiotomatiki',
                          style: TextStyle(color: Colors.white70, fontSize: 14),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 24),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text(
                        'Vifaa Vyangu',
                        style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                      ),
                      TextButton.icon(
                        onPressed: () {},
                        icon: const Icon(Icons.add),
                        label: const Text('Ongeza'),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
                  if (_sensors.isEmpty)
                    const Card(
                      child: Padding(
                        padding: EdgeInsets.all(24),
                        child: Center(
                          child: Column(
                            children: [
                              Icon(Icons.sensors_off, size: 48, color: Colors.grey),
                              SizedBox(height: 12),
                              Text('Huna vifaa vilivyounganishwa'),
                            ],
                          ),
                        ),
                      ),
                    )
                  else
                    ..._sensors.map((sensor) => _SensorCard(sensor: sensor)),
                ],
              ),
            ),
    );
  }
}

class _SensorCard extends StatelessWidget {
  final dynamic sensor;

  const _SensorCard({required this.sensor});

  @override
  Widget build(BuildContext context) {
    final lastReading = sensor['last_reading'] ?? {};
    final isOnline = sensor['status'] == 'online';

    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Row(
                  children: [
                    Container(
                      width: 12,
                      height: 12,
                      decoration: BoxDecoration(
                        color: isOnline ? Colors.green : Colors.red,
                        shape: BoxShape.circle,
                      ),
                    ),
                    const SizedBox(width: 8),
                    Text(
                      sensor['name'],
                      style: const TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ],
                ),
                Chip(
                  label: Text('${sensor['battery']}%'),
                  backgroundColor: sensor['battery'] > 20
                      ? Colors.green.withValues(alpha: 0.1)
                      : Colors.red.withValues(alpha: 0.1),
                ),
              ],
            ),
            const SizedBox(height: 8),
            Text(
              sensor['location'],
              style: TextStyle(color: Colors.grey[600]),
            ),
            const SizedBox(height: 16),
            if (lastReading.isNotEmpty) ...[
              const Text(
                'Soma la Mwisho',
                style: TextStyle(fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 8),
              Wrap(
                spacing: 12,
                runSpacing: 8,
                children: [
                  if (lastReading['moisture'] != null)
                    _ReadingChip(
                      icon: Icons.water_drop,
                      label: 'Unyevu',
                      value: '${lastReading['moisture']}%',
                      color: Colors.blue,
                    ),
                  if (lastReading['temperature'] != null)
                    _ReadingChip(
                      icon: Icons.thermostat,
                      label: 'Joto',
                      value: '${lastReading['temperature']}°C',
                      color: Colors.orange,
                    ),
                  if (lastReading['humidity'] != null)
                    _ReadingChip(
                      icon: Icons.water,
                      label: 'Unyevu Hewa',
                      value: '${lastReading['humidity']}%',
                      color: Colors.teal,
                    ),
                  if (lastReading['ph'] != null)
                    _ReadingChip(
                      icon: Icons.science,
                      label: 'pH',
                      value: '${lastReading['ph']}',
                      color: Colors.purple,
                    ),
                ],
              ),
            ],
            const SizedBox(height: 16),
            if (sensor['readings'] != null) ...[
              const Text(
                'Historia ya Siku',
                style: TextStyle(fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 8),
              SizedBox(
                height: 100,
                child: ListView.builder(
                  scrollDirection: Axis.horizontal,
                  itemCount: (sensor['readings'] as List).length,
                  itemBuilder: (context, index) {
                    final reading = sensor['readings'][index];
                    return Container(
                      width: 80,
                      margin: const EdgeInsets.only(right: 8),
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        color: Colors.grey[100],
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Text(
                            reading['time'],
                            style: TextStyle(
                              fontSize: 12,
                              color: Colors.grey[600],
                            ),
                          ),
                          const SizedBox(height: 4),
                          if (reading['moisture'] != null)
                            Text(
                              '${reading['moisture']}%',
                              style: const TextStyle(
                                fontWeight: FontWeight.bold,
                                color: Colors.blue,
                              ),
                            ),
                          if (reading['temp'] != null)
                            Text(
                              '${reading['temp']}°C',
                              style: const TextStyle(
                                fontWeight: FontWeight.bold,
                                color: Colors.orange,
                              ),
                            ),
                        ],
                      ),
                    );
                  },
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}

class _ReadingChip extends StatelessWidget {
  final IconData icon;
  final String label;
  final String value;
  final Color color;

  const _ReadingChip({
    required this.icon,
    required this.label,
    required this.value,
    required this.color,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(20),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 16, color: color),
          const SizedBox(width: 4),
          Text(
            '$label: $value',
            style: TextStyle(color: color, fontWeight: FontWeight.bold),
          ),
        ],
      ),
    );
  }
}
