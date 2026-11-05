import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:qr_flutter/qr_flutter.dart';

import '../../core/theme.dart';
import '../../providers/connectivity_provider.dart';
import '../../services/api_service.dart';
import '../../trace/trace_ids.dart';
import '../../trace/trace_strings.dart';
import '../../trace/trace_sync.dart';

/// Send a write now, or keep it on the phone until there is a connection.
/// Shows the farmer which of the two happened. Returns null when the server
/// refused it (the message is shown) so the form can stay open.
Future<TraceSubmit?> traceSubmit(
  BuildContext context,
  TraceOp op, {
  String? queuedMessage,
}) async {
  final api = context.read<ApiService>();
  final online = context.read<ConnectivityProvider>().isOnline;
  final messenger = ScaffoldMessenger.of(context);
  try {
    final result = await TraceSync.instance.submit(
      posterFor(api),
      op,
      online: online,
    );
    messenger.showSnackBar(
      SnackBar(
        content: Text(
          result.synced
              ? '${op.label}: ${TraceStrings.syncSynced.toLowerCase()}.'
              : (queuedMessage ?? TraceStrings.savedOffline),
        ),
      ),
    );
    return result;
  } catch (e) {
    messenger.showSnackBar(SnackBar(content: Text(traceError(e).message)));
    return null;
  }
}

String traceKg(Object? kg) {
  final v = kg is num ? kg : num.tryParse('$kg');
  if (v == null) return '—';
  final text = v == v.roundToDouble()
      ? v.toStringAsFixed(0)
      : v.toStringAsFixed(1);
  return '$text kg';
}

String traceDate(Object? iso) {
  final d = DateTime.tryParse('${iso ?? ''}')?.toLocal();
  if (d == null) return '—';
  String two(int n) => n.toString().padLeft(2, '0');
  return '${two(d.day)}/${two(d.month)}/${d.year} ${two(d.hour)}:${two(d.minute)}';
}

/// A small coloured label.
class TraceChip extends StatelessWidget {
  final String label;
  final Color color;
  final Color background;
  final IconData? icon;

  const TraceChip(
    this.label, {
    super.key,
    this.color = MkColors.primaryDark,
    this.background = MkColors.leafPale,
    this.icon,
  });

  const TraceChip.warning(this.label, {super.key, this.icon})
    : color = MkColors.onAccentSoft,
      background = MkColors.accentSoft;

  const TraceChip.danger(this.label, {super.key, this.icon})
    : color = MkColors.danger,
      background = MkColors.dangerSoft;

  const TraceChip.info(this.label, {super.key, this.icon})
    : color = MkColors.info,
      background = MkColors.infoSoft;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: background,
        borderRadius: BorderRadius.circular(999),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          if (icon != null) ...[
            Icon(icon, size: 16, color: color),
            const SizedBox(width: 4),
          ],
          Flexible(
            child: Text(
              label,
              style: TextStyle(
                color: color,
                fontSize: 13,
                fontWeight: FontWeight.w600,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

/// Sync state of a write that is still on the phone.
class TraceOpChip extends StatelessWidget {
  final TraceOp op;
  const TraceOpChip(this.op, {super.key});

  @override
  Widget build(BuildContext context) {
    if (op.isFailed) {
      return const TraceChip.danger(
        TraceStrings.syncFailed,
        icon: Icons.error_outline,
      );
    }
    if (op.isPending) {
      return const TraceChip.warning(
        TraceStrings.syncPending,
        icon: Icons.cloud_upload_outlined,
      );
    }
    return const TraceChip(
      TraceStrings.syncSynced,
      icon: Icons.cloud_done_outlined,
    );
  }
}

/// The QR code for a lot, plot or participant. Holds only the code link.
class TraceQr extends StatelessWidget {
  final String code;
  final double size;
  const TraceQr(this.code, {super.key, this.size = 180});

  @override
  Widget build(BuildContext context) {
    return Semantics(
      label: 'Msimbo wa QR $code',
      child: Container(
        color: Colors.white,
        padding: const EdgeInsets.all(8),
        child: QrImageView(
          data: TraceIds.link(code),
          size: size,
          backgroundColor: Colors.white,
          errorCorrectionLevel: QrErrorCorrectLevel.M,
        ),
      ),
    );
  }
}

/// A row of label and value.
class TraceField extends StatelessWidget {
  final String label;
  final String? value;
  const TraceField(this.label, this.value, {super.key});

  @override
  Widget build(BuildContext context) {
    if (value == null || value!.isEmpty) return const SizedBox.shrink();
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Expanded(
            flex: 2,
            child: Text(
              label,
              style: const TextStyle(color: MkColors.muted, fontSize: 15),
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            flex: 3,
            child: Text(
              value!,
              style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w600),
            ),
          ),
        ],
      ),
    );
  }
}

/// Amount and unit, side by side. Units match the server's list.
class TraceQuantityInput extends StatelessWidget {
  final TextEditingController controller;
  final String unit;
  final ValueChanged<String> onUnit;
  final String label;
  final bool required;

  const TraceQuantityInput({
    super.key,
    required this.controller,
    required this.unit,
    required this.onUnit,
    this.label = 'Kiasi',
    this.required = true,
  });

  @override
  Widget build(BuildContext context) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Expanded(
          child: TextFormField(
            controller: controller,
            keyboardType: const TextInputType.numberWithOptions(decimal: true),
            decoration: InputDecoration(labelText: label),
            validator: (v) {
              if ((v ?? '').trim().isEmpty) {
                return required ? 'Andika kiasi' : null;
              }
              final n = double.tryParse(v!.trim().replaceAll(',', '.'));
              if (n == null || n <= 0) return 'Andika namba sahihi';
              return null;
            },
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: DropdownButtonFormField<String>(
            initialValue: unit,
            isExpanded: true,
            decoration: const InputDecoration(labelText: 'Kipimo'),
            items: [
              for (final e in TraceStrings.units.entries)
                DropdownMenuItem(
                  value: e.key,
                  child: Text(e.value, overflow: TextOverflow.ellipsis),
                ),
            ],
            onChanged: (v) => onUnit(v ?? unit),
          ),
        ),
      ],
    );
  }
}

double? traceNumber(TextEditingController c) {
  final t = c.text.trim().replaceAll(',', '.');
  return t.isEmpty ? null : double.tryParse(t);
}

String? traceText(TextEditingController c) {
  final t = c.text.trim();
  return t.isEmpty ? null : t;
}

/// The reminder that a trace record is not a certificate.
class TraceNotice extends StatelessWidget {
  const TraceNotice({super.key});

  @override
  Widget build(BuildContext context) {
    return const Padding(
      padding: EdgeInsets.symmetric(vertical: 12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            TraceStrings.notice,
            style: TextStyle(color: MkColors.muted, fontSize: 13, height: 1.45),
          ),
          SizedBox(height: 4),
          Text(
            TraceStrings.noticeEn,
            style: TextStyle(color: MkColors.muted, fontSize: 13, height: 1.45),
          ),
        ],
      ),
    );
  }
}

/// Scaffold body wrapper for forms: scrolls, pads, and has one primary button.
class TraceFormBody extends StatelessWidget {
  final GlobalKey<FormState> formKey;
  final List<Widget> children;
  final String submitLabel;
  final bool busy;
  final VoidCallback onSubmit;

  const TraceFormBody({
    super.key,
    required this.formKey,
    required this.children,
    required this.submitLabel,
    required this.busy,
    required this.onSubmit,
  });

  @override
  Widget build(BuildContext context) {
    return Form(
      key: formKey,
      child: ListView(
        padding: EdgeInsets.fromLTRB(
          16,
          16,
          16,
          MediaQuery.paddingOf(context).bottom + 24,
        ),
        children: [
          ...children,
          const SizedBox(height: 24),
          FilledButton(
            onPressed: busy ? null : onSubmit,
            style: FilledButton.styleFrom(
              minimumSize: const Size.fromHeight(48),
            ),
            child: busy
                ? const SizedBox(
                    width: 20,
                    height: 20,
                    child: CircularProgressIndicator(
                      strokeWidth: 2,
                      color: Colors.white,
                    ),
                  )
                : Text(submitLabel),
          ),
        ],
      ),
    );
  }
}
