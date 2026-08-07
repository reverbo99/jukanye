import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import '../data/app_images.dart';
import '../main.dart';
import '../navigation/app_page_route.dart';
import '../screens/my_tickets_screen.dart';
import '../theme/app_colors.dart';
import '../widgets/common.dart';
import '../widgets/skeleton.dart';
import '../widgets/app_page_bar.dart';

class ProfileScreen extends StatelessWidget {
  const ProfileScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final colors = AppColors.of(context);
    final links = [
      (Icons.person_outline, 'My Profile', null),
      (Icons.confirmation_number_outlined, 'My Tickets', 'tickets'),
      (Icons.volunteer_activism_outlined, 'My Donations', null),
      (Icons.shopping_bag_outlined, 'My Orders', null),
      (Icons.settings_outlined, 'Settings', null),
      (Icons.help_outline, 'Help & Support', null),
      (Icons.logout, 'Log Out', 'logout'),
    ];

    return Scaffold(
      appBar: const AppPageBar(title: 'Profile'),
      body: PageSkeletonGate(
        skeleton: ScreenSkeletons.listCards(count: 7),
        child: ListView(
          padding: const EdgeInsets.fromLTRB(20, 12, 20, 28),
          children: [
            Center(
              child: Column(
                children: [
                  Container(
                    padding: const EdgeInsets.all(3),
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      border: Border.all(color: AppColors.gold, width: 2),
                    ),
                    child: const CircleAvatar(
                      radius: 48,
                      backgroundImage: NetworkImage(AppImages.profileAvatar),
                    ),
                  ),
                  const SizedBox(height: 14),
                  Text(
                    'John Carlos',
                    style: GoogleFonts.cinzel(
                      color: colors.textPrimary,
                      fontSize: 22,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    'john.carlos@email.com',
                    style: TextStyle(color: colors.textMuted),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    '+255 712 345 678',
                    style: TextStyle(color: colors.textMuted),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 20),
            ListenableBuilder(
              listenable: themeController,
              builder: (context, _) {
                final isDark = themeController.isDark;
                return AppCard(
                  child: Row(
                    children: [
                      Icon(
                        isDark ? Icons.dark_mode_rounded : Icons.light_mode_rounded,
                        color: AppColors.gold,
                      ),
                      const SizedBox(width: 14),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Appearance',
                              style: TextStyle(
                                color: colors.textPrimary,
                                fontWeight: FontWeight.w700,
                              ),
                            ),
                            const SizedBox(height: 2),
                            Text(
                              isDark ? 'Dark mode (design default)' : 'Light mode',
                              style: TextStyle(
                                color: colors.textMuted,
                                fontSize: 12,
                              ),
                            ),
                          ],
                        ),
                      ),
                      Switch.adaptive(
                        value: isDark,
                        activeThumbColor: Colors.black,
                        activeTrackColor: AppColors.gold,
                        onChanged: (dark) {
                          themeController.setMode(
                            dark ? ThemeMode.dark : ThemeMode.light,
                          );
                        },
                      ),
                    ],
                  ),
                );
              },
            ),
            const SizedBox(height: 16),
            ...links.map(
              (item) => Padding(
                padding: const EdgeInsets.only(bottom: 8),
                child: AppCard(
                  onTap: () {
                    if (item.$3 == 'tickets') {
                      AppNav.push(context, const MyTicketsScreen());
                      return;
                    }
                    if (item.$3 == 'logout') {
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(
                          content: const Text('Logged out (demo)'),
                          backgroundColor: colors.surfaceElevated,
                        ),
                      );
                      return;
                    }
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(
                        content: Text('${item.$2} coming soon'),
                        backgroundColor: colors.surfaceElevated,
                      ),
                    );
                  },
                  child: Row(
                    children: [
                      Icon(
                        item.$1,
                        color: item.$3 == 'logout' ? AppColors.danger : AppColors.gold,
                      ),
                      const SizedBox(width: 14),
                      Expanded(
                        child: Text(
                          item.$2,
                          style: TextStyle(
                            color: item.$3 == 'logout'
                                ? AppColors.danger
                                : colors.textPrimary,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ),
                      Icon(Icons.chevron_right, color: colors.textMuted),
                    ],
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
