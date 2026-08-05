import 'dart:typed_data';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:provider/provider.dart';
import '../core/strings.dart';
import '../core/theme.dart';
import '../services/api_service.dart';
import '../providers/connectivity_provider.dart';
import '../widgets/mk_button.dart';

/// Standalone page wrapper so the flagship AI Plant Scanner can be pushed
/// from anywhere (App Bar button, center FAB, home hero) with one tap.
class ScannerPage extends StatelessWidget {
  const ScannerPage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Column(
          children: [
            Text(MkStrings.scannerBrand, style: TextStyle(fontSize: 17)),
            Text(
              MkStrings.scannerTagline,
              style: TextStyle(fontSize: 11, color: Colors.white70),
            ),
          ],
        ),
        backgroundColor: MkColors.primary,
        foregroundColor: Colors.white,
      ),
      body: const ScannerScreen(),
    );
  }
}

class ScannerScreen extends StatefulWidget {
  const ScannerScreen({super.key});

  @override
  State<ScannerScreen> createState() => _ScannerScreenState();
}

class _ScannerScreenState extends State<ScannerScreen> {
  // Bytes instead of dart:io File so the scanner works on mobile AND web
  // (Image.file / MultipartFile.fromFile crash on Flutter Web).
  Uint8List? _imageBytes;
  String _imageName = 'plant.jpg';
  bool _isScanning = false;
  Map<String, dynamic>? _result;

  Future<void> _pickImage(ImageSource source) async {
    final picker = ImagePicker();
    final picked = await picker.pickImage(source: source, maxWidth: 1024);
    if (picked != null) {
      final bytes = await picked.readAsBytes();
      if (!mounted) return;
      setState(() {
        _imageBytes = bytes;
        _imageName = picked.name;
        _result = null;
      });
    }
  }

  Future<void> _scanDisease() async {
    if (_imageBytes == null) return;

    setState(() => _isScanning = true);

    try {
      final api = Provider.of<ApiService>(context, listen: false);
      final result = await api.scanDisease(_imageBytes!, _imageName);
      setState(() {
        _result = result;
        _isScanning = false;
      });
    } catch (e) {
      setState(() => _isScanning = false);
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Kosa: $e')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final connectivity = Provider.of<ConnectivityProvider>(context);

    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        children: [
          if (!connectivity.isOnline)
            Card(
              color: Colors.orange[50],
              child: const Padding(
                padding: EdgeInsets.all(12),
                child: Row(
                  children: [
                    Icon(Icons.wifi_off, color: Colors.orange),
                    SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        'Hakuna mtandao. Tumia AI ya ndani (inakuja).',
                        style: TextStyle(fontSize: 12),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          const SizedBox(height: 16),
          if (_imageBytes != null)
            ClipRRect(
              borderRadius: BorderRadius.circular(16),
              child: Image.memory(
                _imageBytes!,
                height: 250,
                width: double.infinity,
                fit: BoxFit.cover,
              ),
            )
          else
            Container(
              height: 250,
              width: double.infinity,
              decoration: BoxDecoration(
                color: Colors.grey[200],
                borderRadius: BorderRadius.circular(16),
              ),
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.camera_alt, size: 60, color: Colors.grey[400]),
                  const SizedBox(height: 12),
                  Text(
                    MkStrings.scanPlant,
                    style: TextStyle(color: Colors.grey[600]),
                  ),
                ],
              ),
            ),
          const SizedBox(height: 24),
          Row(
            children: [
              Expanded(
                child: ElevatedButton.icon(
                  onPressed: () => _pickImage(ImageSource.camera),
                  icon: const Icon(Icons.camera_alt),
                  label: const Text('Kamera'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: MkColors.primary,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 12),
                  ),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: ElevatedButton.icon(
                  onPressed: () => _pickImage(ImageSource.gallery),
                  icon: const Icon(Icons.photo_library),
                  label: const Text('Ghalari'),
                  style: ElevatedButton.styleFrom(
                    padding: const EdgeInsets.symmetric(vertical: 12),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          if (_imageBytes != null)
            MkButton(
              label: MkStrings.titleScanner,
              icon: Icons.biotech,
              loading: _isScanning,
              onPressed: _scanDisease,
            ),
          if (_result != null) ...[
            const SizedBox(height: 24),
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Icon(Icons.medical_services, color: Colors.red[700]),
                        const SizedBox(width: 8),
                        Text(
                          'Matokeo ya Uchunguzi',
                          style: Theme.of(context).textTheme.titleLarge,
                        ),
                      ],
                    ),
                    const Divider(),
                    _buildResultRow('Ugonjwa', _result!['disease_name'] ?? 'Haijulikani'),
                    _buildResultRow('Uthibitisho', '${((_result!['confidence'] ?? 0) * 100).toStringAsFixed(1)}%'),
                    _buildResultRow('Dalili', _result!['symptoms'] ?? 'Hakuna maelezo'),
                    _buildResultRow('Tiba', _result!['treatment'] ?? 'Hakuna maelezo'),
                    _buildResultRow('Kinga', _result!['prevention'] ?? 'Hakuna maelezo'),
                  ],
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildResultRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 100,
            child: Text(
              '$label:',
              style: const TextStyle(fontWeight: FontWeight.bold),
            ),
          ),
          Expanded(
            child: Text(value),
          ),
        ],
      ),
    );
  }
}
