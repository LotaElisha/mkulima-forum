import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../providers/connectivity_provider.dart';

/// Slim banner shown when the device is offline. Wrap screen bodies:
///   Column(children: [const MkOfflineBanner(), Expanded(child: ...)])
class MkOfflineBanner extends StatelessWidget {
  const MkOfflineBanner({super.key});

  @override
  Widget build(BuildContext context) {
    final connectivity = context.watch<ConnectivityProvider>();
    if (connectivity.isOnline) return const SizedBox.shrink();

    return Material(
      color: Colors.orange.shade800,
      child: const Padding(
        padding: EdgeInsets.symmetric(vertical: 6, horizontal: 12),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.wifi_off, size: 16, color: Colors.white),
            SizedBox(width: 8),
            Text(
              'Hakuna mtandao — unaona taarifa zilizohifadhiwa',
              style: TextStyle(color: Colors.white, fontSize: 12),
            ),
          ],
        ),
      ),
    );
  }
}
