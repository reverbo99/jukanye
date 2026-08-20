import 'dart:io' show Platform;

import 'package:flutter/foundation.dart' show kIsWeb;

/// Public CMS API configuration.
///
/// Default production API: `https://jukanye.online`.
/// Local override: `flutter run --dart-define=API_BASE_URL=http://127.0.0.1:8000`
/// Android emulator: `--dart-define=API_BASE_URL=http://10.0.2.2:8000`
abstract final class ApiConfig {
  static const String _fromDefine = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'https://jukanye.online',
  );

  static String get baseUrl {
    if (_fromDefine.isNotEmpty) {
      return _stripTrailingSlash(_fromDefine);
    }
    if (!kIsWeb && Platform.isAndroid) {
      return 'http://10.0.2.2:8000';
    }
    return 'http://127.0.0.1:8000';
  }

  static String get apiPrefix => '$baseUrl/api/v1';

  static const String _voteUrlFromDefine = String.fromEnvironment(
    'VOTE_URL',
    defaultValue: 'https://jukanye.online/apk/eVoting.apk',
  );

  /// Voting app APK download URL (same as website Vote menu).
  static String get voteApkUrl => _voteUrlFromDefine;

  /// Laravel `asset()` often emits `http://127.0.0.1:8000/storage/...`.
  /// Rewrite that host so emulators/devices can reach media.
  static String? mediaUrl(String? url) {
    if (url == null || url.isEmpty) return null;
    final uri = Uri.tryParse(url);
    if (uri == null || !uri.hasScheme) return url;

    final base = Uri.parse(baseUrl);
    final host = uri.host.toLowerCase();
    if (host == '127.0.0.1' || host == 'localhost') {
      return uri
          .replace(
            scheme: base.scheme,
            host: base.host,
            port: base.hasPort ? base.port : null,
          )
          .toString();
    }
    return url;
  }

  static String _stripTrailingSlash(String value) {
    if (value.endsWith('/')) {
      return value.substring(0, value.length - 1);
    }
    return value;
  }
}
