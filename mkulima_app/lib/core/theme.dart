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
  /// Darkened from #626D66 so 13px captions clear 4.5:1 on white with room
  /// to spare in direct sunlight (about 6:1).
  static const Color muted = Color(0xFF5A645E);

  // ── Surfaces: white first ─────────────────────────────────────────
  static const Color surface = Color(0xFFFFFFFF);
  static const Color surfaceRaised = Color(0xFFFFFFFF);
  static const Color surfaceMuted = Color(0xFFF4F7F4);
  static const Color border = Color(0xFFE5EAE6);

  // ── Semantic ──────────────────────────────────────────────────────
  static const Color danger = Color(0xFFB3261E);
  static const Color success = Color(0xFF1B7A3E);
  static const Color warning = Color(0xFFB26A00);

  /// Text that sits on [accentSoft], e.g. the offline banner. Amber text on
  /// pale amber fails contrast; this brown passes at 13px.
  static const Color onAccentSoft = Color(0xFF5C3A00);

  /// Skeleton placeholder fill.
  static const Color skeleton = Color(0xFFEDF1EE);
  static const Color dangerBorder = Color(0xFFF2C9C5);
}

/// The type scale. Six sizes, nothing below 13. Screens use these rather than
/// ad-hoc `fontSize:` values so Swahili strings, which run long, get the same
/// treatment everywhere and nothing drops under the 13px floor.
class MkText {
  MkText._();

  static const TextStyle page = TextStyle(
    fontSize: 24,
    fontWeight: FontWeight.w700,
    letterSpacing: -.3,
    color: MkColors.ink,
  );
  static const TextStyle section = TextStyle(
    fontSize: 18,
    fontWeight: FontWeight.w700,
    color: MkColors.ink,
  );
  static const TextStyle title = TextStyle(
    fontSize: 17,
    fontWeight: FontWeight.w700,
    height: 1.35,
    color: MkColors.ink,
  );
  static const TextStyle body = TextStyle(
    fontSize: 15,
    height: 1.45,
    color: MkColors.ink,
  );
  static const TextStyle bodyMuted = TextStyle(
    fontSize: 15,
    height: 1.45,
    color: MkColors.muted,
  );
  static const TextStyle label = TextStyle(
    fontSize: 15,
    fontWeight: FontWeight.w600,
    color: MkColors.ink,
  );
  static const TextStyle caption = TextStyle(
    fontSize: 13,
    height: 1.35,
    color: MkColors.muted,
  );
}

class MkRadii {
  MkRadii._();
  static const double card = 16;
  static const double button = 14;
  static const double sheet = 24;
}

/// Material's defaults put several text roles at 11-12px (labelSmall,
/// bodySmall, the navigation bar labels). Every role is pinned at 13 or above.
const TextTheme _mkTextTheme = TextTheme(
  headlineSmall: MkText.page,
  titleLarge: TextStyle(fontSize: 20, fontWeight: FontWeight.w700),
  titleMedium: TextStyle(fontSize: 17, fontWeight: FontWeight.w700),
  titleSmall: TextStyle(fontSize: 15, fontWeight: FontWeight.w600),
  bodyLarge: TextStyle(fontSize: 16, height: 1.45),
  bodyMedium: TextStyle(fontSize: 15, height: 1.45),
  bodySmall: TextStyle(fontSize: 13, height: 1.35),
  labelLarge: TextStyle(fontSize: 15, fontWeight: FontWeight.w700),
  labelMedium: TextStyle(fontSize: 13, fontWeight: FontWeight.w600),
  labelSmall: TextStyle(fontSize: 13, fontWeight: FontWeight.w500),
);

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
    textTheme: _mkTextTheme.apply(
      bodyColor: MkColors.ink,
      displayColor: MkColors.ink,
    ),
    scaffoldBackgroundColor: MkColors.surface,
    dividerColor: MkColors.border,
    appBarTheme: const AppBarTheme(
      elevation: 0,
      scrolledUnderElevation: 0,
      centerTitle: false,
      backgroundColor: MkColors.surface,
      foregroundColor: MkColors.ink,
      surfaceTintColor: Colors.transparent,
      titleTextStyle: MkText.page,
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
        minimumSize: const Size(48, 48),
        textStyle: const TextStyle(fontSize: 16, fontWeight: FontWeight.w700),
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
        minimumSize: const Size(48, 48),
        textStyle: const TextStyle(fontSize: 16, fontWeight: FontWeight.w700),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(MkRadii.button),
        ),
      ),
    ),
    outlinedButtonTheme: OutlinedButtonThemeData(
      style: OutlinedButton.styleFrom(
        foregroundColor: MkColors.primaryDark,
        side: const BorderSide(color: MkColors.primary, width: 1.5),
        minimumSize: const Size(48, 48),
        textStyle: const TextStyle(fontSize: 16, fontWeight: FontWeight.w700),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(MkRadii.button),
        ),
      ),
    ),
    textButtonTheme: TextButtonThemeData(
      style: TextButton.styleFrom(
        foregroundColor: MkColors.primary,
        minimumSize: const Size(44, 44),
        textStyle: const TextStyle(fontSize: 15, fontWeight: FontWeight.w700),
      ),
    ),
    inputDecorationTheme: InputDecorationTheme(
      filled: true,
      fillColor: MkColors.surface,
      hintStyle: const TextStyle(color: MkColors.muted, fontSize: 15),
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
      // Pale-leaf selection with ink text: a dark-green fill would need the
      // label colour to flip with state, and green-on-green has shipped once.
      selectedColor: MkColors.leafPale,
      checkmarkColor: MkColors.primaryDark,
      labelStyle: const TextStyle(fontSize: 15, color: MkColors.ink),
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 6),
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
    navigationBarTheme: NavigationBarThemeData(
      backgroundColor: MkColors.surfaceRaised,
      surfaceTintColor: Colors.transparent,
      indicatorColor: MkColors.leafPale,
      elevation: 0,
      height: 72,
      labelTextStyle: WidgetStateProperty.resolveWith(
        (states) => TextStyle(
          fontSize: 13,
          fontWeight: states.contains(WidgetState.selected)
              ? FontWeight.w700
              : FontWeight.w500,
          color: states.contains(WidgetState.selected)
              ? MkColors.primaryDark
              : MkColors.muted,
        ),
      ),
      iconTheme: WidgetStateProperty.resolveWith(
        (states) => IconThemeData(
          size: 24,
          color: states.contains(WidgetState.selected)
              ? MkColors.primaryDark
              : MkColors.muted,
        ),
      ),
    ),
    snackBarTheme: const SnackBarThemeData(
      behavior: SnackBarBehavior.floating,
      contentTextStyle: TextStyle(fontSize: 15, color: Colors.white),
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
    textTheme: _mkTextTheme,
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
