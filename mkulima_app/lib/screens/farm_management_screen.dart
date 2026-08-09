import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:mkulima_app/core/theme.dart';
import 'package:mkulima_app/services/api_service.dart';

class FarmManagementScreen extends StatefulWidget {
  const FarmManagementScreen({super.key});

  @override
  State<FarmManagementScreen> createState() => _FarmManagementScreenState();
}

class _FarmManagementScreenState extends State<FarmManagementScreen> {
  bool _loading = true;
  List<dynamic> _farms = [];
  String? _error;

  final _nameController = TextEditingController();
  final _locationController = TextEditingController();
  final _acresController = TextEditingController();
  final _cropController = TextEditingController();
  final String _selectedSoil = 'Loam';

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) => _loadFarms());
  }

  Future<void> _loadFarms() async {
    setState(() {
      _loading = true;
      _error = null;
    });

    try {
      final api = Provider.of<ApiService>(context, listen: false);
      final data = await api.getFarms();
      setState(() {
        _farms = data;
        _loading = false;
      });
    } catch (e) {
      setState(() {
        _error = ApiService.formatError(e);
        _loading = false;
      });
    }
  }

  void _showAddFarmDialog() {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Text(
          'Usajili wa Shamba Jipya',
          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
        ),
        content: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              TextField(
                controller: _nameController,
                decoration: const InputDecoration(
                  labelText: 'Jina la Shamba (mf. Shamba la Mahindi)',
                  border: OutlineInputBorder(),
                ),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: _locationController,
                decoration: const InputDecoration(
                  labelText: 'Eneo / Eneo la Kilimo (mf. Mbeya Vijijini)',
                  border: OutlineInputBorder(),
                ),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: _acresController,
                keyboardType: TextInputType.number,
                decoration: const InputDecoration(
                  labelText: 'Ukubwa (Akeri)',
                  border: OutlineInputBorder(),
                ),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: _cropController,
                decoration: const InputDecoration(
                  labelText: 'Aina ya Zao (mf. Mahindi, Mpunga, Avocado)',
                  border: OutlineInputBorder(),
                ),
              ),
            ],
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Ghairi'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: MkColors.primary,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            ),
            onPressed: () async {
              if (_nameController.text.isEmpty || _acresController.text.isEmpty) return;
              Navigator.pop(ctx);
              try {
                final api = Provider.of<ApiService>(context, listen: false);
                await api.createFarm({
                  'name': _nameController.text,
                  'location': _locationController.text.isEmpty ? 'Tanzania' : _locationController.text,
                  'size_acres': double.tryParse(_acresController.text) ?? 1.0,
                  'crop_type': _cropController.text.isEmpty ? 'Mahindi' : _cropController.text,
                  'soil_type': _selectedSoil,
                  'status': 'active',
                });
                _nameController.clear();
                _locationController.clear();
                _acresController.clear();
                _cropController.clear();
                _loadFarms();
                if (mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(content: Text('Shamba limesajiliwa kikamilifu!')),
                  );
                }
              } catch (e) {
                if (mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(content: Text(ApiService.formatError(e))),
                  );
                }
              }
            },
            child: const Text('Sajili Shamba'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Usimamizi wa Mashamba'),
        backgroundColor: MkColors.primary,
        foregroundColor: Colors.white,
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _showAddFarmDialog,
        backgroundColor: MkColors.leafGreen,
        icon: const Icon(Icons.add, color: Colors.white),
        label: const Text('Sajili Shamba', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Text(_error!, style: const TextStyle(color: Colors.red)),
                      const SizedBox(height: 12),
                      ElevatedButton(
                        onPressed: _loadFarms,
                        child: const Text('Jaribu Tena'),
                      ),
                    ],
                  ),
                )
              : _farms.isEmpty
                  ? Center(
                      child: Padding(
                        padding: const EdgeInsets.all(24.0),
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(Icons.agriculture, size: 64, color: Colors.grey[400]),
                            const SizedBox(height: 16),
                            const Text(
                              'Hujasajili Shamba Lolote Bado',
                              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                            ),
                            const SizedBox(height: 8),
                            const Text(
                              'Sajili mashamba yako ili kufuatilia mzunguko wa mazao, tarehe za kupanda na gharama za kilimo.',
                              textAlign: TextAlign.center,
                              style: TextStyle(color: Colors.grey),
                            ),
                            const SizedBox(height: 24),
                            ElevatedButton.icon(
                              onPressed: _showAddFarmDialog,
                              style: ElevatedButton.styleFrom(
                                backgroundColor: MkColors.primary,
                                foregroundColor: Colors.white,
                                padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                              ),
                              icon: const Icon(Icons.add),
                              label: const Text('Sajili Shamba la Kwanza'),
                            ),
                          ],
                        ),
                      ),
                    )
                  : ListView.builder(
                      padding: const EdgeInsets.all(16),
                      itemCount: _farms.length,
                      itemBuilder: (context, index) {
                        final farm = _farms[index];
                        return Card(
                          margin: const EdgeInsets.only(bottom: 12),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                          child: Padding(
                            padding: const EdgeInsets.all(16),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    Text(
                                      farm['name'] ?? 'Shamba',
                                      style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                                    ),
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                      decoration: BoxDecoration(
                                        color: MkColors.leafGreen.withValues(alpha: 0.1),
                                        borderRadius: BorderRadius.circular(12),
                                      ),
                                      child: Text(
                                        farm['status'] ?? 'active',
                                        style: const TextStyle(
                                          color: MkColors.leafGreen,
                                          fontWeight: FontWeight.bold,
                                          fontSize: 12,
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 8),
                                Row(
                                  children: [
                                    const Icon(Icons.location_on, size: 14, color: Colors.grey),
                                    const SizedBox(width: 4),
                                    Text(
                                      farm['location'] ?? 'Tanzania',
                                      style: const TextStyle(color: Colors.grey, fontSize: 13),
                                    ),
                                    const SizedBox(width: 16),
                                    const Icon(Icons.square_foot, size: 14, color: Colors.grey),
                                    const SizedBox(width: 4),
                                    Text(
                                      '${farm['size_acres']} Akeri',
                                      style: const TextStyle(color: Colors.grey, fontSize: 13),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 8),
                                Container(
                                  padding: const EdgeInsets.all(8),
                                  decoration: BoxDecoration(
                                    color: MkColors.surface,
                                    borderRadius: BorderRadius.circular(10),
                                  ),
                                  child: Row(
                                    children: [
                                      const Icon(Icons.grass, color: MkColors.primary, size: 18),
                                      const SizedBox(width: 8),
                                      Text(
                                        'Zao: ${farm['crop_type']}',
                                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                          ),
                        );
                      },
                    ),
    );
  }
}
