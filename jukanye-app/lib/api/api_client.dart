import 'dart:async';
import 'dart:convert';

import 'package:http/http.dart' as http;

import '../config/api_config.dart';
import 'api_exception.dart';

/// Thin HTTP client for Laravel `/api/v1` JSON endpoints.
class ApiClient {
  ApiClient({http.Client? httpClient}) : _http = httpClient ?? http.Client();

  final http.Client _http;
  static const _timeout = Duration(seconds: 20);

  String? _bearerToken;

  String? get bearerToken => _bearerToken;

  void setBearerToken(String? token) {
    final trimmed = token?.trim();
    _bearerToken = (trimmed == null || trimmed.isEmpty) ? null : trimmed;
  }

  Future<Map<String, dynamic>> getJson(
    String path, {
    Map<String, String>? query,
  }) {
    return _sendJson(
      method: 'GET',
      path: path,
      query: query,
    );
  }

  Future<Map<String, dynamic>> postJson(
    String path, {
    Map<String, dynamic>? body,
    Map<String, String>? query,
  }) {
    return _sendJson(
      method: 'POST',
      path: path,
      query: query,
      body: body,
    );
  }

  Future<Map<String, dynamic>> putJson(
    String path, {
    Map<String, dynamic>? body,
    Map<String, String>? query,
  }) {
    return _sendJson(
      method: 'PUT',
      path: path,
      query: query,
      body: body,
    );
  }

  Future<Map<String, dynamic>> _sendJson({
    required String method,
    required String path,
    Map<String, String>? query,
    Map<String, dynamic>? body,
  }) async {
    final normalized = path.startsWith('/') ? path : '/$path';
    final uri = Uri.parse('${ApiConfig.apiPrefix}$normalized').replace(
      queryParameters: query == null || query.isEmpty ? null : query,
    );

    final headers = <String, String>{
      'Accept': 'application/json',
      if (body != null) 'Content-Type': 'application/json',
      if (_bearerToken != null) 'Authorization': 'Bearer $_bearerToken',
    };

    try {
      late final http.Response response;
      switch (method) {
        case 'GET':
          response = await _http.get(uri, headers: headers).timeout(_timeout);
        case 'POST':
          response = await _http
              .post(
                uri,
                headers: headers,
                body: body == null ? null : jsonEncode(body),
              )
              .timeout(_timeout);
        case 'PUT':
          response = await _http
              .put(
                uri,
                headers: headers,
                body: body == null ? null : jsonEncode(body),
              )
              .timeout(_timeout);
        default:
          throw ApiException('Unsupported HTTP method: $method');
      }

      final decoded = _decodeBody(response.body);
      final map = decoded is Map<String, dynamic> ? decoded : null;

      if (response.statusCode < 200 || response.statusCode >= 300) {
        final message = _errorMessage(map, response.statusCode);
        throw ApiException(
          message,
          statusCode: response.statusCode,
          body: map,
        );
      }

      if (map == null) {
        throw const ApiException('Unexpected response format');
      }
      return map;
    } on TimeoutException {
      throw const ApiException('Connection timed out');
    } on ApiException {
      rethrow;
    } catch (_) {
      throw const ApiException('Unable to reach the server');
    }
  }

  String _errorMessage(Map<String, dynamic>? map, int statusCode) {
    if (map == null) return 'Request failed ($statusCode)';

    final data = map['data'];
    if (data is Map<String, dynamic>) {
      final nested = data['message'];
      if (nested is String && nested.trim().isNotEmpty) {
        return nested;
      }
    }

    final message = map['message'];
    if (message is String && message.trim().isNotEmpty) {
      return message;
    }

    final errors = map['errors'];
    if (errors is Map<String, dynamic> && errors.isNotEmpty) {
      final first = errors.values.first;
      if (first is List && first.isNotEmpty) {
        return first.first.toString();
      }
      if (first is String && first.trim().isNotEmpty) {
        return first;
      }
    }

    return 'Request failed ($statusCode)';
  }

  dynamic _decodeBody(String body) {
    if (body.isEmpty) return null;
    try {
      return jsonDecode(body);
    } catch (_) {
      return body;
    }
  }

  void close() => _http.close();
}
