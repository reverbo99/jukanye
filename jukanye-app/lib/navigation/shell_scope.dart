import 'package:flutter/material.dart';

/// Provides the shell nested navigator + tab switcher to descendants.
class ShellScope extends InheritedWidget {
  const ShellScope({
    super.key,
    required this.navigatorKey,
    required this.goToTab,
    required this.currentIndex,
    required super.child,
  });

  final GlobalKey<NavigatorState> navigatorKey;
  final void Function(int tab) goToTab;
  final int currentIndex;

  static ShellScope? maybeOf(BuildContext context) {
    return context.dependOnInheritedWidgetOfExactType<ShellScope>();
  }

  static ShellScope of(BuildContext context) {
    final scope = maybeOf(context);
    assert(scope != null, 'ShellScope not found');
    return scope!;
  }

  static NavigatorState? maybeNavigator(BuildContext context) {
    return maybeOf(context)?.navigatorKey.currentState;
  }

  @override
  bool updateShouldNotify(ShellScope oldWidget) {
    return currentIndex != oldWidget.currentIndex ||
        goToTab != oldWidget.goToTab;
  }
}
