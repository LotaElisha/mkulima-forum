import 'package:flutter/material.dart';

/// MkulimaForum design tokens — single source of truth for colors,
/// radii and component themes. Screens must not hardcode colors;
/// use MkColors / Theme.of(context).
class MkColors {
  MkColors._();

  static const Color primary = Color(0xFF2E7D32); // Kilimo green
  static const Color primaryDark = Color(0xFF1B5E20);
  static const Color accent = Color(0xFFF9A825); // Harvest amber
  static const Color danger = Color(0xFFC62828);
  static const Color surface = Color(0xFFF6F8F6);
}

class MkRadii {
  MkRadii._();

  static const double card = 12;
  static const double button = 12;
  static const double sheet = 20;
}

ThemeData mkLightTheme() {
  final scheme = ColorScheme.fromSeed(
    seedColor: MkColors.primary,
    brightness: Brightness.light,
  );
  return ThemeData(
    colorScheme: scheme,
    useMaterial3: true,
    fontFamily: 'Roboto',
    scaffoldBackgroundColor: MkColors.surface,
    appBarTheme: const AppBarTheme(
      elevation: 0,
      centerTitle: true,
      backgroundColor: MkColors.primary,
      foregroundColor: Colors.white,
    ),
    cardTheme: CardThemeData(
      elevation: 2,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(MkRadii.card),
      ),
    ),
    elevatedButtonTheme: ElevatedButtonThemeData(
      style: ElevatedButton.styleFrom(
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(MkRadii.button),
        ),
      ),
    ),
    navigationBarTheme: const NavigationBarThemeData(
      backgroundColor: Colors.white,
      elevation: 8,
    ),
  );
}

ThemeData mkDarkTheme() {
  return ThemeData(
    colorScheme: ColorScheme.fromSeed(
      seedColor: MkColors.primary,
      brightness: Brightness.dark,
    ),
    useMaterial3: true,
    fontFamily: 'Roboto',
  );
}
