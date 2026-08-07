import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import '../theme/app_colors.dart';

/// Shared loading / empty / error presentation for API-backed screens.
class AsyncBody<T> extends StatelessWidget {
  const AsyncBody({
    super.key,
    required this.loading,
    required this.error,
    required this.items,
    required this.skeleton,
    required this.onRetry,
    required this.builder,
    this.emptyMessage = 'Nothing here yet',
    this.emptyIcon = Icons.inbox_outlined,
  });

  final bool loading;
  final String? error;
  final List<T> items;
  final Widget skeleton;
  final VoidCallback onRetry;
  final Widget Function(BuildContext context, List<T> items) builder;
  final String emptyMessage;
  final IconData emptyIcon;

  @override
  Widget build(BuildContext context) {
    if (loading && items.isEmpty) {
      return skeleton;
    }

    if (error != null && items.isEmpty) {
      return _MessageState(
        icon: Icons.wifi_off_rounded,
        title: 'Could not load',
        message: error!,
        actionLabel: 'Retry',
        onAction: onRetry,
      );
    }

    if (items.isEmpty) {
      return _MessageState(
        icon: emptyIcon,
        title: emptyMessage,
        message: 'Check back soon for updates.',
        actionLabel: 'Refresh',
        onAction: onRetry,
      );
    }

    return builder(context, items);
  }
}

class _MessageState extends StatelessWidget {
  const _MessageState({
    required this.icon,
    required this.title,
    required this.message,
    required this.actionLabel,
    required this.onAction,
  });

  final IconData icon;
  final String title;
  final String message;
  final String actionLabel;
  final VoidCallback onAction;

  @override
  Widget build(BuildContext context) {
    final colors = AppColors.of(context);
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(28),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, size: 42, color: colors.textMuted),
            const SizedBox(height: 14),
            Text(
              title,
              textAlign: TextAlign.center,
              style: GoogleFonts.dmSans(
                color: colors.textPrimary,
                fontSize: 17,
                fontWeight: FontWeight.w700,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              message,
              textAlign: TextAlign.center,
              style: GoogleFonts.dmSans(
                color: colors.textMuted,
                fontSize: 13,
                height: 1.4,
              ),
            ),
            const SizedBox(height: 18),
            TextButton(
              onPressed: onAction,
              child: Text(
                actionLabel,
                style: GoogleFonts.dmSans(
                  color: AppColors.gold,
                  fontWeight: FontWeight.w700,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
