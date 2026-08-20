import 'package:flutter/material.dart';

import '../data/app_data.dart';
import '../screens/about_screen.dart';
import '../screens/awards_screen.dart';
import '../screens/contact_screen.dart';
import '../screens/map_screen.dart';
import '../screens/my_tickets_screen.dart';
import '../screens/news_screen.dart';
import '../screens/people_list_screen.dart';
import '../screens/profile_screen.dart';
import '../screens/programme_screen.dart';
import '../screens/shop_screen.dart';
import '../screens/sponsors_screen.dart';
import '../screens/tourism_screen.dart';
import '../theme/app_colors.dart';
import 'app_page_route.dart';
import 'main_shell.dart';

/// Shared route handling for bottom nav, menu tab, and sidebar.
abstract final class AppRouter {
  static void open(
    BuildContext context,
    String route, {
    void Function(int tab)? goToTab,
  }) {
    switch (route) {
      case 'about':
        AppNav.push(context, const AboutScreen());
        break;
      case 'programme':
        AppNav.push(context, const ProgrammeScreen());
        break;
      case 'tourism':
        AppNav.push(context, const TourismScreen());
        break;
      case 'shop':
        AppNav.push(context, const ShopScreen());
        break;
      case 'map':
        AppNav.push(context, const MapScreen());
        break;
      case 'profile':
        AppNav.push(context, const ProfileScreen());
        break;
      case 'my_tickets':
        AppNav.push(context, const MyTicketsScreen());
        break;
      case 'donate':
        if (goToTab != null) {
          goToTab(2);
        } else {
          AppNav.pushReplacement(context, const MainShell(initialIndex: 2));
        }
        break;
      case 'tickets':
        if (goToTab != null) {
          goToTab(1);
        } else {
          AppNav.pushReplacement(context, const MainShell(initialIndex: 1));
        }
        break;
      case 'home':
        if (goToTab != null) {
          goToTab(0);
        } else {
          AppNav.pushReplacement(context, const MainShell());
        }
        break;
      case 'news':
        AppNav.push(context, const NewsScreen());
        break;
      case 'awards':
        AppNav.push(context, const AwardsScreen());
        break;
      case 'speakers':
        AppNav.push(
          context,
          const PeopleListScreen(
            title: 'Speakers',
            type: 'speaker',
            emptyIcon: Icons.record_voice_over_outlined,
          ),
        );
        break;
      case 'artists':
        AppNav.push(
          context,
          const PeopleListScreen(
            title: 'Artists',
            type: 'artist',
            emptyIcon: Icons.music_note_outlined,
          ),
        );
        break;
      case 'heroes':
        AppNav.push(
          context,
          const PeopleListScreen(
            title: 'Heroes',
            type: 'hero',
            emptyIcon: Icons.military_tech_outlined,
          ),
        );
        break;
      case 'exhibitions':
        AppNav.push(
          context,
          const PeopleListScreen(
            title: 'Exhibitions',
            type: 'exhibition',
            emptyIcon: Icons.museum_outlined,
          ),
        );
        break;
      case 'friends':
        AppNav.push(
          context,
          const PeopleListScreen(
            title: 'Friends',
            type: 'friend',
            emptyIcon: Icons.groups_outlined,
          ),
        );
        break;
      case 'sponsors':
        AppNav.push(context, const SponsorsScreen());
        break;
      case 'contact':
        AppNav.push(context, const ContactScreen());
        break;
      default:
        break;
    }
  }
}

IconData menuIconFor(String key) {
  return switch (key) {
    'info_outline' => Icons.info_outline,
    'event_note' => Icons.event_note_outlined,
    'record_voice_over' => Icons.record_voice_over_outlined,
    'music_note' => Icons.music_note_outlined,
    'military_tech' => Icons.military_tech_outlined,
    'museum' => Icons.museum_outlined,
    'travel_explore' => Icons.travel_explore,
    'shopping_bag' => Icons.shopping_bag_outlined,
    'groups' => Icons.groups_outlined,
    'volunteer_activism' => Icons.volunteer_activism_outlined,
    'emoji_events' => Icons.emoji_events_outlined,
    'handshake' => Icons.handshake_outlined,
    'newspaper' => Icons.newspaper_outlined,
    'map' => Icons.map_outlined,
    'mail_outline' => Icons.mail_outline,
    'person_outline' => Icons.person_outline,
    _ => Icons.circle_outlined,
  };
}

/// Left sidebar with festival menus — works from any screen.
abstract final class AppDrawer {
  static Future<void> open(
    BuildContext context, {
    void Function(int tab)? goToTab,
  }) {
    final colors = AppColors.of(context);
    final width = MediaQuery.sizeOf(context).width * 0.82;

    return showGeneralDialog<void>(
      context: context,
      barrierLabel: 'Menu',
      barrierDismissible: true,
      barrierColor: Colors.black.withValues(alpha: 0.55),
      transitionDuration: const Duration(milliseconds: 280),
      pageBuilder: (ctx, animation, secondaryAnimation) {
        return Align(
          alignment: Alignment.centerLeft,
          child: Material(
            color: colors.surface,
            child: SizedBox(
              width: width.clamp(280, 340),
              height: double.infinity,
              child: SafeArea(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Padding(
                      padding: const EdgeInsets.fromLTRB(20, 16, 12, 8),
                      child: Row(
                        children: [
                          const Expanded(
                            child: Text(
                              'Jukanye',
                              style: TextStyle(
                                color: AppColors.gold,
                                fontSize: 22,
                                fontWeight: FontWeight.w700,
                                fontFamily: 'serif',
                              ),
                            ),
                          ),
                          IconButton(
                            onPressed: () => Navigator.of(ctx).pop(),
                            icon: Icon(Icons.close, color: colors.textPrimary),
                          ),
                        ],
                      ),
                    ),
                    Padding(
                      padding: const EdgeInsets.fromLTRB(20, 0, 20, 12),
                      child: Text(
                        'Explore the festival',
                        style: TextStyle(color: colors.textMuted, fontSize: 13),
                      ),
                    ),
                    const Divider(height: 1),
                    Expanded(
                      child: ListView.builder(
                        padding: const EdgeInsets.symmetric(vertical: 8),
                        itemCount: AppData.menuItems.length,
                        itemBuilder: (context, index) {
                          final item = AppData.menuItems[index];
                          return ListTile(
                            leading: Icon(
                              menuIconFor(item.$3),
                              color: AppColors.gold,
                              size: 22,
                            ),
                            title: Text(
                              item.$1,
                              style: TextStyle(
                                color: colors.textPrimary,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                            onTap: () {
                              Navigator.of(ctx).pop();
                              AppRouter.open(
                                context,
                                item.$2,
                                goToTab: goToTab,
                              );
                            },
                          );
                        },
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        );
      },
      transitionBuilder: (context, animation, secondaryAnimation, child) {
        final curved = CurvedAnimation(parent: animation, curve: Curves.easeOutCubic);
        return SlideTransition(
          position: Tween<Offset>(
            begin: const Offset(-1, 0),
            end: Offset.zero,
          ).animate(curved),
          child: child,
        );
      },
    );
  }
}
