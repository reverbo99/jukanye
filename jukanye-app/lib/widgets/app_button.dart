import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import '../theme/app_colors.dart';

enum AppButtonVariant { gold, green, outline, ghost }

class AppButton extends StatelessWidget {
  const AppButton({
    super.key,
    required this.label,
    required this.onPressed,
    this.variant = AppButtonVariant.gold,
    this.expand = true,
    this.icon,
    this.height = 52,
  });

  final String label;
  final VoidCallback? onPressed;
  final AppButtonVariant variant;
  final bool expand;
  final IconData? icon;
  final double height;

  @override
  Widget build(BuildContext context) {
    final colors = AppColors.of(context);
    final (bg, fg, border) = switch (variant) {
      AppButtonVariant.gold => (AppColors.gold, Colors.black, null),
      AppButtonVariant.green => (AppColors.green, Colors.white, null),
      AppButtonVariant.outline => (Colors.transparent, colors.textPrimary, colors.border),
      AppButtonVariant.ghost => (Colors.transparent, colors.textSecondary, null),
    };

    final child = Row(
      mainAxisAlignment: MainAxisAlignment.center,
      mainAxisSize: MainAxisSize.min,
      children: [
        if (icon != null) ...[
          Icon(icon, size: 18, color: fg),
          const SizedBox(width: 8),
        ],
        Text(
          label.toUpperCase(),
          style: GoogleFonts.dmSans(
            color: fg,
            fontWeight: FontWeight.w700,
            fontSize: 13,
            letterSpacing: 1.1,
          ),
        ),
      ],
    );

    return Opacity(
      opacity: onPressed == null ? 0.55 : 1,
      child: Material(
        color: bg,
        borderRadius: BorderRadius.circular(14),
        child: InkWell(
          onTap: onPressed,
          borderRadius: BorderRadius.circular(14),
          child: Container(
            height: height,
            width: expand ? double.infinity : null,
            padding: EdgeInsets.symmetric(horizontal: expand ? 16 : 22),
            alignment: Alignment.center,
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(14),
              border: border == null ? null : Border.all(color: border),
            ),
            child: child,
          ),
        ),
      ),
    );
  }
}
