import 'package:flutter/material.dart';

import '../screens/about_screen.dart';
import '../screens/menu_screen.dart';
import '../screens/news_screen.dart';
import '../screens/shop_screen.dart';
import '../widgets/app_bottom_nav.dart';
import '../widgets/landing_content.dart';
import 'app_drawer.dart';
import 'shell_scope.dart';

class MainShell extends StatefulWidget {
  const MainShell({super.key, this.initialIndex = 0, this.pendingRoute});

  final int initialIndex;
  final String? pendingRoute;

  @override
  State<MainShell> createState() => _MainShellState();
}

class _MainShellState extends State<MainShell> {
  late int _index;
  final _shellNavKey = GlobalKey<NavigatorState>();

  @override
  void initState() {
    super.initState();
    _index = widget.initialIndex.clamp(0, 4);
    final pending = widget.pendingRoute;
    if (pending != null) {
      WidgetsBinding.instance.addPostFrameCallback((_) {
        if (!mounted) return;
        openRoute(pending);
      });
    }
  }

  void goToTab(int index) {
    _shellNavKey.currentState?.popUntil((route) => route.isFirst);
    if (index == _index) return;
    setState(() => _index = index);
  }

  void openRoute(String route) {
    final navContext = _shellNavKey.currentContext;
    if (navContext == null) return;
    AppRouter.open(navContext, route, goToTab: goToTab);
  }

  @override
  Widget build(BuildContext context) {
    return ShellScope(
      navigatorKey: _shellNavKey,
      goToTab: goToTab,
      currentIndex: _index,
      child: Scaffold(
        extendBody: true,
        body: Padding(
          padding: const EdgeInsets.only(bottom: 90),
          child: Navigator(
            key: _shellNavKey,
            initialRoute: '/',
            onGenerateRoute: (settings) {
              if (settings.name == '/') {
                return MaterialPageRoute<void>(
                  settings: settings,
                  builder: (_) => _ShellTabs(onOpenRoute: openRoute),
                );
              }
              return null;
            },
          ),
        ),
        bottomNavigationBar: AppBottomNav(
          currentIndex: _index,
          onTap: goToTab,
        ),
      ),
    );
  }
}

class _ShellTabs extends StatelessWidget {
  const _ShellTabs({required this.onOpenRoute});

  final ValueChanged<String> onOpenRoute;

  @override
  Widget build(BuildContext context) {
    final shell = ShellScope.of(context);
    final index = shell.currentIndex;
    final goToTab = shell.goToTab;

    return IndexedStack(
      index: index,
      sizing: StackFit.expand,
      children: [
        LandingContent(
          showLogo: true,
          onActivities: () => onOpenRoute('programme'),
          onBuyTickets: () => onOpenRoute('tickets'),
          onSupport: () => onOpenRoute('donate'),
          onVote: () => onOpenRoute('vote'),
        ),
        const ShopScreen(title: 'Products'),
        const NewsScreen(title: 'Media'),
        const AboutScreen(),
        MenuScreen(onOpenRoute: onOpenRoute, goToTab: goToTab),
      ],
    );
  }
}
