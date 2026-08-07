import 'package:flutter/material.dart';

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
        onBuyTickets: () => _enter(context, 1),
        onDonate: () => _enter(context, 2),
        bottomPadding: 96,
      ),
      bottomNavigationBar: AppBottomNav(
        currentIndex: 0,
        onTap: (index) {
          if (index == 0) return;
          _enter(context, index);
        },
      ),
    );
  }
}
