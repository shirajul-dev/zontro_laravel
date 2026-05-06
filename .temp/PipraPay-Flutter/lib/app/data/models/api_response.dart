class ApiResponse<T> {
  const ApiResponse({
    required this.status,
    this.message,
    this.data,
    this.raw,
  });

  final bool status;
  final String? message;
  final T? data;
  final Map<String, dynamic>? raw;

  factory ApiResponse.fromJson(
    Map<String, dynamic> json,
    T Function(Object?) decoder,
  ) {
    final Object? payload = json['response'] ?? json['data'];
    return ApiResponse<T>(
      status: _statusFromJson(json['status']),
      message: json['message'] as String?,
      data: payload == null ? null : decoder(payload),
      raw: json,
    );
  }

  static bool _statusFromJson(Object? value) {
    if (value is bool) {
      return value;
    }
    if (value is String) {
      return value.toLowerCase() == 'true';
    }
    if (value is num) {
      return value > 0;
    }
    return false;
  }
}
