import 'package:flutter/material.dart';

/// MkulimaForum design tokens — single source of truth for colors,
/// radii and component themes. Screens must not hardcode colors;
/// use MkColors / Theme.of(context).
class MkColors {
  MkColors._();

  static const Color primary = Color(0xFF165A2A); // Forest green
  static const Color primaryDark = Color(0xFF0C3619); // Deep Forest green
  static const Color leafGreen = Color(0xFF4C9B27); // Vivid leaf green
  static const Color accent = Color(0xFFF5A623); // Sun gold
  static const Color danger = Color(0xFFC62828);
  static const Color surface = Color(0xFFFAF8F2); // Organic cream
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
  final scheme = ColorScheme.fromSeed(
    seedColor: MkColors.primary,
    brightness: Brightness.dark,
  );
  return ThemeData(
    colorScheme: scheme,
    useMaterial3: true,
    fontFamily: 'Roboto',
    scaffoldBackgroundColor: const Color(0xFF121412),
    appBarTheme: AppBarTheme(
      elevation: 0,
      centerTitle: true,
      backgroundColor: scheme.surfaceContainerHigh,
      foregroundColor: scheme.onSurface,
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
    navigationBarTheme: NavigationBarThemeData(
      backgroundColor: scheme.surfaceContainer,
      elevation: 8,
    ),
  );
}
