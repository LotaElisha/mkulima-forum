import 'package:flutter/material.dart';
import '../core/theme.dart';
import '../data/disease_library.dart';

/// Maktaba ya magonjwa — readable with no connection.
class DiseaseLibraryScreen extends StatelessWidget {
  const DiseaseLibraryScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final crops = {for (final d in diseaseLibrary) d.crop}.toList();
    return Scaffold(
      appBar: AppBar(title: const Text('Maktaba ya magonjwa')),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(16, 8, 16, 24),
        children: [
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: MkColors.surfaceMuted,
              borderRadius: BorderRadius.circular(MkRadii.card),
            ),
            child: const Text(
              'Inafanya kazi bila mtandao. Linganisha dalili unazoona na hizi. '
              'Huu si utambuzi wa picha: thibitisha na mtaalamu au afisa ugani '
              'kabla ya kununua dawa, na tumia dawa zilizosajiliwa na TPHPA.',
              style: MkText.bodyMuted,
            ),
          ),
          for (final crop in crops) ...[
            Padding(
              padding: const EdgeInsets.only(top: 16, bottom: 4),
              child: Text(crop, style: MkText.section),
            ),
            for (final d in diseaseLibrary.where((d) => d.crop == crop))
              Card(
                margin: const EdgeInsets.only(bottom: 8),
                child: ExpansionTile(
                  shape: const Border(),
                  title: Text(d.name, style: MkText.title),
                  subtitle: Text(d.part, style: MkText.caption),
                  childrenPadding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
                  expandedCrossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _section('Dalili', d.symptoms),
                    _section('Tiba', d.treatment),
                    _section('Kinga', d.prevention),
                    _section('Ushauri', d.advice),
                  ],
                ),
              ),
          ],
        ],
      ),
    );
  }

  Widget _section(String title, String body) => Padding(
        padding: const EdgeInsets.only(top: 10),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(title, style: MkText.label),
            const SizedBox(height: 2),
            Text(body, style: MkText.body),
          ],
        ),
      );
}
