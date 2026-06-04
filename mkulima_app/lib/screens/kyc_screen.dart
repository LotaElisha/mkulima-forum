import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';

class KycScreen extends StatelessWidget {
  const KycScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context);
    final user = auth.user;

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
                          user?.kycStatus == 'verified'
                              ? Icons.verified
                              : Icons.pending,
                          color: user?.kycStatus == 'verified'
                              ? Colors.green
                              : Colors.orange,
                          size: 40,
                        ),
                        const SizedBox(width: 16),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'Status: ${user?.kycStatus.toUpperCase()}',
                                style: const TextStyle(
                                  fontSize: 18,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                              Text(
                                user?.kycStatus == 'verified'
                                    ? 'Akaunti yako imethibitishwa'
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
            if (user?.kycStatus != 'verified') ...[
              const Text(
                'Nyaraka Zinazohitajika:',
                style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 16),
              _buildDocumentCard(
                icon: Icons.badge,
                title: 'Kitambulisho cha Taifa',
                subtitle: 'NIDA, Passport, au Leseni ya Udereva',
                onTap: () {},
              ),
              _buildDocumentCard(
                icon: Icons.home,
                title: 'Uthibitisho wa Anwani',
                subtitle: 'Bili ya umeme, maji, au barua ya mtaa',
                onTap: () {},
              ),
              _buildDocumentCard(
                icon: Icons.photo_camera,
                title: 'Picha ya Selfie',
                subtitle: 'Picha ya uso wako kwa uwazi',
                onTap: () {},
              ),
              const SizedBox(height: 24),
              SizedBox(
                width: double.infinity,
                height: 50,
                child: ElevatedButton(
                  onPressed: () {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        content: Text('Maombi yametumwa kwa ukaguzi'),
                      ),
                    );
                  },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF2E7D32),
                    foregroundColor: Colors.white,
                  ),
                  child: const Text('Wasilisha Maombi'),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildDocumentCard({
    required IconData icon,
    required String title,
    required String subtitle,
    required VoidCallback onTap,
  }) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: ListTile(
        leading: Icon(icon, color: const Color(0xFF2E7D32)),
        title: Text(title),
        subtitle: Text(subtitle),
        trailing: ElevatedButton(
          onPressed: onTap,
          style: ElevatedButton.styleFrom(
            backgroundColor: const Color(0xFF2E7D32),
            foregroundColor: Colors.white,
          ),
          child: const Text('Pakia'),
        ),
      ),
    );
  }
}
