import 'dart:convert';

import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../api/jukanye_api.dart';

/// Persists API auth token + user payload for the mobile app.
class AuthSession extends ChangeNotifier {
  AuthSession();

  static const _tokenKey = 'jukanye_auth_token';
  static const _userKey = 'jukanye_auth_user';

  static final AuthSession instance = AuthSession();

  String? _token;
  Map<String, dynamic>? _user;
  bool _ready = false;

  String? get token => _token;
  Map<String, dynamic>? get user => _user;
  bool get ready => _ready;
  bool get isLoggedIn => _token != null && _token!.isNotEmpty;

  String get displayName => (_user?['name'] as String?)?.trim().isNotEmpty == true
      ? (_user!['name'] as String).trim()
      : 'Guest';

  String? get email => (_user?['email'] as String?)?.trim();
  String? get phone => (_user?['phone'] as String?)?.trim();

  Future<void> load() async {
    final prefs = await SharedPreferences.getInstance();
    _token = prefs.getString(_tokenKey);
    final rawUser = prefs.getString(_userKey);
    if (rawUser != null && rawUser.isNotEmpty) {
      try {
        final decoded = jsonDecode(rawUser);
        _user = decoded is Map<String, dynamic>
            ? decoded
            : Map<String, dynamic>.from(decoded as Map);
      } catch (_) {
        _user = null;
      }
    } else {
      _user = null;
    }

    JukanyeApi.instance.setAuthToken(_token);
    _ready = true;
    notifyListeners();
  }

  Future<void> saveSession({
    required String token,
    required Map<String, dynamic> user,
  }) async {
    _token = token.trim();
    _user = Map<String, dynamic>.from(user);
    JukanyeApi.instance.setAuthToken(_token);

    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_tokenKey, _token!);
    await prefs.setString(_userKey, jsonEncode(_user));
    notifyListeners();
  }

  Future<void> updateUser(Map<String, dynamic> user) async {
    _user = Map<String, dynamic>.from(user);
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_userKey, jsonEncode(_user));
    notifyListeners();
  }

  Future<void> clear() async {
    _token = null;
    _user = null;
    JukanyeApi.instance.setAuthToken(null);

    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_tokenKey);
    await prefs.remove(_userKey);
    notifyListeners();
  }
}
