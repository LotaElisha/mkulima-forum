import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../core/strings.dart';
import '../core/theme.dart';
import '../providers/connectivity_provider.dart';

/// Slim banner shown when the device is offline. Wrap screen bodies:
///   Column(children: [const MkOfflineBanner(), Expanded(child: ...)])
class MkOfflineBanner extends StatelessWidget {
  const MkOfflineBanner({super.key});

  @override
  Widget build(BuildContext context) {
    final connectivity = context.watch<ConnectivityProvider>();
    if (connectivity.isOnline) return const SizedBox.shrink();

    // Pale amber with dark text: noticeable without shouting, and readable.
    // The old solid orange bar with 12px white text failed contrast.
    return Semantics(
      liveRegion: true,
      child: Material(
        color: MkColors.accentSoft,
        child: const Padding(
          padding: EdgeInsets.symmetric(vertical: 8, horizontal: 16),
          child: Row(
            children: [
              Icon(Icons.wifi_off, size: 20, color: MkColors.onAccentSoft),
              SizedBox(width: 10),
              Expanded(
                child: Text(
                  MkStrings.offline,
                  style: TextStyle(
                    color: MkColors.onAccentSoft,
                    fontSize: 13,
                    height: 1.35,
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
