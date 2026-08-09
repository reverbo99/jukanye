class ApiException implements Exception {
  const ApiException(
    this.message, {
    this.statusCode,
    this.body,
  });

  final String message;
  final int? statusCode;
  final Map<String, dynamic>? body;

  bool get requiresConfiguration {
    final data = body?['data'];
    if (data is Map<String, dynamic>) {
      return data['requires_configuration'] == true;
    }
    return body?['requires_configuration'] == true;
  }

  @override
  String toString() =>
      statusCode == null ? message : 'ApiException($statusCode): $message';
}
