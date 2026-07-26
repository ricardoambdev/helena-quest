import 'package:flutter/material.dart';

class AppTheme {
  AppTheme._();

  // Palette
  static const Color ignite = Color(0xFFFF6600);
  static const Color ember = Color(0xFFCC5200);
  static const Color flame = Color(0xFFFF8533);
  static const Color ink = Color(0xFF0D0D0F);
  static const Color paper = Color(0xFFFAF8F5);
  static const Color chalk = Color(0xFF7A7468);
  static const Color rule = Color(0xFFE0DCD3);
  static const Color error = Color(0xFFD32F2F);

  // Team colors (for dynamic assignment)
  static const List<Color> teamColors = [
    Color(0xFFFF6600),
    Color(0xFF2563EB),
    Color(0xFF16A34A),
    Color(0xFFDC2626),
    Color(0xFF9333EA),
    Color(0xFF0891B2),
  ];

  static Color teamColorFromHex(String hex) {
    hex = hex.replaceFirst('#', '');
    if (hex.length == 6) hex = 'FF$hex';
    return Color(int.parse(hex, radix: 16));
  }

  static ThemeData get light => ThemeData(
    useMaterial3: true,
    brightness: Brightness.light,
    colorScheme: ColorScheme.light(
      primary: ignite,
      onPrimary: Colors.white,
      primaryContainer: ignite.withValues(alpha: 0.15),
      secondary: ink,
      onSecondary: paper,
      surface: paper,
      onSurface: ink,
      surfaceContainerHighest: const Color(0xFFF5F5F5),
      outline: rule,
      error: error,
    ),
    scaffoldBackgroundColor: paper,
    fontFamily: 'Nunito',
    textTheme: const TextTheme(
      displayLarge: TextStyle(
        fontFamily: 'Inter',
        fontWeight: FontWeight.w800,
        fontSize: 32,
        height: 1.1,
        color: ink,
      ),
      displayMedium: TextStyle(
        fontFamily: 'Inter',
        fontWeight: FontWeight.w700,
        fontSize: 24,
        height: 1.15,
        color: ink,
      ),
      headlineLarge: TextStyle(
        fontFamily: 'Nunito',
        fontWeight: FontWeight.w700,
        fontSize: 20,
        height: 1.2,
        color: ink,
      ),
      titleLarge: TextStyle(
        fontFamily: 'Nunito',
        fontWeight: FontWeight.w600,
        fontSize: 16,
        height: 1.25,
        color: ink,
      ),
      bodyLarge: TextStyle(
        fontFamily: 'Nunito',
        fontWeight: FontWeight.w400,
        fontSize: 16,
        height: 1.5,
        color: ink,
      ),
      bodyMedium: TextStyle(
        fontFamily: 'Nunito',
        fontWeight: FontWeight.w400,
        fontSize: 14,
        height: 1.5,
        color: ink,
      ),
      labelLarge: TextStyle(
        fontFamily: 'JetBrains Mono',
        fontWeight: FontWeight.w500,
        fontSize: 14,
        height: 1.3,
        color: ink,
      ),
      labelMedium: TextStyle(
        fontFamily: 'JetBrains Mono',
        fontWeight: FontWeight.w400,
        fontSize: 12,
        height: 1.3,
        color: chalk,
      ),
    ),
    elevatedButtonTheme: ElevatedButtonThemeData(
      style: ElevatedButton.styleFrom(
        backgroundColor: ignite,
        foregroundColor: Colors.white,
        padding: const EdgeInsets.symmetric(horizontal: 28, vertical: 16),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(12),
        ),
        textStyle: const TextStyle(
          fontFamily: 'JetBrains Mono',
          fontWeight: FontWeight.w500,
          fontSize: 15,
        ),
      ),
    ),
    outlinedButtonTheme: OutlinedButtonThemeData(
      style: OutlinedButton.styleFrom(
        foregroundColor: ignite,
        side: const BorderSide(color: ignite, width: 1.5),
        padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 14),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(12),
        ),
        textStyle: const TextStyle(
          fontFamily: 'JetBrains Mono',
          fontWeight: FontWeight.w500,
          fontSize: 14,
        ),
      ),
    ),
    inputDecorationTheme: InputDecorationTheme(
      filled: true,
      fillColor: Colors.white,
      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: rule),
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: rule),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: ignite, width: 2),
      ),
      errorBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: error),
      ),
      labelStyle: const TextStyle(
        fontFamily: 'Nunito',
        fontWeight: FontWeight.w400,
        color: chalk,
      ),
    ),
    cardTheme: CardThemeData(
      elevation: 0,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
        side: const BorderSide(color: rule),
      ),
      clipBehavior: Clip.antiAlias,
    ),
    dividerTheme: const DividerThemeData(
      color: rule,
      thickness: 1,
      space: 1,
    ),
  );
}
