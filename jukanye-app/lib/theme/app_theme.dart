import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';

import 'app_colors.dart';

class AppTheme {
  static ThemeData dark() => _build(AppPalette.dark, Brightness.dark);

  static ThemeData light() => _build(AppPalette.light, Brightness.light);

  static ThemeData _build(AppPalette palette, Brightness brightness) {
    final base = brightness == Brightness.dark
        ? ThemeData.dark(useMaterial3: true)
        : ThemeData.light(useMaterial3: true);
    final display = GoogleFonts.cinzelTextTheme(base.textTheme);
    final body = GoogleFonts.dmSansTextTheme(base.textTheme);
    final overlay = brightness == Brightness.dark
        ? SystemUiOverlayStyle.light
        : SystemUiOverlayStyle.dark;

    return base.copyWith(
      scaffoldBackgroundColor: palette.background,
      extensions: [palette],
      colorScheme: ColorScheme(
        brightness: brightness,
        primary: AppColors.gold,
        onPrimary: Colors.black,
        secondary: AppColors.green,
        onSecondary: Colors.white,
        error: AppColors.danger,
        onError: Colors.white,
        surface: palette.surface,
        onSurface: palette.textPrimary,
      ),
      appBarTheme: AppBarTheme(
        backgroundColor: palette.background,
        foregroundColor: palette.textPrimary,
        elevation: 0,
        centerTitle: true,
        systemOverlayStyle: overlay.copyWith(statusBarColor: Colors.transparent),
        titleTextStyle: GoogleFonts.cinzel(
          color: palette.textPrimary,
          fontSize: 18,
          fontWeight: FontWeight.w600,
        ),
        iconTheme: IconThemeData(color: palette.textPrimary),
      ),
      textTheme: body.copyWith(
        displayLarge: display.displayLarge?.copyWith(color: palette.textPrimary),
        displayMedium: display.displayMedium?.copyWith(color: palette.textPrimary),
        displaySmall: display.displaySmall?.copyWith(color: palette.textPrimary),
        headlineLarge: display.headlineLarge?.copyWith(
          color: palette.textPrimary,
          fontWeight: FontWeight.w700,
        ),
        headlineMedium: display.headlineMedium?.copyWith(
          color: palette.textPrimary,
          fontWeight: FontWeight.w700,
        ),
        headlineSmall: display.headlineSmall?.copyWith(
          color: palette.textPrimary,
          fontWeight: FontWeight.w600,
        ),
        titleLarge: display.titleLarge?.copyWith(
          color: palette.textPrimary,
          fontWeight: FontWeight.w600,
        ),
        titleMedium: body.titleMedium?.copyWith(
          color: palette.textPrimary,
          fontWeight: FontWeight.w600,
        ),
        bodyLarge: body.bodyLarge?.copyWith(color: palette.textPrimary),
        bodyMedium: body.bodyMedium?.copyWith(color: palette.textSecondary),
        bodySmall: body.bodySmall?.copyWith(color: palette.textMuted),
        labelLarge: body.labelLarge?.copyWith(
          color: palette.textPrimary,
          fontWeight: FontWeight.w700,
          letterSpacing: 0.6,
        ),
      ),
      dividerTheme: DividerThemeData(color: palette.border, thickness: 1),
      chipTheme: ChipThemeData(
        backgroundColor: palette.card,
        selectedColor: AppColors.gold,
        side: BorderSide.none,
        labelStyle: TextStyle(color: palette.textSecondary),
      ),
      snackBarTheme: SnackBarThemeData(
        backgroundColor: palette.surfaceElevated,
        contentTextStyle: TextStyle(color: palette.textPrimary),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: palette.surfaceElevated,
        hintStyle: TextStyle(color: palette.textMuted),
        labelStyle: TextStyle(color: palette.textMuted),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: palette.border),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: palette.border),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: AppColors.gold, width: 1.4),
        ),
      ),
    );
  }
}
