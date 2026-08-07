import 'dart:ui' show PlatformDispatcher;

import 'package:flutter/widgets.dart';

/// Prefer Swahili when the app/device locale is `sw`, else English.
/// Falls back to the other language when the preferred value is empty.
String localizedText({
  required String? en,
  required String? sw,
  BuildContext? context,
}) {
  final code = _localeCode(context);
  if (code == 'sw') {
    final preferred = sw?.trim() ?? '';
    if (preferred.isNotEmpty) return preferred;
    return en?.trim() ?? '';
  }
  final preferred = en?.trim() ?? '';
  if (preferred.isNotEmpty) return preferred;
  return sw?.trim() ?? '';
}

String _localeCode(BuildContext? context) {
  if (context != null) {
    return Localizations.localeOf(context).languageCode.toLowerCase();
  }
  return PlatformDispatcher.instance.locale.languageCode.toLowerCase();
}
