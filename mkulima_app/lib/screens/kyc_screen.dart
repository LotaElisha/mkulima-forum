import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../services/api_service.dart';

class KycScreen extends StatefulWidget {
  const KycScreen({super.key});

  @override
  State<KycScreen> createState() => _KycScreenState();
}

class _KycScreenState extends State<KycScreen> {
  String? _kycStatus;
  bool _isLoading = true;
  bool _isSubmitting = false;

  final _idNumberController = TextEditingController();
  final _fullNameController = TextEditingController();
  final _addressController = TextEditingController();
  final _regionController = TextEditingController();
  final _districtController = TextEditingController();

  String _selectedIdType = 'national_id';

  @override
  void initState() {
    super.initState();
    _loadKycStatus();
  }

  Future<void> _loadKycStatus() async {
    try {
      final api = Provider.of<ApiService>(context, listen: false);
      final data = await api.getKycStatus();
      setState(() {
        _kycStatus = data['kyc_status'] ?? 'not_submitted';
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _kycStatus = 'not_submitted';
        _isLoading = false;
      });
    }
  }

  Future<void> _submitKyc() async {
    final fullName = _fullNameController.text.trim();
    final idNumber = _idNumberController.text.trim();
    final address = _addressController.text.trim();
    final region = _regionController.text.trim();
    final district = _districtController.text.trim();

    if (fullName.isEmpty || idNumber.isEmpty || address.isEmpty ||
        region.isEmpty || district.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Tafadhali jaza sehemu zote')),
      );
      return;
    }

    setState(() => _isSubmitting = true);

    try {
      final api = Provider.of<ApiService>(context, listen: false);
      await api.submitKyc({
        'id_type': _selectedIdType,
        'id_number': idNumber,
        'full_name': fullName,
        'address': address,
        'region': region,
        'district': district,
      });

      setState(() {
        _kycStatus = 'pending';
        _isSubmitting = false;
      });

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('KYC yametumwa kikamilifu. Inasubiri ukaguzi.')),
        );
      }
    } catch (e) {
      setState(() => _isSubmitting = false);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Kosa: $e')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {

    if (_isLoading) {
      return const Scaffold(
        body: Center(child: CircularProgressIndicator()),
      );
    }

    return Scaffold(
      appBar: AppBar(
        title: const Text('KYC Verification'),
        backgroundColor: const Color(0xFF2E7D32),
        foregroundColor: Colors.white,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Icon(
                          _kycStatus == 'verified'
                              ? Icons.verified
                              : _kycStatus == 'pending'
                                  ? Icons.pending
                                  : Icons.warning,
                          color: _kycStatus == 'verified'
                              ? Colors.green
                              : _kycStatus == 'pending'
                                  ? Colors.orange
                                  : Colors.red,
                          size: 40,
                        ),
                        const SizedBox(width: 16),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'Status: ${_kycStatus?.toUpperCase() ?? 'NOT SUBMITTED'}',
                                style: const TextStyle(
                                  fontSize: 18,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                              Text(
                                _kycStatus == 'verified'
                                    ? 'Akaunti yako imethibitishwa'
                                    : _kycStatus == 'pending'
                                        ? 'Maombi yako yanasubiri ukaguzi'
                                        : 'Tafadhali thibitisha utambulisho wako',
                                style: TextStyle(color: Colors.grey[600]),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 24),
            if (_kycStatus != 'verified' && _kycStatus != 'pending') ...[
              const Text(
                'Taarifa za KYC:',
                style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 16),
              DropdownButtonFormField<String>(
                value: _selectedIdType,
                decoration: const InputDecoration(
                  labelText: 'Aina ya Kitambulisho',
                  border: OutlineInputBorder(),
                ),
                items: const [
                  DropdownMenuItem(value: 'national_id', child: Text('NIDA')),
                  DropdownMenuItem(value: 'drivers_license', child: Text('Leseni ya Udereva')),
                  DropdownMenuItem(value: 'passport', child: Text('Passport')),
                  DropdownMenuItem(value: 'voter_id', child: Text('Kitambulisho cha Mpiga Kura')),
                ],
                onChanged: (v) => setState(() => _selectedIdType = v!),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: _idNumberController,
                decoration: const InputDecoration(
                  labelText: 'Namba ya Kitambulisho',
                  hintText: 'Mfano: 1234567890123',
                  border: OutlineInputBorder(),
                ),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: _fullNameController,
                decoration: const InputDecoration(
                  labelText: 'Jina Kamili',
                  hintText: 'Mfano: Juma Hamisi',
                  border: OutlineInputBorder(),
                ),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: _addressController,
                maxLines: 2,
                decoration: const InputDecoration(
                  labelText: 'Anwani',
                  hintText: 'Mfano: Kariakoo, Dar es Salaam',
                  border: OutlineInputBorder(),
                ),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: _regionController,
                decoration: const InputDecoration(
                  labelText: 'Mkoa',
                  hintText: 'Mfano: Dar es Salaam',
                  border: OutlineInputBorder(),
                ),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: _districtController,
                decoration: const InputDecoration(
                  labelText: 'Wilaya',
                  hintText: 'Mfano: Ilala',
                  border: OutlineInputBorder(),
                ),
              ),
              const SizedBox(height: 24),
              SizedBox(
                width: double.infinity,
                height: 50,
                child: ElevatedButton(
                  onPressed: _isSubmitting ? null : _submitKyc,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF2E7D32),
                    foregroundColor: Colors.white,
                  ),
                  child: _isSubmitting
                      ? const SizedBox(
                          width: 20,
                          height: 20,
                          child: CircularProgressIndicator(
                            color: Colors.white,
                            strokeWidth: 2,
                          ),
                        )
                      : const Text('Wasilisha Maombi'),
                ),
              ),
            ] else if (_kycStatus == 'pending') ...[
              const Center(
                child: Column(
                  children: [
                    SizedBox(height: 32),
                    Icon(Icons.hourglass_top, size: 64, color: Colors.orange),
                    SizedBox(height: 16),
                    Text(
                      'Maombi yako yanasubiri ukaguzi',
                      style: TextStyle(fontSize: 16),
                    ),
                    SizedBox(height: 8),
                    Text(
                      'Utapata arifa baada ya kukamilika',
                      style: TextStyle(color: Colors.grey),
                    ),
                  ],
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
