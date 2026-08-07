import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import '../data/app_data.dart';
import '../navigation/app_drawer.dart';
import '../theme/app_colors.dart';
import '../widgets/app_page_bar.dart';

class MenuScreen extends StatelessWidget {
  const MenuScreen({
    super.key,
    required this.onOpenRoute,
    this.goToTab,
  });

  final ValueChanged<String> onOpenRoute;
  final void Function(int tab)? goToTab;

  @override
  Widget build(BuildContext context) {
    final colors = AppColors.of(context);
    return Scaffold(
      appBar: AppPageBar(title: 'Menu', goToTab: goToTab),
      body: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(20, 8, 20, 12),
            child: Text(
              'Explore every part of Jukanye Festival',
              style: GoogleFonts.dmSans(color: colors.textMuted),
            ),
          ),
          Expanded(
            child: ListView.separated(
              padding: const EdgeInsets.fromLTRB(16, 4, 16, 24),
              itemCount: AppData.menuItems.length,
              separatorBuilder: (_, _) => const SizedBox(height: 6),
              itemBuilder: (context, index) {
                final item = AppData.menuItems[index];
                return Material(
                  color: colors.card,
                  borderRadius: BorderRadius.circular(14),
                  child: ListTile(
                    onTap: () => onOpenRoute(item.$2),
                    leading: Container(
                      width: 40,
                      height: 40,
                      decoration: BoxDecoration(
                        color: colors.surfaceElevated,
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Icon(
                        menuIconFor(item.$3),
                        color: AppColors.gold,
                        size: 20,
                      ),
                    ),
                    title: Text(
                      item.$1,
                      style: GoogleFonts.dmSans(
                        color: colors.textPrimary,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    trailing: Icon(
                      Icons.chevron_right,
                      color: colors.textMuted,
                    ),
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}
