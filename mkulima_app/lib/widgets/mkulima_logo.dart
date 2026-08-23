import 'package:flutter/material.dart';

/// The Mkulima Forum mark, in one place.
///
/// Every screen that showed the logo used to reference an image path directly,
/// and the path they all referenced — `assets/images/app_icon.jpg` — held
/// another company's logo entirely (SokoMoto's). One widget means the next
/// brand change is one edit, and there is no longer a loose string for the
/// wrong file to hide behind.
class MkulimaLogo extends StatelessWidget {
  final double size;

  /// Draws a white rounded card behind the mark. Use on a coloured or
  /// photographic background, where the emblem's own transparency would
  /// otherwise let the backdrop show through the ring.
  final bool onDarkBackground;

  const MkulimaLogo({super.key, this.size = 76, this.onDarkBackground = false});

  static const String assetPath = 'assets/images/mkulima_logo.png';

  @override
  Widget build(BuildContext context) {
    final image = Image.asset(
      assetPath,
      width: size,
      height: size,
      fit: BoxFit.contain,
      // An asset that fails to decode must not take the screen down with it.
      // A missing logo is cosmetic; a red error box on the login screen is not.
      errorBuilder: (context, error, stack) => Icon(
        Icons.eco_outlined,
        size: size * 0.6,
        color: const Color(0xFF1B7A3E),
      ),
    );

    if (!onDarkBackground) return image;

    return Container(
      width: size,
      height: size,
      padding: EdgeInsets.all(size * 0.10),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(size * 0.26),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.18),
            blurRadius: size * 0.22,
            offset: Offset(0, size * 0.10),
          ),
        ],
      ),
      child: image,
    );
  }
}
