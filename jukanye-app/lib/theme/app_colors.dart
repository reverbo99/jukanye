import 'package:flutter/material.dart';

/// Brand accents — same in light and dark (match mockup).
class AppColors {
  static const gold = Color(0xFFC9A227);
  static const goldLight = Color(0xFFE0C15A);
  static const green = Color(0xFF1F6B3A);
  static const greenLight = Color(0xFF2E8B57);
  static const danger = Color(0xFFB33A3A);

  static AppPalette of(BuildContext context) {
    return Theme.of(context).extension<AppPalette>() ?? AppPalette.dark;
  }
}

/// Surfaces & text that flip between screenshot-dark and light mode.
@immutable
class AppPalette extends ThemeExtension<AppPalette> {
  const AppPalette({
    required this.background,
    required this.surface,
    required this.surfaceElevated,
    required this.card,
    required this.textPrimary,
    required this.textSecondary,
    required this.textMuted,
    required this.border,
    required this.skeletonBase,
    required this.skeletonHighlight,
    required this.isDark,
  });

  final Color background;
  final Color surface;
  final Color surfaceElevated;
  final Color card;
  final Color textPrimary;
  final Color textSecondary;
  final Color textMuted;
  final Color border;
  final Color skeletonBase;
  final Color skeletonHighlight;
  final bool isDark;

  static const dark = AppPalette(
    background: Color(0xFF0B0B0B),
    surface: Color(0xFF161616),
    surfaceElevated: Color(0xFF1E1E1E),
    card: Color(0xFF1A1A1A),
    textPrimary: Color(0xFFF5F5F5),
    textSecondary: Color(0xFFB0B0B0),
    textMuted: Color(0xFF7A7A7A),
    border: Color(0xFF2A2A2A),
    skeletonBase: Color(0xFF1E1E1E),
    skeletonHighlight: Color(0xFF2A2A2A),
    isDark: true,
  );

  /// Soft light mode — warm paper, gold/green accents preserved.
  static const light = AppPalette(
    background: Color(0xFFF6F3EC),
    surface: Color(0xFFFFFFFF),
    surfaceElevated: Color(0xFFF0EBE1),
    card: Color(0xFFFFFFFF),
    textPrimary: Color(0xFF1A1A1A),
    textSecondary: Color(0xFF5C5C5C),
    textMuted: Color(0xFF8A8A8A),
    border: Color(0xFFE2DCD0),
    skeletonBase: Color(0xFFE8E2D6),
    skeletonHighlight: Color(0xFFF5F1E8),
    isDark: false,
  );

  @override
  AppPalette copyWith({
    Color? background,
    Color? surface,
    Color? surfaceElevated,
    Color? card,
    Color? textPrimary,
    Color? textSecondary,
    Color? textMuted,
    Color? border,
    Color? skeletonBase,
    Color? skeletonHighlight,
    bool? isDark,
  }) {
    return AppPalette(
      background: background ?? this.background,
      surface: surface ?? this.surface,
      surfaceElevated: surfaceElevated ?? this.surfaceElevated,
      card: card ?? this.card,
      textPrimary: textPrimary ?? this.textPrimary,
      textSecondary: textSecondary ?? this.textSecondary,
      textMuted: textMuted ?? this.textMuted,
      border: border ?? this.border,
      skeletonBase: skeletonBase ?? this.skeletonBase,
      skeletonHighlight: skeletonHighlight ?? this.skeletonHighlight,
      isDark: isDark ?? this.isDark,
    );
  }

  @override
  AppPalette lerp(ThemeExtension<AppPalette>? other, double t) {
    if (other is! AppPalette) return this;
    return AppPalette(
      background: Color.lerp(background, other.background, t)!,
      surface: Color.lerp(surface, other.surface, t)!,
      surfaceElevated: Color.lerp(surfaceElevated, other.surfaceElevated, t)!,
      card: Color.lerp(card, other.card, t)!,
      textPrimary: Color.lerp(textPrimary, other.textPrimary, t)!,
      textSecondary: Color.lerp(textSecondary, other.textSecondary, t)!,
      textMuted: Color.lerp(textMuted, other.textMuted, t)!,
      border: Color.lerp(border, other.border, t)!,
      skeletonBase: Color.lerp(skeletonBase, other.skeletonBase, t)!,
      skeletonHighlight: Color.lerp(skeletonHighlight, other.skeletonHighlight, t)!,
      isDark: t < 0.5 ? isDark : other.isDark,
    );
  }
}
