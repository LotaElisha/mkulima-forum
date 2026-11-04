import 'dart:math' as math;
import 'package:flutter/material.dart';

/// Illustrations painted in code rather than shipped as photos.
///
/// Farmers are on metered data and cheap phones: a painted mountain and leaf
/// cost nothing to download and stay sharp at any density. Swap in a bundled
/// photo later by replacing the painter with an Image.asset in the same slot.

/// Hills, a snow-capped mountain and field rows, for the Home hero.
class MkLandscape extends StatelessWidget {
  const MkLandscape({super.key});

  @override
  Widget build(BuildContext context) =>
      const CustomPaint(painter: _LandscapePainter(), size: Size.infinite);
}

class _LandscapePainter extends CustomPainter {
  const _LandscapePainter();

  @override
  void paint(Canvas canvas, Size size) {
    final w = size.width;
    final h = size.height;

    // Sky.
    canvas.drawRect(
      Offset.zero & size,
      Paint()
        ..shader = const LinearGradient(
          begin: Alignment.topCenter,
          end: Alignment.bottomCenter,
          colors: [Color(0xFFDDEFF7), Color(0xFFF4F9F1)],
        ).createShader(Offset.zero & size),
    );

    // Mountain with a snow cap, right of centre.
    final peak = Offset(w * 0.70, h * 0.12);
    final mountain = Path()
      ..moveTo(w * 0.36, h * 0.62)
      ..quadraticBezierTo(w * 0.55, h * 0.30, peak.dx, peak.dy)
      ..quadraticBezierTo(w * 0.86, h * 0.30, w * 1.05, h * 0.60)
      ..lineTo(w * 1.05, h * 0.70)
      ..lineTo(w * 0.36, h * 0.70)
      ..close();
    canvas.drawPath(mountain, Paint()..color = const Color(0xFF7F95A3));

    final snow = Path()
      ..moveTo(w * 0.62, h * 0.24)
      ..quadraticBezierTo(w * 0.66, h * 0.16, peak.dx, peak.dy)
      ..quadraticBezierTo(w * 0.75, h * 0.16, w * 0.79, h * 0.24)
      ..lineTo(w * 0.75, h * 0.22)
      ..lineTo(w * 0.72, h * 0.26)
      ..lineTo(w * 0.68, h * 0.22)
      ..lineTo(w * 0.65, h * 0.26)
      ..close();
    canvas.drawPath(snow, Paint()..color = Colors.white);

    // Rolling hills.
    void hill(double y, Color color, double phase) {
      final p = Path()..moveTo(0, h * y);
      for (var x = 0.0; x <= w; x += 8) {
        p.lineTo(x, h * y + math.sin(x / w * math.pi * 2 + phase) * h * 0.04);
      }
      p
        ..lineTo(w, h)
        ..lineTo(0, h)
        ..close();
      canvas.drawPath(p, Paint()..color = color);
    }

    hill(0.62, const Color(0xFF6E9F4E), 0.4);
    hill(0.72, const Color(0xFF4E8A3A), 1.6);

    // Field rows converging toward the horizon.
    final field = Path()
      ..moveTo(0, h * 0.80)
      ..lineTo(w, h * 0.78)
      ..lineTo(w, h)
      ..lineTo(0, h)
      ..close();
    canvas.drawPath(field, Paint()..color = const Color(0xFF2F7A32));
    final rows = Paint()
      ..color = const Color(0xFF4F9A3E)
      ..strokeWidth = 3;
    for (var i = -6; i <= 14; i++) {
      final x = w * 0.5 + i * w * 0.09;
      canvas.drawLine(
        Offset(w * 0.5 + i * w * 0.02, h * 0.79),
        Offset(x, h),
        rows,
      );
    }

    // A few acacia-like trees on the hill line.
    final trunk = Paint()..color = const Color(0xFF3B2F22);
    final crown = Paint()..color = const Color(0xFF2E5E27);
    for (final (fx, fy, s) in const [
      (0.82, 0.66, 1.0),
      (0.93, 0.70, 0.8),
      (0.58, 0.69, 0.7),
    ]) {
      final base = Offset(w * fx, h * fy);
      canvas.drawLine(
        base,
        base.translate(0, -h * 0.06 * s),
        trunk..strokeWidth = 2,
      );
      canvas.drawOval(
        Rect.fromCenter(
          center: base.translate(0, -h * 0.075 * s),
          width: w * 0.07 * s,
          height: h * 0.035 * s,
        ),
        crown,
      );
    }
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}

/// A leaf with disease spots on a green background, for scanner tiles.
class MkSpottedLeaf extends StatelessWidget {
  const MkSpottedLeaf({super.key});

  @override
  Widget build(BuildContext context) =>
      const CustomPaint(painter: _LeafPainter(), size: Size.infinite);
}

class _LeafPainter extends CustomPainter {
  const _LeafPainter();

  @override
  void paint(Canvas canvas, Size size) {
    final w = size.width;
    final h = size.height;

    canvas.drawRect(
      Offset.zero & size,
      Paint()
        ..shader = const RadialGradient(
          center: Alignment(0.2, -0.2),
          radius: 1.0,
          colors: [Color(0xFF5E9B45), Color(0xFF1F4D22)],
        ).createShader(Offset.zero & size),
    );

    // Background foliage.
    final back = Paint()..color = const Color(0xFF2F6B2C);
    for (final (fx, fy, r) in const [
      (0.1, 0.15, 0.22),
      (0.9, 0.85, 0.25),
      (0.05, 0.9, 0.2),
    ]) {
      canvas.drawOval(
        Rect.fromCenter(
          center: Offset(w * fx, h * fy),
          width: w * r * 2,
          height: h * r,
        ),
        back,
      );
    }

    canvas.save();
    canvas.translate(w * 0.5, h * 0.52);
    canvas.rotate(-0.5);
    final lw = math.min(w, h) * 0.62;
    final lh = math.min(w, h) * 0.95;
    final leaf = Path()
      ..moveTo(0, -lh / 2)
      ..cubicTo(lw * 0.62, -lh * 0.28, lw * 0.55, lh * 0.30, 0, lh / 2)
      ..cubicTo(-lw * 0.55, lh * 0.30, -lw * 0.62, -lh * 0.28, 0, -lh / 2)
      ..close();
    canvas.drawPath(
      leaf,
      Paint()
        ..shader =
            const LinearGradient(
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
              colors: [Color(0xFF8CC152), Color(0xFF4C8C2B)],
            ).createShader(
              Rect.fromCenter(center: Offset.zero, width: lw, height: lh),
            ),
    );

    final vein = Paint()
      ..color = const Color(0xFFCFE6A8)
      ..strokeWidth = 1.6
      ..style = PaintingStyle.stroke;
    canvas.drawLine(Offset(0, -lh / 2 + 4), Offset(0, lh / 2), vein);
    for (var i = 1; i <= 4; i++) {
      final y = -lh / 2 + lh * i / 5;
      canvas.drawLine(Offset(0, y), Offset(lw * 0.32, y - lh * 0.08), vein);
      canvas.drawLine(Offset(0, y), Offset(-lw * 0.32, y - lh * 0.08), vein);
    }

    // Disease spots: yellow halo, brown centre.
    for (final (fx, fy, r) in const [
      (0.12, -0.18, 0.07),
      (-0.14, 0.02, 0.09),
      (0.16, 0.16, 0.06),
      (-0.06, 0.28, 0.05),
      (0.05, -0.02, 0.04),
    ]) {
      final c = Offset(lw * fx, lh * fy);
      canvas.drawCircle(
        c,
        lw * r * 1.7,
        Paint()..color = const Color(0xCCE3C93B),
      );
      canvas.drawCircle(c, lw * r, Paint()..color = const Color(0xFF5B3A1A));
    }
    canvas.restore();
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}

/// Four viewfinder corners, drawn over a photo or illustration.
class MkScanCorners extends StatelessWidget {
  final double inset;
  final double length;
  final double stroke;
  const MkScanCorners({
    super.key,
    this.inset = 12,
    this.length = 26,
    this.stroke = 4,
  });

  @override
  Widget build(BuildContext context) => CustomPaint(
    painter: _CornersPainter(inset, length, stroke),
    size: Size.infinite,
  );
}

class _CornersPainter extends CustomPainter {
  final double inset;
  final double length;
  final double stroke;
  const _CornersPainter(this.inset, this.length, this.stroke);

  @override
  void paint(Canvas canvas, Size size) {
    final p = Paint()
      ..color = Colors.white
      ..strokeWidth = stroke
      ..strokeCap = StrokeCap.round
      ..style = PaintingStyle.stroke;
    final l = inset, t = inset, r = size.width - inset, b = size.height - inset;
    final len = length;
    for (final path in [
      Path()
        ..moveTo(l, t + len)
        ..lineTo(l, t)
        ..lineTo(l + len, t),
      Path()
        ..moveTo(r - len, t)
        ..lineTo(r, t)
        ..lineTo(r, t + len),
      Path()
        ..moveTo(l, b - len)
        ..lineTo(l, b)
        ..lineTo(l + len, b),
      Path()
        ..moveTo(r - len, b)
        ..lineTo(r, b)
        ..lineTo(r, b - len),
    ]) {
      canvas.drawPath(path, p);
    }
  }

  @override
  bool shouldRepaint(covariant _CornersPainter old) =>
      old.inset != inset || old.length != length || old.stroke != stroke;
}
