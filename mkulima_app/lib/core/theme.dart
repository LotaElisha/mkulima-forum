import 'package:flutter/material.dart';

/// MkulimaForum's editorial palette. Neutral surfaces carry the interface;
/// green is reserved for agricultural identity and positive states.
class MkColors {
  MkColors._();

  static const Color primary = Color(0xFF24221D);
  static const Color primaryDark = Color(0xFF171612);
  static const Color leafGreen = Color(0xFF5B8F45);
  static const Color accent = Color(0xFFEBAA27);
  static const Color accentSoft = Color(0xFFF8E8BD);
  static const Color charcoal = Color(0xFF24221D);
  static const Color ink = Color(0xFF302E29);
  static const Color muted = Color(0xFF706D64);
  static const Color surface = Color(0xFFFAF7EF);
  static const Color surfaceRaised = Color(0xFFFFFDF8);
  static const Color surfaceMuted = Color(0xFFF1EBDD);
  static const Color border = Color(0xFFE2DACB);
  static const Color danger = Color(0xFFB84638);
  static const Color success = Color(0xFF39752D);
}

class MkRadii {
  MkRadii._();
  static const double card = 18;
  static const double button = 14;
  static const double sheet = 24;
}

ThemeData mkLightTheme() {
  const scheme = ColorScheme.light(
    primary: MkColors.charcoal,
    onPrimary: Colors.white,
    secondary: MkColors.accent,
    onSecondary: MkColors.charcoal,
    tertiary: MkColors.leafGreen,
    onTertiary: Colors.white,
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
      foregroundColor: MkColors.charcoal,
      surfaceTintColor: Colors.transparent,
      titleTextStyle: TextStyle(
        color: MkColors.charcoal,
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
        backgroundColor: MkColors.charcoal,
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
        backgroundColor: MkColors.charcoal,
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
        foregroundColor: MkColors.charcoal,
        side: const BorderSide(color: MkColors.charcoal),
        minimumSize: const Size(48, 52),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(MkRadii.button),
        ),
      ),
    ),
    inputDecorationTheme: InputDecorationTheme(
      filled: true,
      fillColor: MkColors.surfaceRaised,
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
        borderSide: const BorderSide(color: MkColors.charcoal, width: 1.5),
      ),
    ),
    chipTheme: ChipThemeData(
      backgroundColor: MkColors.surfaceMuted,
      selectedColor: MkColors.accentSoft,
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
    navigationBarTheme: const NavigationBarThemeData(
      backgroundColor: MkColors.surfaceRaised,
      indicatorColor: MkColors.accentSoft,
      elevation: 0,
    ),
  );
}

ThemeData mkDarkTheme() {
  final scheme = ColorScheme.fromSeed(
    seedColor: MkColors.accent,
    brightness: Brightness.dark,
  );
  return ThemeData(
    colorScheme: scheme,
    useMaterial3: true,
    fontFamily: 'Roboto',
    scaffoldBackgroundColor: const Color(0xFF1D1C19),
    appBarTheme: const AppBarTheme(elevation: 0, centerTitle: false),
    cardTheme: CardThemeData(
      elevation: 0,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(MkRadii.card),
      ),
    ),
  );
}
