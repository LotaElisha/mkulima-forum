import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/theme.dart';
import '../../providers/connectivity_provider.dart';
import '../../services/api_service.dart';
import '../../trace/trace_sync.dart';
import '../../widgets/mk_offline_banner.dart';
import 'trace_widgets.dart';

/// Every Mkulima Trace write made on this phone and whether it reached the
/// server: waiting, sent, or refused (with the reason).
class TraceSyncScreen extends StatefulWidget {
  const TraceSyncScreen({super.key});

  @override
  State<TraceSyncScreen> createState() => _TraceSyncScreenState();
}

class _TraceSyncScreenState extends State<TraceSyncScreen> {
  final _store = TraceSync.instance;

  @override
  void initState() {
    super.initState();
    _store.addListener(_changed);
    _store.load();
  }

  @override
  void dispose() {
    _store.removeListener(_changed);
    super.dispose();
  }

  void _changed() {
    if (mounted) setState(() {});
  }

  Future<void> _sync({bool includeFailed = false}) => _store.syncNow(
    posterFor(context.read<ApiService>()),
    includeFailed: includeFailed,
  );

  @override
  Widget build(BuildContext context) {
    final online = context.watch<ConnectivityProvider>().isOnline;
    final ops = _store.ops.reversed.toList();
    return Scaffold(
      appBar: AppBar(title: const Text('Rekodi kwenye simu')),
      body: Column(
        children: [
          const MkOfflineBanner(),
          Expanded(
            child: ListView(
              padding: const EdgeInsets.all(16),
              children: [
                Text(
                  _store.pendingCount == 0 && _store.failedCount == 0
                      ? 'Rekodi zote zimetumwa.'
                      : 'Zinasubiri: ${_store.pendingCount} · Zimeshindwa: ${_store.failedCount}',
                  style: const TextStyle(
                    fontSize: 17,
                    fontWeight: FontWeight.w700,
                  ),
                ),
                if (_store.lastSyncedAt != null)
                  Text(
                    'Mara ya mwisho kutuma: ${traceDate(_store.lastSyncedAt!.toIso8601String())}',
                    style: const TextStyle(color: MkColors.muted),
                  ),
                const SizedBox(height: 12),
                FilledButton.icon(
                  onPressed:
                      !online || _store.syncing || _store.pendingCount == 0
                      ? null
                      : _sync,
                  icon: _store.syncing
                      ? const SizedBox(
                          width: 18,
                          height: 18,
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            color: Colors.white,
                          ),
                        )
                      : const Icon(Icons.cloud_upload_outlined),
                  label: Text(online ? 'Tuma sasa' : 'Hakuna mtandao'),
                  style: FilledButton.styleFrom(
                    minimumSize: const Size.fromHeight(48),
                  ),
                ),
                const SizedBox(height: 16),
                for (final op in ops)
                  Card(
                    margin: const EdgeInsets.only(bottom: 10),
                    child: Padding(
                      padding: const EdgeInsets.all(12),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            op.label,
                            style: const TextStyle(fontWeight: FontWeight.w700),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            traceDate(op.createdAt.toIso8601String()),
                            style: const TextStyle(
                              color: MkColors.muted,
                              fontSize: 13,
                            ),
                          ),
                          const SizedBox(height: 6),
                          TraceOpChip(op),
                          if (op.isFailed && op.error != null) ...[
                            const SizedBox(height: 6),
                            Text(
                              op.error!,
                              style: const TextStyle(color: MkColors.danger),
                            ),
                            const SizedBox(height: 6),
                            Wrap(
                              spacing: 8,
                              children: [
                                TextButton(
                                  onPressed: online
                                      ? () => _sync(includeFailed: true)
                                      : null,
                                  child: const Text('Jaribu tena'),
                                ),
                                TextButton(
                                  onPressed: () => _store.discard(op.id),
                                  child: const Text('Futa rekodi hii'),
                                ),
                              ],
                            ),
                          ],
                        ],
                      ),
                    ),
                  ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
