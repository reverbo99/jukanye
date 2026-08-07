import 'package:flutter/material.dart';

import '../models/ticket_tier.dart';
import '../screens/about_screen.dart';
import '../screens/donate_screen.dart';
import '../screens/home_screen.dart';
import '../screens/ticket_details_screen.dart';
import '../screens/tickets_screen.dart';
import '../widgets/app_bottom_nav.dart';
import 'app_drawer.dart';
import 'app_page_route.dart';
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
    _index = widget.initialIndex;
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

  void openTicketDetails(TicketTier ticket) {
    _shellNavKey.currentState?.push(
      AppPageRoute<void>(page: TicketDetailsScreen(ticket: ticket)),
    );
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
                  builder: (_) => _ShellTabs(
                    onOpenTicketDetails: openTicketDetails,
                    onOpenRoute: openRoute,
                  ),
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
  const _ShellTabs({
    required this.onOpenTicketDetails,
    required this.onOpenRoute,
  });

  final ValueChanged<TicketTier> onOpenTicketDetails;
  final ValueChanged<String> onOpenRoute;

  @override
  Widget build(BuildContext context) {
    final shell = ShellScope.of(context);
    final index = shell.currentIndex;
    final goToTab = shell.goToTab;

    final pages = [
      HomeScreen(
        onOpenDonate: () => goToTab(2),
        onOpenTickets: () => goToTab(1),
        onOpenRoute: onOpenRoute,
        goToTab: goToTab,
      ),
      TicketsScreen(
        onBuy: onOpenTicketDetails,
        onMyTickets: () => onOpenRoute('my_tickets'),
        goToTab: goToTab,
      ),
      DonateScreen(onOpenRoute: onOpenRoute, goToTab: goToTab),
      const AboutScreen(),
    ];

    return Stack(
      children: [
        for (var i = 0; i < pages.length; i++)
          IgnorePointer(
            ignoring: index != i,
            child: AnimatedOpacity(
              opacity: index == i ? 1 : 0,
              duration: const Duration(milliseconds: 260),
              curve: Curves.easeOutCubic,
              child: AnimatedSlide(
                offset: index == i ? Offset.zero : const Offset(0.02, 0),
                duration: const Duration(milliseconds: 260),
                curve: Curves.easeOutCubic,
                child: TickerMode(
                  enabled: index == i,
                  child: pages[i],
                ),
              ),
            ),
          ),
      ],
    );
  }
}
