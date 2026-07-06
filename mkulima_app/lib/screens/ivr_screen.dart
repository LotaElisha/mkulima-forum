import 'package:flutter/material.dart';

class IvrScreen extends StatelessWidget {
  const IvrScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('IVR - Simu ya Kupiga'),
        backgroundColor: const Color(0xFF2E7D32),
        foregroundColor: Colors.white,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Phone number card
            Container(
              padding: const EdgeInsets.all(24),
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [Color(0xFF2E7D32), Color(0xFF1B5E20)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.circular(20),
              ),
              child: Column(
                children: [
                  const Icon(
                    Icons.phone_in_talk,
                    size: 64,
                    color: Colors.white,
                  ),
                  const SizedBox(height: 16),
                  const Text(
                    'Piga Simu Bila Mtandao',
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 20,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 8),
                  const Text(
                    'Piga +255 714 524 007',
                    style: TextStyle(
                      color: Colors.white70,
                      fontSize: 16,
                    ),
                  ),
                  const SizedBox(height: 16),
                  ElevatedButton.icon(
                    onPressed: () {},
                    icon: const Icon(Icons.call),
                    label: const Text('Piga Sasa'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.white,
                      foregroundColor: const Color(0xFF2E7D32),
                      padding: const EdgeInsets.symmetric(
                        horizontal: 32,
                        vertical: 12,
                      ),
                    ),
                  ),
                ],
              ),
            ),

            const SizedBox(height: 24),
            const Text(
              'Chaguo za Menyu',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 16),

            _MenuOption(
              number: '1',
              title: 'Bei za Soko',
              description: 'Pata bei za bidhaa za kilimo kutoka sokoni',
              icon: Icons.price_check,
            ),
            _MenuOption(
              number: '2',
              title: 'Hali ya Hewa',
              description: 'Jua hali ya hewa ya siku hii na kesho',
              icon: Icons.wb_cloudy,
            ),
            _MenuOption(
              number: '3',
              title: 'Ongea na Mtaalamu',
              description: 'Unganishwa na mtaalamu wa kilimo kupitia simu',
              icon: Icons.support_agent,
            ),
            _MenuOption(
              number: '0',
              title: 'Acha',
              description: 'Maliza simu',
              icon: Icons.call_end,
            ),

            const SizedBox(height: 24),
            const Card(
              color: Color(0xFFFFF3E0),
              child: Padding(
                padding: EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Icon(Icons.info, color: Colors.orange),
                        SizedBox(width: 8),
                        Text(
                          'Vidokezo',
                          style: TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: 16,
                          ),
                        ),
                      ],
                    ),
                    SizedBox(height: 8),
                    Text(
                      '• Huduma hii ni bila malipo\n'
                      '• Inafanya kazi kwa simu yoyote\n'
                      '• Hakuna mtandao wa intaneti unaohitajika\n'
                      '• Mtaalamu anapatikana saa 2 asubuhi hadi 6 jioni',
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _MenuOption extends StatelessWidget {
  final String number;
  final String title;
  final String description;
  final IconData icon;

  const _MenuOption({
    required this.number,
    required this.title,
    required this.description,
    required this.icon,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: ListTile(
        leading: CircleAvatar(
          backgroundColor: const Color(0xFF2E7D32),
          child: Text(
            number,
            style: const TextStyle(
              color: Colors.white,
              fontWeight: FontWeight.bold,
            ),
          ),
        ),
        title: Text(title),
        subtitle: Text(description),
        trailing: Icon(icon, color: const Color(0xFF2E7D32)),
      ),
    );
  }
}
