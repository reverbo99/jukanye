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

  Future<Map<String, dynamic>> getJson(
    String path, {
    Map<String, String>? query,
  }) async {
    final normalized = path.startsWith('/') ? path : '/$path';
    final uri = Uri.parse('${ApiConfig.apiPrefix}$normalized').replace(
      queryParameters: query == null || query.isEmpty ? null : query,
    );

    try {
      final response = await _http
          .get(uri, headers: const {'Accept': 'application/json'})
          .timeout(_timeout);

      final decoded = _decodeBody(response.body);

      if (response.statusCode < 200 || response.statusCode >= 300) {
        final message = decoded is Map<String, dynamic>
            ? (decoded['message'] as String? ??
                'Request failed (${response.statusCode})')
            : 'Request failed (${response.statusCode})';
        throw ApiException(message, statusCode: response.statusCode);
      }

      if (decoded is! Map<String, dynamic>) {
        throw const ApiException('Unexpected response format');
      }
      return decoded;
    } on TimeoutException {
      throw const ApiException('Connection timed out');
    } on ApiException {
      rethrow;
    } catch (_) {
      throw const ApiException('Unable to reach the server');
    }
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
