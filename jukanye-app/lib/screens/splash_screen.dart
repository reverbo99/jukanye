import 'package:flutter/material.dart';

import '../navigation/app_drawer.dart';
import '../navigation/app_page_route.dart';
import '../navigation/main_shell.dart';
import '../widgets/app_bottom_nav.dart';
import '../widgets/landing_content.dart';

/// Screen 1 — Splash / Landing (no sidebar toggle; Menu is in bottom nav).
class SplashScreen extends StatelessWidget {
  const SplashScreen({super.key});

  void _enter(BuildContext context, [int tab = 0, String? pendingRoute]) {
    AppNav.pushReplacement(
      context,
      MainShell(initialIndex: tab, pendingRoute: pendingRoute),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      extendBody: true,
      body: LandingContent(
        showLogo: true,
        onActivities: () => _enter(context, 0, 'programme'),
        onBuyTickets: () => _enter(context, 0, 'tickets'),
        onSupport: () => _enter(context, 0, 'donate'),
        onVote: () => AppRouter.open(context, 'vote'),
      ),
      bottomNavigationBar: AppBottomNav(
        currentIndex: 0,
        onTap: (index) => _enter(context, index),
      ),
    );
  }
}
