import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import '../data/app_images.dart';
import '../navigation/app_drawer.dart';
import '../navigation/shell_scope.dart';
import '../theme/app_colors.dart';

/// Persistent top bar: sidebar toggle + profile avatar.
class AppPageBar extends StatelessWidget implements PreferredSizeWidget {
  const AppPageBar({
    super.key,
    required this.title,
    this.actions,
    this.showProfile = true,
    this.onProfileTap,
    this.goToTab,
    this.centerTitle = true,
    this.foregroundColor,
    this.backgroundColor,
  });

  final String title;
  final List<Widget>? actions;
  final bool showProfile;
  final VoidCallback? onProfileTap;
  final void Function(int tab)? goToTab;
  final bool centerTitle;
  final Color? foregroundColor;
  final Color? backgroundColor;

  @override
  Size get preferredSize => const Size.fromHeight(kToolbarHeight);

  @override
  Widget build(BuildContext context) {
    final colors = AppColors.of(context);
    final fg = foregroundColor ?? colors.textPrimary;
    final tabSwitcher = goToTab ?? ShellScope.maybeOf(context)?.goToTab;

    return AppBar(
      backgroundColor: backgroundColor ?? colors.background,
      foregroundColor: fg,
      centerTitle: centerTitle,
      automaticallyImplyLeading: false,
      leading: IconButton(
        tooltip: 'Open menu',
        onPressed: () => AppDrawer.open(context, goToTab: tabSwitcher),
        icon: Icon(Icons.menu_rounded, color: fg),
      ),
      title: Text(
        title,
        style: GoogleFonts.dmSans(
          color: fg,
          fontSize: 18,
          fontWeight: FontWeight.w600,
        ),
      ),
      actions: [
        ...?actions,
        if (showProfile)
          Padding(
            padding: const EdgeInsets.only(right: 12),
            child: GestureDetector(
              onTap: onProfileTap ??
                  () => AppRouter.open(context, 'profile', goToTab: tabSwitcher),
              child: Container(
                padding: const EdgeInsets.all(2),
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  border: Border.all(color: AppColors.gold, width: 1.4),
                ),
                child: const CircleAvatar(
                  radius: 16,
                  backgroundImage: NetworkImage(AppImages.profileAvatar),
                ),
              ),
            ),
          ),
      ],
    );
  }
}
