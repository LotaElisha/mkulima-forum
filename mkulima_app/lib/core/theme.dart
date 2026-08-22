import 'package:flutter/material.dart';

/// MkulimaForum's palette: a white canvas carrying agricultural green.
///
/// The previous palette was a cream editorial scheme — a #FAF7EF ground with
/// charcoal as the primary action colour and amber as the accent — which put
/// green in third place on a farming product and gave every screen a warm
/// tint. Under the current direction white is the page and green is what you
/// can act on, so a farmer scanning a screen always knows the green control is
/// the one to press. Amber survives only as a warning and highlight colour.
///
/// Names are unchanged so every existing reference keeps compiling; only the
/// values moved. `charcoal` is now the deep green it acts as in practice,
/// because it was used for primary buttons and the bottom bar rather than for
/// text.
class MkColors {
  MkColors._();

  // ── Brand green ───────────────────────────────────────────────────
  static const Color primary = Color(0xFF1B7A3E);
  static const Color primaryDark = Color(0xFF14532D);
  static const Color leafGreen = Color(0xFF1B7A3E);
  static const Color leafBright = Color(0xFF3FA463);
  static const Color leafPale = Color(0xFFEEF7F0);

  /// Retained for source compatibility, and it is genuinely ink: two thirds
  /// of its uses are Text colours. Background uses were changed to `primary`
  /// individually rather than by redefining this, which would have produced
  /// green labels on green buttons.
  static const Color charcoal = Color(0xFF0F1511);

  // ── Accent, now genuinely an accent ───────────────────────────────
  static const Color accent = Color(0xFFE0A008);
  static const Color accentSoft = Color(0xFFFDF1D6);

  // ── Ink ───────────────────────────────────────────────────────────
  static const Color ink = Color(0xFF0F1511);
  static const Color muted = Color(0xFF626D66);

  // ── Surfaces: white first ─────────────────────────────────────────
  static const Color surface = Color(0xFFFFFFFF);
  static const Color surfaceRaised = Color(0xFFFFFFFF);
  static const Color surfaceMuted = Color(0xFFF4F7F4);
  static const Color border = Color(0xFFE5EAE6);

  // ── Semantic ──────────────────────────────────────────────────────
  static const Color danger = Color(0xFFB3261E);
  static const Color success = Color(0xFF1B7A3E);
  static const Color warning = Color(0xFFB26A00);
}

class MkRadii {
  MkRadii._();
  static const double card = 18;
  static const double button = 14;
  static const double sheet = 24;
}

ThemeData mkLightTheme() {
  const scheme = ColorScheme.light(
    primary: MkColors.primary,
    onPrimary: Colors.white,
    secondary: MkColors.leafBright,
    onSecondary: Colors.white,
    // Amber demoted from secondary to tertiary: it now marks warnings and
    // highlights, not primary actions.
    tertiary: MkColors.accent,
    onTertiary: MkColors.ink,
    error: MkColors.danger,
    surface: MkColors.surfaceRaised,
    onSurface: MkColors.ink,
    outline: MkColors.border,
  );
  return ThemeData(
    colorScheme: scheme,
    useMaterial3: true,
    fontFamily: 'Roboto',
    scaffoldBackgroundColor: MkColors.surface,
    dividerColor: MkColors.border,
    appBarTheme: const AppBarTheme(
      elevation: 0,
      scrolledUnderElevation: 0,
      centerTitle: false,
      backgroundColor: MkColors.surface,
      foregroundColor: MkColors.ink,
      surfaceTintColor: Colors.transparent,
      titleTextStyle: TextStyle(
        color: MkColors.ink,
        fontSize: 20,
        fontWeight: FontWeight.w800,
        letterSpacing: -.3,
      ),
    ),
    cardTheme: CardThemeData(
      color: MkColors.surfaceRaised,
      surfaceTintColor: Colors.transparent,
      elevation: 0,
      margin: EdgeInsets.zero,
      shape: RoundedRectangleBorder(
        side: const BorderSide(color: MkColors.border),
        borderRadius: BorderRadius.circular(MkRadii.card),
      ),
    ),
    filledButtonTheme: FilledButtonThemeData(
      style: FilledButton.styleFrom(
        backgroundColor: MkColors.primary,
        foregroundColor: Colors.white,
        minimumSize: const Size(48, 52),
        textStyle: const TextStyle(fontWeight: FontWeight.w700),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(MkRadii.button),
        ),
      ),
    ),
    elevatedButtonTheme: ElevatedButtonThemeData(
      style: ElevatedButton.styleFrom(
        elevation: 0,
        backgroundColor: MkColors.primary,
        foregroundColor: Colors.white,
        minimumSize: const Size(48, 52),
        textStyle: const TextStyle(fontWeight: FontWeight.w700),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(MkRadii.button),
        ),
      ),
    ),
    outlinedButtonTheme: OutlinedButtonThemeData(
      style: OutlinedButton.styleFrom(
        foregroundColor: MkColors.ink,
        side: const BorderSide(color: MkColors.border, width: 1.5),
        minimumSize: const Size(48, 52),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(MkRadii.button),
        ),
      ),
    ),
    inputDecorationTheme: InputDecorationTheme(
      filled: true,
      fillColor: MkColors.surface,
      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 15),
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(14),
        borderSide: const BorderSide(color: MkColors.border),
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(14),
        borderSide: const BorderSide(color: MkColors.border),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(14),
        borderSide: const BorderSide(color: MkColors.primary, width: 1.8),
      ),
    ),
    chipTheme: ChipThemeData(
      backgroundColor: MkColors.surfaceMuted,
      selectedColor: MkColors.leafPale,
      side: const BorderSide(color: MkColors.border),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(999)),
    ),
    bottomSheetTheme: const BottomSheetThemeData(
      backgroundColor: MkColors.surfaceRaised,
      surfaceTintColor: Colors.transparent,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(
          top: Radius.circular(MkRadii.sheet),
        ),
      ),
    ),
    // Farmers use this on low-cost devices, often outdoors and one-handed.
    materialTapTargetSize: MaterialTapTargetSize.padded,
    navigationBarTheme: const NavigationBarThemeData(
      backgroundColor: MkColors.surfaceRaised,
      indicatorColor: MkColors.leafPale,
      elevation: 0,
    ),
  );
}

ThemeData mkDarkTheme() {
  final scheme = ColorScheme.fromSeed(
    seedColor: MkColors.primary,
    brightness: Brightness.dark,
  );
  return ThemeData(
    colorScheme: scheme,
    useMaterial3: true,
    fontFamily: 'Roboto',
    scaffoldBackgroundColor: const Color(0xFF0E1211),
    appBarTheme: const AppBarTheme(elevation: 0, centerTitle: false),
    cardTheme: CardThemeData(
      elevation: 0,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(MkRadii.card),
      ),
    ),
  );
}
