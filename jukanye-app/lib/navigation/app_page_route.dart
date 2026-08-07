import 'package:flutter/material.dart';

import 'shell_scope.dart';

/// Shared navigation helpers with smooth page transitions.
abstract final class AppNav {
  static NavigatorState _nav(BuildContext context) {
    return ShellScope.maybeNavigator(context) ?? Navigator.of(context);
  }

  static Future<T?> push<T>(BuildContext context, Widget page) {
    return _nav(context).push<T>(AppPageRoute<T>(page: page));
  }

  static Future<T?> pushReplacement<T, TO>(BuildContext context, Widget page) {
    return _nav(context).pushReplacement<T, TO>(
      AppPageRoute<T>(page: page, fadeOnly: true),
    );
  }

  /// Replaces the entire app stack (root navigator).
  /// Always use this for shell exits (e.g. Thank You → Home) so a new
  /// [MainShell] is never nested inside the shell navigator.
  static Future<T?> pushAndRemoveUntil<T>(
    BuildContext context,
    Widget page,
  ) {
    return Navigator.of(context, rootNavigator: true).pushAndRemoveUntil<T>(
      AppPageRoute<T>(page: page, fadeOnly: true),
      (_) => false,
    );
  }

  static void popToShellRoot(BuildContext context) {
    final shellNav = ShellScope.maybeNavigator(context);
    shellNav?.popUntil((route) => route.isFirst);
  }
}

/// Fade + subtle slide for pushes; fade-only for splash/replacements.
class AppPageRoute<T> extends PageRouteBuilder<T> {
  AppPageRoute({
    required Widget page,
    this.fadeOnly = false,
    super.settings,
  }) : super(
          transitionDuration: const Duration(milliseconds: 380),
          reverseTransitionDuration: const Duration(milliseconds: 300),
          pageBuilder: (context, animation, secondaryAnimation) => page,
          transitionsBuilder: (context, animation, secondaryAnimation, child) {
            final curved = CurvedAnimation(
              parent: animation,
              curve: Curves.easeOutCubic,
              reverseCurve: Curves.easeInCubic,
            );

            if (fadeOnly) {
              return FadeTransition(opacity: curved, child: child);
            }

            final offset = Tween<Offset>(
              begin: const Offset(0.06, 0),
              end: Offset.zero,
            ).animate(curved);

            final fadeOut = Tween<double>(begin: 1, end: 0.92).animate(
              CurvedAnimation(
                parent: secondaryAnimation,
                curve: Curves.easeOut,
              ),
            );

            return FadeTransition(
              opacity: curved,
              child: SlideTransition(
                position: offset,
                child: FadeTransition(
                  opacity: fadeOut,
                  child: child,
                ),
              ),
            );
          },
        );

  final bool fadeOnly;
}
