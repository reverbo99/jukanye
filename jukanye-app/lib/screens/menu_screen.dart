import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import '../theme/app_colors.dart';
import '../widgets/app_menu_list.dart';
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
            padding: const EdgeInsets.fromLTRB(20, 8, 20, 0),
            child: Text(
              'Explore every part of Jukanye Festival',
              style: GoogleFonts.dmSans(color: colors.textMuted),
            ),
          ),
          Expanded(
            child: AppMenuList(
              onSelectRoute: onOpenRoute,
            ),
          ),
        ],
      ),
    );
  }
}
